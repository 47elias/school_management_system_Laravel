<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\GradeLevel;
use App\Models\Payment;
use App\Models\FeeStructure;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display Directory / Student Management
     * GET: /students or /students/manage
     */
    public function index()
    {
        $students = Student::with(['term', 'schoolClass', 'schoolClass.gradeLevel'])
            ->orderBy('surname', 'asc')
            ->get();

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
        // 1. Grade Stream / Level Statistics
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
        $gradeLevels = class_exists(GradeLevel::class) 
            ? GradeLevel::with('schoolClasses.students')->orderBy('level_order', 'asc')->get() 
            : collect();
        $currentTerm = Term::where('is_current', true)->first() ?? Term::latest()->first();
        $terms = Term::orderBy('id', 'desc')->get();

        return view('students.create', compact('classes', 'gradeLevels', 'currentTerm', 'terms'));
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
            'grade'             => 'nullable|string',
            'class_id'          => 'nullable|exists:school_classes,id',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            return DB::transaction(function () use ($validatedData) {
                // Resolve Class & Grade Assignment
                $schoolClass = null;
                if (!empty($validatedData['class_id'])) {
                    $schoolClass = SchoolClass::find($validatedData['class_id']);
                } elseif (!empty($validatedData['grade'])) {
                    $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();
                }

                // Generate Unique Student Number (e.g., STU20260001)
                $studentCount = Student::count() + 1;
                $prefix = env('SCHOOL_ACRONYM', 'STU');
                $studentNumber = $prefix . date('Y') . str_pad($studentCount, 4, '0', STR_PAD_LEFT);

                // Generate default portal email address
                $generatedEmail = strtolower(substr($validatedData['name'], 0, 1) . $validatedData['surname']) . $studentCount . '@student.local';

                $studentData = $validatedData;
                $studentData['class_id'] = $schoolClass ? $schoolClass->id : null;
                $studentData['grade'] = $schoolClass ? $schoolClass->class_name : ($validatedData['grade'] ?? 'Unassigned');
                $studentData['status'] = 'active';
                $studentData['enrollment_date'] = now();
                $studentData['parent_contact'] = $validatedData['emergency_contact'] ?? $validatedData['phone'];
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
        $gradeLevels = class_exists(GradeLevel::class) 
            ? GradeLevel::with('schoolClasses')->orderBy('level_order', 'asc')->get() 
            : collect();
        $terms = Term::orderBy('id', 'desc')->get();

        return view('students.edit', compact('student', 'classes', 'gradeLevels', 'terms'));
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
            'grade'             => 'nullable|string',
            'class_id'          => 'nullable|exists:school_classes,id',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'status'            => 'required|in:active,inactive',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            DB::transaction(function () use ($student, $validatedData, $oldNationalId) {
                $schoolClass = null;
                if (!empty($validatedData['class_id'])) {
                    $schoolClass = SchoolClass::find($validatedData['class_id']);
                } elseif (!empty($validatedData['grade'])) {
                    $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();
                }

                $updateData = $validatedData;
                $updateData['class_id'] = $schoolClass ? $schoolClass->id : $student->class_id;
                $updateData['grade']    = $schoolClass ? $schoolClass->class_name : ($validatedData['grade'] ?? $student->grade);

                $updateData['parent_contact'] = $validatedData['emergency_contact'] ?? $validatedData['phone'];
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
     * Show Grade-Based Student Promotion Form
     * GET: /students/promote
     */
    public function showPromotionForm()
    {
        $gradeLevels = class_exists(GradeLevel::class)
            ? GradeLevel::with(['schoolClasses.students', 'students'])->orderBy('level_order', 'asc')->get()
            : collect();

        $classes = SchoolClass::orderBy('class_name', 'asc')->get();
        $terms   = Term::orderBy('id', 'desc')->get();

        return view('students.promote', compact('gradeLevels', 'classes', 'terms'));
    }

    /**
     * Alias for showPromotionForm
     * GET: /students/promote
     */
    public function promote()
    {
        return $this->showPromotionForm();
    }

    /**
     * Process Grade Level Mass Promotion Matrix Form (Prevents Cascading Updates)
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
        $totalPromoted = 0;
        $totalGraduated = 0;

        try {
            DB::transaction(function () use ($promotions, $targetTermId, &$totalPromoted, &$totalGraduated) {
                // Registry to track student IDs already promoted during this pass to prevent single-turn cascades
                $processedStudentIds = [];

                // Sort Grade Levels in DESCENDING order (highest level_order down to lowest)
                $gradeLevelIds = array_keys($promotions);
                $orderedGradeLevels = class_exists(GradeLevel::class)
                    ? GradeLevel::whereIn('id', $gradeLevelIds)->orderBy('level_order', 'desc')->get()->keyBy('id')
                    : collect();

                $promotionKeys = $orderedGradeLevels->count() > 0 
                    ? $orderedGradeLevels->keys()->toArray() 
                    : array_keys($promotions);

                foreach ($promotionKeys as $id) {
                    if (!isset($promotions[$id])) {
                        continue;
                    }

                    $item = $promotions[$id];

                    if (empty($item['active'])) {
                        continue;
                    }

                    // 1. Grade Level Mode
                    if (isset($item['from_grade_level_id']) || isset($item['to_grade_level_id'])) {
                        $fromGradeLevelId = $item['from_grade_level_id'] ?? $id;
                        $toDestination = $item['to_grade_level_id'] ?? null;

                        if (empty($toDestination)) {
                            continue;
                        }

                        $fromGradeLevel = GradeLevel::with('schoolClasses')->find($fromGradeLevelId);
                        if (!$fromGradeLevel) {
                            continue;
                        }

                        if ($toDestination === 'Graduated') {
                            $classIds = $fromGradeLevel->schoolClasses->pluck('id')->toArray();
                            
                            $studentsToGraduate = Student::where(function($q) use ($classIds, $fromGradeLevel) {
                                    if (!empty($classIds)) {
                                        $q->whereIn('class_id', $classIds);
                                    }
                                    $q->orWhere('grade', 'LIKE', $fromGradeLevel->name . '%');
                                })
                                ->where('status', 'active')
                                ->whereNotIn('id', $processedStudentIds)
                                ->pluck('id');

                            if ($studentsToGraduate->isNotEmpty()) {
                                $processedStudentIds = array_merge($processedStudentIds, $studentsToGraduate->toArray());

                                $affected = Student::whereIn('id', $studentsToGraduate)
                                    ->update([
                                        'status'     => 'inactive',
                                        'grade'      => 'Graduated',
                                        'class_id'   => null,
                                        'updated_at' => now(),
                                    ]);

                                $totalGraduated += $affected;
                            }
                        } else {
                            $targetGradeLevel = GradeLevel::with('schoolClasses')->find($toDestination);
                            if ($targetGradeLevel) {
                                $fromClasses = $fromGradeLevel->schoolClasses;

                                if ($fromClasses->count() > 0) {
                                    foreach ($fromClasses as $fromClass) {
                                        // Match corresponding stream (e.g. 1A -> 2A) or fall back to primary stream in target grade
                                        $targetClass = $targetGradeLevel->schoolClasses->firstWhere('stream', $fromClass->stream)
                                                    ?? $targetGradeLevel->schoolClasses->first();

                                        if ($targetClass) {
                                            $studentsToPromote = Student::where('class_id', $fromClass->id)
                                                ->where('status', 'active')
                                                ->whereNotIn('id', $processedStudentIds)
                                                ->pluck('id');

                                            if ($studentsToPromote->isNotEmpty()) {
                                                $processedStudentIds = array_merge($processedStudentIds, $studentsToPromote->toArray());

                                                $affected = Student::whereIn('id', $studentsToPromote)
                                                    ->update([
                                                        'class_id'   => $targetClass->id,
                                                        'grade'      => $targetClass->class_name,
                                                        'term_id'    => $targetTermId,
                                                        'updated_at' => now(),
                                                    ]);

                                                $totalPromoted += $affected;
                                            }
                                        }
                                    }
                                } else {
                                    // Fallback when students are assigned directly by grade name string
                                    $targetClass = $targetGradeLevel->schoolClasses->first();
                                    $studentsToPromote = Student::where('grade', 'LIKE', $fromGradeLevel->name . '%')
                                        ->where('status', 'active')
                                        ->whereNotIn('id', $processedStudentIds)
                                        ->pluck('id');

                                    if ($studentsToPromote->isNotEmpty()) {
                                        $processedStudentIds = array_merge($processedStudentIds, $studentsToPromote->toArray());

                                        $affected = Student::whereIn('id', $studentsToPromote)
                                            ->update([
                                                'grade'      => $targetGradeLevel->name,
                                                'class_id'   => $targetClass ? $targetClass->id : null,
                                                'term_id'    => $targetTermId,
                                                'updated_at' => now(),
                                            ]);

                                        $totalPromoted += $affected;
                                    }
                                }
                            }
                        }
                    } 
                    // 2. Legacy Grade String Mode
                    elseif (isset($item['from_grade']) && isset($item['to_grade'])) {
                        $fromGrade = $item['from_grade'];
                        $toGrade = $item['to_grade'];

                        if (empty($toGrade) || ($fromGrade === $toGrade && $toGrade !== 'Graduated')) {
                            continue;
                        }

                        $isGraduated = ($toGrade === 'Graduated');
                        $destinationClass = $isGraduated ? null : SchoolClass::where('class_name', $toGrade)->first();

                        $studentsToTransition = Student::where('grade', $fromGrade)
                            ->where('status', 'active')
                            ->whereNotIn('id', $processedStudentIds)
                            ->pluck('id');

                        if ($studentsToTransition->isNotEmpty()) {
                            $processedStudentIds = array_merge($processedStudentIds, $studentsToTransition->toArray());

                            $affected = Student::whereIn('id', $studentsToTransition)
                                ->update([
                                    'term_id'    => $targetTermId,
                                    'status'     => $isGraduated ? 'inactive' : 'active',
                                    'grade'      => $isGraduated ? 'Graduated' : $toGrade,
                                    'class_id'   => $destinationClass ? $destinationClass->id : null,
                                    'updated_at' => now(),
                                ]);

                            if ($isGraduated) {
                                $totalGraduated += $affected;
                            } else {
                                $totalPromoted += $affected;
                            }
                        }
                    }
                }
            });

            return redirect()->route('students.index')
                ->with('success', "Mass promotion complete! {$totalPromoted} student(s) promoted, {$totalGraduated} student(s) marked as Graduated.");
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

        $allTerms = Term::orderBy('id', 'desc')->get();
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
        $student = Student::with(['payments', 'term', 'schoolClass', 'feeStructures'])
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
        $billingDates = method_exists($term, 'getBillingMonthDates') ? $term->getBillingMonthDates() : [];
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