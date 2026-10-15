<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\FeeStructure;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;

class StudentController extends Controller
{
    /**
     * Display Directory / Student Management
     * GET: /students or /students/manage
     */
    public function index()
    {
        $students = Student::with(['term', 'schoolClass'])->orderBy('surname', 'asc')->get();
        return view('students.manage', compact('students'));
    }

    /**
     * Display Full Student Profile
     * GET: /students/{student}
     */
    public function show(Student $student)
    {
        $student->load(['term', 'schoolClass', 'payments', 'feeStructures']);
        return view('students.show', compact('student'));
    }

    /**
     * Return Student Profile Data for AJAX Modal Preview
     * GET: /students/{id}/profile-data
     */
    public function showProfile($id)
    {
        try {
            $student = Student::with(['term', 'schoolClass'])->findOrFail($id);
            return view('students.profile_data', compact('student'));
        } catch (Exception $e) {
            return response('<div class="p-4 rounded-xl bg-rose-950/40 border border-rose-800/50 text-rose-300 text-xs font-semibold"><i class="fa-solid fa-circle-exclamation mr-2"></i> Error: Could not load student profile data.</div>', 500);
        }
    }

    /**
     * Display Enrollment Statistics
     * GET: /students/stats
     */
    public function enrollmentStats()
    {
        // 1. Class/Grade Statistics
        $classStats = Student::select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->orderBy('grade', 'asc')
            ->get();

        // 2. Gender Statistics
        $genderStats = Student::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->get();

        // 3. Term Growth (Top 5 most recent terms with student counts)
        $termStats = DB::table('students')
            ->join('terms', 'students.term_id', '=', 'terms.id')
            ->select('terms.term_name', 'terms.academic_year', DB::raw('count(students.id) as total'))
            ->groupBy('terms.id', 'terms.term_name', 'terms.academic_year')
            ->orderBy('terms.id', 'desc')
            ->limit(5)
            ->get();

        return view('students.stats', compact('classStats', 'genderStats', 'termStats'));
    }

    /**
     * Show Student Registration Form
     * GET: /students/create
     */
    public function create()
    {
        $classes = SchoolClass::orderBy('class_name', 'asc')->get();
        $currentTerm = Term::where('is_current', true)->first() ?? Term::latest()->first();
        $terms = Term::orderBy('id', 'desc')->get();

        return view('students.create', compact('classes', 'currentTerm', 'terms'));
    }

