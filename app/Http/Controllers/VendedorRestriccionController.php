<?php

namespace App\Http\Controllers;

use App\Services\RestriccionVendedorService;
use Illuminate\Http\Request;

class VendedorRestriccionController extends Controller
{
    public function __construct(
        private readonly RestriccionVendedorService $restriccionService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $vendedorId = $user->can('administrador.permisos') && $request->filled('vendedor_id')
            ? (int) $request->vendedor_id
            : (int) $user->id;

        if (! $user->can('administrador.permisos') && $vendedorId !== (int) $user->id) {
            abort(403);
        }

        return response()->json([
            'data' => $this->restriccionService->restriccionesDeVendedor($vendedorId),
        ]);
    }
}
