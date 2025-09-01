<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoAdministradorController extends Controller
{
    public function index(){
        $pedidos = Pedido::select(
            'id_cliente',
            'numero_pedido',
            DB::raw('DATE(fecha_pedido) AS fecha_pedido')
        )
            ->whereNull('fecha_entrega')
            ->where('estado_pedido', false)
            ->groupBy('id_cliente', 'numero_pedido','fecha_pedido')
            ->orderBy('numero_pedido', 'asc')
            ->paginate(10);
        return view('administrador.pedidos.index',compact('pedidos'));
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
                'fecha_pedido',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'ruta_id',
            )
            ->orderBy('numero_pedido', 'asc')
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
                'id_usuario' => auth()->id(),
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
}
