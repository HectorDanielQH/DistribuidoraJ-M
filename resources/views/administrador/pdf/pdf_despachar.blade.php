<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Despacho Final</title>
    <style>
        /* CONFIGURACIÓN DE PÁGINA */
        @page { margin: 0.5cm 0.6cm; }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #222;
            font-size: 11px;
            line-height: 1.2;
        }

        /* === HEADER DOCUMENTO === */
        .doc-header { width: 100%; border-bottom: 3px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: 900; color: #2c3e50; text-transform: uppercase; padding-top: 5px; }
        
        /* === RESUMEN === */
        .summary-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px; margin-bottom: 15px; border-radius: 5px; }
        .summary-val { font-size: 14px; font-weight: bold; color: #2c3e50; }
        .summary-label { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 1px; }

        /* === ESTRUCTURA TABLA === */
        .layout-table { width: 100%; border-collapse: separate; border-spacing: 12px 15px; table-layout: fixed; }
        .layout-table td { vertical-align: top; width: 49%; }
        .spacer { width: 2% !important; border: none; }

        /* === CLASES DE UTILIDAD === */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { font-size: 8px; padding: 1px 3px; border-radius: 3px; font-weight: bold; color: #fff; margin-left: 2px; }
        .bg-red { background-color: #d32f2f; }
        
        /* FIRMAS */
        .signatures-box { margin-top: 30px; page-break-inside: avoid; }
        .sig-line { border-top: 1px solid #555; width: 80%; margin: 0 auto 5px auto; }
        .sig-text { font-size: 9px; text-transform: uppercase; text-align: center; color: #555; font-weight: bold; }
    </style>
</head>
<body>

    {{-- PROCESAMIENTO DE IMAGEN A BASE64 (Vital para DomPDF) --}}
    @php
        $pathImagen = public_path('images/logo_distribuidora.jpg'); 
        $logoBase64 = null;
        // Imagen por defecto transparente si no encuentra el logo (evita errores)
        $cssBackground = 'none';

        if (file_exists($pathImagen)) {
            $type = pathinfo($pathImagen, PATHINFO_EXTENSION);
            $data = file_get_contents($pathImagen);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $logoBase64 = $base64;
            // Preparamos la cadena CSS para el fondo
            $cssBackground = "url('$base64')";
        }

        // Cálculos
        $itemsAgrupados = $pedidos->groupBy('numero_pedido');
        $totalDinero = 0; $totalItems = 0;
        foreach($pedidos as $p) {
            $linea = $p->cantidad_pedido * $p->precio_venta;
            if ($p->promocion && $p->descripcion_descuento_porcentaje > 0) $linea -= $linea * ($p->descripcion_descuento_porcentaje / 100);
            $totalDinero += $linea; $totalItems += $p->cantidad_pedido;
        }
    @endphp

    <table class="doc-header">
        <tr>
            <td valign="top">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 50px; height: auto; float: left; margin-right: 15px;">
                @endif
                <div style="font-size: 11px; color: #666; margin-top: 2px;">REPORTE DE CARGA Y DESPACHO</div>
            </td>
            <td align="right" valign="middle">
                <div style="font-size: 14px; font-weight: bold;">{{ date('d/m/Y') }}</div>
                <div style="font-size: 10px; color: #444;">{{ date('H:i A') }}</div>
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td align="center" style="border-right: 1px solid #ccc;"><div class="summary-val">{{ count($lista_de_pedidos) }}</div><div class="summary-label">Pedidos</div></td>
                <td align="center" style="border-right: 1px solid #ccc;"><div class="summary-val">{{ $totalItems }}</div><div class="summary-label">Items</div></td>
                <td align="center"><div class="summary-val" style="color: #2e7d32;">Bs {{ number_format($totalDinero, 2, ',', '.') }}</div><div class="summary-label">Total</div></td>
            </tr>
        </table>
    </div>

    <table class="layout-table">
        @foreach($lista_de_pedidos->chunk(2) as $row)
            <tr>
                @foreach($row as $cabecera)
                    @php
                        $productos = $itemsAgrupados[$cabecera->numero_pedido] ?? collect([]);
                        $subTotal = 0;
                    @endphp
                    <td>
                        <div style="
                            border: 1px solid #999; 
                            border-radius: 6px; 
                            overflow: hidden; 
                            min-height: 200px;
                            background-image: {{ $cssBackground }};
                            background-repeat: no-repeat;
                            background-position: center center;
                            background-size: 140px auto; /* Ajusta este tamaño (140px) según tu logo */
                            background-color: #fff; /* Fondo base blanco por si falla la imagen */
                        ">
                            <div style="background-color: rgba(255, 255, 255, 0.74);">
                                <div style="background-color: #2c3e50; color: #fff; padding: 8px 10px; border-bottom: 1px solid #000;">
                                    <span style="float: right; font-weight: bold; font-size: 13px;">#{{ $cabecera->numero_pedido }}</span>
                                    <div style="font-weight: bold; text-transform: uppercase; font-size: 11px;">
                                        {{ \Illuminate\Support\Str::limit($cabecera->nombres . ' ' . $cabecera->apellidos, 22) }}
                                    </div>
                                </div>

                                <div style="padding: 5px 10px; font-size: 10px; border-bottom: 1px solid #ccc; color: #444;">
                                    <b>Dir:</b> {{ \Illuminate\Support\Str::limit($cabecera->calle_avenida, 30) }}<br>
                                    <div style="margin-top: 2px;">
                                        <b>Zona:</b> {{ \Illuminate\Support\Str::limit($cabecera->zona_barrio, 18) }} 
                                        <span style="float: right;"><b>Cel. Comprador:</b> {{ $cabecera->celular }}</span>
                                    </div>
                                    <div style="margin-top: 2px;">
                                        @php
                                            $nombre_de_la_ruta = DB::table('rutas')->where('id', $cabecera->ruta_id)->value('nombre_ruta');
                                            $nombre_del_vendedor = DB::table('users')->where('id', $cabecera->id_vendedor)->get();
                                        @endphp
                                        <b>Ruta:</b> {{ $nombre_de_la_ruta }}
                                        <div style="float: right;"><b>Vendedor:</b> {{ \Illuminate\Support\Str::limit($nombre_del_vendedor->first()->nombres.' '.$nombre_del_vendedor->first()->apellido_paterno.' '.$nombre_del_vendedor->first()->apellido_materno ?? 'N/A', 20) }}</div>
                                    </div>
                                    <div style="margin-top: 2px;">
                                        <b>Cel. del Vendedor:</b> {{ $nombre_del_vendedor->first()->celular }}
                                    </div>
                                </div>
                                {{-- Tabla Productos --}}
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th width="50%" style="color: #666; font-size: 8px; text-transform: uppercase; padding: 5px; text-align: left; border-bottom: 1px solid #ccc;">Producto</th>
                                            <th width="15%" style="color: #666; font-size: 8px; text-transform: uppercase; padding: 5px; text-align: center; border-bottom: 1px solid #ccc;">Cant.</th>
                                            <th width="35%" style="color: #666; font-size: 8px; text-transform: uppercase; padding: 5px; text-align: right; border-bottom: 1px solid #ccc;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productos as $item)
                                            @php
                                                $parcial = $item->cantidad_pedido * $item->precio_venta;
                                                $esPromo = $item->promocion && $item->descripcion_descuento_porcentaje > 0;
                                                if ($esPromo) $parcial -= $parcial * ($item->descripcion_descuento_porcentaje / 100);
                                                $subTotal += $parcial;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 6px 5px; font-size: 10px;">
                                                    {{ $item->nombre_producto }}
                                                    <!--
                                                    @if($esPromo) <span class="badge bg-red">-{{ (int)$item->descripcion_descuento_porcentaje }}%</span> @endif
                                                    @if($item->descripcion_regalo) <br><span style="font-size:8px; color:#2e7d32;">+ {{ \Illuminate\Support\Str::limit($item->descripcion_regalo, 12) }}</span> @endif-->
                                                </td>
                                                <td style="padding: 6px 5px; font-size: 15px; text-align: center; font-weight: bold;">{{ $item->cantidad_pedido }}</td>
                                                <td style="padding: 6px 5px; font-size: 15px; text-align: right;">{{ number_format($parcial, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                {{-- Footer Total (Fondo verde sólido para tapar logo y resaltar total) --}}
                                <div style="background-color: #e8f5e9; border-top: 1px solid #a5d6a7; padding: 8px 10px; text-align: right;">
                                    TOTAL: <span style="font-size: 15px; font-weight: bold; color: #141414;">{{ number_format($subTotal, 2) }} Bs</span>
                                </div>
                            
                            </div> {{-- Fin Capa Filtro --}}
                        </div>
                    </td>

                    @if($loop->first && $row->count() > 1) <td class="spacer"></td> @endif
                @endforeach
                @if($row->count() == 1) <td class="spacer"></td><td style="border:none;"></td> @endif
            </tr>
        @endforeach
    </table>

    <div class="signatures-box">
        <table style="width: 100%;">
            <tr>
                <td width="33%"><div class="sig-line"></div><div class="sig-text">Almacén</div></td>
                <td width="33%"><div class="sig-line"></div><div class="sig-text">Conductor</div></td>
                <td width="33%"><div class="sig-line"></div><div class="sig-text">Revisado</div></td>
            </tr>
        </table>
    </div>

</body>
</html>