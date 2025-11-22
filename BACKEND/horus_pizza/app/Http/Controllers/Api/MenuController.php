<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // 🔹 Listar todos los platillos (con filtro opcional por categoría)
    public function index(Request $request)
    {
        $query = Menu::query();

        if ($request->has('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        return response()->json($query->get());
    }

    // 🔹 Crear un platillo (con imagen opcional)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'             => 'required|string|max:100',
            'descripcion'        => 'nullable|string',
            'precio'             => 'required|numeric|min:0',
            'id_categoria'       => 'required|integer|exists:categorias,id_categoria',
            'tiempo_preparacion' => 'nullable|integer|min:0',
            'imagen'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Manejo de imagen
        if ($request->hasFile('imagen')) {
            // Guarda en storage/app/public/platillos
            $path = $request->file('imagen')->store('platillos', 'public');
            // Guardamos la ruta relativa para usar con /storage
            $validated['imagen'] = 'storage/' . $path;
        }

        $platillo = Menu::create($validated);

        return response()->json([
            'message'  => 'Platillo creado correctamente',
            'platillo' => $platillo,
        ], 201);
    }

    // 🔹 Mostrar un platillo
    public function show($id)
    {
        $platillo = Menu::findOrFail($id);

        return response()->json($platillo);
    }

    // 🔹 Actualizar un platillo (con opción de cambiar imagen)
    public function update(Request $request, $id)
    {
        $platillo = Menu::findOrFail($id);

        $validated = $request->validate([
            'nombre'             => 'sometimes|required|string|max:100',
            'descripcion'        => 'sometimes|nullable|string',
            'precio'             => 'sometimes|required|numeric|min:0',
            'id_categoria'       => 'sometimes|required|integer|exists:categorias,id_categoria',
            'tiempo_preparacion' => 'sometimes|nullable|integer|min:0',
            'imagen'             => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('platillos', 'public');
            $validated['imagen'] = 'storage/' . $path;
        }

        $platillo->update($validated);

        return response()->json([
            'message'  => 'Platillo actualizado correctamente',
            'platillo' => $platillo,
        ]);
    }

    // 🔹 Eliminar un platillo
    public function destroy($id)
    {
        $platillo = Menu::findOrFail($id);
        $platillo->delete();

        return response()->json([
            'message' => 'Platillo eliminado correctamente',
        ]);
    }
}
