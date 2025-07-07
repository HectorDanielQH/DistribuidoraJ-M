<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rutas extends Model
{
    protected $table = 'rutas';
    protected $fillable = ['nombre_ruta'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'ruta_id');
    }
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_ruta');
    }
}
