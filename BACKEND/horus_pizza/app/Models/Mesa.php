<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    // 👇 ESTA LÍNEA ES CLAVE
    protected $primaryKey = 'id_mesa';

    protected $fillable = [
        'id_sucursal',
        'numero_mesa',
        'capacidad',
        'estado'
    ];

    public $timestamps = false;
}