    /**
     * Store New Student Record
     * POST: /students
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'              => 'required|string|max:255',
            'surname'           => 'required|string|max:255',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|in:Male,Female',
            'national_id'       => 'required|string|unique:students,national_id',
            'grade'             => 'required|string',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            return DB::transaction(function () use ($validatedData) {
                $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();

                // Generate Unique Student Number (e.g., STU20260001)
                $studentCount = Student::count() + 1;
                $prefix = env('SCHOOL_ACRONYM', 'STU');
                $studentNumber = $prefix . date('Y') . str_pad($studentCount, 4, '0', STR_PAD_LEFT);

                // Generate default email address
                $generatedEmail = strtolower(substr($validatedData['name'], 0, 1) . $validatedData['surname']) . $studentCount . '@student.local';

                $studentData = $validatedData;
                $studentData['class_id'] = $schoolClass ? $schoolClass->id : null;
                $studentData['status'] = 'active';
                $studentData['enrollment_date'] = now();
                $studentData['parent_contact'] = $validatedData['emergency_contact'];
                $studentData['age'] = Carbon::parse($validatedData['date_of_birth'])->age;
                
                // Assign generated credentials
                $studentData['student_number'] = $studentNumber;
                $studentData['email'] = $generatedEmail;

                $student = Student::create($studentData);

                User::create([
                    'name'        => $validatedData['name'] . ' ' . $validatedData['surname'],
                    'email'       => $generatedEmail,
                    'national_id' => $validatedData['national_id'],
                    'ec_number'   => $validatedData['national_id'],
                    'password'    => Hash::make(strtolower($validatedData['surname']) . '123'),
                    'role'        => 'student',
                    'base_salary' => 0,
                ]);

                return redirect()->route('students.index')
                    ->with('success', "Registration successful: {$student->name} {$student->surname} (ID: {$studentNumber})");
            });
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    /**
     * Show Student Edit Form
     * GET: /students/{id}/edit
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classes = SchoolClass::orderBy('class_name', 'asc')->get();
        $terms   = Term::orderBy('id', 'desc')->get();
        return view('students.edit', compact('student', 'classes', 'terms'));
    }

    /**
     * Update Student Record
     * PUT/PATCH: /students/{id}
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $oldNationalId = $student->national_id;

        $validatedData = $request->validate([
            'name'              => 'required|string|max:255',
            'surname'           => 'required|string|max:255',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|in:Male,Female',
            'national_id'       => 'required|string|unique:students,national_id,' . $student->id,
            'grade'             => 'required|string',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'status'            => 'required|in:active,inactive',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            DB::transaction(function () use ($student, $validatedData, $oldNationalId) {
                $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();
                $updateData = $validatedData;
                $updateData['class_id'] = $schoolClass ? $schoolClass->id : $student->class_id;

                $updateData['parent_contact'] = $validatedData['emergency_contact'];
                $updateData['age'] = Carbon::parse($validatedData['date_of_birth'])->age;

                $student->update($updateData);

                User::where('national_id', $oldNationalId)
                    ->where('role', 'student')
                    ->update([
                        'name'        => $validatedData['name'] . ' ' . $validatedData['surname'],
                        'national_id' => $validatedData['national_id'],
                        'ec_number'   => $validatedData['national_id']
                    ]);
            });

            return redirect()->route('students.index')->with('success', 'Student profile updated successfully!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete Student Record
     * DELETE: /students/{id}
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        try {
            DB::transaction(function() use ($student) {
                Payment::where('student_id', $student->id)->delete();
                FeeStructure::where('student_id', $student->id)->delete();
                User::where('national_id', $student->national_id)->where('role', 'student')->delete();
                $student->delete();
            });

            return redirect()->route('students.index')->with('success', 'Student record deleted permanently.');
        } catch (Exception $e) {
            return redirect()->route('students.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Show Student Promotion Form
     * GET: /students/promote
     */
    public function showPromotionForm()
    {
        $classes = SchoolClass::orderBy('class_name', 'asc')->get();
        $terms = Term::orderBy('start_date', 'desc')->get();

        return view('students.promote', compact('classes', 'terms'));
    }

