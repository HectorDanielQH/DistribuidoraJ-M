<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas mayoristas</title>
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#17211d}
        h1{margin:0 0 6px;font-size:22px}
        .muted{color:#5f6f68}
        .summary{margin:14px 0;padding:12px;border:1px solid #d8e4de;border-radius:6px}
        .summary span{display:inline-block;min-width:180px;margin-right:10px}
        .sale{margin-bottom:16px;border:1px solid #d8e4de;border-radius:6px;padding:12px}
        .sale h2{margin:0 0 6px;font-size:15px}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{border:1px solid #d8e4de;padding:6px}
        th{background:#eef3f1;text-align:left}
        .text-right{text-align:right}
    </style>
</head>
<body>
    <h1>Reporte de ventas mayoristas</h1>
    <div class="muted">Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="summary">
        <span><strong>Ventas:</strong> {{ $resumen['ventas'] }}</span>
        <span><strong>Items:</strong> {{ $resumen['items'] }}</span>
        <span><strong>Total:</strong> Bs {{ number_format($resumen['total'], 2, '.', ',') }}</span>
    </div>

    @foreach ($ventas as $venta)
        <div class="sale">
            <h2>Venta #{{ $venta->numero_venta }}</h2>
            <div class="muted">
                {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }} |
                {{ $venta->cliente }} |
                {{ $venta->ruta }} |
                {{ $venta->mayorista }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Forma</th>
                        <th>Cant.</th>
                        <th>Unid.</th>
                        <th>P/U</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($detalles[$venta->numero_venta] ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->nombre_producto }}</td>
                            <td>{{ $item->tipo_venta }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>{{ $item->cantidad * $item->equivalencia_cantidad }}</td>
                            <td class="text-right">{{ number_format($item->precio_unitario, 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format($item->cantidad * $item->precio_unitario, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-right" style="margin-top:8px">
                <strong>Total venta: Bs {{ number_format($venta->total, 2, '.', ',') }}</strong>
            </div>
        </div>
    @endforeach
</body>
</html>
