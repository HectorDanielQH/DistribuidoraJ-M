<?php

namespace App\Http\Controllers;

use App\Models\Linea;
use App\Models\Marca;
use Illuminate\Http\Request;

class LineaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Marca::query();

        if ($request->filled('nombre')) {
            $query->where('descripcion', 'like', '%' . $request->nombre . '%');
        }

        $marcas = $query->paginate(5);

        $marcas_busqueda= Marca::all();

        return view('administrador.lineas.index_lineas',compact('marcas','marcas_busqueda'))->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('ci'));
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
            'descripcion_linea' => 'required|array',
            'marca_id' => 'required|exists:marcas,id',
        ],[
            'descripcion_linea.required' => 'La descripción de la línea es obligatoria.',
            'descripcion_linea.array' => 'La descripción de la línea debe ser un arreglo.',
            'marca_id.required' => 'Debe seleccionar una marca.',
            'marca_id.exists' => 'La marca seleccionada no existe.'
        ]);

        foreach ($request->descripcion_linea as $descripcion) {
            Linea::create([
                'descripcion_linea' => trim(strtoupper($descripcion)),
                'id_marca' => $request->marca_id,
            ]);
        }

        return redirect()->route('lineas.index')->with('success', 'Linea created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_marca)
    {
        $lineas = Linea::where('id_marca', $id_marca)->get();
        return response()->json($lineas);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Linea $linea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $linea)
    {
        $linea = Linea::findOrFail($linea);

        $request->validate([
            'descripcion_linea' => 'required|string|max:255',
        ],[
            'descripcion_linea.required' => 'La descripción de la línea es obligatoria.',
            'descripcion_linea.string' => 'La descripción de la línea debe ser una cadena de texto.',
            'descripcion_linea.max' => 'La descripción de la línea no puede exceder los 255 caracteres.',
        ]);

        $linea->update([
            'descripcion_linea' => trim(strtoupper($request->descripcion_linea)),
        ]);

        return redirect()->route('lineas.index')->with('success', 'Linea updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $linea)
    {
        $linea = Linea::findOrFail($linea);
        $linea->delete();

        return redirect()->route('lineas.index')->with('success', 'Linea deleted successfully.');
    }
}
