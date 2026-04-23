<?php

namespace App\Http\Controllers\Mayorista;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\FormaVenta;
use App\Models\Producto;
use App\Models\VentaMayorista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PedidoMayoristaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:mayoristas.panel');
    }

    public function index(Request $request)
    {
        return view('mayorista.pedidos.index', [
            'ventaInicial' => $request->query('venta'),
            'modoAdministrador' => auth()->user()->can('administrador.permisos'),
        ]);
    }

    public function buscarClientes(Request $request)
    {
        $termino = trim((string) $request->query('q', ''));

        $clientes = Cliente::query()
            ->select('id', 'codigo_cliente', 'nombres', 'apellidos', 'celular', 'calle_avenida', 'zona_barrio', 'ruta_id')
            ->with('ruta:id,nombre_ruta')
            ->when($termino !== '', function ($query) use ($termino) {
                $query->where(function ($cliente) use ($termino) {
                    $cliente->where('codigo_cliente', 'ilike', "%{$termino}%")
                        ->orWhere('nombres', 'ilike', "%{$termino}%")
                        ->orWhere('apellidos', 'ilike', "%{$termino}%")
                        ->orWhere('celular', 'ilike', "%{$termino}%");
                });
            })
            ->orderBy('nombres')
            ->limit(12)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'codigo_cliente' => $cliente->codigo_cliente,
                    'nombre' => trim($cliente->nombres . ' ' . $cliente->apellidos),
                    'celular' => $cliente->celular ?: 'N/A',
                    'direccion' => trim(($cliente->calle_avenida ?: '') . ' ' . ($cliente->zona_barrio ?: '')),
                    'ruta' => $cliente->ruta?->nombre_ruta ?: 'Sin ruta',
                ];
            });

        return response()->json(['clientes' => $clientes], 200);
    }

    public function buscarProductos(Request $request)
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

    public function obtenerProducto(string $idProducto)
    {
        $producto = Producto::select(
                'id',
                'codigo',
                'nombre_producto',
                'foto_producto',
                'descripcion_producto',
                'cantidad',
                'detalle_cantidad'
            )
            ->where('id', $idProducto)
            ->where('estado_de_baja', false)
            ->firstOrFail();

        $formasVenta = FormaVenta::query()
            ->where('id_producto', $idProducto)
            ->where('activo', true)
            ->orderBy('equivalencia_cantidad')
            ->get(['id', 'tipo_venta', 'precio_venta', 'equivalencia_cantidad']);

        return response()->json([
            'producto' => $producto,
            'formasVenta' => $formasVenta,
        ], 200);
    }

    public function obtenerFormaVenta(string $idFormaVenta)
    {
        $formaVenta = FormaVenta::query()
            ->where('id', $idFormaVenta)
            ->where('activo', true)
            ->firstOrFail();

        return response()->json($formaVenta, 200);
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

    public function listadoPedidos(Request $request, DataTables $dataTables)
    {
        $query = VentaMayorista::query()
            ->join('clientes', 'ventas_mayoristas.id_cliente', '=', 'clientes.id')
            ->join('forma_ventas', 'ventas_mayoristas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('users', 'ventas_mayoristas.id_usuario', '=', 'users.id')
            ->when(! $this->puedeAdministrar(), function ($query) {
                $query->where('ventas_mayoristas.id_usuario', auth()->id());
            })
            ->selectRaw('ventas_mayoristas.numero_venta AS numero_pedido')
            ->select('ventas_mayoristas.id_cliente', 'ventas_mayoristas.id_usuario')
            ->selectRaw('DATE(MIN(ventas_mayoristas.fecha_venta)) AS fecha_pedido')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(clientes.celular, 'N/A') AS celular")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS mayorista")
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('SUM(ventas_mayoristas.cantidad * forma_ventas.equivalencia_cantidad) AS unidades')
            ->selectRaw('SUM(ventas_mayoristas.cantidad * ventas_mayoristas.precio_unitario) AS total')
            ->groupBy('ventas_mayoristas.numero_venta', 'ventas_mayoristas.id_cliente', 'ventas_mayoristas.id_usuario', 'clientes.nombres', 'clientes.apellidos', 'clientes.celular', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('ventas_mayoristas.numero_venta');

        return $dataTables->eloquent($query)
            ->editColumn('fecha_pedido', fn ($row) => date('d/m/Y', strtotime($row->fecha_pedido)))
            ->editColumn('items', fn ($row) => (int) $row->items)
            ->editColumn('unidades', fn ($row) => (float) $row->unidades)
            ->editColumn('total', fn ($row) => round((float) $row->total, 2))
            ->addColumn('acciones', function ($row) {
                return '<button type="button" class="btn btn-info btn-sm wholesale-action-btn btn-editar-mayorista" data-pedido="' . $row->numero_pedido . '">
                            <i class="fas fa-edit"></i> Editar
                        </button>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function detallePedido(string $numeroPedido)
    {
        $ventaBase = $this->ventasVisibles()
            ->with(['cliente.ruta'])
            ->where('numero_venta', $numeroPedido)
            ->firstOrFail();

        $cliente = $ventaBase->cliente;

        $items = $this->ventasVisibles()
            ->join('productos', 'ventas_mayoristas.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'ventas_mayoristas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas_mayoristas.numero_venta', $numeroPedido)
            ->select(
                'ventas_mayoristas.id_producto',
                'ventas_mayoristas.id_forma_venta',
                'ventas_mayoristas.cantidad',
                'ventas_mayoristas.precio_unitario',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.equivalencia_cantidad'
            )
            ->orderBy('productos.nombre_producto')
            ->get()
            ->map(function ($item) {
                $precio = (float) $item->precio_unitario;
                return [
                    'id_producto' => $item->id_producto,
                    'id_forma_venta' => $item->id_forma_venta,
                    'codigo_producto' => $item->codigo,
                    'texto_producto' => $item->nombre_producto,
                    'tipo_venta' => $item->tipo_venta,
                    'precio_venta' => round($precio, 2),
                    'cantidad' => (int) $item->cantidad,
                    'equivalencia_cantidad' => (int) $item->equivalencia_cantidad,
                    'sub_total' => round($precio * (int) $item->cantidad, 2),
                ];
            });

        return response()->json([
            'numero_pedido' => $numeroPedido,
            'cliente' => [
                'id' => $cliente?->id,
                'codigo_cliente' => $cliente?->codigo_cliente,
                'nombre' => trim(($cliente?->nombres ?? '') . ' ' . ($cliente?->apellidos ?? '')),
                'celular' => $cliente?->celular ?: 'N/A',
                'direccion' => trim(($cliente?->calle_avenida ?? '') . ' ' . ($cliente?->zona_barrio ?? '')),
                'ruta' => $cliente?->ruta?->nombre_ruta ?: 'Sin ruta',
            ],
            'items' => $items,
        ], 200);
    }

    public function guardarPedido(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required',
        ]);

        $productos = is_string($request->productos)
            ? json_decode($request->productos, true)
            : $request->productos;

        if (! is_array($productos) || empty($productos)) {
            return response()->json(['message' => 'Debes agregar al menos un producto valido.'], 422);
        }

        $resultado = DB::transaction(function () use ($request, $productos) {
            $numeroVentaEditar = $request->input('numero_pedido');
            $cliente = Cliente::findOrFail($request->cliente_id);
            $usuarioVenta = auth()->id();
            $fechaVenta = now();

            if ($numeroVentaEditar) {
                $anteriores = $this->ventasVisibles()
                    ->where('numero_venta', $numeroVentaEditar)
                    ->lockForUpdate()
                    ->get();

                if ($anteriores->isEmpty()) {
                    abort(response()->json(['message' => 'La venta mayorista ya no esta disponible para edicion.'], 404));
                }

                $usuarioVenta = (int) $anteriores->first()->id_usuario;
                $fechaVenta = $anteriores->first()->fecha_venta ?? now();

                foreach ($anteriores as $anterior) {
                    $formaAnterior = FormaVenta::findOrFail($anterior->id_forma_venta);
                    $productoAnterior = Producto::query()->lockForUpdate()->findOrFail($anterior->id_producto);
                    $productoAnterior->cantidad += ($anterior->cantidad * $formaAnterior->equivalencia_cantidad);
                    $productoAnterior->save();
                }

                $this->ventasVisibles()
                    ->where('numero_venta', $numeroVentaEditar)
                    ->delete();

                $numeroVenta = $numeroVentaEditar;
            } else {
                DB::select('SELECT pg_advisory_xact_lock(?)', [23042026]);
                $numeroVenta = ((int) VentaMayorista::query()->max('numero_venta')) + 1;
            }

            $productosAgrupados = collect($productos)
                ->map(function ($producto) {
                    return [
                        'id_producto' => (int) ($producto['id_producto'] ?? 0),
                        'id_forma_venta' => (int) ($producto['id_forma_venta'] ?? 0),
                        'cantidad' => (int) ($producto['cantidad'] ?? 0),
                        'precio_venta' => round((float) ($producto['precio_venta'] ?? 0), 2),
                    ];
                })
                ->groupBy(fn ($producto) => $producto['id_producto'] . '-' . $producto['id_forma_venta'] . '-' . $producto['precio_venta'])
                ->map(function ($items) {
                    $base = $items->first();
                    $base['cantidad'] = $items->sum('cantidad');
                    return $base;
                })
                ->values();

            foreach ($productosAgrupados as $productoPedido) {
                if ($productoPedido['cantidad'] <= 0) {
                    abort(response()->json(['message' => 'La cantidad debe ser mayor a cero.'], 422));
                }

                if ($productoPedido['precio_venta'] <= 0) {
                    abort(response()->json(['message' => 'El precio de venta debe ser mayor a cero.'], 422));
                }

                $formaVenta = FormaVenta::query()
                    ->where('id', $productoPedido['id_forma_venta'])
                    ->where('id_producto', $productoPedido['id_producto'])
                    ->where('activo', true)
                    ->firstOrFail();

                $productoModel = Producto::query()
                    ->where('id', $productoPedido['id_producto'])
                    ->where('estado_de_baja', false)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidadInventario = $productoPedido['cantidad'] * $formaVenta->equivalencia_cantidad;

                if ($productoModel->cantidad < $cantidadInventario) {
                    abort(response()->json([
                        'message' => 'Stock insuficiente para ' . $productoModel->nombre_producto . '. Disponible: ' . $productoModel->cantidad . ' ' . $productoModel->detalle_cantidad . '.',
                        'producto_id' => $productoModel->id,
                        'stock_disponible' => $productoModel->cantidad,
                    ], 409));
                }
            }

            foreach ($productosAgrupados as $productoPedido) {
                $formaVenta = FormaVenta::query()
                    ->where('id', $productoPedido['id_forma_venta'])
                    ->where('id_producto', $productoPedido['id_producto'])
                    ->where('activo', true)
                    ->firstOrFail();

                $productoModel = Producto::query()->lockForUpdate()->findOrFail($productoPedido['id_producto']);

                VentaMayorista::create([
                    'id_usuario' => $usuarioVenta,
                    'id_cliente' => $cliente->id,
                    'id_producto' => $productoModel->id,
                    'id_forma_venta' => $formaVenta->id,
                    'precio_unitario' => $productoPedido['precio_venta'],
                    'numero_venta' => $numeroVenta,
                    'fecha_venta' => $fechaVenta,
                    'cantidad' => $productoPedido['cantidad'],
                    'observaciones' => null,
                ]);

                $productoModel->cantidad -= ($productoPedido['cantidad'] * $formaVenta->equivalencia_cantidad);
                $productoModel->save();
            }

            return $numeroVenta;
        }, 3);

        return response()->json([
            'message' => 'Venta mayorista guardada correctamente.',
            'numero_pedido' => $resultado,
        ], 201);
    }

    private function puedeAdministrar(): bool
    {
        return auth()->user()->can('administrador.permisos');
    }

    private function ventasVisibles()
    {
        return VentaMayorista::query()->when(! $this->puedeAdministrar(), function ($query) {
            $query->where('id_usuario', auth()->id());
        });
    }
}
