<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Show the student login form
    public function showLoginForm()
    {
        // If already logged in as a student, skip login page
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        return view('student.auth.login');
    }

    // Handle the login request
    public function login(Request $request)
    {
        // 1. Validate
        $request->validate([
            'student_number' => 'required|string',
            'password'       => 'required|string',
        ]);

        // 2. Attempt Login using the 'student' guard
        // Added 'status' => 'active' to ensure only active students can log in
        if (Auth::guard('student')->attempt([
            'student_number' => $request->student_number,
            'password'       => $request->password,
            'status'         => 'active'
        ], $request->remember)) {

            // 3. Success: Redirect to Student Dashboard
            $request->session()->regenerate();

            // Explicitly redirect to dashboard if intended URL is missing
            return redirect()->intended(route('student.dashboard'));
        }

        // 4. Failure: Go back with error
        return back()->withErrors([
            'student_number' => 'The provided credentials do not match our active student records.',
        ])->onlyInput('student_number');
    }

    // Handle Logout
    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }
}
