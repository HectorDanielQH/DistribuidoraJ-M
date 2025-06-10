<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = [
        'nombre_proveedor', 
    ];

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_proveedor');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_proveedor');
    }
}