    /**
     * Process Legacy Single Promotion
     * POST: /students/promote
     */
    public function promote(Request $request)
    {
        $request->validate([
            'current_grade' => 'required|string',
            'next_grade'    => 'required|string',
            'next_term_id'  => 'required|exists:terms,id'
        ]);

        if ($request->current_grade === $request->next_grade) {
            return back()->with('error', "Source and destination grades cannot be the same.");
        }

        try {
            DB::transaction(function () use ($request) {
                $nextClass = SchoolClass::where('class_name', $request->next_grade)->first();

                Student::where('grade', $request->current_grade)
                    ->where('status', 'active')
                    ->update([
                        'grade'      => $request->next_grade,
                        'class_id'   => $nextClass ? $nextClass->id : null,
                        'term_id'    => $request->next_term_id,
                        'updated_at' => now()
                    ]);
            });

            return redirect()->route('students.index')->with('success', "Promotion processed successfully.");
        } catch (Exception $e) {
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    /**
     * Process Mass Promotion Matrix Form
     * POST: /students/promote/mass
     */
    public function processMassPromotion(Request $request)
    {
        $request->validate([
            'target_term_id' => 'required|exists:terms,id',
            'promote'        => 'required|array',
        ]);

        $promotions = $request->input('promote');
        $targetTermId = $request->input('target_term_id');
        $totalTransitioned = 0;

        try {
            DB::transaction(function () use ($promotions, $targetTermId, &$totalTransitioned) {
                $validMappings = [];
                foreach ($promotions as $item) {
                    if (isset($item['active']) && !empty($item['to_grade'])) {
                        $fromGrade = $item['from_grade'];
                        $toGrade = $item['to_grade'];

                        if ($fromGrade === $toGrade && $toGrade !== 'Graduated') {
                            continue;
                        }

                        $validMappings[$fromGrade] = $toGrade;
                    }
                }

                if (empty($validMappings)) {
                    throw new Exception("No valid class transitions were selected.");
                }

                $processedStudentIds = [];

                foreach ($validMappings as $fromGrade => $toGrade) {
                    $isGraduated = ($toGrade === 'Graduated');

                    $studentIds = Student::where('grade', $fromGrade)
                        ->where('status', 'active')
                        ->whereNotIn('id', $processedStudentIds)
                        ->pluck('id');

                    if ($studentIds->isEmpty()) {
                        continue;
                    }

                    $processedStudentIds = array_merge($processedStudentIds, $studentIds->toArray());

                    $updateData = [
                        'term_id' => $targetTermId,
                        'status'  => $isGraduated ? 'Alumni' : 'active',
                    ];

                    if ($isGraduated) {
                        $updateData['class_id'] = null;
                        $updateData['grade'] = 'Alumni';
                    } else {
                        $updateData['grade'] = $toGrade;
                        $destinationClass = SchoolClass::where('class_name', $toGrade)->first();
                        $updateData['class_id'] = $destinationClass ? $destinationClass->id : null;
                    }

                    $affected = Student::whereIn('id', $studentIds)->update($updateData);
                    $totalTransitioned += $affected;
                }
            });

            return redirect()->route('students.promote')
                ->with('success', "Success! {$totalTransitioned} student records have been successfully transitioned.");
        } catch (Exception $e) {
            return back()->with('error', 'Mass promotion failed: ' . $e->getMessage());
        }
    }

    /**
     * Display Academic Performance
     * GET: /students/{id}/performance
     */
    public function performance(Request $request, $id = null)
    {
        if ($id) {
            $student = Student::findOrFail($id);
        } else {
            $student = auth()->user()->student ?? auth('student')->user();
        }

        if (!$student) {
            return back()->with('error', 'Student record not found.');
        }

        $allTerms = Term::orderBy('start_date', 'desc')->get();
        $requestedTermId = $request->get('term_id');

        if ($requestedTermId) {
            $displayTerm = Term::find($requestedTermId);
        } else {
            $displayTerm = Term::where('is_current', true)->first() ?? $allTerms->first();
        }

        if (!$displayTerm) {
            return back()->with('error', 'No academic terms found.');
        }

        $termResults = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($displayTerm) {
                $query->where('term_id', $displayTerm->id);
            })
            ->with(['exam.subject'])
            ->get();

        $average = $termResults->count() > 0 ? $termResults->avg('score') : 0;

        return view('student.performance', compact('student', 'allTerms', 'displayTerm', 'termResults', 'average'));
    }

    /**
     * Display Financial Summary & Fees
     * GET: /students/{id}/financials
     */
    public function financials(Request $request, $id)
    {
        $student = Student::with(['payments', 'term', 'class', 'feeStructures'])
            ->findOrFail($id);

        $termId = $request->get('term_id');
        $term = $termId ? Term::find($termId) : (Term::where('is_current', true)->first() ?? Term::latest()->first());

        if (!$term) {
            return back()->with('error', 'No active academic term found.');
        }

        $expected = $this->getStudentExpectedTotal($student, $term->id);
        $paid = $student->payments->where('term_id', $term->id)->sum('amount_paid');
        $arrears = $this->calculateSumOfReds($student, $term, $expected, $paid);

        $student->expected_total = $expected;
        $student->monthly_installment = 50.00;
        $student->monthly_arrears = $arrears;
        $student->calculated_balance = $expected - $paid;

        $allTerms = Term::orderBy('academic_year', 'desc')->orderBy('id', 'desc')->get();

        return view('receptionist.students.show', compact('student', 'term', 'allTerms'));
    }

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
}