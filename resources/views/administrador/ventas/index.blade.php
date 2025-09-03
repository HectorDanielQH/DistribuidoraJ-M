{{-- resources/views/ventas/reporte.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.2rem; font-weight: 500;">
                Panel de Ventas Realizadas
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container text-center mt-3">
        <h3 class="text-lg">
            <i class="fas fa-shopping-cart mr-2"></i> Ventas Realizadas
            <small class="text-muted d-block mt-1">Seleccione un rango de fechas para ver las ventas realizadas.</small>
        </h3>

        <div class="row mt-3 g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="fecha_inicio" class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i> Fecha de Inicio
                </label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
            </div>
            <div class="col-12 col-md-5">
                <label for="fecha_fin" class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i> Fecha de Fin
                </label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button id="btnGenerar" class="btn btn-success" onclick="generarReporte()">
                    <i class="fas fa-file-alt me-2"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="table-responsive">
            <table id="tabla-ventas" class="table table-striped">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="tbody-ventas"></tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">TOTAL GENERAL</th>
                        <th id="total-general"></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }
        .btn:hover { opacity: 0.9; }
        /* Mejor visual para cabeceras de usuario */
        .tr-usuario > td {
            background:#f2f4f6; 
            font-weight:700;
        }
        /* Separador visual */
        .tr-sep > td {
            padding:0; 
            border-top:2px solid #e5e7eb;
        }
    </style>
@stop

@section('js')
    {{-- Dependencias (jQuery, SweetAlert2, etc.) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.js"
            integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Si no usas SlimSelect/ECharts aquí, puedes quitar estas dos líneas --}}
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>

    <script>
        // Formato moneda BOB
        function formatoMoneda(n) {
            return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(n ?? 0);
        }

        // Validación y fetch del reporte
        function generarReporte(){
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;

            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, seleccione un rango de fechas válido.',
                });
                return;
            }

            Swal.fire({
                title: 'Generando Reporte',
                text: 'Por favor, espere mientras se generan los datos.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('ventas.obtenerVentas.porfechas') }}",
                type: 'GET',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                },
                success: function(response) {
                    Swal.close();
                    pintarVentasAgrupadas(response);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron obtener las ventas. Intente nuevamente.',
                    });
                    console.error(xhr.responseText || xhr);
                }
            });
        }

        // Pintado agrupado por usuario con totales
        function pintarVentasAgrupadas(data) {
            const tbody = document.getElementById('tbody-ventas');
            tbody.innerHTML = '';

            (data.usuarios || []).forEach(u => {
                // Cabecera de USUARIO
                const trUser = document.createElement('tr');
                trUser.className = 'tr-usuario';
                trUser.innerHTML = `
                    <td colspan="4">
                        <i class="fas fa-user me-2"></i> ${u.usuario} (ID: ${u.id_usuario})
                    </td>
                `;
                tbody.appendChild(trUser);

                // Filas de PEDIDOS del usuario
                (u.pedidos || []).forEach(venta => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>Pedido #${venta.numero_pedido}</td>
                        <td>${venta.cliente}</td>
                        <td>${formatoMoneda(venta.total_pedido)}</td>
                        <td>
                            <button class="btn btn-primary"
                                    onclick="verPedidoCliente(this)"
                                    data-numero-pedido="${venta.numero_pedido}"
                                    data-id-usuario="${u.id_usuario}">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Subtotal por USUARIO
                const trSubtotal = document.createElement('tr');
                trSubtotal.innerHTML = `
                    <td colspan="2" style="text-align:right; font-weight:700;">Subtotal de ${u.usuario}:</td>
                    <td style="font-weight:700;">${formatoMoneda(u.subtotal_usuario)}</td>
                    <td></td>
                `;
                tbody.appendChild(trSubtotal);

                // Separador visual
                const trSep = document.createElement('tr');
                trSep.className = 'tr-sep';
                trSep.innerHTML = `<td colspan="4"></td>`;
                tbody.appendChild(trSep);
            });

            // Total general en el tfoot
            document.getElementById('total-general').textContent = formatoMoneda(data.total_general || 0);
        }

        // Ver detalles de un pedido
        function verPedidoCliente(btn) {
            const numeroPedido = btn.getAttribute('data-numero-pedido');
            const widthValue = window.innerWidth <= 600 ? '100%' : '60%';

            // Arma la URL de detalle de forma segura:
            // Opción 1: base con url() y concatenación (recomendada si tu ruta es /ventas/administrador/visualizacion-pedido/{id})
            const baseUrl = "{{ url('/ventas/administrador/visualizacion-pedido') }}";
            const urlShow = `${baseUrl}/${encodeURIComponent(numeroPedido)}`;

            // Opción 2 (si tienes una ruta con nombre y parámetro): descomenta y ajusta el nombre de la ruta
            // const urlShow = "{{ route('ventas.administrador.visualizacionPedido', ['id' => '__ID__']) }}".replace('__ID__', numeroPedido);

            $.ajax({
                url: urlShow,
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Cargando Pedido...',
                        html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espera un momento.',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        width: widthValue
                    });
                },
                success: function (response) {
                    Swal.close();

                    let html_tabla = `
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>📦 Código</th>
                                        <th>🧾 Producto</th>
                                        <th>📊 Stock</th>
                                        <th>🛒 Solicitado</th>
                                        <th>💵 Precio</th>
                                        <th>🎁 Promoción</th>
                                        <th>🧮 Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    (response.pedidos || []).forEach(item => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        const total = (item.cantidad_pedido * item.precio_venta) -
                                      ((item.cantidad_pedido * item.precio_venta * descuento) / 100);

                        html_tabla += `
                            <tr class="text-center">
                                <td><code>${item.codigo}</code></td>
                                <td>${item.nombre_producto}</td>
                                <td>${item.cantidad_stock} ${item.detalle_cantidad}</td>
                                <td><strong>${item.cantidad_pedido} ${item.tipo_venta}</strong></td>
                                <td>${item.precio_venta} Bs</td>
                                <td>
                                    ${item.promocion
                                        ? `<span class="badge bg-success mb-1">${descuento}%</span><br>
                                           <span class="badge bg-info">${item.descripcion_regalo ?? '🎁 Regalo'}</span>`
                                        : `<span class="badge bg-secondary">Sin Promoción</span>`}
                                </td>
                                <td><strong>${total.toFixed(2)} Bs</strong></td>
                            </tr>`;
                    });

                    const totalPedido = (response.pedidos || []).reduce((sum, item) => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        return sum + ((item.cantidad_pedido * item.precio_venta) -
                                      ((item.cantidad_pedido * item.precio_venta * descuento) / 100));
                    }, 0);

                    html_tabla += `
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <h5 class="text-success"><strong>🧾 Total Pedido: ${totalPedido.toFixed(2)} Bs</strong></h5>
                        </div>`;

                    Swal.fire({
                        title: `📋 Pedido N.º ${response.numero_pedido}`,
                        html: html_tabla,
                        icon: 'info',
                        width: widthValue,
                        showCloseButton: true,
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Error',
                        text: 'No se pudo cargar el pedido. Intenta de nuevo.',
                    });
                    console.error(xhr.responseText || xhr);
                }
            });
        }
    </script>
@stop
