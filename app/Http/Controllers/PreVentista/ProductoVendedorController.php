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
    
    public function descargarCatalogo()
    {
        $productos = Producto::where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->get();
        $marcas = Marca::all();
        $lineas = Linea::all();

        $pdf = Pdf::setOptions([
                'dpi' => 96,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,   // true solo si usas http(s) en <img>
                'chroot' => public_path(),    // restringe a /public
            ])
            ->loadView('vendedor.pdf.catalogo_pdf', compact('productos','marcas','lineas'))
            ->setPaper('letter', 'landscape');

        return $pdf->stream('catalogo.pdf');
    }
    
}
