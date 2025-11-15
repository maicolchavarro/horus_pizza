<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Pedido;
use App\Models\Mesa;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    // 🔹 Listar todas las facturas
    public function index()
    {
        return Factura::with('detalles')->orderBy('id_factura', 'desc')->get();
    }

    // 🔹 Ver una factura
    public function show($id)
    {
        return Factura::with('detalles')->findOrFail($id);
    }

    // 🔹 Crear factura desde un pedido
    public function store(Request $request)
    {
        $request->validate([
            'id_pedido'   => 'required|exists:pedidos,id_pedido',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia,Mixto'
        ]);

        $pedido = Pedido::with('detalles.platillo')->findOrFail($request->id_pedido);

        if ($pedido->estado !== 'Listo') {
            return response()->json(['message' => 'El pedido no está listo para facturar'], 400);
        }

        // ----- Generar número de factura -----
        $numero = 'FAC-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT);

        // Calcular totales
        $subtotal = $pedido->detalles->sum('subtotal');
        $impuesto = $subtotal * 0.19; // IVA 19% opcional
        $total = $subtotal + $impuesto;

        // Crear factura
        $factura = Factura::create([
            'id_pedido'      => $pedido->id_pedido,
            'numero_factura' => $numero,
            'subtotal'       => $subtotal,
            'impuesto'       => $impuesto,
            'total'          => $total,
            'metodo_pago'    => $request->metodo_pago,
            'estado'         => 'Emitida',
        ]);

        // Crear detalle_factura copiando de detalle_pedido
        foreach ($pedido->detalles as $item) {
            DetalleFactura::create([
                'id_factura'      => $factura->id_factura,
                'id_platillo'     => $item->platillo->id_platillo,
                'nombre_platillo' => $item->platillo->nombre,
                'cantidad'        => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'subtotal'        => $item->subtotal
            ]);
        }

        // Cambiar estado del pedido a Pagado
        $pedido->estado = 'Pagado';
        $pedido->save();

        // Liberar mesa a Disponible
        Mesa::where('id_mesa', $pedido->id_mesa)->update(['estado' => 'Disponible']);

        return response()->json([
            'message' => 'Factura creada correctamente',
            'factura' => $factura
        ]);
    }

    // 🔹 Anular factura
    public function anular($id)
    {
        $factura = Factura::findOrFail($id);

        $factura->estado = 'Anulada';
        $factura->save();

        return response()->json(['message' => 'Factura anulada']);
    }
}
