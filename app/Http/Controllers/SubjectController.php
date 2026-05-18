<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index() {
    $subjects = \App\Models\Subject::all();
    return view('subjects.manage', compact('subjects'));
}

public function store(Request $request) {
    $data = $request->validate([
        'subject_name' => 'required|string',
        'subject_code' => 'required|unique:subjects',
        'type'         => 'required',
        'pass_mark'    => 'required|integer'
    ]);

    \App\Models\Subject::create($data);
    return back()->with('success', 'Subject added successfully!');
}
}
