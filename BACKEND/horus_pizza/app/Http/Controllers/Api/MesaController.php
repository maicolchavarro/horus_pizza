<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mesa;

class MesaController extends Controller
{
    // ✅ Listar todas las mesas
    public function index()
    {
        $mesas = Mesa::all();
        return response()->json($mesas);
    }

    // ✅ Crear una nueva mesa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sucursal' => 'required|integer',
            'numero_mesa' => 'required|integer|unique:mesas,numero_mesa',
            'capacidad' => 'required|integer',
            'estado' => 'required|string'
        ]);

        $mesa = Mesa::create($validated);
        return response()->json([
            'message' => 'Mesa creada correctamente',
            'mesa' => $mesa
        ], 201);
    }

    // ✅ Actualizar una mesa
    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update($request->all());

        return response()->json([
            'message' => 'Mesa actualizada correctamente',
            'mesa' => $mesa
        ]);
    }

    // ✅ Eliminar una mesa
    public function destroy($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->delete();

        return response()->json([
            'message' => 'Mesa eliminada correctamente'
        ]);
    }
}
