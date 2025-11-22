<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioEmpleadoController extends Controller
{
    // 🔹 Obtener usuario de un empleado (si tiene)
    public function show($id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);

        $login = Login::where('id_empleado', $empleado->id_empleado)->first();

        if (!$login) {
            return response()->json(null, 200);
        }

        return response()->json([
            'id_login'    => $login->id_login,
            'id_empleado' => $login->id_empleado,
            'usuario'     => $login->usuario,
            'ultimo_acceso' => $login->ultimo_acceso,
        ]);
    }

    // 🔹 Crear usuario para un empleado
    public function store(Request $request, $id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);

        // Si ya tiene usuario, no permitir crear de nuevo
        if (Login::where('id_empleado', $empleado->id_empleado)->exists()) {
            return response()->json([
                'message' => 'Este empleado ya tiene un usuario creado.',
            ], 400);
        }

        $validated = $request->validate([
            'usuario'  => 'required|string|max:50|unique:login,usuario',
            'password' => 'required|string|min:4',
        ]);

        $login = Login::create([
            'id_empleado' => $empleado->id_empleado,
            'usuario'     => $validated['usuario'],
            'password'    => Hash::make($validated['password']),
            'ultimo_acceso' => null,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'usuario' => [
                'id_login'      => $login->id_login,
                'id_empleado'   => $login->id_empleado,
                'usuario'       => $login->usuario,
                'ultimo_acceso' => $login->ultimo_acceso,
            ]
        ], 201);
    }

    // 🔹 Actualizar usuario (cambiar nombre y/o resetear contraseña)
    public function update(Request $request, $id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);

        $login = Login::where('id_empleado', $empleado->id_empleado)->firstOrFail();

        $validated = $request->validate([
            'usuario'  => 'sometimes|required|string|max:50|unique:login,usuario,' . $login->id_login . ',id_login',
            'password' => 'sometimes|nullable|string|min:4',
        ]);

        if (isset($validated['usuario'])) {
            $login->usuario = $validated['usuario'];
        }

        if (array_key_exists('password', $validated) && $validated['password']) {
            $login->password = Hash::make($validated['password']);
        }

        $login->save();

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => [
                'id_login'      => $login->id_login,
                'id_empleado'   => $login->id_empleado,
                'usuario'       => $login->usuario,
                'ultimo_acceso' => $login->ultimo_acceso,
            ]
        ]);
    }

    // 🔹 Eliminar usuario de un empleado
    public function destroy($id_empleado)
    {
        $empleado = Empleado::findOrFail($id_empleado);

        $login = Login::where('id_empleado', $empleado->id_empleado)->first();

        if (!$login) {
            return response()->json([
                'message' => 'Este empleado no tiene usuario asignado.',
            ], 404);
        }

        $login->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
}
