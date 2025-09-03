<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('administrador.ventas.index');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        //
    }

    public function obtenerVentas(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
    $fechaFin    = Carbon::parse($request->input('fecha_fin'))->endOfDay();

    // 1) Agregamos en SQL: total por pedido = SUM(cantidad * precio_venta)
    $pedidos = DB::table('ventas')
        ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
        ->join('users', 'ventas.id_usuario', '=', 'users.id')
        ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
        ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio, $fechaFin])
        ->selectRaw("
            ventas.id_usuario,
            CONCAT(users.nombres, ' ', COALESCE(users.apellido_paterno,''), ' ', COALESCE(users.apellido_materno,'')) as usuario,
            ventas.numero_pedido,
            CONCAT(clientes.nombres, ' ', COALESCE(clientes.apellidos,'')) as cliente,
            SUM(ventas.cantidad * forma_ventas.precio_venta)::numeric(12,2) as total_pedido
        ")
        ->groupBy('ventas.id_usuario', 'usuario', 'ventas.numero_pedido', 'cliente')
        ->orderBy('ventas.numero_pedido', 'asc')
        ->get();

    // 2) Reagrupamos en PHP por usuario para armar subtotales y total general
    $porUsuario = $pedidos->groupBy('id_usuario');

    $usuarios = [];
    $totalGeneral = 0.0;

    foreach ($porUsuario as $idUsuario => $rows) {
        $usuarioNombre = $rows->first()->usuario;
        $subtotal = $rows->sum(function($r){ return (float)$r->total_pedido; });
        $totalGeneral += $subtotal;

        $usuarios[] = [
            'id_usuario'       => $idUsuario,
            'usuario'          => $usuarioNombre,
            'pedidos'          => $rows->map(function($r){
                return [
                    'numero_pedido' => $r->numero_pedido,
                    'cliente'       => $r->cliente,
                    'total_pedido'  => (float)$r->total_pedido,
                ];
            })->values(),
            'subtotal_usuario' => (float)round($subtotal, 2),
        ];
    }

    return response()->json([
        'usuarios'      => $usuarios,
        'total_general' => (float)round($totalGeneral, 2),
    ]);
    }



    public function visualizacionPedido(string $numero_pedido)
    {
        $pedidos = Venta::join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.id as id_producto',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'ventas.id as id_pedido',
                'ventas.numero_pedido',
                'ventas.cantidad as cantidad_pedido',
                'ventas.promocion',
                'ventas.descripcion_descuento_porcentaje',
                'ventas.descripcion_regalo',
            )
            ->where('ventas.numero_pedido', $numero_pedido)
            ->orderBy('ventas.numero_pedido', 'asc')
            ->get();

        return response()->json([
            'numero_pedido' => $numero_pedido,
            'pedidos' => $pedidos
        ],200);
    }

    public function resporteVentasProducto(Request $request)
    {
        $productos = Producto::all();

        return view('administrador.reportes.ventasproducto', compact('productos'));
    }

    public function reporteVentaProductosId(Request $request)
    {
        $productos_vendidos = Venta::where('id_producto',$request->id)->exists();
        $periodo = $request->periodo;

        if (!$productos_vendidos) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($periodo == 'dias') {
            $venta = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->selectRaw('DATE(ventas.fecha_contabilizacion) as dia, sum(ventas.cantidad*forma_ventas.equivalencia_cantidad) as total')
                ->whereBetween('ventas.fecha_contabilizacion', [$request->fechaInicio, $request->fechaFin])
                ->where('ventas.id_producto', $request->id)
                ->groupByRaw('DATE(ventas.fecha_contabilizacion)')
                ->orderBy('dia')
                ->get();
            return response()->json([
                'fechas'=>$venta,
            ]);
        }
        if ($periodo == 'semanas') {
            list($anioInicio, $semanaInicio) = explode('-W', $request->semanaInicio);
            list($anioFin, $semanaFin) = explode('-W', $request->semanaFin);

            $fechaInicio = Carbon::now()->setISODate($anioInicio, $semanaInicio)->startOfWeek();
            $fechaFin = Carbon::now()->setISODate($anioFin, $semanaFin)->endOfWeek();

            $atencion = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->selectRaw("TO_CHAR(ventas.fecha_contabilizacion, 'IYYY-IW') as semana, sum(ventas.cantidad * forma_ventas.equivalencia_cantidad) as total")
                ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio, $fechaFin])
                ->where('ventas.id_producto', $request->id)
                ->groupByRaw("TO_CHAR(ventas.fecha_contabilizacion, 'IYYY-IW')")
                ->orderBy('semana')
                ->get();

            return response()->json(['fechas' => $atencion]);
        }
        if ($periodo == 'meses') {
            $fechaInicio = Carbon::parse($request->mesInicio . '-01')->startOfMonth();
            $fechaFin = Carbon::parse($request->mesFin . '-01')->endOfMonth();
            $atencion = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->selectRaw("TO_CHAR(ventas.fecha_contabilizacion, 'YYYY-MM') as mes, sum(ventas.cantidad * forma_ventas.equivalencia_cantidad) as total")
                ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio, $fechaFin])
                ->where('ventas.id_producto', $request->id)
                ->groupByRaw("TO_CHAR(ventas.fecha_contabilizacion, 'YYYY-MM')")
                ->orderBy('mes')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }
        if ($periodo == 'anios') {
            // Asegurar que los valores sean enteros (ej: 2023, 2025)
            $anioInicio = (int) $request->anioInicio;
            $anioFin = (int) $request->anioFin;

            // Crear fechas desde el primer día del año hasta el último día del año
            $fechaInicio = Carbon::create($anioInicio)->startOfYear();
            $fechaFin = Carbon::create($anioFin)->endOfYear();

            $atencion = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->selectRaw("EXTRACT(YEAR FROM ventas.fecha_contabilizacion) as anio, sum(ventas.cantidad * forma_ventas.equivalencia_cantidad) as total")
                ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio, $fechaFin])
                ->where('ventas.id_producto', $request->id)
                ->groupByRaw("EXTRACT(YEAR FROM ventas.fecha_contabilizacion)")
                ->orderBy('anio')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }


        return response()->json(['error' => 'Periodo no válido'], 400);
    }
}
