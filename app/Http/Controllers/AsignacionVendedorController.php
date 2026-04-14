<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\FormaVenta;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RendimientoPersonal;
use App\Models\Rutas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AsignacionVendedorController extends Controller
{
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = Asignacion::query()
                ->with(['cliente.ruta', 'ruta'])
                ->where('id_usuario', auth()->id())
                ->when($request->filled('cliente'), function ($query) use ($request) {
                    $termino = trim($request->cliente);
                    $query->whereHas('cliente', function ($cliente) use ($termino) {
                        $cliente->whereRaw("CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')) ilike ?", ["%{$termino}%"]);
                    });
                })
                ->when($request->filled('ruta_id'), function ($query) use ($request) {
                    $query->where('id_ruta', $request->ruta_id);
                })
                ->when($request->filled('estado'), function ($query) use ($request) {
                    match ($request->estado) {
                        'pendiente' => $query->whereNull('atencion_fecha_hora'),
                        'con_pedido' => $query->whereNotNull('numero_pedido')->where('estado_pedido', true),
                        'sin_pedido' => $query->whereNull('numero_pedido')->whereNotNull('atencion_fecha_hora'),
                        'atendido' => $query->whereNotNull('atencion_fecha_hora'),
                        default => null,
                    };
                })
                ->orderByRaw('CASE WHEN atencion_fecha_hora IS NULL THEN 0 ELSE 1 END')
                ->orderBy('asignacion_fecha_hora', 'asc');

            return $dataTables->eloquent($query)
                ->addColumn('cliente', function($asignacion){
                    if (!$asignacion->cliente) {
                        return '<span class="text-muted">Cliente no encontrado</span>';
                    }

                    $nombre = trim($asignacion->cliente->nombres.' '.$asignacion->cliente->apellidos);
                    return '
                        <div class="client-cell">
                            <span class="client-name">'.e($nombre).'</span>
                        </div>
                    ';
                })
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cliente', function ($q) use ($keyword) {
                        $q->whereRaw("CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')) ilike ?", ["%{$keyword}%"])
                            ->orWhere('codigo_cliente', 'ilike', "%{$keyword}%")
                            ->orWhere('cedula_identidad', 'ilike', "%{$keyword}%");
                    });
                })
                ->addColumn('celular', function($asignacion){
                    if (!$asignacion->cliente || !$asignacion->cliente->celular) {
                        return '<span class="text-muted">Sin celular</span>';
                    }

                    $celular = e($asignacion->cliente->celular);
                    return '<a class="phone-link" href="tel:'.$celular.'"><i class="fas fa-phone-alt"></i> '.$celular.'</a>';
                })
                ->addColumn('ruta', function($asignacion){
                    $ruta = $asignacion->ruta?->nombre_ruta ?? $asignacion->cliente?->ruta?->nombre_ruta ?? 'Sin ruta';
                    $zona = $asignacion->cliente?->zona_barrio ?: 'Sin zona';

                    return '
                        <div class="route-cell">
                            <span class="route-name">'.e($ruta).'</span>
                            <span class="client-meta">'.e($zona).'</span>
                        </div>
                    ';
                })
                ->addColumn('ubicacion', function($asignacion){
                    if (!$asignacion->cliente) {
                        return '<span class="text-muted">Sin direccion</span>';
                    }

                    $calle = $asignacion->cliente->calle_avenida ?: 'Sin calle registrada';
                    $referencia = $asignacion->cliente->referencia_direccion;

                    return '
                        <div class="address-cell">
                            <span>'.e($calle).'</span>
                            '.($referencia ? '<small>Ref. '.e($referencia).'</small>' : '').'
                        </div>
                    ';
                })
                ->addColumn('tiene_pedido', function($asignacion){
                    if($asignacion->numero_pedido && $asignacion->estado_pedido){
                        return '<span class="status-pill status-order"><i class="fas fa-receipt"></i> Pedido #'.e($asignacion->numero_pedido).'</span>';
                    }

                    if($asignacion->atencion_fecha_hora){
                        return '<span class="status-pill status-done"><i class="fas fa-check"></i> Sin pedido</span>';
                    }

                    return '<span class="status-pill status-pending"><i class="fas fa-clock"></i> Pendiente</span>';
                })
                ->addColumn('acciones', function($asignacion){
                    $ruta=route('preventistas.registrar.pedido', ['id' => $asignacion->id_cliente]);
                    $botones = '<div class="assignment-actions">';
                    if(!$asignacion->estado_pedido){
                        $botones .= '
                            <a href="'.$ruta.'" class="btn btn-success btn-action">
                                <i class="fas fa-shopping-cart"></i> Tomar pedido
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-action btn-sin-pedido" data-id="'.$asignacion->id.'">
                                <i class="fas fa-user-check"></i> Sin pedido
                            </button>
                        ';
                    } else {
                        $botones .= '
                            <a href="'.$ruta.'" class="btn btn-warning btn-action">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        ';
                    }
                    if($asignacion->numero_pedido){
                        $botones .= '
                            <button type="button" class="btn btn-info btn-action btn-ver-pedido" data-asignacion-id="'.$asignacion->id.'">
                                <i class="fas fa-eye"></i> Ver pedido
                            </button>
                        ';
                    }
                    $botones .= '</div>';
                    return $botones;
                })
                ->rawColumns(['cliente', 'celular', 'ruta', 'ubicacion', 'tiene_pedido','acciones'])
                ->make(true);
        }

        $rutas = Rutas::whereHas('asignaciones', function ($query) {
                $query->where('id_usuario', auth()->id());
            })
            ->orderBy('nombre_ruta')
            ->get(['id', 'nombre_ruta']);

        $base = Asignacion::where('id_usuario', auth()->id());
        $resumen = [
            'total' => (clone $base)->count(),
            'pendientes' => (clone $base)->whereNull('atencion_fecha_hora')->count(),
            'con_pedido' => (clone $base)->whereNotNull('numero_pedido')->where('estado_pedido', true)->count(),
            'sin_pedido' => (clone $base)->whereNull('numero_pedido')->whereNotNull('atencion_fecha_hora')->count(),
        ];

        return view('vendedor.asignaciones.index_asignaciones', compact('rutas', 'resumen'));
    }

    public function registrarAtencion(Request $request, string $id)
    {
        DB::transaction(function () use ($id) {
            $asignacion = Asignacion::where('id_usuario', auth()->id())
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if($asignacion->numero_pedido){
                $pedidos = Pedido::where('numero_pedido', $asignacion->numero_pedido)
                    ->where('id_usuario', auth()->id())
                    ->where('id_cliente', $asignacion->id_cliente)
                    ->lockForUpdate()
                    ->get();

                foreach ($pedidos as $pedido) {
                    $formaVenta = FormaVenta::findOrFail($pedido->id_forma_venta);
                    $producto = Producto::where('id', $pedido->id_producto)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $producto->cantidad += ($pedido->cantidad * $formaVenta->equivalencia_cantidad);
                    $producto->save();
                }

                Pedido::where('numero_pedido', $asignacion->numero_pedido)
                    ->where('id_usuario', auth()->id())
                    ->where('id_cliente', $asignacion->id_cliente)
                    ->delete();
            }

            $asignacion->numero_pedido = null;
            $asignacion->atencion_fecha_hora = $asignacion->atencion_fecha_hora?$asignacion->atencion_fecha_hora:now();
            $asignacion->estado_pedido = false;
            $asignacion->save();
        });

        return response()->json([
            'message' => 'Atención registrada exitosamente.'
        ], 200);
    }

    public function obtenerPedidosProceso(string $id_cliente)
    {
        $asignacion = Asignacion::where('id_usuario', auth()->id())
            ->where('id', $id_cliente)
            ->first();

        if (!$asignacion) {
            $asignacion = Asignacion::where('id_usuario', auth()->id())
                ->where('id_cliente', $id_cliente)
                ->firstOrFail();
        }

        if (!$asignacion->numero_pedido) {
            return response()->json([
                'pedidos' => [],
                'cantidad_pedidos' => [],
            ], 200);
        }

        // Obtener la cantidad de pedidos distintos por número de pedido.
        $cantidad_pedidos = Pedido::where('id_cliente', $asignacion->id_cliente)
            ->where('id_usuario', auth()->id())
            ->where('numero_pedido', $asignacion->numero_pedido)
            ->select('numero_pedido')
            ->distinct()
            ->get();

        // Obtener detalle de los pedidos en proceso
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_cliente', $asignacion->id_cliente)
            ->where('pedidos.id_usuario', auth()->id())
            ->where('pedidos.numero_pedido', $asignacion->numero_pedido)
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
        Asignacion::where('id_usuario', auth()->id())
            ->where('id_cliente', $id_cliente)
            ->firstOrFail();

        // Obtener detalle de los pedidos pendientes
        $pedidos = Pedido::join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_cliente', $id_cliente)
            ->where('pedidos.id_usuario', auth()->id())
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
