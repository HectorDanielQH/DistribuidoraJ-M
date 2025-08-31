<?php

namespace App\Http\Controllers\PreVentista;

use App\Http\Controllers\Controller;

use App\Models\FormaVenta;
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
        if($request->ajax())
        {
            $query = Producto::query()->where('estado_de_baja', false)->where('cantidad', '>', 0);
            if ($request->filled('nombre')) {
                $query->where('nombre_producto', 'like', '%' . strtoupper($request->nombre) . '%');
            }

            if ($request->filled('codigo')) {
                $query->where('codigo', 'like', '%' . $request->codigo . '%');
            }

            return $dataTables->eloquent($query)
                ->addColumn('acciones', function ($producto) {
                    return '';
                })
                ->editColumn('imagen', function ($producto) {
                    if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                }) 
                ->addColumn('marca', function ($producto) {
                    return $producto->marca ? $producto->marca->descripcion : 'Sin Marca';
                })
                ->addColumn('stock', function ($producto) {
                    $claseCantidad = $producto->cantidad <= 15 ? 'badge bg-danger' : 'badge bg-success';
                    $cantidadHtml = '<span class="' . $claseCantidad . ' fs-6">' . $producto->cantidad . ' ' . $producto->detalle_cantidad . '</span>';
                    return $cantidadHtml;
                })
                ->addColumn('formas_venta', function ($producto) {
                    $formasVenta = FormaVenta::where('id_producto', $producto->id)->get();
                    if ($formasVenta->isEmpty()) {
                        $output = '<div class="d-flex flex-column">';
                        foreach ($formasVenta as $formaVenta) {
                            $output .= '
                                <div class="border rounded p-2 '.
                                ($formaVenta->activo ? 'bg-white' : ' bg-secondary text-white')
                                .' shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <strong>' . ucfirst($formaVenta->tipo_venta) . '</strong>
                                        </div>
                                        <span class="badge bg-success fs-6">
                                            Bs.-' . number_format($formaVenta->precio_venta, 2, ',', '.') . '
                                        </span>
                                    </div>
                                </div>
                            ';
                        }
                        $output .= '</div>';
                    return $output;
                    } else {
                        return '<span class="badge bg-secondary fs-6">Sin Forma de Venta</span>';
                    }
                })
                ->addColumn('promocion_vista', function($producto){
                    $descuento = ($producto->descripcion_descuento_porcentaje !== null && $producto->descripcion_descuento_porcentaje !== '')
                        ? $producto->descripcion_descuento_porcentaje . '%'
                        : 'N/A';

                    $regalo = ($producto->descripcion_regalo !== null && $producto->descripcion_regalo !== '' )
                        ? $producto->descripcion_regalo
                        :'N/A';
                    $render = '<div class="d-flex flex-column align-items-center justify-content-center mb-2 bg-white">';
                    if ($producto->promocion) {
                        $render = '
                        <span>
                            Descuento: <strong>' . $descuento . '</strong><br>
                            Regalo: <strong>' . $regalo . '</strong>
                        </span>
                        <br/>
                        ';
                    } else {
                        $render .= '<span class="badge bg-secondary fs-6">Sin Promoción</span>';
                    }
                    $render .= '</div>';
                    return $render;
                })
                ->rawColumns(['acciones', 'imagen', 'stock', 'formas_venta','promocion_vista'])
                ->make(true);
        }

        $contar_productos_promocion = Producto::where('promocion', true)
                ->where('estado_de_baja', false)
                ->where('cantidad', '>', 0)
                ->count();

        return view('vendedor.productos.index', compact('contar_productos_promocion'));
    }

    public function verDetalleProductosPromocion(){
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

        $formas_venta = FormaVenta::where('id_producto', $producto->id)
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
            return response()->json(['error' => 'El producto no está en promoción'], 404);
        }

        return response()->json([
            'producto' => $producto
        ]);
    }

    public function descargarCatalogo() {
        $productos = Producto::where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->get();
        $marcas = Marca::all();
        $lineas = Linea::all();

        $pdf = Pdf::loadView('vendedor.pdf.catalogo_pdf', compact('productos','marcas','lineas'));
        $pdf->setOptions([
            'dpi' => 96,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'Helvetica',
            'chroot' => base_path(),
        ])->setPaper('letter', 'horizontal'); // 'landscape' (no 'horizontal')

        $dir = storage_path('app/private/catalogos');
        @mkdir($dir, 0775, true);

        $original = $dir.'/catalogo_raw.pdf';
        $comprimido = $dir.'/catalogo.pdf';

        file_put_contents($original, $pdf->output());

        // Comprimir con Ghostscript
        $in  = escapeshellarg($original);
        $out = escapeshellarg($comprimido);
        $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen ".
            "-dDownsampleColorImages=true -dColorImageResolution=96 ".
            "-dNOPAUSE -dQUIET -dBATCH -sOutputFile=$out $in";
        @exec($cmd, $o, $code);

        // Si falla gs, sirve el original; si no, sirve el comprimido
        $fileToServe = (file_exists($comprimido) && filesize($comprimido) > 0) ? $comprimido : $original;

        return response()->file($fileToServe, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="catalogo.pdf"',
        ]);
    }
    
}
