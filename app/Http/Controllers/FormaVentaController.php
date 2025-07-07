<?php

namespace App\Http\Controllers;

use App\Models\FormaVenta;
use Illuminate\Http\Request;

class FormaVentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    public function editarVisualizacion(Request $request, $id)
    {
        $formaVenta = FormaVenta::findOrFail($id);
        $formaVenta->activo = !$formaVenta->activo;
        $formaVenta->save();

        return response()->json([
            'id_producto' => $formaVenta->id_producto,
        ]);
    }
}
