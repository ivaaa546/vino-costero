@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="margin: 0; color: #5c1d2e;">Panel de Control</h1>
    <p style="color: #6b6664; margin: 0.3rem 0 0;">Bienvenido, <strong>{{ $user->nombre }}</strong>. Sesión activa como <strong>{{ $user->rol?->nombre ?? 'Usuario' }}</strong>.</p>
</div>

@php
    $rolId = (int) $user->id_rol;
    $moduleLinks = [
        'Parcelas' => ['route' => 'parcelas.index', 'roles' => [1, 3, 4], 'icon' => '🌱'],
        'Controles de Tierra' => ['route' => 'controles_tierra.index', 'roles' => [1, 3, 4], 'icon' => '🧪'],
        'Siembras' => ['route' => 'siembras.index', 'roles' => [1, 3, 4], 'icon' => '🌿'],
        'Controles de Siembra' => ['route' => 'controles_siembra.index', 'roles' => [1, 3, 4], 'icon' => '📋'],
        'Siembras Enfermas' => ['route' => 'siembras_enfermas.index', 'roles' => [1, 3, 4], 'icon' => '🩺'],
        'Producciones' => ['route' => 'producciones.index', 'roles' => [1, 2, 4], 'icon' => '🍷'],
        'Seguimiento de Fases' => ['route' => 'seguimiento_fases.index', 'roles' => [1, 2, 4], 'icon' => '⏳'],
        'Catas' => ['route' => 'catas.index', 'roles' => [1, 2, 4], 'icon' => '🏆'],
    ];
@endphp

<div class="grid-4" style="margin-bottom: 2rem;">
    @foreach($counts as $label => $count)
        @php
            $mod = $moduleLinks[$label] ?? null;
            $canAccess = $mod && ($rolId === 1 || in_array($rolId, $mod['roles'], true));
        @endphp
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.8rem;">{{ $mod['icon'] ?? '📊' }}</span>
                <span style="font-size: 2rem; font-weight: 700; color: #5c1d2e;">{{ $count }}</span>
            </div>
            <div style="margin-top: 0.8rem;">
                <strong style="color: #444; font-size: 0.95rem;">{{ $label }}</strong>
            </div>
            <div style="margin-top: 0.6rem;">
                @if($canAccess)
                    <a href="{{ route($mod['route']) }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">Ver registros →</a>
                @else
                    <span style="font-size: 0.75rem; color: #999;">Sin permisos</span>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #5c1d2e;">Accesos rápidos</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
        @if($rolId === 1 || $rolId === 3 || $rolId === 4)
            <a href="{{ route('parcelas.create') }}" class="btn btn-primary">+ Nueva Parcela</a>
            <a href="{{ route('siembras.create') }}" class="btn btn-primary">+ Nueva Siembra</a>
            <a href="{{ route('controles_tierra.create') }}" class="btn btn-primary">+ Control de Tierra</a>
            <a href="{{ route('controles_siembra.create') }}" class="btn btn-primary">+ Control de Siembra</a>
        @endif
        @if($rolId === 1 || $rolId === 2 || $rolId === 4)
            <a href="{{ route('producciones.create') }}" class="btn btn-primary">+ Nueva Producción</a>
            <a href="{{ route('seguimiento_fases.create') }}" class="btn btn-primary">+ Seguimiento de Fase</a>
            <a href="{{ route('catas.create') }}" class="btn btn-primary">+ Registrar Cata</a>
        @endif
    </div>
</div>
@endsection
