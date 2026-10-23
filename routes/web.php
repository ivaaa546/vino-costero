<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirección inicial
Route::get('/', fn () => redirect()->route('dashboard'));

// Autenticación Web
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:1')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/edit', [UserController::class, 'edit'])->whereNumber('id')->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UserController::class, 'update'])->whereNumber('id')->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->whereNumber('id')->name('usuarios.destroy');
    });

    // Módulos de Agronomía (Roles: 1 = Admin, 3 = Agrónomo, 4 = Operador)
    Route::middleware('role:1,3,4,agronomo,operador')->group(function () {
        foreach (['parcelas', 'controles_tierra', 'siembras', 'controles_siembra', 'siembras_enfermas'] as $res) {
            Route::get("/{$res}", [ResourceController::class, 'index'])->name("{$res}.index");
            Route::get("/{$res}/create", [ResourceController::class, 'create'])->name("{$res}.create");
            Route::post("/{$res}", [ResourceController::class, 'store'])->name("{$res}.store");
            Route::get("/{$res}/{id}/edit", [ResourceController::class, 'edit'])->whereNumber('id')->name("{$res}.edit");
            Route::put("/{$res}/{id}", [ResourceController::class, 'update'])->whereNumber('id')->name("{$res}.update");
            Route::delete("/{$res}/{id}", [ResourceController::class, 'destroy'])->whereNumber('id')->name("{$res}.destroy");
        }
    });

    // Módulos de Enología (Roles: 1 = Admin, 2 = Enólogo, 4 = Operador)
    Route::middleware('role:1,2,4,enologo,operador')->group(function () {
        foreach (['producciones', 'seguimiento_fases', 'catas'] as $res) {
            Route::get("/{$res}", [ResourceController::class, 'index'])->name("{$res}.index");
            Route::get("/{$res}/create", [ResourceController::class, 'create'])->name("{$res}.create");
            Route::post("/{$res}", [ResourceController::class, 'store'])->name("{$res}.store");
            Route::get("/{$res}/{id}/edit", [ResourceController::class, 'edit'])->whereNumber('id')->name("{$res}.edit");
            Route::put("/{$res}/{id}", [ResourceController::class, 'update'])->whereNumber('id')->name("{$res}.update");
            Route::delete("/{$res}/{id}", [ResourceController::class, 'destroy'])->whereNumber('id')->name("{$res}.destroy");
        }
    });

    // Alias genéricos para compatibilidad
    Route::get('/resources/{resource}', [ResourceController::class, 'index'])->name('resources.index');
    Route::get('/resources/{resource}/create', [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/resources/{resource}', [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/resources/{resource}/{id}/edit', [ResourceController::class, 'edit'])->whereNumber('id')->name('resources.edit');
    Route::put('/resources/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id')->name('resources.update');
    Route::delete('/resources/{resource}/{id}', [ResourceController::class, 'destroy'])->whereNumber('id')->name('resources.destroy');
});

// Redirecciones desde nombres antiguos (.php)
Route::get('/login.php', fn () => redirect()->route('login'));
Route::get('/parcelas.php', fn () => redirect()->route('parcelas.index'));
Route::get('/controles_tierra.php', fn () => redirect()->route('controles_tierra.index'));
Route::get('/siembras.php', fn () => redirect()->route('siembras.index'));
Route::get('/controles_siembras.php', fn () => redirect()->route('controles_siembra.index'));
Route::get('/siembras_enfermas.php', fn () => redirect()->route('siembras_enfermas.index'));
Route::get('/producciones.php', fn () => redirect()->route('producciones.index'));
Route::get('/seguimiento_fases.php', fn () => redirect()->route('seguimiento_fases.index'));
Route::get('/catas.php', fn () => redirect()->route('catas.index'));
