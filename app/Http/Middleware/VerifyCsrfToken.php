<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Paynow posts here server-to-server to confirm payment status.
        // It is not a browser request, so it cannot carry a CSRF token.
        'fees/pay-online/result',
    ];
}
