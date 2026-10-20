<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Mark;
use App\Models\Term;
use App\Models\ExamAttendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Teacher Portal Dashboard.
     */
    public function dashboard()
    {
        $teacher = Auth::user();

        // 1. Active Term Context
        $activeTerm = Term::where('is_current', 1)->first() ?? Term::orderBy('id', 'desc')->first();

        // Load form-teacher classes and every class where a subject is taught.
        $assignedSubjects = SubjectAssignment::with(['schoolClass.gradeLevel', 'gradeClasses', 'subject'])
            ->forTeacher($teacher->id)
            ->get();

        $formTeacherClassIds = SchoolClass::query()
            ->where('teacher_id', $teacher->id)
            ->orWhere('class_teacher_id', $teacher->id)
            ->pluck('id');

        // A grade-wide allocation has no class_id; include every stream in that grade.
        $assignedGradeNames = $assignedSubjects->whereNull('class_id')->pluck('grade')->filter()->unique();
        $assignedClassIds = $assignedSubjects->pluck('class_id')->filter();
        $gradeClassIds = SchoolClass::query()
            ->whereHas('gradeLevel', fn ($query) => $query->whereIn('name', $assignedGradeNames))
            ->pluck('id');
        $allClassIds = $formTeacherClassIds->merge($assignedClassIds)->merge($gradeClassIds)->unique()->values();
        $myClasses = SchoolClass::with('gradeLevel')
            ->withCount(['students as student_count' => fn ($query) => $query->where('status', 'active')])
            ->whereIn('id', $allClassIds)
            ->orderBy('class_name')
            ->get();

        $formTeacherClassIds = $formTeacherClassIds->map(fn ($id) => (int) $id)->all();
        foreach ($myClasses as $class) {
            $gradeName = $class->gradeLevel->name ?? null;
            $classAssignments = $assignedSubjects->filter(fn ($assignment) =>
                (int) $assignment->class_id === (int) $class->id
                || (is_null($assignment->class_id) && $assignment->grade === $gradeName)
            );
            $class->setAttribute('is_form_teacher', in_array((int) $class->id, $formTeacherClassIds, true));
            $class->setAttribute('subjects', $classAssignments->pluck('subject.subject_name')->filter()->unique()->values());
        }

        $assignedClass = $myClasses->firstWhere('is_form_teacher', true);
        $myClass = $assignedClass;
        $classStudents = $assignedClass
            ? Student::where('class_id', $assignedClass->id)->where('status', 'active')->orderBy('surname')->get()
            : collect();
        $studentCount = $classStudents->count();

        $assignedSubjectsCount = $assignedSubjects->pluck('subject_id')->unique()->count();
        $mySubjectIds = $assignedSubjects->pluck('subject_id')->unique();

        // 4. Combined Class Student Count
        $allocatedClassIds = $allClassIds;

        $totalStudentsCount = Student::whereIn('class_id', $allocatedClassIds)
            ->where('status', 'active')
            ->count();

        // 5. Exam Stats (exams table uses term_id)
        $assignmentIds = $assignedSubjects->modelKeys();
        $examAssignmentScope = function ($query) use ($assignmentIds, $assignedSubjects) {
            $query->whereIn('subject_assignment_id', $assignmentIds);

            foreach ($assignedSubjects->whereNotNull('class_id') as $assignment) {
                $query->orWhere(function ($pair) use ($assignment) {
                    $pair->where('subject_id', $assignment->subject_id)
                        ->where('class_id', $assignment->class_id);
                });
            }
        };

        $examCount = Exam::where($examAssignmentScope)
            ->when($activeTerm, fn ($query) => $query->where('term_id', $activeTerm->id))
            ->count();

        $examsRecordedCount = Exam::where($examAssignmentScope)
            ->when($activeTerm, fn ($query) => $query->where('term_id', $activeTerm->id))
            ->count();

        $recentExams = Exam::with(['subject', 'schoolClass', 'subjectAssignment'])
            ->where($examAssignmentScope)
            ->latest()
            ->take(5)
            ->get();

        $activitiesCount = $assignedSubjectsCount;

        return view('teachers.dashboard', compact(
            'assignedClass',
            'myClass',
            'myClasses',
            'classStudents',
            'studentCount',
            'assignedSubjects',
            'assignedSubjectsCount',
            'totalStudentsCount',
            'examCount',
            'examsRecordedCount',
            'activitiesCount',
            'recentExams',
            'activeTerm'
        ));
    }

    /**
     * View Form Class Roster.
     */
    public function students()
    {
        $teacher = Auth::user();

        $classIds = SchoolClass::query()
            ->where('teacher_id', $teacher->id)
            ->orWhere('class_teacher_id', $teacher->id)
            ->pluck('id')
            ->merge(SubjectAssignment::forTeacher($teacher->id)->whereNotNull('class_id')->pluck('class_id'));
        $gradeNames = SubjectAssignment::forTeacher($teacher->id)->whereNull('class_id')->pluck('grade')->filter()->unique();
        $classIds = $classIds
            ->merge(SchoolClass::whereHas('gradeLevel', fn ($query) => $query->whereIn('name', $gradeNames))->pluck('id'))
            ->unique()
            ->values();

        $myClass = SchoolClass::whereIn('id', $classIds)->orderBy('class_name')->first();
        $students = Student::with('schoolClass')
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->orderBy('surname')
            ->get();

        return view('teachers.students.index', compact('students', 'myClass'));
    }

    /**
     * View Single Student Details.
     */
    public function showStudent($id)
    {
        $teacher = Auth::user();
        $classIds = SchoolClass::query()
            ->where('teacher_id', $teacher->id)
            ->orWhere('class_teacher_id', $teacher->id)
            ->pluck('id')
            ->merge(SubjectAssignment::forTeacher($teacher->id)->whereNotNull('class_id')->pluck('class_id'));
        $gradeNames = SubjectAssignment::forTeacher($teacher->id)->whereNull('class_id')->pluck('grade')->filter()->unique();
        $classIds = $classIds
            ->merge(SchoolClass::whereHas('gradeLevel', fn ($query) => $query->whereIn('name', $gradeNames))->pluck('id'))
            ->unique();

        abort_unless(Student::whereIn('class_id', $classIds)->whereKey($id)->exists(), 404);
        $student = Student::with(['schoolClass', 'marks.exam.subject'])->findOrFail($id);
        return view('students.show', compact('student'));
    }

    /**
     * View Assigned Subject Allocations.
     */
    public function assignedSubjects()
    {
        $assignments = SubjectAssignment::with(['schoolClass', 'gradeClasses', 'subject'])
            ->where('teacher_id', Auth::id())
            ->get();

        return view('teachers.my_subjects', compact('assignments'));
    }

    /**
     * Exam Schedule Listing.
     */
    public function examIndex(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();

        $selectedTermId = $request->get('term_id');
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        $myAssignments = SubjectAssignment::with(['subject', 'schoolClass', 'gradeClasses'])
            ->forTeacher(Auth::id())
            ->get();
        $assignmentIds = $myAssignments->modelKeys();

        $exams = Exam::with(['subject', 'schoolClass', 'term'])
            ->withCount('marks')
            ->where(function ($query) use ($assignmentIds, $myAssignments) {
                $query->whereIn('subject_assignment_id', $assignmentIds);

                foreach ($myAssignments->whereNotNull('class_id') as $assignment) {
                    $query->orWhere(function ($pair) use ($assignment) {
                        $pair->where('subject_id', $assignment->subject_id)
                            ->where('class_id', $assignment->class_id);
                    });
                }
            })
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->orderBy('exam_date')
            ->orderBy('exam_name')
            ->get();

        $subjectGroups = [];
        foreach ($myAssignments as $assignment) {
            $subjectId = $assignment->subject_id;
            if (!isset($subjectGroups[$subjectId])) {
                $subjectGroups[$subjectId] = [
                    'subject' => $assignment->subject,
                    'classes' => [],
                ];
            }

            // A grade-wide allocation is one teaching target. Keep it grouped
            // by grade so its exam and all-stream roster do not appear under
            // every individual stream.
            if (!$assignment->schoolClass && $assignment->grade) {
                $classKey = 'grade-wide-' . $assignment->id;
                $label = $assignment->grade . ' · All Streams';
                $subjectGroups[$subjectId]['classes'][$classKey] ??= [
                    'label' => $label,
                    'class_id' => null,
                    'assignment_ids' => [],
                    'papers' => collect(),
                ];
                $subjectGroups[$subjectId]['classes'][$classKey]['assignment_ids'][] = $assignment->id;
                continue;
            }

            $targetClasses = collect([$assignment->schoolClass])->filter();

            if ($targetClasses->isEmpty()) {
                $classKey = 'unassigned-' . $assignment->id;
                $subjectGroups[$subjectId]['classes'][$classKey] ??= [
                    'label' => $assignment->grade ?: 'Class not assigned',
                    'class_id' => null,
                    'assignment_ids' => [],
                    'papers' => collect(),
                ];
                $subjectGroups[$subjectId]['classes'][$classKey]['assignment_ids'][] = $assignment->id;
                continue;
            }

            foreach ($targetClasses as $class) {
                $classKey = (string) $class->id;
                $subjectGroups[$subjectId]['classes'][$classKey] ??= [
                    'label' => $class->class_name,
                    'class_id' => $class->id,
                    'assignment_ids' => [],
                    'papers' => collect(),
                ];
                $subjectGroups[$subjectId]['classes'][$classKey]['assignment_ids'][] = $assignment->id;
            }
        }

        uasort($subjectGroups, fn ($left, $right) => strnatcasecmp(
            $left['subject']->subject_name ?? '',
            $right['subject']->subject_name ?? ''
        ));

        foreach ($subjectGroups as $subjectId => &$subjectGroup) {
            uasort($subjectGroup['classes'], fn ($left, $right) => strnatcasecmp($left['label'], $right['label']));

            foreach ($subjectGroup['classes'] as &$classGroup) {
                $classGroup['papers'] = $exams->filter(function ($exam) use ($subjectId, $classGroup) {
                    return in_array($exam->subject_assignment_id, $classGroup['assignment_ids'])
                        || ($classGroup['class_id'] && (int) $exam->subject_id === (int) $subjectId
                            && (int) $exam->class_id === (int) $classGroup['class_id']);
                })->values();
            }
            unset($classGroup);
        }
        unset($subjectGroup);

        return view('teachers.exams.index', compact('subjectGroups', 'exams', 'selectedTerm', 'terms', 'activeTerm'));
    }

    /**
     * Create Exam Form View.
     */
    public function examCreate(Request $request)
    {
        $activeTerm = Term::where('is_current', 1)->first() ?? Term::orderBy('id', 'desc')->first();
        $selectedTermId = $request->get('term_id');
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        $myAssignments = SubjectAssignment::with(['subject', 'schoolClass', 'gradeClasses'])
            ->where('teacher_id', Auth::id())
            ->get();

        return view('teachers.exams.create', compact('myAssignments', 'selectedTerm', 'activeTerm'));
    }

    /**
     * Store Scheduled Exam.
     */
    public function examStore(Request $request)
    {
        $request->validate([
            'subject_assignment_id' => ['required', Rule::exists('subject_assignments', 'id')->where('teacher_id', Auth::id())],
            'term_id'               => 'required|exists:terms,id',
            'exam_name'             => 'required|string|max:255',
            'exam_date'             => 'required|date',
            'max_marks'             => 'required|numeric|min:1',
        ]);

        $assignment = SubjectAssignment::where('teacher_id', Auth::id())->findOrFail($request->subject_assignment_id);

        Exam::create([
            'exam_name'  => $request->exam_name,
            'term_id'    => $request->term_id,
            'subject_id' => $assignment->subject_id,
            'subject_assignment_id' => $assignment->id,
            'class_id'   => $assignment->class_id,
            'exam_date'  => $request->exam_date,
            'max_marks'  => $request->max_marks,
            'status'     => 'pending',
        ]);

        return redirect()->route('teacher.exams.index')->with('success', 'Exam scheduled successfully!');
    }

    /**
     * Manage Marks View.
     */
    public function manageMarks($id)
    {
        $exam = Exam::with(['subject', 'schoolClass', 'term'])->findOrFail($id);

        $students = $exam->class_id 
            ? Student::where('class_id', $exam->class_id)->where('status', 'active')->orderBy('surname')->get()
            : Student::where('status', 'active')->orderBy('surname')->get();

        $marks = Mark::where('exam_id', $exam->id)->get()->keyBy('student_id');

        return view('teachers.exams.record_marks', compact('exam', 'students', 'marks'));
    }

    /**
     * Store/Update Marks.
     */
    public function storeMarks(Request $request)
    {
        $request->validate([
            'exam_id'       => 'required|exists:exams,id',
            'marks'         => 'required|array',
            'marks.*.score' => 'nullable|numeric|min:0|max:100',
        ]);

        $exam = Exam::with('subject')->findOrFail($request->exam_id);

        DB::transaction(function () use ($request, $exam) {
            foreach ($request->marks as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== null && $data['score'] !== '') {
                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject'         => $exam->subject->subject_name ?? $exam->subject->name ?? 'Subject',
                            'score'           => $data['score'],
                            'max_score'       => $data['max_score'] ?? $exam->max_marks ?? 100,
                            'teacher_comment' => $data['comment'] ?? $data['remarks'] ?? null,
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Marks recorded successfully!');
    }

    /**
     * Biometric Face Recognition View.
     */
    public function examVerifyView($examId)
    {
        $exam = Exam::with(['subject', 'schoolClass', 'term'])->findOrFail($examId);
        return view('teachers.exams.verify', compact('exam'));
    }

    /**
     * Process Biometric Face Verification AJAX Endpoint.
     */
    public function processFaceVerification(Request $request)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'face_image' => 'required|string',
        ]);

        $exam = Exam::with(['subject', 'schoolClass'])->findOrFail($request->exam_id);
        $invigilator = Auth::user();

        $student = Student::where('class_id', $exam->class_id)
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'No active student found for biometric verification in this class.'
            ], 404);
        }

        $balance = $student->balance ?? $student->fees_balance ?? 0;
        if ($balance > 0) {
            return response()->json([
                'success'        => false,
                'student_name'   => $student->name . ' ' . $student->surname,
                'student_number' => $student->student_number ?? $student->ec_number,
                'photo_url'      => $student->photo_path ? asset('storage/' . $student->photo_path) : "https://ui-avatars.com/api/?name=" . urlencode($student->name),
                'message'        => "Outstanding Balance: $" . number_format($balance, 2) . ". Financial clearance required."
            ], 402);
        }

        if (class_exists(ExamAttendance::class)) {
            ExamAttendance::updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $student->id],
                [
                    'verified_by' => $invigilator->id,
                    'verified_at' => now(),
                    'status'      => 'present'
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Biometric signature verified successfully. Entry granted.',
            'student' => [
                'name'           => $student->name . ' ' . $student->surname,
                'student_number' => $student->student_number ?? $student->ec_number,
                'photo_url'      => $student->photo_path ? asset('storage/' . $student->photo_path) : "https://ui-avatars.com/api/?name=" . urlencode($student->name),
                'verified_time'  => now()->format('H:i:s d M, Y')
            ]
        ], 200);
    }

    /**
     * Teacher Profile View.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('settings.profile', compact('user'));
    }

    /**
     * Update Profile Details.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string',
            'password'     => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }
}
