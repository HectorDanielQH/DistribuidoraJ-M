<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Despachados</title>
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
        <h2>Productos Despachados</h2>
    </div>
    
    @foreach($lista_de_pedidos as $lista_pedido)
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th colspan="2" style="text-align: left; font-size: 18px; padding: 10px 5px; border-bottom: 2px solid #000;">
                        Pedido #{{ $lista_pedido->numero_pedido }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 30%; padding: 8px; font-weight: bold;">Fecha de Pedido:</td>
                    <td style="padding: 8px;">{{ $lista_pedido->fecha_pedido }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Cliente:</td>
                    <td style="padding: 8px;">
                        {{ $lista_pedido->nombres }} {{ $lista_pedido->apellido_paterno }} {{ $lista_pedido->apellido_materno }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Celular:</td>
                    <td style="padding: 8px;">{{ $lista_pedido->celular }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Ubicación:</td>
                    <td style="padding: 8px;">{{ $lista_pedido->ubicacion }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Cod. Producto</th>
                    <th>Nombre del Producto</th>
                    <th>Cant. Solicitada</th>
                    <th>Precio U.</th>
                    <th>Promocion</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    @if($pedido->numero_pedido == $lista_pedido->numero_pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->nombre_producto }}</td>
                            <td>{{ $pedido->cantidad_pedido }} ({{$pedido->tipo_venta}})</td>
                            <td>{{$pedido->precio_venta}}</td>
                            @if($pedido->promocion)
                                <td>
                                @if($pedido->descripcion_descuento_porcentaje> 0)
                                    Descuento: {{ $pedido->descripcion_descuento_porcentaje }}%
                                @endif
                                <br>
                                @if($pedido->descripcion_regalo != null && $pedido->descripcion_regalo != '')
                                    Regalo: {{ $pedido->descripcion_regalo }}
                                @endif
                                </td>
                            @else
                                <td>No aplica</td>
                            @endif
                            <td>
                                {{ ($pedido->cantidad_pedido * $pedido->precio_venta) - ($pedido->cantidad_pedido * $pedido->precio_venta * $pedido->descripcion_descuento_porcentaje / 100) }} Bs.-
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">Total:</td>
                    <td>
                        @php
                            $total = 0;
                            foreach($pedidos as $pedido) {
                                if($pedido->numero_pedido == $lista_pedido->numero_pedido) {
                                    $total += ($pedido->cantidad_pedido * $pedido->precio_venta) - ($pedido->cantidad_pedido * $pedido->precio_venta * $pedido->descripcion_descuento_porcentaje / 100);
                                }
                            }
                        @endphp
                        {{ $total }} Bs.-
                    </td>
            </tbody>
        </table>
        <!--CAJA OBSERVACIONES PARA LLENAR A MANO-->
        <div class="">
            <h5>Observaciones:</h5>
            <p style="border: 1px solid #ccc; padding: 10px; height: 80px; margin-top: 10px;"></p>
        </div>
    @endforeach

    <!--SUMA TOTAL DE TODOS LOS PEDIDOS-->
    <div class="container">
        <h4>Monto Total Estimado a Recaudar:
            @php
                $total_general = 0;
                foreach($pedidos as $pedido) {
                    $total_general += ($pedido->cantidad_pedido * $pedido->precio_venta) - ($pedido->cantidad_pedido * $pedido->precio_venta * $pedido->descripcion_descuento_porcentaje / 100);
                }
            @endphp
            {{ $total_general }}
            Bs.-
        </h4>
    </div>
    <!--FIRMA DEL REPARTIDOR Y DEL QUE ENTREGA EL PEDIDO-->
    <table>
        <tr>
            <td style="width: 50%; text-align: center;">
                <h4>Firma del Repartidor:</h4>
                <p style="border: 1px solid #ccc; padding: 10px; height: 50px; margin-top: 10px;"></p>
            </td>
            <td style="width: 50%; text-align: center;">
                <h4>Firma del Despachador:</h4>
                <p style="border: 1px solid #ccc; padding: 10px; height: 50px; margin-top: 10px;"></p>
            </td>
        </tr>
    </table>
    <p style="text-align: center; font-size: 10px; color: #888;">
        Este documento es una representación impresa de los pedidos despachados y debe ser guardado para futuras referencias.
    </p>
    <!--FIRMA DEL REPARTIDOR QUE INDIQUE QUE ENTREGO LA PLATA-->
    <table>
        <tr>
            <td style="width: 50%; text-align: center;">
                <h4>Firma del Repartidor (Entrega de ingresos generados):</h4>
                <p style="border: 1px solid #ccc; padding: 10px; height: 50px; margin-top: 10px;"></p>
            </td><td style="width: 50%; text-align: center;">
                <h4>Firma del Recepcionista (Recepción de ingresos generados):</h4>
                <p style="border: 1px solid #ccc; padding: 10px; height: 50px; margin-top: 10px;"></p>
            </td>
        </tr>
    </table>
    <div class="">
        <h5>Observaciones:</h5>
        <p style="border: 1px solid #ccc; padding: 10px; height: 80px; margin-top: 10px;"></p>
    </div>

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
