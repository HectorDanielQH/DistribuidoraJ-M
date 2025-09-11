<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\FormaVenta;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax())
        {
            $query = Producto::query();

            return $dataTables->eloquent($query)
                ->filterColumn('codigo', function ($query, $keyword) {
                    $query->where('codigo', 'like', "%{$keyword}%");
                })
                ->addColumn('imagen', function ($producto){
                    if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
                        return '<img src="' . route('productos.imagen', ['id' => $producto->id]) . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                    }
                    return '<img src="' . asset('images/logo_color.webp') . '" class="img-thumbnail" style="width: 80px; height: 80px;">';
                })
                ->filterColumn('nombre_producto', function ($query, $keyword) {
                    $keyword = trim(strtoupper($keyword));
                    $query->where('nombre_producto', 'like', "%".$keyword."%");
                })
                ->addColumn('marca', function ($producto) {
                    return $producto->marca ? $producto->marca->descripcion : 'Sin Marca';
                })
                ->addColumn('stock', function ($producto) {
                    $claseCantidad = $producto->cantidad <= 15 ? 'badge bg-danger' : 'badge bg-success';
                    $cantidadHtml = '<span class="' . $claseCantidad . ' fs-6">' . $producto->cantidad . ' ' . $producto->detalle_cantidad . '</span>';

                    $boton = '
                        <div class="mt-2 d-flex justify-content-center">
                            <button class="btn btn-sm btn-outline-primary" type="button"
                                onclick="editarCantidadProductoStock(this)"
                                id-cantidad-stock="' . $producto->id . '" 
                                cantidad-stock="' . $producto->cantidad . '" 
                                detalle-cantidad-stock="' . $producto->detalle_cantidad . '"
                            >
                                <i class="fas fa-edit me-1"></i> Editar
                            </button>
                        </div>
                    ';

                    return '<div class="w-full d-flex flex-column justify-content-center align-items-center">' . $cantidadHtml . $boton . '</div>';
                })
                ->addColumn('formas_venta', function ($producto) {
                    $formasVenta = FormaVenta::where('id_producto', $producto->id)->get();

                    if ($formasVenta->isEmpty()) {
                        $output = '<div class="d-flex flex-column">';
                        $output.='
                            <button class="btn btn-sm btn-success mb-2" type="button" id-producto="' . $producto->id . '" onclick="agregarFormasVenta(this)">
                                <i class="fas fa-plus"></i> Agregar Forma de Venta
                            </button>';
                        $output .= '</div>';
                        $output .= '<span class="text-muted"><i class="fas fa-ban"></i> Sin Formas de Venta</span>';
                        return $output;
                    }

                    $output = '<div class="d-flex flex-column">';
                    $output.='
                        <button class="btn btn-sm btn-success mb-2" type="button" id-producto="' . $producto->id . '" onclick="agregarFormasVenta(this)">
                            <i class="fas fa-plus"></i> Agregar Forma de Venta
                        </button>
                    ';
                    $output .= '
                        <button class="btn btn-sm btn-warning mb-2" type="button" data-toggle="modal" data-target="#formas-venta-producto" id-producto="' . $producto->id . '" onclick="verFormasVenta(this)">
                            <i class="fas fa-edit"></i> Editar Formas de Venta
                        </button>
                    ';

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
                })
                ->addColumn('promocion_vista', function($producto){
                    $descuento = ($producto->descripcion_descuento_porcentaje !== null && $producto->descripcion_descuento_porcentaje !== '')
                        ? $producto->descripcion_descuento_porcentaje . '%'
                        : 'N/A';

                    $regalo = ($producto->descripcion_regalo !== null && $producto->descripcion_regalo !== '' )
                        ? $producto->descripcion_regalo
                        : 'N/A';

                    $id = (int) $producto->id;

                    $boxOpen  = '<div class="text-center"><div class="d-inline-block bg-light border rounded px-3 py-2 shadow-sm">';
                    $boxClose = '</div></div>';

                    if ($producto->promocion) {
                        return $boxOpen . '
                            <div class="mb-2">
                                <span class="badge bg-success me-1"><i class="fas fa-percent me-1"></i>'.$descuento.'</span>
                                <span class="badge bg-info"><i class="fas fa-gift me-1"></i>'.$regalo.'</span>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones promoción">
                                <button class="btn btn-warning"
                                        type="button"
                                        title="Editar"
                                        id-producto="'.$id.'"
                                        editar-promocion-procentaje="'.$producto->descripcion_descuento_porcentaje.'"
                                        editar-promocion-regalo="'.$producto->descripcion_regalo.'"
                                        onclick="editarPromocion(this)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger"
                                        type="button"
                                        title="Eliminar"
                                        id-producto="'.$id.'"
                                        onclick="eliminarPromocion(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        ' . $boxClose;
                    }

                    // Sin promoción
                    return $boxOpen . '
                        <button class="btn btn-success btn-sm mb-1"
                                type="button"
                                id-producto="'.$id.'"
                                onclick="agregarPromocion(this)">
                            <i class="fas fa-plus me-1"></i> Agregar promoción
                        </button>
                        <div><span class="badge bg-secondary">Sin promoción</span></div>
                    ' . $boxClose;
                })
                ->addColumn('acciones', function ($producto) {
                    $route=route('administrador.productos.edit', ['producto' => $producto->id]);
                    $acciones = '<div class="btn-group" role="group">';
                    $acciones .= "<a href='". $route ."' class='btn btn-warning' title='Editar Producto'>
                        <i class='fas fa-edit'></i>
                    </a>";
                    if($producto->estado_de_baja) {
                        $acciones .= '<button type="button" class="btn btn-secondary" onclick="ProductoDeAlta(this)" id-producto="' . $producto->id . '">
                            <i class="fas fa-eye-slash"></i>
                        </button>';
                    } else {
                        $acciones .= '<button type="button" class="btn btn-info" onclick="ProductoDeBaja(this)" id-producto="' . $producto->id . '">
                            <i class="fas fa-eye"></i>
                        </button>';
                    }
                    $acciones .= '<button type="button" class="btn btn-danger" id-producto="' . $producto->id . '" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>';
                    $acciones .= '</div>';
                    return $acciones;
                })
                ->rawColumns(['acciones', 'imagen', 'formas_venta', 'promocion_vista', 'stock'])
                ->make(true);
        }

        $proveedores = Proveedor::all();
        $contar_productos_menores = Producto::where('cantidad', '<=', 15)->where('estado_de_baja',false)->count();

        return view('administrador.productos.index_productos',compact('proveedores', 'contar_productos_menores'));
    }

    function obtenerProductosBajoStock(Request $request,DataTables $datatables)
    {
        if($request->ajax()){
            $productos = Producto::query('cantidad', '<=', 15)->where('estado_de_baja', false)->orderBy('cantidad', 'asc');
            return $datatables->eloquent($productos)
                ->addColumn('codigo', function ($producto) {
                    return $producto->codigo;
                })
                ->filterColumn('codigo', function ($query, $keyword) {
                    $query->where('codigo', 'like', "%{$keyword}%");
                })
                ->addColumn('nombre_producto', function ($producto) {
                    return $producto->nombre_producto;
                })
                ->filterColumn('nombre_producto', function ($query, $keyword) {
                    $keyword = trim(strtoupper($keyword));
                    $query->where('nombre_producto', 'like', "%{$keyword}%");
                })
                ->addColumn('stock', function ($producto) {
                    return $producto->cantidad . ' ' . $producto->detalle_cantidad;
                })
                ->make(true);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::all();
        return view('administrador.productos.create_productos',compact('proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'proveedor' => 'required|exists:proveedors,id',
            'marca_producto' => 'required|exists:marcas,id',
            'linea_producto' => 'required|exists:lineas,id',
            'codigo_producto' => 'required|string|max:255|unique:productos,codigo',
            'nombre_producto' => 'required|string|max:255',
            'descripcion_producto' => 'required|string|max:1000',
            'cantidad' => 'required|integer|min:0',
            'detalle_cantidad' => 'required|string|max:255',
            'precio_compra' => 'required|numeric|min:0',
            'detalle_precio_compra' => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'presentacion' => 'nullable|string|max:255',
            'promocion' => 'nullable',
            'descuento_porcentaje' => 'nullable|integer|min:0|max:100',
            'descuento_promocion' => 'nullable|string|max:255',
            'imagen_producto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'nombre_forma_venta' => 'required|array|min:1',
            'precio_forma_venta' => 'required|array|min:1',
            'equivalencia' => 'required|array|min:1',
        ],[
            'proveedor.required' => 'El proveedor es obligatorio.',
            'marca_producto.required' => 'La marca es obligatoria.',
            'linea_producto.required' => 'La línea es obligatoria.',
            'codigo_producto.required' => 'El código es obligatorio.',
            'codigo_producto.unique' => 'El código debe ser único.',
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'descripcion_producto'=> 'La descripción del producto es obligatoria.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'detalle_cantidad.required' => 'La descripción de la cantidad es obligatoria.',
            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'detalle_precio_compra.required' => 'La descripción del precio de compra es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es una fecha válida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser una fecha futura.',
            'presentacion.max' => 'La presentación no puede exceder los 255 caracteres.',
            'descuento_porcentaje.integer' => 'El descuento de la promoción debe ser un número entero.',
            'descuento_porcentaje.min' => 'El descuento de la promoción no puede ser negativo.',
            'descuento_porcentaje.max' => 'El descuento de la promoción no puede ser mayor a 100.',
            'descuento_promocion.max' => 'La descripción del regalo de la promoción no puede exceder los 255 caracteres.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg, gif, svg o webp.',
            'imagen_producto.max' => 'La imagen no puede exceder los 2MB.',
            'nombre_forma_venta.required' => 'La forma de venta es obligatoria.',
            'nombre_forma_venta.array' => 'La forma de venta debe ser un arreglo.',
            'nombre_forma_venta.min' => 'Debe haber al menos una forma de venta.',
            'precio_forma_venta.required' => 'La cantidad de venta es obligatoria.',
            'precio_forma_venta.array' => 'La cantidad de venta debe ser un arreglo.',
            'precio_forma_venta.min' => 'Debe haber al menos una cantidad de venta.',
            'equivalencia.required' => 'La equivalencia de stock es obligatoria.',
            'equivalencia.array' => 'La equivalencia de stock debe ser un arreglo.',
            'equivalencia.min' => 'Debe haber al menos una equivalencia de stock.',
        ]);

        if($request->hasFile('imagen_producto')) {
            $file = $request->file('imagen_producto');
            $path=$file->store('foto_producto','local');
            $producto = Producto::create([
                'id_proveedor' => $request->proveedor,
                'id_marca' => $request->marca_producto,
                'id_linea' => $request->linea_producto,
                'codigo' => $request->codigo_producto,
                'nombre_producto' => trim(strtoupper($request->nombre_producto)),
                'descripcion_producto' => trim(strtoupper($request->descripcion_producto)),
                'cantidad' => $request->cantidad,
                'detalle_cantidad' => trim(strtoupper($request->detalle_cantidad)),
                'precio_compra' => str_replace(',', '.', $request->precio_compra),
                'detalle_precio_compra' => trim(strtoupper($request->detalle_precio_compra)),
                'fecha_vencimiento' => $request->fecha_vencimiento ?? null,
                'presentacion' => trim(strtoupper($request->presentacion)) ?? null,
                'promocion' => $request->promocion ? true : false,
                'descripcion_descuento_porcentaje' => $request->descripcion_descuento_porcentaje ?? 0,
                'descuento_promocion' => trim(strtoupper($request->descuento_promocion)) ?? null,
                'foto_producto' => $path
            ]);   
        }
        else {
            $producto = Producto::create([
                'id_proveedor' => $request->proveedor,
                'id_marca' => $request->marca_producto,
                'id_linea' => $request->linea_producto,
                'codigo' => $request->codigo_producto,
                'nombre_producto' => trim(strtoupper($request->nombre_producto)),
                'descripcion_producto' => trim(strtoupper($request->descripcion_producto)),
                'cantidad' => $request->cantidad,
                'detalle_cantidad' => trim(strtoupper($request->detalle_cantidad)),
                'precio_compra' => str_replace(',', '.', $request->precio_compra),
                'detalle_precio_compra' => trim(strtoupper($request->detalle_precio_compra)),
                'fecha_vencimiento' => $request->fecha_vencimiento ?? null,
                'presentacion' => trim(strtoupper($request->presentacion)) ?? null,
                'promocion' => $request->promocion ? true : false,
                'descripcion_descuento_porcentaje' => $request->descuento_porcentaje ?? 0,
                'descuento_promocion' => trim(strtoupper($request->descuento_promocion)) ?? null,
            ]);
        }

        foreach ($request->nombre_forma_venta as $key => $nombreFormaVenta) {
            FormaVenta::create([
                'tipo_venta' => trim(strtoupper($nombreFormaVenta)),
                'precio_venta' => str_replace(',', '.', $request->precio_forma_venta[$key]),
                'equivalencia_cantidad' => $request->equivalencia[$key],
                'id_producto' => $producto->id
            ]);
        }

        return redirect()->route('administrador.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_producto)
    {
        $producto = Producto::where('id', $id_producto)->with(['proveedor', 'marca', 'linea'])->firstOrFail();
        return view('administrador.productos.show_producto', compact('producto'));
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
        $producto = Producto::findOrFail($producto);
        $proveedores=Proveedor::all();
        $marcas=Marca::all();
        $lineas=Linea::all();
        return view('administrador.productos.edit_productos', compact('producto','proveedores','marcas','lineas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $producto)
    {
        $request->validate([
            'proveedor' => 'required|exists:proveedors,id',
            'marca_producto' => 'required|exists:marcas,id',
            'linea_producto' => 'required|exists:lineas,id',
            'codigo_producto' => 'required|string|max:255|unique:productos,codigo,'.$producto,
            'nombre_producto' => 'required|string|max:255',
            'descripcion_producto' => 'required|string|max:1000',
            'cantidad' => 'required|integer|min:0',
            'detalle_cantidad' => 'required|string|max:255',
            'precio_compra' => 'required|numeric|min:0',
            'detalle_precio_compra' => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'presentacion' => 'nullable|string|max:255',
            'promocion' => 'nullable',
            'descuento_porcentaje' => 'nullable|integer|min:0|max:100',
            'descuento_promocion' => 'nullable|string|max:255',
            'imagen_producto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ],[
            'proveedor.required' => 'El proveedor es obligatorio.',
            'marca_producto.required' => 'La marca es obligatoria.',
            'linea_producto.required' => 'La línea es obligatoria.',
            'codigo_producto.required' => 'El código es obligatorio.',
            'codigo_producto.unique' => 'El código debe ser único.',
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'descripcion_producto'=> 'La descripción del producto es obligatoria.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'detalle_cantidad.required' => 'La descripción de la cantidad es obligatoria.',
            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'detalle_precio_compra.required' => 'La descripción del precio de compra es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es una fecha válida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser una fecha futura.',
            'presentacion.max' => 'La presentación no puede exceder los 255 caracteres.',
            'descuento_porcentaje.integer' => 'El descuento de la promoción debe ser un número entero.',
            'descuento_porcentaje.min' => 'El descuento de la promoción no puede ser negativo.',
            'descuento_porcentaje.max' => 'El descuento de la promoción no puede ser mayor a 100.',
            'descuento_promocion.max' => 'La descripción del regalo de la promoción no puede exceder los 255 caracteres.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg, gif, svg o webp.',
            'imagen_producto.max' => 'La imagen no puede exceder los 2MB.',
        ]);

        $producto = Producto::findOrFail($producto);
        $producto->id_proveedor = $request->proveedor;
        $producto->id_marca = $request->marca_producto;
        $producto->id_linea = $request->linea_producto;
        $producto->codigo = $request->codigo_producto;
        $producto->nombre_producto = trim(strtoupper($request->nombre_producto));
        $producto->descripcion_producto = trim(strtoupper($request->descripcion_producto));
        $producto->cantidad = $request->cantidad;
        $producto->detalle_cantidad = trim(strtoupper($request->detalle_cantidad));
        $producto->precio_compra = str_replace(',', '.', $request->precio_compra);
        $producto->detalle_precio_compra = trim(strtoupper($request->detalle_precio_compra));
        $producto->fecha_vencimiento = $request->fecha_vencimiento ?? null;
        $producto->presentacion = trim(strtoupper($request->presentacion)) ?? null;
        $producto->promocion = $request->promocion ? true : false;
        $producto->descripcion_descuento_porcentaje = $request->descuento_porcentaje ?? 0;
        $producto->descripcion_regalo = trim(strtoupper($request->descuento_promocion)) ?? null;
        if($request->hasFile('imagen_producto')) {
            if ($producto->foto_producto && Storage::disk('local')->exists($producto->foto_producto)) {
                Storage::disk('local')->delete($producto->foto_producto);
            }
            $file = $request->file('imagen_producto');
            $path=$file->store('foto_producto','local');
            $producto->foto_producto = $path;
        }
        $producto->save();

        return redirect()->route('administrador.productos.index');
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

    public function darBaja(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado_de_baja = !$producto->estado_de_baja;
        $producto->save();

        return response()->json(['success' => true]);
    }


    public function obtenerProductoPorNombre(Request $request)
    {
        $search = strtoupper($request->q); // Select2 manda el término en "q"
        $perPage = 10; // cuántos productos por página
        $page = $request->page ?? 1;

        $query = Producto::query()
            ->where('estado_de_baja', false);

        if (!empty($search)) {
            $query->whereRaw('UPPER(nombre_producto) ILIKE ?', ['%' . $search . '%'])
                ->orWhereRaw('UPPER(codigo) ILIKE ?', ['%' . $search . '%']);
        }

        $productos = $query->paginate($perPage, ['*'], 'page', $page);

        // Formato compatible con Select2
        $results = $productos->items();

        return response()->json([
            'items' => $results,
            'total_count' => $productos->total(),
            'per_page' => $productos->perPage(),
            'current_page' => $productos->currentPage(),
            'has_more' => $productos->hasMorePages(),
        ]);
    }

    public function obtenerProductoPorId(string $codigo)
    {
        $producto = Producto::findOrFail($codigo);
        return response()->json($producto);
    }

    public function obtenerProductosParaEdicion(Request $request){
        $term = trim($request->get('term'));
        $productos = Producto::where('estado_de_baja', false)
            ->where(function($query) use ($term) {
                $query->where('nombre_producto', 'ILIKE', '%' . strtoupper($term) . '%')
                      ->orWhere('codigo', 'ILIKE', '%' . strtoupper($term) . '%');
            })
            ->limit(10)
            ->get();

        return response()->json($productos);
    }
}
