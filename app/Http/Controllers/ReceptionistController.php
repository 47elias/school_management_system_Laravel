<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\FeeStructure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReceptionistController extends Controller
{
    /**
     * Dashboard: Shows aggregates based on the fixed 3-month billing logic.
     */
    public function dashboard()
    {
        $currentTerm = Term::where('is_current', true)->first();
        $totalArrears = 0;

        if ($currentTerm) {
            $activeStudents = Student::with(['payments', 'feeStructures'])
                ->where('term_id', $currentTerm->id)
                ->get();

            foreach($activeStudents as $student) {
                $totalExpected = $this->getStudentExpectedTotal($student, $currentTerm->id);
                $totalPaid = $student->payments->where('term_id', $currentTerm->id)->sum('amount_paid');

                $totalArrears += $this->calculateSumOfReds($student, $currentTerm, $totalExpected, $totalPaid);
            }
        }

        return view('receptionist.dashboard', [
            'currentTerm'    => $currentTerm,
            'totalStudents'  => Student::count(),
            'todayPayments'  => Payment::whereDate('created_at', Carbon::today())->sum('amount_paid'),
            'recentPayments' => Payment::with('student')->latest()->take(5)->get(),
            'totalClasses'   => SchoolClass::count(),
            'totalArrears'   => $totalArrears,
        ]);
    }

    /**
     * Student Financial Portfolio: UPDATED TO INCLUDE CARRY FORWARD LOGIC
     */
    public function showStudent(Request $request, $id)
    {
        $student = Student::with(['payments', 'term', 'class', 'feeStructures'])
            ->findOrFail($id);

        $allTerms = Term::orderBy('academic_year', 'desc')
                        ->orderBy('term_name', 'desc')
                        ->get();

        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $term = Term::find($selectedTermId);
        } else {
            $term = $student->term ?? Term::where('is_current', true)->first();
        }

        if (!$term) {
            return back()->with('error', 'No active term found to generate financial portfolio.');
        }

        // --- CARRY FORWARD LOGIC ---
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

        // Current Term Logic
        $termRawExpected = $this->getStudentExpectedTotal($student, $term->id);
        $termPaid = Payment::where('student_id', $student->id)
                           ->where('term_id', $term->id)
                           ->sum('amount_paid');

        // Adjust totals for carry forward
        $adjustedExpected = $termRawExpected + $carriedForward;
        $arrears = $this->calculateSumOfReds($student, $term, $adjustedExpected, $termPaid);

        $student->expected_total = $adjustedExpected;
        $student->monthly_installment = 50.00;
        $student->monthly_arrears = $arrears;
        $student->calculated_balance = $adjustedExpected - $termPaid;
        $student->carried_forward = $carriedForward;

        return view('receptionist.students.show', compact('student', 'term', 'allTerms'));
    }

    /**
     * Student Directory: UPDATED TO INCLUDE CARRY FORWARD LOGIC
     */
    public function indexStudents(Request $request)
    {
        $selectedTermId = $request->get('term_id');
        $selectedGrade = $request->get('grade');
        $searchName = $request->get('search');

        $terms = Term::orderBy('academic_year', 'desc')->get();
        $grades = Student::distinct()->whereNotNull('grade')->pluck('grade')->sort();
        $classes = SchoolClass::orderBy('id')->get();

        $currentTerm = $selectedTermId
            ? Term::find($selectedTermId)
            : Term::where('is_current', true)->first();

        if (!$currentTerm) {
            return back()->with('error', 'Please set an active term first.');
        }

        $query = Student::query();
        if ($selectedGrade) $query->where('grade', $selectedGrade);
        if ($request->filled('class_id')) $query->where('class_id', $request->class_id);

        if ($searchName) {
            $query->where(function($q) use ($searchName) {
                $q->where('name', 'LIKE', "%{$searchName}%")
                  ->orWhere('surname', 'LIKE', "%{$searchName}%")
                  ->orWhere('student_number', 'LIKE', "%{$searchName}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();

        $report = $students->getCollection()->map(function ($student) use ($currentTerm) {

            // 1. Calculate Carry Forward
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

            // 2. Current Term Raw
            $termRawExpected = $this->getStudentExpectedTotal($student, $currentTerm->id);
            $termPaid = Payment::where('student_id', $student->id)
                               ->where('term_id', $currentTerm->id)
                               ->sum('amount_paid');

            // 3. Final Adjusted Totals
            $adjustedExpected = $termRawExpected + $carriedForward;
            $trueBalance = $adjustedExpected - $termPaid;

            return (object)[
                'id'              => $student->id,
                'student_number'  => $student->student_number,
                'name'            => $student->name,
                'surname'         => $student->surname,
                'grade'           => $student->grade,
                'expected'        => $adjustedExpected,
                'paid'            => $termPaid,
                'balance'         => $trueBalance,
                'monthly_arrears' => $this->calculateSumOfReds($student, $currentTerm, $adjustedExpected, $termPaid)
            ];
        });

        return view('receptionist.students.index', compact(
            'report', 'students', 'classes', 'terms', 'grades',
            'currentTerm', 'selectedTermId', 'selectedGrade', 'searchName'
        ));
    }

    /**
     * Helper: Get Total Expected
     */
    private function getStudentExpectedTotal($student, $termId)
    {
        if (!$termId) return 0;

        $individualFees = FeeStructure::where('term_id', $termId)
            ->where('student_id', $student->id)
            ->sum('amount');

        if ($individualFees > 0) return (float) $individualFees;

        return (float) FeeStructure::where('term_id', $termId)
            ->where('grade', $student->grade)
            ->whereNull('student_id')
            ->sum('amount');
    }

    /**
     * Helper: FIXED $50 ARREARS LOGIC
     */
    public function calculateSumOfReds($student, $term, $totalFee, $totalPaid)
    {
        if (!$term) return 0;

        $monthlyTarget = 50.00;
        $billingDates = $term->getBillingMonthDates();
        $now = Carbon::now()->startOfMonth();

        $pool = (float)$totalPaid;
        $totalOverdueArrears = 0;

        foreach ($billingDates as $mDate) {
            $billingMonth = Carbon::parse($mDate)->startOfMonth();

            $paidInThisSlot = min($monthlyTarget, $pool);
            $pool -= $paidInThisSlot;

            $shortfall = $monthlyTarget - $paidInThisSlot;

            if ($now->gte($billingMonth) && $shortfall > 0) {
                $totalOverdueArrears += $shortfall;
            }
        }

        return $totalOverdueArrears;
    }

    /**
     * Create Payment: Fully aligned with Select2 Search UI
     */
    public function createPayment(Request $request)
    {
        $currentTerm = Term::where('is_current', true)->first();
        $selectedStudentId = $request->get('student_id');

        $terms = Term::orderBy('academic_year', 'desc')->get();

        $students = Student::all()->map(function($student) use ($currentTerm) {
            $pastExpected = FeeStructure::where('term_id', '!=', $currentTerm->id)
                ->where('term_id', '>=', $student->term_id)
                ->where(function($q) use ($student) {
                    $q->where('student_id', $student->id)->orWhere(function($sq) use ($student) {
                        $sq->where('grade', $student->grade)->whereNull('student_id');
                    });
                })->sum('amount');

            $pastPaid = Payment::where('student_id', $student->id)
                ->where('term_id', '!=', $currentTerm->id)
                ->sum('amount_paid');

            $carriedForward = (float)$pastExpected - (float)$pastPaid;
            $termExpected = $this->getStudentExpectedTotal($student, $currentTerm->id);
            $termPaid = Payment::where('student_id', $student->id)
                               ->where('term_id', $currentTerm->id)
                               ->sum('amount_paid');

            $student->calculated_balance = ($termExpected + $carriedForward) - $termPaid;
            return $student;
        });

        return view('receptionist.payments.create', compact('students', 'currentTerm', 'selectedStudentId', 'terms'));
    }

    /**
     * Store Payment: Handles date conversion and returns payment_id for printing
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference_no'   => 'nullable|string|unique:payments,reference_no',
            'payment_date'   => 'required',
            'term_id'        => 'required|exists:terms,id'
        ]);

        try {
            // Convert dd/mm/yyyy from UI to yyyy-mm-dd for MySQL
            $formattedDate = Carbon::createFromFormat('d/m/Y', $request->payment_date)->format('Y-m-d');

            $payment = Payment::create([
                'student_id'     => $request->student_id,
                'term_id'        => $request->term_id,
                'amount_paid'    => $request->amount_paid,
                'payment_date'   => $formattedDate,
                'payment_method' => $request->payment_method,
                'reference_no'   => $request->reference_no,
                'received_by'    => Auth::id(), // Tracks the user who saved it
                'remarks'        => $request->remarks,
            ]);

            return redirect()->route('receptionist.payments.index')
                ->with([
                    'success' => 'Payment of $' . number_format($request->amount_paid, 2) . ' recorded successfully.',
                    'payment_id' => $payment->id
                ]);

        } catch (\Exception $e) {
            // Returns the user back to the form with the specific error message
            return back()->withInput()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function studentsCreate()
    {
        $terms = Term::all();
        $classes = SchoolClass::all();
        return view('receptionist.students.create', compact('terms', 'classes'));
    }

    public function paymentsIndex()
    {
        $payments = Payment::with('student', 'term')->latest()->paginate(20);
        return view('receptionist.payments.index', compact('payments'));
    }

    public function printReceipt($id)
    {
        // 1. Fetch the payment with relationships
        $payment = Payment::with(['student', 'term'])->findOrFail($id);

        // 2. Define the student variable (this is what's missing in your current setup)
        $student = $payment->student;

        // 3. Pass both to the view
        return view('receptionist.payments.receipt', compact('payment', 'student'));
    }

    public function classesIndex()
    {
        $classes = SchoolClass::withCount('students')->get();
        return view('receptionist.classes.index', compact('classes'));
    }

    public function profile()
    {
        return view('receptionist.profile', ['user' => Auth::user()]);
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'term_id' => 'required|exists:terms,id',
            'grade' => 'required|string',
            'age' => 'required|integer|min:3|max:25',
            'gender' => 'required|in:Male,Female',
            'national_id' => 'required|string|unique:students,national_id',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $studentNumber = 'ST' . date('Y') . mt_rand(1000, 9999);

            $student = Student::create([
                'student_number' => $studentNumber,
                'name' => $request->name,
                'surname' => $request->surname,
                'gender' => $request->gender,
                'age' => $request->age,
                'national_id' => $request->national_id,
                'phone' => $request->phone,
                'emergency_contact' => $request->emergency_contact,
                'address' => $request->address,
                'grade' => $request->grade,
                'term_id' => $request->term_id,
                'status' => 'active',
            ]);

            User::create([
                'name' => $request->name . ' ' . $request->surname,
                'email' => strtolower($studentNumber) . '@school.com',
                'password' => Hash::make($request->national_id),
                'role' => 'student',
                'student_id' => $student->id,
            ]);

            DB::commit();
            return redirect()->route('receptionist.students.index', ['term_id' => $request->term_id])
                             ->with('success', "Student $studentNumber registered successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

}
