<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'id_producto',
        'id_forma_venta',
        'numero_pedido',
        'fecha_pedido',
        'fecha_entrega',
        'cantidad',
        'estado_pedido',
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
}