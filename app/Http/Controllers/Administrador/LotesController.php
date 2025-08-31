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
            $query = Lotes::query();
            return $dataTables->eloquent($query)
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
        $lotes=Lotes::all();
        return view('administrador.lotes.index', compact('lotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lotes=Lotes::all();
        return view('administrador.lotes.create', compact('lotes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lote' => 'nullable',
            'producto_id' => 'required|exists:productos,id',
            'cantidadProducto' => 'required|integer|min:1',
            'descripcionCantidad' => 'nullable|string|max:255',
            'precioCompra' => 'required|numeric|min:0',
            'descripcionCompra' => 'nullable|string',
            'vencimientoProducto' => 'nullable|date|after_or_equal:ingreso_lote',
        ]);

        if(isset($request->lote)) {
            Lotes::create([
                'codigo_lote' => Lotes::find($request->lote)->codigo_lote,
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidadProducto,
                'detalle_cantidad' => $request->descripcionCantidad ? trim(strtoupper($request->descripcionCantidad)) : null,
                'precio_ingreso' => $request->precioCompra,
                'detalle_precio_ingreso' => $request->descripcionCompra ? trim(strtoupper($request->descripcionCompra)) : null,
                'ingreso_lote' => NOW(),
                'fecha_vencimiento' => $request->vencimientoProducto ? $request->vencimientoProducto : null,
            ]);
        }
        else{
            Lotes::create([
                'codigo_lote' => Lotes::max('codigo_lote') ? 'LT'.str_pad((int)substr(Lotes::max('codigo_lote'), 2) + 1, 6, '0', STR_PAD_LEFT) : 'LT000001',
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidadProducto,
                'detalle_cantidad' => $request->descripcionCantidad ? trim(strtoupper($request->descripcionCantidad)) : null,
                'precio_ingreso' => $request->precioCompra,
                'detalle_precio_ingreso' => $request->descripcionCompra ? trim(strtoupper($request->descripcionCompra)) : null,
                'ingreso_lote' => NOW(),
                'fecha_vencimiento' => $request->vencimientoProducto ? $request->vencimientoProducto : null,
            ]);
        }
        $productos=Producto::find($request->producto_id);
        $productos->cantidad += $request->cantidadProducto;
        $productos->detalle_cantidad = $request->descripcionCantidad ? trim(strtoupper($request->descripcionCantidad)) : $productos->detalle_cantidad;
        $productos->precio_compra = $request->precioCompra;
        $productos->detalle_precio_compra = $request->descripcionCompra ? trim(strtoupper($request->descripcionCompra)) : $productos->detalle_precio_compra;
        $productos->save();

        return response()->json(['success' => 'Lote registrado correctamente']);
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
        $search = $request->get('query'); // <-- aquí recuperas lo que escribe el usuario

        // Busca por nombre o código
        $productos = Producto::where('nombre_producto', 'LIKE', "%{$search}%")
            ->orWhere('codigo', 'LIKE', "%{$search}%")
            ->get();

        // Devuelve JSON para Select2
        return response()->json($productos);
    }
}
