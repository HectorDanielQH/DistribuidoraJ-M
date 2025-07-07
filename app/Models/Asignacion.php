<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
    public function ruta()
    {
        return $this->belongsTo(Rutas::class, 'id_ruta');
    }
}
