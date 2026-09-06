<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Register Spatie Role and Permission middleware aliases
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
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
            $user = auth()->user();
            if ($user) {
                // If you eventually migrate fully to Spatie, you can update these to $user->hasRole('admin')
                if ($user->role === 'admin') {
                    return route('dashboard');
                }
                if ($user->role === 'teacher') {
                    return route('teacher.dashboard');
                }
                if ($user->role === 'receptionist') {
                    return route('receptionist.dashboard');
                }
            }

            return '/'; // Final Fallback
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();