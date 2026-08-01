<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use App\Models\NoAtendidos;
use App\Models\RendimientoPersonal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ControlRutasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $asignaciones = Asignacion::query()
                ->with(['cliente', 'ruta', 'usuario'])
                ->orderBy('id_usuario')
                ->orderByRaw('CASE WHEN atencion_fecha_hora IS NULL THEN 0 ELSE 1 END')
                ->orderBy('asignacion_fecha_hora', 'asc');

            return $this->dataTableAsignaciones($dataTables, $asignaciones);
        }

        $vendedores = User::where('estado', 'ACTIVO')->role('vendedor')->orderBy('nombres')->get();
        $estadoPreventistas = $this->construirEstadoPreventistas($vendedores);

        $resumen = [
            'preventistas' => $vendedores->count(),
            'trabajando' => $estadoPreventistas->where('estado_codigo', 'trabajando')->count(),
            'sin_iniciar' => $estadoPreventistas->where('estado_codigo', 'sin_iniciar')->count(),
            'finalizado' => $estadoPreventistas->where('estado_codigo', 'finalizado')->count(),
            'sin_asignaciones' => $estadoPreventistas->where('estado_codigo', 'sin_asignaciones')->count(),
            'clientes_pendientes' => (int) $estadoPreventistas->sum('pendientes'),
            'clientes_con_pedido' => (int) $estadoPreventistas->sum('con_pedido'),
        ];

        return view('administrador.controlrutas.index', compact('vendedores', 'estadoPreventistas', 'resumen'));
    }

    public function indexPreventista(string $idVendedor, DataTables $dataTables)
    {
        $asignaciones = Asignacion::query()
            ->with(['cliente', 'ruta', 'usuario'])
            ->where('id_usuario', $idVendedor)
            ->orderByRaw('CASE WHEN atencion_fecha_hora IS NULL THEN 0 ELSE 1 END')
            ->orderBy('asignacion_fecha_hora', 'asc');

        return $this->dataTableAsignaciones($dataTables, $asignaciones);
    }

    public function cerrarAsignaciones()
    {
        $asignaciones = Asignacion::all();

        if ($asignaciones->isEmpty()) {
            return response()->json(['message' => 'No hay asignaciones para cerrar.'], 404);
        }

        foreach ($asignaciones as $asignacion) {
            RendimientoPersonal::create([
                'id_usuario' => $asignacion->id_usuario,
                'id_cliente' => $asignacion->id_cliente,
                'id_ruta' => $asignacion->id_ruta,
                'numero_pedido' => $asignacion->numero_pedido,
                'asignacion_fecha_hora' => $asignacion->asignacion_fecha_hora,
                'atencion_fecha_hora' => $asignacion->atencion_fecha_hora,
                'estado_pedido' => $asignacion->estado_pedido,
            ]);

            if ($asignacion->atencion_fecha_hora == null) {
                NoAtendidos::create([
                    'id_cliente' => $asignacion->id_cliente,
                ]);
            }
        }

        Asignacion::truncate();

        return response()->json(['message' => 'Ruta de vendedor reseteada exitosamente.'], 200);
    }

    private function dataTableAsignaciones(DataTables $dataTables, $query)
    {
        return $dataTables->eloquent($query)
            ->addColumn('preventista', function ($asignacion) {
                if (! $asignacion->usuario) {
                    return '<span class="text-muted">Sin preventista</span>';
                }

                $nombre = trim($asignacion->usuario->nombres.' '.$asignacion->usuario->apellido_paterno.' '.$asignacion->usuario->apellido_materno);

                return '
                    <div class="route-staff-cell">
                        <strong>'.e($nombre).'</strong>
                        <span>C.I. '.e($asignacion->usuario->cedulaidentidad ?? 'N/A').'</span>
                    </div>
                ';
            })
            ->addColumn('ci', function ($asignacion) {
                return $asignacion->cliente->cedula_identidad ?? 'N/A';
            })
            ->addColumn('nombre_completo', function ($asignacion) {
                if (! $asignacion->cliente) {
                    return '<span class="text-muted">Cliente no encontrado</span>';
                }

                return trim(($asignacion->cliente->nombres ?? '').' '.($asignacion->cliente->apellidos ?? ''));
            })
            ->addColumn('ubicacion', function ($asignacion) {
                if (! $asignacion->cliente) {
                    return 'N/A';
                }

                $calle = $asignacion->cliente->calle_avenida ?: 'Sin calle registrada';
                $zona = $asignacion->cliente->zona_barrio ?: 'Sin zona';

                return '
                    <div class="route-address-cell">
                        <strong>'.e($calle).'</strong>
                        <span>'.e($zona).'</span>
                    </div>
                ';
            })
            ->addColumn('ruta', function ($asignacion) {
                return $asignacion->ruta->nombre_ruta ?? 'N/A';
            })
            ->addColumn('fecha_asignacion', function ($asignacion) {
                if (! $asignacion->asignacion_fecha_hora) {
                    return '<span class="route-pill route-pill-muted">Sin fecha</span>';
                }

                $fecha = Carbon::parse($asignacion->asignacion_fecha_hora)->format('d/m/Y H:i');

                return "<span class='route-pill route-pill-dark'>{$fecha}</span>";
            })
            ->addColumn('fecha_atencion', function ($asignacion) {
                if (! $asignacion->atencion_fecha_hora) {
                    return '<span class="route-pill route-pill-warning">Pendiente</span>';
                }

                $fecha = Carbon::parse($asignacion->atencion_fecha_hora)->format('d/m/Y H:i');

                return "<span class='route-pill route-pill-success'>{$fecha}</span>";
            })
            ->addColumn('pedido', function ($asignacion) {
                if ($asignacion->estado_pedido && $asignacion->numero_pedido) {
                    return "<div class='route-order-cell'><span class='route-pill route-pill-order'>Pedido #{$asignacion->numero_pedido}</span></div>";
                }

                if ($asignacion->atencion_fecha_hora) {
                    return "<div class='route-order-cell'><span class='route-pill route-pill-neutral'>Atendido sin pedido</span></div>";
                }

                return "<div class='route-order-cell'><span class='route-pill route-pill-warning'>Sin registrar atencion</span></div>";
            })
            ->rawColumns(['preventista', 'ubicacion', 'fecha_asignacion', 'fecha_atencion', 'pedido'])
            ->make(true);
    }

    private function construirEstadoPreventistas($vendedores)
    {
        $agregados = Asignacion::query()
            ->selectRaw('id_usuario')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN atencion_fecha_hora IS NULL THEN 1 ELSE 0 END) as pendientes')
            ->selectRaw('SUM(CASE WHEN atencion_fecha_hora IS NOT NULL THEN 1 ELSE 0 END) as atendidos')
            ->selectRaw('SUM(CASE WHEN numero_pedido IS NOT NULL AND estado_pedido = true THEN 1 ELSE 0 END) as con_pedido')
            ->groupBy('id_usuario')
            ->get()
            ->keyBy('id_usuario');

        return $vendedores->map(function ($vendedor) use ($agregados) {
            $datos = $agregados->get($vendedor->id);
            $total = (int) ($datos->total ?? 0);
            $pendientes = (int) ($datos->pendientes ?? 0);
            $atendidos = (int) ($datos->atendidos ?? 0);
            $conPedido = (int) ($datos->con_pedido ?? 0);

            if ($total === 0) {
                $estado = [
                    'codigo' => 'sin_asignaciones',
                    'label' => 'Sin asignaciones',
                    'clase' => 'route-status-empty',
                    'detalle' => 'No tiene clientes cargados en la jornada actual.',
                ];
            } elseif ($pendientes === $total) {
                $estado = [
                    'codigo' => 'sin_iniciar',
                    'label' => 'Sin iniciar',
                    'clase' => 'route-status-idle',
                    'detalle' => 'Aun no registra atenciones en sus clientes.',
                ];
            } elseif ($pendientes === 0) {
                $estado = [
                    'codigo' => 'finalizado',
                    'label' => 'Jornada completada',
                    'clase' => 'route-status-done',
                    'detalle' => 'Atendio todas sus asignaciones registradas.',
                ];
            } else {
                $estado = [
                    'codigo' => 'trabajando',
                    'label' => 'Trabajando',
                    'clase' => 'route-status-working',
                    'detalle' => 'Ya tiene movimiento y aun conserva clientes pendientes.',
                ];
            }

            return (object) [
                'id' => $vendedor->id,
                'nombre' => trim($vendedor->nombres.' '.$vendedor->apellido_paterno.' '.$vendedor->apellido_materno),
                'estado_codigo' => $estado['codigo'],
                'estado_label' => $estado['label'],
                'estado_clase' => $estado['clase'],
                'detalle' => $estado['detalle'],
                'total' => $total,
                'pendientes' => $pendientes,
                'atendidos' => $atendidos,
                'con_pedido' => $conPedido,
            ];
        })->sortBy([
            fn ($item) => match ($item->estado_codigo) {
                'trabajando' => 0,
                'sin_iniciar' => 1,
                'finalizado' => 2,
                default => 3,
            },
            fn ($item) => strtolower($item->nombre),
        ])->values();
    }
}
