<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\GradeLevel;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of grade levels and their streams.
     */
    public function index()
    {
        $gradeLevels = GradeLevel::with(['schoolClasses.teacher', 'schoolClasses.classTeacher', 'schoolClasses.students'])
            ->orderBy('level_order', 'asc')
            ->get();

        $classes = SchoolClass::with(['gradeLevel', 'teacher', 'classTeacher', 'students', 'subjects'])
            ->orderBy('class_name', 'asc')
            ->get();

        $teachers = User::where('role', 'teacher')
            ->orWhereHas('roles', function ($q) {
                $q->where('name', 'Teacher');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('classes.manage', compact('gradeLevels', 'classes', 'teachers'));
    }

    /**
     * Store a new dynamic Grade Level / Form (e.g., Form 5, Grade 7, Primary Grade 1).
     */
    public function storeGradeLevel(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:grade_levels,name',
            'level_order' => 'required|integer|min:1',
        ]);

        GradeLevel::create([
            'name'        => trim($validated['name']),
            'level_order' => $validated['level_order'],
        ]);

        return redirect()->back()->with('success', "Grade Level '{$validated['name']}' added to the system successfully!");
    }

    /**
     * Delete a Grade Level if no streams exist under it.
     */
    public function destroyGradeLevel($id)
    {
        $gradeLevel = GradeLevel::withCount('schoolClasses')->findOrFail($id);

        if ($gradeLevel->school_classes_count > 0) {
            return back()->with('error', "Cannot delete '{$gradeLevel->name}' because it contains {$gradeLevel->school_classes_count} active sub-class stream(s). Remove streams first.");
        }

        $gradeLevel->delete();

        return redirect()->back()->with('success', "Grade Level deleted successfully!");
    }

    /**
     * Store a newly created class stream under a specific Grade Level.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade_level_id'   => 'required|exists:grade_levels,id',
            'stream'           => 'required|string|max:10',
            'room_number'      => 'nullable|string|max:50',
            'capacity'         => 'nullable|integer|min:1',
            'teacher_id'       => 'nullable|exists:users,id',
            'class_teacher_id' => 'nullable|exists:users,id',
        ]);

        $gradeLevel = GradeLevel::findOrFail($validated['grade_level_id']);
        $stream = strtoupper(trim($validated['stream']));
        $className = trim($gradeLevel->name . ' ' . $stream);
        $classCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $className));

        if (SchoolClass::where('class_code', $classCode)->exists()) {
            return back()->with('error', "Class stream '{$className}' already exists!");
        }

        $teacherId = $validated['class_teacher_id'] ?? $validated['teacher_id'] ?? null;

        SchoolClass::create([
            'grade_level_id'   => $gradeLevel->id,
            'class_name'       => $className,
            'stream'           => $stream,
            'class_code'       => $classCode,
            'room_number'      => $validated['room_number'] ?? null,
            'capacity'         => $validated['capacity'] ?? 40,
            'teacher_id'       => $teacherId,
            'class_teacher_id' => $teacherId,
        ]);

        return redirect()->back()->with('success', "Class stream '{$className}' created successfully under {$gradeLevel->name}!");
    }

    /**
     * Show edit form for a class stream.
     */
    public function edit($id)
    {
        $class = SchoolClass::with(['gradeLevel', 'teacher', 'classTeacher'])->findOrFail($id);
        $gradeLevels = GradeLevel::orderBy('level_order', 'asc')->get();
        $teachers = User::where('role', 'teacher')
            ->orWhereHas('roles', function ($q) {
                $q->where('name', 'Teacher');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('classes.edit', compact('class', 'gradeLevels', 'teachers'));
    }

    /**
     * Update class stream details and teacher assignment.
     */
    public function update(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);

        $validated = $request->validate([
            'grade_level_id'   => 'required|exists:grade_levels,id',
            'stream'           => 'required|string|max:10',
            'room_number'      => 'nullable|string|max:50',
            'capacity'         => 'nullable|integer|min:1',
            'teacher_id'       => 'nullable|exists:users,id',
            'class_teacher_id' => 'nullable|exists:users,id',
        ]);

        $gradeLevel = GradeLevel::findOrFail($validated['grade_level_id']);
        $stream = strtoupper(trim($validated['stream']));
        $className = trim($gradeLevel->name . ' ' . $stream);
        $classCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $className));

        $existing = SchoolClass::where('class_code', $classCode)->where('id', '!=', $id)->first();
        if ($existing) {
            return back()->with('error', "Another class with code '{$classCode}' already exists.");
        }

        $teacherId = $validated['class_teacher_id'] ?? $validated['teacher_id'] ?? null;

        $class->update([
            'grade_level_id'   => $gradeLevel->id,
            'class_name'       => $className,
            'stream'           => $stream,
            'class_code'       => $classCode,
            'room_number'      => $validated['room_number'] ?? null,
            'capacity'         => $validated['capacity'] ?? $class->capacity,
            'teacher_id'       => $teacherId,
            'class_teacher_id' => $teacherId,
        ]);

        return redirect()->route('classes.index')->with('success', "Class '{$className}' updated successfully!");
    }

    /**
     * Show the form for assigning subjects to grade levels or individual streams.
     */
    public function assignSubjects()
    {
        $gradeLevels = GradeLevel::with(['schoolClasses.subjects'])->orderBy('level_order', 'asc')->get();
        $classes = SchoolClass::with(['gradeLevel', 'subjects'])->orderBy('class_name', 'asc')->get();
        $subjects = Subject::orderBy('subject_name', 'asc')->get();

        return view('classes.assign_subjects', compact('gradeLevels', 'classes', 'subjects'));
    }

    /**
     * Store or update subject assignments for an entire Grade Level or an individual Stream.
     */
    public function storeAssignments(Request $request)
    {
        $validated = $request->validate([
            'assignment_mode' => 'required|in:grade,stream',
            'grade_level_id'  => 'required_if:assignment_mode,grade|nullable|exists:grade_levels,id',
            'class_id'        => 'required_if:assignment_mode,stream|nullable|exists:school_classes,id',
            'subject_ids'     => 'nullable|array',
            'subject_ids.*'   => 'exists:subjects,id',
        ]);

        $subjectIds = $request->input('subject_ids', []);

        if ($request->assignment_mode === 'grade') {
            $gradeLevel = GradeLevel::with('schoolClasses')->findOrFail($request->grade_level_id);
            
            if ($gradeLevel->schoolClasses->isEmpty()) {
                return back()->with('error', "No sub-class streams found under {$gradeLevel->name}. Create a stream first.");
            }

            // Synchronize selected subjects across ALL streams in this grade level
            foreach ($gradeLevel->schoolClasses as $class) {
                $class->subjects()->sync($subjectIds);
            }

            return back()->with('success', "Subjects synchronized across all {$gradeLevel->schoolClasses->count()} stream(s) of {$gradeLevel->name}!");
        } else {
            // Customize subjects for a single specific stream
            $class = SchoolClass::findOrFail($request->class_id);
            $class->subjects()->sync($subjectIds);

            return back()->with('success', "Subjects customized specifically for {$class->class_name}!");
        }
    }

    /**
     * Display students in a class.
     */
    public function showStudents($id)
    {
        $class = SchoolClass::with(['students', 'teacher', 'classTeacher', 'gradeLevel'])->findOrFail($id);
        return view('classes.students', compact('class'));
    }

    /**
     * Delete a class stream.
     */
    public function destroy($id)
    {
        $class = SchoolClass::withCount('students')->findOrFail($id);

        if ($class->students_count > 0) {
            return back()->with('error', "Cannot delete {$class->class_name} because it has {$class->students_count} enrolled student(s). Reassign students first.");
        }

        $class->subjects()->detach();
        $class->delete();

        return redirect()->route('classes.index')->with('success', "Class stream deleted successfully!");
    }
}