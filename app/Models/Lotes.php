<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lotes extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo_lote',
        'producto_id',
        'cantidad',
        'detalle_cantidad',
        'precio_ingreso',
        'detalle_precio_ingreso',
        'ingreso_lote',
        'fecha_vencimiento',
        'stock_antes',
        'stock_despues',
        'observacion',
    ];

    protected $casts = [
        'ingreso_lote' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
