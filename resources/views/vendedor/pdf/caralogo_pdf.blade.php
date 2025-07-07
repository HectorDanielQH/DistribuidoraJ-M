<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
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
    <div class="container text-center mb-4">
        <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
        <h2>Catálogo de Productos</h2>
    </div>

    <div class="container">
        @foreach($marcas as $marca)
            <div class="section-title">
                <h4>Marca: {{ $marca->descripcion }}</h4>
            </div>

            @foreach($marca->linea as $linea)
                <div class="mb-3">
                    @if($linea->productos->count() > 0)
                        <h5>Línea: {{ $linea->descripcion_linea }}</h5>
                        @forelse($linea->productos as $producto)
                            @if(!$producto->estado_de_baja && $producto->cantidad > 0)
                                <div class="producto row">
                                    <div class="col-3 text-center">
                                        @php
                                            $ruta = $producto->foto_producto;
                                            if (Storage::disk('local')->exists($ruta)) {
                                                $contenido = Storage::disk('local')->get($ruta);
                                                $tipo = pathinfo($ruta, PATHINFO_EXTENSION);
                                                $imagen_base64 = 'data:image/' . $tipo . ';base64,' . base64_encode($contenido);
                                            } else {
                                                $imagen_base64 = null;
                                            }
                                        @endphp
                                        @if($imagen_base64)
                                            <img src="{{ $imagen_base64 }}" alt="Producto">
                                        @else
                                            <p>No imagen</p>
                                        @endif
                                    </div>
                                    <div class="col-9">
                                        <table style="width: 100%; margin-top: 10px; font-size: 13px;">
                                            <tr>
                                                <td><strong>Nombre:</strong> {{ $producto->nombre_producto }}</td>
                                                <td><strong>Código:</strong> {{ $producto->codigo }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><strong>Descripción:</strong> {{ $producto->descripcion_producto }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cantidad:</strong> {{ $producto->cantidad }} {{ $producto->detalle_cantidad }}</td>
                                                <td><strong>Promoción:</strong> {{ $producto->promocion ? 'Sí' : 'No' }}</td>
                                            </tr>
                                            @if($producto->promocion)
                                                <tr>
                                                    <td><strong>Descuento:</strong> {{ $producto->descripcion_descuento_porcentaje ?? '0' }}%</td>
                                                    <td><strong>Regalo:</strong> {{ $producto->descripcion_regalo ?? 'No' }}</td>
                                                </tr>
                                            @endif
                                        </table>

                                        <table class="formas-venta mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Tipo de Venta</th>
                                                    <th>Precio (Bs)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($producto->formaVentas as $venta)
                                                    @if($venta->activo)
                                                        <tr>
                                                            <td>{{ $venta->tipo_venta }}</td>
                                                            <td>{{ number_format($venta->precio_venta, 2, ',', '.') }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                @break
                            @endif
                        @empty
                            <p class="text-muted">No hay productos en esta línea.</p>
                        @endforelse
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
</body>
</html>
