<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\FormaVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ContabilidadVentaController extends Controller
{
    public function ventasPorDia(Request $request){
        $preventistas=User::role('vendedor')->get();
        return view('Contabilidad.VentasPorDia.index',compact('preventistas'));
    }
    public function ventasPorDiaPreventista(string $idPreventista)
    {
        // Query agregado por día y preventista
        $q = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $idPreventista)
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->selectRaw("DATE(ventas.fecha_contabilizacion) AS fecha_venta") // Postgres: DATE(ts)
            ->selectRaw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_venta")
            ->groupBy(DB::raw("DATE(ventas.fecha_contabilizacion)"))
            ->orderBy(DB::raw("DATE(ventas.fecha_contabilizacion)"), 'desc');

        return DataTables::of($q)
            // Formatea fecha como d/m/Y
            ->editColumn('fecha_venta', function ($row) {
                // $row->fecha_venta viene como 'YYYY-MM-DD'
                try {
                    return Carbon::parse($row->fecha_venta)->format('d/m/Y');
                } catch (\Throwable $e) {
                    return $row->fecha_venta ?? 'N/A';
                }
            })
            // Asegura formato numérico con 2 decimales (aunque ya viene como numeric en el select)
            ->editColumn('total_venta', function ($row) {
                return number_format((float)$row->total_venta, 2, '.', '');
            })
            ->addColumn('acciones', function ($row) use ($idPreventista) {
                // Pasa la fecha (ISO) y el preventista para ver el detalle de ese día
                $fechaIso = $row->fecha_venta; // 'YYYY-MM-DD'
                return "<button type='button' class='btn btn-warning btn-sm'
                            onclick='verDetalleVenta(this)'
                            data-fecha='{$fechaIso}'
                            data-preventista='{$idPreventista}'>
                            Ver Detalle <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ventasPorDiaPreventistaDetallePedidos(string $fecha, string $idPreventista)
    {
        $pedidos = Venta::query()
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $idPreventista)
            ->whereBetween('ventas.fecha_contabilizacion', [
                Carbon::parse($fecha)->startOfDay(),
                Carbon::parse($fecha)->endOfDay()
            ])
            ->whereNotNull('ventas.fecha_contabilizacion')
            ->select(
                'ventas.id_cliente',
                'ventas.numero_pedido',
                DB::raw("SUM(forma_ventas.precio_venta * ventas.cantidad)::numeric(14,2) AS total_pedido"),
                'ventas.fecha_contabilizacion'
            )
            ->groupBy('ventas.id_cliente', 'ventas.numero_pedido', 'ventas.fecha_contabilizacion')
            ->orderBy('ventas.numero_pedido', 'asc');
        return DataTables::of($pedidos)
            ->addColumn('nro_pedido', function ($row) {
                return $row->numero_pedido;
            })
            ->addColumn('cliente', function ($row) {
                $cliente=Cliente::find($row->id_cliente);
                return $cliente ? $cliente->nombres . ' ' . $cliente->apellidos : $row->nombre_cliente;
            })
            ->addColumn('total_pedido', function ($row) {
                return number_format((float)$row->total_pedido, 2, '.', '');
            })
            ->addColumn('ruta', function ($row) {
                $cliente=Cliente::find($row->id_cliente);
                return $cliente ? $cliente->ruta->nombre_ruta : 'N/A';
            })
            ->addColumn('acciones', function ($row) {
                return "<button type='button' class='btn btn-warning btn-sm'
                            onclick='verDetallePedido({$row->numero_pedido})'>
                            Detalle Pedido <i class='fas fa-eye'></i>
                        </button>";
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }


    public function ventasPorDiaPreventistaDetallePedidosDetalle(string $numeroPedido)
    {
        $ventas = Venta::query()->where('numero_pedido', $numeroPedido);
        /*
        { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'cantidad', name: 'cantidad' },
                    { data: 'precio_unitario', name: 'precio_unitario' },
                    { data: 'total', name: 'total' },
        */
        return DataTables::of($ventas)
            ->addColumn('codigo_producto', function ($row) {
                $producto=Producto::find($row->id_producto);
                return $producto ? $producto->codigo : 'N/A';
            })
            ->addColumn('nombre_producto', function ($row) {
                $producto=Producto::find($row->id_producto);
                return $producto ? $producto->nombre_producto : 'N/A';
            })
            ->addColumn('cantidad', function ($row) {
                return $row->cantidad;
            })
            ->addColumn('precio_unitario', function ($row) {
                $formaVenta = FormaVenta::find($row->id_forma_venta);
                return $formaVenta ? number_format((float)$formaVenta->precio_venta, 2, '.', '') : 'N/A';
            })
            ->addColumn('total', function ($row) {
                $formaVenta = FormaVenta::find($row->id_forma_venta);
                if ($formaVenta) {
                    $total = $formaVenta->precio_venta * $row->cantidad;
                    return number_format((float)$total, 2, '.', '');
                }
                return 'N/A';
            })
            ->make(true);
    }
}
