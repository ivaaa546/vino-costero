<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiResourceController;
use Illuminate\Support\Facades\Route;

// Autenticación API pública
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/end_login.php', [AuthController::class, 'login']);

// Rutas protegidas API
Route::middleware(['web', 'auth'])->group(function () {
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);
    Route::post('/login/end_cerrar_sesion.php', [AuthController::class, 'logout']);

    // Compatibilidad con contratos legados (definidos antes de {id} para evitar colisiones)
    Route::match(['get', 'post'], '/{resource}/visualizar.php', [ApiResourceController::class, 'index']);
    Route::post('/{resource}/crear.php', [ApiResourceController::class, 'store']);
    Route::post('/{resource}/editar.php', [ApiResourceController::class, 'update']);
    Route::post('/{resource}/eliminar.php', [ApiResourceController::class, 'destroy']);

    // Endpoints RESTful
    Route::get('/{resource}', [ApiResourceController::class, 'index']);
    Route::post('/{resource}', [ApiResourceController::class, 'store']);
    Route::get('/{resource}/{id}', [ApiResourceController::class, 'show'])->whereNumber('id');
    Route::put('/{resource}/{id}', [ApiResourceController::class, 'update'])->whereNumber('id');
    Route::patch('/{resource}/{id}', [ApiResourceController::class, 'update'])->whereNumber('id');
    Route::delete('/{resource}/{id}', [ApiResourceController::class, 'destroy'])->whereNumber('id');
});
