<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pedido;
use App\Models\RendimientoPersonal;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AsignacionVendedorController extends Controller
{
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = Asignacion::query();
            return $dataTables->eloquent($query)
                ->addColumn('cliente', function($asignacion){
                    return $asignacion->cliente ? $asignacion->cliente->nombres." ".$asignacion->cliente->apellidos : 'N/A';
                })
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cliente', function ($q) use ($keyword) {
                        $q->whereRaw("CONCAT(nombres, ' ', apellidos) ilike ?", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('celular', function($asignacion){
                    return $asignacion->cliente ? $asignacion->cliente->celular : 'N/A';
                })
                ->addColumn('ubicacion', function($asignacion){
                    return $asignacion->cliente ? $asignacion->cliente->calle_avenida : 'N/A';
                })
                ->addColumn('tiene_pedido', function($asignacion){
                    if($asignacion->numero_pedido){
                        return '<span class="badge badge-info">Tiene Pedido</span>';
                    } else {
                        return '<span class="badge badge-secondary">Sin Pedido</span>';
                    }
                })
                ->addColumn('acciones', function($asignacion){
                    $ruta=route('preventistas.registrar.pedido', ['id' => $asignacion->id_cliente]);
                    $botones = '<div class="btn-group" role="group">';
                    if(!$asignacion->estado_pedido){
                        $botones .= '
                            <a href="'.$ruta.'" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Atender Cliente
                            </a>
                        ';
                    } else {
                        $botones .= '
                            <a href="'.$ruta.'" class="btn btn-warning btn-sm">
                                <i class="fas fa-check"></i> Editar Pedido
                            </a>
                        ';
                    }
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['tiene_pedido','acciones'])
                ->make(true);
        }
        return view('vendedor.asignaciones.index_asignaciones');
    }

    public function registrarAtencion(Request $request, string $id)
    {
        $asignacion = Asignacion::findOrFail($id);
        if($asignacion->numero_pedido){
            Pedido::where('numero_pedido', $asignacion->numero_pedido)->delete();
        }
        $asignacion->numero_pedido = null;
        $asignacion->atencion_fecha_hora = $asignacion->atencion_fecha_hora?$asignacion->atencion_fecha_hora:now();
        $asignacion->estado_pedido = false;
        $asignacion->save();

        return response()->json([
            'message' => 'Atención registrada exitosamente.'
        ], 200);
    }

    public function obtenerPedidosProceso(string $id_cliente)
    {
        // Obtener la cantidad de pedidos distintos en proceso (por número de pedido)
        $cantidad_pedidos = Pedido::where('id_cliente', $id_cliente)
            ->where('estado_pedido', false)
            ->select('numero_pedido')
            ->distinct()
            ->get();

        // Obtener detalle de los pedidos en proceso
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_cliente', $id_cliente)
            ->where('pedidos.estado_pedido', false)
            ->select(
                'pedidos.numero_pedido',
                'pedidos.fecha_pedido',
                'pedidos.estado_pedido',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
                'pedidos.cantidad',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.foto_producto',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta'
            )
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->get();

        return response()->json([
            'pedidos' => $pedidos,
            'cantidad_pedidos' => $cantidad_pedidos
        ], 200);
    }

    public function obtenerPedidosPendientes(string $id_cliente)
    {
        // Obtener detalle de los pedidos pendientes
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_cliente', $id_cliente)
            ->where('pedidos.estado_pedido', false)
            ->select(
                'productos.id as id_producto',
                'productos.codigo as codigo_producto',
                'productos.foto_producto',
                'productos.nombre_producto',
                'forma_ventas.id as id_forma_venta',
                'forma_ventas.tipo_venta',
                'forma_ventas.precio_venta',
                'pedidos.promocion',
                'pedidos.descripcion_descuento_porcentaje',
                'pedidos.descripcion_regalo',
                'pedidos.cantidad',
            )
            ->get();
        return response()->json([
            'pedidos' => $pedidos,
        ], 200);
    }
}
