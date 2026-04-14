<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Lotes;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class LotesController extends Controller
{
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $query = Lotes::query()
                ->selectRaw('codigo_lote, COUNT(*) as items, SUM(cantidad) as cantidad_total, MAX(ingreso_lote) as ultimo_ingreso')
                ->groupBy('codigo_lote');

            return $dataTables->eloquent($query)
                ->addColumn('items', fn ($lote) => $lote->items . ' productos')
                ->addColumn('cantidad', fn ($lote) => number_format((int) $lote->cantidad_total) . ' unidades')
                ->addColumn('ultimo_ingreso', function ($lote) {
                    return $lote->ultimo_ingreso ? Carbon::parse($lote->ultimo_ingreso)->format('d/m/Y H:i') : 'Sin fecha';
                })
                ->addColumn('acciones', function ($lote) {
                    $route = route('administrador.lote.productos.obtenerLotesProducto', ['id' => $lote->codigo_lote]);

                    return '<div class="lot-actions">
                        <a class="btn btn-warning btn-sm lot-action-btn" href="' . $route . '">
                            <i class="fas fa-eye"></i> Ver detalle
                        </a>
                    </div>';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $resumenLotes = [
            'lotes' => Lotes::distinct('codigo_lote')->count('codigo_lote'),
            'items' => Lotes::count(),
            'unidades' => Lotes::sum('cantidad'),
            'anulados' => Lotes::onlyTrashed()->count(),
        ];

        return view('administrador.lotes.index', compact('resumenLotes'));
    }

    public function create(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $lotes = Lotes::query()
                ->with('producto')
                ->where('codigo_lote', $request->lote);

            return $dataTables->eloquent($lotes)
                ->addColumn('producto', fn ($lote) => $lote->producto ? $lote->producto->nombre_producto : 'N/A')
                ->addColumn('precio_ingreso', fn ($lote) => $this->formatearPrecioLote($lote))
                ->addColumn('ingreso_lote', fn ($lote) => $lote->ingreso_lote ? $lote->ingreso_lote->format('d/m/Y H:i') : 'Sin fecha')
                ->addColumn('acciones', fn ($lote) => $this->botonesAccionesLote($lote))
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $lote_max = $this->siguienteCodigoLote();
        return view('administrador.lotes.create', compact('lote_max'));
    }

    public function store(Request $request)
    {
        $data = $this->validarLote($request);

        $lote = DB::transaction(function () use ($data) {
            $producto = Producto::whereKey($data['producto_id'])->lockForUpdate()->firstOrFail();
            $stockAntes = (int) $producto->cantidad;
            $cantidad = (int) $data['cantidad_producto'];

            $lote = Lotes::create([
                'codigo_lote' => $data['codigo_lote'],
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'detalle_cantidad' => $this->normalizarTexto($data['descripcion_cantidad']),
                'precio_ingreso' => $this->normalizarDecimal($data['precio_compra']),
                'detalle_precio_ingreso' => $this->normalizarTexto($data['descripcion_precio_compra']),
                'ingreso_lote' => now(),
                'fecha_vencimiento' => $data['vencimiento_producto'] ?? null,
                'stock_antes' => $stockAntes,
                'stock_despues' => $stockAntes + $cantidad,
                'observacion' => ! empty($data['observacion']) ? $this->normalizarTexto($data['observacion']) : null,
            ]);

            $this->sincronizarProductoConLote($producto, $lote, $lote->stock_despues);

            return $lote;
        });

        return response()->json([
            'success' => true,
            'message' => 'Ingreso registrado y stock actualizado correctamente.',
            'lote_id' => $lote->id,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $lote = Lotes::withTrashed()->with('producto')->findOrFail($id);

        if ($request->ajax()) {
            return response()->json([
                'lote' => $lote,
                'producto' => $lote->producto,
                'precio_formateado' => $this->formatearPrecioLote($lote),
                'estado' => $lote->trashed() ? 'Anulado' : 'Activo',
            ]);
        }

        return redirect()->route('administrador.lote.productos.obtenerLotesProducto', $lote->codigo_lote);
    }

    public function edit(string $id)
    {
        return redirect()->route('administrador.lotes.show', $id);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validarLote($request, $id);

        $lote = DB::transaction(function () use ($data, $id) {
            $lote = Lotes::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($lote->trashed()) {
                throw ValidationException::withMessages([
                    'lote' => 'No se puede editar un ingreso anulado.',
                ]);
            }

            if ((int) $lote->producto_id !== (int) $data['producto_id']) {
                throw ValidationException::withMessages([
                    'producto_id' => 'No se permite cambiar el producto de un ingreso. Anula este registro y crea uno nuevo.',
                ]);
            }

            $producto = Producto::whereKey($lote->producto_id)->lockForUpdate()->firstOrFail();
            $cantidadAnterior = (int) $lote->cantidad;
            $cantidadNueva = (int) $data['cantidad_producto'];
            $diferencia = $cantidadNueva - $cantidadAnterior;
            $stockNuevo = (int) $producto->cantidad + $diferencia;

            if ($stockNuevo < 0) {
                throw ValidationException::withMessages([
                    'cantidad_producto' => 'No se puede editar: el stock quedaria negativo.',
                ]);
            }

            $lote->update([
                'cantidad' => $cantidadNueva,
                'detalle_cantidad' => $this->normalizarTexto($data['descripcion_cantidad']),
                'precio_ingreso' => $this->normalizarDecimal($data['precio_compra']),
                'detalle_precio_ingreso' => $this->normalizarTexto($data['descripcion_precio_compra']),
                'fecha_vencimiento' => $data['vencimiento_producto'] ?? null,
                'stock_antes' => (int) $producto->cantidad,
                'stock_despues' => $stockNuevo,
                'observacion' => ! empty($data['observacion']) ? $this->normalizarTexto($data['observacion']) : null,
            ]);

            $producto->cantidad = $stockNuevo;

            if ($this->esUltimoLoteDelProducto($lote)) {
                $this->sincronizarPrecioConLote($producto, $lote);
            }

            $producto->save();

            return $lote;
        });

        return response()->json([
            'success' => true,
            'message' => 'Ingreso editado y stock sincronizado correctamente.',
            'lote_id' => $lote->id,
        ]);
    }

    public function destroy(string $id)
    {
        return $this->eliminarLote($id);
    }

    public function obtenerProducto(Request $request)
    {
        $search = trim(strtoupper($request->get('query')));

        $productos = Producto::where('nombre_producto', 'LIKE', "%{$search}%")
            ->orWhere('codigo', 'LIKE', "%{$search}%")
            ->orderBy('nombre_producto')
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    public function obtenerDetalleProducto($id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'detalle_cantidad' => $producto->detalle_cantidad,
            'precio_compra' => $producto->precio_compra,
            'detalle_precio_compra' => $producto->detalle_precio_compra,
            'stock_actual' => $producto->cantidad,
            'producto' => $producto,
        ]);
    }

    public function obtenerLotesProducto(Request $request, string $id)
    {
        if ($request->ajax()) {
            $lotes = Lotes::query()
                ->with('producto')
                ->where('codigo_lote', $id);

            return datatables()->eloquent($lotes)
                ->addColumn('codigo_producto', fn ($lote) => $lote->producto ? $lote->producto->codigo : 'N/A')
                ->addColumn('imagen', function ($lote) {
                    if (! $lote->producto) {
                        return 'Sin imagen';
                    }

                    $ruta = route('productos.imagen', $lote->producto->id);
                    return '<img src="' . $ruta . '" alt="Imagen" width="52" height="52" class="lot-product-image"/>';
                })
                ->addColumn('descripcion', fn ($lote) => $lote->producto ? $lote->producto->nombre_producto : 'N/A')
                ->addColumn('cantidad_anadida', fn ($lote) => $lote->cantidad ? $lote->cantidad . ' ' . $lote->detalle_cantidad : 'N/A')
                ->addColumn('nuevo_precio', fn ($lote) => $this->formatearPrecioLote($lote))
                ->addColumn('fecha_vencimiento', function ($lote) {
                    return $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento';
                })
                ->addColumn('stock_movimiento', function ($lote) {
                    $antes = $lote->stock_antes ?? 'N/D';
                    $despues = $lote->stock_despues ?? 'N/D';
                    return '<span class="lot-stock-move">' . $antes . ' <i class="fas fa-arrow-right"></i> ' . $despues . '</span>';
                })
                ->addColumn('ingreso_lote', fn ($lote) => $lote->ingreso_lote ? $lote->ingreso_lote->format('d/m/Y H:i') : 'Sin fecha')
                ->addColumn('acciones', fn ($lote) => $this->botonesAccionesLote($lote))
                ->rawColumns(['imagen', 'acciones', 'stock_movimiento'])
                ->make(true);
        }

        $lotes = Lotes::where('codigo_lote', $id)->firstOrFail();

        return view('administrador.lotes.show', compact('lotes'));
    }

    public function eliminarLote(string $id)
    {
        DB::transaction(function () use ($id) {
            $lote = Lotes::whereKey($id)->lockForUpdate()->first();

            if (! $lote) {
                throw ValidationException::withMessages([
                    'lote' => 'Lote no encontrado.',
                ]);
            }

            $producto = Producto::whereKey($lote->producto_id)->lockForUpdate()->first();

            if ($producto) {
                $stockAntesAnular = (int) $producto->cantidad;
                $stockNuevo = max(0, $stockAntesAnular - (int) $lote->cantidad);
                $producto->cantidad = $stockNuevo;

                if ($this->esUltimoLoteDelProducto($lote)) {
                    $this->sincronizarPrecioConUltimoLoteDisponible($producto, $lote->id);
                }

                $producto->save();
            }

            $lote->stock_antes = $producto ? $stockAntesAnular : $lote->stock_antes;
            $lote->stock_despues = $producto ? (int) $producto->cantidad : $lote->stock_despues;
            $lote->observacion = trim(($lote->observacion ? $lote->observacion . ' | ' : '') . 'ANULADO DESDE PANEL DE LOTES');
            $lote->save();
            $lote->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Ingreso anulado. El stock fue descontado y el historial se conserva.',
        ]);
    }

    private function validarLote(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'codigo_lote' => 'required|string|max:255',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_producto' => 'required|integer|min:1',
            'descripcion_cantidad' => 'required|string|max:255',
            'precio_compra' => 'required|numeric|min:0.01',
            'descripcion_precio_compra' => 'required|string|max:255',
            'vencimiento_producto' => 'nullable|date',
            'observacion' => 'nullable|string|max:255',
        ], [
            'codigo_lote.required' => 'El codigo del lote es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no es valido.',
            'cantidad_producto.min' => 'La cantidad debe ser al menos 1.',
            'descripcion_cantidad.required' => 'La descripcion de la cantidad es obligatoria.',
            'precio_compra.min' => 'El precio de compra debe ser mayor a cero.',
            'descripcion_precio_compra.required' => 'La descripcion del precio de compra es obligatoria.',
            'vencimiento_producto.date' => 'La fecha de vencimiento no es valida.',
            'observacion.max' => 'La observacion no puede exceder los 255 caracteres.',
        ]);
    }

    private function normalizarTexto(?string $texto): ?string
    {
        return $texto ? trim(strtoupper($texto)) : null;
    }

    private function normalizarDecimal($valor): float
    {
        return (float) str_replace(',', '.', $valor);
    }

    private function siguienteCodigoLote(): string
    {
        $loteMax = Lotes::withTrashed()->max('codigo_lote');

        if (! $loteMax) {
            return 'LT000001';
        }

        return 'LT' . str_pad((int) substr($loteMax, 2) + 1, 6, '0', STR_PAD_LEFT);
    }

    private function sincronizarProductoConLote(Producto $producto, Lotes $lote, int $stockNuevo): void
    {
        $producto->cantidad = $stockNuevo;
        $this->sincronizarPrecioConLote($producto, $lote);
        $producto->save();
    }

    private function sincronizarPrecioConLote(Producto $producto, Lotes $lote): void
    {
        $producto->detalle_cantidad = $lote->detalle_cantidad;
        $producto->precio_compra = $lote->precio_ingreso;
        $producto->detalle_precio_compra = $lote->detalle_precio_ingreso;
        $producto->fecha_vencimiento = $lote->fecha_vencimiento;
    }

    private function sincronizarPrecioConUltimoLoteDisponible(Producto $producto, int $loteIgnorado): void
    {
        $ultimoLote = Lotes::where('producto_id', $producto->id)
            ->where('id', '!=', $loteIgnorado)
            ->latest('ingreso_lote')
            ->first();

        if (! $ultimoLote) {
            return;
        }

        $producto->detalle_cantidad = $ultimoLote->detalle_cantidad;
        $producto->precio_compra = $ultimoLote->precio_ingreso;
        $producto->detalle_precio_compra = $ultimoLote->detalle_precio_ingreso;
        $producto->fecha_vencimiento = $ultimoLote->fecha_vencimiento;
    }

    private function esUltimoLoteDelProducto(Lotes $lote): bool
    {
        $ultimoLote = Lotes::where('producto_id', $lote->producto_id)
            ->latest('ingreso_lote')
            ->first();

        return $ultimoLote && (int) $ultimoLote->id === (int) $lote->id;
    }

    private function formatearPrecioLote(Lotes $lote): string
    {
        $valor = $lote->precio_ingreso ? number_format($lote->precio_ingreso, 2, '.', ',') . ' Bs.-' : 'N/A';
        return $valor . ($lote->detalle_precio_ingreso ? ' (' . $lote->detalle_precio_ingreso . ')' : '');
    }

    private function botonesAccionesLote(Lotes $lote): string
    {
        return '<div class="lot-actions">
            <button type="button" class="btn btn-primary btn-sm lot-action-btn" onclick="verLote(this)" id-lote="' . $lote->id . '" codigo-lote="' . $lote->codigo_lote . '">
                <i class="fas fa-eye"></i> Ver
            </button>
            <button type="button" class="btn btn-warning btn-sm lot-action-btn" onclick="editarLote(this)" id-lote="' . $lote->id . '" codigo-lote="' . $lote->codigo_lote . '">
                <i class="fas fa-edit"></i> Editar
            </button>
            <button type="button" class="btn btn-danger btn-sm lot-action-btn" onclick="eliminarLote(this)" id-lote="' . $lote->id . '" codigo-lote="' . $lote->codigo_lote . '">
                <i class="fas fa-ban"></i> Anular
            </button>
        </div>';
    }
}
