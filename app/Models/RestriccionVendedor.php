<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestriccionVendedor extends Model
{
    protected $table = 'restricciones_vendedor';

    protected $fillable = [
        'producto_id',
        'vendedor_id',
        'limite',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}
