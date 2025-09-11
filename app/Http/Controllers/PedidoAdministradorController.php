<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPedidoCliente(this)" id-numero-pedido="' . $pedido->numero_pedido . '"><i class="fas fa-trash"></i></button>
                    ';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['estado','acciones'])
                ->make(true);
        }
        
        return view('administrador.pedidos.index');
    }

    public function visualizacionDespachados(){

        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.codigo',
                'productos.foto_producto',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                DB::raw('SUM(pedidos.cantidad*forma_ventas.equivalencia_cantidad) as cantidad_pedido'),
                DB::raw('SUM(pedidos.cantidad*forma_ventas.precio_venta) as subtotal'),
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo'
            )
            ->whereNotNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->groupBy(
                'productos.codigo',
                'productos.foto_producto',
                'productos.nombre_producto',
                'productos.cantidad',
                'productos.detalle_cantidad',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo'
            )
            ->get();


        return view('administrador.pedidos.despachados',compact('pedidos'));
    }


    public function visualizacionParaDespachado(){
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->select(
                'productos.codigo',
                'productos.foto_producto',
                'productos.nombre_producto',
                'productos.cantidad as cantidad_stock',
                'productos.detalle_cantidad',
                DB::raw('SUM(pedidos.cantidad*forma_ventas.equivalencia_cantidad) as cantidad_pedido'),
                DB::raw('SUM(pedidos.cantidad*forma_ventas.precio_venta) as subtotal'),
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo'
            )
            ->whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->groupBy(
                'productos.codigo',
                'productos.foto_producto',
                'productos.nombre_producto',
                'productos.cantidad',
                'productos.detalle_cantidad',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo'
            )
            ->get();


        return view('administrador.pedidos.paradespachar',compact('pedidos'));
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


    public function despacharPedido(){
        $pedidos = Pedido::whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->get();
        foreach ($pedidos as $pedido) {
            $pedido->fecha_entrega = now();
            $pedido->save();
        }
        return response()->json([
            'message' => 'Pedidos despachados correctamente.'
        ], 200);
    }

    public function devolucionPedido(){
        $lista_de_pedidos = Pedido::select('numero_pedido')
            ->whereNotNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->groupBy(
                'numero_pedido',
            )
            ->orderBy('numero_pedido', 'asc')
            ->get();
        return view('administrador.pedidos.devoluciones', compact('lista_de_pedidos'));
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
                ->rawColumns(['acciones'])
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
}
