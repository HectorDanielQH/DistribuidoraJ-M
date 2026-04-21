<?php

namespace App\Http\Controllers\Contabilidad;

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

    public function ventasPorDia(Request $request){
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('Contabilidad.dashboard.index', compact('preventistas', 'rutas'));
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
        $ventas = Venta::query()->where('numero_pedido', $numeroPedido);
        /*
        { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'cantidad', name: 'cantidad' },
                    { data: 'precio_unitario', name: 'precio_unitario' },
                    { data: 'total', name: 'total' },
        */
        return DataTables::of($ventas)
            ->addColumn('codigo_producto', function ($row) {
                $producto=Producto::find($row->id_producto);
                return $producto ? $producto->codigo : 'N/A';
            })
            ->addColumn('nombre_producto', function ($row) {
                $producto=Producto::find($row->id_producto);
                return $producto ? $producto->nombre_producto : 'N/A';
            })
            ->addColumn('cantidad', function ($row) {
                return $row->cantidad;
            })
            ->addColumn('precio_unitario', function ($row) {
                $formaVenta = FormaVenta::find($row->id_forma_venta);
                return $formaVenta ? number_format((float)$formaVenta->precio_venta, 2, '.', '') : 'N/A';
            })
            ->addColumn('total', function ($row) {
                $formaVenta = FormaVenta::find($row->id_forma_venta);
                if ($formaVenta) {
                    $total = $formaVenta->precio_venta * $row->cantidad;
                    return number_format((float)$total, 2, '.', '');
                }
                return 'N/A';
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
            ->selectRaw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_vendido")
            ->groupBy('ventas.id_usuario')
            ->orderBy('total_vendido', 'desc');

        return DataTables::of($q)
            ->addColumn('preventista', function ($row) {
                $user=User::find($row->id_preventista);
                return $user ? $user->nombres.' '.$user->apellidos : 'N/A';
            })
            ->addColumn('total_vendido', function ($row) {
                return number_format((float)$row->total_vendido, 2, '.', '');
            })
            ->addColumn('acciones', function ($row) {
                // Pasa el preventista para ver el detalle de sus ventas
                $idPreventista = $row->id_preventista;
                return "<button type='button' class='btn btn-warning btn-sm'
                            onclick='verDetalleVentasPreventista(this)'
                            data-preventista='{$idPreventista}'>
                            Ver Detalle <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ventasPorPreventistaDetallePedidos(string $fechaInicio, string $fechaFin, string $idPreventista)
    {
        $pedidos = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $idPreventista)
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->select(
                'ventas.id_cliente',
                'ventas.numero_pedido',
                DB::raw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_pedido"),
            )
            ->groupBy('ventas.id_cliente', 'ventas.numero_pedido')
            ->orderBy('ventas.numero_pedido', 'asc');
        return DataTables::of($pedidos)
            ->addColumn('nro_pedido', function ($row) {
                return $row->numero_pedido;
            })
            ->addColumn('cliente', function ($row) {
                $cliente=Cliente::find($row->id_cliente);
                return $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : $row->nombre_cliente;
            })
            ->addColumn('fecha_pedido', function ($row) {
                $fechaPedido=Venta::where('numero_pedido',$row->numero_pedido)->first();
                return $fechaPedido ? Carbon::parse($fechaPedido->fecha_pedido)->format('d/m/Y') : 'N/A';
            })
            ->addColumn('fecha_entrega', function ($row) {
                $fechaEntrega=Venta::where('numero_pedido',$row->numero_pedido)->first();
                return $fechaEntrega ? Carbon::parse($fechaEntrega->fecha_entrega)->format('d/m/Y') : 'N/A';
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

    public function comparacionGanancial(){
        return view('Contabilidad.ComparacionGanancial.index');
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
