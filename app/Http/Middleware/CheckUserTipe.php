<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserTipe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$allowedTypes  // tipe user yang diizinkan (multiple)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$allowedTypes)
    {
        $user = $request->user();

        if (!$user) {
            // Belum login
            return redirect()->route('login');
        }

        // Jika Super Admin atau All Role, boleh akses semua
        if (in_array($user->tipe, ['All Role'])) {
            return $next($request);
        }

        // Cek apakah tipe user termasuk allowed
        if (in_array($user->tipe, $allowedTypes)) {
            return $next($request);
        }

        // Kalau tidak punya akses, bisa redirect ke dashboard atau abort 403
        // abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        return  redirect()->route('permohonan.index');

    }
}
