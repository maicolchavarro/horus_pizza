<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    // 🧾 Listar todos los pedidos
    public function index()
    {
        $pedidos = Pedido::with('detalles')->get();
        return response()->json($pedidos);
    }

    // 👀 Ver un pedido por ID
    public function show($id)
    {
        $pedido = Pedido::with('detalles')->find($id);
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
            'detalles' => 'required|array',
            'detalles.*.id_platillo' => 'required|integer',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calcular total del pedido
            $total = 0;
            foreach ($validated['detalles'] as $detalle) {
                $total += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $pedido = Pedido::create([
                'id_mesa' => $validated['id_mesa'],
                'id_empleado' => $validated['id_empleado'],
                'estado' => 'Pendiente',
                'total' => $total,
            ]);

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
            return response()->json(['message' => 'Pedido creado correctamente', 'pedido' => $pedido], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear pedido', 'detalle' => $e->getMessage()], 500);
        }
    }

    // 🔄 Actualizar estado del pedido
    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->update(['estado' => $request->estado]);
        return response()->json(['message' => 'Estado actualizado correctamente']);
    }

    // ❌ Eliminar pedido
    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->detalles()->delete();
        $pedido->delete();

        return response()->json(['message' => 'Pedido eliminado correctamente']);
    }
}
