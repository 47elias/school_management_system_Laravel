<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\StaffPasswordResetOtp;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function showVerifyForm(Request $request)
    {
        return view('auth.verify', ['resetEmail' => $request->session()->get('staff_password_reset_email')]);
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|max:255']);
        $email = trim($validated['email']);
        $key = 'staff-password-send:'.hash('sha256', strtolower($email));

        if (RateLimiter::tooManyAttempts($key, 1)) {
            throw ValidationException::withMessages([
                'email' => 'Please wait '.RateLimiter::availableIn($key).' seconds before requesting another code.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->where('role', '!=', 'student')->first();
        if ($user) {
            $email = $user->email;
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $token = Hash::make($code);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => $token, 'created_at' => now()]
            );

            try {
                Mail::to($user->email)->send(new StaffPasswordResetOtp($code));
            } catch (Throwable $exception) {
                DB::table('password_reset_tokens')->where('email', $user->email)->where('token', $token)->delete();
                report($exception);

                return back()->withErrors(['email' => 'The reset email could not be sent. Please try again shortly.'])
                    ->withInput($request->only('email'));
            }
        }

        // Keep the response the same for unknown addresses and student accounts.
        $request->session()->put('staff_password_reset_email', $email);

        return redirect()->route('password.request')->with('status',
            'If this email belongs to a staff account, a six-digit reset code has been sent. It expires in 10 minutes.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'regex:/^[0-9]{6}$/'],
            'password' => 'required|string|min:8|confirmed',
        ]);
        $email = $request->session()->get('staff_password_reset_email');
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Request a reset code first.']);
        }

        $key = 'staff-password-verify:'.hash('sha256', strtolower($email));
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['otp' => 'Too many attempts. Please try again in 10 minutes.']);
        }
        RateLimiter::hit($key, 600);

        $user = DB::transaction(function () use ($email, $validated) {
            $reset = DB::table('password_reset_tokens')->where('email', $email)->lockForUpdate()->first();
            $user = User::where('email', $email)->where('role', '!=', 'student')->lockForUpdate()->first();
            if (!$reset || !$user || !$reset->created_at
                || Carbon::parse($reset->created_at)->addMinutes(10)->lte(now())
                || !Hash::check($validated['otp'], $reset->token)) {
                throw ValidationException::withMessages(['otp' => 'The code is invalid or has expired. Request a new code if needed.']);
            }

            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ])->save();
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $user;
        });

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)->delete();
        }
        event(new PasswordReset($user));
        RateLimiter::clear($key);
        $request->session()->forget('staff_password_reset_email');

        return redirect()->route('login')->with('success', 'Password updated successfully. Please login with your new password.');
    }
}
