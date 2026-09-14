<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Jika rute mengizinkan role 'admin', maka 'admin_cabang' juga termasuk admin
        $allowedRoles = $roles;
        if (in_array('admin', $roles, true) && ! in_array('admin_cabang', $roles, true)) {
            $allowedRoles[] = 'admin_cabang';
        }

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403);
        }

        if (! $user->isAktif()) {
            abort(403, 'Akun belum aktif.');
        }

        return $next($request);
    }
}
