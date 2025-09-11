<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Lotes;
use App\Models\Producto;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LotesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = Lotes::query()->select('codigo_lote')->groupBy('codigo_lote');
            return $dataTables->eloquent($query)
                ->addColumn('producto', function($lote){
                    return $lote->producto ? $lote->producto->nombre_producto : 'N/A';
                })

                ->addColumn('cantidad', function($lote){
                    return $lote->cantidad ? $lote->cantidad : 'N/A';
                })

                ->addColumn('acciones', function($lote){
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                    <button type="button" class="btn btn-primary btn-sm" onclick="verLote(this)"
                        id-lote="' . $lote->id . '"
                        data-toggle="modal" data-target="#modalVerLote"
                    >
                        <i class="fas fa-eye"></i> Ver
                    </button>

                    <button type="button" class="btn btn-warning btn-sm" onclick="editarLote(this)"
                    >
                        <i class="fas fa-edit"></i> editar
                    </button>
                    
                    ';
                    $botones .= '<button type="button" class="btn btn-danger btn-sm" onclick="eliminarLote(this)" id-lote="' . $lote->id . '"><i class="fas fa-trash"></i> Eliminar</button>';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        $lotes=Lotes::all();
        return view('administrador.lotes.index', compact('lotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $lotes=Lotes::query()->where('codigo_lote', $request->lote);

            return $dataTables->eloquent($lotes)
                ->addColumn('producto', function($lote){
                    return $lote->producto ? $lote->producto->nombre_producto : 'N/A';
                })
                ->addColumn('acciones', function($lote){
                    $botones = '<div class="btn-group" role="group">';
                    $botones .= '
                    <button type="button" class="btn btn-primary btn-sm" onclick="verLote(this)"
                        id-lote="' . $lote->id . '"
                        data-toggle="modal" data-target="#modalVerLote"
                    >
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editarLote(this)"
                    >
                        <i class="fas fa-edit"></i> editar
                    </button>
                    ';
                    $botones .= '<button type="button" class="btn btn-danger btn-sm" onclick="eliminarLote(this)" id-lote="' . $lote->id . '"><i class="fas fa-trash"></i> Eliminar</button>';
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        $lote_max=Lotes::max('codigo_lote');
        //incrementar el numero del lote + 1 para el nuevo lote
        if($lote_max){
            $lote_max='LT'.str_pad((int)substr($lote_max, 2) + 1, 6, '0', STR_PAD_LEFT);
        }
        else{
            $lote_max='LT000001';
        }
        return view('administrador.lotes.create', compact('lote_max'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo_lote' => 'required|string|max:255',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_producto' => 'required|integer|min:1',
            'descripcion_cantidad' => 'required|string|max:255',
            'precio_compra' => 'required|numeric|min:0',
            'descripcion_precio_compra' => 'required|string|max:255',
            'vencimiento_producto' => 'nullable|date|after_or_equal:ingreso_lote',
        ], [
            'codigo_lote.required' => 'El código del lote es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no es válido.',
            'cantidad_producto.min' => 'La cantidad debe ser al menos 1.',
            'descripcion_cantidad.required' => 'La descripción de la cantidad es obligatoria.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'descripcion_precio_compra.required' => 'La descripción del precio de compra es obligatoria.',
            'vencimiento_producto.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de ingreso del lote.',
        ]);
        Lotes::create([
            'codigo_lote' => $request->codigo_lote,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad_producto,
            'detalle_cantidad' => $request->descripcion_cantidad? trim(strtoupper($request->descripcion_cantidad)) : null,
            'precio_ingreso' => $request->precio_compra? str_replace(',', '', $request->precio_compra) : 0,
            'detalle_precio_ingreso' => $request->descripcion_precio_compra? trim(strtoupper($request->descripcion_precio_compra)) : null,
            'ingreso_lote' => now(),
            'fecha_vencimiento' => $request->vencimiento_producto? $request->vencimiento_producto : null,
        ]);

        $producto = Producto::find($request->producto_id);
        $producto->cantidad += $request->cantidad_producto;
        $producto->detalle_cantidad = $request->descripcion_cantidad;
        $producto->precio_compra = $request->precio_compra? str_replace(',', '', $request->precio_compra) : 0;
        $producto->detalle_precio_compra = $request->descripcion_precio_compra? trim(strtoupper($request->descripcion_precio_compra)) : null;
        $producto->fecha_vencimiento = $request->vencimiento_producto? $request->vencimiento_producto : null;
        $producto->save();


        return response()->json(['success' => true, 'message' => 'Lote creado exitosamente.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function obtenerProducto(Request $request)
    {
        $search = trim(strtoupper($request->get('query'))); 
        
        $productos = Producto::where('nombre_producto', 'LIKE', "%{$search}%")
            ->orWhere('codigo', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get();

        // Devuelve JSON para Select2
        return response()->json($productos);
    }

    public function obtenerDetalleProducto($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            return response()->json([
                'success' => true,
                'detalle_cantidad' => $producto->detalle_cantidad,
                'precio_compra' => $producto->precio_compra,
                'detalle_precio_compra' => $producto->detalle_precio_compra,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }
    }
}
