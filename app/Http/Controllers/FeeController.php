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
use Carbon\Carbon;
use Paynow\Payments\Paynow;

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
                $carriedForward += abs($pt->amount_paid); // Charges increase arrears
            } else {
                $carriedForward -= $pt->amount_paid; // Payments reduce arrears
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
            'amount' => 'required|numeric|min:0.01',
            'term_id' => 'required|exists:terms,id',
            'remarks' => 'nullable|string'
        ]);

        Payment::create([
            'student_id' => $student->id,
            'term_id' => $data['term_id'],
            'amount_paid' => -abs($data['amount']),
            'payment_date' => now(),
            'payment_method' => 'Credit Withdrawal',
            'reference_no' => 'WD-' . strtoupper(uniqid()),
            'remarks' => $data['remarks'] ?? 'Withdrawal of overpayment'
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

        $totalRevenue = Payment::sum('amount_paid');
        $totalGeneralExpenses = Expense::sum('amount');
        $totalSalaries = Payslip::sum('net_salary');
        $totalExpenses = $totalGeneralExpenses + $totalSalaries;
        $schoolBalance = $totalRevenue - $totalExpenses;

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $dailyIncome = Payment::whereDate('payment_date', $today)->sum('amount_paid');
        $weeklyIncome = Payment::whereBetween('payment_date', [$startOfWeek, $endOfWeek])->sum('amount_paid');

        $dailyExpense = Expense::whereDate('expense_date', $today)->sum('amount') +
                        Payslip::whereDate('payment_date', $today)->sum('net_salary');

        $weeklyExpense = Expense::whereBetween('expense_date', [$startOfWeek, $endOfWeek])->sum('amount') +
                         Payslip::whereBetween('payment_date', [$startOfWeek, $endOfWeek])->sum('net_salary');

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

        return view('fees.balance_report', compact(
            'report', 'currentTerm', 'terms', 'grades',
            'selectedGrade', 'selectedTermId', 'searchName', 'status',
            'dailyIncome', 'dailyExpense', 'weeklyIncome', 'weeklyExpense',
            'schoolBalance', 'currentTermExpenses'
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
            $data['payment_date'] = Carbon::createFromFormat('d/m/Y', $request->payment_date)->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['payment_date' => 'Invalid date format. Use DD/MM/YYYY.']);
        }

        Payment::create($data);
        return redirect()->route('fees.index')->with('success', 'Payment recorded successfully!');
    }

    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return back()->with('success', 'Payment record has been deleted successfully.');
    }

    public function payOnline(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'term_id'        => 'required|exists:terms,id',
            'amount_paid'    => 'required|numeric|min:0.01',
            'online_channel' => 'required|in:paynow_link,ecocash_push,card',
            'payer_phone'    => 'required_if:online_channel,ecocash_push|nullable|string',
            'payer_email'    => 'nullable|email',
            'remarks'        => 'nullable|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $transaction = FeeTransaction::create([
            'student_id' => $student->id,
            'term_id'    => $validated['term_id'],
            'amount'     => $validated['amount_paid'],
            'channel'    => $validated['online_channel'],
            'status'     => 'pending',
            'remarks'    => $validated['remarks'] ?? null,
        ]);

        $paynow = new Paynow(
            config('services.paynow.integration_id'),
            config('services.paynow.integration_key'),
            route('fees.payOnline.result'),
            route('fees.payOnline.return', $transaction)
        );

        $payment = $paynow->createPayment(
            'FEE-' . $transaction->id,
            $validated['payer_email'] ?: $student->parent_email
        );
        
        $payment->add(
            "School fees - {$student->name} {$student->surname} ({$validated['term_id']})",
            $validated['amount_paid']
        );

        if ($validated['online_channel'] === 'ecocash_push') {
            $response = $paynow->sendMobile($payment, $validated['payer_phone'], 'ecocash');
        } else {
            $response = $paynow->send($payment);
        }

        if (! $response->success()) {
            $transaction->update(['status' => 'failed']);
            return back()->withErrors(['online_payment' => 'Could not start the Paynow transaction. Please try again.']);
        }

        $transaction->update(['poll_url' => $response->pollUrl()]);

        if ($validated['online_channel'] === 'ecocash_push') {
            return back()->with('online_success', $response->instructions());
        }

        return redirect($response->redirectLink());
    }

    public function payOnlineResult(Request $request)
    {
        $reference = str_replace('FEE-', '', $request->input('reference'));
        $transaction = FeeTransaction::findOrFail($reference);

        $paynow = new Paynow(
            config('services.paynow.integration_id'),
            config('services.paynow.integration_key')
        );

        $status = $paynow->pollTransaction($transaction->poll_url);

        if ($status->paid()) {
            $transaction->update(['status' => 'paid']);
        } else {
            $transaction->update(['status' => 'failed']);
        }

        return response('OK');
    }

    public function payOnlineReturn(FeeTransaction $feeTransaction)
    {
        return view('fees.create', [
            'students' => Student::all(),
            'terms'    => Term::all(),
        ])->with(
            $feeTransaction->fresh()->status === 'paid' ? 'success' : 'online_success',
            $feeTransaction->fresh()->status === 'paid'
                ? 'Online payment received — thank you!'
                : 'We\'re waiting for confirmation from Paynow. Refresh in a moment.'
        );
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
            
            // 🚨 FOOLPROOF CHECK: Has this student already been billed for THIS term?
            // This prevents the double-charge shown in your image.
            $alreadyCharged = Payment::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where(function($query) {
                    $query->where('payment_method', 'Term Invoice')
                          ->orWhere('remarks', 'LIKE', '%Term Fees Charged%');
                })
                ->exists();

            if ($alreadyCharged) {
                // If they already have the blue "Term Fees Charged", SKIP them completely!
                $skippedCount++;
                continue; 
            }

            // Calculate total fee owed for this term
            $gradeFees = $structures->where('grade', $student->grade)->whereNull('student_id')->sum('amount');
            $specialFees = $structures->where('student_id', $student->id)->sum('amount');
            $totalTermFee = $gradeFees + $specialFees;

            if ($totalTermFee > 0) {
                // Post the initial invoice as a negative ledger entry
                Payment::create([
                    'student_id'     => $student->id,
                    'term_id'        => $term->id,
                    'amount_paid'    => -$totalTermFee, // Negative posts it to the Charge (Dr) column
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