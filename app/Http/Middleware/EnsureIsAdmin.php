<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur.');
        }
        return $next($request);
    }
}

class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur super.');
        }
        return $next($request);
    }
}
