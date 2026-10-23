<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate the input
        $credentials = $request->validate([
            'ec_number' => 'required|string',
            'password'  => 'required|string',
        ]);

        $candidate = User::where('ec_number', $credentials['ec_number'])->first();
        if ($candidate && $candidate->role === 'student') {
            $student = Student::where('national_id', $candidate->national_id)->first();
            if ($student && in_array($student->activation_payment_status, ['pending', 'rejected'], true)) {
                $message = $student->activation_payment_status === 'pending'
                    ? 'Your proof of payment is awaiting approval by the school.'
                    : 'Your proof of payment was not approved. Please contact the admissions office.';

                return back()->withErrors(['ec_number' => $message])->onlyInput('ec_number');
            }
        }

        // 2. Attempt login
        if (Auth::attempt($credentials, $request->remember)) {

            // 3. Success: Regenerate session
            $request->session()->regenerate();

            // FIX: Call the authenticated logic instead of hardcoding /dashboard
            return $this->authenticated($request, Auth::user());
        }

        // 4. Failure
        return back()->withErrors([
            'ec_number' => 'The provided EC Number or password does not match our records.',
        ])->onlyInput('ec_number');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        // Check role and redirect to specific dashboard
        if ($user->role === 'admin') {
            return redirect()->route('dashboard'); // Matches your route name for admin
        }

        if ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }

        if ($user->role === 'receptionist') {
            return redirect()->route('receptionist.dashboard');
        }

        return redirect('/');
    }
}
