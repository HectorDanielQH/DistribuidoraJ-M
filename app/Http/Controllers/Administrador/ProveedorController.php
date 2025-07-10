<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = Proveedor::query();
            if ($request->filled('proveedor')) {
                $nombreBuscado = strtoupper(trim($request->proveedor));
                $query->whereRaw('UPPER(nombre_proveedor) LIKE ?', ["%{$nombreBuscado}%"]);
            }
            return $dataTables->eloquent($query)
                ->addColumn('nombre_proveedor', function ($proveedor) {
                    return $proveedor->nombre_proveedor;
                })

                ->addColumn('producto_marcas', function ($proveedor) {
                    $marcas=Proveedor::find($proveedor->id)->marcas;
                    if ($marcas->isEmpty()) {
                        $botones = "
                            <span class='badge bg-secondary'>No hay marcas</span>
                            <button type='button' class='btn btn-success btn-sm mt-2' data-toggle='modal' data-target='#modalAgregarMarca' onclick='anadirMarca($proveedor->id)' id-proveedor='{$proveedor->id}'>
                                <i class='fas fa-plus'></i> Agregar Marca
                            </button>
                        ";
                        return $botones;
                    }

                    $botones="";

                    foreach ($marcas as $opcion) {
                        $botones .= "
                            <span class='badge bg-dark'>$opcion->descripcion 
                                <button class='btn btn-sm btn-warning mx-2' type='button' id-marca='{$opcion->id}' nombre-marca='{$opcion->descripcion}' onclick='editarFuncion(this)'>
                                    <i class='fas fa-edit'></i>
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='eliminarMarcas({$opcion->id})' type='button'>
                                    <i class='fas fa-trash'></i>
                                </button> 
                                <button type='button' class='btn btn-primary btn-sm ml-2' onclick='moverMarcas({$opcion->id})' data-toggle='tooltip' data-placement='top' title='Mover'>
                                    <i class='fas fa-arrows-alt'></i>
                                </button>
                            </span>
                        ";
                    }

                    $botones .= "
                        <button type='button' class='btn btn-success btn-sm mt-2' data-toggle='modal' data-target='#modalAgregarMarca' onclick='anadirMarca($proveedor->id)' id-proveedor='$proveedor->id'>
                            <i class='fas fa-plus'></i> Agregar Marca
                        </button>
                    ";

                    return $botones;
                })

                ->addColumn('acciones', function ($proveedor) {
                    return "
                        <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#modalEditarProovedor' onclick='funcionEditar(this)' id-usuario-editar='$proveedor->id'>
                            <i class='fas fa-user-edit'></i>
                        </button>
                        <button type='button' class='btn btn-danger btn-sm' onclick='funcionEliminar(this)' id-usuario='$proveedor->id'>
                            <i class='fas fa-trash'></i>
                        </button>
                    ";
                })
                ->rawColumns(['nombre_proveedor','producto_marcas','acciones'])
                ->make(true);
        }

        $proveedores = Proveedor::all();
        return view('administrador.proveedores.index_proveedores', compact('proveedores'));
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
