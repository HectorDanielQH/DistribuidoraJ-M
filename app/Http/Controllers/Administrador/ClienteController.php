<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Exports\ClientesReporteExport;
use App\Imports\ClientesImport;
use App\Models\Cliente;
use App\Models\Rutas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ClienteController extends Controller
{
    private const COLUMNAS_REPORTE = [
        'codigo_cliente' => 'Codigo cliente',
        'cedula_identidad' => 'C.I.',
        'nombres_completos' => 'Nombre completo',
        'nombres' => 'Nombres',
        'apellidos' => 'Apellidos',
        'celular' => 'Celular',
        'calle_avenida' => 'Direccion',
        'zona_barrio' => 'Zona/Barrio',
        'referencia_direccion' => 'Referencia',
        'ruta' => 'Ruta',
    ];

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
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $columnasReporte = self::COLUMNAS_REPORTE;

        return view('administrador.clientes.index_clientes', compact('rutas', 'columnasReporte'));
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

        $clientes_y_rutas=Cliente::with('ruta')->where('nombres', 'ILIKE', "%{$term}%")
            ->orWhere('apellidos', 'ILIKE', "%{$term}%")
            ->get();

        return response()->json($clientes_y_rutas);
    }

    public function exportarReporte(Request $request)
    {
        $validated = $request->validate([
            'ruta_ids' => 'nullable|array',
            'ruta_ids.*' => 'exists:rutas,id',
            'columnas' => 'required|array|min:1',
            'columnas.*' => 'in:' . implode(',', array_keys(self::COLUMNAS_REPORTE)),
            'formato' => 'required|in:excel,pdf',
        ], [
            'columnas.required' => 'Debes seleccionar al menos una columna para el reporte.',
            'columnas.min' => 'Debes seleccionar al menos una columna para el reporte.',
            'formato.required' => 'Debes indicar el formato de exportacion.',
            'formato.in' => 'El formato de exportacion solicitado no es valido.',
        ]);

        $clientes = $this->clientesReporteQuery($request)->get();
        $columnas = array_values($validated['columnas']);
        $encabezados = collect($columnas)
            ->map(fn ($columna) => self::COLUMNAS_REPORTE[$columna])
            ->all();
        $filas = $this->mapearFilasReporte($clientes, $columnas);
        $rutasSeleccionadas = Rutas::query()
            ->whereIn('id', collect($validated['ruta_ids'] ?? [])->filter()->all())
            ->orderBy('nombre_ruta')
            ->pluck('nombre_ruta')
            ->all();
        $nombreBase = 'reporte_clientes_rutas_' . now()->format('Ymd_His');

        if ($validated['formato'] === 'excel') {
            return Excel::download(
                new ClientesReporteExport($encabezados, $filas),
                $nombreBase . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('administrador.pdf.pdf_clientes_por_rutas', [
            'titulo' => 'Reporte de clientes por rutas',
            'encabezados' => $encabezados,
            'filas' => $filas,
            'columnas' => $columnas,
            'rutasSeleccionadas' => $rutasSeleccionadas,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($nombreBase . '.pdf');
    }

    private function clientesReporteQuery(Request $request)
    {
        $rutaIds = collect((array) $request->input('ruta_ids', []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return Cliente::query()
            ->with('ruta:id,nombre_ruta')
            ->when(!empty($rutaIds), function ($query) use ($rutaIds) {
                $query->whereIn('ruta_id', $rutaIds);
            })
            ->orderBy('ruta_id')
            ->orderBy('nombres')
            ->orderBy('apellidos');
    }

    private function mapearFilasReporte($clientes, array $columnas): array
    {
        return $clientes->map(function ($cliente) use ($columnas) {
            $fila = [];

            foreach ($columnas as $columna) {
                $fila[] = match ($columna) {
                    'codigo_cliente' => $cliente->codigo_cliente,
                    'cedula_identidad' => $cliente->cedula_identidad,
                    'nombres_completos' => trim($cliente->nombres . ' ' . $cliente->apellidos),
                    'nombres' => $cliente->nombres,
                    'apellidos' => $cliente->apellidos,
                    'celular' => $cliente->celular,
                    'calle_avenida' => $cliente->calle_avenida,
                    'zona_barrio' => $cliente->zona_barrio,
                    'referencia_direccion' => $cliente->referencia_direccion,
                    'ruta' => optional($cliente->ruta)->nombre_ruta ?: 'No asignada',
                    default => '',
                };
            }

            return $fila;
        })->all();
    }
}
