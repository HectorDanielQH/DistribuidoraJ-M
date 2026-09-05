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
        'precio_unitario',
        'numero_pedido',
        'fecha_pedido',
        'fecha_entrega',
        'fecha_contabilizacion',
        'cantidad',
        'promocion',
        'descripcion_descuento_porcentaje',
        'descripcion_regalo'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function formaVenta()
    {
        return $this->belongsTo(FormaVenta::class, 'id_forma_venta');
    }
}
