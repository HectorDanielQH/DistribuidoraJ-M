<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaVenta extends Model
{
    protected $fillable = [
        'tipo_venta',
        'precio_venta',
        'id_producto',
        'activo',
    ];
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
