<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asignaciones = Asignacion::paginate(10);
        $vendedores=User::where('estado','ACTIVO')->role('vendedor')->get();
        $clientes=Cliente::all();
        return view('administrador.asignacion.index_asignacion', compact('asignaciones','vendedores','clientes'));
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
            'clientes.*' => 'exists:clientes,id',
        ]);

        foreach ($request->clientes as $clienteId) {
            Asignacion::create([
                'id_usuario' => $request->id_vendedor,
                'id_cliente' => $clienteId,
                'asignacion_fecha_hora' => now(),
            ]);
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

    public function clientesNoAsignadosAVendedores(){
        $clientesNoAsignados = Cliente::whereDoesntHave('asignaciones')->get();
        return response()->json($clientesNoAsignados);
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
            $asignacion->asignacion_fecha_hora = now();
            $asignacion->atencion_fecha_hora = null;
            $asignacion->estado_pedido = false;
            $asignacion->save();
        }
        return response()->json(['message' => 'Ruta de vendedor reseteada exitosamente.'], 200);
    }
}
