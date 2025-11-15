<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Mesa;
use Illuminate\Http\Request;


class PedidoController extends Controller
{
    // ✔ LISTAR TODOS LOS PEDIDOS
    public function index()
    {
        return response()->json(Pedido::all());
    }

    // ✔ MOSTRAR UN PEDIDO POR ID
    public function show($id)
    {
        $pedido = Pedido::findOrFail($id);
        return response()->json($pedido);
    }


  
    // ✔ Pedido con mesa, empleado y detalles (con platillos)
    public function showCompleto($id)
    {
        $pedido = Pedido::with([
            'mesa',
            'empleado',
            'detalles.platillo'
        ])->findOrFail($id);

        return response()->json($pedido);
    }
    

    // ✔ Obtener pedido activo de una mesa
public function pedidoActivoPorMesa($idMesa)
{
    $pedido = Pedido::where('id_mesa', $idMesa)
        ->whereIn('estado', ['Pendiente', 'En preparación', 'Listo', 'Servido'])
        ->orderByDesc('fecha_pedido')
        ->first();

    if (!$pedido) {
        return response()->json([
            'message' => 'No hay pedido activo para esta mesa'
        ], 404);
    }

    return response()->json($pedido);
}



    // ✔ CREAR PEDIDO
   public function store(Request $request)
{
    $validated = $request->validate([
        'id_mesa'     => 'required|integer|exists:mesas,id_mesa',
        'id_empleado' => 'required|integer|exists:empleados,id_empleado',
    ]);

    // ✅ Verificar que no haya un pedido activo en esa mesa
    $existeActivo = Pedido::where('id_mesa', $validated['id_mesa'])
        ->whereIn('estado', ['Pendiente', 'En preparación', 'Listo', 'Servido'])
        ->exists();

    if ($existeActivo) {
        return response()->json([
            'message' => 'La mesa ya tiene un pedido activo'
        ], 400);
    }

    // ❌ YA NO TOCAMOS EL ESTADO DE LA MESA AQUÍ

    $pedido = Pedido::create([
        'id_mesa'     => $validated['id_mesa'],
        'id_empleado' => $validated['id_empleado'],
        'estado'      => 'Pendiente',
        'total'       => 0
    ]);

    return response()->json([
        'message' => 'Pedido creado correctamente',
        'pedido'  => $pedido
    ], 201);
}


    // ✔ ACTUALIZAR PEDIDO
    public function update(Request $request, $id)
{
    $pedido = Pedido::findOrFail($id);

    $validated = $request->validate([
        'id_mesa'     => 'sometimes|integer|exists:mesas,id_mesa',
        'id_empleado' => 'sometimes|integer|exists:empleados,id_empleado',
        'estado'      => 'sometimes|string',
        'total'       => 'sometimes|numeric'
    ]);

    // 🔁 Cambio de mesa (si lo usas)
    if ($request->has('id_mesa')) {
        $mesaAnterior = Mesa::find($pedido->id_mesa);
        if ($mesaAnterior) {
            $mesaAnterior->estado = 'Disponible';
            $mesaAnterior->save();
        }

        $mesaNueva = Mesa::find($validated['id_mesa']);

        $tieneActivo = Pedido::where('id_mesa', $validated['id_mesa'])
            ->where('id_pedido', '!=', $pedido->id_pedido)
            ->whereIn('estado', ['Pendiente', 'En preparación', 'Listo', 'Servido'])
            ->exists();

        if ($tieneActivo) {
            return response()->json(['message' => 'La nueva mesa ya tiene un pedido activo'], 400);
        }
    }

    // ✅ Si cambia el estado del pedido, actualizamos el estado de la mesa
    if (isset($validated['estado'])) {
        $mesa = Mesa::find($pedido->id_mesa);

        if ($mesa) {
            if (in_array($validated['estado'], ['En preparación', 'Listo', 'Servido'])) {
                // Cuando ya está en cocina / listo / servido
                $mesa->estado = 'Ocupada';
            } elseif ($validated['estado'] === 'Pagado') {
                // Cuando ya se pagó (Caja)
                $mesa->estado = 'Disponible';
            } elseif ($validated['estado'] === 'Pendiente') {
                // Si quieres, puede seguir como Disponible mientras solo está tomando el pedido
                $mesa->estado = 'Disponible';
            }

            $mesa->save();
        }
    }

    $pedido->update($validated);

    return response()->json([
        'message' => 'Pedido actualizado correctamente',
        'pedido'  => $pedido
    ]);
}


    // ✔ ELIMINAR PEDIDO
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Liberar mesa
        $mesa = Mesa::find($pedido->id_mesa);
        $mesa->estado = 'Disponible';
        $mesa->save();

        // Eliminar pedido
        $pedido->delete();

        return response()->json(['message' => 'Pedido eliminado correctamente']);
    }

    // 👨‍🍳 Pedidos para cocina: Pendientes o En preparación
public function pedidosCocina()
{
    $pedidos = Pedido::with([
            'mesa',
            'empleado',
            'detalles.platillo'
        ])
        ->whereIn('estado', ['Pendiente', 'En preparación'])
        // ✅ Solo pedidos que tengan al menos un detalle
        ->whereHas('detalles')
        ->orderBy('fecha_pedido', 'asc')
        ->get();

    return response()->json($pedidos);
}

}
