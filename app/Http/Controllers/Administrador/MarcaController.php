<?php

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

        $descripcion = $this->normalizarTexto($request->descripcion);
        $existe = Marca::where('id_proveedor', $request->proveedor_id)
            ->whereRaw('UPPER(descripcion) = ?', [$descripcion])
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'La marca ya existe para este proveedor.'], 422);
        }

        Marca::create([
            'descripcion' => $descripcion,
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
        $descripcion = $this->normalizarTexto($request->descripcion);
        $existe = Marca::where('id_proveedor', $marca->id_proveedor)
            ->whereRaw('UPPER(descripcion) = ?', [$descripcion])
            ->where('id', '!=', $marca->id)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'La marca ya existe para este proveedor.'], 422);
        }

        $marca->update([
            'descripcion' => $descripcion,
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
        $existe = Marca::where('id_proveedor', $request->proveedor_id)
            ->whereRaw('UPPER(descripcion) = ?', [$marca->descripcion])
            ->where('id', '!=', $marca->id)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ese proveedor ya tiene una marca con el mismo nombre.'], 422);
        }

        $marca->update(['id_proveedor' => $request->proveedor_id]);

        return response()->json([
            'success' => true,
        ], 200);

    }

    private function normalizarTexto(string $texto): string
    {
        return trim(strtoupper(preg_replace('/\s+/', ' ', $texto)));
    }
}
