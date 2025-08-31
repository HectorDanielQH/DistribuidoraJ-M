<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Imports\ClientesImport;
use App\Models\Cliente;
use App\Models\Rutas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            
            $query = Cliente::query();

            if ($request->filled('nombre')) {
                $keywords = trim(strtoupper($request->nombre));
                $query->where(function ($q) use ($keywords) {
                    $q->where('nombres', 'like', '%' . $keywords . '%')
                      ->orWhere('apellidos', 'like', '%' . $keywords . '%');
                });
            }

            if ($request->filled('ci')) {
                $query->where('cedula_identidad', 'like', '%' . $request->ci . '%');
            }

            return $dataTables->eloquent($query)
                ->addColumn('nombres_completos', function ($cliente) {
                    return trim($cliente->nombres . ' ' . $cliente->apellidos);
                })
                ->addColumn('ruta', function ($cliente) {
                    return $cliente->ruta ? $cliente->ruta->nombre_ruta : 'No asignada';
                })
                ->addColumn('acciones', function ($cliente) {
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                    <button type="button" class="btn btn-primary btn-sm" onclick="editarUsuario(this)"
                        id-cliente="' . $cliente->id . '"
                        id-cliente-cedula="' . $cliente->cedula_identidad . '"
                        id-cliente-nombres="' . $cliente->nombres . '"
                        id-cliente-paterno="' . $cliente->apellido_paterno . '"
                        id-cliente-materno="' . $cliente->apellido_materno . '"
                        id-cliente-celular="' . $cliente->celular . '"
                        id-cliente-ubicacion="' . $cliente->ubicacion . '"
                        id-cliente-ruta="' . $cliente->ruta_id . '"

                        data-toggle="modal" data-target="#modalEditarCliente"
                    >
                        <i class="fas fa-edit"></i> Editar
                    </button>';
                    $botones .= '<button type="button" class="btn btn-danger btn-sm" onclick="eliminarUsuario(this)" id-cliente="' . $cliente->id . '"><i class="fas fa-trash"></i> Eliminar</button>';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->toJson();
        }
        $rutas = Rutas::all();
        return view('administrador.clientes.index_clientes', compact('rutas'));
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
            'ruta' => 'required|exists:rutas,id',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'cedulaidentidad.unique' => 'La Cédula de Identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
            'ruta.required' => 'Debe seleccionar una ruta.',
            'ruta.exists' => 'La ruta seleccionada no es válida.',
        ]);

        Cliente::create([
            'cedula_identidad'=> trim(strtoupper($request->cedulaidentidad)),
            'nombres'=> trim(strtoupper($request->nombres)),
            'apellido_paterno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidopaterno)):null,
            'apellido_materno'=> $request->apellidopaterno ? trim(strtoupper($request->apellidomaterno)):null,
            'celular'=> trim(strtoupper($request->celular)),
            'ubicacion'=> trim(strtoupper($request->direccion)),
            'creador_por_usuario'=>auth()->id(),
            'ruta_id' => $request->ruta,
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
            'ruta' => 'required|exists:rutas,id',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
            'ruta.required' => 'Debe seleccionar una ruta.',
            'ruta.exists' => 'La ruta seleccionada no es válida.',
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
            'ruta_id' => $request->ruta,
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

    public function importarClientes(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'archivo_excel.required' => 'El archivo es obligatorio.',
            'archivo_excel.mimes' => 'El archivo debe ser un archivo de Excel o CSV.',
        ]);

        Excel::import(new ClientesImport, $request->file('archivo_excel'));

        return response()->json([
            'status' => 'success',
            'message' => 'Clientes importados correctamente.',
        ]);
    }

    public function buscarClientes(Request $request)
    {
        $term = trim(strtoupper($request->get('term')));

        $clientes = Cliente::where('nombres', 'LIKE', "%{$term}%")
            ->orWhere('apellidos', 'LIKE', "%{$term}%")
            ->orWhere('cedula_identidad', 'LIKE', "%{$term}%")
            ->get();

        return response()->json($clientes);
    }
}
