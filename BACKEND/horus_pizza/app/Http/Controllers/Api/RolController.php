<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;

class RolController extends Controller
{
    public function index()
    {
        return response()->json(
            Rol::orderBy('nombre_rol')->get()
        );
    }
}
