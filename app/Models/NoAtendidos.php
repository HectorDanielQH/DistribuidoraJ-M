<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoAtendidos extends Model
{
    protected $table = 'no_atendidos';
    protected $fillable = ['id_cliente'];

    /**
     * Define the relationship with the Cliente model.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
}
