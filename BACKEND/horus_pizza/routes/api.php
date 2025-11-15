<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\DetallePedidoController;
use App\Http\Controllers\Api\CategoriaController; // <<< IMPORTANTE
use App\Http\Controllers\Api\FacturaController;


Route::prefix('v1')->group(function () {

    // Login
    Route::post('/login', [LoginController::class, 'login']);

    // Mesas
    Route::get('/mesas', [MesaController::class, 'index']);
    Route::post('/mesas', [MesaController::class, 'store']);
    Route::put('/mesas/{id}', [MesaController::class, 'update']);
    Route::delete('/mesas/{id}', [MesaController::class, 'destroy']);

    // Menú
    Route::get('/menu', [MenuController::class, 'index']);
    Route::post('/menu', [MenuController::class, 'store']);
    Route::get('/menu/{id}', [MenuController::class, 'show']);
    Route::put('/menu/{id}', [MenuController::class, 'update']);
    Route::delete('/menu/{id}', [MenuController::class, 'destroy']);

    // ✔ CRUD de pedidos
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
    Route::put('/pedidos/{id}', [PedidoController::class, 'update']);
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);

    // ✔ Detalle de pedido 
    Route::get('/detalles', [DetallePedidoController::class, 'index']);
    Route::post('/detalles', [DetallePedidoController::class, 'store']);
    Route::put('/detalles/{id}', [DetallePedidoController::class, 'update']);
    Route::delete('/detalles/{id}', [DetallePedidoController::class, 'destroy']);

    // Pedido completo
    Route::get('/pedidos/{id}/detalle-completo', [PedidoController::class, 'showCompleto']);

    // Pedido activo por mesa
    Route::get('/mesas/{id_mesa}/pedido-activo', [PedidoController::class, 'pedidoActivoPorMesa']);

    // Categorías  <<< NUEVO
    Route::get('/categorias', [CategoriaController::class, 'index']);

    Route::get('/pedidos-cocina', [PedidoController::class, 'pedidosCocina']);


      // Facturas
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::post('/facturas', [FacturaController::class, 'store']); // generar factura
    Route::put('/facturas/{id}/anular', [FacturaController::class, 'anular']);

});
