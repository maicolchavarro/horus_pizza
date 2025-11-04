<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'Empleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_rol',
        'nombre',
        'apellido',
        'dni',
        'telefono',
        'correo',
        'fecha_contratacion',
        'salario'
    ];
}
