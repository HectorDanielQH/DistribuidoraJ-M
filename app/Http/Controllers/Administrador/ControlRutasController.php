<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use App\Models\NoAtendidos;
use App\Models\RendimientoPersonal;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ControlRutasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataTables $dataTables)
    {
        if ($request->ajax()) {
            $asignaciones = Asignacion::query();
            return $dataTables->eloquent($asignaciones)
                ->addColumn('ci', function($asignacion) {
                    return $asignacion->cliente->cedula_identidad ?? 'N/A';
                })
                ->addColumn('nombre_completo', function($asignacion) {
                    return $asignacion->cliente->nombres . ' ' . $asignacion->cliente->apellido_paterno . ' ' . $asignacion->cliente->apellido_materno;
                })
                ->addColumn('ubicacion', function($asignacion) {
                    return $asignacion->cliente->ubicacion ?? 'N/A';
                })
                ->addColumn('ruta', function($asignacion) {
                    return $asignacion->ruta->nombre_ruta ?? 'N/A';
                })
                ->addColumn('fecha_asignacion', function($asignacion) {
                    // Formatear la fecha de asignación
                    if (!$asignacion->asignacion_fecha_hora) {
                        return 'N/A';
                    }
                    $asignacion->asignacion_fecha_hora = \Carbon\Carbon::parse($asignacion->asignacion_fecha_hora)->format('d/m/Y H:i');
                    //html
                    return "<span class='badge bg-dark'>{$asignacion->asignacion_fecha_hora}</span>";
                })
                ->addColumn('fecha_atencion', function($asignacion) {
                    // Formatear la fecha de atención
                    if (!$asignacion->atencion_fecha_hora) {
                        return '<span class="badge bg-warning">Pendiente</span>';
                    }
                    $asignacion->atencion_fecha_hora = \Carbon\Carbon::parse($asignacion->atencion_fecha_hora)->format('d/m/Y H:i');
                    //html
                    return "<span class='badge bg-dark'>{$asignacion->atencion_fecha_hora}</span>";
                })
                ->addColumn('pedido', function($asignacion) {
                    $html = '';
                    if ($asignacion->estado_pedido) {
                        //si pidio indicar que realizo un pedido y mostrar boton
                        if ($asignacion->numero_pedido) {
                            $html .= "<span class='badge bg-success'>Pedido: {$asignacion->numero_pedido}</span>";
                            $html .= " <button class='btn btn-info btn-sm' data-id='{$asignacion->id}' onclick='verDetallesPedido(this)'>
                                        <i class='fas fa-eye'></i> Ver Detalles
                                      </button>";
                        }
                        //caso contrario indicar que no realizo un pedido pero fue atendido
                        else {
                            $html .= "<span class='badge bg-secondary'>Atendido sin pedido</span>";
                        }
                    }
                    else{
                        //aun no ha sido atendido
                        $html .= "<span class='badge bg-warning'>Pendiente</span>";
                    }
                    return $html;
                })
                ->rawColumns(['pedido','fecha_asignacion','fecha_atencion'])
                ->make(true);
        }

        $vendedores = User::where('estado', 'ACTIVO')->role('vendedor')->get();

        // Si no es una solicitud AJAX, mostramos la vista normal
        return view('administrador.controlrutas.index', compact('vendedores'));
    }

    public function indexPreventista(string $idVendedor){
        $asignacion=Asignacion::query()->where('id_usuario', $idVendedor);
        return DataTables::of($asignacion)
            ->addColumn('ci', function($asignacion) {
                return $asignacion->cliente->cedula_identidad ?? 'N/A';
            })
            ->addColumn('nombre_completo', function($asignacion) {
                return $asignacion->cliente->nombres . ' ' . $asignacion->cliente->apellido_paterno . ' ' . $asignacion->cliente->apellido_materno;
            })
            ->addColumn('ubicacion', function($asignacion) {
                return $asignacion->cliente->ubicacion ?? 'N/A';
            })
            ->addColumn('ruta', function($asignacion) {
                return $asignacion->ruta->nombre_ruta ?? 'N/A';
            })
            ->addColumn('fecha_asignacion', function($asignacion) {
                // Formatear la fecha de asignación
                if (!$asignacion->asignacion_fecha_hora) {
                    return 'N/A';
                }
                $asignacion->asignacion_fecha_hora = \Carbon\Carbon::parse($asignacion->asignacion_fecha_hora)->format('d/m/Y H:i');
                //html
                return "<span class='badge bg-dark'>{$asignacion->asignacion_fecha_hora}</span>";
            })
            ->addColumn('fecha_atencion', function($asignacion) {
                // Formatear la fecha de atención
                if (!$asignacion->atencion_fecha_hora) {
                    return '<span class="badge bg-warning">Pendiente</span>';
                }
                $asignacion->atencion_fecha_hora = \Carbon\Carbon::parse($asignacion->atencion_fecha_hora)->format('d/m/Y H:i');
                //html
                return "<span class='badge bg-dark'>{$asignacion->atencion_fecha_hora}</span>";
            })
            ->addColumn('pedido', function($asignacion) {
                $html = '';
                if ($asignacion->estado_pedido) {
                    //si pidio indicar que realizo un pedido y mostrar boton
                    if ($asignacion->numero_pedido) {
                        $html .= "<span class='badge bg-success'>Pedido: {$asignacion->numero_pedido}</span>";
                        $html .= " <button class='btn btn-info btn-sm' data-id='{$asignacion->id}' onclick='verDetallesPedido(this)'>
                                    <i class='fas fa-eye'></i> Ver Detalles
                                  </button>";
                    }
                    //caso contrario indicar que no realizo un pedido pero fue atendido
                    else {
                        $html .= "<span class='badge bg-secondary'>Atendido sin pedido</span>";
                    }
                }
                else{
                    //aun no ha sido atendido
                    $html .= "<span class='badge bg-warning'>Pendiente</span>";
                }
                return $html;
            })
            ->rawColumns(['pedido','fecha_asignacion','fecha_atencion'])
            ->make(true);
    }
    // Otros métodos como create, store, show, edit, update, destroy pueden ser añadidos aquí según sea necesario.

    public function cerrarAsignaciones(){
        $asignaciones = Asignacion::all();
        if ($asignaciones->isEmpty()) {
            return response()->json(['message' => 'No hay asignaciones para cerrar.'], 404);
        }
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

            if($asignacion->atencion_fecha_hora == null){
                NoAtendidos::create([
                    'id_cliente' => $asignacion->id_cliente,
                ]);
            }
        }
        // Eliminar todas las asignaciones
        Asignacion::truncate();
        return response()->json(['message' => 'Ruta de vendedor reseteada exitosamente.'], 200);
    }
}
