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

    <div class="overflow-x-auto rounded-lg border border-stone-200">
        <table>
            <thead>
                <tr>
                    <th style="padding:0.75rem 1rem;">Nombre</th>
                    <th style="padding:0.75rem 1rem;">Correo</th>
                    <th style="padding:0.75rem 1rem;">Rol</th>
                    <th style="padding:0.75rem 1rem;">Estado</th>
                    <th style="padding:0.75rem 1rem; text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="padding:0.75rem 1rem; font-weight:500;">{{ $user->nombre }}</td>
                        <td style="padding:0.75rem 1rem; color:#57534e;">{{ $user->correo }}</td>
                        <td style="padding:0.75rem 1rem;">{{ $user->rol?->nombre ?? 'Sin rol' }}</td>
                        <td style="padding:0.75rem 1rem;">
                            <span class="badge {{ $user->estado === 'Activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->estado }}
                            </span>
                        </td>
                        <td style="padding:0.75rem 1rem; text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; align-items:center; justify-content:flex-end; gap:0.4rem;">
                                <a href="{{ route('usuarios.edit', $user->id_usuario) }}" class="btn btn-secondary btn-sm">Editar</a>
                                @if((int) auth()->id() !== (int) $user->id_usuario)
                                    <form action="{{ route('usuarios.destroy', $user->id_usuario) }}" method="POST" style="display:inline; margin:0;" onsubmit="return confirm('¿Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:1.5rem; text-align:center; color:#78716c;">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div style="margin-top:1.2rem;">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
