<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Vino Costero' }} - Sistema de Gestion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
<header class="bg-wine text-white shadow-lg">
    <div class="mx-auto flex max-w-[1300px] flex-wrap items-center justify-between gap-4 px-6 py-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-xl font-bold text-white no-underline">🍇 Vino Costero</a>
        @auth
            @php
                $user = auth()->user();
                $rolId = (int) $user->id_rol;
                $canAgro = ($rolId === 1 || $rolId === 3 || $rolId === 4);
                $canEno = ($rolId === 1 || $rolId === 2 || $rolId === 4);
            @endphp
            <nav class="flex flex-wrap items-center gap-1">
                <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Dashboard</a>
                @if($canAgro)
                    <a href="{{ route('parcelas.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Parcelas</a>
                    <a href="{{ route('controles_tierra.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Tierra</a>
                    <a href="{{ route('siembras.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Siembras</a>
                    <a href="{{ route('controles_siembra.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Controles</a>
                    <a href="{{ route('siembras_enfermas.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Enfermedades</a>
                @endif
                @if($canEno)
                    <a href="{{ route('producciones.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Producciones</a>
                    <a href="{{ route('seguimiento_fases.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Fases</a>
                    <a href="{{ route('catas.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Catas</a>
                @endif
                @if($rolId === 1)
                    <a href="{{ route('usuarios.index') }}" class="rounded-md px-3 py-2 text-sm text-rose-100 hover:bg-white/15 hover:text-white">Usuarios</a>
                @endif
            </nav>
            <div class="flex items-center gap-3 text-sm">
                <span class="rounded-full bg-white/15 px-3 py-1">{{ $user->nombre }} · {{ $user->rol?->nombre ?? 'Rol '.$rolId }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-white/30 bg-black/20 px-3 py-2 text-sm text-white hover:bg-black/40">Salir</button>
                </form>
            </div>
        @endauth
    </div>
</header>

<main class="mx-auto max-w-[1300px] px-6 py-6">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Ocurrieron errores:</strong>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
