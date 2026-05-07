<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\FormaVenta;
use App\Models\Producto;
use App\Models\RestriccionVendedor;
use App\Models\User;
use App\Services\RestriccionVendedorService;
use Illuminate\Http\Request;

class RestriccionVendedorController extends Controller
{
    public function __construct(
        private readonly RestriccionVendedorService $restriccionService
    ) {
        $this->middleware('can:administrador.permisos');
    }

    public function reportesIndex()
    {
        $preventistas = User::role('vendedor')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        return view('administrador.reportes.productos_preventistas', compact('preventistas'));
    }

    public function reporteProductosPreventistas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'vendedor_id' => 'nullable|integer|exists:users,id',
        ]);

        $vendedorId = $request->filled('vendedor_id') ? (int) $request->vendedor_id : null;
        if ($vendedorId) {
            $vendedor = User::find($vendedorId);
            if (! $vendedor || ! $vendedor->hasRole('vendedor')) {
                return response()->json(['message' => 'El vendedor seleccionado no es valido.'], 422);
            }
        }

        $reporte = $this->restriccionService->reporteVentasYDespachos(
            $request->fecha_inicio,
            $request->fecha_fin,
            $vendedorId
        );

        return response()->json([
            'data' => $reporte,
        ]);
    }

    public function restriccionesIndex()
    {
        $productos = Producto::query()
            ->where('estado_de_baja', false)
            ->orderBy('nombre_producto')
            ->get(['id', 'codigo', 'nombre_producto', 'detalle_cantidad']);

        $preventistas = User::role('vendedor')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        return view('administrador.restricciones.index', compact('productos', 'preventistas'));
    }

    public function listarRestricciones()
    {
        return response()->json([
            'data' => $this->restriccionService->restriccionesAdministracion(),
        ]);
    }

    public function contextoProducto(int $id)
    {
        $producto = Producto::query()
            ->where('estado_de_baja', false)
            ->findOrFail($id, ['id', 'codigo', 'nombre_producto', 'detalle_cantidad', 'cantidad']);

        $formasVenta = FormaVenta::query()
            ->where('id_producto', $producto->id)
            ->where('activo', true)
            ->orderBy('tipo_venta')
            ->get(['id', 'tipo_venta', 'equivalencia_cantidad', 'precio_venta']);

        return response()->json([
            'producto' => $producto,
            'formas_venta' => $formasVenta,
        ]);
    }

    public function guardarRestriccion(Request $request)
    {
        [$producto, $vendedor, $limite] = $this->validarEntidadYLimite($request);

        $restriccion = RestriccionVendedor::updateOrCreate(
            [
                'producto_id' => $producto->id,
                'vendedor_id' => $vendedor->id,
            ],
            [
                'limite' => $limite,
            ]
        );

        return response()->json([
            'message' => 'Restriccion guardada correctamente.',
            'restriccion_id' => $restriccion->id,
        ], 201);
    }

    public function actualizarRestriccion(Request $request, int $id)
    {
        $actual = RestriccionVendedor::findOrFail($id);
        [$producto, $vendedor, $limite] = $this->validarEntidadYLimite($request);

        $duplicada = RestriccionVendedor::query()
            ->where('producto_id', $producto->id)
            ->where('vendedor_id', $vendedor->id)
            ->where('id', '!=', $actual->id)
            ->first();

        if ($duplicada) {
            $duplicada->limite = $limite;
            $duplicada->save();
            $actual->delete();

            return response()->json([
                'message' => 'Restriccion actualizada correctamente.',
                'restriccion_id' => $duplicada->id,
            ]);
        }

        $actual->update([
            'producto_id' => $producto->id,
            'vendedor_id' => $vendedor->id,
            'limite' => $limite,
        ]);

        return response()->json([
            'message' => 'Restriccion actualizada correctamente.',
            'restriccion_id' => $actual->id,
        ]);
    }

    public function eliminarRestriccion(int $id)
    {
        $restriccion = RestriccionVendedor::findOrFail($id);
        $restriccion->delete();

        return response()->json([
            'message' => 'Restriccion eliminada correctamente.',
        ]);
    }

    private function validarEntidadYLimite(Request $request): array
    {
        $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'vendedor_id' => 'required|integer|exists:users,id',
            'limite' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail((int) $request->producto_id);
        $vendedor = User::findOrFail((int) $request->vendedor_id);

        if (! $vendedor->hasRole('vendedor')) {
            abort(response()->json([
                'message' => 'El usuario seleccionado no tiene rol vendedor.',
            ], 422));
        }

        if ((int) $request->limite > (int) $producto->cantidad) {
            abort(response()->json([
                'message' => 'El limite no puede ser mayor al stock actual del producto ('.$producto->cantidad.' '.$producto->detalle_cantidad.').',
            ], 422));
        }

        return [$producto, $vendedor, (int) $request->limite];
    }
}
