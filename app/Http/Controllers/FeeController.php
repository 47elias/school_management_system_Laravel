<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\Expense;
use App\Models\Payslip;
use App\Models\FeeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Pesepay SDK Namespace Import
use Pesepay\Pesepay;

class FeeController extends Controller
{
    /**
     * Show a specific student's financial statement.
     */
    public function show(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $allTerms = Term::orderBy('academic_year', 'desc')->orderBy('start_date', 'desc')->get();
        $selectedTermId = $request->get('term_id');

        $term = $selectedTermId
            ? Term::find($selectedTermId)
            : Term::where('is_current', true)->first();

        if (!$term) {
            return back()->with('error', 'Please set an active term first.');
        }

        // 1. CALCULATE CARRY FORWARD (Strictly from the Ledger)
        $pastTransactions = Payment::where('student_id', $student->id)
            ->whereHas('term', function($q) use ($term) {
                $q->where('start_date', '<', $term->start_date);
            })->get();

        $carriedForward = 0;
        foreach($pastTransactions as $pt) {
            if ($pt->amount_paid < 0) {
                $carriedForward += abs($pt->amount_paid);
            } else {
                $carriedForward -= $pt->amount_paid;
            }
        }

        // 2. CURRENT TERM LEDGER CALCULATION
        $termTransactions = Payment::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->get();

        $termRawExpected = $termTransactions->where('amount_paid', '<', 0)->sum(function($q) { 
            return abs($q->amount_paid); 
        });
        
        $termPaid = $termTransactions->where('amount_paid', '>', 0)->sum('amount_paid');

        // 3. FINAL CALCULATIONS
        $adjustedTermExpected = $termRawExpected + $carriedForward;
        $trueBalance = $carriedForward + $termRawExpected - $termPaid;

        $student->carried_forward = $carriedForward;
        $student->expected_total = $adjustedTermExpected;
        $student->calculated_balance = $trueBalance;
        $student->monthly_arrears = $this->calculateSumOfReds($student, $term, $adjustedTermExpected, $termPaid);

        return view('fees.show', compact(
            'student',
            'term',
            'allTerms',
            'carriedForward',
            'termRawExpected',
            'termPaid',
            'trueBalance'
        ));
    }

