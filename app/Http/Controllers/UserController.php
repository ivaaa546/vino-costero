<?php

namespace App\Http\Controllers;

use App\Models\Auth\Rol;
use App\Models\Auth\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = Usuario::with('rol')->orderBy('nombre')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Rol::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:120', 'unique:usuarios,correo'],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ]);

        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        Usuario::create($data);

        return redirect()->route('usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(int $id)
    {
        $user = Usuario::findOrFail($id);
        $roles = Rol::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('users.form', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $user = Usuario::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore($user->id_usuario, 'id_usuario')],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ]);

        if (!empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);

        $user->update($data);

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $user = Usuario::findOrFail($id);

        if ((int) auth()->id() === (int) $user->id_usuario) {
            return back()->withErrors(['usuario' => 'No puedes eliminar tu propio usuario.']);
        }

        if ((int) $user->id_rol === 1 && Usuario::where('id_rol', 1)->count() <= 1) {
            return back()->withErrors(['usuario' => 'Debe existir al menos un administrador.']);
        }

        $user->delete();

        return redirect()->route('usuarios.index')->with('status', 'Usuario eliminado correctamente.');
    }
}
