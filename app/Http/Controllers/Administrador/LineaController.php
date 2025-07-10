<?php


namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Linea;
use App\Models\Marca;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LineaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){            

            $query = Marca::query();
            
            if ($request->filled('descripcion_marca')) {
                $query->where('descripcion', 'like', '%' . trim(strtoupper($request->descripcion_marca)) . '%');
            }
            
            return $dataTables->eloquent($query)
                ->addColumn('descripcion_marca', function ($marca) {
                    return $marca->descripcion;
                })
                ->addColumn('lineas', function ($marca) {
                    $lineas = $marca->linea;
                    if ($lineas->isEmpty()) {
                        return "<span class='badge bg-secondary'>No hay líneas</span>";
                    }
                    $botones = "";
                    foreach ($lineas as $linea) {
                        $botones .= "<span class='badge bg-dark'>{$linea->descripcion_linea}
                            <button class='btn btn-sm btn-warning mx-2' type='button' id-linea='{$linea->id}' id-nombre-linea='$linea->descripcion_linea' onclick='editarLinea(this)'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-sm btn-danger' onclick='eliminarLinea({$linea->id})' type='button'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </span> ";
                    }
                    
                    return $botones;
                })
                ->addColumn('acciones', function ($marca) {
                    $botones = "
                        <button class='btn btn-sm btn-warning' type='button' id-linea='{$marca->id}' onclick='editarLinea(this)'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class='btn btn-sm btn-danger' onclick='eliminarLinea({$marca->id})' type='button'>
                            <i class='fas fa-trash'></i>
                        </button>
                    ";
                    return $botones;
                })
                ->rawColumns(['lineas','acciones'])
                ->make(true);
        }

        $marcas_busqueda= Marca::select('id', 'descripcion')
            ->orderBy('descripcion', 'asc')
            ->get();

        return view('administrador.lineas.index_lineas', compact('marcas_busqueda'));
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

        return redirect()->route('administrador.lineas.index')->with('success', 'Linea created successfully.');
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

        return redirect()->route('administrador.lineas.index')->with('success', 'Linea updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $linea)
    {
        $linea = Linea::findOrFail($linea);
        $linea->delete();

        return redirect()->route('administrador.lineas.index')->with('success', 'Linea deleted successfully.');
    }
}
