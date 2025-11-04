<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    // ⚠️ Nombre de la tabla en minúsculas para evitar errores
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_mesa',
        'id_empleado',
        'fecha_pedido',
        'estado',
        'total'
    ];

    // 🔗 Relación: Pedido tiene muchos Detalles
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    // 🔗 Relación: Pedido pertenece a una Mesa
    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa', 'id_mesa');
    }

    // 🔗 Relación: Pedido pertenece a un Empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
