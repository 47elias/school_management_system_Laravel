<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\Expense;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeController extends Controller
{
    /**
     * Show a specific student's financial statement.
     */
    public function show(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $allTerms = Term::orderBy('academic_year', 'desc')->get();
        $selectedTermId = $request->get('term_id');

        $term = $selectedTermId
            ? Term::find($selectedTermId)
            : Term::where('is_current', true)->first();

        if (!$term) {
            return back()->with('error', 'Please set an active term first.');
        }

        // 1. CALCULATE CARRY FORWARD
        $pastExpected = FeeStructure::where('term_id', '!=', $term->id)
            ->where('term_id', '>=', $student->term_id)
            ->where(function($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere(function($sq) use ($student) {
                      $sq->where('grade', $student->grade)->whereNull('student_id');
                  });
            })->sum('amount');

        $pastPaid = Payment::where('student_id', $student->id)
            ->where('term_id', '!=', $term->id)
            ->sum('amount_paid');

        $carriedForward = (float)$pastExpected - (float)$pastPaid;

        // 2. CURRENT TERM RAW BILLING
        $termRawExpected = 0;
        if ($term->id >= $student->term_id) {
            $termRawExpected = FeeStructure::where('term_id', $term->id)
                ->where(function($q) use ($student) {
                    $q->where('student_id', $student->id)
                      ->orWhere(function($sq) use ($student) {
                          $sq->where('grade', $student->grade)->whereNull('student_id');
                      });
                })->sum('amount');
        }

        $termPaid = Payment::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->sum('amount_paid');

        // 3. FINAL CALCULATIONS
        $adjustedTermExpected = (float)$termRawExpected + $carriedForward;
        $trueBalance = $adjustedTermExpected - (float)$termPaid;

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
     * Deducts money from the current term pool by recording a negative payment.
     */
    public function deductCredit(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'term_id' => 'required|exists:terms,id',
            'remarks' => 'nullable|string'
        ]);

        // Create a negative payment record to offset the credit
        Payment::create([
            'student_id' => $student->id,
            'term_id' => $data['term_id'],
            'amount_paid' => -abs($data['amount']), // Forces the value to be negative
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

        $report = $students->map(function ($student) use ($currentTerm) {
            $pastExpected = FeeStructure::where('term_id', '!=', $currentTerm->id)
                ->where('term_id', '>=', $student->term_id)
                ->where(function($q) use ($student) {
                    $q->where('student_id', $student->id)
                      ->orWhere(function($sq) use ($student) {
                          $sq->where('grade', $student->grade)->whereNull('student_id');
                      });
                })->sum('amount');

            $pastPaid = Payment::where('student_id', $student->id)
                ->where('term_id', '!=', $currentTerm->id)
                ->sum('amount_paid');

            $carriedForward = (float)$pastExpected - (float)$pastPaid;

            $termRawExpected = 0;
            if ($currentTerm->id >= $student->term_id) {
                $termRawExpected = FeeStructure::where('term_id', $currentTerm->id)
                    ->where(function($q) use ($student) {
                        $q->where('student_id', $student->id)
                          ->orWhere(function($sq) use ($student) {
                              $sq->where('grade', $student->grade)->whereNull('student_id');
                          });
                    })->sum('amount');
            }

            $termPaid = Payment::where('student_id', $student->id)
                ->where('term_id', $currentTerm->id)
                ->sum('amount_paid');

            $adjustedTermExpected = (float)$termRawExpected + $carriedForward;
            $trueBalance = $adjustedTermExpected - (float)$termPaid;

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

    public function index()
    {
        $payments = Payment::with(['student', 'term'])->orderBy('payment_date', 'desc')->paginate(20);
        return view('fees.index', compact('payments'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('surname', 'asc')->get();
        $terms = Term::orderBy('is_current', 'desc')->get();
        return view('fees.create', compact('students', 'terms'));
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
}
