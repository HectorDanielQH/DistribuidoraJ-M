<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pedido;
use App\Models\RendimientoPersonal;
use Illuminate\Http\Request;

class AsignacionVendedorController extends Controller
{
    public function index(Request $request)
    {
        // Asignaciones solo del usuario autenticado
        $query = Asignacion::with('cliente') // para evitar N+1
            ->where('id_usuario', auth()->id());

        if ($request->filled('nombre')) {
            $keywords = explode(' ', trim(strtoupper($request->nombre)));
            $query->whereHas('cliente', function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where(function ($subquery) use ($word) {
                        $subquery->where('nombres', 'like', '%' . $word . '%')
                                ->orWhere('apellidos', 'like', '%' . $word . '%');
                    });
                }
            });
        }

        if ($request->filled('ci')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('cedula_identidad', 'like', '%' . trim(strtoupper($request->ci)) . '%');
            });
        }

        $asignaciones = $query->paginate(10);

        return view('vendedor.asignaciones.index_asignaciones', compact('asignaciones'))
            ->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('ci'));
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
