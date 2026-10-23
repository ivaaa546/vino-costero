<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $correo = trim((string) ($request->input('correo') ?? $request->input('usuario') ?? ''));
        $contrasena = (string) ($request->input('contrasena') ?? $request->input('password') ?? '');

        if ($correo === '' || $contrasena === '') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'code' => 400,
                    'message' => 'El correo y la contraseña son obligatorios'
                ], 400);
            }

            return back()->withErrors(['correo' => 'El correo y la contraseña son obligatorios'])->withInput();
        }

        if (!Auth::attempt(['correo' => $correo, 'password' => $contrasena, 'estado' => 'Activo'])) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Correo o contraseña incorrectos'
                ], 401);
            }

            return back()->withErrors(['correo' => 'Correo o contraseña incorrectos'])->withInput();
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        $user = Auth::user();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'code' => 200,
                'message' => 'Inicio de sesion correcto',
                'rol' => (string) $user->id_rol,
                'usuario' => [
                    'id_usuario' => $user->id_usuario,
                    'nombre' => $user->nombre,
                    'correo' => $user->correo,
                    'id_rol' => $user->id_rol,
                    'rol' => $user->rol?->nombre,
                ],
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'code' => 200,
                'message' => 'Sesion cerrada correctamente'
            ]);
        }

        return redirect()->route('login')->with('status', 'Sesión cerrada correctamente.');
    }
}
