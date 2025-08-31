<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Rutas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(Rutas::where('nombre_ruta', trim(strtoupper($row['ruta'])))->exists()) {
            $ruta = Rutas::where('nombre_ruta', trim(strtoupper($row['ruta'])))->first();
        } 
        else {
            $ruta = Rutas::create(['nombre_ruta' => trim(strtoupper($row['ruta']))]);
        }
        return new Cliente([
            'codigo_cliente' => 'CL'.str_pad(Cliente::count()+1, 6, '0', STR_PAD_LEFT),
            'cedula_identidad' => isset($row['cedula_identidad'])?trim(strtoupper($row['cedula_identidad'])): null,
            'nombres' => trim(strtoupper($row['nombres'])),
            'apellidos' => isset($row['apellidos']) ? trim(strtoupper($row['apellidos'])) : null,
            'celular' => isset($row['celular']) ? trim($row['celular']) : null,
            'calle_avenida' => isset($row['direccion']) ? trim(strtoupper($row['direccion'])) : null,
            'zona_barrio' => isset($row['zona']) ? trim(strtoupper($row['zona'])) : null,
            'referencia_direccion' => isset($row['referencia_direccion']) ? trim(strtoupper($row['referencia_direccion'])) : null,
            'latitud' => isset($row['latitud']) ? trim($row['latitud']) : null,
            'longitud' => isset($row['longitud']) ? trim($row['longitud']) : null,
            'ruta_id' => $ruta->id,
        ]);
    }
}
