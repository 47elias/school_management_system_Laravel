<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function showActivationForm()
    {
        return view('student.auth.activate');
    }

    public function activate(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'identifier.required' => 'Enter your admission tracking ID or National ID / Birth Entry Number.',
        ]);

        $identifier = trim($validated['identifier']);
        $normalizedIdentifier = Str::upper($identifier);
        $admission = Admission::where('status', 'approved')
            ->where(function ($query) use ($normalizedIdentifier) {
                $query->whereRaw('UPPER(tracking_id) = ?', [$normalizedIdentifier])
                    ->orWhereRaw('UPPER(identity_number) = ?', [$normalizedIdentifier]);
            })
            ->first();

        if ($admission) {
            $student = Student::where('national_id', $admission->identity_number)->where('status', 'active')->first();

            if ($admission->activation_payment_status === 'pending') {
                return back()->withInput($request->only('identifier'))->withErrors([
                    'identifier' => 'Your proof of payment is awaiting approval by the school.',
                ]);
            }

            if ($admission->activation_payment_status === 'approved') {
                $message = $student && !empty($student->password)
                    ? 'This portal account is already active. Sign in with your student number, or use Forgot password.'
                    : 'Your payment was approved, but your student record is not ready yet. Contact the admissions office.';
                return back()->withInput($request->only('identifier'))->withErrors(['identifier' => $message]);
            }

            if ($student && !empty($student->password) && $admission->activation_payment_status !== 'rejected') {
                return back()->withInput($request->only('identifier'))->withErrors([
                    'identifier' => 'This portal account is already active. Sign in with your student number, or use Forgot password.',
                ]);
            }

            $proofPath = $request->file('payment_proof')->store('student-payment-proofs', 'local');
            $passwordHash = Hash::make($validated['password']);
            $admission->forceFill([
                'activation_payment_proof' => $proofPath,
                'activation_payment_status' => 'pending',
                'activation_payment_remarks' => null,
                'portal_password' => $passwordHash,
            ])->save();

            return redirect()->route('student.login')->with('status', "Your proof of payment has been submitted. The school will review it before adding you to the student register and enabling portal access. Application tracking ID: {$admission->tracking_id}.");
        } else {
            $student = Student::whereRaw('UPPER(national_id) = ?', [$normalizedIdentifier])
                ->where('status', 'active')->first();
        }

        if (!$student) {
            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => 'We could not find an active enrolled student or an approved admission with that tracking ID or National ID / Birth Entry Number.',
            ]);
        }

        if (!empty($student->password) && $student->activation_payment_status !== 'rejected') {
            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => 'This portal account is already active. Sign in with your student number, or use Forgot password.',
            ]);
        }

        $proofPath = $request->file('payment_proof')->store('student-payment-proofs', 'local');
        $passwordHash = Hash::make($validated['password']);
        DB::transaction(function () use ($student, $passwordHash, $proofPath) {
            $student->forceFill([
                'password' => $passwordHash,
                'activation_payment_proof' => $proofPath,
                'activation_payment_status' => 'pending',
                'activation_payment_remarks' => null,
            ])->save();
            User::where('national_id', $student->national_id)
                ->where('role', 'student')
                ->update(['password' => $passwordHash, 'updated_at' => now()]);
        });

        return redirect()->route('student.login')->with('status', "Your activation and proof of payment have been submitted. Portal access will be available after the school approves the payment. Your student number is {$student->student_number}.");
    }

    // Handle the login request
    public function login(Request $request)
    {
        // 1. Validate
        $request->validate([
            'student_number' => 'required|string',
            'password'       => 'required|string',
        ]);

        $studentRecord = Student::where('student_number', $request->student_number)->first();
        if ($studentRecord && in_array($studentRecord->activation_payment_status, ['pending', 'rejected'], true)) {
            $message = $studentRecord->activation_payment_status === 'pending'
                ? 'Your proof of payment is awaiting approval by the school.'
                : 'Your proof of payment was not approved. Please contact the admissions office.';

            return back()->withErrors(['student_number' => $message])->onlyInput('student_number');
        }

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
