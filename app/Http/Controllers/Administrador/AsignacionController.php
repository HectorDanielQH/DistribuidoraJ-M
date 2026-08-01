<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use App\Models\Cliente;
use App\Models\NoAtendidos;
use App\Models\RendimientoPersonal;
use App\Models\Rutas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $vendedor = User::query()->where('estado', 'ACTIVO')->role('vendedor');

            return $dataTables->eloquent($vendedor)
                ->addColumn('nombre_completo', function ($vendedor) {
                    return $vendedor->nombres.' '.$vendedor->apellido_paterno.' '.$vendedor->apellido_materno;
                })
                ->filterColumn('nombre_completo', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) ILIKE ?", ["%{$keyword}%"]);
                })
                ->addColumn('asignacion', function ($vendedor) {
                    $asignaciones = Asignacion::where('id_usuario', $vendedor->id)->count();

                    return $asignaciones > 0
                        ? '<span class="assignment-badge assignment-badge-active"><i class="fas fa-users"></i> '.$asignaciones.' clientes</span>'
                        : '<span class="assignment-badge assignment-badge-empty"><i class="fas fa-user-slash"></i> Sin asignacion</span>';
                })
                ->addColumn('action', function ($vendedor) {
                    return "
                        <div class='assignment-actions'>
                            <button class='btn btn-info btn-sm assignment-action-btn' title='Ver clientes asignados' data-bs-target='#visualizarClientes' data-bs-toggle='modal' data-id='{$vendedor->id}' onclick='clientesAsignados(this)'>
                                <i class='fas fa-users'></i>
                            </button>
                            <button class='btn btn-secondary btn-sm assignment-action-btn' title='Ver rutas asignadas' data-bs-target='#visualizarRutas' data-bs-toggle='modal' data-id='{$vendedor->id}' onclick='rutasAsignadas(this)'>
                                <i class='fas fa-route'></i>
                            </button>
                            <button class='btn btn-success btn-sm assignment-action-btn' title='Asignar rutas masivamente' data-bs-target='#asignarCliente' data-bs-toggle='modal' data-id='{$vendedor->id}' onclick='valordeusuariovendedor(this)'>
                                <i class='fas fa-random'></i>
                            </button>
                            <button class='btn btn-warning btn-sm assignment-action-btn' title='Asignar clientes puntuales' data-bs-target='#asignarClienteUnitario' data-bs-toggle='modal' data-id='{$vendedor->id}' onclick='agregarClienteUnitario(this)'>
                                <i class='fas fa-user-plus'></i>
                            </button>
                        </div>
                    ";
                })
                ->rawColumns(['action', 'asignacion'])
                ->make(true);
        }

        $vendedores = User::where('estado', 'ACTIVO')->role('vendedor')->get();
        $rutas = Rutas::all();
        $no_atendidos = NoAtendidos::all();

        return view('administrador.asignacion.index_asignacion', compact('vendedores', 'rutas', 'no_atendidos'));
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
            'id_vendedor' => 'required|exists:users,id',
            'rutas' => 'required|array',
            'rutas.*' => 'exists:rutas,id',
        ]);

        $resultado = DB::transaction(function () use ($request) {
            $rutas = collect($request->rutas)
                ->map(fn ($ruta) => (int) $ruta)
                ->unique()
                ->values();

            $clientes = Cliente::whereIn('ruta_id', $rutas)->get(['id', 'ruta_id']);
            $clientesYaAsignados = Asignacion::where('id_usuario', $request->id_vendedor)
                ->whereIn('id_cliente', $clientes->pluck('id')->all())
                ->pluck('id_cliente')
                ->map(fn ($id) => (int) $id)
                ->all();

            $clientesYaAsignadosLookup = array_flip($clientesYaAsignados);
            $insertados = 0;
            $omitidos = 0;

            foreach ($clientes as $cliente) {
                if (isset($clientesYaAsignadosLookup[(int) $cliente->id])) {
                    $omitidos++;
                    continue;
                }

                Asignacion::create([
                    'id_usuario' => $request->id_vendedor,
                    'id_cliente' => $cliente->id,
                    'id_ruta' => $cliente->ruta_id,
                    'numero_pedido' => null,
                    'asignacion_fecha_hora' => now(),
                    'atencion_fecha_hora' => null,
                    'estado_pedido' => false,
                ]);

                $clientesYaAsignadosLookup[(int) $cliente->id] = true;
                $insertados++;
            }

            return [
                'insertados' => $insertados,
                'omitidos' => $omitidos,
            ];
        });

        return response()->json([
            'message' => $resultado['insertados'] > 0
                ? 'Asignaciones creadas exitosamente.'
                : 'No se agregaron nuevas asignaciones porque esos clientes ya estaban asignados al preventista.',
            'insertados' => $resultado['insertados'],
            'omitidos' => $resultado['omitidos'],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Asignacion $asignacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asignacion $asignacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asignacion $asignacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }

    public function clientesAsignadosAVendedoresEliminar(string $id_cliente, string $id_vendedor)
    {
        $asignacion = Asignacion::where('id_cliente', $id_cliente)
            ->where('id_usuario', $id_vendedor)
            ->first();

        if ($asignacion) {
            $asignacion->delete();

            return response()->json(['message' => 'Asignacion eliminada exitosamente.'], 200);
        }

        return response()->json(['message' => 'Asignacion no encontrada.'], 404);
    }

    public function RutasNoAsignadosAVendedores(Request $request)
    {
        $rutasNoAsignados = Rutas::query()
            ->when(
                $request->filled('id_vendedor'),
                function ($query) use ($request) {
                    $query->whereDoesntHave('asignaciones', function ($subquery) use ($request) {
                        $subquery->where('id_usuario', $request->id_vendedor);
                    });
                },
                function ($query) {
                    $query->whereDoesntHave('asignaciones');
                }
            )
            ->orderBy('nombre_ruta')
            ->get();

        return response()->json($rutasNoAsignados);
    }

    public function clientesAsignadosAVendedores(string $id_vendedor)
    {
        $clientesAsignados = Cliente::whereHas('asignaciones', function ($query) use ($id_vendedor) {
            $query->where('id_usuario', $id_vendedor);
        });

        return DataTables::of($clientesAsignados)
            ->addColumn('nombre_completo', function ($cliente) {
                return $cliente->nombres.' '.$cliente->apellidos;
            })
            ->filterColumn('nombre_completo', function ($query, $keyword) {
                $keyword = trim(strtoupper($keyword));
                $query->whereRaw("CONCAT(nombres, ' ', apellidos) ILIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('nombre_ruta', function ($cliente) {
                return $cliente->ruta ? $cliente->ruta->nombre_ruta : 'Sin ruta';
            })
            ->make(true);
    }

    public function obtenerVendedoresRuta(string $id_vendedor)
    {
        $conjuntoTablas = Asignacion::join('clientes', 'asignacions.id_cliente', '=', 'clientes.id')
            ->join('users', 'asignacions.id_usuario', '=', 'users.id')
            ->where('asignacions.id_usuario', $id_vendedor)
            ->select('clientes.*', 'asignacions.*')
            ->get();

        return response()->json($conjuntoTablas);
    }

    public function rutasAsignadasAVendedores(string $id_vendedor)
    {
        $rutasAsignadas = Rutas::whereHas('asignaciones', function ($query) use ($id_vendedor) {
            $query->where('id_usuario', $id_vendedor);
        });

        return DataTables::of($rutasAsignadas)
            ->addColumn('nombre_ruta', function ($ruta) {
                return $ruta->nombre_ruta;
            })
            ->filterColumn('nombre_ruta', function ($query, $keyword) {
                $query->whereRaw('nombre_ruta LIKE ?', ["%{$keyword}%"]);
            })
            ->addColumn('numero_clientes', function ($ruta) use ($id_vendedor) {
                return Asignacion::where('id_ruta', $ruta->id)
                    ->where('id_usuario', $id_vendedor)
                    ->count();
            })
            ->addColumn('action', function ($ruta) use ($id_vendedor) {
                return "
                    <button class='btn btn-danger btn-sm' data-id='{$ruta->id}' data-id-vendedor='{$id_vendedor}' onclick='eliminarRutaAsignada(this)'>
                        <i class='fas fa-trash'></i>
                    </button>
                ";
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function rutasAsignadasAVendedoresEliminar(Request $request, string $id_ruta)
    {
        $request->validate([
            'id_vendedor' => 'required|exists:users,id',
        ]);

        $asignacion = Asignacion::where('id_ruta', $id_ruta)
            ->where('id_usuario', $request->id_vendedor)
            ->delete();

        if ($asignacion) {
            return response()->json(['message' => 'Asignacion de ruta eliminada exitosamente.'], 200);
        }

        return response()->json(['message' => 'Asignacion de ruta no encontrada.'], 404);
    }

    public function clientesUnitarios(Request $request)
    {
        $request->validate([
            'id_vendedor' => 'required|exists:users,id',
            'clientes' => 'required|array',
            'clientes.*' => 'exists:clientes,id',
        ]);

        foreach ($request->clientes as $cliente) {
            $cliente = Cliente::find($cliente);

            if (! Asignacion::where('id_usuario', $request->id_vendedor)->where('id_cliente', $cliente->id)->exists()) {
                Asignacion::create([
                    'id_usuario' => $request->id_vendedor,
                    'id_cliente' => $cliente->id,
                    'id_ruta' => $cliente->ruta_id,
                    'numero_pedido' => null,
                    'asignacion_fecha_hora' => now(),
                    'atencion_fecha_hora' => null,
                    'estado_pedido' => false,
                ])->save();
            }
        }

        return response()->json([
            'message' => 'Clientes asignados exitosamente.',
        ], 201);
    }
}
