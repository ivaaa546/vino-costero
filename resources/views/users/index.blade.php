@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.2rem;">
        <div>
            <h1 style="margin:0; color:#5c1d2e;">Usuarios</h1>
            <p style="margin:0.2rem 0 0; color:#6b6664;">Administracion de cuentas y permisos</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">+ Nuevo usuario</a>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->nombre }}</td>
                        <td>{{ $user->correo }}</td>
                        <td>{{ $user->rol?->nombre ?? 'Sin rol' }}</td>
                        <td>
                            <span class="badge {{ $user->estado === 'Activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->estado }}
                            </span>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="{{ route('usuarios.edit', $user->id_usuario) }}" class="btn btn-secondary btn-sm">Editar</a>
                            @if((int) auth()->id() !== (int) $user->id_usuario)
                                <form action="{{ route('usuarios.destroy', $user->id_usuario) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
