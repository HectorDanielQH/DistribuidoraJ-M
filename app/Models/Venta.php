<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'id_producto',
        'id_forma_venta',
        'numero_pedido',
        'fecha_pedido',
        'fecha_entrega',
        'fecha_contabilizacion',
        'cantidad',
        'promocion',
        'descripcion_descuento_porcentaje',
        'descripcion_regalo'
    ];
}
