<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index()
    {
        $classes = SchoolClass::with('teacher')->get();
        return view('classes.manage', compact('classes'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'class_name'  => 'required|string|max:255',
            'class_code'  => 'required|unique:school_classes,class_code',
            'room_number' => 'nullable|string|max:50',
            'capacity'    => 'nullable|integer',
        ]);

        SchoolClass::create($validatedData);

        return redirect()->back()->with('success', 'Class created successfully!');
    }

    /**
     * Show the form for editing the specified class (Assign Teacher).
     */
    public function edit($id)
    {
        $class = SchoolClass::findOrFail($id);
        $teachers = User::where('role', 'teacher')->get();

        return view('classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the class (specifically the teacher assignment).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'teacher_id' => 'nullable|exists:users,id',
            'class_name' => 'required|string|max:255',
        ]);

        $class = SchoolClass::findOrFail($id);

        $class->teacher_id = $request->teacher_id;
        $class->class_name = $request->class_name;
        $class->save();

        return redirect()->route('classes.index')->with('success', 'Class updated successfully!');
    }

    /**
     * Show the form for assigning subjects to classes.
     */
    public function assignSubjects()
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        return view('classes.assign_subjects', compact('classes', 'subjects'));
    }

    /**
     * Store the subject assignments.
     */
    public function storeAssignments(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'subject_ids' => 'required|array'
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        $class->subjects()->sync($request->subject_ids);

        return back()->with('success', 'Subjects assigned successfully!');
    }

    /**
     * Display students in a specific class.
     */
    public function showStudents($id) 
    {
        $class = SchoolClass::with('students')->findOrFail($id);
        return view('classes.students', compact('class'));
    }
}