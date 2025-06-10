<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'cedula_identidad',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'celular',
        'ubicacion',
        'creador_por_usuario'
    ];

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_cliente');
    }
}
