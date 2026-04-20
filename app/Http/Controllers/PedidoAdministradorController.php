<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rutas;
use App\Models\User;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PedidoAdministradorController extends Controller
{
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = $this->basePedidosPendientes()
                ->with(['cliente.ruta', 'usuario'])
                ->select('pedidos.id_cliente', 'pedidos.numero_pedido', 'pedidos.id_usuario')
                ->selectRaw('DATE(pedidos.fecha_pedido) AS fecha_pedido')
                ->selectRaw('COUNT(*) AS cantidad_items')
                ->selectRaw('SUM(pedidos.cantidad * forma_ventas.precio_venta) AS total_estimado')
                ->selectRaw('SUM(pedidos.cantidad * forma_ventas.equivalencia_cantidad) AS unidades_reservadas')
                ->groupBy('pedidos.id_cliente', 'pedidos.numero_pedido', 'pedidos.id_usuario', DB::raw('DATE(pedidos.fecha_pedido)'))
                ->orderBy('pedidos.numero_pedido', 'asc');

            if ($request->filled('ruta_id')) {
                $query->whereHas('cliente', function ($query) use ($request) {
                    $query->where('ruta_id', $request->ruta_id);
                });
            }

            if ($request->filled('preventista_id')) {
                $query->where('pedidos.id_usuario', $request->preventista_id);
            }

            if ($request->filled('fecha_pedido')) {
                $query->whereDate('pedidos.fecha_pedido', $request->fecha_pedido);
            }
            
            return $dataTables->eloquent($query)
                ->addColumn('numero_pedido', function($pedido){
                    return '<span class="order-number">#' . str_pad($pedido->numero_pedido, 6, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('cliente', function($pedido){
                    $nombre = $pedido->cliente ? $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos : 'N/A';
                    $celular = $pedido->cliente ? $pedido->cliente->celular : 'Sin celular';
                    return '<div class="order-client"><strong>' . e($nombre) . '</strong><span>' . e($celular) . '</span></div>';
                })
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cliente', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(nombres, ' ', apellidos)"), 'ilike', "%{$keyword}%");
                    });
                })
                ->addColumn('direccion', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->calle_avenida . ' - ' . $pedido->cliente->zona_barrio : 'N/A';
                })
                ->addColumn('ruta', function($pedido){
                    return $pedido->cliente && $pedido->cliente->ruta_id ? $pedido->cliente->ruta->nombre_ruta : 'N/A';
                })
                ->addColumn('preventista', function($pedido){
                    return $pedido->usuario ? $pedido->usuario->nombres.' '.$pedido->usuario->apellido_paterno.' '.$pedido->usuario->apellido_materno : 'N/A';
                })
                ->addColumn('resumen', function($pedido){
                    return '<div class="order-summary-mini">
                        <strong>' . (int) $pedido->cantidad_items . ' items</strong>
                        <span>' . (int) $pedido->unidades_reservadas . ' unidades reservadas</span>
                    </div>';
                })
                ->addColumn('total_estimado', function($pedido){
                    return '<strong class="order-total">Bs ' . number_format((float) $pedido->total_estimado, 2, '.', ',') . '</strong>';
                })
                ->addColumn('estado', function($pedido){
                    return '<span class="order-status order-status-pending"><i class="fas fa-clock"></i> Pendiente para despacho</span>';
                })
                ->addColumn('acciones', function($pedido){
                    $ruta=route('administrador.pedidos.administrador.editar', $pedido->numero_pedido);
                    $botones = '<div class="order-actions">';
                    $botones .= '
                    <button type="button" class="btn btn-primary btn-sm order-action-btn" onclick="verPedidoCliente(this)"
                        id-numero-pedido="' . $pedido->numero_pedido . '"
                    >
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <a
                        href="' . $ruta . '"
                        class="btn btn-warning btn-sm order-action-btn"
                    >
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    ';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['numero_pedido', 'cliente', 'resumen', 'total_estimado', 'estado','acciones'])
                ->make(true);
        }

        $resumenPedidos = $this->resumenPedidosFlujo();
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        
        return view('administrador.pedidos.index', compact('resumenPedidos', 'rutas', 'preventistas'));
    }

    public function visualizacionDespachados(Request $request, DataTables $dataTables){
        if($request->ajax()){
            return $this->dataTableConsolidadoDespacho(
                $dataTables,
                $this->consolidadoProductosDespachoQuery('despachados', $request)
            );
        }

        $suma_total_estimada = $this->basePedidosDespachados()
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        $resumenPedidos = $this->resumenPedidosFlujo();
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();

        return view('administrador.pedidos.despachados', compact('suma_total_estimada', 'resumenPedidos', 'rutas', 'preventistas'));
    }


    public function visualizacionParaDespachado(Request $request, DataTables $dataTables){
        if($request->ajax()){
            return $this->dataTableConsolidadoDespacho(
                $dataTables,
                $this->consolidadoProductosDespachoQuery('pendientes', $request)
            );
        }

        $suma_total_estimada = $this->basePedidosPendientes()
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        $resumenPedidos = $this->resumenPedidosFlujo();
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();

        return view('administrador.pedidos.paradespachar', compact('suma_total_estimada', 'resumenPedidos', 'rutas', 'preventistas'));
    }

    public function visualizacionPdfConsolidadoDespacho(Request $request, string $estado)
    {
        if (! in_array($estado, ['pendientes', 'despachados'], true)) {
            abort(404);
        }

        $productos = $this->consolidadoProductosDespachoQuery($estado, $request)
            ->get()
            ->sortBy(fn ($item) => $item->producto ? $item->producto->nombre_producto : '')
            ->values()
            ->map(function ($item) {
                $producto = $item->producto;
                $imagen = null;

                if ($producto && $producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
                    $imagen = Storage::disk('local')->path($producto->foto_producto);
                }

                return [
                    'codigo_producto' => $producto ? $producto->codigo : 'N/A',
                    'imagen' => $imagen,
                    'nombre_producto' => $producto ? $producto->nombre_producto : 'N/A',
                    'stock_producto' => $producto ? (int) ($producto->cantidad ?? 0).' '.$producto->detalle_cantidad : 'N/A',
                    'cantidad_valor' => (float) $item->cantidad_despacho,
                    'cantidad_despacho' => number_format((float) $item->cantidad_despacho, 0, '.', ',').' '.($producto ? $producto->detalle_cantidad : 'unidades'),
                    'ingreso_estimado' => (float) $item->ingreso_estimado,
                ];
            });

        $preventistaIds = collect((array) $request->input('preventista_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();
        $preventistasFiltro = $preventistaIds->isNotEmpty()
            ? User::whereIn('id', $preventistaIds)->orderBy('nombres')->get()
            : collect();

        $rutaIds = collect((array) $request->input('ruta_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();
        $rutasFiltro = $rutaIds->isNotEmpty()
            ? Rutas::whereIn('id', $rutaIds)->orderBy('nombre_ruta')->get()
            : collect();

        $filtros = [
            'ruta' => $rutasFiltro->isNotEmpty()
                ? $rutasFiltro->pluck('nombre_ruta')->implode(', ')
                : 'Todas las rutas',
            'preventista' => $preventistasFiltro->isNotEmpty()
                ? $preventistasFiltro->map(fn ($user) => trim($user->nombres.' '.$user->apellido_paterno.' '.$user->apellido_materno))->implode(', ')
                : 'Todos los preventistas',
            'fecha_entrega' => $request->filled('fecha_entrega') ? date('d/m/Y', strtotime($request->fecha_entrega)) : null,
        ];

        $resumen = [
            'productos' => $productos->count(),
            'unidades' => $productos->sum('cantidad_valor'),
            'total' => $productos->sum('ingreso_estimado'),
        ];

        $pdf = Pdf::loadView('administrador.pdf.pdf_consolidado_despacho', [
            'estado' => $estado,
            'productos' => $productos,
            'filtros' => $filtros,
            'resumen' => $resumen,
        ]);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('consolidado-despacho-'.$estado.'.pdf');
    }
    public function visualizacionPedido(string $numero_pedido)
    {
        $pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.id as id_producto',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'pedidos.id as id_pedido',
                'pedidos.numero_pedido',
                'pedidos.cantidad as cantidad_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
            )
            ->where('pedidos.numero_pedido', $numero_pedido)
            ->whereNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        return response()->json([
            'numero_pedido' => $numero_pedido,
            'pedidos' => $pedidos
        ],200);
    }

    public function pedidosPendientesPorProducto(string $id_producto)
    {
        $pedidos = Pedido::with(['cliente.ruta', 'usuario', 'formaVenta'])
            ->where('id_producto', $id_producto)
            ->whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->orderBy('numero_pedido')
            ->get()
            ->map(function ($pedido) {
                return [
                    'numero_pedido' => $pedido->numero_pedido,
                    'cliente' => $pedido->cliente ? trim($pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos) : 'N/A',
                    'ruta' => $pedido->cliente && $pedido->cliente->ruta ? $pedido->cliente->ruta->nombre_ruta : 'N/A',
                    'preventista' => $pedido->usuario ? trim($pedido->usuario->nombres . ' ' . $pedido->usuario->apellido_paterno . ' ' . $pedido->usuario->apellido_materno) : 'N/A',
                    'cantidad' => $pedido->cantidad . ' ' . ($pedido->formaVenta->tipo_venta ?? ''),
                    'unidades' => $pedido->formaVenta ? $pedido->cantidad * $pedido->formaVenta->equivalencia_cantidad : $pedido->cantidad,
                    'subtotal' => $pedido->formaVenta ? $pedido->cantidad * $pedido->formaVenta->precio_venta : 0,
                ];
            });

        return response()->json([
            'pedidos' => $pedidos,
            'total' => $pedidos->sum('subtotal'),
            'unidades' => $pedidos->sum('unidades'),
        ], 200);
    }

    public function pedidosDespachadosPorProducto(string $id_producto)
    {
        $pedidos = Pedido::with(['cliente.ruta', 'usuario', 'formaVenta'])
            ->where('id_producto', $id_producto)
            ->whereNotNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->orderBy('numero_pedido')
            ->get()
            ->map(function ($pedido) {
                return [
                    'numero_pedido' => $pedido->numero_pedido,
                    'cliente' => $pedido->cliente ? trim($pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos) : 'N/A',
                    'ruta' => $pedido->cliente && $pedido->cliente->ruta ? $pedido->cliente->ruta->nombre_ruta : 'N/A',
                    'preventista' => $pedido->usuario ? trim($pedido->usuario->nombres . ' ' . $pedido->usuario->apellido_paterno . ' ' . $pedido->usuario->apellido_materno) : 'N/A',
                    'fecha_entrega' => $pedido->fecha_entrega ? date('d/m/Y H:i', strtotime($pedido->fecha_entrega)) : 'Sin fecha',
                    'cantidad' => $pedido->cantidad . ' ' . ($pedido->formaVenta->tipo_venta ?? ''),
                    'unidades' => $pedido->formaVenta ? $pedido->cantidad * $pedido->formaVenta->equivalencia_cantidad : $pedido->cantidad,
                    'subtotal' => $pedido->formaVenta ? $pedido->cantidad * $pedido->formaVenta->precio_venta : 0,
                ];
            });

        return response()->json([
            'pedidos' => $pedidos,
            'total' => $pedidos->sum('subtotal'),
            'unidades' => $pedidos->sum('unidades'),
        ], 200);
    }

    public function visualizacionPdfDespachar(Request $request){
        $rutaIds = collect((array) $request->input('ruta_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();
        $preventistaIds = collect((array) $request->input('preventista_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();

        $lista_de_pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->select(
            'pedidos.numero_pedido',
            'pedidos.id_usuario as id_vendedor',
            DB::raw('DATE(pedidos.fecha_pedido) AS fecha_pedido'),
            'clientes.nombres',
            'clientes.apellidos',
            'clientes.celular',
            'clientes.calle_avenida',
            'clientes.zona_barrio',
            'ruta_id',      
            )
            ->whereNotNull('fecha_entrega')
            ->where('estado_pedido', false);

        if ($rutaIds->isNotEmpty()) {
            $lista_de_pedidos->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $lista_de_pedidos->whereIn('pedidos.id_usuario', $preventistaIds);
        }

        if ($request->filled('fecha_entrega')) {
            $lista_de_pedidos->whereDate('pedidos.fecha_entrega', $request->fecha_entrega);
        }

        $lista_de_pedidos = $lista_de_pedidos
            ->groupBy(
                'pedidos.numero_pedido',
                'pedidos.id_usuario',
                'fecha_pedido',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'ruta_id',
            )
            ->orderBy('pedidos.id_usuario', 'asc')
            ->get();
            
        $pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.id as id_producto',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'pedidos.id as id_pedido',
                'pedidos.id_usuario as id_vendedor',
                'pedidos.numero_pedido',
                'pedidos.cantidad as cantidad_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
            )
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false);

        if ($rutaIds->isNotEmpty()) {
            $pedidos->whereIn('clientes.ruta_id', $rutaIds);
        }

        if ($preventistaIds->isNotEmpty()) {
            $pedidos->whereIn('pedidos.id_usuario', $preventistaIds);
        }

        if ($request->filled('fecha_entrega')) {
            $pedidos->whereDate('pedidos.fecha_entrega', $request->fecha_entrega);
        }

        $pedidos = $pedidos
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        $pdf = Pdf::loadView('administrador.pdf.pdf_despachar', compact('pedidos', 'lista_de_pedidos'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('productosDespachados.pdf');  
    }



    public function visualizacionPdfDespacharPendientes(){
        $lista_de_pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->select(
            'pedidos.numero_pedido',
            'pedidos.id_usuario as id_vendedor',
            DB::raw('DATE(pedidos.fecha_pedido) AS fecha_pedido'),
            'clientes.nombres',
            'clientes.apellidos',
            'clientes.celular',
            'clientes.calle_avenida',
            'clientes.zona_barrio',
            'ruta_id',      
            )
            ->whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->groupBy(
                'pedidos.numero_pedido',
                'pedidos.id_usuario',
                'fecha_pedido',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'ruta_id',
            )
            ->orderBy('pedidos.id_usuario', 'asc')
            ->get();
            
        $pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.id as id_producto',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'pedidos.id as id_pedido',
                'pedidos.id_usuario as id_vendedor',
                'pedidos.numero_pedido',
                'pedidos.cantidad as cantidad_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
            )
            ->whereNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        $pdf = Pdf::loadView('administrador.pdf.pdf_despachar', compact('pedidos', 'lista_de_pedidos'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('productosDespachados.pdf');  
    }


    public function despacharPedido(){
        $cantidadPedidos = DB::transaction(function () {
            $pedidosPendientes = Pedido::whereNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->lockForUpdate()
                ->get(['id', 'numero_pedido']);

            $numerosPedido = $pedidosPendientes->pluck('numero_pedido')->unique()->values();

            if ($numerosPedido->isEmpty()) {
                return 0;
            }

            Pedido::whereIn('numero_pedido', $numerosPedido)
                ->whereNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->update(['fecha_entrega' => now()]);

            return $numerosPedido->count();
        });

        return response()->json([
            'message' => $cantidadPedidos . ' pedidos entregados al repartidor correctamente.',
            'cantidad_pedidos' => $cantidadPedidos,
        ], 200);
    }

    public function devolucionPedido(Request $request, DataTables $dataTables){

        if($request->ajax()){
            $query = $this->basePedidosDespachados()
                ->with(['cliente.ruta', 'usuario'])
                ->select('pedidos.numero_pedido', 'pedidos.id_cliente', 'pedidos.id_usuario')
                ->selectRaw('DATE(pedidos.fecha_pedido) AS fecha_pedido')
                ->selectRaw('DATE(pedidos.fecha_entrega) AS fecha_entrega')
                ->selectRaw('COUNT(*) AS items')
                ->selectRaw('SUM(pedidos.cantidad * forma_ventas.precio_venta) AS monto_estimado')
                ->groupBy('pedidos.numero_pedido', 'pedidos.id_cliente', 'pedidos.id_usuario', DB::raw('DATE(pedidos.fecha_pedido)'), DB::raw('DATE(pedidos.fecha_entrega)'))
                ->orderBy('pedidos.numero_pedido', 'asc');

            if ($request->filled('ruta_id')) {
                $query->whereHas('cliente', function ($query) use ($request) {
                    $query->where('ruta_id', $request->ruta_id);
                });
            }

            if ($request->filled('preventista_id')) {
                $query->where('pedidos.id_usuario', $request->preventista_id);
            }
            
            return $dataTables->eloquent($query)
                ->addColumn('numero_pedido', function($pedido){
                    return '<span class="return-order-number">#' . str_pad($pedido->numero_pedido, 6, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('cliente', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos : 'N/A';
                })
                ->addColumn('monto_estimado', function($pedido){
                    return '<strong class="return-total">Bs ' . number_format((float) $pedido->monto_estimado, 2, '.', ',') . '</strong>';
                })
                ->addColumn('preventista', function($pedido){
                    return $pedido->usuario ? $pedido->usuario->nombres . ' ' . $pedido->usuario->apellido_paterno . ' ' . $pedido->usuario->apellido_materno : 'N/A';
                })
                ->addColumn('ruta', function($pedido){
                    return $pedido->cliente && $pedido->cliente->ruta ? $pedido->cliente->ruta->nombre_ruta : 'N/A';
                })
                ->addColumn('items', function($pedido){
                    return '<span class="return-pill">' . (int) $pedido->items . ' productos</span>';
                })
                ->addColumn('acciones', function($pedido){
                    return '<div class="return-actions">
                        <button type="button" class="btn btn-info btn-sm return-action" onclick="abrirGestionDevolucion(this)" data-numero-pedido="' . $pedido->numero_pedido . '">
                            <i class="fas fa-undo-alt"></i> Gestionar
                        </button>
                        <button type="button" class="btn btn-danger btn-sm return-action" onclick="anularPedidoDespachado(this)" data-numero-pedido="' . $pedido->numero_pedido . '">
                            <i class="fas fa-ban"></i> Anular todo
                        </button>
                    </div>';
                })
                ->rawColumns(['numero_pedido', 'monto_estimado', 'items', 'acciones'])
                ->make(true);
        }

        $resumenPedidos = $this->resumenPedidosFlujo();
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();

        return view('administrador.pedidos.devoluciones', compact('resumenPedidos', 'rutas', 'preventistas'));
    }

    public function devolucionPedidoDevolucion(string $numero_pedido){
        $lista_de_pedidos = Pedido::join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.id as id_producto',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                'productos.foto_producto',
                'forma_ventas.tipo_venta',
                'forma_ventas.id as id_forma_venta',
                'forma_ventas.precio_venta',
                'forma_ventas.equivalencia_cantidad',
                'pedidos.id as id_pedido',
                'pedidos.numero_pedido',
                'pedidos.cantidad as cantidad_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
            )
            ->where('pedidos.numero_pedido', $numero_pedido)
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        if ($lista_de_pedidos->isEmpty()) {
            return response()->json([
                'message' => 'Este pedido no esta disponible para devoluciones.'
            ], 404);
        }

        return response()->json([
            'pedidos' => $lista_de_pedidos,
            'total' => $lista_de_pedidos->sum(fn ($pedido) => $pedido->cantidad_pedido * $pedido->precio_venta),
        ], 200);
    }
    public function devolucionPedidoDevolucionCantidad(Request $request, int $id){
        $request->validate([
            'cantidad' => 'required|integer|min:0',
        ]);

        $cantidad_actualizada = (int) $request->input('cantidad');
        return DB::transaction(function () use ($id, $cantidad_actualizada) {
        $pedido = Pedido::whereKey($id)->whereNotNull('fecha_entrega')->where('estado_pedido', false)->lockForUpdate()->firstOrFail();
        $forma_venta = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidad_anterior = $pedido->cantidad * $forma_venta->equivalencia_cantidad;
        $cantidad_nueva    = $cantidad_actualizada * $forma_venta->equivalencia_cantidad;

        $producto = Producto::whereKey($pedido->id_producto)->lockForUpdate()->firstOrFail();

        // Ajustar stock del producto según la diferencia de equivalencias
        $diferencia = $cantidad_anterior - $cantidad_nueva;
        $stock_resultante = $producto->cantidad + $diferencia;
        if ($stock_resultante < 0) {
            abort(422, 'No hay inventario suficiente para aumentar esa cantidad.');
        }
        $producto->cantidad = $stock_resultante;
        $producto->save();

        if ($cantidad_actualizada === 0) {
            $pedido->delete();
            return response()->json([
                'message' => 'Producto devuelto por completo y eliminado del pedido.'
            ], 200);
        }

        $pedido->cantidad = $cantidad_actualizada;
        $pedido->save();
        
        return response()->json([
            'message' => 'Cantidad actualizada y stock sincronizado correctamente.'
        ], 200);
        });
    }

    public function productoSelectFormasVentas(string $id_producto){
        $formas_venta=FormaVenta::where('id_producto', $id_producto)->get();
        return response()->json([
            'formas_venta' => $formas_venta
        ], 200);
    }

    public function buscarProductosParaDevolucion(Request $request)
    {
        $termino = trim((string) $request->query('q', ''));

        $productos = Producto::query()
            ->select('id', 'codigo', 'nombre_producto', 'cantidad', 'detalle_cantidad', 'foto_producto')
            ->where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->whereHas('formaVentas', function ($query) {
                $query->where('activo', true);
            })
            ->when($termino !== '', function ($query) use ($termino) {
                $query->where(function ($producto) use ($termino) {
                    $producto->where('codigo', 'ilike', "%{$termino}%")
                        ->orWhere('nombre_producto', 'ilike', "%{$termino}%");
                });
            })
            ->with(['formaVentas' => function ($query) {
                $query->where('activo', true)->orderBy('precio_venta');
            }])
            ->orderBy('nombre_producto')
            ->limit(12)
            ->get()
            ->map(fn ($producto) => [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre_producto' => $producto->nombre_producto,
                'cantidad' => (float) $producto->cantidad,
                'detalle_cantidad' => $producto->detalle_cantidad,
                'foto' => $producto->foto_producto
                    ? route('productos.imagen', ['id' => $producto->id])
                    : asset('images/logo_color.webp'),
                'formas_venta' => $producto->formaVentas->map(fn ($formaVenta) => [
                    'id' => $formaVenta->id,
                    'tipo_venta' => $formaVenta->tipo_venta,
                    'precio_venta' => (float) $formaVenta->precio_venta,
                    'equivalencia_cantidad' => (float) $formaVenta->equivalencia_cantidad,
                ]),
            ]);

        return response()->json(['productos' => $productos], 200);
    }

    public function agregarProductoADevolucion(Request $request, string $numero_pedido)
    {
        $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'id_forma_venta' => 'required|integer|exists:forma_ventas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $resultado = DB::transaction(function () use ($request, $numero_pedido) {
            $pedidoBase = Pedido::where('numero_pedido', $numero_pedido)
                ->whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->lockForUpdate()
                ->firstOrFail();

            $formaVenta = FormaVenta::whereKey($request->id_forma_venta)
                ->where('id_producto', $request->id_producto)
                ->where('activo', true)
                ->firstOrFail();

            $producto = Producto::whereKey($request->id_producto)
                ->where('estado_de_baja', false)
                ->lockForUpdate()
                ->firstOrFail();

            $cantidad = (int) $request->cantidad;
            $cantidadInventario = $cantidad * $formaVenta->equivalencia_cantidad;

            if ($producto->cantidad < $cantidadInventario) {
                abort(409, 'Stock insuficiente: ' . $producto->nombre_producto . ' solo tiene ' . $producto->cantidad . ' ' . $producto->detalle_cantidad . '.');
            }

            $pedido = Pedido::where('numero_pedido', $numero_pedido)
                ->where('id_producto', $producto->id)
                ->where('id_forma_venta', $formaVenta->id)
                ->whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->lockForUpdate()
                ->first();

            if ($pedido) {
                $pedido->cantidad += $cantidad;
            } else {
                $pedido = new Pedido();
                $pedido->id_usuario = $pedidoBase->id_usuario;
                $pedido->id_cliente = $pedidoBase->id_cliente;
                $pedido->id_producto = $producto->id;
                $pedido->id_forma_venta = $formaVenta->id;
                $pedido->numero_pedido = $pedidoBase->numero_pedido;
                $pedido->fecha_pedido = $pedidoBase->fecha_pedido;
                $pedido->fecha_entrega = $pedidoBase->fecha_entrega;
                $pedido->cantidad = $cantidad;
                $pedido->estado_pedido = false;
                $pedido->promocion = (bool) $producto->promocion;
                $pedido->descripcion_descuento_porcentaje = $producto->promocion ? $producto->descripcion_descuento_porcentaje : null;
                $pedido->descripcion_regalo = $producto->promocion ? $producto->descripcion_regalo : null;
            }

            $pedido->save();

            $producto->cantidad -= $cantidadInventario;
            $producto->save();

            return [
                'producto' => $producto->nombre_producto,
                'cantidad' => $pedido->cantidad,
            ];
        }, 3);

        return response()->json([
            'message' => 'Producto agregado al pedido y stock actualizado correctamente.',
            'producto' => $resultado['producto'],
            'cantidad_total' => $resultado['cantidad'],
        ], 201);
    }

    public function productoSelectActualizar(Request $request, int $id_pedido)
    {
        $request->validate([
            'tipo_venta_id' => 'required|integer|exists:forma_ventas,id'
        ]);

        $id_forma_venta = $request->input('tipo_venta_id');
        return DB::transaction(function () use ($id_pedido, $id_forma_venta) {
        $pedido = Pedido::whereKey($id_pedido)->whereNotNull('fecha_entrega')->where('estado_pedido', false)->lockForUpdate()->firstOrFail();

        $forma_venta_nueva = FormaVenta::whereKey($id_forma_venta)->where('id_producto', $pedido->id_producto)->firstOrFail();
        $forma_venta_anterior = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidad_anterior = $pedido->cantidad * $forma_venta_anterior->equivalencia_cantidad;
        $cantidad_nueva    = $pedido->cantidad * $forma_venta_nueva->equivalencia_cantidad;

        $producto = Producto::whereKey($pedido->id_producto)->lockForUpdate()->firstOrFail();

        // Ajustar stock del producto según la diferencia de equivalencias
        $diferencia = $cantidad_anterior - $cantidad_nueva;
        $stock_resultante = $producto->cantidad + $diferencia;
        if ($stock_resultante < 0) {
            abort(422, 'No hay inventario suficiente para cambiar a esa forma de venta.');
        }
        $producto->cantidad = $stock_resultante;
        $producto->save();

        // Actualizar la forma de venta del pedido
        $pedido->id_forma_venta = $id_forma_venta;
        $pedido->save();

        return response()->json([
            'message' => 'Forma de venta actualizada y stock sincronizado correctamente.'
        ], 200);
        });
    }


    public function productoEliminarPromocion(string $id_pedido){
        $pedido = Pedido::findOrFail($id_pedido);
        $pedido->promocion = false;
        $pedido->descripcion_descuento_porcentaje = null;
        $pedido->descripcion_regalo = null;
        $pedido->save();
        return response()->json([
            'message' => 'Promoción eliminada correctamente.'
        ], 200);
    }

    public function productoEliminarPromocionTotal(string $id_pedido){
        DB::transaction(function () use ($id_pedido) {
            $pedido = Pedido::whereKey($id_pedido)->whereNotNull('fecha_entrega')->where('estado_pedido', false)->lockForUpdate()->firstOrFail();
            $producto = Producto::whereKey($pedido->id_producto)->lockForUpdate()->firstOrFail();
            $formas_venta = FormaVenta::findOrFail($pedido->id_forma_venta);
            $producto->cantidad += ($pedido->cantidad * $formas_venta->equivalencia_cantidad);
            $producto->save();
            $pedido->delete();
        });
        return response()->json([
            'message' => 'Producto devuelto por completo y retirado del pedido.'
        ], 200);
    }

    public function anularPedidoDespachado(string $numero_pedido)
    {
        $resultado = DB::transaction(function () use ($numero_pedido) {
            $pedidos = Pedido::where('numero_pedido', $numero_pedido)
                ->whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->lockForUpdate()
                ->get();

            if ($pedidos->isEmpty()) {
                abort(404, 'Este pedido ya fue contabilizado, anulado o no esta despachado.');
            }

            foreach ($pedidos as $pedido) {
                $producto = Producto::whereKey($pedido->id_producto)->lockForUpdate()->first();
                $formaVenta = FormaVenta::find($pedido->id_forma_venta);

                if ($producto && $formaVenta) {
                    $producto->cantidad += ($pedido->cantidad * $formaVenta->equivalencia_cantidad);
                    $producto->save();
                }

                $pedido->delete();
            }

            return $pedidos->count();
        });

        return response()->json([
            'message' => $resultado . ' productos devueltos. Pedido anulado antes de contabilizar.',
            'productos_devueltos' => $resultado,
        ], 200);
    }

    /**
     * 
     * 
     */

    public function contabilizarPedidosPendientes(){
        $resultado = DB::transaction(function () {
            $pedidosPendientes = Pedido::whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->lockForUpdate()
                ->get();

            if ($pedidosPendientes->isEmpty()) {
                return [
                    'pedidos' => collect(),
                    'cantidad_pedidos' => 0,
                    'total' => 0,
                ];
            }

            $numerosPedido = $pedidosPendientes->pluck('numero_pedido')->unique()->values();
            Venta::whereIn('numero_pedido', $numerosPedido)->delete();
            $fechaContabilizacion = now();

            foreach ($pedidosPendientes as $pedido) {
                $pedido->estado_pedido = true;
                $pedido->save();

                Venta::create([
                    'id_usuario' => $pedido->id_usuario,
                    'id_cliente' => $pedido->id_cliente,
                    'id_producto' => $pedido->id_producto,
                    'id_forma_venta' => $pedido->id_forma_venta,
                    'numero_pedido' => $pedido->numero_pedido,
                    'fecha_contabilizacion' => $fechaContabilizacion,
                    'cantidad' => $pedido->cantidad,
                    'promocion' => $pedido->promocion,
                    'descripcion_descuento_porcentaje' => $pedido->descripcion_descuento_porcentaje,
                    'descripcion_regalo' => $pedido->descripcion_regalo
                ]);
            }

            $total = Pedido::whereIn('pedidos.numero_pedido', $numerosPedido)
                ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
                ->sum(DB::raw('pedidos.cantidad * forma_ventas.precio_venta'));

            return [
                'pedidos' => $pedidosPendientes,
                'cantidad_pedidos' => $numerosPedido->count(),
                'total' => (float) $total,
            ];
        });

        return response()->json([
            'pedidosPendientes' => $resultado['pedidos'],
            'cantidad_pedidos' => $resultado['cantidad_pedidos'],
            'total' => $resultado['total'],
            'message' => $resultado['cantidad_pedidos'] . ' pedidos contabilizados correctamente.',
        ], 200);
    }




    ////---------------------------EDITAR PEDIDO------------------------////
    public function editarPedido(Request $request, string $id_numero_pedido)
    {
        if(Pedido::where('numero_pedido', $id_numero_pedido)->doesntExist()){
            return redirect()->route('administrador.pedidos.administrador.visualizacion');
        }

        if ($request->ajax()) {
            $pedido = Pedido::query()->where('numero_pedido', $id_numero_pedido);

            return DataTables::of($pedido)
                ->addColumn('imagen', function ($p) {
                    if ($p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                })
                ->addColumn('producto', function ($p) {
                    return $p->producto ? $p->producto->nombre_producto : 'N/A';
                })
                ->addColumn('precio_venta', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $forma_venta ? $forma_venta->precio_venta.' Bs.-' : 'N/A';
                })
                ->addColumn('cantidad', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $p->cantidad .' '. $forma_venta->tipo_venta;
                })
                ->addColumn('cantidad_stock', function ($p) {
                    return $p->producto ? (int) ($p->producto->cantidad ?? 0).' '.$p->producto->detalle_cantidad: 'N/A';
                })
                ->addColumn('subtotal', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    $precio_venta = $forma_venta ? $forma_venta->precio_venta : 0;
                    $cantidad = $p->cantidad ?? 0;
                    return $precio_venta * $cantidad.' Bs.-';
                })
                ->addColumn('acciones', function ($p) {
                    $id = $p->id;
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="eliminarProductoPedido(this)" data-id-pedido="'.$id.'">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['imagen','acciones'])
                ->make(true);
        }

        $pedido = Pedido::where('numero_pedido', $id_numero_pedido)->firstOrFail();
        $suma_pedido= Pedido::where('numero_pedido', $id_numero_pedido)
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        return view('administrador.pedidos.editar_pedido', compact('pedido','suma_pedido'));
    }


    public function agregarProductoPedido(Request $request, string $id_numero_pedido)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_venta_id' => 'required|exists:forma_ventas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->input('producto_id'));
        $formaVenta = FormaVenta::findOrFail($request->input('tipo_venta_id'));
        $cantidadSolicitada = $request->cantidad;
        $cantidadEnUnidades = $cantidadSolicitada * $formaVenta->equivalencia_cantidad;

        if ($producto->cantidad < $cantidadEnUnidades) {
            return response()->json(['error' => 'No hay suficiente stock del producto.'], 400);
        }

        // Disminuir el stock del producto
        $producto->cantidad -= $cantidadEnUnidades;
        $producto->save();

        Pedido::create([
            'id_usuario' => Pedido::where('numero_pedido', $id_numero_pedido)->value('id_usuario'),
            'id_cliente' => Pedido::where('numero_pedido', $id_numero_pedido)->value('id_cliente'),
            'id_producto' => $producto->id,
            'id_forma_venta' => $formaVenta->id,
            'numero_pedido' => $id_numero_pedido,
            'fecha_pedido' => Pedido::where('numero_pedido', $id_numero_pedido)->value('fecha_pedido')??null,
            'fecha_entrega' => null,
            'cantidad' => $cantidadSolicitada,
            'estado_pedido' => false,
            'promocion' => $producto->promocion ?? false,
            'descripcion_descuento_porcentaje' => $producto->descripcion_descuento_porcentaje ?? null,
            'descripcion_regalo' => $producto->descripcion_regalo ?? null,
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Producto agregado al pedido correctamente.']);
    }

    public function eliminarProductoPedido(string $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);
        $producto = Producto::findOrFail($pedido->id_producto);
        $formaVenta = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidadEnUnidades = $pedido->cantidad * $formaVenta->equivalencia_cantidad;

        // Aumentar el stock del producto
        $producto->cantidad += $cantidadEnUnidades;
        $producto->save();

        $pedido->delete();

        return response()->json(['success' => true, 'mensaje' => 'Producto eliminado del pedido correctamente.']);
    }


    public function editarPedidoDespachado(Request $request, string $id_numero_pedido)
    {
        if(Pedido::where('numero_pedido', $id_numero_pedido)->doesntExist()){
            return redirect()->route('administrador.pedidos.administrador.visualizacion');
        }

        if ($request->ajax()) {
            $pedido = Pedido::query()->where('numero_pedido', $id_numero_pedido);

            return DataTables::of($pedido)
                ->addColumn('imagen', function ($p) {
                    if ($p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                })
                ->addColumn('producto', function ($p) {
                    return $p->producto ? $p->producto->nombre_producto : 'N/A';
                })
                ->addColumn('precio_venta', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $forma_venta ? $forma_venta->precio_venta.' Bs.-' : 'N/A';
                })
                ->addColumn('cantidad', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $p->cantidad .' '. $forma_venta->tipo_venta;
                })
                ->addColumn('cantidad_stock', function ($p) {
                    return $p->producto ? (int) ($p->producto->cantidad ?? 0).' '.$p->producto->detalle_cantidad: 'N/A';
                })
                ->addColumn('subtotal', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    $precio_venta = $forma_venta ? $forma_venta->precio_venta : 0;
                    $cantidad = $p->cantidad ?? 0;
                    return $precio_venta * $cantidad.' Bs.-';
                })
                ->addColumn('acciones', function ($p) {
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger btn-sm" disabled>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['imagen','acciones'])
                ->make(true);
        }
        $pedido = Pedido::where('numero_pedido', $id_numero_pedido)->firstOrFail();
        $suma_pedido= Pedido::where('numero_pedido', $id_numero_pedido)
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        return view('administrador.pedidos.editar_pedido_despachado', compact('pedido','suma_pedido'));
    }

    public function agregarProductoPedidoDespachado(Request $request, string $id_numero_pedido)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_venta_id' => 'required|exists:forma_ventas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->input('producto_id'));
        $formaVenta = FormaVenta::findOrFail($request->input('tipo_venta_id'));
        $cantidadSolicitada = $request->cantidad;
        $cantidadEnUnidades = $cantidadSolicitada * $formaVenta->equivalencia_cantidad;

        if ($producto->cantidad < $cantidadEnUnidades) {
            return response()->json(['error' => 'No hay suficiente stock del producto.'], 400);
        }

        // Disminuir el stock del producto
        $producto->cantidad -= $cantidadEnUnidades;
        $producto->save();

        Pedido::create([
            'id_usuario' => Pedido::where('numero_pedido', $id_numero_pedido)->value('id_usuario'),
            'id_cliente' => Pedido::where('numero_pedido', $id_numero_pedido)->value('id_cliente'),
            'id_producto' => $producto->id,
            'id_forma_venta' => $formaVenta->id,
            'numero_pedido' => $id_numero_pedido,
            'fecha_pedido' => Pedido::where('numero_pedido', $id_numero_pedido)->value('fecha_pedido')??null,
            'fecha_entrega' => Pedido::where('numero_pedido', $id_numero_pedido)->value('fecha_entrega')??null,
            'cantidad' => $cantidadSolicitada,
            'estado_pedido' => false,
            'promocion' => $producto->promocion ?? false,
            'descripcion_descuento_porcentaje' => $producto->descripcion_descuento_porcentaje ?? null,
            'descripcion_regalo' => $producto->descripcion_regalo ?? null,
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Producto agregado al pedido correctamente.']);
    }

    public function eliminarProductoPedidoDespachado(string $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);
        $producto = Producto::findOrFail($pedido->id_producto);
        $formaVenta = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidadEnUnidades = $pedido->cantidad * $formaVenta->equivalencia_cantidad;

        // Aumentar el stock del producto
        $producto->cantidad += $cantidadEnUnidades;
        $producto->save();

        $pedido->delete();

        return response()->json(['success' => true, 'mensaje' => 'Producto eliminado del pedido correctamente.']);
    }


    public function visualizacionContabilizados(Request $request, DataTables $dataTables){
        if($request->ajax()){
            $pedidosFechas = Pedido::query()
                ->select('numero_pedido')
                ->selectRaw('MIN(fecha_pedido) AS primera_fecha_pedido')
                ->selectRaw('MAX(fecha_entrega) AS ultima_fecha_entrega')
                ->groupBy('numero_pedido');

            $query = Venta::query()
                ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
                ->leftJoinSub($pedidosFechas, 'pedidos_fechas', function ($join) {
                    $join->on('pedidos_fechas.numero_pedido', '=', 'ventas.numero_pedido');
                })
                ->selectRaw('DATE(ventas.fecha_contabilizacion) AS fecha_contabilizacion')
                ->selectRaw('DATE(MIN(pedidos_fechas.primera_fecha_pedido)) AS primera_fecha_pedido')
                ->selectRaw('DATE(MAX(pedidos_fechas.ultima_fecha_entrega)) AS ultima_fecha_entrega')
                ->selectRaw('COUNT(DISTINCT ventas.numero_pedido) AS pedidos')
                ->selectRaw('COUNT(*) AS items')
                ->selectRaw('COUNT(DISTINCT ventas.id_usuario) AS preventistas')
                ->selectRaw('SUM(ventas.cantidad * forma_ventas.precio_venta) AS total')
                ->whereNotNull('ventas.fecha_contabilizacion')
                ->groupBy(DB::raw('DATE(ventas.fecha_contabilizacion)'))
                ->orderByDesc(DB::raw('DATE(ventas.fecha_contabilizacion)'));

            if ($request->filled('ruta_id')) {
                $query->where('clientes.ruta_id', $request->ruta_id);
            }

            if ($request->filled('preventista_id')) {
                $query->where('ventas.id_usuario', $request->preventista_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('ventas.fecha_contabilizacion', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('ventas.fecha_contabilizacion', '<=', $request->fecha_hasta);
            }

            return $dataTables->eloquent($query)
                ->addColumn('fecha_contabilizacion', function($cierre){
                    return '<span class="closed-order-number">' . date('d/m/Y', strtotime($cierre->fecha_contabilizacion)) . '</span>';
                })
                ->addColumn('fechas_operacion', function($cierre){
                    $fechaPedido = $cierre->primera_fecha_pedido ? date('d/m/Y', strtotime($cierre->primera_fecha_pedido)) : 'N/A';
                    $fechaEntrega = $cierre->ultima_fecha_entrega ? date('d/m/Y', strtotime($cierre->ultima_fecha_entrega)) : 'N/A';
                    return '<div class="closed-date-stack"><strong>Pedido: ' . $fechaPedido . '</strong><span>Entrega: ' . $fechaEntrega . '</span></div>';
                })
                ->addColumn('pedidos', function($cierre){
                    return '<span class="closed-pill">' . (int) $cierre->pedidos . ' pedidos</span>';
                })
                ->addColumn('items', function($cierre){
                    return '<span class="closed-pill">' . (int) $cierre->items . ' productos</span>';
                })
                ->addColumn('preventistas', function($cierre){
                    return '<span class="closed-pill">' . (int) $cierre->preventistas . ' preventistas</span>';
                })
                ->addColumn('total', function($cierre){
                    return '<strong class="closed-total">Bs ' . number_format((float) $cierre->total, 2, '.', ',') . '</strong>';
                })
                ->addColumn('acciones', function($cierre){
                    $fecha = date('Y-m-d', strtotime($cierre->fecha_contabilizacion));
                    return '<div class="closed-actions">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm closed-action"
                            onclick="verPedidosDeFecha(this)"
                            data-fecha="'.$fecha.'"
                        >
                            <i class="fas fa-folder-open"></i> Entrar
                        </button>
                    </div>';
                })
                ->rawColumns(['fecha_contabilizacion', 'fechas_operacion', 'pedidos', 'items', 'preventistas', 'total', 'acciones'])
                ->make(true);
        }
        $resumenPedidos = $this->resumenPedidosFlujo();
        $rutas = Rutas::orderBy('nombre_ruta')->get();
        $preventistas = User::role('vendedor')->orderBy('nombres')->get();
        $resumenContabilizados = [
            'pedidos' => Pedido::where('estado_pedido', true)->whereNotNull('fecha_entrega')->distinct('numero_pedido')->count('numero_pedido'),
            'items' => Pedido::where('estado_pedido', true)->whereNotNull('fecha_entrega')->count(),
            'total' => Pedido::where('estado_pedido', true)
                ->whereNotNull('fecha_entrega')
                ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
                ->sum(DB::raw('pedidos.cantidad * forma_ventas.precio_venta')),
            'hoy' => Venta::whereDate('fecha_contabilizacion', now()->toDateString())->distinct('numero_pedido')->count('numero_pedido'),
        ];

        return view('administrador.pedidos.contabilizados', compact('resumenPedidos', 'rutas', 'preventistas', 'resumenContabilizados'));
    }

    public function pedidosContabilizadosPorFecha(Request $request, string $fecha)
    {
        $pedidosFechas = Pedido::query()
            ->select('numero_pedido')
            ->selectRaw('MIN(fecha_pedido) AS fecha_pedido')
            ->selectRaw('MIN(fecha_entrega) AS fecha_entrega')
            ->groupBy('numero_pedido');

        $query = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'ventas.id_usuario', '=', 'users.id')
            ->leftJoinSub($pedidosFechas, 'pedidos_fechas', function ($join) {
                $join->on('pedidos_fechas.numero_pedido', '=', 'ventas.numero_pedido');
            })
            ->select('ventas.numero_pedido', 'ventas.id_cliente', 'ventas.id_usuario')
            ->selectRaw("CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, '')) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'N/A') AS ruta")
            ->selectRaw("CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, '')) AS preventista")
            ->selectRaw('DATE(pedidos_fechas.fecha_pedido) AS fecha_pedido')
            ->selectRaw('DATE(pedidos_fechas.fecha_entrega) AS fecha_entrega')
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('SUM(ventas.cantidad * forma_ventas.precio_venta) AS total')
            ->whereDate('ventas.fecha_contabilizacion', $fecha)
            ->groupBy('ventas.numero_pedido', 'ventas.id_cliente', 'ventas.id_usuario', 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno', 'pedidos_fechas.fecha_pedido', 'pedidos_fechas.fecha_entrega')
            ->orderBy('ventas.numero_pedido');

        if ($request->filled('ruta_id')) {
            $query->where('clientes.ruta_id', $request->ruta_id);
        }

        if ($request->filled('preventista_id')) {
            $query->where('ventas.id_usuario', $request->preventista_id);
        }

        $pedidos = $query->get()->map(function ($pedido) {
            return [
                'numero_pedido' => $pedido->numero_pedido,
                'cliente' => trim($pedido->cliente) ?: 'N/A',
                'ruta' => $pedido->ruta,
                'preventista' => trim($pedido->preventista) ?: 'N/A',
                'fecha_pedido' => $pedido->fecha_pedido ? date('d/m/Y', strtotime($pedido->fecha_pedido)) : 'N/A',
                'fecha_entrega' => $pedido->fecha_entrega ? date('d/m/Y', strtotime($pedido->fecha_entrega)) : 'N/A',
                'items' => (int) $pedido->items,
                'total' => (float) $pedido->total,
                'editar_url' => route('administrador.pedidos.administrador.editar.contabilizados', $pedido->numero_pedido),
            ];
        });

        return response()->json([
            'fecha' => $fecha,
            'fecha_formateada' => date('d/m/Y', strtotime($fecha)),
            'pedidos' => $pedidos,
            'total' => $pedidos->sum('total'),
            'cantidad_pedidos' => $pedidos->count(),
            'items' => $pedidos->sum('items'),
        ], 200);
    }

    public function detallePedidoContabilizado(string $numero_pedido)
    {
        $pedidos = Pedido::with(['producto', 'formaVenta', 'cliente.ruta', 'usuario'])
            ->where('numero_pedido', $numero_pedido)
            ->where('estado_pedido', true)
            ->whereNotNull('fecha_entrega')
            ->orderBy('id')
            ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'Pedido contabilizado no encontrado.'], 404);
        }

        $fechaContabilizacion = Venta::where('numero_pedido', $numero_pedido)->min('fecha_contabilizacion');
        $lineas = $pedidos->map(function ($pedido) {
            $precio = $pedido->formaVenta ? (float) $pedido->formaVenta->precio_venta : 0;
            return [
                'producto' => $pedido->producto->nombre_producto ?? 'N/A',
                'codigo' => $pedido->producto->codigo ?? 'N/A',
                'cantidad' => $pedido->cantidad,
                'forma_venta' => $pedido->formaVenta->tipo_venta ?? 'N/A',
                'precio' => $precio,
                'subtotal' => $precio * $pedido->cantidad,
                'promocion' => (bool) $pedido->promocion,
                'regalo' => $pedido->descripcion_regalo,
                'descuento' => $pedido->descripcion_descuento_porcentaje,
            ];
        });
        $pedidoBase = $pedidos->first();

        return response()->json([
            'numero_pedido' => $numero_pedido,
            'cliente' => $pedidoBase->cliente ? trim($pedidoBase->cliente->nombres . ' ' . $pedidoBase->cliente->apellidos) : 'N/A',
            'ruta' => $pedidoBase->cliente && $pedidoBase->cliente->ruta ? $pedidoBase->cliente->ruta->nombre_ruta : 'N/A',
            'preventista' => $pedidoBase->usuario ? trim($pedidoBase->usuario->nombres . ' ' . $pedidoBase->usuario->apellido_paterno . ' ' . $pedidoBase->usuario->apellido_materno) : 'N/A',
            'fecha_pedido' => $pedidoBase->fecha_pedido ? date('d/m/Y', strtotime($pedidoBase->fecha_pedido)) : 'N/A',
            'fecha_entrega' => $pedidoBase->fecha_entrega ? date('d/m/Y', strtotime($pedidoBase->fecha_entrega)) : 'N/A',
            'fecha_contabilizacion' => $fechaContabilizacion ? date('d/m/Y', strtotime($fechaContabilizacion)) : 'Sin venta',
            'lineas' => $lineas,
            'total' => $lineas->sum('subtotal'),
        ], 200);
    }

    public function editarPedidoContabilizado(Request $request, string $id_numero_pedido)
    {
        if(Pedido::where('numero_pedido', $id_numero_pedido)->doesntExist()){
            return redirect()->route('administrador.pedidos.administrador.visualizacion');
        }

        if ($request->ajax()) {
            $pedido = Pedido::query()->where('numero_pedido', $id_numero_pedido);

            return DataTables::of($pedido)
                ->addColumn('imagen', function ($p) {
                    if ($p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                })
                ->addColumn('producto', function ($p) {
                    return $p->producto ? $p->producto->nombre_producto : 'N/A';
                })
                ->addColumn('precio_venta', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $forma_venta ? $forma_venta->precio_venta.' Bs.-' : 'N/A';
                })
                ->addColumn('cantidad', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    return $p->cantidad .' '. $forma_venta->tipo_venta;
                })
                ->addColumn('cantidad_stock', function ($p) {
                    return $p->producto ? (int) ($p->producto->cantidad ?? 0).' '.$p->producto->detalle_cantidad: 'N/A';
                })
                ->addColumn('subtotal', function ($p) {
                    $forma_venta=FormaVenta::find($p->id_forma_venta);
                    $precio_venta = $forma_venta ? $forma_venta->precio_venta : 0;
                    $cantidad = $p->cantidad ?? 0;
                    return $precio_venta * $cantidad.' Bs.-';
                })
                ->addColumn('acciones', function ($p) {
                    $id = $p->id;
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="eliminarProductoPedido(this)" data-id-pedido="'.$id.'">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['imagen','acciones'])
                ->make(true);
        }

        $pedido = Pedido::where('numero_pedido', $id_numero_pedido)->firstOrFail();
        $suma_pedido= Pedido::where('numero_pedido', $id_numero_pedido)
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        return view('administrador.pedidos.editar_pedido_contabilizado', compact('pedido','suma_pedido'));
    }

    public function agregarProductoPedidoContabilizado(Request $request, string $id_numero_pedido){
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_venta_id' => 'required|exists:forma_ventas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->input('producto_id'));
        $formaVenta = FormaVenta::findOrFail($request->input('tipo_venta_id'));
        $cantidadSolicitada = $request->cantidad;
        $cantidadEnUnidades = $cantidadSolicitada * $formaVenta->equivalencia_cantidad;

        if ($producto->cantidad < $cantidadEnUnidades) {
            return response()->json(['error' => 'No hay suficiente stock del producto.'], 400);
        }

        // Disminuir el stock del producto
        $producto->cantidad -= $cantidadEnUnidades;
        $producto->save();

        $pedido_actual=Pedido::where('numero_pedido', $id_numero_pedido)->firstOrFail();

        Pedido::create([
            'id_usuario' => $pedido_actual->id_usuario,
            'id_cliente' => $pedido_actual->id_cliente,
            'id_producto' => $producto->id,
            'id_forma_venta' => $formaVenta->id,
            'numero_pedido' => $pedido_actual->numero_pedido,
            'fecha_pedido' => $pedido_actual->fecha_pedido??null,
            'fecha_entrega' => $pedido_actual->fecha_entrega??null,
            'cantidad' => $cantidadSolicitada,
            'estado_pedido' => true,
            'promocion' => $producto->promocion ?? false,
            'descripcion_descuento_porcentaje' => $producto->descripcion_descuento_porcentaje ?? null,
            'descripcion_regalo' => $producto->descripcion_regalo ?? null,
        ]);

        if($pedido_actual->estado_pedido==true){
            $venta_contabilizada=Venta::where('numero_pedido', $pedido_actual->numero_pedido)->first();
            Venta::where('numero_pedido', $pedido_actual->numero_pedido)->delete();
            $pedidos_restantes=Pedido::where('numero_pedido', $pedido_actual->numero_pedido)->get();
            foreach($pedidos_restantes as $p){
                Venta::create([
                    'id_usuario' => $p->id_usuario,
                    'id_cliente' => $p->id_cliente,
                    'id_producto' => $p->id_producto,
                    'id_forma_venta' => $p->id_forma_venta,
                    'numero_pedido' => $p->numero_pedido,
                    'fecha_contabilizacion' => $venta_contabilizada->fecha_contabilizacion,
                    'cantidad' => $p->cantidad,
                    'promocion' => $p->promocion,
                    'descripcion_descuento_porcentaje' => $p->descripcion_descuento_porcentaje,
                    'descripcion_regalo' => $p->descripcion_regalo
                ]);
            }
        }

        return response()->json(['success' => true, 'mensaje' => 'Producto agregado al pedido correctamente.']);
    }

    public function eliminarProductoPedidoContabilizado(string $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);
        $producto = Producto::findOrFail($pedido->id_producto);
        $formaVenta = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidadEnUnidades = $pedido->cantidad * $formaVenta->equivalencia_cantidad;

        // Aumentar el stock del producto
        $producto->cantidad += $cantidadEnUnidades;
        $producto->save();

        $pedido->delete();

        if(Pedido::where('numero_pedido', $pedido->numero_pedido)->where('estado_pedido', true)->exists()){
            $venta_contabilizada=Venta::where('numero_pedido', $pedido->numero_pedido)->first();
            Venta::where('numero_pedido', $pedido->numero_pedido)->delete();
            $pedidos_restantes=Pedido::where('numero_pedido', $pedido->numero_pedido)->get();
            foreach($pedidos_restantes as $p){
                Venta::create([
                    'id_usuario' => $p->id_usuario,
                    'id_cliente' => $p->id_cliente,
                    'id_producto' => $p->id_producto,
                    'id_forma_venta' => $p->id_forma_venta,
                    'numero_pedido' => $p->numero_pedido,
                    'fecha_contabilizacion' => $venta_contabilizada->fecha_contabilizacion,
                    'cantidad' => $p->cantidad,
                    'promocion' => $p->promocion,
                    'descripcion_descuento_porcentaje' => $p->descripcion_descuento_porcentaje,
                    'descripcion_regalo' => $p->descripcion_regalo
                ]);
            }
        }
        else{
            Venta::where('numero_pedido', $pedido->numero_pedido)->delete();
        }

        return response()->json(['success' => true, 'mensaje' => 'Producto eliminado del pedido correctamente.']);
    }


    public function recontabilizarPedido(Request $request, string $numero_pedido){
        $request->validate([
            'fecha_contabilizacion' => 'required|date',
        ]);

        $resultado = DB::transaction(function () use ($request, $numero_pedido) {
            $pedidos_restantes = Pedido::where('numero_pedido', $numero_pedido)
                ->where('estado_pedido', true)
                ->whereNotNull('fecha_entrega')
                ->lockForUpdate()
                ->get();

            if ($pedidos_restantes->isEmpty()) {
                abort(404, 'El pedido no esta contabilizado.');
            }

            Venta::where('numero_pedido', $numero_pedido)->delete();

            foreach($pedidos_restantes as $p){
                Venta::create([
                    'id_usuario' => $p->id_usuario,
                    'id_cliente' => $p->id_cliente,
                    'id_producto' => $p->id_producto,
                    'id_forma_venta' => $p->id_forma_venta,
                    'numero_pedido' => $p->numero_pedido,
                    'fecha_contabilizacion' => $request->input('fecha_contabilizacion').' 00:00:00',
                    'cantidad' => $p->cantidad,
                    'promocion' => $p->promocion,
                    'descripcion_descuento_porcentaje' => $p->descripcion_descuento_porcentaje,
                    'descripcion_regalo' => $p->descripcion_regalo
                ]);
            }

            return $pedidos_restantes->count();
        });

        return response()->json([
            'success' => true,
            'mensaje' => 'Pedido recontabilizado correctamente.',
            'items' => $resultado,
        ], 200);
    }

    private function basePedidosPendientes()
    {
        return Pedido::query()
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->whereNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false);
    }

    private function basePedidosDespachados()
    {
        return Pedido::query()
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false);
    }

    private function consolidadoProductosDespachoQuery(string $estado, Request $request)
    {
        $query = $estado === 'despachados'
            ? $this->basePedidosDespachados()
            : $this->basePedidosPendientes();

        $query->with('producto')
            ->select('pedidos.id_producto')
            ->selectRaw('COUNT(DISTINCT pedidos.numero_pedido) AS pedidos_involucrados')
            ->selectRaw('SUM(pedidos.cantidad * forma_ventas.equivalencia_cantidad) AS cantidad_despacho')
            ->selectRaw('SUM(pedidos.cantidad * forma_ventas.precio_venta) AS ingreso_estimado')
            ->groupBy('pedidos.id_producto');

        $rutaIds = collect((array) $request->input('ruta_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();

        if ($rutaIds->isNotEmpty()) {
            $query->whereHas('cliente', function ($query) use ($rutaIds) {
                $query->whereIn('ruta_id', $rutaIds);
            });
        }

        $preventistaIds = collect((array) $request->input('preventista_id', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();

        if ($preventistaIds->isNotEmpty()) {
            $query->whereIn('pedidos.id_usuario', $preventistaIds);
        }

        if ($estado === 'despachados' && $request->filled('fecha_entrega')) {
            $query->whereDate('pedidos.fecha_entrega', $request->fecha_entrega);
        }

        return $query;
    }

    private function dataTableConsolidadoDespacho(DataTables $dataTables, $query)
    {
        return $dataTables->eloquent($query)
            ->addColumn('imagen', function ($p){
                if ($p->producto && $p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                    return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail dispatch-product-image">';
                }
                return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail dispatch-product-image">';
            })
            ->addColumn('codigo_producto', function ($p) {
                return $p->producto ? $p->producto->codigo : 'N/A';
            })
            ->filterColumn('codigo_producto', function ($query, $keyword) {
                $query->whereHas('producto', function ($q) use ($keyword) {
                    $q->where('codigo', 'ilike', "%{$keyword}%");
                });
            })
            ->addColumn('nombre_producto', function ($p) {
                return $p->producto ? $p->producto->nombre_producto : 'N/A';
            })
            ->filterColumn('nombre_producto', function ($query, $keyword) {
                $query->whereHas('producto', function ($q) use ($keyword) {
                    $q->where('nombre_producto', 'ilike', "%{$keyword}%");
                });
            })
            ->addColumn('stock_producto', function ($p) {
                return $p->producto ? (int) ($p->producto->cantidad ?? 0).' '.$p->producto->detalle_cantidad: 'N/A';
            })
            ->addColumn('cantidad_despacho', function ($p) {
                $unidad = $p->producto ? $p->producto->detalle_cantidad : 'unidades';
                return '<strong class="dispatch-quantity">' . number_format((float) $p->cantidad_despacho, 0, '.', ',') . ' ' . e($unidad) . '</strong>';
            })
            ->addColumn('ingreso_estimado', function ($p) {
                return '<strong class="dispatch-money">Bs ' . number_format((float) $p->ingreso_estimado, 2, '.', ',') . '</strong>';
            })
            ->rawColumns(['imagen', 'cantidad_despacho', 'ingreso_estimado'])
            ->make(true);
    }

    private function resumenPedidosFlujo(): array
    {
        $pendientes = $this->basePedidosPendientes();
        $despachados = $this->basePedidosDespachados();

        return [
            'pendientes' => (clone $pendientes)->distinct('pedidos.numero_pedido')->count('pedidos.numero_pedido'),
            'pendientes_items' => (clone $pendientes)->count(),
            'pendientes_total' => (float) (clone $pendientes)->sum(DB::raw('pedidos.cantidad * forma_ventas.precio_venta')),
            'despachados' => (clone $despachados)->distinct('pedidos.numero_pedido')->count('pedidos.numero_pedido'),
            'despachados_total' => (float) (clone $despachados)->sum(DB::raw('pedidos.cantidad * forma_ventas.precio_venta')),
            'contabilizados' => Pedido::where('estado_pedido', true)->whereNotNull('fecha_entrega')->distinct('numero_pedido')->count('numero_pedido'),
        ];
    }
}
