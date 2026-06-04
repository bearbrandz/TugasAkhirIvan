<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdminOrApoteker
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && in_array(Auth::user()->tipe_user, ['admin', 'apoteker'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}