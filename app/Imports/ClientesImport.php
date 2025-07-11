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
        if(Rutas::where('nombre_ruta', trim(strtoupper($row['zona'])))->exists()) {
            $ruta = Rutas::where('nombre_ruta', trim(strtoupper($row['zona'])))->first();
        } 
        else {
            $ruta = Rutas::create(['nombre_ruta' => trim(strtoupper($row['zona']))]);
        }
        if (Cliente::where('cedula_identidad', trim(strtoupper($row['cedulaidentidad'])))->exists()) {
            $cliente = Cliente::where('cedula_identidad', trim(strtoupper($row['cedulaidentidad'])))->first();
            $cliente->update([
                'nombres' => trim(strtoupper($row['nombres'])),
                'apellido_paterno' => trim(strtoupper($row['apellidopaterno'])),
                'apellido_materno' => trim(strtoupper($row['apellidomaterno'])),
                'celular' => trim($row['celular']) ?? null,
                'ubicacion' => trim(strtoupper($row['direccion'])) ?? null,
                'creador_por_usuario' => auth()->user()->id,
                'ruta_id' => $ruta->id,
            ]);
            return $cliente;
        }
        else{
            return new Cliente([
                'cedula_identidad' => trim(strtoupper($row['cedulaidentidad'])),
                'nombres' => trim(strtoupper($row['nombres'])),
                'apellido_paterno' => trim(strtoupper($row['apellidopaterno'])),
                'apellido_materno' => trim(strtoupper($row['apellidomaterno'])),
                'celular' => trim($row['celular']) ?? null,
                'ubicacion' => trim(strtoupper($row['direccion'])) ?? null,
                'creador_por_usuario' => auth()->user()->id,
                'ruta_id' => $ruta->id,
            ]);
        }
    }
}
