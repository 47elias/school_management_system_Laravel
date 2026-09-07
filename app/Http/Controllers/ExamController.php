<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Term;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    /**
     * STUDENT VIEW: Display personal exam results
     * UPDATED: Now groups by Subject and calculates the average for multiple papers
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

        // 1. Fetch raw marks for the current term
        $raw_current_results = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', $activeTerm->id);
            })
            ->with(['exam.subject', 'exam.term'])
            ->get();

        // 2. Aggregate current term results (Averages Paper 1 + Paper 2 automatically)
        $current_results = $this->aggregateMarksBySubject($raw_current_results);

        // 3. Calculate overall average based on the final aggregated subject scores
        $average = $current_results->count() > 0 ? $current_results->avg('average_score') : 0;

        // 4. Fetch and aggregate historical terms
        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', '!=', $activeTerm->id);
            })
            ->with(['exam.term', 'exam.subject'])
            ->get()
            ->groupBy(fn($item) => $item->exam->term->term_name ?? 'Archive')
            ->map(function ($termMarks) {
                return $this->aggregateMarksBySubject($termMarks);
            });

        return view('exams.student_index', compact(
            'student', 'current_results', 'history', 'average', 'activeTerm', 'allTerms'
        ));
    }

    /**
     * ADMIN/TEACHER VIEW: Generate a final averaged report card for a student
     * (You can link to this method from your Admin or Teacher views)
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

        $finalGrades = $this->aggregateMarksBySubject($raw_marks);
        $overallAverage = $finalGrades->count() > 0 ? round($finalGrades->avg('average_score'), 2) : 0;

        return view('exams.student_report', compact('student', 'term', 'finalGrades', 'overallAverage'));
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
            ->where('term_id', $selectedTerm->id)
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
     * ADMIN STORE: Schedule an exam
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name'  => 'required|string|max:255',
            'term_id'    => 'required|exists:terms,id',
            'subject_id' => 'required|exists:subjects,id',
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
                            'max_score'       => 100,
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
                            'max_score'       => 100,
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

    /* 
    |--------------------------------------------------------------------------
    | HELPER METHODS FOR AGGREGATING MULTIPLE PAPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Groups raw marks by subject and calculates the final average.
     * Automatically handles Paper 1, Paper 2, etc.
     */
    private function aggregateMarksBySubject($marksCollection)
    {
        return $marksCollection->groupBy('exam.subject_id')->map(function ($subjectMarks) {
            // Calculate the average score across all papers for this subject
            $averageScore = $subjectMarks->avg('score');
            
            // Calculate percentage based on max marks 
            $totalMaxMarks = $subjectMarks->sum('exam.max_marks');
            $totalObtained = $subjectMarks->sum('score');
            $percentage = ($totalMaxMarks > 0) ? (($totalObtained / $totalMaxMarks) * 100) : 0;

            return (object) [
                'subject_id'    => $subjectMarks->first()->exam->subject_id,
                'subject_name'  => $subjectMarks->first()->exam->subject->subject_name,
                'papers_taken'  => $subjectMarks->count(),
                'total_score'   => $totalObtained,
                'average_score' => round($averageScore, 2),
                'percentage'    => round($percentage, 2),
                'grade'         => $this->calculateGrade(round($averageScore)),
                'individual'    => $subjectMarks // Raw paper data included in case the view needs to list them
            ];
        })->values(); // Reset keys for easy iteration in blade
    }

    /**
     * Resolves the letter grade based on the average score.
     */
    private function calculateGrade($score)
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }
}