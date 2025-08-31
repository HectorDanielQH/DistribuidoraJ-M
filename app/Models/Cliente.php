<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'codigo_cliente',
        'cedula_identidad',
        'nombres',
        'apellidos',
        'celular',
        'calle_avenida',
        'zona_barrio',
        'referencia_direccion',
        'latitud',
        'longitud',
        'ruta_id',
    ];

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_cliente');
    }
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente');
    }
    public function ruta()
    {
        return $this->belongsTo(Rutas::class, 'ruta_id');
    }
}
