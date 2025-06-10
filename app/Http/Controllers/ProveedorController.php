<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Si se proporcionó un nombre, filtrar la búsqueda (sin modificar la base de datos)
        if ($request->filled('nombre')) {
            $nombreBuscado = strtoupper(trim($request->nombre));
            $query->whereRaw('UPPER(nombre_proveedor) LIKE ?', ["%{$nombreBuscado}%"]);
        }

        // Obtener valor del campo para reenviarlo a la vista
        $nombre = $request->nombre;

        // Paginación de resultados
        $proveedores = $query->paginate(10);

        return view('administrador.proveedores.index_proveedores', compact('proveedores', 'nombre'))
            ->with('eliminar_busqueda', $request->filled('nombre'));
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
            'nombreproveedor'=> 'required|string|max:255|unique:proveedors,nombre_proveedor',
            'opciones' => 'required|array'
        ],[
            'nombreproveedor.required' => 'El nombre del proveedor es obligatorio.',
            'nombreproveedor.string' => 'El nombre del proveedor debe ser una cadena de texto.',
            'nombreproveedor.unique' => 'El nombre del proveedor ya existe.',
            'nombreproveedor.max' => 'El nombre del proveedor no puede exceder los 255 caracteres.',
            'opciones.required' => 'Debe seleccionar al menos una opción.',
            'opciones.array' => 'Las opciones deben ser un arreglo.'
        ]);

        $proveedor=Proveedor::create([
            'nombre_proveedor' => trim(strtoupper($request->nombreproveedor)),
        ]);

        foreach ($request->opciones as $opcion) {
            Marca::create([
                'descripcion' => trim(strtoupper($opcion)),
                'id_proveedor' => $proveedor->id,
            ]);
        }

        return response()->json([
            'success' => true,
        ],200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $proveedor)
    {
        return response()->json([
            'usuario'=>Proveedor::find($proveedor),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proveedor $proveedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $proveedor)
    {
        $request->validate([
            'nombreproveedor'=> 'required|string|max:255',
        ],[
            'nombreproveedor.required' => 'El nombre del proveedor es obligatorio.',
            'nombreproveedor.string' => 'El nombre del proveedor debe ser una cadena de texto.',
            'nombreproveedor.unique' => 'El nombre del proveedor ya existe.',
            'nombreproveedor.max' => 'El nombre del proveedor no puede exceder los 255 caracteres.',
        ]);

        $existe = Proveedor::where('nombre_proveedor', $request->nombreproveedor)
                ->where('id', '!=', $proveedor)
                ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El nombre del proveedor ya existe.'
            ], 422);
        }

        $proveedor = Proveedor::find($proveedor);

        $proveedor->update([
            'nombre_proveedor' => $request->nombreproveedor
        ]);

        return response()->json(['mensaje' => $request->opciones]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $proveedor)
    {
        $proveedor = Proveedor::find($proveedor);

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado.'], 404);
        }

        $proveedor->delete();
        return response()->json(['mensaje' => 'Proveedor y sus marcas eliminados correctamente.']);
    }
}
