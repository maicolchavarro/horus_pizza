<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    // 🧾 Listar todos los pedidos con detalles, mesa, empleado y platillos
    public function index()
    {
        $pedidos = Pedido::with([
            'detalles.menu:id_platillo,nombre,precio',
            'mesa:id_mesa,numero_mesa,estado',
            'empleado:id_empleado,nombre,apellido'
        ])->get();

        return response()->json($pedidos);
    }

    // 👀 Ver un pedido por ID con detalles y platillos
    public function show($id)
    {
        $pedido = Pedido::with([
            'detalles.menu:id_platillo,nombre,precio',
            'mesa:id_mesa,numero_mesa,estado',
            'empleado:id_empleado,nombre,apellido'
        ])->find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    // ➕ Crear un nuevo pedido con detalles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mesa' => 'required|integer',
            'id_empleado' => 'required|integer',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_platillo' => 'required|integer',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calcular total del pedido
            $total = collect($validated['detalles'])->sum(
                fn($d) => $d['cantidad'] * $d['precio_unitario']
            );

            // Crear el pedido
            $pedido = Pedido::create([
                'id_mesa' => $validated['id_mesa'],
                'id_empleado' => $validated['id_empleado'],
                'estado' => 'Pendiente',
                'total' => $total,
            ]);

            // Insertar los detalles del pedido
            foreach ($validated['detalles'] as $detalle) {
                DetallePedido::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_platillo' => $detalle['id_platillo'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                ]);
            }

            DB::commit();

            // Cargar relaciones al pedido creado
            $pedido->load([
                'detalles.menu:id_platillo,nombre,precio',
                'mesa:id_mesa,numero_mesa,estado',
                'empleado:id_empleado,nombre,apellido'
            ]);

            return response()->json([
                'message' => '✅ Pedido creado correctamente',
                'pedido' => $pedido
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => '❌ Error al crear pedido',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    // 🔄 Actualizar un pedido y sus detalles
    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            // Actualizar campos principales si vienen en la request
            $pedido->update([
                'id_mesa' => $request->id_mesa ?? $pedido->id_mesa,
                'id_empleado' => $request->id_empleado ?? $pedido->id_empleado,
                'estado' => $request->estado ?? $pedido->estado,
            ]);

            // Si vienen nuevos detalles, reemplazarlos
            if ($request->has('detalles')) {
                $pedido->detalles()->delete();

                $total = 0;
                foreach ($request->detalles as $detalle) {
                    $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                    $total += $subtotal;

                    DetallePedido::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_platillo' => $detalle['id_platillo'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);
                }

                // Actualizar total del pedido
                $pedido->update(['total' => $total]);
            }

            DB::commit();

            $pedido->load([
                'detalles.menu:id_platillo,nombre,precio',
                'mesa:id_mesa,numero_mesa,estado',
                'empleado:id_empleado,nombre,apellido'
            ]);

            return response()->json([
                'message' => '✅ Pedido actualizado correctamente',
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => '❌ Error al actualizar pedido',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    // ❌ Eliminar pedido y detalles
    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        try {
            $pedido->delete(); // ON DELETE CASCADE elimina los detalles automáticamente
            return response()->json(['message' => '🗑️ Pedido eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ Error al eliminar pedido',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
