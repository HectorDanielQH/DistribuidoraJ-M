<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable =[
        'id_proveedor',
        'id_marca',
        'id_linea',
        'codigo',
        'nombre_producto',
        'descripcion_producto',
        'cantidad',
        'detalle_cantidad',
        'precio_compra',
        'detalle_precio_compra',
        'presentacion',
        'promocion',
        'descripcion_descuento_porcentaje',
        'descripcion_regalo',
        'foto_producto',
        'estado_de_baja',
        'fecha_vencimiento',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }
    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea');
    }
    public function formaVentas()
    {
        return $this->hasMany(FormaVenta::class, 'id_producto');
    }
}
