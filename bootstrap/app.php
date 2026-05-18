<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Register the role middleware alias (only once)
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // 2. Where to send users who are NOT logged in (Guests)
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('student/*') || $request->is('student')) {
                return route('student.login');
            }
            return route('login');
        });

        // 3. Where to send users immediately AFTER login (Authenticated)
        $middleware->redirectUsersTo(function (Request $request) {
            // Check if it's a Student using the student guard
            if (auth()->guard('student')->check()) {
                return route('student.dashboard');
            }

            // Check Staff roles using the default web guard
            $user = auth::user();
            if ($user) {
                if ($user->role === 'admin') {
                    return route('dashboard');
                }
                if ($user->role === 'teacher') {
                    return route('teacher.dashboard');
                }
            }

            return '/'; // Final Fallback
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
