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
                    return view('vendedor.productos.partials.acciones', compact('producto'));
                })
                ->editColumn('foto_producto', function ($producto) {
                    return view('vendedor.productos.partials.foto_producto', compact('producto'));
                })
                ->rawColumns(['acciones', 'foto_producto'])
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
            'dpi' => 72, // ya ayuda
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
            "-dDownsampleColorImages=true -dColorImageResolution=72 ".
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
