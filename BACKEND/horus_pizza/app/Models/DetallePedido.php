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

    // Relación: Detalle pertenece a un Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    // Relación: Detalle pertenece a un Platillo del menú
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_platillo', 'id_platillo');
    }
}
