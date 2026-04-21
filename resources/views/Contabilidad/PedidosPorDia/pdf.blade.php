<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos por dia</title>
    <style>
        @font-face {
            font-family: 'ReporteSans';
            font-weight: 400;
            src: url("{{ base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'ReporteSans';
            font-weight: 700;
            src: url("{{ base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf') }}") format('truetype');
        }
        * { box-sizing: border-box; font-family: 'ReporteSans', sans-serif !important; }
        body { margin: 0; color: #17211d; font-size: 10px; }
        .header { border-bottom: 4px solid #263746; padding-bottom: 10px; margin-bottom: 14px; }
        .header-table, .summary, .filters, .table-report { width: 100%; border-collapse: collapse; }
        .logo { width: 54px; height: 38px; object-fit: contain; }
        h1 { margin: 0; color: #263746; font-size: 24px; text-transform: uppercase; }
        .subtitle { color: #527060; font-size: 11px; font-weight: 700; margin-top: 4px; }
        .date { text-align: right; font-size: 12px; font-weight: 700; }
        .summary td { border: 1px solid #d6dee3; padding: 8px; text-align: center; }
        .summary strong { display: block; color: #166534; font-size: 16px; }
        .summary span { display: block; color: #263746; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .filters td { border: 1px solid #d6dee3; padding: 6px 8px; }
        .filters .label { width: 110px; background: #e8f2ee; color: #263746; font-weight: 700; text-transform: uppercase; }
        .table-report th { background: #263746; color: #fff; padding: 7px 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .table-report td { border: 1px solid #d6dee3; padding: 6px; vertical-align: middle; }
        .table-report tbody tr:nth-child(even) { background: #f7faf9; }
        .money { text-align: right; font-weight: 700; color: #166534; }
        .empty { padding: 24px; text-align: center; color: #64748b; font-weight: 700; }
    </style>
</head>
<body>
    @php $logo = public_path('images/logo_color.webp'); @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 70px;">
                    @if(file_exists($logo))
                        <img src="{{ $logo }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td>
                    <h1>Lista de pedidos por dia</h1>
                    <div class="subtitle">Distribuidora H &amp; J - reporte contable de pedidos contabilizados</div>
                </td>
                <td class="date">
                    {{ now()->format('d/m/Y') }}<br>
                    {{ now()->format('H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="summary">
        <tr>
            <td>
                <strong>{{ $resumen['pedidos'] }}</strong>
                <span>Pedidos</span>
            </td>
            <td>
                <strong>{{ $resumen['items'] }}</strong>
                <span>Items</span>
            </td>
            <td>
                <strong>Bs {{ number_format($resumen['total'], 2, '.', ',') }}</strong>
                <span>Total</span>
            </td>
        </tr>
    </table>

    <table class="filters" style="margin: 12px 0;">
        <tr>
            <td class="label">Fecha</td>
            <td>{{ $fecha }}</td>
            <td class="label">Rutas</td>
            <td>{{ $rutasTexto }}</td>
        </tr>
        <tr>
            <td class="label">Preventistas</td>
            <td colspan="3">{{ $preventistasTexto }}</td>
        </tr>
    </table>

    <table class="table-report">
        <thead>
            <tr>
                <th>Nro. pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Ruta</th>
                <th>Preventista</th>
                <th>Items</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->numero_pedido }}</td>
                    <td>{{ \Carbon\Carbon::parse($pedido->fecha_contable)->format('d/m/Y') }}</td>
                    <td>{{ $pedido->cliente }}</td>
                    <td>{{ $pedido->ruta }}</td>
                    <td>{{ $pedido->preventista }}</td>
                    <td>{{ $pedido->items }}</td>
                    <td class="money">Bs {{ number_format((float) $pedido->total, 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">No hay pedidos contabilizados para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
