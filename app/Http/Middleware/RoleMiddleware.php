<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('web')->user();


        // Debug
        logger([
            'user' => $user,
            'role' => $user?->role,
            'expected' => $roles,
            'match' => $user && in_array($user->role, $roles),
        ]);

        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized - role tidak sesuai');
        }

        return $next($request);
    }
}
