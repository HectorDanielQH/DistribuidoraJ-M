<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $fillable = [
        'descripcion',
        'id_proveedor',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function linea()
    {
        return $this->hasMany(Linea::class, 'id_marca');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_marca');
    }
}
