<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query();

        if ($request->has('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'             => 'required|string|max:100',
            'descripcion'        => 'nullable|string',
            'precio'             => 'required|numeric|min:0',
            'id_categoria'       => 'required|integer|exists:categorias,id_categoria',
            'tiempo_preparacion' => 'nullable|integer|min:0',
            'imagen'             => 'nullable|string|max:255',
        ]);

        $platillo = Menu::create($validated);

        return response()->json([
            'message'  => 'Platillo creado correctamente',
            'platillo' => $platillo,
        ], 201);
    }

    public function show($id)
    {
        $platillo = Menu::findOrFail($id);

        return response()->json($platillo);
    }

    public function update(Request $request, $id)
    {
        $platillo = Menu::findOrFail($id);

        $validated = $request->validate([
            'nombre'             => 'sometimes|required|string|max:100',
            'descripcion'        => 'sometimes|nullable|string',
            'precio'             => 'sometimes|required|numeric|min:0',
            'id_categoria'       => 'sometimes|required|integer|exists:categorias,id_categoria',
            'tiempo_preparacion' => 'sometimes|nullable|integer|min:0',
            'imagen'             => 'sometimes|nullable|string|max:255',
        ]);

        $platillo->update($validated);

        return response()->json([
            'message'  => 'Platillo actualizado correctamente',
            'platillo' => $platillo,
        ]);
    }

    public function destroy($id)
    {
        $platillo = Menu::findOrFail($id);
        $platillo->delete();

        return response()->json([
            'message' => 'Platillo eliminado correctamente',
        ]);
    }
}
