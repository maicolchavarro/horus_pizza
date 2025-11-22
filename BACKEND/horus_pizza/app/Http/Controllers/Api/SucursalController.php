<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;

class SucursalController extends Controller
{
    public function index()
    {
        return response()->json(
            Sucursal::orderBy('nombre')->get()
        );
    }
}
