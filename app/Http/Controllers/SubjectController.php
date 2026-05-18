<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of all subjects.
     */
    public function index()
    {
        $subjects = Subject::all();
        return view('subjects.manage', compact('subjects'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code',
            'type'         => 'required|string',
            'pass_mark'    => 'required|integer|min:0|max:100'
        ]);

        Subject::create($data);

        return back()->with('success', 'Subject added successfully!');
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        // When updating, ignore the current record's ID to prevent unique validation failures
        $data = $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code,' . $subject->id,
            'type'         => 'required|string',
            'pass_mark'    => 'required|integer|min:0|max:100'
        ]);

        $subject->update($data);

        return back()->with('success', 'Subject updated successfully!');
    }

    /**
     * Remove the specified subject from storage.
     * THIS FIXES THE CRASH: Call to undefined method App\Http\Controllers\SubjectController::destroy()
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);

        $subject->delete();

        return back()->with('success', 'Subject deleted successfully from the registry.');
    }
}
