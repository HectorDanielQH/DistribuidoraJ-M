<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('nombre')) {
            $query->where('nombre_producto', 'like', '%' . strtoupper($request->nombre) . '%');
        }

        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }

        $proveedores = Proveedor::all();
        $productos = $query->paginate(10);
        

        $contar_productos_menores = Producto::where('cantidad', '<=', 15)->where('estado_de_baja',false)->count();

        return view('administrador.productos.index_productos',compact('proveedores','productos','contar_productos_menores'))->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('codigo'));
    }

    function obtenerProductosBajoStock()
    {
        $productos = Producto::where('cantidad', '<=', 15)->get();
        return response()->json($productos);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedors,id',
            'marca_id' => 'required|exists:marcas,id',
            'linea_id' => 'required|exists:lineas,id',
            'codigo' => 'required|string|max:255|unique:productos,codigo',
            'nombreProducto' => 'required|string|max:255',
            'descripcionProducto' => 'required|string|max:1000',
            'cantidadProducto' => 'required|integer|min:0',
            'descripcionCantidad' => 'required|string|max:255',
            'precioCompra' => 'required|numeric|min:0',
            'descripcionCompra' => 'required|string|max:255',
            'presentacionProducto' => 'nullable|string|max:255',
            'habilitarPromocion' => 'nullable',
            'promocionDescuento' => 'nullable|integer|min:0|max:100',
            'promocionRegalo' => 'nullable|string|max:255',
            'imagen_producto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'forma_venta' => 'required|array|min:1',
            'cantidad_venta' => 'required|array|min:1',
            'equivalencia_stock' => 'required|array|min:1',
        ],[
            'proveedor_id.required' => 'El proveedor es obligatorio.',
            'marca_id.required' => 'La marca es obligatoria.',
            'linea_id.required' => 'La línea es obligatoria.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'El código debe ser único.',
            'nombreProducto.required' => 'El nombre del producto es obligatorio.',
            'descripcionProducto'=> 'La descripción del producto es obligatoria.',
            'cantidadProducto.required' => 'La cantidad es obligatoria.',
            'cantidadProducto.integer' => 'La cantidad debe ser un número entero.',
            'cantidadProducto.min' => 'La cantidad no puede ser negativa.',
            'descripcionCantidad.required' => 'La descripción de la cantidad es obligatoria.',
            'precioCompra.required' => 'El precio de compra es obligatorio.',
            'precioCompra.numeric' => 'El precio de compra debe ser un número.',
            'precioCompra.min' => 'El precio de compra no puede ser negativo.',
            'descripcionCompra.required' => 'La descripción del precio de compra es obligatoria.',
            'presentacionProducto.max' => 'La presentación no puede exceder los 255 caracteres.',
            'promocionDescuento.integer' => 'El descuento de la promoción debe ser un número entero.',
            'promocionDescuento.min' => 'El descuento de la promoción no puede ser negativo.',
            'promocionDescuento.max' => 'El descuento de la promoción no puede ser mayor a 100.',
            'promocionRegalo.max' => 'La descripción del regalo de la promoción no puede exceder los 255 caracteres.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg, gif, svg o webp.',
            'imagen_producto.max' => 'La imagen no puede exceder los 2MB.',
            'forma_venta.required' => 'La forma de venta es obligatoria.',
            'forma_venta.array' => 'La forma de venta debe ser un arreglo.',
            'forma_venta.min' => 'Debe haber al menos una forma de venta.',
            'cantidad_venta.required' => 'La cantidad de venta es obligatoria.',
            'cantidad_venta.array' => 'La cantidad de venta debe ser un arreglo.',
            'cantidad_venta.min' => 'Debe haber al menos una cantidad de venta.',
            'equivalencia_stock.required' => 'La equivalencia de stock es obligatoria.',
            'equivalencia_stock.array' => 'La equivalencia de stock debe ser un arreglo.',
            'equivalencia_stock.min' => 'Debe haber al menos una equivalencia de stock.',
        ]);

        if($request->hasFile('imagen_producto')) {
            $file = $request->file('imagen_producto');
            $path=$file->store('foto_producto','local');
            $producto = Producto::create([
                'id_proveedor' => $request->proveedor_id,
                'id_marca' => $request->marca_id,
                'id_linea' => $request->linea_id,
                'codigo' => $request->codigo,
                'nombre_producto' => $request->nombreProducto,
                'descripcion_producto' => $request->descripcionProducto,
                'cantidad' => $request->cantidadProducto,
                'detalle_cantidad' => $request->descripcionCantidad,
                'precio_compra' => str_replace(',', '.', $request->precioCompra),
                'detalle_precio_compra' => $request->descripcionCompra,
                'presentacion' => $request->presentacionProducto,
                'promocion' => $request->habilitarPromocion ? true : false,
                'descripcion_descuento_porcentaje' => $request->promocionDescuento ?? 0,
                'descripcion_regalo' => $request->promocionRegalo ?? null,
                'foto_producto' => $path
            ]);   
        }
        else {
            $producto = Producto::create([
                'id_proveedor' => $request->proveedor_id,
                'id_marca' => $request->marca_id,
                'id_linea' => $request->linea_id,
                'codigo' => $request->codigo,
                'nombre_producto' => $request->nombreProducto,
                'descripcion_producto' => $request->descripcionProducto,
                'cantidad' => $request->cantidadProducto,
                'detalle_cantidad' => $request->descripcionCantidad,
                'precio_compra' => str_replace(',', '.', $request->precioCompra),
                'detalle_precio_compra' => $request->descripcionCompra,
                'presentacion' => $request->presentacionProducto,
                'promocion' => $request->habilitarPromocion ? true : false,
                'descripcion_descuento_porcentaje' => $request->promocionDescuento ?? 0,
                'descripcion_regalo' => $request->promocionRegalo ?? null,
            ]);
        }
        foreach ($request->forma_venta as $key => $forma_venta) {
            FormaVenta::create([
                'tipo_venta' => $forma_venta,
                'precio_venta' => str_replace(',', '.', $request->cantidad_venta[$key]),
                'equivalencia_cantidad' => $request->equivalencia_stock[$key],
                'id_producto' => $producto->id
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_producto)
    {
        $producto = Producto::findOrFail($id_producto);
        $formasVenta = FormaVenta::where('id_producto', $producto->id)->get();
        return response()->json([
            'proveedor' => $producto->proveedor,
            'marca' => $producto->marca,
            'linea' => $producto->linea,
            'producto' => $producto,
            'formasVenta' => $formasVenta
        ]);
    }

    public function obtenerCodigo()
    {
        $prefijo = "PROD-";
        do {
            $codigo_numerico = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $codigo_unico = $prefijo . $codigo_numerico;
        } while (Producto::where('codigo', $codigo_unico)->exists());

        return response()->json(['codigo' => $codigo_unico]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $producto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $producto)
    {
        $producto = Producto::findOrFail($producto);
        FormaVenta::where('id_producto', $producto->id)->delete();

        if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
            Storage::disk('local')->delete($producto->foto_producto);
        }
        $producto->delete();

        return response()->json(['success' => true]);
    }


    public function imagenProducto(string $id)
    {
        $producto = Producto::findOrFail($id);
        if (!$producto->foto_producto || !Storage::disk('local')->exists($producto->foto_producto)) {
            abort(404);
        }

        return response()->file(storage_path('app/private/' . $producto->foto_producto));
    }
    public function imagenProductoCodigo(string $codigo)
    {
        $producto = Producto::where('codigo', $codigo)->firstOrFail();
        if (!$producto->foto_producto || !Storage::disk('local')->exists($producto->foto_producto)) {
            abort(404);
        }

        return response()->file(storage_path('app/private/' . $producto->foto_producto));
    }
    public function actualizarCantidadProducto(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);
        $request->validate([
            'cantidadStock' => 'required|integer|min:0',
            'detalleCantidadStock' => 'required|string|max:255',
        ], [
            'cantidadStock.required' => 'La cantidad es obligatoria.',
            'cantidadStock.integer' => 'La cantidad debe ser un número entero.',
            'cantidadStock.min' => 'La cantidad no puede ser negativa.',
            'detalleCantidadStock.required' => 'El detalle de la cantidad es obligatorio.',
            'detalleCantidadStock.max' => 'El detalle de la cantidad no puede exceder los 255 caracteres.',
        ]);

        $producto->cantidad = $request->cantidadStock;
        $producto->detalle_cantidad = trim(strtoupper($request->detalleCantidadStock));
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function agregarPromocion(Request $request, string $id)
    {
        $request->validate([
            'descuento' => 'nullable|integer|min:0|max:100|required_without:regalo',
            'regalo' => 'nullable|string|max:255|required_without:descuento',
        ], [
            'descuento.required_without' => 'El campo descuento es requerido si el regalo no está presente.',
            'regalo.required_without' => 'El campo regalo es requerido si el descuento no está presente.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->promocion = true;
        $producto->descripcion_descuento_porcentaje= $request->descuento ?? 0;
        $producto->descripcion_regalo = trim(strtoupper($request->regalo)) ?? null;
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function eliminarPromocion(string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->promocion = false;
        $producto->descripcion_descuento_porcentaje = 0;
        $producto->descripcion_regalo = null;
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function editarPromocion(Request $request, string $id)
    {
        $request->validate([
            'descuento' => 'nullable|integer|min:0|max:100|required_without:regalo',
            'regalo' => 'nullable|string|max:255|required_without:descuento',
        ], [
            'descuento.required_without' => 'El campo descuento es requerido si el regalo no está presente.',
            'regalo.required_without' => 'El campo regalo es requerido si el descuento no está presente.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->descripcion_descuento_porcentaje = $request->descuento ?? 0;
        $producto->descripcion_regalo = trim(strtoupper($request->regalo)) ?? null;
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function editarFotografia(Request $request, string $id)
    {
        $request->validate([
            'foto_producto' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'foto_producto.required' => 'La imagen del producto es obligatoria.',
            'foto_producto.image' => 'El archivo debe ser una imagen.',
            'foto_producto.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg, gif, svg o webp.',
            'foto_producto.max' => 'La imagen no puede exceder los 2MB.',
        ]);

        $producto = Producto::findOrFail($id);

        if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
            Storage::disk('local')->delete($producto->foto_producto);
        }

        $file = $request->file('foto_producto');
        $path = $file->store('foto_producto', 'local');
        $producto->foto_producto = $path;
        $producto->save();

        return response()->json(['file' => $path]);
    }

    public function editarCodigoManual(Request $request, string $id)
    {
        $request->validate([
            'codigo_producto' => 'required|string|max:255|unique:productos,codigo,' . $id,
        ], [
            'codigo_producto.required' => 'El código es obligatorio.',
            'codigo_producto.unique' => 'El código debe ser único.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->codigo = $request->codigo_producto;
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function editarCodigoAutogenerar(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);
        $prefijo = "PROD-";
        do {
            $codigo_numerico = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $codigo_unico = $prefijo . $codigo_numerico;
        } while (Producto::where('codigo', $codigo_unico)->exists());

        $producto->codigo = $codigo_unico;
        $producto->save();

        return response()->json(['codigo' => $codigo_unico]);
    }

    public function editarNombre(Request $request, string $id)
    {
        $request->validate([
            'nombre_producto' => 'required|string|max:255',
        ], [
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->nombre_producto = trim(strtoupper($request->nombre_producto));
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function editarProveedorMarcaLinea(Request $request, string $id)
    {
        $request->validate([
            'id_proveedor' => 'required|exists:proveedors,id',
            'id_marca' => 'required|exists:marcas,id',
            'id_linea' => 'required|exists:lineas,id',
        ], [
            'id_proveedor.required' => 'El proveedor es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_linea.required' => 'La línea es obligatoria.',
        ]);

        $producto = Producto::findOrFail($id);

        $producto->id_proveedor = $request->id_proveedor;
        $producto->id_marca = $request->id_marca;
        $producto->id_linea = $request->id_linea;
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function updateDescripcion(Request $request, string $id)
    {
        $request->validate([
            'descripcion_producto' => 'required|string|max:1000',
        ], [
            'descripcion_producto.required' => 'La descripción del producto es obligatoria.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->descripcion_producto = trim(strtoupper($request->descripcion_producto));
        $producto->save();

        return response()->json(['success' => true]);
    }
    public function updatePrecioCompraProducto(Request $request, string $id)
    {
        $request->validate([
            'precio_compra_producto' => 'required|numeric|min:0',
        ], [
            'precio_compra_producto.required' => 'El precio de compra es obligatorio.',
            'precio_compra_producto.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra_producto.min' => 'El precio de compra no puede ser negativo.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->precio_compra = str_replace(',', '.', $request->precio_compra_producto);
        $producto->save();

        return response()->json(['success' => true]);
    }
    public function updatePrecioDescripcionProducto(Request $request, string $id)
    {
        $request->validate([
            'descripcion_precio_compra_producto' => 'required|string|max:255',
        ], [
            'descripcion_precio_compra_producto.required' => 'La descripción del precio es obligatoria.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->detalle_precio_compra = trim(strtoupper($request->descripcion_precio_compra_producto));
        $producto->save();

        return response()->json(['success' => true]);
    }
    public function updatePresentacionProducto(Request $request, string $id)
    {
        $request->validate([
            'presentacion_producto' => 'nullable|string|max:255',
        ], [
            'presentacion_producto.max' => 'La presentación no puede exceder los 255 caracteres.',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->presentacion = trim(strtoupper($request->presentacion_producto));
        $producto->save();

        return response()->json(['success' => true]);
    }

    public function darBaja(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado_de_baja = !$producto->estado_de_baja;
        $producto->save();

        return response()->json(['success' => true]);
    }
}
