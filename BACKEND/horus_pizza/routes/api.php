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

    /* =========================
       LOGIN
    ============================ */
    Route::post('/login', [LoginController::class, 'login']);



    /* =========================
       MESAS
    ============================ */
    Route::get('/mesas', [MesaController::class, 'index']);
    Route::post('/mesas', [MesaController::class, 'store']);
    Route::put('/mesas/{id}', [MesaController::class, 'update']);
    Route::delete('/mesas/{id}', [MesaController::class, 'destroy']);



    /* =========================
       MENÚ
    ============================ */
    Route::get('/menu', [MenuController::class, 'index']);
    Route::post('/menu', [MenuController::class, 'store']);
    Route::get('/menu/{id}', [MenuController::class, 'show']);
    Route::put('/menu/{id}', [MenuController::class, 'update']);
    Route::delete('/menu/{id}', [MenuController::class, 'destroy']);



    /* =========================
       PEDIDOS
    ============================ */
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
    Route::put('/pedidos/{id}', [PedidoController::class, 'update']);
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);



    /* =========================
       DETALLE DE PEDIDO
    ============================ */
    Route::get('/detalles', [DetallePedidoController::class, 'index']);
    Route::post('/detalles', [DetallePedidoController::class, 'store']);
    Route::put('/detalles/{id}', [DetallePedidoController::class, 'update']);
    Route::delete('/detalles/{id}', [DetallePedidoController::class, 'destroy']);



    /* =========================
       UTILS PEDIDOS
    ============================ */
    Route::get('/pedidos/{id}/detalle-completo', [PedidoController::class, 'showCompleto']);
    Route::get('/mesas/{id_mesa}/pedido-activo', [PedidoController::class, 'pedidoActivoPorMesa']);



    /* =========================
       CATEGORÍAS  (NUEVO)
    ============================ */
    Route::get('/categorias', [CategoriaController::class, 'index']);



    /* =========================
       COCINA
    ============================ */
    Route::get('/pedidos-cocina', [PedidoController::class, 'pedidosCocina']);



    /* =========================
       CAJA
    ============================ */
    Route::get('/pedidos-caja', [PedidoController::class, 'pedidosParaCaja']);

    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::post('/facturas', [FacturaController::class, 'store']);



    /* =========================
       ADMIN
    ============================ */
    Route::get('/admin/resumen', [AdminController::class, 'resumen']);

// CATEGORÍAS
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::post('/categorias', [CategoriaController::class, 'store']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

// SUCURSALES 
Route::get('/sucursales', [SucursalController::class, 'index']);

// ROLES (solo listar)
Route::get('/roles', [RolController::class, 'index']);

// EMPLEADOS (CRUD)
Route::get('/empleados', [EmpleadoController::class, 'index']);
Route::post('/empleados', [EmpleadoController::class, 'store']);
Route::get('/empleados/{id}', [EmpleadoController::class, 'show']);
Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy']);

// USUARIOS POR EMPLEADO
Route::get('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'show']);
Route::post('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'store']);
Route::put('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'update']);
Route::delete('/empleados/{id}/usuario', [UsuarioEmpleadoController::class, 'destroy']);



});
