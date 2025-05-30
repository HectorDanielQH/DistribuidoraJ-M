<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('nombre')) {
            $keywords = explode(' ', $request->nombre);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where(function ($subquery) use ($word) {
                        $subquery->where('nombres', 'like', '%' . $word . '%')
                                ->orWhere('apellido_paterno', 'like', '%' . $word . '%')
                                ->orWhere('apellido_materno', 'like', '%' . $word . '%');
                    });
                }
            });
        }

        if ($request->filled('ci')) {
            $query->where('cedula_identidad', 'like', '%' . $request->ci . '%');
        }

        $clientes = $query->paginate(10);

        return view('administrador.clientes.index_clientes', compact('clientes', 'request'))->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('ci'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cedulaidentidad' => 'required|string|max:255|unique:clientes,cedula_identidad',
            'nombres' => 'required|string|max:255',
            'apellidopaterno' => 'nullable|string|max:255|required_without:apellidomaterno',
            'apellidomaterno' => 'nullable|string|max:255|required_without:apellidopaterno',
            'celular' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'cedulaidentidad.unique' => 'La Cédula de Identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
        ]);

        Cliente::create([
            'cedula_identidad'=> trim(strtoupper($request->cedulaidentidad)),
            'nombres'=> trim(strtoupper($request->nombres)),
            'apellido_paterno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidopaterno)):null,
            'apellido_materno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidomaterno)):null,
            'celular'=> trim(strtoupper($request->celular)),
            'ubicacion'=> trim(strtoupper($request->direccion)),
            'creador_por_usuario'=>auth()->id(),
        ]);

        return response()->json([
            "message"=>"creado con exito"
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $cliente)
    {
        $request->validate([
            'cedulaidentidad' => 'required|string|max:255|unique:clientes,cedula_identidad,'. $cliente,
            'nombres' => 'required|string|max:255',
            'apellidopaterno' => 'nullable|string|max:255|required_without:apellidomaterno',
            'apellidomaterno' => 'nullable|string|max:255|required_without:apellidopaterno',
            'celular' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
        ]);

        $cliente = Cliente::findOrFail($cliente);

        $cliente->update([
            'cedula_identidad'=> trim(strtoupper($request->cedulaidentidad)),
            'nombres'=> trim(strtoupper($request->nombres)),
            'apellido_paterno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidopaterno)):null,
            'apellido_materno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidomaterno)):null,
            'celular'=> trim(strtoupper($request->celular)),
            'ubicacion'=> trim(strtoupper($request->direccion)),
            'creador_por_usuario'=>auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cliente actualizado correctamente.',
        ]);   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cliente)
    {
        $cliente=Cliente::find($cliente);
        $cliente->delete();
        return response()->json([
            "message"=> "Cliente eliminado con exito"
        ]);
    }
}
