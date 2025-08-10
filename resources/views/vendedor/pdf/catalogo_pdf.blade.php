<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        /* Fuente estándar PDF-friendly */
        body {
            font-family: DejaVu Sans, Arial, sans-serif; /* DejaVu Sans asegura compatibilidad UTF-8 */
            font-size: 11px;
            color: #222;
            background-color: #ffffff; /* Evita fondos grises que Dompdf a veces imprime feos */
            margin: 0;
            padding: 0;
        }

        .logo {
            width: 80px;
            margin-bottom: 6px;
            display: block;
        }

        .catalogo-title {
            font-weight: bold;
            font-size: 14px; /* valor fijo mejor que rem */
            color: #234e70;
            margin: 0 0 5px 0;
            text-align: center;
        }

        .marca-title {
            background-color: #e3e9f9;
            padding: 5px 10px;
            border-left: 4px solid #234e70;
            font-size: 12px;
            font-weight: bold;
            color: #234e70;
            margin: 18px 0 7px 0;
        }

        .linea-title {
            font-size: 11px;
            font-weight: bold;
            color: #205072;
            margin: 8px 0 6px 4px;
            border-left: 3px solid #2f80ed;
            padding-left: 5px;
        }

        .producto-table {
            width: 100%;
            border: 1px solid #e3e9f9;
            background-color: #fff;
            margin-bottom: 8px;
            border-radius: 4px; /* menos costoso de renderizar que 7px */
            border-collapse: collapse;
        }

        .producto-table td {
            vertical-align: top;
            padding: 4px 6px;
        }

        .producto-img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #d1d9e6;
            background-color: #f3f6fa;
            display: block;
        }

        .producto-nombre {
            font-size: 11px;
            font-weight: bold;
            color: #205072;
            margin-bottom: 1px;
        }

        .promo-badge {
            font-size: 10px;
            background-color: #e5fae5;
            color: #27ae60;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 4px;
            margin-left: 4px;
            display: inline-block;
        }

        .precios-table {
            width: 100%;
            border-radius: 3px;
            border: 1px solid #e9e9ef;
            margin-top: 4px;
            border-collapse: collapse;
        }

        .precios-table th {
            background-color: #f7fbfc;
            color: #205072;
            font-weight: bold;
            padding: 3px;
            font-size: 10px;
            border-bottom: 1px solid #e4e7ee;
            text-align: left;
        }

        .precios-table td {
            padding: 3px;
            font-size: 10px;
            color: #333;
            background-color: #fff;
        }

        .promo-info {
            font-size: 10px;
            color: #25b352;
            margin-bottom: 2px;
        }
    </style>

</head>
<body>
    <div class="container text-center my-2">
        <img src="{{ public_path('images/logo_distribuidora.jpg') }}" alt="Logo" class="logo">
        <div class="catalogo-title">Catálogo de Productos</div>
    </div>
    <div class="container">

        @forelse($marcas as $marca)
            @php
                // Filtra las líneas que tienen productos activos
                $lineas_con_productos = $marca->linea->filter(function($linea) {
                    return $linea->productos->where('estado_de_baja', false)->where('cantidad','>',0)->count() > 0;
                });
            @endphp
            @if($lineas_con_productos->count() > 0)
            <div>
                <div class="marca-title">{{ $marca->descripcion }}</div>

                @foreach($lineas_con_productos as $linea)
                    @php
                        $productosActivos = $linea->productos->where('estado_de_baja', false)->where('cantidad','>',0);
                    @endphp
                    @if($productosActivos->count() > 0)
                    <div class="linea-title">
                        Línea: {{ $linea->descripcion_linea }}
                    </div>
                    @foreach($productosActivos as $producto)
                    <table class="producto-table">
                        <tr>
                            <td style="width: 58px;">
                                <img src="{{ storage_path('app/private/' . $producto->foto_producto) }}"
                                    alt="{{ $producto->nombre_producto }}"
                                    class="producto-img">
                            </td>
                            <td>
                                <div class="producto-nombre">
                                    {{ $producto->nombre_producto }}
                                    @if($producto->promocion)
                                        <span class="promo-badge">Promoción</span>
                                    @endif
                                </div>
                                <div style="font-size:.81rem; color:#666;">
                                    Código: <b>{{ $producto->codigo }}</b>
                                </div>
                                <div style="font-size:.85rem; color:#222; margin:2px 0 3px 0;">
                                    {{ $producto->descripcion_producto }}
                                </div>
                                <div style="font-size:.85rem; color:#234e70;">
                                    Cantidad: <b>{{ $producto->cantidad }}</b> {{ $producto->detalle_cantidad }}
                                </div>
                                @if($producto->promocion)
                                <div class="promo-info">
                                    @if($producto->descripcion_descuento_porcentaje)
                                        <span>Desc: <b>{{ $producto->descripcion_descuento_porcentaje }}%</b></span>
                                    @endif
                                    @if($producto->descripcion_regalo)
                                        <span style="margin-left:6px;">Regalo: <b>{{ $producto->descripcion_regalo }}</b></span>
                                    @endif
                                </div>
                                @endif

                                @php $ventasActivas = $producto->formaVentas->where('activo', true); @endphp
                                @if($ventasActivas->count() > 0)
                                <table class="precios-table">
                                    <thead>
                                    <tr>
                                        <th>Venta</th>
                                        <th>Precio (Bs)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($ventasActivas as $venta)
                                    <tr>
                                        <td>{{ $venta->tipo_venta }}</td>
                                        <td>{{ number_format($venta->precio_venta, 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </td>
                        </tr>
                    </table>
                    @endforeach
                    @endif
                @endforeach
            </div>
            @endif
        @empty
            <p class="text-muted">No se encontraron marcas registradas.</p>
        @endforelse
    </div>
</body>
</html>