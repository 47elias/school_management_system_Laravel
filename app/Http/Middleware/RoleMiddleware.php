<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  <-- Changed to spread operator to accept multiple roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. If the user is a student (using student guard), let them pass.
        if (Auth::guard('student')->check()) {
            return $next($request);
        }

        // 2. Check if the user is logged in via the default (web) guard
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 3. Updated Role Authorization Logic
        // We now check if the user's role exists anywhere in the allowed roles array
        if (!in_array($user->role, $roles)) {

            // Redirect based on the user's actual role to their respective dashboard
            if ($user->role === 'admin') {
                return redirect()->intended('/dashboard');
            }

            if ($user->role === 'teacher') {
                return redirect()->intended('/teacher/dashboard');
            }

            // Fallback for unauthorized roles
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
