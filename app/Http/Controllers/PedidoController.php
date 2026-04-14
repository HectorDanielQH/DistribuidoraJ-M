<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $asignacion = Asignacion::where('id_cliente',$id_asignacion)
            ->where('id_usuario', auth()->id())
            ->firstOrFail();
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

    public function buscarProductosPedido(Request $request)
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
            ->orderBy('nombre_producto')
            ->limit(20)
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
            ]);

        return response()->json(['productos' => $productos], 200);
    }

    public function obtenerStockProductos(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['productos' => []], 200);
        }

        $productos = Producto::query()
            ->whereIn('id', $ids)
            ->where('estado_de_baja', false)
            ->get(['id', 'codigo', 'nombre_producto', 'cantidad', 'detalle_cantidad', 'updated_at'])
            ->map(fn ($producto) => [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre_producto' => $producto->nombre_producto,
                'cantidad' => (float) $producto->cantidad,
                'detalle_cantidad' => $producto->detalle_cantidad,
                'updated_at' => optional($producto->updated_at)->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['productos' => $productos], 200);
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

        try {
            DB::transaction(function () use ($productos, $request) {
                $asignacion = Asignacion::where('id_usuario', auth()->id())
                    ->where('id', $request->asignacion_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $numero_pedido = $asignacion->numero_pedido;

                if ($numero_pedido) {
                    $pedidos_anteriores = Pedido::where('numero_pedido', $numero_pedido)
                        ->where('id_usuario', auth()->id())
                        ->where('id_cliente', $asignacion->id_cliente)
                        ->lockForUpdate()
                        ->get();

                    foreach ($pedidos_anteriores as $pedido_anterior) {
                        $formaAnterior = FormaVenta::findOrFail($pedido_anterior->id_forma_venta);
                        $productoAnterior = Producto::where('id', $pedido_anterior->id_producto)
                            ->lockForUpdate()
                            ->firstOrFail();
                        $productoAnterior->cantidad += ($pedido_anterior->cantidad * $formaAnterior->equivalencia_cantidad);
                        $productoAnterior->save();
                    }

                    Pedido::where('numero_pedido', $numero_pedido)
                        ->where('id_usuario', auth()->id())
                        ->where('id_cliente', $asignacion->id_cliente)
                        ->delete();
                } else {
                    $max_pedido = Pedido::orderByDesc('numero_pedido')
                        ->lockForUpdate()
                        ->value('numero_pedido') ?? 0;
                    $numero_pedido = $max_pedido + 1;
                }

                $productosAgrupados = collect($productos)
                    ->groupBy(fn ($producto) => $producto['id_producto'].'-'.$producto['id_forma_venta'])
                    ->map(function ($items) {
                        $primero = $items->first();
                        $primero['cantidad'] = $items->sum(fn ($item) => (int) $item['cantidad']);
                        return $primero;
                    })
                    ->values();

                foreach ($productosAgrupados as $productoPedido) {
                    $cantidadPedido = (int) ($productoPedido['cantidad'] ?? 0);
                    if ($cantidadPedido <= 0) {
                        abort(response()->json(['message' => 'La cantidad debe ser mayor a cero.'], 422));
                    }

                    $formaVenta = FormaVenta::where('id', $productoPedido['id_forma_venta'])
                        ->where('id_producto', $productoPedido['id_producto'])
                        ->where('activo', true)
                        ->firstOrFail();

                    $productoModel = Producto::where('id', $productoPedido['id_producto'])
                        ->where('estado_de_baja', false)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $cantidadInventario = $cantidadPedido * $formaVenta->equivalencia_cantidad;

                    if ($productoModel->cantidad < $cantidadInventario) {
                        abort(response()->json([
                            'message' => 'Stock actualizado: '.$productoModel->nombre_producto.' solo tiene '.$productoModel->cantidad.' '.$productoModel->detalle_cantidad.'. Actualiza la cantidad del pedido.',
                            'producto_id' => $productoModel->id,
                            'stock_disponible' => $productoModel->cantidad,
                        ], 409));
                    }
                }

                foreach ($productosAgrupados as $productoPedido) {
                    $formaVenta = FormaVenta::where('id', $productoPedido['id_forma_venta'])
                        ->where('id_producto', $productoPedido['id_producto'])
                        ->where('activo', true)
                        ->firstOrFail();

                    $productoModel = Producto::where('id', $productoPedido['id_producto'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $pedido = new Pedido();
                    $pedido->id_usuario = auth()->id();
                    $pedido->id_cliente = $asignacion->id_cliente;
                    $pedido->id_producto = $productoModel->id;
                    $pedido->id_forma_venta = $formaVenta->id;
                    $pedido->numero_pedido = $numero_pedido;
                    $pedido->fecha_pedido = now();
                    $pedido->fecha_entrega = null;
                    $pedido->cantidad = (int) $productoPedido['cantidad'];
                    $pedido->estado_pedido = false;
                    $pedido->promocion = (bool) $productoModel->promocion;
                    $pedido->descripcion_descuento_porcentaje = $productoModel->promocion ? $productoModel->descripcion_descuento_porcentaje : null;
                    $pedido->descripcion_regalo = $productoModel->promocion ? $productoModel->descripcion_regalo : null;
                    $pedido->save();

                    $productoModel->cantidad -= ((int) $productoPedido['cantidad'] * $formaVenta->equivalencia_cantidad);
                    $productoModel->save();
                }

                $asignacion->numero_pedido = $numero_pedido;
                $asignacion->atencion_fecha_hora = now();
                $asignacion->estado_pedido = true;
                $asignacion->save();
            }, 3);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        }

        return response()->json(['message' => 'Pedido registrado exitosamente. Inventario actualizado.'], 201);
    }

    public function obtenerPdfRutas(){
        $usuario= auth()->user();
        $asignaciones = Asignacion::join('clientes', 'asignacions.id_cliente', '=', 'clientes.id')
            ->join('rutas', 'asignacions.id_ruta', '=', 'rutas.id')
            ->join('users', 'asignacions.id_usuario', '=', 'users.id')
            ->where('asignacions.id_usuario', $usuario->id)
            ->select(
                'asignacions.*',
                'rutas.nombre_ruta',
                'clientes.codigo_cliente',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'clientes.referencia_direccion',
                'clientes.celular',
            )
            ->orderBy('rutas.nombre_ruta', 'asc')
            ->orderBy('clientes.zona_barrio', 'asc')
            ->orderBy('clientes.nombres', 'asc')
            ->get();
        $pdf = Pdf::loadView('vendedor.pdf.pedidos_pdf_rutas', compact('asignaciones', 'usuario'));
        $pdf->setPaper('letter', 'portrait');
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
