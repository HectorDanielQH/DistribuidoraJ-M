<?php

namespace App\Http\Controllers\PreVentista;

use App\Http\Controllers\Controller;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class ProductoVendedorController extends Controller
{
    public function obtenerProductos(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $query = Producto::query()
                ->with(['marca', 'formaVentas' => function ($query) {
                    $query->where('activo', true);
                }])
                ->where('estado_de_baja', false)
                ->where('cantidad', '>', 0);

            if ($request->filled('nombre')) {
                $query->where('nombre_producto', 'ILIKE', '%' . strtoupper($request->nombre) . '%');
            }

            if ($request->filled('codigo')) {
                $query->where('codigo', 'ILIKE', '%' . strtoupper($request->codigo) . '%');
            }

            if ($request->filled('marca')) {
                $query->where('id_marca', $request->marca);
            }

            if ($request->filled('linea')) {
                $query->where('id_linea', $request->linea);
            }

            if ($request->filled('promocion')) {
                $query->where('promocion', $request->promocion === 'si');
            }

            if ($request->filled('stock')) {
                if ($request->stock === 'bajo') {
                    $query->where('cantidad', '<=', 15);
                }

                if ($request->stock === 'disponible') {
                    $query->where('cantidad', '>', 15);
                }
            }

            return $dataTables->eloquent($query)
                ->addColumn('acciones', function () {
                    return '';
                })
                ->editColumn('imagen', function ($producto) {
                    if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $producto->id]) . '" class="seller-product-img" alt="' . e($producto->nombre_producto) . '">';
                    }

                    return '<img src="' . asset('images/logo_color.webp') . '" class="seller-product-img" alt="Producto">';
                })
                ->editColumn('codigo', function ($producto) {
                    return '<span class="seller-code">' . e($producto->codigo) . '</span>';
                })
                ->editColumn('nombre_producto', function ($producto) {
                    return '<div class="seller-product-name">'
                        . '<strong>' . e($producto->nombre_producto) . '</strong>'
                        . '<span>' . e($producto->descripcion_producto) . '</span>'
                        . '</div>';
                })
                ->addColumn('marca', function ($producto) {
                    return '<span class="seller-brand">' . e($producto->marca ? $producto->marca->descripcion : 'Sin marca') . '</span>';
                })
                ->addColumn('stock', function ($producto) {
                    $class = $producto->cantidad <= 15 ? 'seller-stock seller-stock-low' : 'seller-stock seller-stock-ok';

                    return '<span class="' . $class . '">' . (int) $producto->cantidad . ' ' . e($producto->detalle_cantidad) . '</span>';
                })
                ->addColumn('formas_venta', function ($producto) {
                    $formasVenta = $producto->formaVentas;

                    if ($formasVenta->isNotEmpty()) {
                        $output = '<div class="seller-sale-list">';

                        foreach ($formasVenta as $formaVenta) {
                            $output .= '<span><strong>' . e($formaVenta->tipo_venta) . '</strong> Bs. ' . number_format($formaVenta->precio_venta, 2, '.', ',') . '</span>';
                        }

                        $output .= '</div>';
                        $output .= '<button type="button" class="btn btn-sm seller-detail-btn" id-producto="' . $producto->id . '" onclick="verDetalleFormaVenta(this)">Ver precios</button>';

                        return $output;
                    }

                    return '<span class="seller-empty">Sin forma de venta</span>';
                })
                ->addColumn('promocion_vista', function ($producto) {
                    $descuento = ($producto->descripcion_descuento_porcentaje !== null && $producto->descripcion_descuento_porcentaje !== '')
                        ? $producto->descripcion_descuento_porcentaje . '%'
                        : 'N/A';

                    $regalo = ($producto->descripcion_regalo !== null && $producto->descripcion_regalo !== '')
                        ? $producto->descripcion_regalo
                        : 'N/A';

                    if ($producto->promocion) {
                        return '<div class="seller-promo">'
                            . '<strong>Promocion</strong>'
                            . '<span>Desc: ' . e($descuento) . '</span>'
                            . '<span>Regalo: ' . e($regalo) . '</span>'
                            . '<button type="button" class="btn btn-sm seller-detail-btn" id-producto="' . $producto->id . '" onclick="verDetallePromocion(this)">Ver promo</button>'
                            . '</div>';
                    }

                    return '<span class="seller-empty">Sin promocion</span>';
                })
                ->rawColumns(['acciones', 'codigo', 'imagen', 'nombre_producto', 'marca', 'stock', 'formas_venta', 'promocion_vista'])
                ->make(true);
        }

        $contar_productos_promocion = Producto::where('promocion', true)
            ->where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->count();
        $marcas = Marca::orderBy('descripcion')->get();
        $lineas = Linea::orderBy('descripcion_linea')->get();

        return view('vendedor.productos.index', compact('contar_productos_promocion', 'marcas', 'lineas'));
    }

    public function verDetalleProductosPromocion()
    {
        $contar_productos_promocion = Producto::where('promocion', true)
            ->where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->get();

        return response()->json([
            'productos' => $contar_productos_promocion
        ]);
    }

    public function verDetalleFormaVenta(string $id_producto)
    {
        $producto = Producto::find($id_producto);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $formas_venta = $producto->formaVentas()
            ->where('activo', true)
            ->get();

        return response()->json([
            'formas_venta' => $formas_venta
        ]);
    }

    public function verDetallePromocion(string $id_producto)
    {
        $producto = Producto::find($id_producto);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        if (!$producto->promocion) {
            return response()->json(['error' => 'El producto no esta en promocion'], 404);
        }

        return response()->json([
            'producto' => $producto
        ]);
    }

    public function descargarCatalogo()
    {
        $productos = Producto::where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->with(['marca', 'linea', 'formaVentas' => function ($query) {
                $query->where('activo', true)->orderBy('precio_venta');
            }])
            ->orderBy('id_marca')
            ->orderBy('id_linea')
            ->orderBy('nombre_producto')
            ->get();

        $marcas = Marca::with(['linea.productos' => function ($query) {
            $query->where('estado_de_baja', false)
                ->where('cantidad', '>', 0)
                ->with(['formaVentas' => function ($query) {
                    $query->where('activo', true)->orderBy('precio_venta');
                }])
                ->orderBy('nombre_producto');
        }])->orderBy('descripcion')->get();

        $resumen = [
            'total_productos' => $productos->count(),
            'total_promociones' => $productos->where('promocion', true)->count(),
            'total_marcas' => $productos->pluck('id_marca')->unique()->count(),
            'fecha' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('vendedor.pdf.catalogo_pdf', compact('productos', 'marcas', 'resumen'));
        $pdf->setOptions([
            'dpi' => 96,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'Helvetica',
            'chroot' => base_path(),
        ])->setPaper('letter', 'horizontal');

        $dir = storage_path('app/private/catalogos');
        @mkdir($dir, 0775, true);

        $original = $dir . '/catalogo_raw.pdf';
        $comprimido = $dir . '/catalogo.pdf';

        file_put_contents($original, $pdf->output());

        $in = escapeshellarg($original);
        $out = escapeshellarg($comprimido);
        $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen " .
            "-dDownsampleColorImages=true -dColorImageResolution=96 " .
            "-dNOPAUSE -dQUIET -dBATCH -sOutputFile=$out $in";
        @exec($cmd, $o, $code);

        $fileToServe = (file_exists($comprimido) && filesize($comprimido) > 0) ? $comprimido : $original;

        return response()->file($fileToServe, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="catalogo.pdf"',
        ]);
    }
}
