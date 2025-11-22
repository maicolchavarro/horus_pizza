<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // 🔹 Listar todas las categorías
    public function index()
    {
        return response()->json(Categoria::orderBy('nombre_categoria')->get());
    }

    // 🔹 Crear una categoría
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:50|unique:categorias,nombre_categoria',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json([
            'message'   => 'Categoría creada correctamente',
            'categoria' => $categoria,
        ], 201);
    }

    // 🔹 Mostrar una categoría
    public function show($id)
    {
        $categoria = Categoria::findOrFail($id);

        return response()->json($categoria);
    }

    // 🔹 Actualizar una categoría
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validated = $request->validate([
            'nombre_categoria' => 'required|string|max:50|unique:categorias,nombre_categoria,' . $categoria->id_categoria . ',id_categoria',
        ]);

        $categoria->update($validated);

        return response()->json([
            'message'   => 'Categoría actualizada correctamente',
            'categoria' => $categoria,
        ]);
    }

    // 🔹 Eliminar una categoría
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        // ⚠️ Opcional: aquí podrías validar si tiene platillos asociados en `menu`
        // y evitar eliminar si está en uso.

        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}
