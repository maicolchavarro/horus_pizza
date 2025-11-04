<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Categoria;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // ✅ Listar todos los productos del menú agrupados por categoría
    public function index()
    {
        $categorias = Categoria::with('platillos')->get();

        return response()->json([
            'message' => 'Menú obtenido correctamente',
            'data' => $categorias
        ]);
    }

    // ✅ Listar todos los productos sin agrupar (opcional)
    public function all()
    {
        $platillos = Menu::with('categoria')->get();

        return response()->json([
            'message' => 'Platillos obtenidos correctamente',
            'data' => $platillos
        ]);
    }
}
