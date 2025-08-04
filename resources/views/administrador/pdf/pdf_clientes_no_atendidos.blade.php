<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cliente No Atendidos</title>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .logo {
            width: 140px;
            height: auto;
        }
        .section-title {
            background-color: #f2f2f2;
            padding: 8px;
            margin-top: 20px;
            border-left: 5px solid #007bff;
        }
        .producto {
            border: 1px solid #ccc;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .producto h5 {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .producto img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }
        table.formas-venta th, table.formas-venta td {
            font-size: 11px;
            padding: 4px;
        }
        table.formas-venta {
            width: 100%;
            border-collapse: collapse;
        }
        table.formas-venta, .formas-venta th, .formas-venta td {
            border: 1px solid #aaa;
        }
    </style>
</head>
<body>
    <div class="container mb-4">
        <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
        <h2>Clientes no atendidos</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo Cliente</th>
                <th>Celular</th>
                <th>Dirección</th>
                <th>Zona(Ruta)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($noAtendidos as $cliente)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $cliente->cliente->nombres }} {{ $cliente->cliente->apellido_paterno }} {{ $cliente->cliente->apellido_materno }}</td>
                    <td>{{ $cliente->cliente->celular }}</td>
                    <td>{{ $cliente->cliente->ubicacion }}</td>
                    <td>{{ $cliente->cliente->ruta->nombre_ruta }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No hay clientes no atendidos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <p style="text-align: center; font-size: 10px; color: #888;">
        Distribuidora H&j - Todos los derechos reservados.
    </p>
    <p style="text-align: center; font-size: 10px; color: #888;">
        Fecha de impresión: {{ date('d/m/Y H:i:s') }}
    </p>
    <p style="text-align: center; font-size: 10px; color: #888;">
        Generado por el sistema de gestión de pedidos H&J.
    </p>
    <p style="text-align: center; font-size: 10px; color: #888;">
        Versión 1.0
    </p>
</body>
</html>
