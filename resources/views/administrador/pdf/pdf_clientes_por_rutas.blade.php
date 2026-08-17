<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de clientes por rutas</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        .header {
            margin-bottom: 18px;
            border-bottom: 2px solid #dbe4ee;
            padding-bottom: 12px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            width: 120px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 6px;
        }
        .subtitle {
            color: #4b5563;
            margin: 0;
            font-size: 11px;
        }
        .meta {
            margin-top: 10px;
            padding: 10px 12px;
            background: #f5f8fb;
            border: 1px solid #d8e2ec;
            border-radius: 6px;
        }
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #d6dee8;
            padding: 7px 8px;
            text-align: left;
        }
        .report-table thead th {
            background: #1f4e78;
            color: #ffffff;
            font-size: 10px;
        }
        .report-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .footer {
            margin-top: 18px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 140px;">
                    <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
                </td>
                <td>
                    <p class="title">{{ $titulo }}</p>
                    <p class="subtitle">Distribuidora H&J</p>
                    <p class="subtitle">Fecha de generacion: {{ now()->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
        <div class="meta">
            <strong>Rutas incluidas:</strong>
            {{ empty($rutasSeleccionadas) ? 'Todas las rutas' : implode(', ', $rutasSeleccionadas) }}
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                @foreach ($encabezados as $encabezado)
                    <th>{{ $encabezado }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    @foreach ($fila as $valor)
                        <td>{{ $valor !== null && $valor !== '' ? $valor : 'S/D' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($encabezados) }}" style="text-align: center;">No se encontraron clientes para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte generado por el modulo de clientes de Distribuidora H&amp;J.
    </div>
</body>
</html>
