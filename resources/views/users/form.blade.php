@extends('layouts.app')

@section('content')
@php($editing = isset($user))
<div class="card" style="max-width:760px; margin:0 auto;">
    <h1 style="margin-top:0; color:#5c1d2e;">{{ $editing ? 'Editar usuario' : 'Nuevo usuario' }}</h1>

    <form method="POST" action="{{ $editing ? route('usuarios.update', $user->id_usuario) : route('usuarios.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $user->nombre ?? '') }}" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="correo">Correo</label>
                <input id="correo" type="email" name="correo" class="form-control" value="{{ old('correo', $user->correo ?? '') }}" required maxlength="120">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="id_rol">Rol</label>
                <select id="id_rol" name="id_rol" class="form-control" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id_rol }}" @selected((string) old('id_rol', $user->id_rol ?? '') === (string) $role->id_rol)>
                            {{ $role->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" class="form-control" required>
                    <option value="Activo" @selected(old('estado', $user->estado ?? 'Activo') === 'Activo')>Activo</option>
                    <option value="Inactivo" @selected(old('estado', $user->estado ?? '') === 'Inactivo')>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="password">{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
                <input id="password" type="password" name="password" class="form-control" {{ $editing ? '' : 'required' }} minlength="8">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" {{ $editing ? '' : 'required' }} minlength="8">
            </div>
        </div>

        <div style="display:flex; gap:0.6rem; margin-top:1rem;">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Guardar cambios' : 'Crear usuario' }}</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
