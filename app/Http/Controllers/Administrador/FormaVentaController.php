<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\FormaVenta;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FormaVentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTable, string $id_producto)
    {
        $formasVenta = FormaVenta::where('id_producto', $id_producto)->get();

        return $dataTable->of($formasVenta)
            ->editColumn('precio_venta', function ($formaVenta) {
                return $formaVenta->precio_venta. ' Bs.-';
            })
            ->addColumn('conversion_stock', function ($formaVenta) {
                return $formaVenta->equivalencia_cantidad . ' ' . $formaVenta->producto->detalle_cantidad;
            })
            ->addColumn('acciones', function ($formaVenta) {
                $acciones = '<div class="btn-group" role="group">';
                $acciones .= '<button class="btn btn-sm btn-danger" onclick="eliminarFormaVenta(' . $formaVenta->id . ')"><i class="fas fa-trash"></i></button>';
                $acciones .= '<button class="btn btn-sm btn-warning" onclick="editarFormaVenta(' . $formaVenta->id . ')"><i class="fas fa-edit"></i></button>';
                $acciones .= $formaVenta->activo?'<button class="btn btn-sm btn-info" id-visualizacion="' . $formaVenta->id . '" onclick="editarVisualizacion(this)"><i class="fas fa-eye"></i></button>': '<button class="btn btn-sm btn-secondary" id-visualizacion="' . $formaVenta->id . '" onclick="editarVisualizacion(this)"><i class="fas fa-eye-slash"></i></button>';
                $acciones .= '</div>';
                return $acciones;
            })
            ->rawColumns(['acciones'])
            ->make(true);
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
            'tipo_venta' => 'required|string|max:255',
            'precio_venta' => 'required|numeric',
            'equivalencia_cantidad' => 'required|numeric',
            'id_producto' => 'required|exists:productos,id',
        ]);

        $formaVenta = FormaVenta::create($request->all());

        $formaVentaActualizada = FormaVenta::where('id_producto', $formaVenta->id_producto)->get();
        return response()->json($formaVentaActualizada);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_producto)
    {
        $formaVenta = FormaVenta::where('id_producto', $id_producto)->get();
        return response()->json($formaVenta);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormaVenta $formaVenta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FormaVenta $formaVenta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id_formaVenta)
    {
        $formaVenta = FormaVenta::findOrFail($id_formaVenta);
        $formaVenta->delete();
        return response()->json([
            'id_producto' =>$formaVenta->id_producto
        ]);
    }

    public function editarVisualizacion(Request $request, string $id)
    {
        $formaVenta = FormaVenta::findOrFail($id);
        $formaVenta->activo = !$formaVenta->activo;
        $formaVenta->save();

        return response()->json([
            'id_producto' => $formaVenta->id_producto,
        ]);
    }

    public function editarStock(Request $request, string $id)
    {
        $request->validate([
            'equivalencia_cantidad' => 'required|numeric|min:1',
        ]);

        $formaVenta = FormaVenta::findOrFail($id);
        $formaVenta->equivalencia_cantidad = $request->equivalencia_cantidad;
        $formaVenta->save();

        return response()->json([
            'id_producto' => $formaVenta->id_producto,
            'mensaje' => 'Stock actualizado correctamente.'
        ]);
    }
}
