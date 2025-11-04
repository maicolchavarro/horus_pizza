<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MesaController;

Route::prefix('v1')->group(function () {
    // ✅ Endpoint de Login
    Route::post('/login', [LoginController::class, 'login']);

    // ✅ Endpoints de Mesas
    Route::get('/mesas', [MesaController::class, 'index']);
    Route::post('/mesas', [MesaController::class, 'store']);
    Route::put('/mesas/{id}', [MesaController::class, 'update']);
    Route::delete('/mesas/{id}', [MesaController::class, 'destroy']);

     // ✅ Endpoints del menú
    Route::get('/menu', [MenuController::class, 'index']); // Agrupado por categoría
    Route::get('/platillos', [MenuController::class, 'all']); // Todos los platillos


});
