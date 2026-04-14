<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $query = Proveedor::query()
                ->with('marcas')
                ->withCount(['marcas', 'productos']);

            if ($request->filled('proveedor')) {
                $nombreBuscado = $this->normalizarTexto($request->proveedor);
                $query->whereRaw('UPPER(nombre_proveedor) LIKE ?', ["%{$nombreBuscado}%"]);
            }

            return $dataTables->eloquent($query)
                ->addColumn('nombre_proveedor', function ($proveedor) {
                    return "
                        <div class='provider-name-block'>
                            <strong>{$proveedor->nombre_proveedor}</strong>
                            <small>{$proveedor->marcas_count} marcas / {$proveedor->productos_count} productos</small>
                        </div>
                    ";
                })
                ->addColumn('producto_marcas', function ($proveedor) {
                    $marcas = $proveedor->marcas;

                    if ($marcas->isEmpty()) {
                        return "
                            <div class='provider-brand-empty'>
                                <span>Sin marcas registradas</span>
                            </div>
                            <button type='button' class='btn btn-success btn-sm mt-2 provider-mini-btn' data-toggle='modal' data-target='#modalAgregarMarca' onclick='anadirMarca({$proveedor->id})' id-proveedor='{$proveedor->id}'>
                                <i class='fas fa-plus'></i> Agregar marca
                            </button>
                        ";
                    }

                    $botones = "<div class='provider-brand-list'>";

                    foreach ($marcas as $opcion) {
                        $botones .= "
                            <span class='provider-brand-chip'>
                                <span>{$opcion->descripcion}</span>
                                <button class='btn btn-sm btn-warning' type='button' id-marca='{$opcion->id}' nombre-marca='{$opcion->descripcion}' onclick='editarFuncion(this)' title='Editar marca'>
                                    <i class='fas fa-edit'></i> Editar
                                </button>
                                <button class='btn btn-sm btn-primary' onclick='moverMarcas({$opcion->id})' type='button' title='Mover marca'>
                                    <i class='fas fa-arrows-alt'></i> Mover
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='eliminarMarcas({$opcion->id})' type='button' title='Eliminar marca'>
                                    <i class='fas fa-trash'></i> Eliminar
                                </button>
                            </span>
                        ";
                    }

                    $botones .= "</div>";
                    $botones .= "
                        <button type='button' class='btn btn-success btn-sm mt-2 provider-mini-btn' data-toggle='modal' data-target='#modalAgregarMarca' onclick='anadirMarca({$proveedor->id})' id-proveedor='{$proveedor->id}'>
                            <i class='fas fa-plus'></i> Agregar marca
                        </button>
                    ";

                    return $botones;
                })
                ->addColumn('acciones', function ($proveedor) {
                    return "
                        <div class='provider-actions'>
                            <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#modalEditarProovedor' onclick='funcionEditar(this)' id-usuario-editar='{$proveedor->id}'>
                                <i class='fas fa-edit'></i> Editar
                            </button>
                            <button type='button' class='btn btn-danger btn-sm' onclick='funcionEliminar(this)' id-usuario='{$proveedor->id}'>
                                <i class='fas fa-trash'></i> Eliminar
                            </button>
                        </div>
                    ";
                })
                ->rawColumns(['nombre_proveedor', 'producto_marcas', 'acciones'])
                ->make(true);
        }

        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $resumenProveedores = [
            'proveedores' => Proveedor::count(),
            'marcas' => Marca::count(),
            'sin_marcas' => Proveedor::doesntHave('marcas')->count(),
        ];

        return view('administrador.proveedores.index_proveedores', compact('proveedores', 'resumenProveedores'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombreproveedor' => 'required|string|max:255',
            'opciones' => 'required|array|min:1',
            'opciones.*' => 'required|string|max:255|distinct',
        ], [
            'nombreproveedor.required' => 'El nombre del proveedor es obligatorio.',
            'nombreproveedor.string' => 'El nombre del proveedor debe ser una cadena de texto.',
            'nombreproveedor.max' => 'El nombre del proveedor no puede exceder los 255 caracteres.',
            'opciones.required' => 'Debe registrar al menos una marca.',
            'opciones.array' => 'Las marcas deben ser un arreglo.',
            'opciones.min' => 'Debe registrar al menos una marca.',
            'opciones.*.required' => 'No se permiten marcas vacias.',
            'opciones.*.distinct' => 'Hay marcas repetidas.',
        ]);

        $nombreProveedor = $this->normalizarTexto($request->nombreproveedor);
        $marcas = collect($request->opciones)
            ->map(fn ($marca) => $this->normalizarTexto($marca))
            ->filter()
            ->unique()
            ->values();

        if ($marcas->isEmpty()) {
            return response()->json(['message' => 'Debe registrar al menos una marca valida.'], 422);
        }

        if (Proveedor::whereRaw('UPPER(nombre_proveedor) = ?', [$nombreProveedor])->exists()) {
            return response()->json(['message' => 'El nombre del proveedor ya existe.'], 422);
        }

        DB::transaction(function () use ($nombreProveedor, $marcas) {
            $proveedor = Proveedor::create([
                'nombre_proveedor' => $nombreProveedor,
            ]);

            foreach ($marcas as $marca) {
                Marca::create([
                    'descripcion' => $marca,
                    'id_proveedor' => $proveedor->id,
                ]);
            }
        });

        return response()->json(['success' => true], 200);
    }

    public function show(string $proveedor)
    {
        return response()->json([
            'usuario' => Proveedor::withCount(['marcas', 'productos'])->findOrFail($proveedor),
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        //
    }

    public function update(Request $request, string $proveedor)
    {
        $request->validate([
            'nombreproveedor' => 'required|string|max:255',
        ], [
            'nombreproveedor.required' => 'El nombre del proveedor es obligatorio.',
            'nombreproveedor.string' => 'El nombre del proveedor debe ser una cadena de texto.',
            'nombreproveedor.max' => 'El nombre del proveedor no puede exceder los 255 caracteres.',
        ]);

        $nombreProveedor = $this->normalizarTexto($request->nombreproveedor);
        $existe = Proveedor::whereRaw('UPPER(nombre_proveedor) = ?', [$nombreProveedor])
            ->where('id', '!=', $proveedor)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'El nombre del proveedor ya existe.'], 422);
        }

        Proveedor::findOrFail($proveedor)->update([
            'nombre_proveedor' => $nombreProveedor,
        ]);

        return response()->json(['mensaje' => 'Proveedor actualizado correctamente.']);
    }

    public function destroy(string $proveedor)
    {
        $proveedor = Proveedor::withCount('productos')->with('marcas.productos')->find($proveedor);

        if (!$proveedor) {
            return response()->json(['error' => 'Proveedor no encontrado.'], 404);
        }

        $tieneProductosEnMarcas = $proveedor->marcas->contains(fn ($marca) => $marca->productos->isNotEmpty());

        if ($proveedor->productos_count > 0 || $tieneProductosEnMarcas) {
            return response()->json([
                'message' => 'No se puede eliminar el proveedor porque tiene productos asociados.',
            ], 422);
        }

        DB::transaction(function () use ($proveedor) {
            $proveedor->marcas()->delete();
            $proveedor->delete();
        });

        return response()->json(['mensaje' => 'Proveedor y sus marcas eliminados correctamente.']);
    }

    private function normalizarTexto(string $texto): string
    {
        return trim(strtoupper(preg_replace('/\s+/', ' ', $texto)));
    }
}
