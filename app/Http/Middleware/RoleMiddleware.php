<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 401, 'message' => 'No autenticado'], 401);
            }
            return redirect()->route('login');
        }

        $userRoleId = (string) $user->id_rol;
        $userRoleName = mb_strtolower($user->rol?->nombre ?? '');

        // El Administrador tiene acceso total
        if ($userRoleId === '1' || str_contains($userRoleName, 'admin')) {
            return $next($request);
        }

        $allowed = false;
        foreach ($roles as $role) {
            $r = mb_strtolower(trim($role));
            if ($userRoleId === $r) {
                $allowed = true;
                break;
            }
            if ($userRoleName === $r || str_contains($userRoleName, $r)) {
                $allowed = true;
                break;
            }
            if ($r === 'agronomo' && ($userRoleId === '3' || str_contains($userRoleName, 'agronomo'))) {
                $allowed = true;
                break;
            }
            if ($r === 'enologo' && ($userRoleId === '2' || str_contains($userRoleName, 'enologo'))) {
                $allowed = true;
                break;
            }
            if ($r === 'operador' && ($userRoleId === '4' || str_contains($userRoleName, 'operador'))) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 403, 'message' => 'No autorizado'], 403);
            }
            abort(403, 'No tiene permisos para acceder a este módulo.');
        }

        return $next($request);
    }
}
