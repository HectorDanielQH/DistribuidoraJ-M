<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MIS RUTAS</title>

    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .logo {
            width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
    </div>
    <div class="container">
        <h1 class="text-center text-black">Mis Rutas</h1>
        <table>
            <tr>
                <td><small>Preventista:</small></td>
                <td><small>{{ $usuario->nombres }} {{ $usuario->apellidoS}}</small></td>
            </tr>
            <tr>
                <td><small>Fecha:</small></td>
                <td><small>{{ now() }}</small></td>
            </tr>
        </table>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ruta</th>
                    <th>Dirección</th>
                    <th>Zona/Barrio</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Fecha de Asignacion</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($asignaciones as $asignacion)
                    <tr>
                        <td>{{ $asignacion->ruta->nombre_ruta }}</td>
                        <td>{{ $asignacion->calle_avenida }}</td>
                        <td>{{ $asignacion->zona_barrio }}</td>
                        <td>{{ $asignacion->nombres }} {{ $asignacion->apellido_paterno }} {{ $asignacion->apellido_materno }}</td>
                        <td>{{ $asignacion->celular}}</td>
                        <td>{{ $asignacion->asignacion_fecha_hora}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
