<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaMayorista extends Model
{
    protected $table = 'ventas_mayoristas';

    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'id_producto',
        'id_forma_venta',
        'precio_unitario',
        'numero_venta',
        'fecha_venta',
        'cantidad',
        'observaciones',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'precio_unitario' => 'decimal:2',
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
