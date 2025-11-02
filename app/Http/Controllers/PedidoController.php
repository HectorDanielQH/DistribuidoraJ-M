<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
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
    public function show(Pedido $pedido)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedido $pedido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedido $pedido)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedido $pedido)
    {
        //
    }

    public function crearPedido(string $id_asignacion){
        $productos = Producto::select('id','codigo','nombre_producto' )
                    ->where('cantidad', '>', 0)
                    ->where('estado_de_baja', false)->get();
        $asignacion = Asignacion::where('id_cliente',$id_asignacion)->first();
        return view('vendedor.pedidos.index_pedidos', compact('asignacion','productos'));
    }

    public function ObtenerProductoParaPedido(string $id_producto){
        $producto = Producto::select('id','codigo','nombre_producto','foto_producto','descripcion_producto','cantidad','detalle_cantidad','promocion','descripcion_descuento_porcentaje','descripcion_regalo')->where('id', $id_producto)->where('estado_de_baja', false)->first();
        $formasVenta = FormaVenta::where('id_producto',$id_producto)
                    ->where('activo', true)
                    ->get();
        return response()->json([
            'producto' => $producto,
            'formasVenta' => $formasVenta
        ], 200);
    }

    public function obtenerFormaVenta(string $id_forma_venta)
    {
        $formaVenta = FormaVenta::where('id', $id_forma_venta)
                                ->where('activo', true)
                                ->first();

        if (!$formaVenta) {
            return response()->json(['mensaje' => 'Forma de venta no encontrada o inactiva'], 404);
        }

        return response()->json($formaVenta, 200);
    }


    public function registrarPedido(Request $request)
    {
        $request->validate([
            'asignacion_id' => 'required|exists:asignacions,id',
            'productos' => 'required',
        ], [
            'asignacion_id.required' => 'El campo id_asignacion es obligatorio.',
            'asignacion_id.exists' => 'La asignación especificada no existe.',
            'productos.required' => 'El campo productos es obligatorio.',
        ]);

        // Si 'productos' llega como string JSON, decodifícalo
        $productos = is_string($request->productos)
            ? json_decode($request->productos, true)
            : $request->productos;

        if (!is_array($productos)) {
            return response()->json(['message' => 'El campo productos debe ser un arreglo válido.'], 422);
        }

        $asignacion = Asignacion::find($request->asignacion_id);
        $numero_pedido = null;

        if($asignacion->numero_pedido){
            $numero_pedido = $asignacion->numero_pedido;
            //devolver stock de productos del pedido anterior
            $pedidos_anteriores = Pedido::where('numero_pedido', $numero_pedido)->get();
            foreach ($pedidos_anteriores as $pedido_anterior) {
                $productoModel = Producto::find($pedido_anterior->id_producto);
                $formasVenta = FormaVenta::find($pedido_anterior->id_forma_venta);
                if ($productoModel) {
                    $productoModel->cantidad += ($pedido_anterior->cantidad*$formasVenta->equivalencia_cantidad);
                    $productoModel->save();
                }
            }
            //eliminar pedidos anteriores
            Pedido::where('numero_pedido', $asignacion->numero_pedido)->delete();
        }
        else{
            $max_pedido = Pedido::max('numero_pedido') ?? 0;
            $numero_pedido = $max_pedido + 1;
        }

        //validacion de productos con pedido

        foreach ($productos as $producto) {
            $obtenerProducto = Producto::find($producto['id_producto']);
            if($obtenerProducto->cantidad < $producto['cantidad'])
            {
                return response()->json([
                    'message' => 'No hay suficiente cantidad del producto: '.$obtenerProducto->descripcion_producto.'. Stock disponible: '.$obtenerProducto->cantidad." ".$obtenerProducto->detalle_cantidad
                ], 400);
            }
        }

        //registro de pedidos

        foreach ($productos as $producto) {
            $pedido = new Pedido();
            $pedido->id_usuario = auth()->id();
            $pedido->id_cliente = $asignacion->id_cliente;
            $pedido->id_producto = $producto['id_producto'];
            $pedido->id_forma_venta = $producto['id_forma_venta'];
            $pedido->numero_pedido = $numero_pedido;
            $pedido->fecha_pedido = now();
            $pedido->fecha_entrega = null;
            $pedido->cantidad = $producto['cantidad'];
            $pedido->estado_pedido = false;
            $pedido->promocion = $producto['promocion'] ?? false;
            $pedido->descripcion_descuento_porcentaje = $producto['descripcion_descuento_porcentaje'] ?? null;
            $pedido->descripcion_regalo = $producto['descripcion_regalo'] ?? null;
            $pedido->save();

            // Actualizar la cantidad del producto
            $productoModel = Producto::find($producto['id_producto']);
            $formasVenta = FormaVenta::find($producto['id_forma_venta']);
            if ($productoModel) {
                $productoModel->cantidad -= ($producto['cantidad']*$formasVenta->equivalencia_cantidad);
                $productoModel->save();
            } else {
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }
        }
        $asignacion->numero_pedido = $numero_pedido;
        $asignacion->atencion_fecha_hora = now();
        $asignacion->estado_pedido = true;
        $asignacion->save();

        return response()->json(['message' => 'Pedido registrado exitosamente.'], 201);
    }

    public function obtenerPdfRutas(){
        $usuario= auth()->user();
        $asignaciones = Asignacion::join('clientes', 'asignacions.id_cliente', '=', 'clientes.id')
            ->join('users', 'asignacions.id_usuario', '=', 'users.id')
            ->where('asignacions.id_usuario', $usuario->id)
            ->select(
                'asignacions.*',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'clientes.celular',
            )
            ->orderBy('clientes.ruta_id', 'asc')
            ->get();
        $pdf = Pdf::loadView('vendedor.pdf.pedidos_pdf_rutas', compact('asignaciones', 'usuario'));
        $pdf->setPaper('letter', 'horizontal');
        return $pdf->stream('rutas_pedidos.pdf');
    }

    public function obtenerPedidosPorNumero(string $numero_pedido){
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
                    ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
                    ->where('pedidos.numero_pedido', $numero_pedido)
                    ->where('pedidos.id_usuario', auth()->id())
                    ->select(
                        'pedidos.*',
                        'productos.codigo',
                        'productos.nombre_producto',
                        'productos.descripcion_producto',
                        'productos.detalle_cantidad',
                        'forma_ventas.tipo_venta',
                        'forma_ventas.precio_venta'
                    )
                    ->get();
        return response()->json($pedidos, 200);
    }
}
