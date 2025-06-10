<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    protected $fillable=[
        'descripcion_linea',
        'id_marca'
    ];

    protected function marca(){
        return $this->belongsTo(Marca::class,'id_marca');
    }
    protected function productos(){
        return $this->hasMany(Producto::class,'id_linea');
    }
    
}
