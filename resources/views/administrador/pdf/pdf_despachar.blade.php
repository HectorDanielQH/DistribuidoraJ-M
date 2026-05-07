<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de carga y despacho</title>
    <style>
        @font-face {
            font-family: 'ReporteSans';
            font-style: normal;
            font-weight: 400;
            src: url("{{ base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'ReporteSans';
            font-style: normal;
            font-weight: 700;
            src: url("{{ base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf') }}") format('truetype');
        }
        * {
            box-sizing: border-box;
            font-family: 'ReporteSans' !important;
        }
        @page { margin: 6mm 7mm; }
        body {
            color: #1f2937;
            font-size: 10px;
            line-height: 1.24;
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            width: 32px;
        }
        .title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .title-sub {
            margin-top: 1px;
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .date-box {
            width: 94px;
            border: 1px solid #d1d5db;
            padding: 4px 5px;
            text-align: right;
            font-size: 8px;
        }
        .date-box strong {
            display: block;
            font-size: 11px;
        }
        .divider {
            height: 1px;
            background: #111827;
            margin: 4px 0 6px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .summary td {
            width: 33.33%;
            border: 1px solid #d1d5db;
            padding: 4px 3px;
            text-align: center;
        }
        .summary strong {
            display: block;
            font-size: 11px;
        }
        .summary span {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .sheet-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            table-layout: fixed;
        }
        .sheet-row td {
            vertical-align: top;
        }
        .ticket-cell {
            width: 47.5%;
        }
        .cut-space {
            width: 5%;
            border-left: 1px dashed #9ca3af;
            border-right: 1px dashed #9ca3af;
        }
        .ticket {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #9ca3af;
        }
        .ticket-title td {
            border-bottom: 1px solid #9ca3af;
            padding: 5px 6px;
            font-size: 9px;
            font-weight: 700;
        }
        .ticket-title-left {
            width: auto;
        }
        .buyer-name {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }
        .buyer-note {
            display: block;
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .order-no {
            width: 62px;
            text-align: right;
            white-space: nowrap;
            font-size: 10px;
            vertical-align: middle;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            border-bottom: 1px solid #e5e7eb;
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: top;
            word-break: break-word;
        }
        .meta tr:last-child td {
            border-bottom: 0;
        }
        .meta-label {
            font-weight: 700;
            color: #374151;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items th {
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding: 4px 5px;
            font-size: 8px;
            text-transform: uppercase;
            text-align: left;
        }
        .items td {
            border-bottom: 1px solid #eceff1;
            padding: 4px 5px;
            font-size: 8px;
            vertical-align: top;
        }
        .items tr {
            page-break-inside: avoid;
        }
        .items .qty {
            width: 78px;
            text-align: center;
            font-weight: 700;
            font-size: 8px;
        }
        .items .price {
            width: 54px;
            text-align: right;
            white-space: nowrap;
            font-weight: 700;
            font-size: 8px;
        }
        .product-name {
            font-weight: 700;
            font-size: 8px;
        }
        .product-code {
            color: #6b7280;
            font-size: 7px;
        }
        .ticket-footer {
            border-top: 1px solid #9ca3af;
            padding: 5px 6px;
            font-size: 8px;
            text-transform: uppercase;
        }
        .ticket-footer.total {
            text-align: right;
            font-weight: 700;
        }
        .ticket-footer.continua {
            text-align: center;
            color: #6b7280;
            font-weight: 700;
        }
        .empty-cell {
            border: 0;
        }
    </style>
</head>
<body>
@php
    use App\Models\Rutas;
    use App\Models\User;

    $itemsPorPedido = [];
    foreach ($pedidos as $pedidoItem) {
        $itemsPorPedido[$pedidoItem->numero_pedido][] = $pedidoItem;
    }

    $vendedorIds = $lista_de_pedidos->pluck('id_vendedor')->filter()->unique()->all();
    $rutaIds = $lista_de_pedidos->pluck('ruta_id')->filter()->unique()->all();
    $vendedores = User::whereIn('id', $vendedorIds)->get()->keyBy('id');
    $rutas = Rutas::whereIn('id', $rutaIds)->get()->keyBy('id');

    $totalGeneral = 0;
    $itemsGeneral = 0;
    foreach ($pedidos as $pedidoItem) {
        $linea = $pedidoItem->cantidad_pedido * $pedidoItem->precio_venta;
        if ($pedidoItem->promocion && $pedidoItem->descripcion_descuento_porcentaje > 0) {
            $linea -= $linea * ($pedidoItem->descripcion_descuento_porcentaje / 100);
        }
        $totalGeneral += $linea;
        $itemsGeneral += $pedidoItem->cantidad_pedido;
    }

    $boletas = [];
    $maxItemsPorBoleta = 16;

    foreach ($lista_de_pedidos as $lista) {
        $items = collect($itemsPorPedido[$lista->numero_pedido] ?? []);
        $segmentos = $items->chunk($maxItemsPorBoleta)->values();
        $totalPedido = 0;

        foreach ($items as $it) {
            $linea = $it->cantidad_pedido * $it->precio_venta;
            if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                $linea -= $linea * ($it->descripcion_descuento_porcentaje / 100);
            }
            $totalPedido += $linea;
        }

        $vend = $vendedores[$lista->id_vendedor] ?? null;
        $ruta = $rutas[$lista->ruta_id] ?? null;
        $nombreVendedor = $vend ? trim($vend->nombres.' '.$vend->apellido_paterno.' '.$vend->apellido_materno) : 'No asignado';
        $celularVendedor = $vend && !empty($vend->celular) ? $vend->celular : 'N/A';
        $nombreComprador = trim(($lista->nombres ?? '').' '.($lista->apellidos ?? '')) ?: 'Comprador no disponible';

        foreach ($segmentos as $indice => $segmento) {
            $boletas[] = [
                'pedido' => $lista,
                'items' => $segmento,
                'nombre_comprador' => $nombreComprador,
                'nombre_vendedor' => $nombreVendedor,
                'celular_vendedor' => $celularVendedor,
                'ruta_nombre' => $ruta ? $ruta->nombre_ruta : 'No asignada',
                'total_pedido' => $totalPedido,
                'es_continuacion' => $indice > 0,
                'es_ultimo_segmento' => $indice === ($segmentos->count() - 1),
                'segmento' => $indice + 1,
            ];
        }
    }
@endphp

<table class="header">
    <tr>
        <td style="width:40px;">
            <img src="{{ public_path('images/logo_distribuidora.jpg') }}" class="logo" alt="Logo">
        </td>
        <td>
            <h1 class="title">Reporte de carga y despacho</h1>
            <div class="title-sub">Formato funcional para impresion y corte</div>
        </td>
        <td class="date-box">
            <strong>{{ now()->format('d/m/Y') }}</strong>
            {{ now()->format('H:i') }}
        </td>
    </tr>
</table>

<div class="divider"></div>

<table class="summary">
    <tr>
        <td>
            <strong>{{ count($boletas) }}</strong>
            <span>Boletas</span>
        </td>
        <td>
            <strong>{{ $lista_de_pedidos->count() }}</strong>
            <span>Pedidos</span>
        </td>
        <td>
            <strong>{{ $itemsGeneral }}</strong>
            <span>Items</span>
        </td>
        <td>
            <strong>Bs {{ number_format($totalGeneral, 2, '.', ',') }}</strong>
            <span>Total</span>
        </td>
    </tr>
</table>

@foreach(collect($boletas)->chunk(2) as $fila)
    <table class="sheet-row">
        <tr>
            @foreach($fila as $boleta)
                <td class="ticket-cell">
                    <table class="ticket">
                        <tr class="ticket-title">
                            <td class="ticket-title-left">
                                <span class="buyer-name">{{ $boleta['nombre_comprador'] }}</span>
                                <span class="buyer-note">
                                    @if($boleta['es_continuacion'])
                                        Continuacion {{ $boleta['segmento'] }}
                                    @endif
                                </span>
                            </td>
                            <td class="order-no">#{{ $boleta['pedido']->numero_pedido }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table class="meta">
                                    <tr>
                                        <td><span class="meta-label">Celular:</span> {{ $boleta['pedido']->celular ?: 'N/A' }}</td>
                                        <td><span class="meta-label">Direccion:</span> {{ $boleta['pedido']->calle_avenida ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="meta-label">Zona:</span> {{ $boleta['pedido']->zona_barrio ?: 'N/A' }}</td>
                                        <td><span class="meta-label">Referencia:</span> {{ $boleta['pedido']->referencia_direccion ?: 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="meta-label">Vendedor:</span> {{ $boleta['nombre_vendedor'] }}</td>
                                        <td><span class="meta-label">Celular vendedor:</span> {{ $boleta['celular_vendedor'] }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><span class="meta-label">Ruta:</span> {{ $boleta['ruta_nombre'] }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table class="items">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="qty">Cantidad</th>
                                            <th class="price">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($boleta['items'] as $it)
                                            @php
                                                $sub = $it->cantidad_pedido * $it->precio_venta;
                                                if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                                                    $sub -= $sub * ($it->descripcion_descuento_porcentaje / 100);
                                                }
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="product-name">{{ $it->nombre_producto }}</div>
                                                    <div class="product-code">{{ $it->codigo }}</div>
                                                </td>
                                                <td class="qty">{{ $it->cantidad_pedido }} ({{ $it->tipo_venta }})</td>
                                                <td class="price">{{ number_format($sub, 2, '.', ',') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="ticket-footer {{ $boleta['es_ultimo_segmento'] ? 'total' : 'continua' }}">
                                @if($boleta['es_ultimo_segmento'])
                                    Total pedido: <strong>{{ number_format($boleta['total_pedido'], 2, '.', ',') }} Bs</strong>
                                @else
                                    Continua en la siguiente boleta
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                @if(! $loop->last)
                    <td class="cut-space">&nbsp;</td>
                @endif
            @endforeach
            @if($fila->count() === 1)
                <td class="cut-space">&nbsp;</td>
                <td class="ticket-cell empty-cell">&nbsp;</td>
            @endif
        </tr>
    </table>
@endforeach
</body>
</html>
