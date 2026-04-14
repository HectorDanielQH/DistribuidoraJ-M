<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'productos_bajo_stock' => 0,
            'pedidos_pendientes' => 0,
            'pedidos_despachados' => 0,
            'asignaciones_pendientes' => 0,
            'mis_ventas_hoy' => 0,
            'ventas_hoy' => 0,
        ];

        if ($user->can('administrador.permisos')) {
            $stats['productos_bajo_stock'] = Producto::where('cantidad', '<=', 15)
                ->where('estado_de_baja', false)
                ->count();

            $stats['pedidos_pendientes'] = Pedido::whereNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->distinct('numero_pedido')
                ->count('numero_pedido');

            $stats['pedidos_despachados'] = Pedido::whereNotNull('fecha_entrega')
                ->where('estado_pedido', false)
                ->distinct('numero_pedido')
                ->count('numero_pedido');
        }

        if ($user->can('vendedor.permisos')) {
            $stats['asignaciones_pendientes'] = Asignacion::where('id_usuario', $user->id)
                ->where('estado_pedido', false)
                ->count();

            $stats['mis_ventas_hoy'] = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->where('ventas.id_usuario', $user->id)
                ->whereDate('ventas.fecha_contabilizacion', now()->toDateString())
                ->sum(DB::raw('ventas.cantidad * forma_ventas.precio_venta'));
        }

        if ($user->can('contador.permisos')) {
            $stats['ventas_hoy'] = Venta::join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
                ->whereDate('ventas.fecha_contabilizacion', now()->toDateString())
                ->sum(DB::raw('ventas.cantidad * forma_ventas.precio_venta'));
        }

        return view('home', compact('stats'));
    }
}
