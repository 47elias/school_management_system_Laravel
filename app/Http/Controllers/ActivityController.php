<?php

namespace App\Http\Controllers;

use App\Models\ActivityMark;
use App\Models\ClassActivity;
use App\Models\Student;
use App\Models\SubjectAssignment;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CONTINUOUS ASSESSMENT (CA) CONTROLLER
 * -----------------------------------------------------------------------
 * Fully independent from ExamController / the `exams` + `marks` tables.
 * This covers day-to-day, in-class activity marks (classwork, homework,
 * quizzes, participation, etc.) that have no fixed exam-style schedule
 * and can be logged as often as needed.
 */
class ActivityController extends Controller
{
    /**
     * TEACHER: Landing page - one card per teaching assignment (subject + class),
     * each with a "Log Activity" action and a link to that class's activity history.
     */
    public function teacherIndex()
    {
        $assignments = SubjectAssignment::with(['subject', 'schoolClass'])
            ->where('teacher_id', Auth::id())
            ->withCount('activities')
            ->get();

        return view('teachers.activities.index', compact('assignments'));
    }

    /**
     * TEACHER: Quick-create a new activity for one of their assignments,
     * then send them straight to the mark-entry grid. Defaults the date to
     * today since these are meant to be logged the same day, not scheduled
     * ahead of time.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_assignment_id' => 'required|exists:subject_assignments,id',
            'title'                 => 'required|string|max:255',
            'type'                  => 'required|in:classwork,homework,quiz,participation,practical,project,other',
            'activity_date'         => 'required|date',
            'max_score'             => 'required|integer|min:1|max:1000',
            'weight'                => 'nullable|numeric|min:0.1|max:10',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        $assignment = SubjectAssignment::findOrFail($validated['subject_assignment_id']);

        if ($assignment->teacher_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized: you are not assigned to this class/subject.');
        }

        $term = Term::where('is_current', 1)->first() ?? Term::latest('id')->first();

        $activity = ClassActivity::create([
            'subject_assignment_id' => $assignment->id,
            'term_id'                => $term?->id,
            'title'                  => $validated['title'],
            'type'                   => $validated['type'],
            'activity_date'          => $validated['activity_date'],
            'max_score'              => $validated['max_score'],
            'weight'                 => $validated['weight'] ?? 1.00,
            'notes'                  => $validated['notes'] ?? null,
            'created_by'             => Auth::id(),
        ]);

        return redirect()
            ->route('teacher.activities.record', $activity->id)
            ->with('success', 'Activity logged. Now record student scores below.');
    }

    /**
     * TEACHER: Mark-entry grid for one activity - lists every student in
     * that class with an editable score field, mirroring the exam
     * record-marks screen for a consistent UX.
     */
    public function recordMarks($id)
    {
        $activity = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass', 'term'])
            ->findOrFail($id);

        $this->authorizeAssignment($activity->subjectAssignment);

        $classId = $activity->subjectAssignment->class_id;

        $students = Student::where('class_id', $classId)
            ->orderBy('surname')
            ->get();

        $marks = ActivityMark::where('class_activity_id', $activity->id)
            ->get()
            ->keyBy('student_id');

        return view('teachers.activities.record_marks', compact('activity', 'students', 'marks'));
    }

    /**
     * TEACHER: Bulk save/update scores for an activity. Uses
     * updateOrCreate against the unique (class_activity_id, student_id)
     * constraint, so re-opening the same activity later to add or correct
     * scores just works - no duplicate rows, no separate edit endpoint.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'activity_id'      => 'required|exists:class_activities,id',
            'marks'             => 'required|array',
            'marks.*.score'     => 'nullable|numeric|min:0',
            'marks.*.comment'   => 'nullable|string|max:255',
        ]);

        $activity = ClassActivity::with('subjectAssignment')->findOrFail($validated['activity_id']);
        $this->authorizeAssignment($activity->subjectAssignment);

        DB::beginTransaction();
        try {
            foreach ($validated['marks'] as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '') {
                    ActivityMark::updateOrCreate(
                        ['class_activity_id' => $activity->id, 'student_id' => $studentId],
                        [
                            'score'   => min((float) $data['score'], $activity->max_score),
                            'comment' => $data['comment'] ?? null,
                        ]
                    );
                }
            }
            DB::commit();
            return back()->with('success', 'Activity scores saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to save scores: ' . $e->getMessage());
        }
    }

    /**
     * TEACHER: History of everything logged for one teaching assignment,
     * plus each student's running CA average for that subject/term -
     * computed with a single aggregate query, not per-row loops.
     */
    public function history($assignmentId)
    {
        $assignment = SubjectAssignment::with(['subject', 'schoolClass'])->findOrFail($assignmentId);
        $this->authorizeAssignment($assignment);

        $term = Term::where('is_current', 1)->first();

        $activities = ClassActivity::where('subject_assignment_id', $assignment->id)
            ->when($term, fn($q) => $q->where('term_id', $term->id))
            ->withCount('marks')
            ->orderByDesc('activity_date')
            ->get();

        $students = Student::where('class_id', $assignment->class_id)
            ->orderBy('surname')
            ->get()
            ->map(function ($student) use ($assignment, $term) {
                $student->ca_average = $term
                    ? $student->continuousAssessmentAverage($assignment->subject_id, $term->id)
                    : 0.0;
                return $student;
            });

        return view('teachers.activities.history', compact('assignment', 'activities', 'students', 'term'));
    }

    /**
     * TEACHER/ADMIN: Delete an activity and its scores.
     */
    public function destroy($id)
    {
        $activity = ClassActivity::with('subjectAssignment')->findOrFail($id);
        $this->authorizeAssignment($activity->subjectAssignment);

        // Marks cascade-delete at the DB level, but staying explicit keeps
        // this consistent with the rest of the codebase's transaction style.
        DB::beginTransaction();
        try {
            ActivityMark::where('class_activity_id', $activity->id)->delete();
            $activity->delete();
            DB::commit();
            return back()->with('success', 'Activity deleted.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting activity: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN: School-wide overview of all logged activities, filterable by
     * term / class / subject / type.
     */
    public function adminIndex(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first();
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : ($activeTerm ?? $terms->first());

        $activities = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass'])
            ->withCount('marks')
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('subjectAssignment', fn($qq) => $qq->where('class_id', $request->class_id)))
            ->when($request->filled('subject_id'), fn($q) => $q->whereHas('subjectAssignment', fn($qq) => $qq->where('subject_id', $request->subject_id)))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->orderByDesc('activity_date')
            ->paginate(25)
            ->withQueryString();

        $classes = \App\Models\SchoolClass::orderBy('class_name')->get();
        $subjects = \App\Models\Subject::orderBy('subject_name')->get();

        return view('activities.index', compact('activities', 'terms', 'selectedTerm', 'classes', 'subjects'));
    }

    /**
     * Shared authorization check: teacher must own the assignment, or be an admin.
     */
    private function authorizeAssignment(SubjectAssignment $assignment): void
    {
        $user = Auth::user();
        if ($assignment->teacher_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized: you do not teach this class/subject.');
        }
    }
}
