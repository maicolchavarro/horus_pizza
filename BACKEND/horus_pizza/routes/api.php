<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\DetallePedidoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SucursalController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\UsuarioEmpleadoController;

Route::prefix('v1')->group(function () {
    // Pública
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware('api.token')->group(function () {
        Route::get('/mesas', [MesaController::class, 'index']);
        Route::post('/mesas', [MesaController::class, 'store']);
        Route::put('/mesas/{id}', [MesaController::class, 'update']);
        Route::delete('/mesas/{id}', [MesaController::class, 'destroy']);

        Route::get('/menu', [MenuController::class, 'index']);
        Route::post('/menu', [MenuController::class, 'store']);
        Route::get('/menu/{id}', [MenuController::class, 'show']);
        Route::put('/menu/{id}', [MenuController::class, 'update']);
        Route::delete('/menu/{id}', [MenuController::class, 'destroy']);

        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
        Route::put('/pedidos/{id}', [PedidoController::class, 'update']);
        Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);

        Route::get('/detalles', [DetallePedidoController::class, 'index']);
        Route::post('/detalles', [DetallePedidoController::class, 'store']);
        Route::put('/detalles/{id}', [DetallePedidoController::class, 'update']);
        Route::delete('/detalles/{id}', [DetallePedidoController::class, 'destroy']);

        Route::get('/pedidos/{id}/detalle-completo', [PedidoController::class, 'showCompleto']);
        Route::get('/mesas/{id_mesa}/pedido-activo', [PedidoController::class, 'pedidoActivoPorMesa']);

        Route::get('/categorias', [CategoriaController::class, 'index']);
        Route::post('/categorias', [CategoriaController::class, 'store']);
        Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
        Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

        Route::get('/pedidos-cocina', [PedidoController::class, 'pedidosCocina']);
        Route::get('/pedidos-caja', [PedidoController::class, 'pedidosParaCaja']);

        Route::get('/facturas', [FacturaController::class, 'index']);
        Route::get('/facturas/{id}', [FacturaController::class, 'show']);
        Route::post('/facturas', [FacturaController::class, 'store']);

        Route::get('/admin/resumen', [AdminController::class, 'resumen']);

        Route::get('/sucursales', [SucursalController::class, 'index']);
        Route::get('/roles', [RolController::class, 'index']);

        Route::get('/empleados', [EmpleadoController::class, 'index']);
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        Route::get('/empleados/{id}', [EmpleadoController::class, 'show']);
        Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
        Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy']);

        Route::get('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'show']);
        Route::post('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'store']);
        Route::put('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'update']);
        Route::delete('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'destroy']);
    });
});
