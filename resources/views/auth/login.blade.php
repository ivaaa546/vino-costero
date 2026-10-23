<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al Sistema - Vino Costero</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-rose-950 via-wine to-rose-700 p-4">
<div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-wine">🍇 Vino Costero</h1>
        <p class="mt-1 text-sm text-stone-500">Sistema Integral de Vitivinicultura</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div class="form-group">
            <label for="correo">Correo institucional</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}" required autofocus class="form-control" placeholder="usuario@vinocostero.com">
        </div>
        <div class="form-group">
            <label for="contrasena">Contrasena</label>
            <input type="password" id="contrasena" name="contrasena" required class="form-control" placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-full py-3">Iniciar sesion</button>
    </form>
</div>
</body>
</html>
