<?php

namespace App\Http\Controllers;

use App\Models\RendimientoPersonal;
use App\Models\Rutas;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RendimientoPersonalController extends Controller
{
    public function index()
    {
        $personal = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('administrador.reportes.rendimiento', compact('personal', 'rutas'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    public function edit(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    public function update(Request $request, RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    public function destroy(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    public function panelData(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'periodo' => 'nullable|in:dia,semana,mes',
            'preventista_id' => 'nullable|exists:users,id',
            'ruta_id' => 'nullable|exists:rutas,id',
        ]);

        $fechaInicio = !empty($validated['fecha_inicio'])
            ? Carbon::parse($validated['fecha_inicio'])->startOfDay()
            : now()->startOfMonth();
        $fechaFin = !empty($validated['fecha_fin'])
            ? Carbon::parse($validated['fecha_fin'])->endOfDay()
            : now()->endOfDay();

        if ($fechaInicio->gt($fechaFin)) {
            return response()->json([
                'message' => 'La fecha de inicio no puede ser mayor que la fecha fin.',
            ], 422);
        }

        $periodo = $validated['periodo'] ?? 'dia';
        $preventistaId = isset($validated['preventista_id']) ? (int) $validated['preventista_id'] : null;
        $rutaId = isset($validated['ruta_id']) ? (int) $validated['ruta_id'] : null;

        $rankingPreventistas = $this->obtenerRankingPreventistas($fechaInicio, $fechaFin, $preventistaId, $rutaId);
        $resumen = $this->obtenerResumenGeneral($fechaInicio, $fechaFin, $preventistaId, $rutaId);
        $series = $this->obtenerSeriesDashboard($fechaInicio, $fechaFin, $periodo, $preventistaId, $rutaId);
        $rutasResumen = $this->obtenerResumenRutas($fechaInicio, $fechaFin, $preventistaId, $rutaId);
        $alertas = $this->obtenerAlertasRRHH($rankingPreventistas, $resumen);
        $preventistaDestacado = $preventistaId
            ? $rankingPreventistas->firstWhere('id', $preventistaId)
            : $rankingPreventistas->first();

        return response()->json([
            'filtros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'periodo' => $periodo,
                'preventista_id' => $preventistaId,
                'ruta_id' => $rutaId,
            ],
            'resumen' => $resumen,
            'series' => $series,
            'ranking' => $rankingPreventistas->values(),
            'rutas' => $rutasResumen->values(),
            'alertas' => $alertas,
            'preventista_destacado' => $preventistaDestacado,
        ]);
    }

    public function rendimientoPersonal(Request $request)
    {
        $personal = User::role('vendedor')->find($request->id);
        $periodo = $request->periodo;

        if (!$personal) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($periodo == 'dias') {
            $atencion = RendimientoPersonal::selectRaw('DATE(atencion_fecha_hora) as dia, COUNT(*) as total')
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$request->fechaInicio, $request->fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw('DATE(atencion_fecha_hora)')
                ->orderBy('dia')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }

        if ($periodo == 'semanas') {
            [$anioInicio, $semanaInicio] = explode('-W', $request->semanaInicio);
            [$anioFin, $semanaFin] = explode('-W', $request->semanaFin);

            $fechaInicio = Carbon::now()->setISODate($anioInicio, $semanaInicio)->startOfWeek();
            $fechaFin = Carbon::now()->setISODate($anioFin, $semanaFin)->endOfWeek();

            $atencion = RendimientoPersonal::selectRaw("TO_CHAR(atencion_fecha_hora, 'IYYY-IW') as semana, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("TO_CHAR(atencion_fecha_hora, 'IYYY-IW')")
                ->orderBy('semana')
                ->get();

            return response()->json(['fechas' => $atencion]);
        }

        if ($periodo == 'meses') {
            $fechaInicio = Carbon::parse($request->mesInicio . '-01')->startOfMonth();
            $fechaFin = Carbon::parse($request->mesFin . '-01')->endOfMonth();

            $atencion = RendimientoPersonal::selectRaw("TO_CHAR(atencion_fecha_hora, 'YYYY-MM') as mes, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("TO_CHAR(atencion_fecha_hora, 'YYYY-MM')")
                ->orderBy('mes')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }

        if ($periodo == 'anios') {
            $anioInicio = (int) $request->anioInicio;
            $anioFin = (int) $request->anioFin;
            $fechaInicio = Carbon::create($anioInicio)->startOfYear();
            $fechaFin = Carbon::create($anioFin)->endOfYear();

            $atencion = RendimientoPersonal::selectRaw("EXTRACT(YEAR FROM atencion_fecha_hora) as anio, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("EXTRACT(YEAR FROM atencion_fecha_hora)")
                ->orderBy('anio')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }

        return response()->json(['error' => 'Periodo no valido'], 400);
    }

    private function ventasBaseQuery(Carbon $fechaInicio, Carbon $fechaFin, ?int $preventistaId = null, ?int $rutaId = null)
    {
        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio, $fechaFin]);

        if ($preventistaId) {
            $query->where('ventas.id_usuario', $preventistaId);
        }

        if ($rutaId) {
            $query->where('clientes.ruta_id', $rutaId);
        }

        return $query;
    }

    private function rendimientoBaseQuery(Carbon $fechaInicio, Carbon $fechaFin, ?int $preventistaId = null, ?int $rutaId = null)
    {
        $query = RendimientoPersonal::query()
            ->leftJoin('clientes', 'rendimiento_personals.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'rendimiento_personals.id_ruta', '=', 'rutas.id')
            ->leftJoin('users', 'rendimiento_personals.id_usuario', '=', 'users.id')
            ->whereBetween('rendimiento_personals.asignacion_fecha_hora', [$fechaInicio, $fechaFin]);

        if ($preventistaId) {
            $query->where('rendimiento_personals.id_usuario', $preventistaId);
        }

        if ($rutaId) {
            $query->where('rendimiento_personals.id_ruta', $rutaId);
        }

        return $query;
    }

    private function ingresoExpr(): string
    {
        return '(ventas.cantidad * COALESCE(ventas.precio_unitario, forma_ventas.precio_venta) * (1 - COALESCE(ventas.descripcion_descuento_porcentaje, 0) / 100.0))';
    }

    private function unidadesExpr(): string
    {
        return '(ventas.cantidad * forma_ventas.equivalencia_cantidad)';
    }

    private function obtenerResumenGeneral(Carbon $fechaInicio, Carbon $fechaFin, ?int $preventistaId = null, ?int $rutaId = null): array
    {
        $ventas = $this->ventasBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw('COUNT(DISTINCT ventas.id_usuario) AS preventistas_activos')
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos_contabilizados')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes_con_venta')
            ->selectRaw("COALESCE(SUM({$this->ingresoExpr()}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$this->unidadesExpr()}), 0) AS unidades_vendidas")
            ->first();

        $rendimiento = $this->rendimientoBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw('COUNT(*) AS clientes_asignados')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NOT NULL) AS clientes_atendidos')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.estado_pedido = true) AS clientes_con_pedido')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NULL) AS clientes_pendientes')
            ->selectRaw('COUNT(DISTINCT rendimiento_personals.id_ruta) AS rutas_cubiertas')
            ->first();

        $totalPreventistas = $preventistaId ? 1 : User::role('vendedor')->count();

        $ticketPromedio = (int) ($ventas->pedidos_contabilizados ?? 0) > 0
            ? (float) $ventas->ventas_netas / (int) $ventas->pedidos_contabilizados
            : 0;

        $ventaPromedioPorPreventista = (int) $totalPreventistas > 0
            ? (float) $ventas->ventas_netas / (int) $totalPreventistas
            : 0;

        $efectividadAtencion = (int) ($rendimiento->clientes_asignados ?? 0) > 0
            ? ((int) $rendimiento->clientes_atendidos / (int) $rendimiento->clientes_asignados) * 100
            : 0;

        $conversionPedido = (int) ($rendimiento->clientes_atendidos ?? 0) > 0
            ? ((int) $rendimiento->clientes_con_pedido / (int) $rendimiento->clientes_atendidos) * 100
            : 0;

        return [
            'preventistas_activos' => (int) ($ventas->preventistas_activos ?? 0),
            'total_preventistas' => (int) $totalPreventistas,
            'pedidos_contabilizados' => (int) ($ventas->pedidos_contabilizados ?? 0),
            'clientes_con_venta' => (int) ($ventas->clientes_con_venta ?? 0),
            'clientes_asignados' => (int) ($rendimiento->clientes_asignados ?? 0),
            'clientes_atendidos' => (int) ($rendimiento->clientes_atendidos ?? 0),
            'clientes_con_pedido' => (int) ($rendimiento->clientes_con_pedido ?? 0),
            'clientes_pendientes' => (int) ($rendimiento->clientes_pendientes ?? 0),
            'rutas_cubiertas' => (int) ($rendimiento->rutas_cubiertas ?? 0),
            'ventas_netas' => round((float) ($ventas->ventas_netas ?? 0), 2),
            'unidades_vendidas' => round((float) ($ventas->unidades_vendidas ?? 0), 2),
            'ticket_promedio' => round($ticketPromedio, 2),
            'venta_promedio_por_preventista' => round($ventaPromedioPorPreventista, 2),
            'efectividad_atencion' => round($efectividadAtencion, 2),
            'conversion_pedido' => round($conversionPedido, 2),
        ];
    }

    private function obtenerRankingPreventistas(Carbon $fechaInicio, Carbon $fechaFin, ?int $preventistaId = null, ?int $rutaId = null)
    {
        $ventasPorUsuario = $this->ventasBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw('ventas.id_usuario')
            ->selectRaw("COALESCE(SUM({$this->ingresoExpr()}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$this->unidadesExpr()}), 0) AS unidades_vendidas")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes_con_venta')
            ->groupBy('ventas.id_usuario');

        $rendimientoPorUsuario = $this->rendimientoBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw('rendimiento_personals.id_usuario')
            ->selectRaw('COUNT(*) AS clientes_asignados')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NOT NULL) AS clientes_atendidos')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.estado_pedido = true) AS clientes_con_pedido')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NULL) AS clientes_pendientes')
            ->selectRaw('COUNT(DISTINCT rendimiento_personals.id_ruta) AS rutas_cubiertas')
            ->groupBy('rendimiento_personals.id_usuario');

        $query = User::query()
            ->role('vendedor')
            ->leftJoinSub($ventasPorUsuario, 'ventas_usuario', function ($join) {
                $join->on('users.id', '=', 'ventas_usuario.id_usuario');
            })
            ->leftJoinSub($rendimientoPorUsuario, 'rendimiento_usuario', function ($join) {
                $join->on('users.id', '=', 'rendimiento_usuario.id_usuario');
            })
            ->select('users.id', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno', 'users.cedulaidentidad', 'users.foto_perfil')
            ->selectRaw('COALESCE(ventas_usuario.ventas_netas, 0) AS ventas_netas')
            ->selectRaw('COALESCE(ventas_usuario.unidades_vendidas, 0) AS unidades_vendidas')
            ->selectRaw('COALESCE(ventas_usuario.pedidos, 0) AS pedidos')
            ->selectRaw('COALESCE(ventas_usuario.clientes_con_venta, 0) AS clientes_con_venta')
            ->selectRaw('COALESCE(rendimiento_usuario.clientes_asignados, 0) AS clientes_asignados')
            ->selectRaw('COALESCE(rendimiento_usuario.clientes_atendidos, 0) AS clientes_atendidos')
            ->selectRaw('COALESCE(rendimiento_usuario.clientes_con_pedido, 0) AS clientes_con_pedido')
            ->selectRaw('COALESCE(rendimiento_usuario.clientes_pendientes, 0) AS clientes_pendientes')
            ->selectRaw('COALESCE(rendimiento_usuario.rutas_cubiertas, 0) AS rutas_cubiertas');

        if ($preventistaId) {
            $query->where('users.id', $preventistaId);
        }

        return $query
            ->orderByDesc('ventas_netas')
            ->get()
            ->map(function ($fila, $index) {
                $ticketPromedio = (int) $fila->pedidos > 0
                    ? (float) $fila->ventas_netas / (int) $fila->pedidos
                    : 0;
                $efectividad = (int) $fila->clientes_asignados > 0
                    ? ((int) $fila->clientes_atendidos / (int) $fila->clientes_asignados) * 100
                    : 0;
                $conversion = (int) $fila->clientes_atendidos > 0
                    ? ((int) $fila->clientes_con_pedido / (int) $fila->clientes_atendidos) * 100
                    : 0;

                return [
                    'posicion' => $index + 1,
                    'id' => (int) $fila->id,
                    'nombre' => trim(collect([
                        $fila->nombres,
                        $fila->apellido_paterno,
                        $fila->apellido_materno,
                    ])->filter()->implode(' ')),
                    'cedula' => $fila->cedulaidentidad ?: 'S/D',
                    'foto_url' => $fila->foto_perfil ? route('usuarios.imagenperfil', $fila->id) : asset('images/logo_profile.webp'),
                    'ventas_netas' => round((float) $fila->ventas_netas, 2),
                    'unidades_vendidas' => round((float) $fila->unidades_vendidas, 2),
                    'pedidos' => (int) $fila->pedidos,
                    'clientes_con_venta' => (int) $fila->clientes_con_venta,
                    'clientes_asignados' => (int) $fila->clientes_asignados,
                    'clientes_atendidos' => (int) $fila->clientes_atendidos,
                    'clientes_con_pedido' => (int) $fila->clientes_con_pedido,
                    'clientes_pendientes' => (int) $fila->clientes_pendientes,
                    'rutas_cubiertas' => (int) $fila->rutas_cubiertas,
                    'ticket_promedio' => round($ticketPromedio, 2),
                    'efectividad_atencion' => round($efectividad, 2),
                    'conversion_pedido' => round($conversion, 2),
                ];
            });
    }

    private function obtenerSeriesDashboard(Carbon $fechaInicio, Carbon $fechaFin, string $periodo, ?int $preventistaId = null, ?int $rutaId = null): array
    {
        $agrupacionVentas = match ($periodo) {
            'semana' => "TO_CHAR(ventas.fecha_contabilizacion, 'IYYY-IW')",
            'mes' => "TO_CHAR(ventas.fecha_contabilizacion, 'YYYY-MM')",
            default => "TO_CHAR(DATE(ventas.fecha_contabilizacion), 'YYYY-MM-DD')",
        };

        $agrupacionRendimiento = match ($periodo) {
            'semana' => "TO_CHAR(rendimiento_personals.asignacion_fecha_hora, 'IYYY-IW')",
            'mes' => "TO_CHAR(rendimiento_personals.asignacion_fecha_hora, 'YYYY-MM')",
            default => "TO_CHAR(DATE(rendimiento_personals.asignacion_fecha_hora), 'YYYY-MM-DD')",
        };

        $ventasSerie = $this->ventasBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw("{$agrupacionVentas} AS periodo")
            ->selectRaw("COALESCE(SUM({$this->ingresoExpr()}), 0) AS ventas_netas")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->groupBy(DB::raw($agrupacionVentas))
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $rendimientoSerie = $this->rendimientoBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw("{$agrupacionRendimiento} AS periodo")
            ->selectRaw('COUNT(*) AS asignados')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NOT NULL) AS atendidos')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.estado_pedido = true) AS con_pedido')
            ->groupBy(DB::raw($agrupacionRendimiento))
            ->orderBy('periodo')
            ->get()
            ->keyBy('periodo');

        $periodos = collect($ventasSerie->keys())
            ->merge($rendimientoSerie->keys())
            ->unique()
            ->sort()
            ->values();

        return [
            'categorias' => $periodos->all(),
            'ventas' => $periodos->map(fn ($periodoItem) => round((float) optional($ventasSerie->get($periodoItem))->ventas_netas, 2))->all(),
            'pedidos' => $periodos->map(fn ($periodoItem) => (int) optional($ventasSerie->get($periodoItem))->pedidos)->all(),
            'asignados' => $periodos->map(fn ($periodoItem) => (int) optional($rendimientoSerie->get($periodoItem))->asignados)->all(),
            'atendidos' => $periodos->map(fn ($periodoItem) => (int) optional($rendimientoSerie->get($periodoItem))->atendidos)->all(),
            'con_pedido' => $periodos->map(fn ($periodoItem) => (int) optional($rendimientoSerie->get($periodoItem))->con_pedido)->all(),
        ];
    }

    private function obtenerResumenRutas(Carbon $fechaInicio, Carbon $fechaFin, ?int $preventistaId = null, ?int $rutaId = null)
    {
        return $this->rendimientoBaseQuery($fechaInicio, $fechaFin, $preventistaId, $rutaId)
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw('COUNT(*) AS asignados')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.atencion_fecha_hora IS NOT NULL) AS atendidos')
            ->selectRaw('COUNT(*) FILTER (WHERE rendimiento_personals.estado_pedido = true) AS con_pedido')
            ->groupBy('rutas.nombre_ruta')
            ->orderByDesc('con_pedido')
            ->get()
            ->map(function ($fila) {
                $efectividad = (int) $fila->asignados > 0
                    ? ((int) $fila->atendidos / (int) $fila->asignados) * 100
                    : 0;

                return [
                    'ruta' => $fila->ruta,
                    'asignados' => (int) $fila->asignados,
                    'atendidos' => (int) $fila->atendidos,
                    'con_pedido' => (int) $fila->con_pedido,
                    'efectividad' => round($efectividad, 2),
                ];
            });
    }

    private function obtenerAlertasRRHH($rankingPreventistas, array $resumen): array
    {
        $sinVentas = $rankingPreventistas
            ->filter(fn ($fila) => (float) $fila['ventas_netas'] <= 0)
            ->pluck('nombre')
            ->values()
            ->all();

        $bajaConversion = $rankingPreventistas
            ->filter(fn ($fila) => (float) $fila['clientes_atendidos'] > 0 && (float) $fila['conversion_pedido'] < 40)
            ->sortBy('conversion_pedido')
            ->take(5)
            ->map(fn ($fila) => [
                'nombre' => $fila['nombre'],
                'conversion_pedido' => $fila['conversion_pedido'],
            ])
            ->values()
            ->all();

        $pendientesAltos = $rankingPreventistas
            ->filter(fn ($fila) => (int) $fila['clientes_pendientes'] >= 5)
            ->sortByDesc('clientes_pendientes')
            ->take(5)
            ->map(fn ($fila) => [
                'nombre' => $fila['nombre'],
                'clientes_pendientes' => $fila['clientes_pendientes'],
            ])
            ->values()
            ->all();

        return [
            [
                'titulo' => 'Cobertura operativa',
                'descripcion' => $resumen['clientes_asignados'] > 0
                    ? $resumen['clientes_atendidos'] . ' de ' . $resumen['clientes_asignados'] . ' clientes asignados fueron atendidos en el periodo.'
                    : 'No existen asignaciones registradas en el periodo seleccionado.',
                'nivel' => $resumen['efectividad_atencion'] >= 75 ? 'positivo' : 'atencion',
            ],
            [
                'titulo' => 'Preventistas sin venta',
                'descripcion' => empty($sinVentas)
                    ? 'Todos los preventistas filtrados registran al menos una venta contabilizada.'
                    : 'Sin venta registrada: ' . implode(', ', $sinVentas) . '.',
                'nivel' => empty($sinVentas) ? 'positivo' : 'critico',
            ],
            [
                'titulo' => 'Conversion de pedido',
                'descripcion' => empty($bajaConversion)
                    ? 'No se detectaron preventistas con conversion menor al 40%.'
                    : collect($bajaConversion)->map(fn ($fila) => $fila['nombre'] . ' (' . $fila['conversion_pedido'] . '%)')->implode(', ') . '.',
                'nivel' => empty($bajaConversion) ? 'positivo' : 'atencion',
            ],
            [
                'titulo' => 'Clientes pendientes',
                'descripcion' => empty($pendientesAltos)
                    ? 'No se observan acumulaciones criticas de clientes pendientes.'
                    : collect($pendientesAltos)->map(fn ($fila) => $fila['nombre'] . ' (' . $fila['clientes_pendientes'] . ')')->implode(', ') . '.',
                'nivel' => empty($pendientesAltos) ? 'positivo' : 'critico',
            ],
        ];
    }
}
