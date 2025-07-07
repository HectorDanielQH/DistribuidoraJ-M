<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Cliente;
use App\Models\RendimientoPersonal;
use App\Models\Rutas;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $vendedor= User::query()->where('estado', 'ACTIVO')->role('vendedor');
            return $dataTables->eloquent($vendedor)
                ->addColumn('nombre_completo', function($vendedor) {
                    return $vendedor->nombres . ' ' . $vendedor->apellido_paterno . ' ' . $vendedor->apellido_materno;
                })
                ->filterColumn('nombre_completo', function($query, $keyword) {
                    $query->whereRaw("CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) LIKE ?", ["%{$keyword}%"]);
                })
                ->addColumn('asignacion', function($vendedor) {
                    $asignaciones = Asignacion::where('id_usuario', $vendedor->id)->count();
                    return $asignaciones > 0 ? $asignaciones.' clientes' : 'No asignado';
                })
                ->addColumn('action', function ($vendedor) {
                    return "
                        <button class='btn btn-primary btn-sm' data-bs-target='#visualizarClientes' data-bs-toggle='modal' data-id='$vendedor->id' onclick='clientesAsignados(this)'>
                            <i class='fas fa-eye mr-1'></i>
                        </button>
                        <button class='btn btn-warning btn-sm' data-bs-target='#visualizarRutas' data-bs-toggle='modal' data-id='$vendedor->id' onclick='rutasAsignadas(this)'>
                            <i class='fas fa-eye mr-1'></i>
                        </button>
                        <button class='btn btn-success btn-sm' data-bs-target='#asignarCliente' data-bs-toggle='modal' data-id='$vendedor->id' onclick='valordeusuariovendedor(this)'>
                            <i class='fas fa-plus mr-1'></i>
                        </button>
                    ";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        // Si no es una solicitud AJAX, mostramos la vista normal
        $vendedores=User::where('estado','ACTIVO')->role('vendedor')->get();
        $rutas=Rutas::all();
        return view('administrador.asignacion.index_asignacion', compact('vendedores','rutas'));
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
            'id_vendedor' => 'required|exists:users,id',
            'rutas' => 'required|array',
            'rutas.*' => 'exists:rutas,id',
        ]);

        foreach($request->rutas as $ruta){
            $clientes = Cliente::where('ruta_id', $ruta)->get();
            foreach ($clientes as $cliente) {
                Asignacion::create([
                    'id_usuario' => $request->id_vendedor,
                    'id_cliente' => $cliente->id,
                    'id_ruta' => $ruta,
                    'numero_pedido' => null,
                    'asignacion_fecha_hora' => now(),
                    'atencion_fecha_hora' => null,
                    'estado_pedido' => false,
                ]);
            }
        }
        
        return response()->json([
            'message' => 'Asignaciones creadas exitosamente.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Asignacion $asignacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asignacion $asignacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asignacion $asignacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }


    public function clientesAsignadosAVendedoresEliminar(string $id_cliente, string $id_vendedor){
        $asignacion = Asignacion::where('id_cliente', $id_cliente)
            ->where('id_usuario', $id_vendedor)
            ->first();

        if ($asignacion) {
            $asignacion->delete();
            return response()->json(['message' => 'Asignación eliminada exitosamente.'], 200);
        }

        return response()->json(['message' => 'Asignación no encontrada.'], 404);
    }

    public function RutasNoAsignadosAVendedores(){
        $rutasNoAsignados = Rutas::whereDoesntHave('asignaciones')->get();
        return response()->json($rutasNoAsignados);
    }

    public function clientesAsignadosAVendedores(string $id_vendedor){
        $clientesAsignados = Cliente::whereHas('asignaciones', function ($query) use ($id_vendedor) {
            $query->where('id_usuario', $id_vendedor);
        })->get();
        return response()->json($clientesAsignados);
    } 

    public function obtenerVendedoresRuta(string $id_vendedor)
    {
        $conjuntoTablas=Asignacion::join('clientes', 'asignacions.id_cliente', '=', 'clientes.id')
            ->join('users', 'asignacions.id_usuario', '=', 'users.id')
            ->where('asignacions.id_usuario', $id_vendedor)
            ->select('clientes.*','asignacions.*')
            ->get();
        return response()->json($conjuntoTablas);
    }

    public function resetearVendedoresRuta(string $id_vendedor)
    {
        $asignaciones = Asignacion::where('id_usuario', $id_vendedor)->get();
        foreach ($asignaciones as $asignacion) {
            RendimientoPersonal::create([
                'id_usuario' => $asignacion->id_usuario,
                'id_cliente' => $asignacion->id_cliente,
                'id_ruta' => $asignacion->id_ruta,
                'numero_pedido' => $asignacion->numero_pedido,
                'asignacion_fecha_hora' => $asignacion->asignacion_fecha_hora,
                'atencion_fecha_hora' => $asignacion->atencion_fecha_hora,
                'estado_pedido' => $asignacion->estado_pedido,
            ]);
            $asignacion->asignacion_fecha_hora = now();
            $asignacion->atencion_fecha_hora = null;
            $asignacion->estado_pedido = false;
            $asignacion->save();
        }
        return response()->json(['message' => 'Ruta de vendedor reseteada exitosamente.'], 200);
    }


    public function rutasAsignadasAVendedores(string $id_vendedor)
    {
        $rutasAsignadas = Rutas::whereHas('asignaciones', function ($query) use ($id_vendedor) {
            $query->where('id_usuario', $id_vendedor);
        })->get();
        return response()->json($rutasAsignadas);
    }

    public function rutasAsignadasAVendedoresEliminar(string $id_ruta, string $id_vendedor)
    {
        $asignacion = Asignacion::where('id_ruta', $id_ruta)
            ->where('id_usuario', $id_vendedor)->delete();
        if ($asignacion) {
            return response()->json(['message' => 'Asignación de ruta eliminada exitosamente.'], 200);
        }
        return response()->json(['message' => 'Asignación de ruta no encontrada.'], 404);
    }
}
