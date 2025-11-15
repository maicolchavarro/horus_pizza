<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'id_pedido',
        'numero_factura',
        'id_cliente',
        'subtotal',
        'impuesto',
        'total',
        'metodo_pago',
        'estado'
    ];

    public $timestamps = false;

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'id_factura');
    }
}
