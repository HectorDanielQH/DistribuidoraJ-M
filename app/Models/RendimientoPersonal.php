<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendimientoPersonal extends Model
{
    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'id_ruta',
        'numero_pedido',
        'asignacion_fecha_hora',
        'atencion_fecha_hora',
        'estado_pedido',
    ];

}
