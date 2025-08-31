<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lotes extends Model
{
    protected $fillable = [
        'codigo_lote',
        'producto_id',
        'cantidad',
        'detalle_cantidad',
        'precio_ingreso',
        'detalle_precio_ingreso',
        'ingreso_lote',
        'fecha_vencimiento',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
