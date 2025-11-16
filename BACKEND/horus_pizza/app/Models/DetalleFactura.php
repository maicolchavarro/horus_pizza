<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $table = 'detalle_factura';
    protected $primaryKey = 'id_detalle_factura';
    public $timestamps = false;

    protected $fillable = [
        'id_factura',
        'id_platillo',
        'nombre_platillo',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }

    public function platillo()
    {
        return $this->belongsTo(Menu::class, 'id_platillo', 'id_platillo');
    }
}
