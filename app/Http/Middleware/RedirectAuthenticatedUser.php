<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        $user = Auth::guard('web')->user();
        if ($user) {
            return match ($user->role) {
                'teacher' => redirect()->route('teacher.dashboard'),
                'receptionist' => redirect()->route('receptionist.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}
