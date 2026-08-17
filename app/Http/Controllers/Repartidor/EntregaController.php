<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Rutas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntregaController extends Controller
{
    public function index(Request $request)
    {
        $fechaEntrega = $request->input('fecha_entrega', now()->toDateString());
        $rutas = Rutas::orderBy('nombre_ruta')->get(['id', 'nombre_ruta']);
        $preventistas = User::role('vendedor')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        $resumen = $this->resumenDespachos([
            'ruta_ids' => [],
            'preventista_ids' => [],
            'fecha_entrega' => $fechaEntrega,
        ]);

        return view('repartidor.entregas.index', compact('fechaEntrega', 'rutas', 'preventistas', 'resumen'));
    }

    public function datos(Request $request)
    {
        $filtros = $this->normalizarFiltros($request);
        $pedidos = $this->queryPedidosDespachados($filtros)
            ->orderByRaw('MAX(pedidos.fecha_entrega) DESC')
            ->orderBy('pedidos.numero_pedido', 'asc')
            ->limit(500)
            ->get()
            ->map(fn ($pedido) => $this->mapearPedido($pedido))
            ->values();

        return response()->json([
            'resumen' => $this->resumenDespachos($filtros),
            'pedidos' => $pedidos,
        ]);
    }

    public function mapa(Request $request)
    {
        $filtros = $this->normalizarFiltros($request);

        $ubicaciones = $this->queryPedidosDespachados($filtros)
            ->whereNotNull('clientes.latitud')
            ->whereNotNull('clientes.longitud')
            ->orderByRaw('MAX(pedidos.fecha_entrega) DESC')
            ->limit(500)
            ->get()
            ->map(function ($pedido) {
                $data = $this->mapearPedido($pedido);
                $data['latitud'] = (float) $pedido->latitud;
                $data['longitud'] = (float) $pedido->longitud;

                return $data;
            })
            ->values();

        return response()->json([
            'ubicaciones' => $ubicaciones,
        ]);
    }

    public function opciones(Request $request)
    {
        $filtros = $this->normalizarFiltros($request);

        return response()->json([
            'rutas' => $this->opcionesRutas($filtros),
            'preventistas' => $this->opcionesPreventistas($filtros),
        ]);
    }

    public function marcarEntregado(Request $request, string $numeroPedido)
    {
        try {
            $cantidad = DB::transaction(function () use ($numeroPedido) {
                $pedidos = Pedido::where('numero_pedido', $numeroPedido)
                    ->whereNotNull('fecha_entrega')
                    ->where('estado_pedido', false)
                    ->whereNull('entregado_repartidor_at')
                    ->lockForUpdate()
                    ->get();

                if ($pedidos->isEmpty()) {
                    abort(404, 'El pedido no existe, ya fue entregado o ya no esta despachado pendiente.');
                }

                Pedido::whereIn('id', $pedidos->pluck('id'))->update([
                    'entregado_repartidor_at' => now(),
                    'entregado_repartidor_por' => auth()->id(),
                    'updated_at' => now(),
                ]);

                return $pedidos->count();
            });

            return response()->json([
                'message' => 'Pedido marcado como entregado correctamente.',
                'items_actualizados' => $cantidad,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al marcar pedido como entregado por repartidor.', [
                'numero_pedido' => $numeroPedido,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function programarReparto(Request $request, string $numeroPedido)
    {
        $data = $request->validate([
            'fecha_reparto' => ['required', 'date'],
        ], [
            'fecha_reparto.required' => 'Debes elegir una fecha para el reparto.',
            'fecha_reparto.date' => 'La fecha de reparto no es valida.',
        ]);

        try {
            $cantidad = DB::transaction(function () use ($numeroPedido, $data) {
                $pedidos = Pedido::where('numero_pedido', $numeroPedido)
                    ->whereNotNull('fecha_entrega')
                    ->where('estado_pedido', false)
                    ->whereNull('entregado_repartidor_at')
                    ->lockForUpdate()
                    ->get();

                if ($pedidos->isEmpty()) {
                    abort(404, 'El pedido no existe, ya fue entregado o ya no esta despachado pendiente.');
                }

                Pedido::whereIn('id', $pedidos->pluck('id'))->update([
                    'reparto_programado_fecha' => $data['fecha_reparto'],
                    'reparto_programado_por' => auth()->id(),
                    'updated_at' => now(),
                ]);

                return $pedidos->count();
            });

            return response()->json([
                'message' => 'Pedido programado correctamente.',
                'items_actualizados' => $cantidad,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al programar pedido para reparto.', [
                'numero_pedido' => $numeroPedido,
                'user_id' => auth()->id(),
                'fecha_reparto' => $data['fecha_reparto'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function normalizarFiltros(Request $request): array
    {
        return [
            'ruta_ids' => collect((array) $request->input('ruta_id', []))
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->values()
                ->all(),
            'preventista_ids' => collect((array) $request->input('preventista_id', []))
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->values()
                ->all(),
            'fecha_entrega' => $request->filled('fecha_reparto')
                ? $request->fecha_reparto
                : ($request->filled('fecha_entrega') ? $request->fecha_entrega : null),
            'numero_pedido' => $request->filled('numero_pedido') ? trim((string) $request->numero_pedido) : null,
            'estado_entrega' => $request->input('estado_entrega', 'pendiente'),
        ];
    }

    private function queryPedidosDespachados(array $filtros)
    {
        $query = Pedido::query()
            ->join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->leftJoin('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->leftJoin('users', 'pedidos.id_usuario', '=', 'users.id')
            ->leftJoin('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->select(
                'pedidos.numero_pedido',
                'pedidos.id_cliente',
                'pedidos.id_usuario',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'clientes.referencia_direccion',
                'clientes.latitud',
                'clientes.longitud',
                'rutas.nombre_ruta',
                'users.nombres as preventista_nombres',
                'users.apellido_paterno as preventista_apellido_paterno',
                'users.apellido_materno as preventista_apellido_materno'
            )
            ->selectRaw('MIN(pedidos.fecha_pedido) AS fecha_pedido')
            ->selectRaw('MAX(pedidos.fecha_entrega) AS fecha_entrega')
            ->selectRaw('MAX(pedidos.entregado_repartidor_at) AS entregado_repartidor_at')
            ->selectRaw('MAX(pedidos.reparto_programado_fecha) AS reparto_programado_fecha')
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('SUM(pedidos.cantidad * COALESCE(pedidos.precio_unitario, forma_ventas.precio_venta, 0)) AS monto_estimado')
            ->groupBy(
                'pedidos.numero_pedido',
                'pedidos.id_cliente',
                'pedidos.id_usuario',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.celular',
                'clientes.calle_avenida',
                'clientes.zona_barrio',
                'clientes.referencia_direccion',
                'clientes.latitud',
                'clientes.longitud',
                'rutas.nombre_ruta',
                'users.nombres',
                'users.apellido_paterno',
                'users.apellido_materno'
            );

        if (! empty($filtros['ruta_ids'])) {
            $query->whereIn('clientes.ruta_id', $filtros['ruta_ids']);
        }

        if (! empty($filtros['preventista_ids'])) {
            $query->whereIn('pedidos.id_usuario', $filtros['preventista_ids']);
        }

        if (! empty($filtros['numero_pedido'])) {
            $query->whereRaw('CAST(pedidos.numero_pedido AS TEXT) ILIKE ?', ['%'.$filtros['numero_pedido'].'%']);
        }

        if (($filtros['estado_entrega'] ?? 'pendiente') === 'pendiente') {
            $query->whereNull('pedidos.entregado_repartidor_at');
            if (! empty($filtros['fecha_entrega'])) {
                $query->where(function ($q) use ($filtros) {
                    $q->whereDate('pedidos.reparto_programado_fecha', $filtros['fecha_entrega'])
                        ->orWhere(function ($sinProgramar) use ($filtros) {
                            $sinProgramar->whereNull('pedidos.reparto_programado_fecha')
                                ->whereDate('pedidos.fecha_entrega', '<=', $filtros['fecha_entrega']);
                        });
                });
            }
        } elseif (($filtros['estado_entrega'] ?? null) === 'entregado') {
            $query->whereNotNull('pedidos.entregado_repartidor_at');
            if (! empty($filtros['fecha_entrega'])) {
                $query->whereDate('pedidos.entregado_repartidor_at', $filtros['fecha_entrega']);
            }
        } elseif (! empty($filtros['fecha_entrega'])) {
            $query->where(function ($q) use ($filtros) {
                $q->whereDate('pedidos.reparto_programado_fecha', $filtros['fecha_entrega'])
                    ->orWhereDate('pedidos.fecha_entrega', $filtros['fecha_entrega'])
                    ->orWhereDate('pedidos.entregado_repartidor_at', $filtros['fecha_entrega']);
            });
        }

        return $query;
    }

    private function baseOpcionesDespacho(array $filtros, ?string $ignorarFiltro = null)
    {
        $query = Pedido::query()
            ->join('clientes', 'pedidos.id_cliente', '=', 'clientes.id')
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false);

        if ($ignorarFiltro !== 'ruta' && ! empty($filtros['ruta_ids'])) {
            $query->whereIn('clientes.ruta_id', $filtros['ruta_ids']);
        }

        if ($ignorarFiltro !== 'preventista' && ! empty($filtros['preventista_ids'])) {
            $query->whereIn('pedidos.id_usuario', $filtros['preventista_ids']);
        }

        if (! empty($filtros['numero_pedido'])) {
            $query->whereRaw('CAST(pedidos.numero_pedido AS TEXT) ILIKE ?', ['%'.$filtros['numero_pedido'].'%']);
        }

        if (($filtros['estado_entrega'] ?? 'pendiente') === 'pendiente') {
            $query->whereNull('pedidos.entregado_repartidor_at');
            if (! empty($filtros['fecha_entrega'])) {
                $query->where(function ($q) use ($filtros) {
                    $q->whereDate('pedidos.reparto_programado_fecha', $filtros['fecha_entrega'])
                        ->orWhere(function ($sinProgramar) use ($filtros) {
                            $sinProgramar->whereNull('pedidos.reparto_programado_fecha')
                                ->whereDate('pedidos.fecha_entrega', '<=', $filtros['fecha_entrega']);
                        });
                });
            }
        } elseif (($filtros['estado_entrega'] ?? null) === 'entregado') {
            $query->whereNotNull('pedidos.entregado_repartidor_at');
            if (! empty($filtros['fecha_entrega'])) {
                $query->whereDate('pedidos.entregado_repartidor_at', $filtros['fecha_entrega']);
            }
        } elseif (! empty($filtros['fecha_entrega'])) {
            $query->where(function ($q) use ($filtros) {
                $q->whereDate('pedidos.reparto_programado_fecha', $filtros['fecha_entrega'])
                    ->orWhereDate('pedidos.fecha_entrega', $filtros['fecha_entrega'])
                    ->orWhereDate('pedidos.entregado_repartidor_at', $filtros['fecha_entrega']);
            });
        }

        return $query;
    }

    private function opcionesRutas(array $filtros): array
    {
        return $this->baseOpcionesDespacho($filtros, 'ruta')
            ->join('rutas', 'clientes.ruta_id', '=', 'rutas.id')
            ->whereNotNull('clientes.ruta_id')
            ->select('rutas.id', 'rutas.nombre_ruta')
            ->groupBy('rutas.id', 'rutas.nombre_ruta')
            ->orderBy('rutas.nombre_ruta')
            ->get()
            ->map(fn ($ruta) => [
                'id' => (string) $ruta->id,
                'text' => $ruta->nombre_ruta,
            ])
            ->values()
            ->all();
    }

    private function opcionesPreventistas(array $filtros): array
    {
        return $this->baseOpcionesDespacho($filtros, 'preventista')
            ->join('users', 'pedidos.id_usuario', '=', 'users.id')
            ->select('users.id', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->groupBy('users.id', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->orderBy('users.nombres')
            ->get()
            ->map(function ($preventista) {
                return [
                    'id' => (string) $preventista->id,
                    'text' => trim(($preventista->nombres ?? '') . ' ' . ($preventista->apellido_paterno ?? '') . ' ' . ($preventista->apellido_materno ?? '')),
                ];
            })
            ->values()
            ->all();
    }

    private function resumenDespachos(array $filtros): array
    {
        $base = $this->queryPedidosDespachados($filtros);

        $totales = DB::query()
            ->fromSub($base, 'despachos')
            ->selectRaw('COUNT(*) AS pedidos')
            ->selectRaw('COALESCE(SUM(items), 0) AS items')
            ->selectRaw('COALESCE(SUM(monto_estimado), 0) AS monto')
            ->selectRaw('SUM(CASE WHEN latitud IS NOT NULL AND longitud IS NOT NULL THEN 1 ELSE 0 END) AS con_ubicacion')
            ->first();

        $pedidos = (int) ($totales->pedidos ?? 0);
        $conUbicacion = (int) ($totales->con_ubicacion ?? 0);

        return [
            'pedidos' => $pedidos,
            'items' => (int) ($totales->items ?? 0),
            'monto' => (float) ($totales->monto ?? 0),
            'con_ubicacion' => $conUbicacion,
            'sin_ubicacion' => max($pedidos - $conUbicacion, 0),
        ];
    }

    private function mapearPedido($pedido): array
    {
        $preventista = trim(($pedido->preventista_nombres ?? '') . ' ' . ($pedido->preventista_apellido_paterno ?? '') . ' ' . ($pedido->preventista_apellido_materno ?? ''));
        $direccion = trim(($pedido->calle_avenida ?? 'Sin direccion') . ' - ' . ($pedido->zona_barrio ?? 'Sin zona'));

        return [
            'numero_pedido' => $pedido->numero_pedido,
            'numero_pedido_formateado' => '#' . str_pad((string) $pedido->numero_pedido, 6, '0', STR_PAD_LEFT),
            'cliente' => trim(($pedido->nombres ?? '') . ' ' . ($pedido->apellidos ?? '')),
            'celular' => $pedido->celular ?: 'Sin celular',
            'direccion' => $direccion,
            'referencia' => $pedido->referencia_direccion ?: 'Sin referencia',
            'ruta' => $pedido->nombre_ruta ?: 'Sin ruta',
            'preventista' => $preventista ?: 'Sin preventista',
            'fecha_pedido' => $pedido->fecha_pedido ? date('d/m/Y H:i', strtotime($pedido->fecha_pedido)) : 'N/A',
            'fecha_entrega' => $pedido->fecha_entrega ? date('d/m/Y H:i', strtotime($pedido->fecha_entrega)) : 'N/A',
            'items' => (int) $pedido->items,
            'monto_estimado' => (float) $pedido->monto_estimado,
            'tiene_ubicacion' => $pedido->latitud !== null && $pedido->longitud !== null,
            'latitud' => $pedido->latitud !== null ? (float) $pedido->latitud : null,
            'longitud' => $pedido->longitud !== null ? (float) $pedido->longitud : null,
            'entregado' => $pedido->entregado_repartidor_at !== null,
            'entregado_repartidor_at' => $pedido->entregado_repartidor_at ? date('d/m/Y H:i', strtotime($pedido->entregado_repartidor_at)) : null,
            'reparto_programado_fecha' => $pedido->reparto_programado_fecha ? date('Y-m-d', strtotime($pedido->reparto_programado_fecha)) : null,
            'reparto_programado_texto' => $pedido->reparto_programado_fecha ? date('d/m/Y', strtotime($pedido->reparto_programado_fecha)) : 'Sin programar',
        ];
    }
}
