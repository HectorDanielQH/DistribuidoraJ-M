<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catalogo de Ofertas</title>
    <style>
        @page {
            margin: 14px 16px;
        }

        body {
            background: #ffffff;
            color: #111111;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            margin: 0;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .hero {
            border-bottom: 10px solid #058b3a;
            height: 88px;
            margin-bottom: 12px;
            position: relative;
            width: 100%;
        }

        .hero-bg {
            height: 88px;
            left: 0;
            object-fit: cover;
            opacity: .5;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 1;
        }

        .logo-box {
            background: #ffffff;
            border-radius: 50%;
            height: 64px;
            left: 12px;
            padding: 5px;
            position: absolute;
            top: 10px;
            width: 64px;
            z-index: 3;
        }

        .logo-box img {
            height: 64px;
            object-fit: contain;
            width: 64px;
        }

        .hero-title {
            background: #ffffff;
            color: #000000;
            font-size: 34px;
            font-weight: bold;
            left: 95px;
            letter-spacing: 1px;
            line-height: 1;
            padding: 12px 18px;
            position: absolute;
            right: 10px;
            text-align: center;
            top: 9px;
            z-index: 2;
        }

        .meta-strip {
            background: #f3f3f3;
            border: 1px solid #d7d7d7;
            color: #333333;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 5px 8px;
            text-align: center;
        }

        .brand-title {
            background: #058b3a;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: .5px;
            margin: 10px 0 6px;
            padding: 7px 10px;
            text-transform: uppercase;
        }

        .line-title {
            background: #ffd900;
            color: #111111;
            font-size: 12px;
            font-weight: bold;
            margin: 6px 0 4px;
            padding: 5px 9px;
        }

        .grid {
            border-collapse: collapse;
            margin-bottom: 8px;
            width: 100%;
        }

        .grid td {
            padding: 5px 7px 8px;
            vertical-align: top;
            width: 33.333%;
        }

        .item {
            height: 108px;
            overflow: hidden;
            position: relative;
        }

        .item-table {
            border-collapse: collapse;
            width: 100%;
        }

        .item-table td {
            padding: 0;
            vertical-align: top;
        }

        .photo-cell {
            text-align: center;
            width: 76px;
        }

        .product-img {
            height: 74px;
            object-fit: contain;
            width: 74px;
        }

        .info-cell {
            padding-left: 7px !important;
        }

        .name {
            color: #111111;
            font-size: 11.5px;
            font-weight: bold;
            line-height: 1.05;
            margin-bottom: 2px;
        }

        .detail {
            color: #333333;
            font-size: 8.6px;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .limit {
            color: #058b3a;
            font-size: 8.5px;
            font-weight: bold;
            line-height: 1.15;
            margin-bottom: 3px;
        }

        .price {
            background: #ffd900;
            border-radius: 5px;
            color: #f00000;
            display: inline-block;
            font-size: 19px;
            font-weight: bold;
            line-height: 1;
            min-width: 76px;
            padding: 8px 9px 7px;
        }

        .price small {
            font-size: 13px;
        }

        .sale-type {
            color: #333333;
            display: block;
            font-size: 7.8px;
            font-weight: bold;
            margin-top: 2px;
        }

        .promo {
            background: #058b3a;
            color: #ffffff;
            display: inline-block;
            font-size: 7.8px;
            font-weight: bold;
            margin-top: 3px;
            padding: 2px 5px;
        }

        .footer {
            border-top: 2px solid #d71920;
            bottom: 0;
            display: table;
            margin-top: 8px;
            width: 100%;
        }

        .qr-box {
            background: #058b3a;
            color: #ffffff;
            display: table-cell;
            font-size: 11px;
            font-weight: bold;
            padding: 8px;
            vertical-align: middle;
            width: 130px;
        }

        .valid-box {
            color: #222222;
            display: table-cell;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .5px;
            padding: 9px 12px;
            text-align: center;
            vertical-align: middle;
        }

        .brand-box {
            background: #d71920;
            color: #ffffff;
            display: table-cell;
            font-size: 8px;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            width: 210px;
        }

        .no-products {
            color: #555555;
            font-size: 14px;
            font-weight: bold;
            margin: 40px 0;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $productosPorMarca = $productos->groupBy('id_marca');
    $paginas = [];

    foreach ($productosPorMarca as $productosMarca) {
        $marca = optional($productosMarca->first()->marca)->descripcion ?: 'Sin marca';
        $productosPorLinea = $productosMarca->groupBy('id_linea');

        foreach ($productosPorLinea as $productosLinea) {
            $linea = optional($productosLinea->first()->linea)->descripcion_linea ?: 'Sin linea';

            foreach ($productosLinea->values()->chunk(18) as $chunk) {
                $paginas[] = [
                    'marca' => $marca,
                    'linea' => $linea,
                    'productos' => $chunk,
                ];
            }
        }
    }
@endphp

@forelse($paginas as $pagina)
    <div class="page">
        <div class="hero">
            <img src="{{ public_path('images/background.webp') }}" class="hero-bg" alt="Fondo">
            <div class="logo-box">
                <img src="{{ public_path('images/logo_color.webp') }}" alt="Logo">
            </div>
            <div class="hero-title">Distribuidora H&J</div>
        </div>

        <div class="meta-strip">
            Catalogo H&J | {{ $resumen['total_productos'] }} productos disponibles | {{ $resumen['total_promociones'] }} promociones | Generado: {{ $resumen['fecha'] }}
        </div>

        <div class="brand-title">Marca: {{ $pagina['marca'] }}</div>
        <div class="line-title">Linea: {{ $pagina['linea'] }}</div>

        <table class="grid">
            @foreach($pagina['productos']->chunk(3) as $fila)
                <tr>
                    @foreach($fila as $producto)
                        @php
                            $foto = $producto->foto_producto && file_exists(storage_path('app/private/' . $producto->foto_producto))
                                ? storage_path('app/private/' . $producto->foto_producto)
                                : public_path('images/logo_color.webp');
                            $ventaPrincipal = $producto->formaVentas->sortBy('precio_venta')->first();
                            $precio = $ventaPrincipal ? $ventaPrincipal->precio_venta : 0;
                            $tipoVenta = $ventaPrincipal ? $ventaPrincipal->tipo_venta : 'Sin forma de venta';
                            $detalle = $producto->presentacion ?: $producto->detalle_cantidad;
                        @endphp
                        <td>
                            <div class="item">
                                <table class="item-table">
                                    <tr>
                                        <td class="photo-cell">
                                            <img src="{{ $foto }}" alt="{{ $producto->nombre_producto }}" class="product-img">
                                        </td>
                                        <td class="info-cell">
                                            <div class="name">{{ $producto->nombre_producto }}</div>
                                            <div class="detail">{{ $detalle }}</div>
                                            <div class="limit">Max. {{ max(1, min((int) $producto->cantidad, 6)) }} {{ $producto->detalle_cantidad }} por boleta</div>
                                            <div class="price">
                                                <small>Bs.</small>{{ number_format($precio, 2, '.', '') }}
                                            </div>
                                            <span class="sale-type">{{ $tipoVenta }}</span>
                                            @if($producto->promocion)
                                                <span class="promo">
                                                    @if($producto->descripcion_descuento_porcentaje)
                                                        Desc. {{ $producto->descripcion_descuento_porcentaje }}%
                                                    @else
                                                        Promo
                                                    @endif
                                                    @if($producto->descripcion_regalo)
                                                        + {{ $producto->descripcion_regalo }}
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    @endforeach

                    @for($i = $fila->count(); $i < 3; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <div class="no-products">No hay productos disponibles para generar el catalogo.</div>
@endforelse
</body>
</html>
