<?php

namespace App\Http\Controllers\PreVentista;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class VentasVendedorController extends Controller
{
    public function index(Request $request, DataTables $dataTable)
    {
        if($request->ajax()){
            $user = auth()->user();
            $ventas = Venta::query()
                ->selectRaw('DATE(fecha_contabilizacion) as fecha_contabilizacion')
                ->selectRaw('SUM(cantidad * (SELECT precio_venta FROM forma_ventas WHERE forma_ventas.id = ventas.id_forma_venta)) as total')
                ->groupBy(DB::raw('DATE(fecha_contabilizacion)'))
                ->where('id_usuario', $user->id)
                ->whereNot(function($query){
                    $query->whereBetween('fecha_contabilizacion', ['2025-09-16 00:00:00', '2025-09-16 23:59:59']);
                })
                ->orderBy('fecha_contabilizacion', 'desc');

            return $dataTable->of($ventas)
                ->addColumn('fecha_contabilizacion', function($venta){
                    return Carbon::parse($venta->fecha_contabilizacion)->format('d/m/Y');
                })
                ->editColumn('monto_contabilizado', function($venta){
                    return 'Bs '.number_format($venta->total, 2, '.', ',');
                })
                ->addColumn('acciones', function($venta){
                    return '<a class="btn btn-success btn-action" href="'.route('preventistas.ventas.vendedor.detalleVentasPorFechaContabilizacion',['fecha_contabilizacion' => $venta->fecha_contabilizacion]).'">
                                <i class="fas fa-eye"></i> Ver pedidos
                            </a>';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        $user = auth()->user();
        $resumen = Venta::query()
            ->where('id_usuario', $user->id)
            ->whereNot(function($query){
                $query->whereBetween('fecha_contabilizacion', ['2025-09-16 00:00:00', '2025-09-16 23:59:59']);
            })
            ->selectRaw('COUNT(DISTINCT DATE(fecha_contabilizacion)) as dias')
            ->selectRaw('COUNT(DISTINCT numero_pedido) as pedidos')
            ->selectRaw('SUM(cantidad * (SELECT precio_venta FROM forma_ventas WHERE forma_ventas.id = ventas.id_forma_venta)) as total')
            ->selectRaw('MAX(fecha_contabilizacion) as ultima_fecha')
            ->first();

        return view('vendedor.ventas.index_ventas_vendedor', compact('resumen'));
    }

    public function detalleVentasPorFechaContabilizacion(Request $request, string $fecha_contabilizacion)
    {
        if ($request->ajax()) {
            $userId = auth()->id();

            $ventas = DB::table('ventas')
                ->join('forma_ventas', 'forma_ventas.id', '=', 'ventas.id_forma_venta')
                ->join('clientes', 'clientes.id', '=', 'ventas.id_cliente')
                ->leftJoin('rutas', 'rutas.id', '=', 'clientes.ruta_id') // ajusta si tu FK difiere
                ->whereDate('ventas.fecha_contabilizacion', '=', $fecha_contabilizacion)
                ->where('ventas.id_usuario', '=', $userId)
                ->select([
                    'ventas.id_cliente',
                    'ventas.numero_pedido',
                    DB::raw("CONCAT(clientes.nombres,' ',clientes.apellidos) AS cliente"),
                    DB::raw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta"),
                    DB::raw('SUM(ventas.cantidad * forma_ventas.precio_venta) AS sub_total'),
                    DB::raw('MIN(ventas.created_at) AS fecha_pedido'),
                ])
                ->groupBy(
                    'ventas.id_cliente',
                    'ventas.numero_pedido',
                    'clientes.nombres',
                    'clientes.apellidos',
                    'rutas.nombre_ruta'
                )
                ->orderByRaw('MIN(ventas.created_at) DESC');

            return DataTables::of($ventas)
                ->editColumn('cliente', fn($r) => $r->cliente ?? 'N/A')
                ->editColumn('ruta', fn($r) => $r->ruta ?? 'N/A')
                ->editColumn('numero_pedido', fn($r) => $r->numero_pedido ?? 'N/A')
                ->editColumn('sub_total', fn($r) => number_format((float)$r->sub_total, 2, '.', ',') . ' Bs.-')
                ->editColumn('fecha_pedido', fn($r) => Carbon::parse($r->fecha_pedido)->format('Y-m-d H:i'))
                ->addColumn('acciones', function ($r) {
                    return '<button class="btn btn-success btn-action" data-id-cliente="' . $r->id_cliente . '" data-numero-pedido="' . $r->numero_pedido . '" onclick="verDetalleVenta(this)">
                                <i class="fas fa-eye"></i> Ver productos
                            </button>';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('vendedor.ventas.show_ventas_contabilizadas', compact('fecha_contabilizacion'));
    }
}
