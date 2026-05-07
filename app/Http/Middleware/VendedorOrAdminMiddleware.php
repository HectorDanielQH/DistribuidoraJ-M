<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendedorOrAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->can('administrador.permisos') && ! $user->can('vendedor.permisos'))) {
            abort(403);
        }

        return $next($request);
    }
}
