<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Rutas;
use App\Models\User;
use App\Models\VentaMayorista;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class VentaMayoristaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index()
    {
        $mayoristas = $this->mayoristasUsuarios();
        $rutas = Rutas::orderBy('nombre_ruta')->get();

        return view('administrador.mayoristas.index', compact('mayoristas', 'rutas'));
    }

    public function resumen(Request $request)
    {
        $base = $this->ventasBase($request);

        $resumen = (clone $base)
            ->selectRaw('COUNT(DISTINCT ventas_mayoristas.numero_venta) AS ventas')
            ->selectRaw('COUNT(DISTINCT ventas_mayoristas.id_cliente) AS clientes')
            ->selectRaw('COUNT(DISTINCT ventas_mayoristas.id_usuario) AS mayoristas')
            ->selectRaw('COALESCE(SUM(ventas_mayoristas.cantidad * forma_ventas.equivalencia_cantidad), 0) AS unidades')
            ->selectRaw('COALESCE(SUM(ventas_mayoristas.cantidad * ventas_mayoristas.precio_unitario), 0) AS total')
            ->first();

        $ticketPromedio = ((int) ($resumen->ventas ?? 0)) > 0
            ? ((float) $resumen->total / (int) $resumen->ventas)
            : 0;

        return response()->json([
            'ventas' => (int) ($resumen->ventas ?? 0),
            'clientes' => (int) ($resumen->clientes ?? 0),
            'mayoristas' => (int) ($resumen->mayoristas ?? 0),
            'unidades' => (float) ($resumen->unidades ?? 0),
            'total' => round((float) ($resumen->total ?? 0), 2),
            'ticket_promedio' => round($ticketPromedio, 2),
        ]);
    }

    public function data(Request $request, DataTables $dataTables)
    {
        $query = $this->ventasBase($request)
            ->selectRaw('ventas_mayoristas.numero_venta')
            ->selectRaw('DATE(MIN(ventas_mayoristas.fecha_venta)) AS fecha_venta')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS mayorista")
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('COALESCE(SUM(ventas_mayoristas.cantidad * forma_ventas.equivalencia_cantidad), 0) AS unidades')
            ->selectRaw('COALESCE(SUM(ventas_mayoristas.cantidad * ventas_mayoristas.precio_unitario), 0) AS total')
            ->groupBy('ventas_mayoristas.numero_venta', 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('ventas_mayoristas.numero_venta');

        return $dataTables->eloquent($query)
            ->editColumn('fecha_venta', fn ($row) => date('d/m/Y', strtotime($row->fecha_venta)))
            ->editColumn('total', fn ($row) => round((float) $row->total, 2))
            ->addColumn('acciones', function ($row) {
                $editarUrl = route('mayoristas.pedidos.index', ['venta' => $row->numero_venta]);
                return '<div class="admin-actions">
                    <button type="button" class="btn btn-info btn-sm btn-ver-mayorista" data-venta="' . $row->numero_venta . '">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <a href="' . $editarUrl . '" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function detalle(string $numeroVenta)
    {
        $lineas = VentaMayorista::query()
            ->join('clientes', 'ventas_mayoristas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->join('users', 'ventas_mayoristas.id_usuario', '=', 'users.id')
            ->join('productos', 'ventas_mayoristas.id_producto', '=', 'productos.id')
            ->join('forma_ventas', 'ventas_mayoristas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas_mayoristas.numero_venta', $numeroVenta)
            ->select(
                'ventas_mayoristas.numero_venta',
                'ventas_mayoristas.fecha_venta',
                'ventas_mayoristas.cantidad',
                'ventas_mayoristas.precio_unitario',
                'clientes.codigo_cliente',
                'clientes.nombres as cliente_nombres',
                'clientes.apellidos as cliente_apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'rutas.nombre_ruta',
                'users.nombres as user_nombres',
                'users.apellido_paterno',
                'users.apellido_materno',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.detalle_cantidad',
                'forma_ventas.tipo_venta',
                'forma_ventas.equivalencia_cantidad'
            )
            ->orderBy('productos.nombre_producto')
            ->get();

        abort_if($lineas->isEmpty(), 404, 'Venta mayorista no encontrada.');

        $meta = $lineas->first();

        $items = $lineas->map(function ($item) {
            return [
                'codigo' => $item->codigo,
                'producto' => $item->nombre_producto,
                'forma_venta' => $item->tipo_venta,
                'cantidad' => (int) $item->cantidad,
                'unidades' => (int) $item->cantidad * (int) $item->equivalencia_cantidad,
                'precio_unitario' => round((float) $item->precio_unitario, 2),
                'subtotal' => round((float) $item->precio_unitario * (int) $item->cantidad, 2),
            ];
        });

        return response()->json([
            'venta' => [
                'numero' => $meta->numero_venta,
                'fecha' => date('d/m/Y', strtotime($meta->fecha_venta)),
                'cliente' => trim($meta->cliente_nombres . ' ' . $meta->cliente_apellidos),
                'codigo_cliente' => $meta->codigo_cliente,
                'celular' => $meta->celular ?: 'N/A',
                'direccion' => trim(($meta->calle_avenida ?: '') . ' ' . ($meta->zona_barrio ?: '')),
                'ruta' => $meta->nombre_ruta ?: 'Sin ruta',
                'mayorista' => trim($meta->user_nombres . ' ' . $meta->apellido_paterno . ' ' . $meta->apellido_materno),
            ],
            'items' => $items,
            'total' => round($items->sum('subtotal'), 2),
            'unidades' => $items->sum('unidades'),
        ]);
    }

    public function pdf(Request $request)
    {
        $ventas = $this->ventasBase($request)
            ->selectRaw('ventas_mayoristas.numero_venta')
            ->selectRaw('DATE(MIN(ventas_mayoristas.fecha_venta)) AS fecha_venta')
            ->selectRaw("TRIM(CONCAT(COALESCE(clientes.nombres, ''), ' ', COALESCE(clientes.apellidos, ''))) AS cliente")
            ->selectRaw("COALESCE(rutas.nombre_ruta, 'Sin ruta') AS ruta")
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS mayorista")
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('COALESCE(SUM(ventas_mayoristas.cantidad * ventas_mayoristas.precio_unitario), 0) AS total')
            ->groupBy('ventas_mayoristas.numero_venta', 'clientes.nombres', 'clientes.apellidos', 'rutas.nombre_ruta', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderByDesc('ventas_mayoristas.numero_venta')
            ->get();

        $detalles = $this->ventasBase($request)
            ->join('productos', 'ventas_mayoristas.id_producto', '=', 'productos.id')
            ->select(
                'ventas_mayoristas.numero_venta',
                'ventas_mayoristas.cantidad',
                'ventas_mayoristas.precio_unitario',
                'productos.codigo',
                'productos.nombre_producto',
                'forma_ventas.tipo_venta',
                'forma_ventas.equivalencia_cantidad'
            )
            ->orderBy('ventas_mayoristas.numero_venta')
            ->get()
            ->groupBy('numero_venta');

        $resumen = [
            'ventas' => $ventas->count(),
            'items' => $ventas->sum('items'),
            'total' => $ventas->sum('total'),
        ];

        $pdf = Pdf::loadView('administrador.mayoristas.pdf', [
            'ventas' => $ventas,
            'detalles' => $detalles,
            'resumen' => $resumen,
        ]);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-ventas-mayoristas.pdf');
    }

    private function ventasBase(Request $request)
    {
        $query = VentaMayorista::query()
            ->join('forma_ventas', 'ventas_mayoristas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('clientes', 'ventas_mayoristas.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->join('users', 'ventas_mayoristas.id_usuario', '=', 'users.id');

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('ventas_mayoristas.fecha_venta', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('ventas_mayoristas.fecha_venta', '<=', $request->fecha_fin);
        }

        $rutaIds = collect((array) $request->input('ruta_id', []))->filter()->values();
        if ($rutaIds->isNotEmpty()) {
            $query->whereIn('clientes.ruta_id', $rutaIds);
        }

        $mayoristaIds = collect((array) $request->input('mayorista_id', []))->filter()->values();
        if ($mayoristaIds->isNotEmpty()) {
            $query->whereIn('ventas_mayoristas.id_usuario', $mayoristaIds);
        }

        if ($request->filled('cliente')) {
            $cliente = trim((string) $request->cliente);
            $query->where(function ($q) use ($cliente) {
                $q->where('clientes.codigo_cliente', 'ilike', "%{$cliente}%")
                    ->orWhere('clientes.nombres', 'ilike', "%{$cliente}%")
                    ->orWhere('clientes.apellidos', 'ilike', "%{$cliente}%");
            });
        }

        return $query;
    }

    private function mayoristasUsuarios()
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['mayorista', 'mayoristas']);
            })
            ->orderBy('nombres')
            ->get();
    }
}
