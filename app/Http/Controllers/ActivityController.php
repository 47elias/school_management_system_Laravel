<?php

namespace App\Http\Controllers;

use App\Models\ActivityMark;
use App\Models\ClassActivity;
use App\Models\Student;
use App\Models\SubjectAssignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CONTINUOUS ASSESSMENT (CA) CONTROLLER
 * -----------------------------------------------------------------------
 * Handles day-to-day, in-class activities (classwork, homework, quizzes,
 * participation, practicals, projects, etc.).
 */
class ActivityController extends Controller
{
    /**
     * TEACHER: Landing page listing assigned classes and subject teaching load.
     */
    public function index()
    {
        // Expands grade-wide admin allocations into one row per stream
        $assignments = SubjectAssignment::expandedForTeacher(Auth::id());

        $counts = \App\Models\ClassActivity::whereIn('subject_assignment_id', $assignments->pluck('id')->unique())
            ->selectRaw('subject_assignment_id, COUNT(*) as total')
            ->groupBy('subject_assignment_id')
            ->pluck('total', 'subject_assignment_id');

        $assignments->each(fn($a) => $a->activities_count = $counts[$a->id] ?? 0);

        return view('teachers.activities.index', compact('assignments'));
    }

    /**
     * TEACHER: Store a newly logged in-class activity.
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
        $this->authorizeAssignment($assignment);

        $term = Term::where('is_current', 1)->first() ?? Term::latest('id')->first();

        $activity = ClassActivity::create([
            'subject_assignment_id' => $assignment->id,
            'term_id'               => $term?->id,
            'title'                 => $validated['title'],
            'type'                  => $validated['type'],
            'activity_date'         => $validated['activity_date'],
            'max_score'             => $validated['max_score'],
            'weight'                => $validated['weight'] ?? 1.00,
            'notes'                 => $validated['notes'] ?? null,
            'created_by'            => Auth::id(),
        ]);

        return redirect()
            ->route('teacher.activities.record', $activity->id)
            ->with('success', 'Activity logged successfully. Record scores below.');
    }

    /**
     * TEACHER: Mark-entry grid for a specific activity.
     */
    public function recordMarks($id)
    {
        $activity = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass', 'term'])
            ->findOrFail($id);

        $this->authorizeAssignment($activity->subjectAssignment);

        $classId = $activity->subjectAssignment->class_id;

        $students = Student::where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('surname')
            ->get();

        $marks = ActivityMark::where('class_activity_id', $activity->id)
            ->get()
            ->keyBy('student_id');

        return view('teachers.activities.record_marks', compact('activity', 'students', 'marks'));
    }

    /**
     * TEACHER: Save/update student scores for an activity.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'activity_id'     => 'required|exists:class_activities,id',
            'marks'           => 'required|array',
            'marks.*.score'   => 'nullable|numeric|min:0',
            'marks.*.comment' => 'nullable|string|max:255',
        ]);

        $activity = ClassActivity::with('subjectAssignment')->findOrFail($validated['activity_id']);
        $this->authorizeAssignment($activity->subjectAssignment);

        DB::beginTransaction();
        try {
            foreach ($validated['marks'] as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null) {
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
     * TEACHER: Activity history and running CA averages for an assignment.
     */
    public function history($assignmentId)
    {
        $assignment = SubjectAssignment::with(['subject', 'schoolClass'])->findOrFail($assignmentId);
        $this->authorizeAssignment($assignment);

        $term = Term::where('is_current', 1)->first() ?? Term::latest('id')->first();

        $activities = ClassActivity::where('subject_assignment_id', $assignment->id)
            ->when($term, fn($q) => $q->where('term_id', $term->id))
            ->withCount('marks')
            ->orderByDesc('activity_date')
            ->get();

        $students = Student::where('class_id', $assignment->class_id)
            ->where('status', 'active')
            ->orderBy('surname')
            ->get()
            ->map(function ($student) use ($assignment, $term) {
                $student->ca_average = ($term && method_exists($student, 'continuousAssessmentAverage'))
                    ? $student->continuousAssessmentAverage($assignment->subject_id, $term->id)
                    : 0.0;
                return $student;
            });

        return view('teachers.activities.history', compact('assignment', 'activities', 'students', 'term'));
    }

    /**
     * TEACHER/ADMIN: Delete an activity and its associated marks.
     */
    public function destroy($id)
    {
        $activity = ClassActivity::with('subjectAssignment')->findOrFail($id);
        $this->authorizeAssignment($activity->subjectAssignment);

        DB::beginTransaction();
        try {
            ActivityMark::where('class_activity_id', $activity->id)->delete();
            $activity->delete();
            DB::commit();

            return back()->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error deleting activity: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN: School-wide overview of all logged continuous assessment activities.
     */
    public function adminIndex(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : $activeTerm;

        $activities = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass'])
            ->withCount('marks')
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('subjectAssignment', fn($qq) => $qq->where('class_id', $request->class_id)))
            ->when($request->filled('subject_id'), fn($q) => $q->whereHas('subjectAssignment', fn($qq) => $qq->where('subject_id', $request->subject_id)))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->orderByDesc('activity_date')
            ->paginate(25)
            ->withQueryString();

        $classes = SchoolClass::orderBy('class_name')->get();
        $subjects = Subject::orderBy('subject_name')->get();

        return view('activities.index', compact('activities', 'terms', 'selectedTerm', 'classes', 'subjects'));
    }

    /**
     * Authorization check: verifies if the logged-in user owns the assignment or is an admin.
     */
    private function authorizeAssignment(SubjectAssignment $assignment): void
    {
        $user = Auth::user();

        $isAdmin = ($user->role === 'admin') || (method_exists($user, 'hasRole') && $user->hasRole('admin'));

        if ($assignment->teacher_id !== $user->id && !$isAdmin) {
            abort(403, 'Unauthorized access to this class activity.');
        }
    }
}