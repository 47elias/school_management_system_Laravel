<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentForgotPasswordController extends Controller
{
    public function showResetForm()
    {
        return view('student_verify');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'student_number' => 'required|string',
            'dob'            => 'required|date',
            'password'       => 'required|string|min:8|confirmed',
        ]);

        $student = Student::where('student_number', $request->student_number)
                          ->where('date_of_birth', $request->dob)
                          ->first();

        if (!$student) {
            return back()
                ->withInput($request->only('student_number', 'dob'))
                ->with('error', 'The student number or date of birth provided does not match our records.');
        }

        $student->password = Hash::make($request->password);
        $student->save();

        // CHANGE: Redirect back with success so the message shows on this page first
        return back()->with('success', 'Password updated successfully! You will be redirected to the login page in 3 seconds.');
    }
}
