<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\FormaVenta;
use App\Models\Lotes;
use App\Models\Producto;
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
        $preventistas=User::role('vendedor')->get();
        return view('Contabilidad.VentasPorDia.index',compact('preventistas'));
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

    public function comparacionGanancialData(string $mes, string $anio)
    {
        $data = Venta::query()
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->whereMonth('ventas.fecha_contabilizacion', $mes)
            ->whereYear('ventas.fecha_contabilizacion', $anio)
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->select(
                'ventas.id_producto',
                'productos.codigo as codigo_prod', // Traemos el código directamente
                'productos.nombre_producto'
            )
            ->groupBy('ventas.id_producto', 'productos.codigo', 'productos.nombre_producto');
            
        return DataTables::of($data)
            ->addColumn('codigo_producto', function ($row) {
                return $row->codigo_prod ?? 'N/A';
            })
            ->addColumn('nombre_producto', function ($row) {
                return $row->nombre_producto ?? 'N/A';
            })
            ->addColumn('imagen_producto', function ($row) {
                $url = route('productos.imagen', ['id' => $row->id_producto]);
                return '<img src="'.$url.'" alt="Imagen" width="50" height="50" class="img-thumbnail">';
            })
            ->addColumn('cantidad_ventas', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');
                $cantidad_ventas = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->sum(DB::raw('ventas.cantidad*forma_ventas.equivalencia_cantidad'));
                $producto=Producto::find($row->id_producto);
                return $cantidad_ventas.' '.$producto->detalle_cantidad;
            })
            ->addColumn('precio_compra', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');

                // 1. Obtenemos el producto una sola vez
                $Producto = Producto::find($row->id_producto);
                
                // 2. Traemos los lotes del mes
                $suma_lotes = Lotes::where('producto_id', $row->id_producto)
                    ->whereMonth('ingreso_lote', $mes)
                    ->whereYear('ingreso_lote', $anio)
                    ->get();

                // 3. Verificamos si hay lotes (isNotEmpty es correcto para colecciones)
                if ($suma_lotes->isNotEmpty()) {
                    $total_precio = 0; // IMPORTANTE: Inicializar la variable para que no de error

                    foreach ($suma_lotes as $lote) {
                        // Multiplicamos cantidad por precio de cada lote
                        $total_precio += ((float)$lote->precio_ingreso * (float)$lote->cantidad);
                    }

                    $total_cantidad = $suma_lotes->sum('cantidad');
                    
                    // Evitamos división por cero y calculamos el promedio real (Ponderado)
                    $precio_compra_promedio = ($total_cantidad > 0) ? ($total_precio / $total_cantidad) : 0;

                    return 'Bs.- ' . number_format((float)$precio_compra_promedio, 2, '.', ',') . ' por ' . $Producto->detalle_cantidad;
                } else {
                    // Si no hay lotes, usamos el precio base del maestro de productos
                    return 'Bs.- ' . number_format((float)$Producto->precio_compra, 2, '.', ',') . ' por ' . $Producto->detalle_cantidad;
                }
            })
            ->addColumn('costo_total_mes_actual', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');

                $cantidad_ventas = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->sum(DB::raw('ventas.cantidad*forma_ventas.equivalencia_cantidad'));
                // 1. Obtenemos el producto una sola vez
                $Producto = Producto::find($row->id_producto);
                
                // 2. Traemos los lotes del mes
                $suma_lotes = Lotes::where('producto_id', $row->id_producto)
                    ->whereMonth('ingreso_lote', $mes)
                    ->whereYear('ingreso_lote', $anio)
                    ->get();

                // 3. Verificamos si hay lotes (isNotEmpty es correcto para colecciones)
                if ($suma_lotes->isNotEmpty()) {
                    $total_precio = 0; // IMPORTANTE: Inicializar la variable para que no de error

                    foreach ($suma_lotes as $lote) {
                        // Multiplicamos cantidad por precio de cada lote
                        $total_precio += ((float)$lote->precio_ingreso * (float)$lote->cantidad);
                    }

                    $total_cantidad = $suma_lotes->sum('cantidad');
                    // Evitamos división por cero y calculamos el promedio real (Ponderado)
                    $precio_compra_promedio = ($total_cantidad > 0) ? ($total_precio / $total_cantidad) : 0;
                    $total_costo = $precio_compra_promedio * $cantidad_ventas;
                    return 'Bs.- ' . number_format((float)$total_costo, 2, '.', ',');
                } else {
                    // Si no hay lotes, usamos el precio base del maestro de productos
                    $total_costo = $Producto->precio_compra * $cantidad_ventas;
                    return 'Bs.- ' . number_format((float)$total_costo, 2, '.', ',');
                }
            })
            ->addColumn('precio_ventas', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');

                $precio_venta = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->avg('forma_ventas.precio_venta');

                return 'Bs.- ' . number_format((float)$precio_venta, 2, '.', ',');
            })
            ->addColumn('ventas_mes_actual', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');

                $ventas_mes_actual = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->sum(DB::raw('forma_ventas.precio_venta * (ventas.cantidad*forma_ventas.equivalencia_cantidad)'));

                return 'Bs.- ' . number_format((float)$ventas_mes_actual, 2, '.', ',');
            })
            ->addColumn('ganancia_mes_actual', function ($row) {
                $mes = request()->route('mes');
                $anio = request()->route('anio');

                $cantidad_ventas = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->sum(DB::raw('ventas.cantidad*forma_ventas.equivalencia_cantidad'));
                // 1. Obtenemos el producto una sola vez
                $Producto = Producto::find($row->id_producto);
                
                // 2. Traemos los lotes del mes
                $suma_lotes = Lotes::where('producto_id', $row->id_producto)
                    ->whereMonth('ingreso_lote', $mes)
                    ->whereYear('ingreso_lote', $anio)
                    ->get();

                // 3. Verificamos si hay lotes (isNotEmpty es correcto para colecciones)
                if ($suma_lotes->isNotEmpty()) {
                    $total_precio = 0; // IMPORTANTE: Inicializar la variable para que no de error

                    foreach ($suma_lotes as $lote) {
                        // Multiplicamos cantidad por precio de cada lote
                        $total_precio += ((float)$lote->precio_ingreso * (float)$lote->cantidad);
                    }

                    $total_cantidad = $suma_lotes->sum('cantidad');
                    // Evitamos división por cero y calculamos el promedio real (Ponderado)
                    $precio_compra_promedio = ($total_cantidad > 0) ? ($total_precio / $total_cantidad) : 0;
                } else {
                    // Si no hay lotes, usamos el precio base del maestro de productos
                    $precio_compra_promedio = $Producto->precio_compra;
                }

                $ventas_mes_actual = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('ventas.id_producto', $row->id_producto)
                    ->whereMonth('ventas.fecha_contabilizacion', $mes)
                    ->whereYear('ventas.fecha_contabilizacion', $anio)
                    ->whereNotNull('ventas.fecha_contabilizacion')
                    ->sum(DB::raw('forma_ventas.precio_venta * (ventas.cantidad*forma_ventas.equivalencia_cantidad)')); 
                $costo_total = $precio_compra_promedio * $cantidad_ventas;
                $ganancia = $ventas_mes_actual - $costo_total;
                return 'Bs.- ' . number_format((float)$ganancia, 2, '.', ',');
                
            })
            ->rawColumns(['imagen_producto'])
            ->make(true);
    }
}
