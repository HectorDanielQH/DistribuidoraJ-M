<?php

namespace App\Http\Controllers\Contabilidad;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rutas;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ContabilidadVentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:contador.permisos');
    }

    public function dashboard(Request $request){
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('Contabilidad.dashboard.index', compact('preventistas', 'rutas'));
    }

    public function ventasPorDia(Request $request){
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('Contabilidad.VentasPorDia.index', compact('preventistas', 'rutas'));
    }

    public function ventasPorDiaResumenPanel(Request $request)
    {
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : now()->startOfMonth();
        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : now()->endOfDay();

        $ingresoExpr = $this->dashboardIngresoExpr();
        $query = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin);

        $resumen = (clone $query)
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total_vendido")
            ->selectRaw('COUNT(DISTINCT DATE(ventas.fecha_contabilizacion)) AS dias_con_venta')
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->selectRaw('COUNT(DISTINCT ventas.id_usuario) AS preventistas')
            ->first();

        $mejorDia = (clone $query)
            ->selectRaw('DATE(ventas.fecha_contabilizacion) AS fecha')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy(DB::raw('DATE(ventas.fecha_contabilizacion)'))
            ->orderByDesc('total')
            ->first();

        $promedioDia = (float) ($resumen->dias_con_venta ?? 0) > 0
            ? ((float) $resumen->total_vendido / (float) $resumen->dias_con_venta)
            : 0;

        return response()->json([
            'total_vendido' => round((float) ($resumen->total_vendido ?? 0), 2),
            'dias_con_venta' => (int) ($resumen->dias_con_venta ?? 0),
            'pedidos' => (int) ($resumen->pedidos ?? 0),
            'clientes' => (int) ($resumen->clientes ?? 0),
            'preventistas' => (int) ($resumen->preventistas ?? 0),
            'promedio_dia' => round($promedioDia, 2),
            'mejor_dia' => [
                'fecha' => $mejorDia?->fecha ? Carbon::parse($mejorDia->fecha)->format('d/m/Y') : 'Sin datos',
                'total' => round((float) ($mejorDia->total ?? 0), 2),
            ],
        ]);
    }

    public function ventasPorDiaDataPanel(Request $request)
    {
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : now()->startOfMonth();
        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : now()->endOfDay();

        $ingresoExpr = $this->dashboardIngresoExpr();
        $query = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw('DATE(ventas.fecha_contabilizacion) AS fecha_venta')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total_venta")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->selectRaw('COUNT(DISTINCT ventas.id_usuario) AS preventistas')
            ->groupBy(DB::raw('DATE(ventas.fecha_contabilizacion)'))
            ->orderBy(DB::raw('DATE(ventas.fecha_contabilizacion)'), 'desc');

        return DataTables::of($query)
            ->editColumn('fecha_venta', fn ($row) => Carbon::parse($row->fecha_venta)->format('d/m/Y'))
            ->editColumn('total_venta', fn ($row) => round((float) $row->total_venta, 2))
            ->addColumn('acciones', function ($row) {
                return "<button type='button' class='btn btn-info btn-sm sales-action-btn btn-ver-dia'
                            data-fecha='{$row->fecha_venta}'>
                            Ver Detalle <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ventasPorDiaDetallePedidosPanel(Request $request, string $fecha)
    {
        $ingresoExpr = $this->dashboardIngresoExpr();
        $pedidoFechas = Pedido::query()
            ->select('numero_pedido')
            ->selectRaw('MIN(fecha_pedido) AS fecha_pedido')
            ->selectRaw('MIN(fecha_entrega) AS fecha_entrega')
            ->groupBy('numero_pedido');

        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->leftJoinSub($pedidoFechas, 'pedido_fechas', function ($join) {
                $join->on('ventas.numero_pedido', '=', 'pedido_fechas.numero_pedido');
            })
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fecha)->startOfDay(),
                Carbon::parse($fecha)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->selectRaw('ventas.numero_pedido')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'N/A') AS ruta")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS preventista")
            ->selectRaw('MIN(pedido_fechas.fecha_pedido) AS fecha_pedido')
            ->selectRaw('MIN(pedido_fechas.fecha_entrega) AS fecha_entrega')
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total_pedido")
            ->groupBy('ventas.numero_pedido', 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderBy('ventas.numero_pedido', 'asc');

        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))->filter()->values();

        if ($rutaIds->isNotEmpty()) {
            $query->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $query->whereIn('ventas.id_usuario', $preventistaIds);
        }

        return DataTables::of($query)
            ->editColumn('fecha_pedido', fn ($row) => $row->fecha_pedido ? Carbon::parse($row->fecha_pedido)->format('d/m/Y') : 'N/A')
            ->editColumn('fecha_entrega', fn ($row) => $row->fecha_entrega ? Carbon::parse($row->fecha_entrega)->format('d/m/Y') : 'N/A')
            ->editColumn('total_pedido', fn ($row) => round((float) $row->total_pedido, 2))
            ->addColumn('acciones', function ($row) {
                return "<button type='button' class='btn btn-info btn-sm sales-action-btn btn-ver-pedido-dia'
                            data-pedido='{$row->numero_pedido}'>
                            Productos <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function dashboardResumen(Request $request)
    {
        [$fechaInicio, $fechaFin, $periodoAnteriorInicio, $periodoAnteriorFin] = $this->resolverPeriodoDashboard($request);
        $ingresoExpr = $this->dashboardIngresoExpr();
        $costoExpr = $this->dashboardCostoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();

        $resumen = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$costoExpr}), 0) AS costo_estimado")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->selectRaw('COUNT(DISTINCT ventas.id_producto) AS productos')
            ->selectRaw('COUNT(DISTINCT ventas.id_usuario) AS preventistas_activos')
            ->first();

        $resumenAnterior = $this->dashboardVentasBaseQuery($request, $periodoAnteriorInicio, $periodoAnteriorFin)
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->first();

        $mejorPreventista = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS nombre")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('ventas.id_usuario', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('total')
            ->first();

        $mejorRuta = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS nombre")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('clientes.ruta_id', 'rutas.nombre_ruta')
            ->orderByDesc('total')
            ->first();

        $promedioTicket = ((float) ($resumen->pedidos ?? 0)) > 0
            ? ((float) $resumen->ventas_netas / (float) $resumen->pedidos)
            : 0;
        $margen = ((float) ($resumen->ventas_netas ?? 0)) > 0
            ? (((float) $resumen->utilidad_estimada / (float) $resumen->ventas_netas) * 100)
            : 0;

        $pedidosDespachadosPendientes = Pedido::query()
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false);

        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))->filter()->values();

        if ($rutaIds->isNotEmpty()) {
            $pedidosDespachadosPendientes->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $pedidosDespachadosPendientes->whereIn('pedidos.id_usuario', $preventistaIds);
        }

        $despachadosPendientes = (clone $pedidosDespachadosPendientes)
            ->selectRaw('COUNT(DISTINCT pedidos.numero_pedido) AS pedidos')
            ->selectRaw('COALESCE(SUM(pedidos.cantidad * forma_ventas.precio_venta), 0) AS monto')
            ->first();

        $ventasPeriodoIds = $this->dashboardVentasBaseQuery($request, now()->subDays(30)->startOfDay(), now()->endOfDay())
            ->distinct()
            ->pluck('ventas.id_producto');

        $sinRotacion = Producto::query()
            ->where('estado_de_baja', false)
            ->whereNotIn('id', $ventasPeriodoIds)
            ->count();

        $preventistasSinVenta = User::role('vendedor')
            ->when($preventistaIds->isNotEmpty(), function ($query) use ($preventistaIds) {
                $query->whereIn('id', $preventistaIds);
            })
            ->whereNotIn('id', $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)->distinct()->pluck('ventas.id_usuario'))
            ->count();

        return response()->json([
            'periodo' => [
                'desde' => $fechaInicio->format('Y-m-d'),
                'hasta' => $fechaFin->format('Y-m-d'),
                'etiqueta' => $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y'),
            ],
            'kpis' => [
                'ventas_netas' => round((float) ($resumen->ventas_netas ?? 0), 2),
                'costo_estimado' => round((float) ($resumen->costo_estimado ?? 0), 2),
                'utilidad_estimada' => round((float) ($resumen->utilidad_estimada ?? 0), 2),
                'pedidos' => (int) ($resumen->pedidos ?? 0),
                'clientes' => (int) ($resumen->clientes ?? 0),
                'productos' => (int) ($resumen->productos ?? 0),
                'preventistas_activos' => (int) ($resumen->preventistas_activos ?? 0),
                'ticket_promedio' => round($promedioTicket, 2),
                'margen' => round($margen, 2),
                'crecimiento_ventas' => $this->dashboardVariacion((float) ($resumenAnterior->ventas_netas ?? 0), (float) ($resumen->ventas_netas ?? 0)),
                'crecimiento_utilidad' => $this->dashboardVariacion((float) ($resumenAnterior->utilidad_estimada ?? 0), (float) ($resumen->utilidad_estimada ?? 0)),
                'crecimiento_pedidos' => $this->dashboardVariacion((float) ($resumenAnterior->pedidos ?? 0), (float) ($resumen->pedidos ?? 0)),
                'mejor_preventista' => [
                    'nombre' => $mejorPreventista->nombre ?? 'Sin datos',
                    'total' => round((float) ($mejorPreventista->total ?? 0), 2),
                ],
                'mejor_ruta' => [
                    'nombre' => $mejorRuta->nombre ?? 'Sin datos',
                    'total' => round((float) ($mejorRuta->total ?? 0), 2),
                ],
            ],
            'alertas' => [
                [
                    'tipo' => 'warning',
                    'titulo' => 'Pedidos despachados sin contabilizar',
                    'detalle' => (int) ($despachadosPendientes->pedidos ?? 0) . ' pedidos por Bs ' . number_format((float) ($despachadosPendientes->monto ?? 0), 2, '.', ','),
                ],
                [
                    'tipo' => 'danger',
                    'titulo' => 'Productos con stock critico',
                    'detalle' => Producto::where('cantidad', '<=', 15)->where('estado_de_baja', false)->count() . ' productos necesitan revision',
                ],
                [
                    'tipo' => 'info',
                    'titulo' => 'Productos sin rotacion en 30 dias',
                    'detalle' => $sinRotacion . ' productos no registran ventas recientes',
                ],
                [
                    'tipo' => 'secondary',
                    'titulo' => 'Preventistas sin ventas en el periodo',
                    'detalle' => $preventistasSinVenta . ' preventistas sin movimiento',
                ],
            ],
        ]);
    }

    public function dashboardSeries(Request $request)
    {
        [$fechaInicio, $fechaFin] = $this->resolverPeriodoDashboard($request);
        $ingresoExpr = $this->dashboardIngresoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();
        $dias = $fechaInicio->diffInDays($fechaFin) + 1;
        $agruparPorMes = $dias > 45;
        $periodoSelect = $agruparPorMes
            ? "TO_CHAR(DATE_TRUNC('month', ventas.fecha_contabilizacion), 'YYYY-MM')"
            : "TO_CHAR(DATE(ventas.fecha_contabilizacion), 'YYYY-MM-DD')";

        $serieVentas = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("{$periodoSelect} AS periodo")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad")
            ->groupBy(DB::raw($periodoSelect))
            ->orderBy('periodo')
            ->get();

        $ventasPorPreventista = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS etiqueta")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('ventas.id_usuario', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $ventasPorRuta = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS etiqueta")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('clientes.ruta_id', 'rutas.nombre_ruta')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topProductos = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("productos.nombre_producto AS etiqueta")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('ventas.id_producto', 'productos.nombre_producto')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'serie' => [
                'labels' => $serieVentas->pluck('periodo'),
                'ventas' => $serieVentas->pluck('ventas')->map(fn ($valor) => round((float) $valor, 2)),
                'utilidad' => $serieVentas->pluck('utilidad')->map(fn ($valor) => round((float) $valor, 2)),
            ],
            'preventistas' => [
                'labels' => $ventasPorPreventista->pluck('etiqueta'),
                'data' => $ventasPorPreventista->pluck('total')->map(fn ($valor) => round((float) $valor, 2)),
            ],
            'rutas' => [
                'labels' => $ventasPorRuta->pluck('etiqueta'),
                'data' => $ventasPorRuta->pluck('total')->map(fn ($valor) => round((float) $valor, 2)),
            ],
            'productos' => [
                'labels' => $topProductos->pluck('etiqueta'),
                'data' => $topProductos->pluck('total')->map(fn ($valor) => round((float) $valor, 2)),
            ],
        ]);
    }

    public function dashboardReportePreventistas(Request $request)
    {
        $ingresoExpr = $this->dashboardIngresoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();

        $filas = $this->dashboardVentasBaseQuery($request)
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS preventista")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->groupBy('ventas.id_usuario', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('ventas_netas')
            ->get()
            ->map(function ($fila) {
                $ticketPromedio = (int) $fila->pedidos > 0 ? ((float) $fila->ventas_netas / (int) $fila->pedidos) : 0;
                $margen = (float) $fila->ventas_netas > 0 ? (((float) $fila->utilidad_estimada / (float) $fila->ventas_netas) * 100) : 0;

                return [
                    'preventista' => $fila->preventista ?: 'Sin nombre',
                    'ventas_netas' => round((float) $fila->ventas_netas, 2),
                    'utilidad_estimada' => round((float) $fila->utilidad_estimada, 2),
                    'pedidos' => (int) $fila->pedidos,
                    'clientes' => (int) $fila->clientes,
                    'ticket_promedio' => round($ticketPromedio, 2),
                    'margen' => round($margen, 2),
                ];
            });

        return response()->json(['data' => $filas]);
    }

    public function dashboardReporteRutas(Request $request)
    {
        $ingresoExpr = $this->dashboardIngresoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();

        $filas = $this->dashboardVentasBaseQuery($request)
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->groupBy('clientes.ruta_id', 'rutas.nombre_ruta')
            ->orderByDesc('ventas_netas')
            ->get()
            ->map(function ($fila) {
                $ticketPromedio = (int) $fila->pedidos > 0 ? ((float) $fila->ventas_netas / (int) $fila->pedidos) : 0;
                $margen = (float) $fila->ventas_netas > 0 ? (((float) $fila->utilidad_estimada / (float) $fila->ventas_netas) * 100) : 0;

                return [
                    'ruta' => $fila->ruta,
                    'ventas_netas' => round((float) $fila->ventas_netas, 2),
                    'utilidad_estimada' => round((float) $fila->utilidad_estimada, 2),
                    'pedidos' => (int) $fila->pedidos,
                    'clientes' => (int) $fila->clientes,
                    'ticket_promedio' => round($ticketPromedio, 2),
                    'margen' => round($margen, 2),
                ];
            });

        return response()->json(['data' => $filas]);
    }

    public function dashboardReporteProductos(Request $request)
    {
        $ingresoExpr = $this->dashboardIngresoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();

        $filas = $this->dashboardVentasBaseQuery($request)
            ->leftJoin('marcas', 'productos.id_marca', '=', 'marcas.id')
            ->leftJoin('lineas', 'productos.id_linea', '=', 'lineas.id')
            ->select('productos.codigo', 'productos.nombre_producto')
            ->selectRaw("COALESCE(marcas.descripcion, 'Sin marca') AS marca")
            ->selectRaw("COALESCE(lineas.descripcion_linea, 'Sin linea') AS linea")
            ->selectRaw('SUM(ventas.cantidad * forma_ventas.equivalencia_cantidad) AS unidades')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->groupBy('ventas.id_producto', 'productos.codigo', 'productos.nombre_producto', 'marcas.descripcion', 'lineas.descripcion_linea')
            ->orderByDesc('ventas_netas')
            ->get()
            ->map(function ($fila) {
                $margen = (float) $fila->ventas_netas > 0 ? (((float) $fila->utilidad_estimada / (float) $fila->ventas_netas) * 100) : 0;

                return [
                    'codigo' => $fila->codigo,
                    'producto' => $fila->nombre_producto,
                    'marca' => $fila->marca,
                    'linea' => $fila->linea,
                    'unidades' => (int) $fila->unidades,
                    'ventas_netas' => round((float) $fila->ventas_netas, 2),
                    'utilidad_estimada' => round((float) $fila->utilidad_estimada, 2),
                    'margen' => round($margen, 2),
                ];
            });

        return response()->json(['data' => $filas]);
    }

    public function dashboardReporteCierres(Request $request)
    {
        [$fechaInicio, $fechaFin] = $this->resolverPeriodoDashboard($request);
        $ingresoExpr = $this->dashboardIngresoExpr();
        $utilidadExpr = $this->dashboardUtilidadExpr();
        $dias = $fechaInicio->diffInDays($fechaFin) + 1;
        $agruparPorMes = $dias > 45;
        $selectPeriodo = $agruparPorMes
            ? "TO_CHAR(DATE_TRUNC('month', ventas.fecha_contabilizacion), 'YYYY-MM')"
            : "TO_CHAR(DATE(ventas.fecha_contabilizacion), 'YYYY-MM-DD')";

        $filas = $this->dashboardVentasBaseQuery($request, $fechaInicio, $fechaFin)
            ->selectRaw("{$selectPeriodo} AS periodo")
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS ventas_netas")
            ->selectRaw("COALESCE(SUM({$utilidadExpr}), 0) AS utilidad_estimada")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->groupBy(DB::raw($selectPeriodo))
            ->orderBy('periodo')
            ->get()
            ->map(function ($fila) {
                $ticketPromedio = (int) $fila->pedidos > 0 ? ((float) $fila->ventas_netas / (int) $fila->pedidos) : 0;

                return [
                    'periodo' => $fila->periodo,
                    'ventas_netas' => round((float) $fila->ventas_netas, 2),
                    'utilidad_estimada' => round((float) $fila->utilidad_estimada, 2),
                    'pedidos' => (int) $fila->pedidos,
                    'clientes' => (int) $fila->clientes,
                    'ticket_promedio' => round($ticketPromedio, 2),
                ];
            });

        return response()->json(['data' => $filas]);
    }
    public function ventasPorDiaPreventista(string $idPreventista)
    {
        // Query agregado por día y preventista
        $q = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $idPreventista)
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->selectRaw("DATE(ventas.fecha_contabilizacion) AS fecha_venta") // Postgres: DATE(ts)
            ->selectRaw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_venta")
            ->groupBy(DB::raw("DATE(ventas.fecha_contabilizacion)"))
            ->orderBy(DB::raw("DATE(ventas.fecha_contabilizacion)"), 'desc');

        return DataTables::of($q)
            // Formatea fecha como d/m/Y
            ->editColumn('fecha_venta', function ($row) {
                // $row->fecha_venta viene como 'YYYY-MM-DD'
                try {
                    return Carbon::parse($row->fecha_venta)->format('d/m/Y');
                } catch (\Throwable $e) {
                    return $row->fecha_venta ?? 'N/A';
                }
            })
            // Asegura formato numérico con 2 decimales (aunque ya viene como numeric en el select)
            ->editColumn('total_venta', function ($row) {
                return number_format((float)$row->total_venta, 2, '.', '');
            })
            ->addColumn('acciones', function ($row) use ($idPreventista) {
                // Pasa la fecha (ISO) y el preventista para ver el detalle de ese día
                $fechaIso = $row->fecha_venta; // 'YYYY-MM-DD'
                return "<button type='button' class='btn btn-warning btn-sm'
                            onclick='verDetalleVenta(this)'
                            data-fecha='{$fechaIso}'
                            data-preventista='{$idPreventista}'>
                            Ver Detalle <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ventasPorDiaPreventistaDetallePedidos(string $fecha, string $idPreventista)
    {
        $pedidos = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $idPreventista)
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fecha)->startOfDay(),
                Carbon::parse($fecha)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->select(
                'ventas.id_cliente',
                'ventas.numero_pedido',
                DB::raw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_pedido"),
                'ventas.fecha_contabilizacion'
            )
            ->groupBy('ventas.id_cliente', 'ventas.numero_pedido', 'ventas.fecha_contabilizacion')
            ->orderBy('ventas.numero_pedido', 'asc');
        return DataTables::of($pedidos)
            ->addColumn('nro_pedido', function ($row) {
                return $row->numero_pedido;
            })
            ->addColumn('cliente', function ($row) {
                $cliente=Cliente::find($row->id_cliente);
                return $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : $row->nombre_cliente;
            })
            ->addColumn('total_pedido', function ($row) {
                return number_format((float)$row->total_pedido, 2, '.', '');
            })
            ->addColumn('ruta', function ($row) {
                $cliente=Cliente::find($row->id_cliente);
                return $cliente ? $cliente->ruta->nombre_ruta : 'N/A';
            })
            ->addColumn('acciones', function ($row) {
                return "<button type='button' class='btn btn-warning btn-sm'
                            onclick='verDetallePedido({$row->numero_pedido})'>
                            Detalle Pedido <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }


    public function ventasPorDiaPreventistaDetallePedidosDetalle(string $numeroPedido)
    {
        $ventas = Venta::query()
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.numero_pedido', $numeroPedido)
            ->selectRaw("COALESCE(productos.codigo, 'N/A') AS codigo_producto")
            ->selectRaw("COALESCE(productos.nombre_producto, 'N/A') AS nombre_producto")
            ->selectRaw("COALESCE(forma_ventas.tipo_venta, 'N/A') AS presentacion")
            ->selectRaw('COALESCE(ventas.cantidad, 0) AS cantidad')
            ->selectRaw('COALESCE(forma_ventas.equivalencia_cantidad, 1) AS unidades')
            ->selectRaw('COALESCE(ventas.descripcion_descuento_porcentaje, 0) AS descuento')
            ->selectRaw('COALESCE(forma_ventas.precio_venta, 0) AS precio_unitario')
            ->selectRaw('(ventas.cantidad * forma_ventas.precio_venta * (1 - COALESCE(ventas.descripcion_descuento_porcentaje, 0) / 100.0)) AS total');

        return DataTables::of($ventas)
            ->editColumn('cantidad', function ($row) {
                return (float) $row->cantidad;
            })
            ->editColumn('unidades', function ($row) {
                return (float) $row->unidades;
            })
            ->editColumn('descuento', function ($row) {
                return round((float) $row->descuento, 2);
            })
            ->editColumn('precio_unitario', function ($row) {
                return round((float) $row->precio_unitario, 2);
            })
            ->editColumn('total', function ($row) {
                return round((float) $row->total, 2);
            })
            ->make(true);
    }

    public function ventasPorPreventista(){
        return view('Contabilidad.VentasPorPreventista.index');
    }

    public function ventasPorPreventistaOpciones(string $fechaInicio, string $fechaFin)
    {
        // Query agregado por preventista en un rango de fechas
        $q = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->selectRaw("ventas.id_usuario AS id_preventista")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS preventista")
            ->selectRaw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_vendido")
            ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
            ->selectRaw('COUNT(DISTINCT ventas.id_cliente) AS clientes')
            ->groupBy('ventas.id_usuario', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderBy('total_vendido', 'desc');

        return DataTables::of($q)
            ->addColumn('total_vendido', function ($row) {
                return number_format((float)$row->total_vendido, 2, '.', '');
            })
            ->addColumn('pedidos', function ($row) {
                return (int) $row->pedidos;
            })
            ->addColumn('clientes', function ($row) {
                return (int) $row->clientes;
            })
            ->addColumn('acciones', function ($row) {
                // Pasa el preventista para ver el detalle de sus ventas
                $idPreventista = $row->id_preventista;
                return "<button type='button' class='btn btn-info btn-sm sales-action-btn btn-ver-preventista'
                            data-preventista='{$idPreventista}'
                            data-preventista-total='{$row->total_vendido}'
                            data-preventista-nombre=\"".e($row->preventista)."\">
                            Ver Detalle <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ventasPorPreventistaDetallePedidos(string $fechaInicio, string $fechaFin, string $idPreventista)
    {
        $pedidoFechas = Pedido::query()
            ->select('numero_pedido')
            ->selectRaw('MIN(fecha_pedido) AS fecha_pedido')
            ->selectRaw('MIN(fecha_entrega) AS fecha_entrega')
            ->groupBy('numero_pedido');

        $pedidos = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoinSub($pedidoFechas, 'pedido_fechas', function ($join) {
                $join->on('ventas.numero_pedido', '=', 'pedido_fechas.numero_pedido');
            })
            ->where('ventas.id_usuario', $idPreventista)
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->selectRaw('ventas.id_cliente')
            ->selectRaw('ventas.numero_pedido')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'N/A') AS ruta")
            ->selectRaw("MIN(pedido_fechas.fecha_pedido) AS fecha_pedido")
            ->selectRaw("MIN(pedido_fechas.fecha_entrega) AS fecha_entrega")
            ->selectRaw("COUNT(*) AS items")
            ->selectRaw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_pedido")
            ->groupBy('ventas.id_cliente', 'ventas.numero_pedido', 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta')
            ->orderBy('ventas.numero_pedido', 'asc');
        return DataTables::of($pedidos)
            ->addColumn('nro_pedido', function ($row) {
                return $row->numero_pedido;
            })
            ->addColumn('fecha_pedido', function ($row) {
                return $row->fecha_pedido ? Carbon::parse($row->fecha_pedido)->format('d/m/Y') : 'N/A';
            })
            ->addColumn('fecha_entrega', function ($row) {
                return $row->fecha_entrega ? Carbon::parse($row->fecha_entrega)->format('d/m/Y') : 'N/A';
            })
            ->addColumn('total_pedido', function ($row) {
                return number_format((float)$row->total_pedido, 2, '.', '');
            })
            ->addColumn('items', function ($row) {
                return (int) $row->items;
            })
            ->addColumn('acciones', function ($row) {
                return "<button type='button' class='btn btn-info btn-sm sales-action-btn btn-ver-pedido'
                            data-pedido='{$row->numero_pedido}'>
                            Detalle Pedido <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function comparacionGanancial()
    {
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('Contabilidad.ComparacionGanancial.index', compact('preventistas', 'rutas'));
    }

    public function pedidosPorDia()
    {
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('Contabilidad.PedidosPorDia.index', compact('preventistas', 'rutas'));
    }

    public function pedidosPorDiaData(Request $request)
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->toDateString()
            : now()->toDateString();

        $ingresoExpr = $this->dashboardIngresoExpr();

        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->whereDate('ventas.fecha_contabilizacion', $fecha)
            ->selectRaw('ventas.numero_pedido')
            ->selectRaw('DATE(ventas.fecha_contabilizacion) AS fecha_contable')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS preventista")
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('ventas.numero_pedido', DB::raw('DATE(ventas.fecha_contabilizacion)'), 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderBy('ventas.numero_pedido', 'desc');

        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))->filter()->values();

        if ($rutaIds->isNotEmpty()) {
            $query->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $query->whereIn('ventas.id_usuario', $preventistaIds);
        }

        return DataTables::of($query)
            ->editColumn('fecha_contable', function ($row) {
                return Carbon::parse($row->fecha_contable)->format('d/m/Y');
            })
            ->editColumn('items', function ($row) {
                return (int) $row->items . ' items';
            })
            ->editColumn('total', function ($row) {
                return 'Bs ' . number_format((float) $row->total, 2, '.', ',');
            })
            ->make(true);
    }

    public function pedidosPorDiaPdf(Request $request)
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->toDateString()
            : now()->toDateString();

        $ingresoExpr = $this->dashboardIngresoExpr();
        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))->filter()->values();

        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->whereDate('ventas.fecha_contabilizacion', $fecha)
            ->selectRaw('ventas.numero_pedido')
            ->selectRaw('DATE(ventas.fecha_contabilizacion) AS fecha_contable')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS preventista")
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw("COALESCE(SUM({$ingresoExpr}), 0) AS total")
            ->groupBy('ventas.numero_pedido', DB::raw('DATE(ventas.fecha_contabilizacion)'), 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderBy('ventas.numero_pedido', 'desc');

        if ($rutaIds->isNotEmpty()) {
            $query->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $query->whereIn('ventas.id_usuario', $preventistaIds);
        }

        $pedidos = $query->get();

        $detalles = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->whereDate('ventas.fecha_contabilizacion', $fecha)
            ->select(
                'ventas.numero_pedido',
                'productos.codigo',
                'productos.nombre_producto',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'forma_ventas.equivalencia_cantidad',
                'ventas.cantidad',
                'ventas.descripcion_descuento_porcentaje'
            );

        if ($rutaIds->isNotEmpty()) {
            $detalles->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $detalles->whereIn('ventas.id_usuario', $preventistaIds);
        }

        $detallesPorPedido = $detalles->orderBy('ventas.numero_pedido')->get()
            ->groupBy('numero_pedido')
            ->map(function ($items) {
                return $items->map(function ($venta) {
                    $descuento = (float) ($venta->descripcion_descuento_porcentaje ?? 0);
                    $subtotal = ((float) $venta->precio_venta * (int) $venta->cantidad) * (1 - ($descuento / 100));

                    return [
                        'codigo' => $venta->codigo,
                        'producto' => $venta->nombre_producto,
                        'presentacion' => $venta->tipo_venta,
                        'cantidad' => (int) $venta->cantidad,
                        'unidades' => (int) $venta->cantidad * (int) $venta->equivalencia_cantidad,
                        'precio_unitario' => round((float) $venta->precio_venta, 2),
                        'descuento' => round($descuento, 2),
                        'subtotal' => round($subtotal, 2),
                    ];
                });
            });

        $rutasTexto = $rutaIds->isNotEmpty()
            ? Rutas::whereIn('id', $rutaIds)->orderBy('nombre_ruta')->pluck('nombre_ruta')->implode(', ')
            : 'Todas las rutas';

        $preventistasTexto = $preventistaIds->isNotEmpty()
            ? User::whereIn('id', $preventistaIds)->orderBy('nombres')->get()
                ->map(fn ($user) => trim($user->nombres.' '.$user->apellido_paterno.' '.$user->apellido_materno))
                ->implode(', ')
            : 'Todos los preventistas';

        $resumen = [
            'pedidos' => $pedidos->count(),
            'items' => $pedidos->sum('items'),
            'total' => $pedidos->sum('total'),
        ];

        $pdf = Pdf::loadView('Contabilidad.PedidosPorDia.pdf', [
            'pedidos' => $pedidos,
            'detallesPorPedido' => $detallesPorPedido,
            'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
            'rutasTexto' => $rutasTexto,
            'preventistasTexto' => $preventistasTexto,
            'resumen' => $resumen,
        ]);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('pedidos-por-dia-'.$fecha.'.pdf');
    }

    public function pedidosPorDiaDetalle(string $numeroPedido)
    {
        $ventas = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->where('ventas.numero_pedido', $numeroPedido)
            ->select(
                'productos.codigo',
                'productos.nombre_producto',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'forma_ventas.equivalencia_cantidad',
                'ventas.cantidad',
                'ventas.descripcion_descuento_porcentaje'
            )
            ->orderBy('productos.nombre_producto')
            ->get()
            ->map(function ($venta) {
                $descuento = (float) ($venta->descripcion_descuento_porcentaje ?? 0);
                $subtotal = ((float) $venta->precio_venta * (int) $venta->cantidad) * (1 - ($descuento / 100));

                return [
                    'codigo' => $venta->codigo,
                    'producto' => $venta->nombre_producto,
                    'presentacion' => $venta->tipo_venta,
                    'cantidad' => (int) $venta->cantidad,
                    'unidades' => (int) $venta->cantidad * (int) $venta->equivalencia_cantidad,
                    'precio_unitario' => round((float) $venta->precio_venta, 2),
                    'descuento' => round($descuento, 2),
                    'subtotal' => round($subtotal, 2),
                ];
            });

        return response()->json([
            'numero_pedido' => $numeroPedido,
            'items' => $ventas,
            'total' => round($ventas->sum('subtotal'), 2),
        ]);
    }

    private function resolverPeriodoDashboard(Request $request): array
    {
        $hoy = now();
        $preset = $request->input('preset', 'month');

        switch ($preset) {
            case 'today':
                $inicio = $hoy->copy()->startOfDay();
                $fin = $hoy->copy()->endOfDay();
                break;
            case 'week':
                $inicio = $hoy->copy()->startOfWeek();
                $fin = $hoy->copy()->endOfWeek();
                break;
            case 'year':
                $inicio = $hoy->copy()->startOfYear();
                $fin = $hoy->copy()->endOfYear();
                break;
            case 'custom':
                $inicio = $request->filled('fecha_inicio')
                    ? Carbon::parse($request->fecha_inicio)->startOfDay()
                    : $hoy->copy()->startOfMonth();
                $fin = $request->filled('fecha_fin')
                    ? Carbon::parse($request->fecha_fin)->endOfDay()
                    : $hoy->copy()->endOfDay();
                break;
            case 'month':
            default:
                $inicio = $hoy->copy()->startOfMonth();
                $fin = $hoy->copy()->endOfMonth();
                break;
        }

        if ($request->filled('fecha_inicio')) {
            $inicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        }

        if ($request->filled('fecha_fin')) {
            $fin = Carbon::parse($request->fecha_fin)->endOfDay();
        }

        if ($inicio->gt($fin)) {
            [$inicio, $fin] = [$fin->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        $dias = $inicio->diffInDays($fin) + 1;
        $anteriorFin = $inicio->copy()->subDay()->endOfDay();
        $anteriorInicio = $anteriorFin->copy()->subDays(max($dias - 1, 0))->startOfDay();

        return [$inicio, $fin, $anteriorInicio, $anteriorFin];
    }

    private function dashboardVentasBaseQuery(Request $request, ?Carbon $inicio = null, ?Carbon $fin = null)
    {
        [$inicio, $fin] = [$inicio ?? $this->resolverPeriodoDashboard($request)[0], $fin ?? $this->resolverPeriodoDashboard($request)[1]];

        $costosLotes = DB::table('lotes')
            ->select('producto_id')
            ->selectRaw('SUM(precio_ingreso * cantidad) / NULLIF(SUM(cantidad), 0) AS costo_promedio')
            ->whereNull('deleted_at')
            ->groupBy('producto_id');

        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->leftJoinSub($costosLotes, 'costos_lote', function ($join) {
                $join->on('costos_lote.producto_id', '=', 'ventas.id_producto');
            })
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->whereBetween('ventas.fecha_contabilizacion', [$inicio, $fin]);

        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))->filter()->values();

        if ($rutaIds->isNotEmpty()) {
            $query->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $query->whereIn('ventas.id_usuario', $preventistaIds);
        }

        return $query;
    }

    private function dashboardIngresoExpr(): string
    {
        return '(ventas.cantidad * forma_ventas.precio_venta * (1 - COALESCE(ventas.descripcion_descuento_porcentaje, 0) / 100.0))';
    }

    private function dashboardCostoExpr(): string
    {
        return '(ventas.cantidad * forma_ventas.equivalencia_cantidad * COALESCE(costos_lote.costo_promedio, productos.precio_compra, 0))';
    }

    private function dashboardUtilidadExpr(): string
    {
        return '(' . $this->dashboardIngresoExpr() . ' - ' . $this->dashboardCostoExpr() . ')';
    }

    private function dashboardVariacion(float $anterior, float $actual): float
    {
        if ($anterior == 0.0 && $actual == 0.0) {
            return 0.0;
        }

        if ($anterior == 0.0) {
            return 100.0;
        }

        return round((($actual - $anterior) / $anterior) * 100, 2);
    }
}
