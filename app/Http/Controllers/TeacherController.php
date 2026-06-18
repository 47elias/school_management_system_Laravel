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
use Illuminate\Http\Request;
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

        // 1. Form Teacher Logic (The class they are in charge of)
        $myClass = SchoolClass::where('teacher_id', $teacher->id)->first();

        if ($myClass) {
            // Fetch all students registered under this class to match the complete list view
            $classStudents = Student::where('class_id', $myClass->id)->get();
            // This guarantees the total count exactly matches the students in this form class
            $studentCount = $classStudents->count();
        } else {
            $classStudents = collect();
            $studentCount = 0;
        }

        // 2. Subject Teacher Logic (Teaching Load)
        $assignedSubjects = SubjectAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        // 3. General Stats
        $mySubjectIds = $assignedSubjects->pluck('subject_id');

        $examCount = Exam::whereIn('subject_id', $mySubjectIds)
            ->where('exam_date', '>=', now()->format('Y-m-d'))
            ->count();

        $recentExams = Exam::with('subject')
            ->whereIn('subject_id', $mySubjectIds)
            ->latest()
            ->take(3)
            ->get();

        return view('teachers.dashboard', compact(
            'myClass',
            'classStudents',
            'studentCount',
            'assignedSubjects',
            'examCount',
            'recentExams'
        ));
    }

    /**
     * View all students in the teacher's "Form Class".
     */
    public function students()
    {
        $teacher = Auth::user();
        $myClass = SchoolClass::where('teacher_id', $teacher->id)->first();

        if (!$myClass) {
            return view('teachers.students.index', ['students' => collect(), 'myClass' => null]);
        }

        $students = Student::where('class_id', $myClass->id)
            ->orderBy('surname', 'asc')
            ->get();

        return view('teachers.students.index', compact('students', 'myClass'));
    }

    /**
     * View academic load and subject assignments.
     */
    public function assignedSubjects()
    {
        $assignments = SubjectAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', Auth::id())
            ->get();

        return view('teachers.my_subjects', compact('assignments'));
    }

    /**
     * --- EXAM MANAGEMENT METHODS ---
     */
    public function examIndex(Request $request)
    {
        // 1. Term Context Logic
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first();

        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $selectedTerm = Term::find($selectedTermId);
        } else {
            $selectedTerm = $activeTerm ?? $terms->first();
        }

        $mySubjectIds = SubjectAssignment::where('teacher_id', Auth::id())->pluck('subject_id');

        $exams = Exam::with(['subject', 'schoolClass', 'term'])
            ->withCount('marks')
            ->whereIn('subject_id', $mySubjectIds)
            ->where('term_id', $selectedTerm->id)
            ->orderBy('exam_date', 'desc')
            ->get();

        return view('teachers.exams.index', compact('exams', 'selectedTerm', 'terms', 'activeTerm'));
    }

    /**
     * Create Exam form view with Term Switcher logic.
     */
    public function examCreate(Request $request)
    {
        // Get term context for the banner
        $activeTerm = Term::where('is_current', 1)->first();
        $selectedTermId = $request->get('term_id');

        if ($selectedTermId) {
            $selectedTerm = Term::find($selectedTermId);
        } else {
            $selectedTerm = $activeTerm;
        }

        $myAssignments = SubjectAssignment::with(['subject', 'schoolClass'])
            ->where('teacher_id', Auth::id())
            ->get();

        return view('teachers.exams.create', compact('myAssignments', 'selectedTerm', 'activeTerm'));
    }

    /**
     * Store newly scheduled exam details.
     */
    public function examStore(Request $request)
    {
        $request->validate([
            'subject_assignment_id' => 'required|exists:subject_assignments,id',
            'term_id' => 'required|exists:terms,id',
            'exam_name' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'max_marks' => 'required|numeric|min:1',
        ]);

        $assignment = SubjectAssignment::where('id', $request->subject_assignment_id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        Exam::create([
            'exam_name' => $request->exam_name,
            'term_id' => $request->term_id,
            'subject_id' => $assignment->subject_id,
            'class_id' => $assignment->class_id,
            'exam_date' => $request->exam_date,
            'max_marks' => $request->max_marks,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.exams.index')->with('success', 'Exam scheduled successfully!');
    }

    /**
     * Manage Marks (record_marks.blade.php)
     */
    public function manageMarks($id)
    {
        $exam = Exam::with(['subject', 'schoolClass', 'term'])->findOrFail($id);

        $isAuthorized = SubjectAssignment::where('teacher_id', Auth::id())
            ->where('subject_id', $exam->subject_id)
            ->where('class_id', $exam->class_id)
            ->exists();

        if (!$isAuthorized && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this exam.');
        }

        $students = Student::where('class_id', $exam->class_id)
            ->orderBy('surname', 'asc')
            ->get();

        $marks = Mark::where('exam_id', $exam->id)
            ->get()
            ->keyBy('student_id');

        return view('teachers.record_marks', compact('exam', 'students', 'marks'));
    }

    /**
     * Bulk store or update student scores.
     */
    public function storeMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks' => 'required|array',
            'marks.*.score' => 'nullable|numeric|min:0|max:100',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        $isAuthorized = SubjectAssignment::where('teacher_id', Auth::id())
            ->where('subject_id', $exam->subject_id)
            ->where('class_id', $exam->class_id)
            ->exists();

        if (!$isAuthorized && Auth::user()->role !== 'admin') {
            abort(403);
        }

        DB::transaction(function () use ($request, $exam) {
            foreach ($request->marks as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '') {
                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject' => $exam->subject->subject_name,
                            'score' => $data['score'],
                            'max_score' => $exam->max_marks ?? 100,
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Marks recorded successfully!');
    }

    /**
     * Teacher personal account settings view.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('teachers.profile', compact('user'));
    }

    /**
     * Update teacher account registration details.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * --- ADMIN ACCESSIBLE STAFF ERP RECORDS ---
     */
    public function index()
    {
        $teachers = User::whereIn('role', ['teacher', 'admin', 'receptionist'])->latest()->get();
        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'national_id' => 'required|unique:users,national_id',
            'dob' => 'required|date',
            'ec_number' => 'required|unique:users,ec_number',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,teacher,receptionist',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => $request->national_id,
            'dob' => $request->dob,
            'phone_number' => $request->phone_number,
            'ec_number' => $request->ec_number,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Staff account created successfully!');
    }

    public function edit($id)
    {
        $staff = User::findOrFail($id);
        return view('teachers.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,teacher,receptionist',
            'national_id' => 'required|unique:users,national_id,' . $user->id,
            'dob' => 'required|date',
            'ec_number' => 'required|unique:users,ec_number,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->national_id = $request->national_id;
        $user->dob = $request->dob;
        $user->ec_number = $request->ec_number;
        $user->phone_number = $request->phone_number;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('teachers.index')->with('success', 'Staff updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'Staff member deleted successfully.');
    }
}
