<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login?
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Ambil role user yang sedang login
        $userRole = Auth::user()->role;

        // 3. Cek apakah role user ada di daftar yang diizinkan?
        // Contoh penggunaan di route: middleware('role:admin,wali_kelas')
        if (in_array($userRole, $roles)) {
            return $next($request); // Silakan masuk
        }

        // 4. Jika tidak cocok, blokir akses (403 Forbidden)
        abort(403, 'MAAF, ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
    }
}