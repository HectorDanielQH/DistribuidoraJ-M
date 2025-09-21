<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
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
            $query=Pedido::query()->select('id_cliente','numero_pedido','id_usuario',DB::raw('DATE(fecha_pedido) AS fecha_pedido'))
                ->whereNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->groupBy('id_cliente', 'numero_pedido','id_usuario','fecha_pedido')
                ->orderBy('numero_pedido', 'asc');  
            
            return $dataTables->eloquent($query)
                ->addColumn('cliente', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos : 'N/A';
                })
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cliente', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(nombres, ' ', apellidos)"), 'ilike', "%{$keyword}%");
                    });
                })
                ->addColumn('celular', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->celular : 'N/A';
                })
                ->addColumn('direccion', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->calle_avenida . ' - ' . $pedido->cliente->zona_barrio : 'N/A';
                })
                ->addColumn('ruta', function($pedido){
                    return $pedido->cliente->ruta_id ? $pedido->cliente->ruta->nombre_ruta : 'N/A';
                })
                ->addColumn('preventista', function($pedido){
                    return $pedido->usuario ? $pedido->usuario->nombres.' '.$pedido->usuario->apellido_paterno.' '.$pedido->usuario->apellido_materno : 'N/A';
                })
                ->addColumn('estado', function($pedido){
                    return $pedido->estado_pedido ? '<span class="badge badge-success">Contabilizado</span>' : '<span class="badge badge-warning">Pendiente</span>';
                })
                ->addColumn('acciones', function($pedido){
                    $ruta=route('administrador.pedidos.administrador.editar', $pedido->numero_pedido);
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                    <button type="button" class="btn btn-primary btn-sm" onclick="verPedidoCliente(this)"
                        id-numero-pedido="' . $pedido->numero_pedido . '"
                        data-toggle="modal" data-target="#modalVerPedido"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                    <a
                        href="' . $ruta . '"
                        class="btn btn-warning btn-sm"
                    >
                        <i class="fas fa-edit"></i>
                    </a>
                    ';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['estado','acciones'])
                ->make(true);
        }
        
        return view('administrador.pedidos.index');
    }

    public function visualizacionDespachados(Request $request, DataTables $dataTables){
        if($request->ajax()){
            $query=Pedido::query();
            $query->join('productos', 'pedidos.id_producto', '=', 'productos.id');
            $query->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id');
            $query->select('pedidos.id_producto', DB::raw('SUM(pedidos.cantidad*forma_ventas.equivalencia_cantidad) AS cantidad_despacho'), DB::raw('SUM(pedidos.cantidad*forma_ventas.precio_venta) AS ingreso_estimado'));
            $query->groupBy('pedidos.id_producto');
            $query->whereNotNull('fecha_entrega');
            $query->where('estado_pedido', false);
            return $dataTables->eloquent($query)
                ->addColumn('imagen', function ($p){
                    if ($p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
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
                    return $p->cantidad_despacho.' '.$p->producto->detalle_cantidad;
                })
                ->addColumn('ingreso_estimado', function ($p) {
                    return $p->ingreso_estimado.' Bs.-';
                })
                ->rawColumns(['imagen'])
                ->make(true);
        }

        $suma_total_estimada=Pedido::whereNotNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        return view('administrador.pedidos.despachados', compact('suma_total_estimada'));
    }


    public function visualizacionParaDespachado(Request $request, DataTables $dataTables){
        if($request->ajax()){
            $query=Pedido::query();
            $query->join('productos', 'pedidos.id_producto', '=', 'productos.id');
            $query->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id');
            $query->select('pedidos.id_producto', DB::raw('SUM(pedidos.cantidad*forma_ventas.equivalencia_cantidad) AS cantidad_despacho'), DB::raw('SUM(pedidos.cantidad*forma_ventas.precio_venta) AS ingreso_estimado'));
            $query->groupBy('pedidos.id_producto');
            $query->whereNull('fecha_entrega');
            $query->where('estado_pedido', false);
            return $dataTables->eloquent($query)
                ->addColumn('imagen', function ($p){
                    if ($p->producto->foto_producto && Storage::disk('local')->exists($p->producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $p->producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
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
                    return $p->cantidad_despacho.' '.$p->producto->detalle_cantidad;
                })
                ->addColumn('ingreso_estimado', function ($p) {
                    return $p->ingreso_estimado.' Bs.-';
                })
                ->rawColumns(['imagen'])
                ->make(true);
        }

        $suma_total_estimada=Pedido::whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
            ->value('total');
        return view('administrador.pedidos.paradespachar', compact('suma_total_estimada'));
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

    public function visualizacionPdfDespachar(){
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
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        $pdf = Pdf::loadView('administrador.pdf.pdf_despachar', compact('pedidos', 'lista_de_pedidos'));
        $pdf->setPaper('letter', 'horizontal');
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
        $pdf->setPaper('letter', 'horizontal');
        return $pdf->stream('productosDespachados.pdf');   
    }


    public function despacharPedido(){
        $pedidos = Pedido::whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->whereNull('fecha_entrega')
            ->get();
        foreach ($pedidos as $pedido) {
            $pedido->fecha_entrega = now();
            $pedido->save();
        }
        return response()->json([
            'message' => 'Pedidos despachados correctamente.'
        ], 200);
    }

    public function devolucionPedido(Request $request, DataTables $dataTables){

        if($request->ajax()){
            $query=Pedido::query()->select('numero_pedido')
                ->whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->groupBy('numero_pedido')
                ->orderBy('numero_pedido', 'asc');  
            
            return $dataTables->eloquent($query)
                ->addColumn('numero_pedido', function($pedido){
                    return $pedido->numero_pedido;
                })
                ->addColumn('cliente', function($pedido){
                    $cliente = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
                        ->select('clientes.nombres', 'clientes.apellidos')
                        ->first();
                    return $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : 'N/A';
                })
                ->addColumn('fecha_pedido', function($pedido){
                    $fecha_pedido = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->select(DB::raw('DATE(fecha_pedido) AS fecha_pedido'))
                        ->first();
                    return $fecha_pedido ? $fecha_pedido->fecha_pedido : 'N/A';
                })
                ->addColumn('fecha_entrega', function($pedido){
                    $fecha_entrega = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->select(DB::raw('DATE(fecha_entrega) AS fecha_entrega'))
                        ->first();
                    return $fecha_entrega ? $fecha_entrega->fecha_entrega : 'N/A';
                })
                ->addColumn('monto_estimado', function($pedido){
                    $monto_estimado = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
                        ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as monto_estimado'))
                        ->value('monto_estimado');
                    return $monto_estimado ? $monto_estimado.' Bs.-' : '0 Bs.-';
                })
                ->addColumn('preventista', function($pedido){
                    $usuario = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->join('users', 'pedidos.id_usuario', '=', 'users.id')
                        ->select('users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
                        ->first();
                    return $usuario ? $usuario->nombres . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno : 'N/A';
                })
                ->addColumn('ruta', function($pedido){
                    $ruta = Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
                        ->join('rutas', 'clientes.ruta_id', '=', 'rutas.id')
                        ->select('rutas.nombre_ruta')
                        ->first();
                    return $ruta ? $ruta->nombre_ruta : 'N/A';
                })
                ->addColumn('acciones', function($pedido){
                    $ruta=route('administrador.pedidos.administrador.editar.despachados', $pedido->numero_pedido);
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                        <a
                            href="' . $ruta . '"
                            class="btn btn-warning btn-sm"
                        >
                            <i class="fas fa-edit"></i>
                        </a>
                    ';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        return view('administrador.pedidos.devoluciones');
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
                'forma_ventas.precio_venta',
                'pedidos.id as id_pedido',
                'pedidos.numero_pedido',
                'pedidos.cantidad as cantidad_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
            )
            ->where('pedidos.numero_pedido', $numero_pedido)
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();
        return response()->json([
            'pedidos' => $lista_de_pedidos
        ], 200);
    }
    public function devolucionPedidoDevolucionCantidad(Request $request, int $id){
        $pedido = Pedido::findOrFail($id);
        $cantidad_actualizada = $request->input('cantidad');
        $forma_venta = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidad_anterior = $pedido->cantidad * $forma_venta->equivalencia_cantidad;
        $cantidad_nueva    = $cantidad_actualizada * $forma_venta->equivalencia_cantidad;

        $producto = Producto::findOrFail($pedido->id_producto);

        // Ajustar stock del producto según la diferencia de equivalencias
        $diferencia = $cantidad_anterior - $cantidad_nueva;
        $producto->cantidad += $diferencia; // puede ser positivo o negativo
        $producto->save();

        $pedido->cantidad = $cantidad_actualizada;
        $pedido->save();
        
        return response()->json([
            'message' => 'Cantidad devuelta correctamente.'
        ], 200);
    }

    public function productoSelectFormasVentas(string $id_producto){
        $formas_venta=FormaVenta::where('id_producto', $id_producto)->get();
        return response()->json([
            'formas_venta' => $formas_venta
        ], 200);
    }

    public function productoSelectActualizar(Request $request, int $id_pedido)
    {
        $request->validate([
            'tipo_venta_id' => 'required|integer|exists:forma_ventas,id'
        ]);

        $pedido = Pedido::findOrFail($id_pedido);

        $id_forma_venta = $request->input('tipo_venta_id');

        $forma_venta_nueva = FormaVenta::findOrFail($id_forma_venta);
        $forma_venta_anterior = FormaVenta::findOrFail($pedido->id_forma_venta);

        // Calcular la cantidad en unidades de inventario según la forma de venta
        $cantidad_anterior = $pedido->cantidad * $forma_venta_anterior->equivalencia_cantidad;
        $cantidad_nueva    = $pedido->cantidad * $forma_venta_nueva->equivalencia_cantidad;

        $producto = Producto::findOrFail($pedido->id_producto);

        // Ajustar stock del producto según la diferencia de equivalencias
        $diferencia = $cantidad_anterior - $cantidad_nueva;
        $producto->cantidad += $diferencia; // puede ser positivo o negativo
        $producto->save();

        // Actualizar la forma de venta del pedido
        $pedido->id_forma_venta = $id_forma_venta;
        $pedido->save();

        return response()->json([
            'message' => 'Forma de venta actualizada correctamente.'
        ], 200);
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
        $pedido = Pedido::findOrFail($id_pedido);
        $pedido->delete();
        $producto = Producto::findOrFail($pedido->id_producto);
        $formas_venta = FormaVenta::findOrFail($pedido->id_forma_venta);
        $producto->cantidad += ($pedido->cantidad * $formas_venta->equivalencia_cantidad);
        $producto->save();
        return response()->json([
            'message' => 'Promociones eliminadas correctamente.'
        ], 200);
    }

    /**
     * 
     * 
     */

    public function contabilizarPedidosPendientes(){
        
        $pedidosPendientes = Pedido::whereNotNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->get();
        foreach ($pedidosPendientes as $pedido) {
            $pedido->estado_pedido = true;
            $pedido->save();
            
            Venta::create([
                'id_usuario' => $pedido->id_usuario,
                'id_cliente' => $pedido->id_cliente,
                'id_producto' => $pedido->id_producto,
                'id_forma_venta' => $pedido->id_forma_venta,
                'numero_pedido' => $pedido->numero_pedido,
                'fecha_contabilizacion' => now(),
                'cantidad' => $pedido->cantidad,
                'promocion' => $pedido->promocion,
                'descripcion_descuento_porcentaje' => $pedido->descripcion_descuento_porcentaje,
                'descripcion_regalo' => $pedido->descripcion_regalo
            ]);
        }
        return response()->json([
            'pedidosPendientes' => $pedidosPendientes
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
            $query=Pedido::query()->select('id_cliente','numero_pedido','id_usuario',DB::raw('DATE(fecha_pedido) AS fecha_pedido'), DB::raw('DATE(fecha_entrega) AS fecha_entrega'))
                ->where('estado_pedido', true)
                ->whereNotNull('fecha_entrega')
                ->groupBy('id_cliente', 'numero_pedido','id_usuario','fecha_pedido','fecha_entrega')
                ->orderBy('numero_pedido', 'asc');
            return $dataTables->eloquent($query)
                ->addColumn('numero_pedido', function($pedido){
                    return str_pad($pedido->numero_pedido, 6, '0', STR_PAD_LEFT);
                })
                ->addColumn('cliente', function($pedido){
                    return $pedido->cliente ? $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos : 'N/A';
                })
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cliente', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(nombres, ' ', apellidos)"), 'ilike', "%{$keyword}%");
                    });
                })
                ->addColumn('fecha_pedido', function($pedido){
                    return $pedido->fecha_pedido ? date('d/m/Y', strtotime($pedido->fecha_pedido)) : 'N/A';
                })
                ->addColumn('fecha_entrega', function($pedido){
                    return $pedido->fecha_entrega ? date('d/m/Y', strtotime($pedido->fecha_entrega)) : 'N/A';
                })
                ->addColumn('monto_contabilizado', function($pedido){
                    $suma_pedido= Pedido::where('numero_pedido', $pedido->numero_pedido)
                        ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
                        ->select(DB::raw('SUM(pedidos.cantidad * forma_ventas.precio_venta) as total'))
                        ->value('total');
                    return $suma_pedido ? $suma_pedido.' Bs.-' : '0 Bs.-';
                })
                ->addColumn('preventista', function($pedido){
                    return $pedido->usuario ? $pedido->usuario->nombres.' '.$pedido->usuario->apellido_paterno.' '.$pedido->usuario->apellido_materno : 'N/A';
                })
                ->addColumn('ruta', function($pedido){
                    return $pedido->cliente->ruta_id ? $pedido->cliente->ruta->nombre_ruta : 'N/A';
                })
                ->addColumn('acciones', function($pedido){
                    $ruta=route('administrador.pedidos.administrador.editar.contabilizados', $pedido->numero_pedido);
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                        <a
                            href="' . $ruta . '"
                            class="btn btn-warning btn-sm"
                        >
                            <i class="fas fa-edit"></i>
                        </a>
                    ';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        return view('administrador.pedidos.contabilizados');
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
            Venta::where('numero_pedido', $pedido_actual->numero_pedido)->delete();
            $pedidos_restantes=Pedido::where('numero_pedido', $pedido_actual->numero_pedido)->get();
            foreach($pedidos_restantes as $p){
                Venta::create([
                    'id_usuario' => $p->id_usuario,
                    'id_cliente' => $p->id_cliente,
                    'id_producto' => $p->id_producto,
                    'id_forma_venta' => $p->id_forma_venta,
                    'numero_pedido' => $p->numero_pedido,
                    'fecha_contabilizacion' => now(),
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
            Venta::where('numero_pedido', $pedido->numero_pedido)->delete();
            $pedidos_restantes=Pedido::where('numero_pedido', $pedido->numero_pedido)->get();
            foreach($pedidos_restantes as $p){
                Venta::create([
                    'id_usuario' => $p->id_usuario,
                    'id_cliente' => $p->id_cliente,
                    'id_producto' => $p->id_producto,
                    'id_forma_venta' => $p->id_forma_venta,
                    'numero_pedido' => $p->numero_pedido,
                    'fecha_contabilizacion' => now(),
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
}
