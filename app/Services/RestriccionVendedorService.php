<?php

namespace App\Services;

use App\Models\RestriccionVendedor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestriccionVendedorService
{
    public function resumenProductoVendedor(int $vendedorId, int $productoId, ?int $clienteIdExcluir = null): ?array
    {
        if (! $this->tablaDisponible()) {
            return null;
        }

        $restriccion = RestriccionVendedor::query()
            ->with('producto:id,detalle_cantidad,cantidad')
            ->where('vendedor_id', $vendedorId)
            ->where('producto_id', $productoId)
            ->first();

        if (! $restriccion) {
            return null;
        }

        $stockActual = (float) ($restriccion->producto?->cantidad ?? 0);
        $cantidadAsignadaGlobal = $this->cantidadAsignadaPendientePorProductoVendedor(
            $vendedorId,
            $productoId,
            $clienteIdExcluir
        );

        return [
            'detalle_cantidad' => $restriccion->producto?->detalle_cantidad,
            ...$this->formatearResumenRestriccionGeneral(
                (int) $restriccion->limite,
                $stockActual,
                $cantidadAsignadaGlobal
            ),
        ];
    }

    public function restriccionesDeVendedor(int $vendedorId): Collection
    {
        if (! $this->tablaDisponible()) {
            return collect();
        }

        $restricciones = RestriccionVendedor::query()
            ->with('producto:id,codigo,nombre_producto,detalle_cantidad,cantidad')
            ->where('vendedor_id', $vendedorId)
            ->orderBy('producto_id')
            ->get();

        return $restricciones->map(function (RestriccionVendedor $restriccion) {
            return [
                'producto_id' => $restriccion->producto_id,
                'producto_codigo' => $restriccion->producto?->codigo,
                'producto_nombre' => $restriccion->producto?->nombre_producto,
                'detalle_cantidad' => $restriccion->producto?->detalle_cantidad,
                ...$this->formatearResumenRestriccionGeneral(
                    (int) $restriccion->limite,
                    (float) ($restriccion->producto?->cantidad ?? 0),
                    $this->cantidadAsignadaPendientePorProductoVendedor(
                        (int) $restriccion->vendedor_id,
                        (int) $restriccion->producto_id
                    )
                ),
            ];
        })->values();
    }

    public function restriccionesAdministracion(): Collection
    {
        if (! $this->tablaDisponible()) {
            return collect();
        }

        $restricciones = RestriccionVendedor::query()
            ->with([
                'producto:id,codigo,nombre_producto,detalle_cantidad,cantidad',
                'vendedor:id,nombres,apellido_paterno,apellido_materno',
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return $restricciones->map(function (RestriccionVendedor $restriccion) {
            return [
                'id' => $restriccion->id,
                'producto_id' => $restriccion->producto_id,
                'vendedor_id' => $restriccion->vendedor_id,
                'producto' => trim(($restriccion->producto?->codigo ? $restriccion->producto->codigo.' - ' : '').($restriccion->producto?->nombre_producto ?? 'Producto')),
                'vendedor' => trim(($restriccion->vendedor?->nombres ?? '').' '.($restriccion->vendedor?->apellido_paterno ?? '').' '.($restriccion->vendedor?->apellido_materno ?? '')),
                'detalle_cantidad' => $restriccion->producto?->detalle_cantidad,
                ...$this->formatearResumenRestriccionGeneral(
                    (int) $restriccion->limite,
                    (float) ($restriccion->producto?->cantidad ?? 0),
                    $this->cantidadAsignadaPendientePorProductoVendedor(
                        (int) $restriccion->vendedor_id,
                        (int) $restriccion->producto_id
                    )
                ),
            ];
        })->values();
    }

    public function reporteVentasYDespachos(string $fechaInicio, string $fechaFin, ?int $vendedorId = null): Collection
    {
        $vendidas = DB::table('ventas')
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->join('productos', 'ventas.id_producto', '=', 'productos.id')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->whereBetween('ventas.fecha_contabilizacion', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->when($vendedorId, fn ($query) => $query->where('ventas.id_usuario', $vendedorId))
            ->select(
                'ventas.id_producto as producto_id',
                'ventas.id_usuario as vendedor_id',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.detalle_cantidad'
            )
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS vendedor")
            ->selectRaw('SUM(ventas.cantidad * forma_ventas.equivalencia_cantidad) AS cantidad_vendida')
            ->groupBy('ventas.id_producto', 'ventas.id_usuario', 'productos.codigo', 'productos.nombre_producto', 'productos.detalle_cantidad', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->get();

        $despachadas = DB::table('pedidos')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->join('productos', 'pedidos.id_producto', '=', 'productos.id')
            ->join('users', 'pedidos.id_usuario', '=', 'users.id')
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->whereBetween('pedidos.fecha_entrega', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->when($vendedorId, fn ($query) => $query->where('pedidos.id_usuario', $vendedorId))
            ->select(
                'pedidos.id_producto as producto_id',
                'pedidos.id_usuario as vendedor_id',
                'productos.codigo',
                'productos.nombre_producto',
                'productos.detalle_cantidad'
            )
            ->selectRaw("TRIM(CONCAT(COALESCE(users.nombres, ''), ' ', COALESCE(users.apellido_paterno, ''), ' ', COALESCE(users.apellido_materno, ''))) AS vendedor")
            ->selectRaw('SUM(pedidos.cantidad * forma_ventas.equivalencia_cantidad) AS cantidad_despachada')
            ->groupBy('pedidos.id_producto', 'pedidos.id_usuario', 'productos.codigo', 'productos.nombre_producto', 'productos.detalle_cantidad', 'users.nombres', 'users.apellido_paterno', 'users.apellido_materno')
            ->get();

        $combinado = [];

        foreach ($vendidas as $fila) {
            $llave = $this->llave($fila->producto_id, $fila->vendedor_id);
            $combinado[$llave] = [
                'producto_id' => (int) $fila->producto_id,
                'vendedor_id' => (int) $fila->vendedor_id,
                'codigo' => $fila->codigo,
                'nombre_producto' => $fila->nombre_producto,
                'detalle_cantidad' => $fila->detalle_cantidad,
                'vendedor' => $fila->vendedor,
                'cantidad_vendida' => (float) $fila->cantidad_vendida,
                'cantidad_despachada' => 0.0,
            ];
        }

        foreach ($despachadas as $fila) {
            $llave = $this->llave($fila->producto_id, $fila->vendedor_id);

            if (! isset($combinado[$llave])) {
                $combinado[$llave] = [
                    'producto_id' => (int) $fila->producto_id,
                    'vendedor_id' => (int) $fila->vendedor_id,
                    'codigo' => $fila->codigo,
                    'nombre_producto' => $fila->nombre_producto,
                    'detalle_cantidad' => $fila->detalle_cantidad,
                    'vendedor' => $fila->vendedor,
                    'cantidad_vendida' => 0.0,
                    'cantidad_despachada' => 0.0,
                ];
            }

            $combinado[$llave]['cantidad_despachada'] = (float) $fila->cantidad_despachada;
        }

        $restricciones = collect();

        if ($this->tablaDisponible()) {
            $restricciones = RestriccionVendedor::query()
                ->whereIn('producto_id', collect($combinado)->pluck('producto_id')->unique())
                ->whereIn('vendedor_id', collect($combinado)->pluck('vendedor_id')->unique())
                ->get()
                ->keyBy(fn (RestriccionVendedor $restriccion) => $this->llave($restriccion->producto_id, $restriccion->vendedor_id));
        }

        return collect($combinado)
            ->map(function (array $fila, string $llave) use ($restricciones) {
                $restriccion = $restricciones->get($llave);
                $limite = (int) ($restriccion?->limite ?? 0);
                $cantidadTotal = $fila['cantidad_vendida'] + $fila['cantidad_despachada'];
                $resumen = $restriccion
                    ? $this->formatearResumenRestriccion($limite, $fila['cantidad_vendida'], $fila['cantidad_despachada'])
                    : [
                        'limite' => null,
                        'cantidad_vendida' => $fila['cantidad_vendida'],
                        'cantidad_despachada' => $fila['cantidad_despachada'],
                        'cantidad_consumida' => $cantidadTotal,
                        'cantidad_disponible' => null,
                        'porcentaje_usado' => null,
                        'estado_limite' => 'sin_limite',
                    ];

                return [
                    'producto' => trim(($fila['codigo'] ? $fila['codigo'].' - ' : '').$fila['nombre_producto']),
                    'vendedor' => $fila['vendedor'],
                    'detalle_cantidad' => $fila['detalle_cantidad'],
                    ...$resumen,
                ];
            })
            ->sortBy(['vendedor', 'producto'])
            ->values();
    }

    private function consumoActualPorProductoVendedor(int $vendedorId, int $productoId): array
    {
        $vendida = (float) DB::table('ventas')
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->where('ventas.id_usuario', $vendedorId)
            ->where('ventas.id_producto', $productoId)
            ->sum(DB::raw('ventas.cantidad * forma_ventas.equivalencia_cantidad'));

        $despachada = (float) DB::table('pedidos')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_usuario', $vendedorId)
            ->where('pedidos.id_producto', $productoId)
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->sum(DB::raw('pedidos.cantidad * forma_ventas.equivalencia_cantidad'));

        return [
            'vendida' => $vendida,
            'despachada' => $despachada,
        ];
    }

    private function consumoActualPorRestricciones(array $vendedorIds, array $productoIds): array
    {
        if (empty($vendedorIds) || empty($productoIds)) {
            return [];
        }

        $vendidas = DB::table('ventas')
            ->join('forma_ventas', 'ventas.id_forma_venta', '=', 'forma_ventas.id')
            ->whereIn('ventas.id_usuario', $vendedorIds)
            ->whereIn('ventas.id_producto', $productoIds)
            ->select('ventas.id_producto as producto_id', 'ventas.id_usuario as vendedor_id')
            ->selectRaw('SUM(ventas.cantidad * forma_ventas.equivalencia_cantidad) AS total')
            ->groupBy('ventas.id_producto', 'ventas.id_usuario')
            ->get();

        $despachadas = DB::table('pedidos')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->whereIn('pedidos.id_usuario', $vendedorIds)
            ->whereIn('pedidos.id_producto', $productoIds)
            ->whereNotNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->select('pedidos.id_producto as producto_id', 'pedidos.id_usuario as vendedor_id')
            ->selectRaw('SUM(pedidos.cantidad * forma_ventas.equivalencia_cantidad) AS total')
            ->groupBy('pedidos.id_producto', 'pedidos.id_usuario')
            ->get();

        $resultado = [];

        foreach ($vendidas as $fila) {
            $resultado[$this->llave($fila->producto_id, $fila->vendedor_id)] = [
                'vendida' => (float) $fila->total,
                'despachada' => 0.0,
            ];
        }

        foreach ($despachadas as $fila) {
            $llave = $this->llave($fila->producto_id, $fila->vendedor_id);

            if (! isset($resultado[$llave])) {
                $resultado[$llave] = [
                    'vendida' => 0.0,
                    'despachada' => 0.0,
                ];
            }

            $resultado[$llave]['despachada'] = (float) $fila->total;
        }

        return $resultado;
    }

    private function formatearResumenRestriccion(int $limite, float $vendida, float $despachada, ?float $stockActual = null): array
    {
        $consumida = $vendida + $despachada;
        $disponible = max($limite - $consumida, 0);
        $disponibleReal = $stockActual !== null ? min($disponible, max($stockActual, 0)) : $disponible;
        $porcentaje = $limite > 0 ? round(($consumida / $limite) * 100, 2) : null;

        $estado = 'ok';
        if ($consumida > $limite) {
            $estado = 'superado';
        } elseif ($porcentaje !== null && $porcentaje >= 80) {
            $estado = 'cerca';
        }

        return [
            'limite' => (float) $limite,
            'cantidad_vendida' => round($vendida, 2),
            'cantidad_despachada' => round($despachada, 2),
            'cantidad_consumida' => round($consumida, 2),
            'cantidad_disponible' => round($disponible, 2),
            'cantidad_disponible_real' => round($disponibleReal, 2),
            'porcentaje_usado' => $porcentaje,
            'estado_limite' => $estado,
        ];
    }

    private function formatearResumenRestriccionGeneral(int $limite, float $stockActual, float $cantidadAsignadaGlobal): array
    {
        $disponiblePorLimite = max((float) $limite - max($cantidadAsignadaGlobal, 0), 0);
        $cantidadDisponibleReal = min($disponiblePorLimite, max($stockActual, 0));
        $porcentajeUsado = $limite > 0 ? round((max($cantidadAsignadaGlobal, 0) / $limite) * 100, 2) : null;

        $estado = 'ok';
        if ($stockActual <= 0) {
            $estado = 'sin_stock';
        } elseif ($cantidadAsignadaGlobal > $limite) {
            $estado = 'superado';
        } elseif ($porcentajeUsado !== null && $porcentajeUsado >= 80) {
            $estado = 'cerca';
        } elseif ($stockActual < $disponiblePorLimite) {
            $estado = 'ajustado_stock';
        }

        return [
            'limite' => (float) $limite,
            'stock_actual' => round($stockActual, 2),
            'cantidad_asignada_global' => round($cantidadAsignadaGlobal, 2),
            'cantidad_disponible_por_limite' => round($disponiblePorLimite, 2),
            'cantidad_disponible_real' => round($cantidadDisponibleReal, 2),
            'limite_efectivo' => round($cantidadDisponibleReal, 2),
            'porcentaje_usado' => $porcentajeUsado,
            'estado_limite' => $estado,
        ];
    }

    private function cantidadAsignadaPendientePorProductoVendedor(int $vendedorId, int $productoId, ?int $clienteIdExcluir = null): float
    {
        return (float) DB::table('pedidos')
            ->join('forma_ventas', 'pedidos.id_forma_venta', '=', 'forma_ventas.id')
            ->where('pedidos.id_usuario', $vendedorId)
            ->where('pedidos.id_producto', $productoId)
            ->whereNull('pedidos.fecha_entrega')
            ->where('pedidos.estado_pedido', false)
            ->when($clienteIdExcluir, fn ($query) => $query->where('pedidos.id_cliente', '!=', $clienteIdExcluir))
            ->sum(DB::raw('pedidos.cantidad * forma_ventas.equivalencia_cantidad'));
    }

    private function llave(int $productoId, int $vendedorId): string
    {
        return $productoId.'-'.$vendedorId;
    }

    private function tablaDisponible(): bool
    {
        return Schema::hasTable('restricciones_vendedor');
    }
}
