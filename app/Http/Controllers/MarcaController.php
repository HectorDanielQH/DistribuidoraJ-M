<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
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

        return redirect()->route('proveedores.index')->with('success', 'Marca created successfully.');
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

        return redirect()->route('proveedores.index')->with('success', 'Marca updated successfully.');          
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $linea)
    {
        $marca = Marca::findOrFail($linea);
        $marca->delete();
        return redirect()->route('proveedores.index');
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

        return redirect()->route('proveedores.index')->with('success', 'Marca moved successfully.');
    }
}