<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Linea;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class LineaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $query = Marca::query()
                ->with('linea.productos')
                ->withCount('linea');

            if ($request->filled('descripcion_marca')) {
                $query->where('descripcion', 'like', '%' . $this->normalizarTexto($request->descripcion_marca) . '%');
            }

            return $dataTables->eloquent($query)
                ->addColumn('descripcion_marca', function ($marca) {
                    $descripcionMarca = e($marca->descripcion);
                    return "
                        <div class='line-brand-block'>
                            <strong>{$descripcionMarca}</strong>
                            <small>{$marca->linea_count} lineas registradas</small>
                        </div>
                    ";
                })
                ->addColumn('lineas', function ($marca) {
                    $lineas = $marca->linea;

                    if ($lineas->isEmpty()) {
                        return "
                            <div class='line-empty-state'>
                                <span>Sin lineas registradas</span>
                            </div>
                        ";
                    }

                    $botones = "<div class='line-chip-list'>";
                    foreach ($lineas as $linea) {
                        $productos = $linea->productos->count();
                        $descripcionLinea = e($linea->descripcion_linea);
                        $botones .= "
                            <span class='line-chip'>
                                <span>
                                    <strong>{$descripcionLinea}</strong>
                                    <small>{$productos} productos</small>
                                </span>
                                <button class='btn btn-sm btn-warning' type='button' id-linea='{$linea->id}' id-nombre-linea='{$descripcionLinea}' onclick='editarLinea(this)'>
                                    <i class='fas fa-edit'></i> Editar
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='eliminarLinea({$linea->id})' type='button'>
                                    <i class='fas fa-trash'></i> Eliminar
                                </button>
                            </span>
                        ";
                    }

                    return $botones . "</div>";
                })
                ->rawColumns(['descripcion_marca', 'lineas'])
                ->make(true);
        }

        $marcas_busqueda = Marca::select('id', 'descripcion')
            ->orderBy('descripcion', 'asc')
            ->get();

        $resumenLineas = [
            'marcas' => Marca::count(),
            'lineas' => Linea::count(),
            'marcas_sin_lineas' => Marca::doesntHave('linea')->count(),
        ];

        return view('administrador.lineas.index_lineas', compact('marcas_busqueda', 'resumenLineas'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion_linea' => 'required|array|min:1',
            'descripcion_linea.*' => 'required|string|max:255',
            'marca_id' => 'required|exists:marcas,id',
        ], [
            'descripcion_linea.required' => 'La descripcion de la linea es obligatoria.',
            'descripcion_linea.array' => 'La descripcion de la linea debe ser un arreglo.',
            'descripcion_linea.min' => 'Debe ingresar al menos una linea.',
            'descripcion_linea.*.required' => 'No se permiten lineas vacias.',
            'marca_id.required' => 'Debe seleccionar una marca.',
            'marca_id.exists' => 'La marca seleccionada no existe.',
        ]);

        $lineas = collect($request->descripcion_linea)
            ->map(fn ($descripcion) => $this->normalizarTexto($descripcion))
            ->filter()
            ->unique()
            ->values();

        if ($lineas->isEmpty()) {
            return response()->json(['message' => 'Debe ingresar al menos una linea valida.'], 422);
        }

        $lineasExistentes = Linea::where('id_marca', $request->marca_id)
            ->whereIn('descripcion_linea', $lineas)
            ->pluck('descripcion_linea');

        if ($lineasExistentes->isNotEmpty()) {
            return response()->json([
                'message' => 'Estas lineas ya existen para la marca: ' . $lineasExistentes->implode(', '),
            ], 422);
        }

        DB::transaction(function () use ($lineas, $request) {
            foreach ($lineas as $descripcion) {
                Linea::create([
                    'descripcion_linea' => $descripcion,
                    'id_marca' => $request->marca_id,
                ]);
            }
        });

        return response()->json(['success' => true], 200);
    }

    public function show(string $id_marca)
    {
        $lineas = Linea::where('id_marca', $id_marca)
            ->orderBy('descripcion_linea')
            ->get();

        return response()->json($lineas);
    }

    public function edit(Linea $linea)
    {
        //
    }

    public function update(Request $request, string $linea)
    {
        $linea = Linea::findOrFail($linea);

        $request->validate([
            'descripcion_linea' => 'required|string|max:255',
        ], [
            'descripcion_linea.required' => 'La descripcion de la linea es obligatoria.',
            'descripcion_linea.string' => 'La descripcion de la linea debe ser una cadena de texto.',
            'descripcion_linea.max' => 'La descripcion de la linea no puede exceder los 255 caracteres.',
        ]);

        $descripcion = $this->normalizarTexto($request->descripcion_linea);
        $existe = Linea::where('id_marca', $linea->id_marca)
            ->where('descripcion_linea', $descripcion)
            ->where('id', '!=', $linea->id)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'La linea ya existe para esta marca.'], 422);
        }

        $linea->update([
            'descripcion_linea' => $descripcion,
        ]);

        return response()->json(['success' => true], 200);
    }

    public function destroy(string $linea)
    {
        $linea = Linea::withCount('productos')->findOrFail($linea);

        if ($linea->productos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la linea porque tiene productos asociados.',
            ], 422);
        }

        $linea->delete();

        return response()->json(['success' => true], 200);
    }

    private function normalizarTexto(string $texto): string
    {
        return trim(strtoupper(preg_replace('/\s+/', ' ', $texto)));
    }
}
