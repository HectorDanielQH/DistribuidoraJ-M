<?php

namespace App\Models\Administrador;

use Illuminate\Database\Eloquent\Model;

class Mayorista extends Model
{
    protected $table = 'mayoristas';

    protected $fillable = [
        'tipo_venta',
        'precio_venta',
        'equivalencia_cantidad',
        'estado',
        'cantidad_minima_venta',
        'id_producto',
    ];
}
