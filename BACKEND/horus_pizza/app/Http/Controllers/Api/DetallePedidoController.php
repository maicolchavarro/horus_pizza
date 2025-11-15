<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Menu;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    // ✔ Listar detalles (opcionalmente filtrando por id_pedido)
    public function index(Request $request)
    {
        $query = DetallePedido::query();

        if ($request->has('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        return response()->json($query->get());
    }

    // ✔ Crear detalle (agregar platillo al pedido)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pedido'  => 'required|integer|exists:pedidos,id_pedido',
            'id_platillo'=> 'required|integer|exists:menu,id_platillo',
            'cantidad'   => 'required|integer|min:1',
        ]);

        $pedido = Pedido::findOrFail($validated['id_pedido']);
        $platillo = Menu::findOrFail($validated['id_platillo']);

        $precioUnitario = $platillo->precio;
        $subtotal = $precioUnitario * $validated['cantidad'];

        $detalle = DetallePedido::create([
            'id_pedido'      => $validated['id_pedido'],
            'id_platillo'    => $validated['id_platillo'],
            'cantidad'       => $validated['cantidad'],
            'precio_unitario'=> $precioUnitario,
            'subtotal'       => $subtotal,
        ]);

        // Recalcular total del pedido
        $this->recalcularTotalPedido($pedido->id_pedido);

        return response()->json([
            'message' => 'Detalle agregado correctamente',
            'detalle' => $detalle
        ], 201);
    }

    // ✔ Actualizar detalle (por ejemplo, cambiar cantidad)
    public function update(Request $request, $id)
    {
        $detalle = DetallePedido::findOrFail($id);

        $validated = $request->validate([
            'cantidad' => 'sometimes|required|integer|min:1',
        ]);

        if ($request->has('cantidad')) {
            $detalle->cantidad = $validated['cantidad'];
            $detalle->subtotal = $detalle->precio_unitario * $detalle->cantidad;
        }

        $detalle->save();

        // Recalcular total del pedido
        $this->recalcularTotalPedido($detalle->id_pedido);

        return response()->json([
            'message' => 'Detalle actualizado correctamente',
            'detalle' => $detalle
        ]);
    }

    // ✔ Eliminar detalle (quitar un platillo del pedido)
    public function destroy($id)
    {
        $detalle = DetallePedido::findOrFail($id);
        $idPedido = $detalle->id_pedido;

        $detalle->delete();

        // Recalcular total del pedido
        $this->recalcularTotalPedido($idPedido);

        return response()->json([
            'message' => 'Detalle eliminado correctamente'
        ]);
    }

    // 🔁 Función privada para recalcular el total del pedido
    private function recalcularTotalPedido($idPedido)
    {
        $pedido = Pedido::findOrFail($idPedido);

        $total = DetallePedido::where('id_pedido', $idPedido)
            ->sum('subtotal');

        $pedido->total = $total;
        $pedido->save();
    }
}
