<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubjectAssignment;
use App\Models\Subject;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;

class SubjectAssignmentController extends Controller
{
    /**
     * Display the management page with all necessary data.
     */
    public function index()
    {
        return view('subjects.manage', [
            'subjects'    => Subject::orderBy('subject_name')->get(),
            'classes'     => SchoolClass::orderBy('class_name')->get(),
            'teachers'    => User::where('role', 'teacher')->orderBy('name')->get(),
            // Eager load using 'schoolClass' to match the relationship name in your model
            'assignments' => SubjectAssignment::with(['teacher', 'subject', 'schoolClass'])->latest()->get()
        ]);
    }

    /**
     * Link a teacher to a subject and class.
     */
    public function store(Request $request)
    {
        // Validated to match your model's $fillable: ['teacher_id', 'subject_id', 'class_id', 'academic_year']
        $request->validate([
            'teacher_id'    => 'required|exists:users,id',
            'subject_id'    => 'required|exists:subjects,id',
            'class_id'      => 'required|exists:school_classes,id',
            'academic_year' => 'required|string',
        ]);

        // Prevent duplicate assignments for the same teacher/subject/class/year combo
        $exists = SubjectAssignment::where([
            'teacher_id'    => $request->teacher_id,
            'subject_id'    => $request->subject_id,
            'class_id'      => $request->class_id,
            'academic_year' => $request->academic_year,
        ])->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'This teacher is already assigned to this subject for the selected class.')
                ->withInput();
        }

        SubjectAssignment::create($request->all());

        return redirect()->back()->with('success', 'Teacher assigned to subject successfully!');
    }

    /**
     * Remove a teacher assignment.
     */
    public function destroy($id)
    {
        $assignment = SubjectAssignment::findOrFail($id);
        $assignment->delete();

        return redirect()->back()->with('success', 'Teacher unassigned from subject.');
    }
    public function mySubjects()
    {
        $teacherId = auth::id();

        $assignments = SubjectAssignment::with(['subject', 'schoolClass'])
            ->where('teacher_id', $teacherId)
            ->orderBy('academic_year', 'desc')
            ->get();

        return view('teacher.my_subjects', compact('assignments'));
    }
}
