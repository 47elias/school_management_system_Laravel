<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Expense;
use App\Models\Payslip;
use App\Models\FeeTransaction;
use App\Services\StudentFinanceService;
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

        $finance = app(StudentFinanceService::class);
        $allTerms = $finance->availableTerms($student);
        $selectedTermId = $request->get('term_id');
        $term = $selectedTermId ? Term::findOrFail($selectedTermId) : null;
        $term = $term ?? $allTerms->firstWhere('is_current', true) ?? $allTerms->first();
        // Even students with no billable term can open their zero-balance statement.
        $term = $term ?? Term::orderByDesc('start_date')->first();
        abort_if(!$term, 404, 'No academic terms have been configured.');
        if (!$allTerms->contains('id', $term->id)) {
            $allTerms->prepend($term);
        }

        if (!$term) {
            return back()->with('error', 'No term in this student’s enrolment period is available.');
        }

        $statement = $finance->statement($student, $term);
        $carriedForward = $statement['carried_forward'];
        $termRawExpected = $statement['base_term_fees'];
        $totalTermCharges = $statement['term_charges'];
        $termPaid = $statement['term_paid'];
        $termTransactions = $statement['transactions'];
        $trueBalance = $statement['balance'];
        $invoices = $termTransactions->where('amount_paid', '<', 0);
        $receipts = $termTransactions->where('amount_paid', '>', 0);
        $feeStructures = FeeStructure::where('term_id', $term->id)
            ->where(function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->orWhere(function ($general) use ($student) {
                        $general->whereNull('student_id')
                            ->where(function ($scope) use ($student) {
                                $scope->where('grade', $student->grade);
                                if ($student->class_id) {
                                    $scope->orWhere('class_id', $student->class_id);
                                }
                            });
                    });
            })->get();

        $student->carried_forward = $carriedForward;
        $student->expected_total = $totalTermCharges + $carriedForward;
        $student->calculated_balance = $trueBalance;
        $student->monthly_arrears = $this->calculateSumOfReds($student, $term, $totalTermCharges + $carriedForward, $termPaid);

        return view('fees.show', compact(
            'student',
            'term',
            'allTerms',
            'carriedForward',
            'termRawExpected',
            'termPaid',
            'trueBalance', 'termTransactions', 'invoices', 'receipts', 'feeStructures'
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

        $term = Term::findOrFail($data['term_id']);
        $finance = app(StudentFinanceService::class);
        if (!$finance->canBillForTerm($student, $term)) {
            return back()->withInput()->withErrors(['term_id' => 'Credit can only be adjusted in a term covered by the student’s enrolment.']);
        }
        $statement = $finance->statement($student, $term);
        if ($statement['balance'] >= 0 || (float) $data['amount'] > abs($statement['balance'])) {
            return back()->withInput()->withErrors(['amount' => 'The withdrawal cannot exceed the student’s available credit.']);
        }

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
        $totalRevenue = Payment::where('amount_paid', '>', 0)->sum('amount_paid');
        $totalGeneralExpenses = Expense::sum('amount');
        $totalSalaries = Payslip::sum('net_salary');
        $totalExpenses = $totalGeneralExpenses + $totalSalaries;
        $schoolBalance = $totalRevenue - $totalExpenses;

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Daily and Weekly Income
        $dailyIncome = Payment::whereDate('payment_date', $today)->where('amount_paid', '>', 0)->sum('amount_paid');
        $weeklyIncome = Payment::whereBetween('payment_date', [$startOfWeek, $endOfWeek])->where('amount_paid', '>', 0)->sum('amount_paid');

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
        $finance = app(StudentFinanceService::class);
        $report = $students->map(function ($student) use ($currentTerm, $finance) {
            $statement = $finance->statement($student, $currentTerm);
            $termRawExpected = $statement['term_charges'];
            $termPaid = $statement['term_paid'];
            $adjustedTermExpected = $termRawExpected + $statement['carried_forward'];
            $trueBalance = $statement['balance'];

            return (object)[
                'id'              => $student->id,
                'term_id'         => $student->term_id,
                'student_number'  => $student->student_number,
                'name'            => $student->name,
                'surname'         => $student->surname,
                'grade'           => $student->grade,
                'carried_forward' => $statement['carried_forward'],
                'raw_expected'    => $termRawExpected,
                'expected'        => $adjustedTermExpected,
                'paid'            => $termPaid,
                'balance'         => $trueBalance,
                'term_dues'       => max(0, (float) $trueBalance),
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

        $gradeLevels = GradeLevel::with('schoolClasses')->orderBy('level_order')->get();
        $grades = $gradeLevels->isNotEmpty()
            ? $gradeLevels->pluck('name')
            : Student::distinct()->orderBy('grade', 'asc')->pluck('grade');
        $schoolClasses = SchoolClass::with('gradeLevel')->orderBy('class_name')->get();
        $students = Student::with(['schoolClass.gradeLevel'])->where('status', 'active')->orderBy('surname', 'asc')->get();

        return view('fees.structure', compact('terms', 'structures', 'grades', 'gradeLevels', 'students', 'currentTerm', 'schoolClasses'));
    }

    public function storeStructure(Request $request)
    {
        $data = $request->validate([
            'fee_name'   => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
            'term_id'    => 'required|exists:terms,id',
            'grade'      => 'nullable|string|max:255',
            'class_id'   => 'nullable|exists:school_classes,id',
            'student_id' => 'nullable|exists:students,id',
            'charge_timing' => 'nullable|in:during_term,end_of_term',
        ]);

        if (!empty($data['student_id'])) {
            $student = Student::with('schoolClass.gradeLevel')->findOrFail($data['student_id']);
            $data['grade'] = $student->schoolClass?->gradeLevel?->name ?? $student->grade;
            $data['class_id'] = null;
        } elseif (!empty($data['class_id'])) {
            $schoolClass = SchoolClass::with('gradeLevel')->findOrFail($data['class_id']);
            $data['grade'] = $schoolClass->gradeLevel?->name ?? $schoolClass->class_name;
        }

        if (empty($data['grade'])) {
            return back()->withInput()->withErrors(['grade' => 'Choose a form, class stream, or student for this fee.']);
        }

        $term = Term::findOrFail($data['term_id']);
        $data['charge_timing'] = $data['charge_timing'] ?? 'during_term';
        FeeStructure::create($data);
        $updatedInvoices = app(StudentFinanceService::class)->refreshTermInvoices($term);
        return back()->with('success', "Fee saved. {$updatedInvoices} existing invoice record(s) refreshed for this term.");
    }

    public function destroyStructure($id)
    {
        $fee = FeeStructure::findOrFail($id);
        $term = $fee->term;
        $fee->delete();
        $updatedInvoices = $term ? app(StudentFinanceService::class)->refreshTermInvoices($term) : 0;
        return back()->with('success', "Fee item removed. {$updatedInvoices} existing invoice record(s) refreshed.");
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
            'charge_timing' => 'nullable|in:during_term,end_of_term',
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
                    'charge_timing' => $request->input('charge_timing', 'during_term'),
                ]);
                $insertedCount++;
            }
        }

        if ($insertedCount > 0) {
            $updatedInvoices = app(StudentFinanceService::class)->refreshTermInvoices(Term::findOrFail($termId));
            return back()->with('success', "Successfully applied {$feeName} to {$insertedCount} forms. {$updatedInvoices} existing invoice record(s) refreshed.");
        }

        return back()->with('info', "No new fees were added. They might already exist for the selected classes.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'term_id'        => 'required|exists:terms,id',
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_date'   => 'required',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|unique:payments,reference_no',
            'remarks'        => 'nullable|string'
        ]);

        try {
            $data['payment_date'] = Carbon::parse($request->payment_date)->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['payment_date' => 'Invalid date format.']);
        }

        $student = Student::findOrFail($data['student_id']);
        $term = Term::findOrFail($data['term_id']);
        $finance = app(StudentFinanceService::class);
        if (!$finance->canBillForTerm($student, $term)
            || Carbon::parse($data['payment_date'])->lt($finance->enrollmentDate($student))) {
            return back()->withInput()->withErrors(['term_id' => 'A student can only be charged or receive payments from their enrolment term onward.']);
        }

        $payment = $finance->recordReceipt($student, $term, $data);
        $allocationMessage = $payment->term_id !== $term->id
            ? ' The receipt was applied to the older ' . ($payment->term->term_name ?? 'term') . ' arrears first; any extra is carried forward.'
            : '';
        return redirect()->route('fees.index')->with('success', 'Payment recorded successfully.' . $allocationMessage);
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
            'term_id'     => 'required|exists:terms,id',
            'amount'      => 'nullable|numeric|min:0.50',
            'amount_paid' => 'nullable|numeric|min:0.50',
            'currency'    => 'required|string',
            'reason'      => 'nullable|string|max:255',
            'payer_phone' => 'nullable|string',
            'payer_email' => 'nullable|email',
        ]);

        $student = Student::findOrFail($request->student_id);
        $term = Term::findOrFail($request->term_id);
        if (!app(StudentFinanceService::class)->canBillForTerm($student, $term)) {
            return back()->with('error', 'This term is before the student’s enrolment period.');
        }
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
            if ($transaction->status !== 'paid' || !$transaction->payment_id) {
                $this->recordPesepayPayment($transaction, 'Online payment via Pesepay Gateway');
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
            
            if ($transaction && ($transaction->status !== 'paid' || !$transaction->payment_id)) {
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
                    $this->recordPesepayPayment($transaction, 'Online payment via Pesepay Webhook');
                }
            }
        }

        return response()->json(['status' => 'SUCCESS']);
    }

    private function recordPesepayPayment(FeeTransaction $transaction, string $remarks): void
    {
        DB::transaction(function () use ($transaction, $remarks) {
            $lockedTransaction = FeeTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $reference = $lockedTransaction->pesepay_reference ?: $lockedTransaction->reference ?: 'FEE-TXN-' . $lockedTransaction->id;
            $student = Student::findOrFail($lockedTransaction->student_id);
            $term = Term::findOrFail($lockedTransaction->term_id);
            $payment = app(StudentFinanceService::class)->recordReceipt($student, $term, [
                'amount_paid' => $lockedTransaction->amount,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'Pesepay (' . ($lockedTransaction->currency ?? 'USD') . ')',
                'reference_no' => $reference,
                'remarks' => $remarks,
            ]);

            $lockedTransaction->update(['status' => 'paid', 'payment_id' => $payment->id]);
        });
    }

    /**
     * Issue Invoices strictly checking if a charge already exists.
     * Skips students already charged. Does NOT create reversals.
     */
    public function processInvoices(Request $request)
    {
        $request->validate(['term_id' => 'required|exists:terms,id']);
        $term = Term::findOrFail($request->term_id);
        $result = app(StudentFinanceService::class)->invoiceTerm($term);

        $reasons = [];
        if ($result['enrolled_after_term'] > 0) {
            $reasons[] = $result['enrolled_after_term'] . ' enrolled after the term ended';
        }
        if ($result['term_not_started'] > 0) {
            $reasons[] = $result['term_not_started'] . ' are in a term that has not started';
        }
        if ($result['missing_term_dates'] > 0) {
            $reasons[] = $result['missing_term_dates'] . ' have a term with missing dates';
        }
        if ($result['already_billed'] > 0) {
            $reasons[] = $result['already_billed'] . ' already have an invoice';
        }
        if ($result['no_schedule'] > 0) {
            $reasons[] = $result['no_schedule'] . ' have no matching fee schedule';
        }

        $message = "{$result['charged']} students billed for {$term->term_name} ({$term->academic_year}); {$result['skipped']} skipped.";
        if ($reasons) {
            $message .= ' Reasons: ' . implode('; ', $reasons) . '.';
        }
        if ($result['charged'] === 0 && $result['skipped'] > 0) {
            $message .= ' No charges were posted. Check the term dates and select the term that covers each student’s enrolment.';
            return back()->with('error', $message);
        }

        return back()->with('success', $message);
    }
}
