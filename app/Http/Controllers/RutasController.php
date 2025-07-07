<?php

namespace App\Http\Controllers;

use App\Models\Rutas;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RutasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTable)
    {
        if($request->ajax()){
            $query = Rutas::query()->select('id', 'nombre_ruta');
            return $dataTable->eloquent($query)
                ->addColumn('action', function ($ruta) {
                    return '<div class="btn-group mx-auto d-flex justify-content-center align-items-center" role="group">
                                <button class="btn btn-danger btn-sm delete-route" data-id="'.$ruta->id.'" onclick="eliminarRutas(this)">
                                    <i class="fas fa-trash"></i>         
                                </button>
                                <button class="btn btn-warning btn-sm edit-route" data-id="'.$ruta->id.'" data-nombre="'. $ruta->nombre_ruta .'" onclick="editarRutas(this)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('administrador.rutas.index');
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
            'nueva_ruta' => 'required|string|max:255',
        ]);

        Rutas::create([
            'nombre_ruta' => trim(strtoupper($request->nueva_ruta)),
        ]);

        return response()->json(['message' => 'Ruta creada exitosamente.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rutas $rutas)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rutas $rutas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $rutas)
    {
        $request->validate([
            'nombre_ruta' => 'required|string|unique:rutas,nombre_ruta,'.$rutas.'',
        ],[
            'nombre_ruta.required' => 'El nombre de la ruta es obligatorio.',
            'nombre_ruta.unique' => 'El nombre de la ruta ya existe.',
            'nombre_ruta.string' => 'El nombre de la ruta debe ser una cadena de texto.',
        ]);

        try{
            $ruta = Rutas::findOrFail($rutas);
            $ruta->nombre_ruta = trim(strtoupper($request->nombre_ruta));
            $ruta->save();

            return response()->json(['message' => 'Ruta actualizada exitosamente.'], 200);
        }

        catch(\Exception $e){
            return response()->json(['message' => 'Error al actualizar la ruta es posible que ya exista'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $rutas)
    {
        $ruta = Rutas::findOrFail($rutas);
        $ruta->delete();
        return response()->json(['message' => 'Ruta eliminada exitosamente.'], 200);
    }
}
