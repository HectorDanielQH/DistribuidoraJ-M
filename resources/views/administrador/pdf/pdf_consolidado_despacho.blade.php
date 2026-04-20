<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consolidado de despacho</title>
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
        * {
            box-sizing: border-box;
            font-family: 'ReporteSans', sans-serif !important;
        }
        body {
            margin: 0;
            color: #17211d;
            font-size: 10px;
        }
        .header {
            width: 100%;
            border-bottom: 5px solid #263746;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 80px;
        }
        .logo {
            width: 58px;
            height: 40px;
            object-fit: contain;
        }
        h1 {
            margin: 0;
            color: #263746;
            font-size: 24px;
            text-transform: uppercase;
        }
        .subtitle {
            color: #527060;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
        }
        .date {
            text-align: right;
            color: #17211d;
            font-size: 12px;
            font-weight: 700;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary td {
            border: 1px solid #d6dee3;
            padding: 8px;
            text-align: center;
        }
        .summary strong {
            display: block;
            color: #166534;
            font-size: 16px;
        }
        .summary span {
            display: block;
            color: #263746;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .filters {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .filters td {
            border: 1px solid #d6dee3;
            padding: 6px 8px;
        }
        .filters .label {
            width: 110px;
            background: #e8f2ee;
            color: #263746;
            font-weight: 700;
            text-transform: uppercase;
        }
        .products {
            width: 100%;
            border-collapse: collapse;
        }
        .products th {
            background: #263746;
            color: #ffffff;
            padding: 7px 6px;
            text-align: left;
            text-transform: uppercase;
            font-size: 9px;
        }
        .products td {
            border: 1px solid #d6dee3;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .products tbody tr:nth-child(even) {
            background: #f7faf9;
        }
        .code {
            width: 72px;
            font-weight: 700;
        }
        .image-cell {
            width: 62px;
            text-align: center;
        }
        .product-image {
            max-width: 45px;
            max-height: 45px;
            object-fit: contain;
        }
        .product-name {
            font-weight: 700;
        }
        .stock {
            width: 110px;
        }
        .quantity {
            width: 115px;
            color: #0f766e;
            font-size: 12px;
            font-weight: 700;
        }
        .money {
            width: 115px;
            color: #166534;
            font-size: 12px;
            font-weight: 700;
            text-align: right;
        }
        .empty {
            padding: 28px;
            text-align: center;
            color: #64748b;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @php
        $titulo = $estado === 'despachados'
            ? 'Consolidado de productos despachados'
            : 'Consolidado para preparar despacho';
        $logo = public_path('images/logo_color.webp');
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(file_exists($logo))
                        <img src="{{ $logo }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td>
                    <h1>{{ $titulo }}</h1>
                    <div class="subtitle">Distribuidora H &amp; J - hoja para almacen y reparto</div>
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
                <strong>{{ $resumen['productos'] }}</strong>
                <span>Productos</span>
            </td>
            <td>
                <strong>{{ number_format($resumen['unidades'], 0, '.', ',') }}</strong>
                <span>Cantidad a sacar</span>
            </td>
            <td>
                <strong>Bs {{ number_format($resumen['total'], 2, '.', ',') }}</strong>
                <span>Ingreso estimado</span>
            </td>
        </tr>
    </table>

    <table class="filters">
        <tr>
            <td class="label">Ruta</td>
            <td>{{ $filtros['ruta'] }}</td>
            <td class="label">Preventista</td>
            <td>{{ $filtros['preventista'] }}</td>
        </tr>
        @if($filtros['fecha_entrega'])
            <tr>
                <td class="label">Fecha despacho</td>
                <td colspan="3">{{ $filtros['fecha_entrega'] }}</td>
            </tr>
        @endif
    </table>

    <table class="products">
        <thead>
            <tr>
                <th class="code">Codigo prod.</th>
                <th class="image-cell">Imagen</th>
                <th>Producto</th>
                <th class="stock">Stock del producto</th>
                <th class="quantity">Cantidad a sacar</th>
                <th class="money">Ingreso estimado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $producto)
                <tr>
                    <td class="code">{{ $producto['codigo_producto'] }}</td>
                    <td class="image-cell">
                        @if($producto['imagen'])
                            <img src="{{ $producto['imagen'] }}" class="product-image" alt="">
                        @else
                            -
                        @endif
                    </td>
                    <td class="product-name">{{ $producto['nombre_producto'] }}</td>
                    <td class="stock">{{ $producto['stock_producto'] }}</td>
                    <td class="quantity">{{ $producto['cantidad_despacho'] }}</td>
                    <td class="money">Bs {{ number_format($producto['ingreso_estimado'], 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No hay productos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
