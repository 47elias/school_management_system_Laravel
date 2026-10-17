<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Term;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Services\ExamCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    protected ExamCalculationService $calculationService;

    /**
     * Inject ExamCalculationService via Dependency Injection
     */
    public function __construct(ExamCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * STUDENT VIEW: Display personal exam results using dynamic grading service
     */
    public function studentResults(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        $allTerms = Term::orderBy('id', 'desc')->get();

        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $activeTerm = Term::find($selectedTermId);
        } else {
            $activeTerm = Term::where('is_current', 1)->first() ?? $allTerms->first();
        }

        if (!$activeTerm) {
            return back()->with('error', 'No academic terms found in the system.');
        }

        // 1. Fetch raw marks for current term
        $raw_current_results = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', $activeTerm->id);
            })
            ->with(['exam.subject', 'exam.term'])
            ->get();

        // 2. Aggregate current term results & resolve dynamic grades
        $current_results = $this->calculationService->aggregateMarksBySubject($raw_current_results);

        // 3. Calculate overall student average across all subjects
        $average = $current_results->count() > 0 ? round($current_results->avg('average_score'), 2) : 0;
        $overallGrade = $this->calculationService->calculateGrade($average);

        // 4. Fetch and aggregate historical term results
        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', '!=', $activeTerm->id);
            })
            ->with(['exam.term', 'exam.subject'])
            ->get()
            ->groupBy(fn($item) => $item->exam->term->term_name ?? 'Archive')
            ->map(function ($termMarks) {
                return $this->calculationService->aggregateMarksBySubject($termMarks);
            });

        return view('exams.student_index', compact(
            'student', 'current_results', 'history', 'average', 'overallGrade', 'activeTerm', 'allTerms'
        ));
    }

    /**
     * ADMIN/TEACHER VIEW: Generate a final averaged report card with dynamic grades
     */
    public function generateTermReport($student_id, $term_id)
    {
        $student = Student::findOrFail($student_id);
        $term = Term::findOrFail($term_id);

        $raw_marks = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($term) {
                $query->where('term_id', $term->id);
            })
            ->with(['exam.subject'])
            ->get();

        // Delegate aggregation to dynamic ExamCalculationService
        $finalGrades = $this->calculationService->aggregateMarksBySubject($raw_marks);
        $overallAverage = $finalGrades->count() > 0 ? round($finalGrades->avg('average_score'), 2) : 0;
        $overallGrade = $this->calculationService->calculateGrade($overallAverage);

        return view('exams.student_report', compact('student', 'term', 'finalGrades', 'overallAverage', 'overallGrade'));
    }

    /**
     * ADMIN VIEW: Exam Index
     */
    public function index(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first();

        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $selectedTerm = Term::find($selectedTermId);
        } else {
            $selectedTerm = $activeTerm ?? $terms->first();
        }

        $exams = Exam::with(['subject', 'term'])
            ->when($selectedTerm, function ($q) use ($selectedTerm) {
                $q->where('term_id', $selectedTerm->id);
            })
            ->latest()
            ->get();

        $subjects = Subject::orderBy('subject_name')->get();

        $grades = Student::distinct()
                    ->whereNotNull('grade')
                    ->orderBy('grade', 'asc')
                    ->pluck('grade');

        return view('exams.index', compact('exams', 'subjects', 'terms', 'grades', 'selectedTerm', 'activeTerm'));
    }

    /**
     * ADMIN STORE: Schedule an exam paper
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name'  => 'required|string|max:255',
            'term_id'    => 'required|exists:terms,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'nullable|exists:school_classes,id',
            'exam_date'  => 'required|date'
        ]);

        Exam::create($validated);
        return back()->with('success', 'Exam scheduled successfully!');
    }

    /**
     * ADMIN DESTROY: Delete exam and associated marks
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        DB::beginTransaction();
        try {
            Mark::where('exam_id', $exam->id)->delete();
            $exam->delete();
            DB::commit();
            return back()->with('success', 'Exam schedule and associated marks deleted.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting exam: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN MARK ENTRY VIEW
     */
    public function createMarks($exam_id, $grade)
    {
        $exam = Exam::with(['subject', 'term'])->findOrFail($exam_id);
        $students = Student::where('grade', $grade)->orderBy('surname')->get();

        $marks = Mark::where('exam_id', $exam_id)->get()->keyBy('student_id');

        return view('exams.enter_marks', compact('exam', 'students', 'grade', 'marks'));
    }

    /**
     * ADMIN BULK STORE
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks'   => 'required|array',
        ]);

        $exam = Exam::with('subject')->findOrFail($request->exam_id);

        DB::beginTransaction();
        try {
            foreach ($request->marks as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '') {
                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject'         => $exam->subject->subject_name,
                            'score'           => $data['score'],
                            'max_score'       => $data['max_score'] ?? 100,
                            'teacher_comment' => $data['comment'] ?? null
                        ]
                    );
                }
            }
            DB::commit();
            return redirect()->route('exams.index')->with('success', 'Marks recorded successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to save marks: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN REPORT (Individual Exam Paper)
     */
    public function examReport($exam_id, $grade)
    {
        $exam = Exam::with(['subject', 'term'])->findOrFail($exam_id);
        $students = Student::where('grade', $grade)
            ->with(['marks' => function($query) use ($exam_id) {
                $query->where('exam_id', $exam_id);
            }])
            ->orderBy('surname', 'asc')
            ->get();

        return view('exams.report', compact('exam', 'students', 'grade'));
    }

    /**
     * TEACHER PORTAL: Manage Marks
     */
    public function teacherManageMarks($id)
    {
        $exam = Exam::with(['subject', 'term', 'schoolClass'])->findOrFail($id);
        $resolvedClassId = $exam->schoolClass->id ?? null;

        if (!$resolvedClassId) {
            return back()->with('error', 'Critical Error: Exam class link not found.');
        }

        $isAssigned = SubjectAssignment::where('teacher_id', Auth::id())
            ->where('subject_id', $exam->subject_id)
            ->where('class_id', $resolvedClassId)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized access to this exam.');
        }

        $students = Student::where('class_id', $resolvedClassId)->orderBy('surname')->get();
        $marks = Mark::where('exam_id', $id)->get()->keyBy('student_id');

        return view('teachers.exams.record_marks', compact('exam', 'students', 'marks'));
    }

    /**
     * TEACHER BULK STORE
     */
    public function teacherBulkStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks'   => 'required|array',
        ]);

        $exam = Exam::with('subject')->findOrFail($request->exam_id);

        DB::beginTransaction();
        try {
            foreach ($request->marks as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '') {
                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject'         => $exam->subject->subject_name,
                            'score'           => $data['score'],
                            'max_score'       => $data['max_score'] ?? 100,
                            'teacher_comment' => $data['comment'] ?? null
                        ]
                    );
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Exam marks saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * TEACHER DESTROY
     */
    public function teacherDestroy($id)
    {
        $exam = Exam::findOrFail($id);

        $isOwner = SubjectAssignment::where('teacher_id', Auth::id())
            ->where('subject_id', $exam->subject_id)
            ->exists();

        if (!$isOwner) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            Mark::where('exam_id', $exam->id)->delete();
            $exam->delete();
            DB::commit();
            return back()->with('success', 'Exam and marks deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}