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
        @page { margin: 8mm 9mm; }
        body {
            font-family: 'ReporteSans' !important;
            color: #20272d;
            font-size: 9px;
            line-height: 1.22;
            background: #f8fafc;
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            width: 42px;
        }
        .title {
            font-size: 14px;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #233240;
            margin: 0;
            font-weight: 700;
        }
        .title-sub {
            margin-top: 2px;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .2px;
        }
        .date-box {
            text-align: right;
            font-size: 8px;
            color: #111827;
            background: #eef3f7;
            border: 1px solid #d7dde2;
            border-radius: 6px;
            padding: 6px 8px;
        }
        .date-box strong {
            display: block;
            font-size: 12px;
        }
        .divider {
            height: 5px;
            background: #233240;
            margin: 6px 0 12px;
            border-radius: 999px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 13px;
            border: 1px solid #cfd8de;
            background: #ffffff;
            border-radius: 8px;
        }
        .summary td {
            padding: 9px 6px;
            text-align: center;
            border-left: 1px solid #d7dde2;
        }
        .summary td:first-child {
            border-left: 0;
        }
        .summary strong {
            display: block;
            font-size: 13px;
            color: #20303d;
        }
        .summary .green {
            color: #2f7d32;
        }
        .summary span {
            display: block;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #64748b;
            font-size: 8px;
        }
        .cards-grid {
            width: 100%;
            font-size: 0;
        }
        .card-cell {
            display: inline-block;
            width: 49%;
            margin: 0 1% 8px 0;
            vertical-align: top;
            page-break-inside: auto;
            break-inside: auto;
            font-size: 9px;
        }
        .card-cell:nth-child(2n) {
            margin-right: 0;
        }
        .card {
            border: 1px solid #cfd8de;
            border-radius: 8px;
            page-break-inside: avoid;
            break-inside: avoid;
            background: #ffffff;
        }
        .grid-break {
            clear: both;
        }
        .card-title {
            width: 100%;
            border-collapse: collapse;
            background: #233240;
            color: #ffffff;
        }
        .card-title td {
            padding: 7px 8px;
            font-size: 9px;
            letter-spacing: 0;
            font-weight: 700;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .buyer-caption {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #cbd5e1;
            letter-spacing: .25px;
            margin-bottom: 2px;
        }
        .buyer-name {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.2;
        }
        .card-title .order {
            text-align: right;
            font-size: 11px;
            width: 88px;
            white-space: nowrap;
            color: #ffffff;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            background: #fbfdff;
        }
        .meta td {
            padding: 5px 8px 2px;
            font-size: 8px;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
            border-bottom: 1px solid #edf2f7;
        }
        .meta tr:last-child td {
            border-bottom: 0;
        }
        .meta strong {
            color: #334155;
        }
        .meta-label {
            display: inline-block;
            min-width: 108px;
        }
        .muted {
            color: #6b7280;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            table-layout: fixed;
        }
        .items th {
            border-top: 1px solid #d6dde0;
            border-bottom: 1px solid #d6dde0;
            background: #f1f5f9;
            padding: 4px 7px;
            font-size: 7px;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0;
            font-weight: 700;
        }
        .items td {
            border-bottom: 1px solid #eef1f2;
            padding: 6px 7px;
            font-size: 9px;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .items .product {
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.18;
        }
        .items .qty {
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            color: #2f3d48;
            white-space: normal;
            line-height: 1.18;
        }
        .items .price {
            text-align: right;
            font-size: 10px;
            white-space: nowrap;
            font-weight: 700;
            color: #20303d;
        }
        .total {
            border-top: 1px solid #9bc79d;
            background: #ecfdf3;
            padding: 8px 9px;
            text-align: right;
            font-size: 9px;
            letter-spacing: 0;
            text-transform: uppercase;
            font-weight: 700;
            color: #166534;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .total strong {
            font-size: 13px;
            letter-spacing: 0;
            color: #14532d;
        }
        .watermark {
            text-align: center;
            color: #d6dde3;
            font-size: 9px;
            letter-spacing: 0;
            font-weight: 700;
            margin: 4px 0 0;
            opacity: .55;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
@php
    use App\Models\User;
    use App\Models\Rutas;

    $itemsPorPedido = [];
    foreach ($pedidos as $p) {
        $itemsPorPedido[$p->numero_pedido][] = $p;
    }

    $vendedorIds = $lista_de_pedidos->pluck('id_vendedor')->filter()->unique()->all();
    $rutaIds = $lista_de_pedidos->pluck('ruta_id')->filter()->unique()->all();
    $vendedores = User::whereIn('id', $vendedorIds)->get()->keyBy('id');
    $rutas = Rutas::whereIn('id', $rutaIds)->get()->keyBy('id');

    $totalGeneral = 0;
    $itemsGeneral = 0;
    foreach ($pedidos as $p) {
        $linea = $p->cantidad_pedido * $p->precio_venta;
        if ($p->promocion && $p->descripcion_descuento_porcentaje > 0) {
            $linea -= $linea * ($p->descripcion_descuento_porcentaje / 100);
        }
        $totalGeneral += $linea;
        $itemsGeneral += $p->cantidad_pedido;
    }
@endphp

<table class="header">
    <tr>
        <td style="width:56px">
            <img src="{{ public_path('images/logo_distribuidora.jpg') }}" class="logo" alt="Logo">
        </td>
        <td>
            <h1 class="title">Reporte de carga y despacho</h1>
            <div class="title-sub">Boletas de entrega listas para despacho</div>
        </td>
        <td class="date-box" style="width:120px">
            <strong>{{ now()->format('d/m/Y') }}</strong>
            {{ now()->format('H:i') }}
        </td>
    </tr>
</table>
<div class="divider"></div>

<table class="summary">
    <tr>
        <td>
            <strong>{{ $lista_de_pedidos->count() }}</strong>
            <span>Pedidos</span>
        </td>
        <td>
            <strong>{{ $itemsGeneral }}</strong>
            <span>Items</span>
        </td>
        <td>
            <strong class="green">Bs {{ number_format($totalGeneral, 2, '.', ',') }}</strong>
            <span>Total</span>
        </td>
    </tr>
</table>

<div class="cards-grid">
@foreach($lista_de_pedidos as $lista)
    @php
        $items = $itemsPorPedido[$lista->numero_pedido] ?? [];
        $vend = $vendedores[$lista->id_vendedor] ?? null;
        $ruta = $rutas[$lista->ruta_id] ?? null;
        $totalPedido = 0;
        foreach ($items as $it) {
            $linea = $it->cantidad_pedido * $it->precio_venta;
            if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                $linea -= $linea * ($it->descripcion_descuento_porcentaje / 100);
            }
            $totalPedido += $linea;
        }
        $nombreVendedor = $vend ? trim($vend->nombres.' '.$vend->apellido_paterno.' '.$vend->apellido_materno) : 'No asignado';
        $celularVendedor = $vend && !empty($vend->celular) ? $vend->celular : 'N/A';
    @endphp
    <div class="card-cell">
        <div class="card">
            <table class="card-title">
                <tr>
                    <td>
                        <span class="buyer-caption">Comprador</span>
                        <span class="buyer-name">{{ trim(($lista->nombres ?? '').' '.($lista->apellidos ?? '')) ?: 'Comprador no disponible' }}</span>
                    </td>
                    <td class="order">#{{ $lista->numero_pedido }}</td>
                </tr>
            </table>

            <table class="meta">
                <tr>
                    <td>
                        <strong class="meta-label">Celular del comprador:</strong>
                        {{ $lista->celular ?: 'N/A' }}
                    </td>
                    <td>
                        <strong class="meta-label">Direccion del comprador:</strong>
                        {{ $lista->calle_avenida ?: 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong class="meta-label">Zona del comprador:</strong>
                        {{ $lista->zona_barrio ?: 'N/A' }}
                    </td>
                    <td>
                        <strong class="meta-label">Direccion de referencia:</strong>
                        {{ $lista->referencia_direccion ?: 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong class="meta-label">Vendedor:</strong>
                        {{ $nombreVendedor }}
                    </td>
                    <td>
                        <strong class="meta-label">Celular vendedor:</strong>
                        {{ $celularVendedor }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong class="meta-label">Ruta del vendedor:</strong>
                        {{ $ruta ? $ruta->nombre_ruta : 'No asignada' }}
                    </td>
                </tr>
            </table>

            <div class="watermark">DISTRIBUIDORA H&amp;J</div>

            <table class="items">
                <thead>
                    <tr>
                        <th style="text-align:left">Producto</th>
                        <th style="width:104px">Cant.</th>
                        <th style="width:68px;text-align:right">Total</th>
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
                            <td>
                                <div class="product">{{ $it->nombre_producto }}</div>
                                <span class="muted">{{ $it->codigo }}</span>
                            </td>
                            <td class="qty">{{ $it->cantidad_pedido }} ({{ $it->tipo_venta }})</td>
                            <td class="price">{{ number_format($sub, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">Total: <strong>{{ number_format($totalPedido, 2, '.', ',') }} Bs</strong></div>
        </div>
    </div>
    @if($loop->iteration % 2 === 0)
        <div class="grid-break"></div>
    @endif
@endforeach
</div>
</body>
</html>
