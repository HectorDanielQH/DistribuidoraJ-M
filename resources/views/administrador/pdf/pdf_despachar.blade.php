<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de pedidos</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 6mm; }
        body {
            font-family: Helvetica, Arial, DejaVu Sans, sans-serif;
            color: #1f2933;
            font-size: 8px;
            line-height: 1.18;
        }
        .page {
            float: left;
            width: 50%;
            height: 50%;
            padding: 2.7mm;
            border: 1px dashed #b7c3bd;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .page-break {
            clear: both;
            page-break-after: always;
        }
        .title {
            text-align: center;
            font-size: 15px;
            letter-spacing: 1.2px;
            font-weight: 900;
            margin: 0 0 4px;
        }
        .meta, .items, .status, .footer-grid, .summary {
            width: 100%;
            border-collapse: collapse;
        }
        .meta th, .meta td,
        .items th, .items td,
        .status th, .status td,
        .summary th, .summary td {
            border: 1px solid #8b9a93;
            padding: 2px;
            vertical-align: top;
        }
        .label {
            background: #79ad9d;
            color: #ffffff;
            text-transform: uppercase;
            font-weight: 900;
            width: 17%;
        }
        .value {
            background: #f8faf9;
            font-weight: 800;
        }
        .paid {
            color: #2f7d32;
            font-weight: 900;
            text-align: center;
        }
        .bar {
            background: #79ad9d;
            color: #ffffff;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .items th {
            background: #a8c999;
            color: #ffffff;
            text-transform: uppercase;
            font-weight: 900;
            text-align: center;
        }
        .items .money-head {
            background: #79ad9d;
        }
        .items .total-head {
            background: #e0bd36;
        }
        .items td {
            height: 13px;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .strong { font-weight: 900; }
        .muted { color: #69777f; }
        .check {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #3f4a45;
            margin-right: 3px;
            vertical-align: -2px;
        }
        .obs {
            height: 22px;
            font-weight: 900;
        }
        .totals td {
            height: 12px;
            font-weight: 900;
        }
        .totals .total {
            color: #dc2626;
        }
        .status td {
            text-align: center;
            font-weight: 800;
        }
        .spacer {
            height: 4px;
        }
        .logo {
            width: 26px;
            height: auto;
        }
        .header-logo {
            position: absolute;
            top: 0;
            left: 0;
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
@endphp

@foreach($lista_de_pedidos as $lista)
    @php
        $items = $itemsPorPedido[$lista->numero_pedido] ?? [];
        $vend = $vendedores[$lista->id_vendedor] ?? null;
        $ruta = $rutas[$lista->ruta_id] ?? null;
        $totalPedido = 0;
        $cantidadTotal = 0;
        foreach ($items as $it) {
            $linea = $it->cantidad_pedido * $it->precio_venta;
            if ($it->promocion && $it->descripcion_descuento_porcentaje > 0) {
                $linea -= $linea * ($it->descripcion_descuento_porcentaje / 100);
            }
            $totalPedido += $linea;
            $cantidadTotal += $it->cantidad_pedido;
        }
        $direccion = trim(($lista->calle_avenida ?? '') . ' ' . ($lista->zona_barrio ?? ''));
    @endphp

    <div class="page">
        <div class="header-logo">
            <img src="{{ public_path('images/logo_distribuidora.jpg') }}" class="logo" alt="Logo">
        </div>
        <h1 class="title">HOJA DE PEDIDO</h1>

        <table class="meta">
            <tr>
                <th class="label">Nombre</th>
                <td class="value">{{ $lista->nombres }} {{ $lista->apellidos }}</td>
                <th class="label">Fecha</th>
                <td class="value">{{ $lista->fecha_pedido ? date('d/m/Y', strtotime($lista->fecha_pedido)) : date('d/m/Y') }}</td>
            </tr>
            <tr>
                <th class="label">Direccion</th>
                <td class="value">{{ $direccion ?: 'Sin direccion registrada' }}</td>
                <th class="label">No pedido</th>
                <td class="value">#{{ str_pad($lista->numero_pedido, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <th class="label">WhatsApp</th>
                <td class="value">{{ $lista->celular ?: 'Sin celular' }}</td>
                <th class="label">Cobrar</th>
                <td class="paid">Bs {{ number_format($totalPedido, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <th class="label">Ruta</th>
                <td class="value">{{ $ruta ? $ruta->nombre_ruta : 'No asignada' }}</td>
                <th class="label">Preventista</th>
                <td class="value">{{ $vend ? trim($vend->nombres.' '.$vend->apellido_paterno.' '.$vend->apellido_materno) : 'No asignado' }}</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <table class="status">
            <tr><th colspan="4" class="bar">Control del repartidor</th></tr>
            <tr>
                <td><span class="check"></span>Recibido</td>
                <td><span class="check"></span>No encontrado</td>
                <td><span class="check"></span>Devuelto parcial</td>
                <td><span class="check"></span>Entregado</td>
            </tr>
        </table>

        <div class="spacer"></div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:9%">Cant</th>
                    <th style="width:15%">Codigo</th>
                    <th>Producto</th>
                    <th style="width:15%" class="money-head">Precio</th>
                    <th style="width:17%" class="total-head">Importe</th>
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
                        <td class="center">{{ $it->cantidad_pedido }} {{ $it->tipo_venta }}</td>
                        <td class="center">{{ $it->codigo }}</td>
                        <td>
                            <span class="strong">{{ $it->nombre_producto }}</span>
                            @if($it->promocion)
                                <br><span class="muted">
                                    Promo:
                                    @if($it->descripcion_descuento_porcentaje > 0)
                                        {{ $it->descripcion_descuento_porcentaje }}%
                                    @endif
                                    {{ $it->descripcion_regalo }}
                                </span>
                            @endif
                        </td>
                        <td class="right">Bs {{ number_format($it->precio_venta, 2, '.', ',') }}</td>
                        <td class="right">Bs {{ number_format($sub, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                @for($i = count($items); $i < 6; $i++)
                    <tr>
                        <td>&nbsp;</td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td rowspan="3" class="obs" style="width:66%">OBSERVACIONES:</td>
                <td class="right strong">SUBTOTAL:</td>
                <td class="right">Bs {{ number_format($totalPedido, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td class="right strong">PRODUCTOS:</td>
                <td class="right">{{ $cantidadTotal }}</td>
            </tr>
            <tr class="totals">
                <td class="right total">TOTAL:</td>
                <td class="right total">Bs {{ number_format($totalPedido, 2, '.', ',') }}</td>
            </tr>
        </table>

        <div class="spacer"></div>

    </div>
    @if($loop->iteration % 4 === 0 && ! $loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
