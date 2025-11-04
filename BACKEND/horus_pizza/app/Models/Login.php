<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    protected $table = 'Login';
    protected $primaryKey = 'id_login';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'usuario',
        'password',
        'ultimo_acceso',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
