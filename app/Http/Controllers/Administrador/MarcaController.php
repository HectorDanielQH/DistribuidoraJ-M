<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index(Request $request)
    {
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
            'descripcion' => 'required|string|max:255',
            'proveedor_id' => 'required|exists:proveedors,id',
        ], [
            'descripcion.required' => 'La descripción de la marca es obligatoria.',
            'descripcion.string' => 'La descripción de la marca debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción de la marca no puede exceder los 255 caracteres.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.'
        ]);

        Marca::create([
            'descripcion' => trim(strtoupper($request->descripcion)),
            'id_proveedor' => $request->proveedor_id,
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_proveedor)
    {
        $marcas = Marca::where('id_proveedor',$id_proveedor)->get();
        return response()->json($marcas);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $linea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $linea)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
        ], [
            'descripcion.required' => 'La descripción de la marca es obligatoria.',
            'descripcion.string' => 'La descripción de la marca debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción de la marca no puede exceder los 255 caracteres.',
        ]);

        $marca = Marca::findOrFail($linea);
        $marca->update([
            'descripcion' => trim(strtoupper($request->descripcion)),
        ]);

        return response()->json([
            'success' => true,
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $linea)
    {
        $marca = Marca::findOrFail($linea);
        $marca->delete();

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function mover(Request $request, string $marca)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedors,id',
        ], [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.'
        ]);

        $marca = Marca::findOrFail($marca);
        $marca->update(['id_proveedor' => $request->proveedor_id]);

        return response()->json([
            'success' => true,
        ], 200);

    }
}