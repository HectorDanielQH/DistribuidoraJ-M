<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mis rutas</title>
    <style>
        @page {
            margin: 18px 20px 22px;
        }

        body {
            margin: 0;
            color: #17211d;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
        }

        .topbar {
            width: 100%;
            border-bottom: 3px solid #15803d;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .topbar td {
            vertical-align: middle;
        }

        .logo {
            width: 82px;
            height: auto;
        }

        .brand {
            padding-left: 12px;
        }

        .brand h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .brand p {
            margin: 2px 0 0;
            color: #5f6f68;
            font-size: 11px;
        }

        .date-box {
            width: 150px;
            text-align: right;
            color: #17211d;
            font-size: 10px;
        }

        .date-box strong {
            display: block;
            font-size: 12px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary td {
            border: 1px solid #dfe7e4;
            padding: 8px;
            background: #f4f7f6;
        }

        .summary .label {
            color: #5f6f68;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary .value {
            margin-top: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .route-title {
            background: #15803d;
            color: #ffffff;
            padding: 7px 9px;
            margin-top: 10px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            page-break-after: avoid;
        }

        .route-title span {
            float: right;
            font-size: 10px;
            font-weight: normal;
            text-transform: none;
        }

        table.clients {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            page-break-inside: auto;
        }

        .clients th {
            background: #eef3f1;
            border: 1px solid #dfe7e4;
            color: #17211d;
            font-size: 9px;
            padding: 6px 5px;
            text-align: left;
            text-transform: uppercase;
        }

        .clients td {
            border: 1px solid #dfe7e4;
            padding: 6px 5px;
            vertical-align: top;
        }

        .clients tr:nth-child(even) td {
            background: #fbfdfc;
        }

        .num {
            width: 22px;
            text-align: center;
            font-weight: bold;
        }

        .client {
            width: 25%;
            font-weight: bold;
        }

        .zone {
            width: 18%;
        }

        .address {
            width: 32%;
        }

        .phone {
            width: 14%;
            font-weight: bold;
        }

        .state {
            width: 11%;
            text-align: center;
        }

        .muted {
            color: #5f6f68;
            font-size: 9px;
        }

        .badge {
            display: inline-block;
            border-radius: 4px;
            padding: 3px 5px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-pending {
            background: #fff4bf;
            color: #5d4900;
        }

        .badge-order {
            background: #e7f6ec;
            color: #15803d;
        }

        .badge-done {
            background: #edf2f7;
            color: #26332e;
        }

        .footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            color: #5f6f68;
            border-top: 1px solid #dfe7e4;
            padding-top: 5px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    @php
        $nombreVendedor = trim(($usuario->nombres ?? '').' '.($usuario->apellido_paterno ?? '').' '.($usuario->apellido_materno ?? ''));
        $gruposRuta = $asignaciones->groupBy('nombre_ruta');
        $pendientes = $asignaciones->whereNull('atencion_fecha_hora')->count();
        $conPedido = $asignaciones->filter(fn ($asignacion) => $asignacion->numero_pedido && $asignacion->estado_pedido)->count();
        $sinPedido = $asignaciones->filter(fn ($asignacion) => !$asignacion->numero_pedido && $asignacion->atencion_fecha_hora)->count();
    @endphp

    <table class="topbar">
        <tr>
            <td style="width: 90px;">
                <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
            </td>
            <td class="brand">
                <h1>Mis rutas</h1>
                <p>Lista de clientes asignados para visitar</p>
            </td>
            <td class="date-box">
                <strong>{{ now()->format('d/m/Y') }}</strong>
                {{ now()->format('H:i') }}<br>
                Total: {{ $asignaciones->count() }} clientes
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Vendedor</div>
                <div class="value">{{ $nombreVendedor ?: $usuario->username }}</div>
            </td>
            <td>
                <div class="label">Rutas</div>
                <div class="value">{{ $gruposRuta->count() }}</div>
            </td>
            <td>
                <div class="label">Pendientes</div>
                <div class="value">{{ $pendientes }}</div>
            </td>
            <td>
                <div class="label">Con pedido</div>
                <div class="value">{{ $conPedido }}</div>
            </td>
            <td>
                <div class="label">Sin pedido</div>
                <div class="value">{{ $sinPedido }}</div>
            </td>
        </tr>
    </table>

    @forelse ($gruposRuta as $ruta => $clientes)
        <div class="route-title">
            {{ $ruta ?: 'Sin ruta' }}
            <span>{{ $clientes->count() }} clientes</span>
        </div>

        <table class="clients">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th class="client">Cliente</th>
                    <th class="zone">Zona</th>
                    <th class="address">Direccion</th>
                    <th class="phone">Celular</th>
                    <th class="state">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clientes as $asignacion)
                    @php
                        $cliente = trim(($asignacion->nombres ?? '').' '.($asignacion->apellidos ?? ''));
                        $direccion = $asignacion->calle_avenida ?: 'Sin direccion';
                        $referencia = $asignacion->referencia_direccion;
                    @endphp
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td class="client">{{ $cliente ?: 'Cliente sin nombre' }}</td>
                        <td class="zone">{{ $asignacion->zona_barrio ?: 'Sin zona' }}</td>
                        <td class="address">
                            {{ $direccion }}
                            @if($referencia)
                                <br><span class="muted">Ref. {{ $referencia }}</span>
                            @endif
                        </td>
                        <td class="phone">{{ $asignacion->celular ?: 'Sin celular' }}</td>
                        <td class="state">
                            @if($asignacion->numero_pedido && $asignacion->estado_pedido)
                                <span class="badge badge-order">Pedido</span>
                                <br><span class="muted">#{{ $asignacion->numero_pedido }}</span>
                            @elseif($asignacion->atencion_fecha_hora)
                                <span class="badge badge-done">Sin pedido</span>
                            @else
                                <span class="badge badge-pending">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <table class="clients">
            <tr>
                <td>No tienes clientes asignados para mostrar.</td>
            </tr>
        </table>
    @endforelse

    <div class="footer">
        Distribuidora H&J - Documento generado automaticamente para control de rutas del vendedor.
    </div>
</body>
</html>
