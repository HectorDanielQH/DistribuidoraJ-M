<?php

namespace App\Http\Controllers\Mayorista;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\FormaVenta;
use App\Models\Producto;
use App\Models\VentaMayorista;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Throwable;

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
            ->select('ventas_mayoristas.id_cliente', 'ventas_mayoristas.id_usuario')
            ->selectRaw('ventas_mayoristas.numero_venta AS numero_pedido')
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
                $numeroPedido = (int) $row->numero_pedido;
                $pdfUrl = route('mayoristas.pedidos.pdf', ['numero' => $numeroPedido]);

                return '<div class="wholesale-row-actions">
                            <a href="' . e($pdfUrl) . '" target="_blank" rel="noopener" class="btn btn-secondary btn-sm wholesale-action-btn btn-pdf-mayorista">
                                <i class="fas fa-file-pdf"></i> Hoja PDF
                            </a>
                            <button type="button" class="btn btn-info btn-sm wholesale-action-btn btn-editar-mayorista" data-pedido="' . $row->numero_pedido . '">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm wholesale-action-btn btn-eliminar-mayorista" data-pedido="' . $row->numero_pedido . '" onclick="eliminarPedidoMayorista(this.getAttribute(\'data-pedido\'))">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function pdfPedido(string $numeroPedido)
    {
        $ventaBase = $this->ventasVisibles()
            ->where('numero_venta', $numeroPedido)
            ->firstOrFail();

        $listaDePedidos = VentaMayorista::query()
            ->leftJoin('clientes', 'ventas_mayoristas.id_cliente', '=', 'clientes.id')
            ->where('ventas_mayoristas.numero_venta', $ventaBase->numero_venta)
            ->where('ventas_mayoristas.id_usuario', $ventaBase->id_usuario)
            ->select(
                'ventas_mayoristas.numero_venta AS numero_pedido',
                'ventas_mayoristas.id_usuario AS id_vendedor',
                DB::raw('DATE(MIN(ventas_mayoristas.fecha_venta)) AS fecha_pedido'),
                DB::raw("COALESCE(clientes.nombres, 'Cliente') AS nombres"),
                DB::raw("COALESCE(clientes.apellidos, '') AS apellidos"),
                DB::raw("COALESCE(clientes.celular, 'N/A') AS celular"),
                DB::raw("COALESCE(clientes.calle_avenida, 'N/A') AS calle_avenida"),
                DB::raw("COALESCE(clientes.zona_barrio, 'N/A') AS zona_barrio"),
                DB::raw("COALESCE(clientes.referencia_direccion, 'N/A') AS referencia_direccion"),
                'clientes.ruta_id AS ruta_id'
            )
            ->groupBy(
                'ventas_mayoristas.numero_venta',
                'ventas_mayoristas.id_usuario',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'clientes.referencia_direccion',
                'clientes.ruta_id'
            )
            ->get();

        $pedidos = VentaMayorista::query()
            ->leftJoin('productos', 'ventas_mayoristas.id_producto', '=', 'productos.id')
            ->leftJoin('forma_ventas', 'ventas_mayoristas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas_mayoristas.numero_venta', $ventaBase->numero_venta)
            ->where('ventas_mayoristas.id_usuario', $ventaBase->id_usuario)
            ->select(
                DB::raw('COALESCE(productos.id, ventas_mayoristas.id_producto) AS id_producto'),
                DB::raw("COALESCE(productos.codigo, 'N/A') AS codigo"),
                DB::raw("COALESCE(productos.nombre_producto, 'Producto no disponible') AS nombre_producto"),
                DB::raw('COALESCE(productos.cantidad, 0) AS cantidad_stock'),
                DB::raw("COALESCE(productos.detalle_cantidad, 'unidades') AS detalle_cantidad"),
                DB::raw("COALESCE(forma_ventas.tipo_venta, 'N/A') AS tipo_venta"),
                'ventas_mayoristas.precio_unitario AS precio_venta',
                'ventas_mayoristas.id AS id_pedido',
                'ventas_mayoristas.id_usuario AS id_vendedor',
                'ventas_mayoristas.numero_venta AS numero_pedido',
                'ventas_mayoristas.cantidad AS cantidad_pedido',
                DB::raw('FALSE AS promocion'),
                DB::raw('0 AS descripcion_descuento_porcentaje'),
                DB::raw("'' AS descripcion_regalo")
            )
            ->orderBy('ventas_mayoristas.id')
            ->get();

        $pdf = Pdf::loadView('administrador.pdf.pdf_despachar', [
            'pedidos' => $pedidos,
            'lista_de_pedidos' => $listaDePedidos,
        ]);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('hoja-pedido-mayorista-' . $ventaBase->numero_venta . '.pdf');
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
                'ventas_mayoristas.id_producto AS venta_id_producto',
                'ventas_mayoristas.id_forma_venta AS venta_id_forma_venta',
                'ventas_mayoristas.cantidad AS venta_cantidad',
                'ventas_mayoristas.precio_unitario AS venta_precio_unitario',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.equivalencia_cantidad'
            )
            ->orderBy('productos.nombre_producto')
            ->get()
            ->map(function ($item) {
                $precio = (float) $item->venta_precio_unitario;
                $cantidad = (int) $item->venta_cantidad;
                return [
                    'id_producto' => (int) $item->venta_id_producto,
                    'id_forma_venta' => (int) $item->venta_id_forma_venta,
                    'codigo_producto' => $item->codigo,
                    'texto_producto' => $item->nombre_producto,
                    'tipo_venta' => $item->tipo_venta,
                    'precio_venta' => round($precio, 2),
                    'cantidad' => $cantidad,
                    'equivalencia_cantidad' => (int) $item->equivalencia_cantidad,
                    'sub_total' => round($precio * $cantidad, 2),
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

        try {
            $resultado = DB::transaction(function () use ($request, $productos) {
                $numeroVentaEditar = $request->input('numero_pedido');
                $cliente = Cliente::findOrFail($request->cliente_id);
                $usuarioVenta = auth()->id();
                $fechaVenta = now();
                $lineasOriginales = collect();
                $cantidadesOriginales = collect();

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
                    $lineasOriginales = $anteriores
                        ->map(fn ($venta) => (int) $venta->id_producto . '-' . (int) $venta->id_forma_venta)
                        ->unique()
                        ->values();
                    $cantidadesOriginales = $anteriores
                        ->groupBy(fn ($venta) => (int) $venta->id_producto . '-' . (int) $venta->id_forma_venta)
                        ->map(fn ($ventas) => (int) $ventas->sum('cantidad'));

                    $this->reincorporarStockVentasMayoristas($anteriores);

                    VentaMayorista::query()
                        ->whereIn('id', $anteriores->pluck('id'))
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
                    if ($productoPedido['id_producto'] <= 0 || $productoPedido['id_forma_venta'] <= 0) {
                        abort(response()->json([
                            'message' => 'Hay una linea con producto o forma de venta invalida. Vuelve a cargar el pedido e intenta nuevamente.',
                        ], 422));
                    }

                    if ($productoPedido['cantidad'] <= 0) {
                        abort(response()->json(['message' => 'La cantidad debe ser mayor a cero.'], 422));
                    }

                    if ($productoPedido['precio_venta'] <= 0) {
                        abort(response()->json(['message' => 'El precio de venta debe ser mayor a cero.'], 422));
                    }

                    $esLineaOriginal = $lineasOriginales->contains($productoPedido['id_producto'] . '-' . $productoPedido['id_forma_venta']);

                    $formaVenta = FormaVenta::query()
                        ->where('id', $productoPedido['id_forma_venta'])
                        ->where('id_producto', $productoPedido['id_producto'])
                        ->when(! $esLineaOriginal, fn ($query) => $query->where('activo', true))
                        ->first();

                    if (! $formaVenta) {
                        abort(response()->json([
                            'message' => 'Una forma de venta ya no esta disponible para el producto seleccionado. Quita esa linea y agregala nuevamente.',
                        ], 422));
                    }

                    $productoModel = Producto::query()
                        ->where('id', $productoPedido['id_producto'])
                        ->when(! $esLineaOriginal, fn ($query) => $query->where('estado_de_baja', false))
                        ->lockForUpdate()
                        ->first();

                    if (! $productoModel) {
                        abort(response()->json([
                            'message' => 'Uno de los productos del pedido ya no existe o fue dado de baja. Quita esa linea y vuelve a agregar un producto activo.',
                        ], 422));
                    }

                    if (
                        $esLineaOriginal
                        && ((! (bool) $formaVenta->activo) || (bool) $productoModel->estado_de_baja)
                        && $productoPedido['cantidad'] > (int) $cantidadesOriginales->get($productoPedido['id_producto'] . '-' . $productoPedido['id_forma_venta'], 0)
                    ) {
                        abort(response()->json([
                            'message' => 'Este producto o forma de venta ya no esta activo. Puedes conservar o reducir la cantidad original, pero no aumentarla.',
                        ], 422));
                    }

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
                    $esLineaOriginal = $lineasOriginales->contains($productoPedido['id_producto'] . '-' . $productoPedido['id_forma_venta']);

                    $formaVenta = FormaVenta::query()
                        ->where('id', $productoPedido['id_forma_venta'])
                        ->where('id_producto', $productoPedido['id_producto'])
                        ->when(! $esLineaOriginal, fn ($query) => $query->where('activo', true))
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
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al guardar venta mayorista desde panel.', [
                'numero_pedido' => $request->input('numero_pedido'),
                'cliente_id' => $request->input('cliente_id'),
                'productos' => collect($productos)->map(fn ($producto) => [
                    'id_producto' => $producto['id_producto'] ?? null,
                    'id_forma_venta' => $producto['id_forma_venta'] ?? null,
                    'cantidad' => $producto['cantidad'] ?? null,
                ])->values()->all(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo guardar la venta mayorista. No se realizaron cambios en el inventario. Revisa si algun producto fue dado de baja o ya no existe.',
            ], 500);
        }

        return response()->json([
            'message' => 'Venta mayorista guardada correctamente.',
            'numero_pedido' => $resultado,
        ], 201);
    }

    public function eliminarPedido(string $numeroPedido)
    {
        try {
            DB::transaction(function () use ($numeroPedido) {
                $ventas = $this->ventasVisibles()
                    ->where('numero_venta', $numeroPedido)
                    ->lockForUpdate()
                    ->get();

                if ($ventas->isEmpty()) {
                    abort(response()->json(['message' => 'La venta mayorista ya no esta disponible o ya fue eliminada.'], 404));
                }

                $this->reincorporarStockVentasMayoristas($ventas);

                VentaMayorista::query()
                    ->whereIn('id', $ventas->pluck('id'))
                    ->delete();
            }, 3);

            return response()->json([
                'message' => 'Venta mayorista eliminada correctamente. El stock fue reincorporado al inventario.',
            ], 200);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al eliminar venta mayorista y reincorporar stock.', [
                'numero_pedido' => $numeroPedido,
                'usuario_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo eliminar la venta mayorista. No se realizaron cambios en el inventario.',
            ], 500);
        }
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

    private function reincorporarStockVentasMayoristas($ventas): void
    {
        foreach ($ventas as $venta) {
            $formaVenta = FormaVenta::query()
                ->where('id', $venta->id_forma_venta)
                ->lockForUpdate()
                ->firstOrFail();

            $producto = Producto::query()
                ->where('id', $venta->id_producto)
                ->lockForUpdate()
                ->firstOrFail();

            $producto->cantidad += ((int) $venta->cantidad * (float) $formaVenta->equivalencia_cantidad);
            $producto->save();
        }
    }
}
