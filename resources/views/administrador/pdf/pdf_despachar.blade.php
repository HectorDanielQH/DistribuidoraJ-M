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
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            width: 42px;
        }
        .title {
            font-size: 13px;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #2f3d48;
            margin: 0;
            font-weight: 700;
        }
        .date-box {
            text-align: right;
            font-size: 8px;
            color: #111827;
        }
        .date-box strong {
            display: block;
            font-size: 12px;
        }
        .divider {
            height: 4px;
            background: #293948;
            margin: 4px 0 12px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 13px;
            border: 1px solid #d7dde2;
            border-radius: 6px;
        }
        .summary td {
            padding: 8px 6px;
            text-align: center;
            border-left: 1px solid #d7dde2;
        }
        .summary td:first-child {
            border-left: 0;
        }
        .summary strong {
            display: block;
            font-size: 12px;
            color: #263646;
        }
        .summary .green {
            color: #2f7d32;
        }
        .summary span {
            display: block;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #4b5563;
            font-size: 8px;
        }
        .cards-wrap {
            font-size: 0;
            margin: 0 -5px;
        }
        .card-cell {
            display: inline-block;
            width: 50%;
            vertical-align: top;
            padding: 0 5px 9px;
            page-break-inside: avoid;
            break-inside: avoid;
            font-size: 9px;
        }
        .card {
            border: 1px solid #aeb8be;
            border-radius: 5px;
            page-break-inside: avoid;
            break-inside: avoid;
            background: #ffffff;
        }
        .card-title {
            width: 100%;
            border-collapse: collapse;
            background: #2f4151;
            color: #ffffff;
        }
        .card-title td {
            padding: 6px 7px;
            font-size: 9px;
            letter-spacing: 0;
            text-transform: uppercase;
            font-weight: 700;
        }
        .card-title .order {
            text-align: right;
            font-size: 10px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            padding: 3px 7px 0;
            font-size: 8px;
            vertical-align: top;
            word-break: break-word;
        }
        .meta strong {
            color: #374151;
        }
        .muted {
            color: #6b7280;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 7px;
            table-layout: fixed;
        }
        .items th {
            border-top: 1px solid #d6dde0;
            border-bottom: 1px solid #d6dde0;
            padding: 4px 7px;
            font-size: 7px;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0;
            font-weight: 700;
        }
        .items td {
            border-bottom: 1px solid #eef1f2;
            padding: 5px 7px;
            font-size: 9px;
            vertical-align: top;
            word-break: break-word;
        }
        .items .product {
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.18;
        }
        .items .qty {
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            color: #2f3d48;
            white-space: normal;
            line-height: 1.18;
        }
        .items .price {
            text-align: right;
            font-size: 10px;
            white-space: nowrap;
        }
        .total {
            border-top: 1px solid #9bc79d;
            background: #edf8ee;
            padding: 7px 8px;
            text-align: right;
            font-size: 9px;
            letter-spacing: 0;
            text-transform: uppercase;
            font-weight: 700;
        }
        .total strong {
            font-size: 12px;
            letter-spacing: 0;
            color: #111827;
        }
        .watermark {
            text-align: center;
            color: #c7d0d5;
            font-size: 11px;
            letter-spacing: 0;
            font-weight: 700;
            margin: 2px 0 -2px;
            opacity: .6;
        }
        .page-break {
            page-break-after: always;
        }
        .spacer-card {
            visibility: hidden;
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

<div class="cards-wrap">
    @foreach($lista_de_pedidos->chunk(2) as $grupo)
        @foreach($grupo as $lista)
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
                $direccion = trim(($lista->calle_avenida ?? '') . ' ' . ($lista->zona_barrio ?? ''));
                $nombreVendedor = $vend ? trim($vend->nombres.' '.$vend->apellido_paterno.' '.$vend->apellido_materno) : 'No asignado';
            @endphp
            <div class="card-cell">
                <div class="card">
                    <table class="card-title">
                        <tr>
                            <td>{{ $lista->nombres }} {{ $lista->apellidos }}</td>
                            <td class="order">#{{ $lista->numero_pedido }}</td>
                        </tr>
                    </table>

                    <table class="meta">
                        <tr>
                            <td colspan="2"><strong>Dir:</strong> {{ $direccion ?: 'Sin direccion registrada' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Zona:</strong> {{ $lista->zona_barrio ?: 'N/A' }}</td>
                            <td><strong>Cel. comprador:</strong> {{ $lista->celular ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ruta:</strong> {{ $ruta ? $ruta->nombre_ruta : 'No asignada' }}</td>
                            <td><strong>Vendedor:</strong> {{ $nombreVendedor }}</td>
                        </tr>
                    </table>

                    <div class="watermark">DISTRIBUIDORA H&amp;J</div>

                    <table class="items">
                        <thead>
                            <tr>
                                <th style="text-align:left">Producto</th>
                                <th style="width:110px">Cant.</th>
                                <th style="width:72px;text-align:right">Total</th>
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
        @endforeach
        @if($grupo->count() === 1)
            <div class="card-cell spacer-card">
                <div class="card">&nbsp;</div>
            </div>
        @endif
    @endforeach
</div>
</body>
</html>
