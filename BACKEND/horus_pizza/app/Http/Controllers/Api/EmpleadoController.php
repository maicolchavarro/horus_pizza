<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    // 🔹 Listar empleados (con sucursal y rol)
    public function index()
    {
        $empleados = Empleado::with(['sucursal', 'rol'])
            ->orderBy('id_empleado', 'asc')
            ->get();

        return response()->json($empleados);
    }

    // 🔹 Crear empleado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sucursal'        => 'required|integer|exists:sucursales,id_sucursal',
            'id_rol'             => 'required|integer|exists:roles,id_rol',
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'required|string|max:100',
            'dni'                => 'required|string|max:15|unique:empleados,dni',
            'telefono'           => 'nullable|string|max:15',
            'correo'             => 'nullable|email|max:100|unique:empleados,correo',
            'fecha_contratacion' => 'required|date',
            'salario'            => 'required|numeric|min:0',
        ]);

        $empleado = Empleado::create($validated);

        $empleado->load(['sucursal', 'rol']);

        return response()->json([
            'message'  => 'Empleado creado correctamente',
            'empleado' => $empleado,
        ], 201);
    }

    // 🔹 Mostrar un empleado
    public function show($id)
    {
        $empleado = Empleado::with(['sucursal', 'rol'])->findOrFail($id);
        return response()->json($empleado);
    }

    // 🔹 Actualizar empleado
    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        $validated = $request->validate([
            'id_sucursal'        => 'sometimes|required|integer|exists:sucursales,id_sucursal',
            'id_rol'             => 'sometimes|required|integer|exists:roles,id_rol',
            'nombre'             => 'sometimes|required|string|max:100',
            'apellido'           => 'sometimes|required|string|max:100',
            'dni'                => 'sometimes|required|string|max:15|unique:empleados,dni,' . $empleado->id_empleado . ',id_empleado',
            'telefono'           => 'sometimes|nullable|string|max:15',
            'correo'             => 'sometimes|nullable|email|max:100|unique:empleados,correo,' . $empleado->id_empleado . ',id_empleado',
            'fecha_contratacion' => 'sometimes|required|date',
            'salario'            => 'sometimes|required|numeric|min:0',
        ]);

        $empleado->update($validated);
        $empleado->load(['sucursal', 'rol']);

        return response()->json([
            'message'  => 'Empleado actualizado correctamente',
            'empleado' => $empleado,
        ]);
    }

    // 🔹 Eliminar empleado
    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return response()->json([
            'message' => 'Empleado eliminado correctamente',
        ]);
    }
}
