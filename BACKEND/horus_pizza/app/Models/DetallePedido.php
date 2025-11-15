<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_platillo',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    // ✅ platillo del menú
    public function platillo()
    {
        return $this->belongsTo(Menu::class, 'id_platillo', 'id_platillo');
    }
}
