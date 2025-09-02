{{-- resources/views/administrador/pdf/pdf_despachar.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Despachados</title>
    <style>
        /* ===== Base minimalista (PDF friendly) ===== */
        *{box-sizing:border-box}
        html,body{font-family:DejaVu Sans, Arial, sans-serif;font-size:10px;color:#222;line-height:1.35}
        h1,h2,h3,h4,h5{margin:0 0 .5rem;font-weight:700}
        h2{font-size:14px}
        h4{font-size:12px}
        small{font-size:9px}
        img{display:inline-block}

        /* Espaciados */
        .m-0{margin:0}.mb-1{margin-bottom:6px}.mb-2{margin-bottom:10px}.mb-3{margin-bottom:14px}.mt-2{margin-top:10px}
        .p-0{padding:0}.p-1{padding:4px}.p-2{padding:8px}

        /* Texto */
        .text-center{text-align:center}.text-right{text-align:right}.muted{color:#777}.fw-bold{font-weight:700}
        .nowrap{white-space:nowrap}

        /* Anchos */
        .w-100{width:100%}.w-50{width:50%}.w-30{width:30%}

        /* Tablas */
        .tbl{width:100%;border-collapse:collapse}
        .tbl th,.tbl td{border:1px solid #aaa;padding:4px;font-size:9px;vertical-align:top}
        .tbl thead th{background:#f4f4f4}

        .tbl-plain{width:100%;border-collapse:collapse}
        .tbl-plain th,.tbl-plain td{border:0;padding:6px 4px;font-size:9.5px}

        /* Bloques */
        .section{border-left:3px solid #007bff;background:#f2f2f2;padding:6px 8px;margin:12px 0}
        .box{border:1px solid #ccc;border-radius:3px;padding:8px}
        .box-md{height:70px}
        .signature{height:45px}

        /* Imágenes */
        .logo{width:100px;height:auto}

        /* Paginación/roturas */
        @page{margin:14mm 12mm}
        table{page-break-inside:auto}
        tr{page-break-inside:avoid;page-break-after:auto}
        .section,.box{page-break-inside:avoid}
    </style>
</head>
<body>

@php
    use App\Models\User;
    use App\Models\Rutas;

    // Agrupar items por número de pedido para evitar O(n^2) en el render
    $itemsPorPedido = [];
    foreach ($pedidos as $p) {
        $itemsPorPedido[$p->numero_pedido][] = $p;
    }

    // Cargar vendedores y rutas en bloque (minimiza queries)
    $vendedorIds = $lista_de_pedidos->pluck('id_vendedor')->filter()->unique()->all();
    $rutaIds     = $lista_de_pedidos->pluck('ruta_id')->filter()->unique()->all();

    $vendedores = User::whereIn('id', $vendedorIds)->get()->keyBy('id');
    $rutas      = Rutas::whereIn('id', $rutaIds)->get()->keyBy('id');

    // Total general
    $total_general = 0;
    foreach ($pedidos as $pp) {
        $linea = $pp->cantidad_pedido * $pp->precio_venta;
        if ($pp->promocion && $pp->descripcion_descuento_porcentaje > 0) {
            $linea -= $linea * ($pp->descripcion_descuento_porcentaje / 100);
        }
        $total_general += $linea;
    }
@endphp

    {{-- Encabezado --}}
    <table class="tbl-plain w-100 mb-2">
        <tr>
            <td class="w-50">
                <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo"><br>
                <small class="muted">Distribuidora H&amp;J</small>
            </td>
            <td class="w-50 text-right">
                <h2>Productos Despachados</h2>
                <small class="muted">Fecha de impresión: {{ date('d/m/Y H:i:s') }}</small><br>
                <small class="muted">Versión 1.0</small>
            </td>
        </tr>
    </table>

    {{-- Listado por pedido --}}
    @foreach($lista_de_pedidos as $lista)
        @php
            $items  = $itemsPorPedido[$lista->numero_pedido] ?? [];
            $vend   = $vendedores[$lista->id_vendedor] ?? null;
            $ruta   = $rutas[$lista->ruta_id] ?? null;

            $totalPedido = 0;
            foreach ($items as $it) {
                $linea = $it->cantidad_pedido * $it->precio_venta;
                if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                    $linea -= $linea * ($it->descripcion_descuento_porcentaje / 100);
                }
                $totalPedido += $linea;
            }
        @endphp

        <table class="tbl-plain w-100">
            <thead>
                <tr>
                    <th colspan="4" class="p-2" style="border-bottom:1px solid #000;">
                        Pedido #{{ $lista->numero_pedido }} —
                        Vendido por: {{ $vend ? ($vend->nombres.' '.$vend->apellido_paterno.' '.$vend->apellido_materno) : 'No asignado' }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="w-30 p-2 fw-bold">Fecha de Pedido:</td>
                    <td class="p-2">{{ $lista->fecha_pedido }}</td>
                    <td class="p-2 fw-bold">Cliente:</td>
                    <td class="p-2">{{ $lista->nombres }} {{ $lista->apellidos }}</td>
                </tr>
                <tr>
                    <td class="p-2 fw-bold">Celular:</td>
                    <td class="p-2">{{ $lista->celular }}</td>
                    <td class="p-2 fw-bold">Dirección:</td>
                    <td class="p-2">{{ $lista->calle_avenida }}</td>
                </tr>
                <tr>
                    <td class="p-2 fw-bold">Zona referencial:</td>
                    <td class="p-2">{{ $lista->zona_barrio }}</td>
                    <td class="p-2 fw-bold">Ruta Asignada:</td>
                    <td class="p-2">{{ $ruta ? $ruta->nombre_ruta : 'No asignada' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="tbl mt-2 mb-2">
            <thead>
                <tr>
                    <th>Cod.</th>
                    <th>Nombre del Producto</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Precio U.</th>
                    <th>Promoción</th>
                    <th class="text-right">Subtotal (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $it)
                    @php
                        $sub = $it->cantidad_pedido * $it->precio_venta;
                        if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                            $sub -= $sub * ($it->descripcion_descuento_porcentaje / 100);
                        }
                    @endphp
                    <tr>
                        <td>{{ $it->codigo }}</td>
                        <td>{{ $it->nombre_producto }}</td>
                        <td class="text-right">{{ $it->cantidad_pedido }} ({{ $it->tipo_venta }})</td>
                        <td class="text-right">{{ number_format($it->precio_venta, 2, ',', '.') }}</td>
                        <td>
                            @if($it->promocion)
                                @if($it->descripcion_descuento_porcentaje > 0)
                                    Descuento: {{ $it->descripcion_descuento_porcentaje }}%
                                @endif
                                @if(!empty($it->descripcion_regalo))
                                    {!! $it->descripcion_descuento_porcentaje > 0 ? '<br>' : '' !!}
                                    Regalo: {{ $it->descripcion_regalo }}
                                @endif
                                @if($it->descripcion_descuento_porcentaje == 0 && empty($it->descripcion_regalo))
                                    No aplica
                                @endif
                            @else
                                No aplica
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($sub, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5" class="text-right fw-bold">Total del Pedido (Bs):</td>
                    <td class="text-right fw-bold">{{ number_format($totalPedido, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Observaciones por pedido --}}
        <div class="box box-md mb-3"></div>

    @endforeach

    {{-- Total general --}}
    <div class="section">
        <h4 class="mb-1">Monto Total Estimado a Recaudar: {{ number_format($total_general, 2, ',', '.') }} Bs</h4>
        <small class="muted">Este total corresponde a todos los pedidos listados en el documento.</small>
    </div>

    {{-- Firmas --}}
    <table class="tbl-plain w-100 mt-2">
        <tr>
            <td class="w-50 text-center">
                <h4>Firma del Repartidor</h4>
                <div class="box signature mt-2"></div>
            </td>
            <td class="w-50 text-center">
                <h4>Firma del Despachador</h4>
                <div class="box signature mt-2"></div>
            </td>
        </tr>
    </table>

    <table class="tbl-plain w-100 mt-2">
        <tr>
            <td class="w-50 text-center">
                <h4>Entrega de Ingresos (Repartidor)</h4>
                <div class="box signature mt-2"></div>
            </td>
            <td class="w-50 text-center">
                <h4>Recepción de Ingresos (Recepcionista)</h4>
                <div class="box signature mt-2"></div>
            </td>
        </tr>
    </table>

    {{-- Observaciones finales --}}
    <div class="mt-2">
        <h4>Observaciones:</h4>
        <div class="box box-md mt-2"></div>
    </div>

    <p class="text-center muted mt-2">
        Este documento es una representación impresa de los pedidos despachados y debe ser guardado para futuras referencias.
    </p>
    <p class="text-center muted">Distribuidora H&amp;J — Todos los derechos reservados.</p>
    <p class="text-center muted">Generado por el sistema de gestión de pedidos H&amp;J.</p>

</body>
</html>