    /**
     * Handle credit deduction / withdrawal.
     */
    public function deductCredit(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'amount'  => 'required|numeric|min:0.01',
            'term_id' => 'required|exists:terms,id',
            'remarks' => 'nullable|string'
        ]);

        Payment::create([
            'student_id'     => $student->id,
            'term_id'        => $data['term_id'],
            'amount_paid'    => -abs($data['amount']),
            'payment_date'   => now(),
            'payment_method' => 'Credit Withdrawal',
            'reference_no'   => 'WD-' . strtoupper(uniqid()),
            'remarks'        => $data['remarks'] ?? 'Withdrawal of overpayment'
        ]);

        return back()->with('success', 'Amount successfully deducted from current term.');
    }

    public function allocateCredit($id)
    {
        $student = Student::findOrFail($id);
        return back()->with('success', "The credit for {$student->name} {$student->surname} has been acknowledged and applied to the current balance.");
    }

    public function balanceReport(Request $request)
    {
        $selectedGrade = $request->get('grade');
        $selectedTermId = $request->get('term_id');
        $searchName = $request->get('search');
        $status = $request->get('status');

        $terms = Term::orderBy('academic_year', 'desc')->get();
        $grades = Student::distinct()->orderBy('grade', 'asc')->pluck('grade');

        $currentTerm = $selectedTermId
            ? Term::find($selectedTermId)
            : Term::where('is_current', true)->first();

        if (!$currentTerm) {
            return back()->with('error', 'Please set an active term first.');
        }

        // Calculate overarching totals
        $totalRevenue = Payment::sum('amount_paid');
        $totalGeneralExpenses = Expense::sum('amount');
        $totalSalaries = Payslip::sum('net_salary');
        $totalExpenses = $totalGeneralExpenses + $totalSalaries;
        $schoolBalance = $totalRevenue - $totalExpenses;

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Daily and Weekly Income
        $dailyIncome = Payment::whereDate('payment_date', $today)->sum('amount_paid');
        $weeklyIncome = Payment::whereBetween('payment_date', [$startOfWeek, $endOfWeek])->sum('amount_paid');

        // Daily and Weekly Expenses
        $dailyExpense = Expense::whereDate('expense_date', $today)->sum('amount') +
                        Payslip::whereDate('payment_date', $today)->sum('net_salary');

        $weeklyExpense = Expense::whereBetween('expense_date', [$startOfWeek, $endOfWeek])->sum('amount') +
                         Payslip::whereBetween('payment_date', [$startOfWeek, $endOfWeek])->sum('net_salary');

        // Term Expenses
        $currentTermExpenses = Expense::whereBetween('expense_date', [$currentTerm->start_date, $currentTerm->end_date])->sum('amount') +
                               Payslip::whereBetween('payment_date', [$currentTerm->start_date, $currentTerm->end_date])->sum('net_salary');

        $query = Student::query();

        if ($selectedGrade) { $query->where('grade', $selectedGrade); }
        if ($searchName) {
            $query->where(function($q) use ($searchName) {
                $q->where('name', 'like', "%{$searchName}%")
                  ->orWhere('surname', 'like', "%{$searchName}%")
                  ->orWhere('student_number', 'like', "%{$searchName}%");
            });
        }

        $students = $query->get();

        // N+1 Optimization: Load all ledger payments into memory
        $studentIds = $students->pluck('id');
        $allPayments = Payment::with('term')->whereIn('student_id', $studentIds)->get()->groupBy('student_id');

        $report = $students->map(function ($student) use ($currentTerm, $allPayments) {
            
            $studentPayments = $allPayments->get($student->id, collect());
            
            // Calculate Carried Forward from Ledger
            $pastTransactions = $studentPayments->filter(function($p) use ($currentTerm) {
                return $p->term && $p->term->start_date < $currentTerm->start_date;
            });

            $carriedForward = 0;
            foreach($pastTransactions as $pt) {
                if ($pt->amount_paid < 0) {
                    $carriedForward += abs($pt->amount_paid);
                } else {
                    $carriedForward -= $pt->amount_paid;
                }
            }

            // Current Term calculations
            $termTransactions = $studentPayments->filter(function($p) use ($currentTerm) {
                return $p->term_id == $currentTerm->id;
            });

            $termRawExpected = $termTransactions->where('amount_paid', '<', 0)->sum(function($q) { 
                return abs($q->amount_paid); 
            });
            $termPaid = $termTransactions->where('amount_paid', '>', 0)->sum('amount_paid');

            $adjustedTermExpected = $termRawExpected + $carriedForward;
            $trueBalance = $carriedForward + $termRawExpected - $termPaid;

            return (object)[
                'id'              => $student->id,
                'term_id'         => $student->term_id,
                'student_number'  => $student->student_number,
                'name'            => $student->name,
                'surname'         => $student->surname,
                'grade'           => $student->grade,
                'carried_forward' => $carriedForward,
                'raw_expected'    => $termRawExpected,
                'expected'        => $adjustedTermExpected,
                'paid'            => $termPaid,
                'balance'         => $trueBalance,
                'monthly_arrears' => $this->calculateSumOfReds($student, $currentTerm, $adjustedTermExpected, $termPaid)
            ];
        });

        if ($status == 'arrears') {
            $report = $report->where('balance', '>', 0);
        } elseif ($status == 'cleared') {
            $report = $report->where('balance', '<=', 0);
        }

        return view('fees.report', compact(
            'report', 'currentTerm', 'terms', 'grades',
            'selectedGrade', 'selectedTermId', 'searchName', 'status',
            'dailyIncome', 'dailyExpense', 'weeklyIncome', 'weeklyExpense',
            'schoolBalance', 'currentTermExpenses', 'totalRevenue'
        ));
    }

    private function calculateSumOfReds($student, $term, $totalFee, $totalPaid)
    {
        if (!$term || $totalFee <= 0) return 0;
        $monthCount = method_exists($term, 'getBillingDuration') ? $term->getBillingDuration() : 1;
        $installment = $totalFee / max($monthCount, 1);
        $billingDates = method_exists($term, 'getBillingMonthDates') ? $term->getBillingMonthDates() : [];

        $pool = $totalPaid;
        $sumArrears = 0;
        $now = Carbon::now()->startOfMonth();

        foreach ($billingDates as $mDate) {
            $mDateObj = Carbon::parse($mDate)->startOfMonth();
            $paidForThisMonth = min($installment, $pool);
            $pool -= $paidForThisMonth;
            $remainingForMonth = $installment - $paidForThisMonth;

            if ($now->gte($mDateObj) && $remainingForMonth > 0) {
                $sumArrears += $remainingForMonth;
            }
        }
        return $sumArrears;
    }

    public function showStructure(Request $request)
    {
        $terms = Term::orderBy('academic_year', 'desc')->get();
        $selectedTermId = $request->get('term_id');
        $currentTerm = $selectedTermId
            ? Term::find($selectedTermId)
            : Term::where('is_current', true)->first();

        if (!$currentTerm && $terms->count() > 0) {
            $currentTerm = $terms->first();
        }

        $structures = FeeStructure::with(['term', 'student'])
            ->where('term_id', $currentTerm->id ?? 0)
            ->orderBy('grade', 'asc')
            ->get();

        $grades = Student::distinct()->orderBy('grade', 'asc')->pluck('grade');
        $students = Student::where('status', 'active')->orderBy('surname', 'asc')->get();

        return view('fees.structure', compact('terms', 'structures', 'grades', 'students', 'currentTerm'));
    }

    public function storeStructure(Request $request)
    {
        $data = $request->validate([
            'fee_name'   => 'required|string|max:255',
            'amount'     => 'required|numeric',
            'grade'      => 'required',
            'term_id'    => 'required|exists:terms,id',
            'student_id' => 'nullable|exists:students,id'
        ]);

        FeeStructure::create($data);
        return back()->with('success', 'Fee structure updated successfully!');
    }

    public function destroyStructure($id)
    {
        FeeStructure::findOrFail($id)->delete();
        return back()->with('success', 'Fee item removed.');
    }

    public function index(Request $request)
    {
        $query = Payment::with(['student', 'term'])->orderBy('payment_date', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('surname', 'like', "%{$search}%")
                       ->orWhere('student_number', 'like', "%{$search}%");
                })->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(20)->appends($request->all());
        
        return view('fees.index', compact('payments'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('surname', 'asc')->get();
        $terms = Term::orderBy('is_current', 'desc')->get();
        return view('fees.create', compact('students', 'terms'));
    }

    public function bulkStoreStructure(Request $request)
    {
        $request->validate([
            'term_id'  => 'required|exists:terms,id',
            'grades'   => 'required|array',
            'fee_name' => 'required|string|max:255',
            'amount'   => 'required|numeric|min:0',
        ]);

        $termId = $request->term_id;
        $feeName = $request->fee_name;
        $amount = $request->amount;
        $insertedCount = 0;

        foreach ($request->grades as $grade) {
            $exists = FeeStructure::where('term_id', $termId)
                ->where('grade', $grade)
                ->where('fee_name', $feeName)
                ->whereNull('student_id')
                ->exists();

            if (!$exists) {
                FeeStructure::create([
                    'term_id'  => $termId,
                    'grade'    => $grade,
                    'fee_name' => $feeName,
                    'amount'   => $amount,
                ]);
                $insertedCount++;
            }
        }

        if ($insertedCount > 0) {
            return back()->with('success', "Successfully applied {$feeName} to {$insertedCount} classes.");
        }

        return back()->with('info', "No new fees were added. They might already exist for the selected classes.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'term_id'        => 'required|exists:terms,id',
            'amount_paid'    => 'required|numeric|min:0',
            'payment_date'   => 'required',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string',
            'remarks'        => 'nullable|string'
        ]);

        try {
            $data['payment_date'] = Carbon::parse($request->payment_date)->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['payment_date' => 'Invalid date format.']);
        }

        Payment::create($data);
        return redirect()->route('fees.index')->with('success', 'Payment recorded successfully!');
    }

    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return back()->with('success', 'Payment record has been deleted successfully.');
    }

    /**
     * INITIATE PESEPAY TRANSACTION
     */
    public function payWithPesepay(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'term_id'     => 'nullable|exists:terms,id',
            'amount'      => 'nullable|numeric|min:0.50',
            'amount_paid' => 'nullable|numeric|min:0.50',
            'currency'    => 'required|string',
            'reason'      => 'nullable|string|max:255',
            'payer_phone' => 'nullable|string',
            'payer_email' => 'nullable|email',
        ]);

        $student = Student::findOrFail($request->student_id);
        $amount = (float) ($request->amount ?? $request->amount_paid ?? 0);
        
        // Normalize currency for Pesepay ISO standard
        $rawCurrency = strtoupper($request->currency ?? 'USD');
        $currency = ($rawCurrency === 'ZIG' || $rawCurrency === 'ZWG') ? 'ZWG' : 'USD';
        
        $reason = $request->reason ?? $request->remarks ?? ('Tuition Fee Payment for ' . $student->name . ' ' . $student->surname);

        $integrationKey = config('services.pesepay.integration_key');
        $encryptionKey  = config('services.pesepay.encryption_key');

        if (empty($integrationKey) || empty($encryptionKey)) {
            return back()->with('error', 'Pesepay API credentials are missing in config/services.php or .env file.');
        }

        $returnUrl = config('services.pesepay.return_url') ?: url('/fees/pesepay/return');
        $resultUrl = config('services.pesepay.result_url') ?: url('/fees/pesepay/result');
        $merchantReference = 'FEE-' . $student->id . '-' . time();

        // 1. Primary: Use Codevirtus Pesepay SDK
        if (class_exists(Pesepay::class)) {
            try {
                $pesepay = new Pesepay($integrationKey, $encryptionKey);
                $pesepay->returnUrl = $returnUrl;
                $pesepay->resultUrl = $resultUrl;

                $transaction = $pesepay->createTransaction($amount, $currency, $reason, $merchantReference);
                $response = $pesepay->initiateTransaction($transaction);

                if ($response->success()) {
                    FeeTransaction::create([
                        'student_id'        => $student->id,
                        'term_id'           => $request->term_id,
                        'amount'            => $amount,
                        'currency'          => $currency,
                        'reference'         => $merchantReference,
                        'pesepay_reference' => $response->referenceNumber(),
                        'poll_url'          => $response->pollUrl(),
                        'status'            => 'pending',
                        'channel'           => $request->online_channel ?? 'pesepay',
                        'payer_phone'       => $request->payer_phone,
                        'payer_email'       => $request->payer_email,
                        'remarks'           => $reason,
                    ]);

                    return redirect($response->redirectUrl());
                }

                Log::error('Pesepay SDK Initiation Error: ' . $response->message());
                return back()->with('error', 'Pesepay Gateway Error: ' . ($response->message() ?: 'Initiation failed. Check your credentials.'));
            } catch (\Exception $e) {
                Log::error('Pesepay SDK Exception: ' . $e->getMessage());
            }
        }

        // 2. Encrypted REST API Fallback
        try {
            $payloadData = [
                'amountDetails' => [
                    'amount'       => $amount,
                    'currencyCode' => $currency,
                ],
                'merchantReference' => $merchantReference,
                'reasonForPayment'  => $reason,
                'resultUrl'         => $resultUrl,
                'returnUrl'         => $returnUrl,
            ];

            // AES-256-CBC Payload Encryption
            $iv = substr($encryptionKey, 0, 16);
            $encryptedPayload = openssl_encrypt(json_encode($payloadData), 'aes-256-cbc', $encryptionKey, 0, $iv);

            $apiResponse = Http::withHeaders([
                'Authorization' => $integrationKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.pesepay.com/api/payments/engine/initiate', [
                'payload' => $encryptedPayload,
            ]);

            if ($apiResponse->successful()) {
                $rawResponseBody = $apiResponse->json();
                
                // Decrypt response payload if returned encrypted
                $responseData = [];
                if (isset($rawResponseBody['payload'])) {
                    $decrypted = openssl_decrypt($rawResponseBody['payload'], 'aes-256-cbc', $encryptionKey, 0, $iv);
                    $responseData = json_decode($decrypted, true) ?? [];
                } else {
                    $responseData = $rawResponseBody;
                }

                $pesepayRef = $responseData['referenceNumber'] ?? $merchantReference;
                $redirectUrl = $responseData['redirectUrl'] ?? null;
                $pollUrl = $responseData['pollUrl'] ?? null;

                FeeTransaction::create([
                    'student_id'        => $student->id,
                    'term_id'           => $request->term_id,
                    'amount'            => $amount,
                    'currency'          => $currency,
                    'reference'         => $merchantReference,
                    'pesepay_reference' => $pesepayRef,
                    'poll_url'          => $pollUrl,
                    'status'            => 'pending',
                    'channel'           => $request->online_channel ?? 'pesepay',
                    'payer_phone'       => $request->payer_phone,
                    'payer_email'       => $request->payer_email,
                    'remarks'           => $reason,
                ]);

                if ($redirectUrl) {
                    return redirect($redirectUrl);
                }
            }

            Log::error('Pesepay API Fallback Error: ' . $apiResponse->body());
            return back()->with('error', 'Pesepay API Error: Verify your PESEPAY_INTEGRATION_KEY and PESEPAY_ENCRYPTION_KEY.');
        } catch (\Exception $e) {
            Log::error('Pesepay Connection Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to connect to Pesepay Gateway: ' . $e->getMessage());
        }
    }

    /**
     * Alias method for online payment submission.
     */
    public function payOnline(Request $request)
    {
        return $this->payWithPesepay($request);
    }

    /**
     * CLIENT RETURN REDIRECT HANDLER (PESEPAY)
     */
    public function pesepayReturn(Request $request)
    {
        $referenceNumber = $request->get('referenceNumber');

        if (!$referenceNumber) {
            return redirect()->route('fees.index')->with('error', 'No transaction reference received.');
        }

        $transaction = FeeTransaction::where('pesepay_reference', $referenceNumber)->first();

        $integrationKey = config('services.pesepay.integration_key');
        $encryptionKey  = config('services.pesepay.encryption_key');

        $isPaid = false;

        if (class_exists(Pesepay::class)) {
            try {
                $pesepay = new Pesepay($integrationKey, $encryptionKey);
                $statusResponse = $pesepay->checkPaymentStatus($referenceNumber);
                $isPaid = $statusResponse->paid();
            } catch (\Exception $e) {
                Log::error('Pesepay Return SDK Check Failed: ' . $e->getMessage());
            }
        } else {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $integrationKey,
                ])->get('https://api.pesepay.com/api/payments/engine/check-status', [
                    'referenceNumber' => $referenceNumber
                ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    $isPaid = ($resData['transactionStatus'] ?? '') === 'SUCCESS' || ($resData['paid'] ?? false);
                }
            } catch (\Exception $e) {
                Log::error('Pesepay Return API Check Failed: ' . $e->getMessage());
            }
        }

        if ($transaction && $isPaid) {
            if ($transaction->status !== 'paid') {
                $transaction->update(['status' => 'paid']);

                Payment::create([
                    'student_id'     => $transaction->student_id,
                    'term_id'        => $transaction->term_id,
                    'amount_paid'    => $transaction->amount,
                    'payment_date'   => now()->format('Y-m-d'),
                    'payment_method' => 'Pesepay (' . ($transaction->currency ?? 'USD') . ')',
                    'reference_no'   => $transaction->pesepay_reference ?? $transaction->reference,
                    'remarks'        => 'Online payment via Pesepay Gateway',
                ]);
            }

            return redirect()->route('fees.index')->with('success', 'Payment of ' . ($transaction->currency ?? 'USD') . ' ' . number_format($transaction->amount, 2) . ' received successfully via Pesepay!');
        }

        return redirect()->route('fees.index')->with('error', 'Payment remains pending or was cancelled.');
    }

    /**
     * ASYNCHRONOUS WEBHOOK RESULT CALLBACK (PESEPAY)
     */
    public function pesepayResult(Request $request)
    {
        $referenceNumber = $request->input('referenceNumber');

        if ($referenceNumber) {
            $transaction = FeeTransaction::where('pesepay_reference', $referenceNumber)->first();
            
            if ($transaction && $transaction->status !== 'paid') {
                $integrationKey = config('services.pesepay.integration_key');
                $encryptionKey  = config('services.pesepay.encryption_key');

                $isPaid = false;

                if (class_exists(Pesepay::class)) {
                    try {
                        $pesepay = new Pesepay($integrationKey, $encryptionKey);
                        $statusResponse = $pesepay->checkPaymentStatus($referenceNumber);
                        $isPaid = $statusResponse->paid();
                    } catch (\Exception $e) {
                        Log::error('Pesepay Webhook SDK Check Failed: ' . $e->getMessage());
                    }
                } else {
                    try {
                        $response = Http::withHeaders([
                            'Authorization' => $integrationKey,
                        ])->get('https://api.pesepay.com/api/payments/engine/check-status', [
                            'referenceNumber' => $referenceNumber
                        ]);

                        if ($response->successful()) {
                            $resData = $response->json();
                            $isPaid = ($resData['transactionStatus'] ?? '') === 'SUCCESS' || ($resData['paid'] ?? false);
                        }
                    } catch (\Exception $e) {
                        Log::error('Pesepay Webhook API Check Failed: ' . $e->getMessage());
                    }
                }

                if ($isPaid) {
                    $transaction->update(['status' => 'paid']);

                    Payment::create([
                        'student_id'     => $transaction->student_id,
                        'term_id'        => $transaction->term_id,
                        'amount_paid'    => $transaction->amount,
                        'payment_date'   => now()->format('Y-m-d'),
                        'payment_method' => 'Pesepay (' . ($transaction->currency ?? 'USD') . ')',
                        'reference_no'   => $transaction->pesepay_reference ?? $transaction->reference,
                        'remarks'        => 'Online payment via Pesepay Webhook',
                    ]);
                }
            }
        }

        return response()->json(['status' => 'SUCCESS']);
    }

    /**
     * Issue Invoices strictly checking if a charge already exists.
     * Skips students already charged. Does NOT create reversals.
     */
    public function processInvoices(Request $request)
    {
        $request->validate(['term_id' => 'required|exists:terms,id']);
        $term = Term::findOrFail($request->term_id);

        $structures = FeeStructure::where('term_id', $term->id)->get();
        $students = Student::where('status', 'active')->get();

        $chargedCount = 0;
        $skippedCount = 0;

        foreach ($students as $student) {
            
            $alreadyCharged = Payment::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where(function($query) {
                    $query->where('payment_method', 'Term Invoice')
                          ->orWhere('remarks', 'LIKE', '%Term Fees Charged%');
                })
                ->exists();

            if ($alreadyCharged) {
                $skippedCount++;
                continue; 
            }

            $gradeFees = $structures->where('grade', $student->grade)->whereNull('student_id')->sum('amount');
            $specialFees = $structures->where('student_id', $student->id)->sum('amount');
            $totalTermFee = $gradeFees + $specialFees;

            if ($totalTermFee > 0) {
                Payment::create([
                    'student_id'     => $student->id,
                    'term_id'        => $term->id,
                    'amount_paid'    => -$totalTermFee,
                    'payment_date'   => now()->format('Y-m-d'),
                    'payment_method' => 'Term Invoice',
                    'remarks'        => 'Term Fees Charged (' . $term->term_name . ')',
                    'reference_no'   => 'INV-' . $term->id . '-' . $student->id
                ]);

                $chargedCount++;
            }
        }

        return back()->with('success', "Invoicing Complete: {$chargedCount} students charged successfully. ({$skippedCount} skipped as they were already charged).");
    }
}