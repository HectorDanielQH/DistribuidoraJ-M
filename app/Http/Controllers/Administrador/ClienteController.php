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
            
            $query = Cliente::query()
            ->select([
                'id',
                'nombres',
                'apellidos',
                'celular',
                'calle_avenida',
                'zona_barrio',
                'referencia_direccion',
                'ruta_id',
            ])
            ->with(['ruta:id,nombre_ruta']);

            return $dataTables->eloquent($query)
                ->addColumn('nombres_completos', function ($cliente) {
                    return trim($cliente->nombres . ' ' . $cliente->apellidos);
                })
                ->filterColumn('nombres_completos', function ($query, $keyword) {
                    $keywords = trim(strtoupper($keyword));
                    $query->where(function ($q) use ($keywords) {
                        $q->where('nombres', 'like', '%' . $keywords . '%')
                          ->orWhere('apellidos', 'like', '%' . $keywords . '%');
                    });
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
                        id-cliente-apellidos="' . $cliente->apellidos . '"
                        id-cliente-celular="' . $cliente->celular . '"
                        id-cliente-calleavenida="' . $cliente->calle_avenida . '"
                        id-cliente-zonabarrio="' . $cliente->zona_barrio . '"
                        id-cliente-referenciadireccion="' . $cliente->referencia_direccion . '"
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
            'cedulaidentidad' => 'nullable|string|max:255|unique:clientes,cedula_identidad',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:255',
            'calle_avenida' => 'required|string|max:255',
            'zona_barrio' => 'required|string|max:255',
            'referencia_direccion' => 'nullable|string|max:255',
            'ruta' => 'required|exists:rutas,id',
        ],[
            'cedulaidentidad.unique' => 'La cédula de identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'calle_avenida.required' => 'El campo Calle/Avenida es obligatorio.',
            'zona_barrio.required' => 'El campo Zona/Barrio es obligatorio.',
            'referencia_direccion.required' => 'El campo Referencia es obligatorio.',
            'ruta.required' => 'Debe seleccionar una ruta.',
            'ruta.exists' => 'La ruta seleccionada no es válida.',
        ]);

        Cliente::create([
            'codigo_cliente'=> 'CL'.str_pad(Cliente::max('id')+1, 6, '0', STR_PAD_LEFT),
            'cedula_identidad'=> $request->cedulaidentidad ? trim(strtoupper($request->cedulaidentidad)):null,
            'nombres'=> trim(strtoupper($request->nombres)),
            'apellidos'=> $request->apellidos ? trim(strtoupper($request->apellidos)):null,
            'celular'=> trim(strtoupper($request->celular)),
            'calle_avenida'=> trim(strtoupper($request->calle_avenida)),
            'zona_barrio'=> trim(strtoupper($request->zona_barrio)),
            'referencia_direccion'=> $request->referencia_direccion ? trim(strtoupper($request->referencia_direccion)):null,
            'latitud'=> null,
            'longitud'=> null,
            'ruta_id'=> $request->ruta,
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
    public function update(Request $request, string $cliente_id)
    {
        $request->validate([
            'idcliente' => 'required|exists:clientes,id',
            'cedulaidentidad' => 'nullable|string|max:255|unique:clientes,cedula_identidad,'. $cliente_id,
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:255',
            'calle_avenida' => 'required|string|max:255',
            'zona_barrio' => 'required|string|max:255',
            'referencia_direccion' => 'nullable|string|max:255',
            'ruta' => 'required|exists:rutas,id',
        ],[
            'idcliente.exists' => 'El cliente no existe.',
            'cedulaidentidad.unique' => 'La cédula de identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'calle_avenida.required' => 'El campo Calle/Avenida es obligatorio.',
            'zona_barrio.required' => 'El campo Zona/Barrio es obligatorio.',
            'referencia_direccion.required' => 'El campo Referencia es obligatorio.',
            'ruta.required' => 'Debe seleccionar una ruta.',
            'ruta.exists' => 'La ruta seleccionada no es válida.',
        ]);

        $cliente = Cliente::findOrFail($cliente_id);

        $cliente->update([
            'cedula_identidad'=> $request->cedulaidentidad ? trim(strtoupper($request->cedulaidentidad)):null,
            'nombres'=> trim(strtoupper($request->nombres)),
            'apellidos'=> $request->apellidos ? trim(strtoupper($request->apellidos)):null,
            'celular'=> trim(strtoupper($request->celular)),
            'calle_avenida'=> trim(strtoupper($request->calle_avenida)),
            'zona_barrio'=> trim(strtoupper($request->zona_barrio)),
            'referencia_direccion'=> $request->referencia_direccion ? trim(strtoupper($request->referencia_direccion)):null,
            'ruta_id'=> $request->ruta,
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
