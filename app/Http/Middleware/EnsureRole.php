<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->session()->get('auth_user');

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Super Admin bypasses all dashboard role checks
        if ($user['role'] === 'super_admin') {
            return $next($request);
        }

        if ($roles !== [] && ! in_array($user['role'], $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
