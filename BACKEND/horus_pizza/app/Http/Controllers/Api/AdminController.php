<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Mesa;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function resumen()
    {
        // Siempre en hora de Colombia
        $now  = Carbon::now('America/Bogota');
        $hoy  = $now->toDateString();                    // YYYY-mm-dd
        $ayer = $now->copy()->subDay()->toDateString();  // YYYY-mm-dd

        // 💵 Ventas del día (solo facturas emitidas y pagadas hoy)
        $ventasDia = Factura::whereDate('fecha_emision', $hoy)
            ->where('estado', 'Emitida')
            ->where('estado_pago', 'Pagado')
            ->sum('total');

        // 💵 Ventas de ayer
        $ventasAyer = Factura::whereDate('fecha_emision', $ayer)
            ->where('estado', 'Emitida')
            ->where('estado_pago', 'Pagado')
            ->sum('total');

        // 📈 Variación vs ayer (%)
        $ventasVariacion = 0;
        if ($ventasAyer > 0) {
            $ventasVariacion = (($ventasDia - $ventasAyer) / $ventasAyer) * 100;
        }

        // 🧾 Número de facturas del día
        $facturasDia = Factura::whereDate('fecha_emision', $hoy)
            ->where('estado', 'Emitida')
            ->where('estado_pago', 'Pagado')
            ->count();

        // 🍕 TOTAL pedidos del día
        $pedidosDia = Pedido::whereDate('fecha_pedido', $hoy)->count();

        // 🍕 PEDIDOS ACTIVOS (Pendiente / En preparación / Listo)
        $pedidosActivos = Pedido::whereDate('fecha_pedido', $hoy)
            ->whereIn('estado', ['Pendiente', 'En preparación', 'Listo'])
            ->count();

        // 🟩 Mesas por estado
        $mesasOcupadas    = Mesa::where('estado', 'Ocupada')->count();
        $mesasDisponibles = Mesa::where('estado', 'Disponible')->count();
        $mesasReservadas  = Mesa::where('estado', 'Reservada')->count();

        // 🧾 Facturas de HOY (para la tabla de reportes)
        $facturasHoy = Factura::with('pedido.mesa')
            ->whereDate('fecha_emision', $hoy)
            ->where('estado', 'Emitida')
            ->where('estado_pago', 'Pagado')
            ->orderBy('id_factura', 'desc')
            ->get();

        // 🧾 Últimas 10 facturas (para usos futuros si quieres)
        $ultimasFacturas = Factura::with('pedido.mesa')
            ->orderBy('id_factura', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'ventas_dia'        => $ventasDia,
            'ventas_ayer'       => $ventasAyer,
            'ventas_variacion'  => $ventasVariacion,
            'facturas_dia'      => $facturasDia,

            'pedidos_dia'       => $pedidosDia,
            'pedidos_activos'   => $pedidosActivos,

            'mesas_ocupadas'    => $mesasOcupadas,
            'mesas_disponibles' => $mesasDisponibles,
            'mesas_reservadas'  => $mesasReservadas,

            // 👇 nuevo: lista ya filtrada SOLO de hoy
            'facturas_hoy'      => $facturasHoy,

            // opcional, por si lo usas en otra parte
            'ultimas_facturas'  => $ultimasFacturas,
        ]);
    }
}
