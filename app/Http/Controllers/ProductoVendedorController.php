<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoVendedorController extends Controller
{
    public function obtenerProductos(Request $request)
    {
        $query = Producto::query()->where('estado_de_baja', false)->where('cantidad', '>', 0);

        if ($request->filled('nombre')) {
            $query->where('nombre_producto', 'like', '%' . strtoupper($request->nombre) . '%');
        }

        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }

        $productos = $query->paginate(10);

        $contar_productos_promocion = Producto::where('promocion', true)
            ->where('estado_de_baja', false)
            ->where('cantidad', '>', 0)
            ->count();

        return view('vendedor.productos.index',compact('productos', 'contar_productos_promocion'))->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('codigo'));
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
            ->get()
            ->map(function ($producto) {
                $ruta = $producto->foto_producto; // Ya contiene 'foto_producto/....'

                if (Storage::disk('local')->exists($ruta)) {
                    $contenido = Storage::disk('local')->get($ruta);
                    $tipo = pathinfo($ruta, PATHINFO_EXTENSION);
                    $producto->imagen_base64 = 'data:image/' . $tipo . ';base64,' . base64_encode($contenido);
                } else {
                    $producto->imagen_base64 = null;
                }

                return $producto;
            });
        
        $marcas = Marca::all();
        $lineas = Linea::all();

        $pdf = Pdf::loadView('vendedor.pdf.caralogo_pdf', compact('productos','marcas','lineas'));
        $pdf->setPaper('letter', 'horizontal');
        return $pdf->stream('caralogo.pdf');
    }
    
}
