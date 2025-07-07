<?php

namespace App\Http\Controllers;

use App\Models\RendimientoPersonal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RendimientoPersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personal=User::Role('vendedor')->get();
        return view('administrador.reportes.rendimiento',compact('personal'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RendimientoPersonal $rendimientoPersonal)
    {
        //
    }

   public function rendimientoPersonal(Request $request)
    {
        $personal = User::role('vendedor')->find($request->id);
        $periodo = $request->periodo;

        if (!$personal) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($periodo == 'dias') {
            $atencion = RendimientoPersonal::selectRaw('DATE(atencion_fecha_hora) as dia, COUNT(*) as total')
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$request->fechaInicio, $request->fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw('DATE(atencion_fecha_hora)')
                ->orderBy('dia')
                ->get();

            return response()->json([
                'fechas'=>$atencion,
            ]);
        }
        if ($periodo == 'semanas') {
            list($anioInicio, $semanaInicio) = explode('-W', $request->semanaInicio);
            list($anioFin, $semanaFin) = explode('-W', $request->semanaFin);

            $fechaInicio = Carbon::now()->setISODate($anioInicio, $semanaInicio)->startOfWeek();
            $fechaFin = Carbon::now()->setISODate($anioFin, $semanaFin)->endOfWeek();

            $atencion = RendimientoPersonal::selectRaw("TO_CHAR(atencion_fecha_hora, 'IYYY-IW') as semana, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("TO_CHAR(atencion_fecha_hora, 'IYYY-IW')")
                ->orderBy('semana')
                ->get();

            return response()->json(['fechas' => $atencion]);
        }
        if ($periodo == 'meses') {
            $fechaInicio = Carbon::parse($request->mesInicio . '-01')->startOfMonth();
            $fechaFin = Carbon::parse($request->mesFin . '-01')->endOfMonth();
            $atencion = RendimientoPersonal::selectRaw("TO_CHAR(atencion_fecha_hora, 'YYYY-MM') as mes, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("TO_CHAR(atencion_fecha_hora, 'YYYY-MM')")
                ->orderBy('mes')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }
        if ($periodo == 'anios') {
            // Asegurar que los valores sean enteros (ej: 2023, 2025)
            $anioInicio = (int) $request->anioInicio;
            $anioFin = (int) $request->anioFin;

            // Crear fechas desde el primer día del año hasta el último día del año
            $fechaInicio = \Carbon\Carbon::create($anioInicio)->startOfYear();
            $fechaFin = \Carbon\Carbon::create($anioFin)->endOfYear();

            $atencion = RendimientoPersonal::selectRaw("EXTRACT(YEAR FROM atencion_fecha_hora) as anio, COUNT(*) as total")
                ->where('id_usuario', $personal->id)
                ->whereBetween('atencion_fecha_hora', [$fechaInicio, $fechaFin])
                ->where('estado_pedido', true)
                ->groupByRaw("EXTRACT(YEAR FROM atencion_fecha_hora)")
                ->orderBy('anio')
                ->get();

            return response()->json([
                'fechas' => $atencion,
            ]);
        }


        return response()->json(['error' => 'Periodo no válido'], 400);
    }

}
