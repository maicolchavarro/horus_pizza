<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = Login::where('usuario', $request->usuario)->first();

        if (!$login || !Hash::check($request->password, $login->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Actualiza el último acceso
        $login->ultimo_acceso = now();
        $login->save();

        // Cargar info del empleado asociado
        $empleado = DB::table('Empleados')
            ->join('Roles', 'Empleados.id_rol', '=', 'Roles.id_rol')
            ->select('Empleados.*', 'Roles.nombre_rol')
            ->where('Empleados.id_empleado', $login->id_empleado)
            ->first();

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'empleado' => $empleado,
        ], 200);
    }
}
