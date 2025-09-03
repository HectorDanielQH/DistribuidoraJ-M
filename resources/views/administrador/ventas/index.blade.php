@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Ventas Realizadas
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container text-center mt-3">
        <h3 class="text-lg">
            <i class="fas fa-shopping-cart mr-2"></i> Ventas Realizadas
            Seleccione un rango de fechas para ver las ventas realizadas.
        </h3>
        <div class="row mt-3">
            <div class="col-5">
                <label for="fecha_inicio" class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i> Fecha de Inicio
                </label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
            </div>
            <div class="col-5">
                <label for="fecha_fin" class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i> Fecha de Fin
                </label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
            </div>
            <div class="col-2">
                <button id="btnGenerar" class="btn btn-success mt-4" onclick="generarReporte()">
                    <i class="fas fa-file-alt me-2"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>
    <div class="container">
        <table class="table table-striped table-bordered mt-4" id="tablaVentas">
            <thead class="thead-dark">
                <tr>
                    <th>Nro. de Pedido</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="text-center" id="tbodyventas">
                <tr>
                    <td colspan="3" class="text-center">Seleccione un rango de fechas para ver las ventas.</td>
                </tr>
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <style>
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>

    <script>
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
                didOpen: () => {
                    Swal.showLoading();
                }
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
                    const tbody = document.getElementById('tbodyventas');
                    tbody.innerHTML = ''; // Limpiar el contenido previo
                    if (response.length > 0) {
                        response.forEach(venta => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>Pedido #${venta.numero_pedido}</td>
                                <td>${venta.nombres} ${venta.apellidos}</td>
                                <td>
                                    <button class="btn btn-primary" onclick="verPedidoCliente(this)" id-numero-pedido="${venta.numero_pedido}">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center">No se encontraron ventas en este rango de fechas.</td></tr>';
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron obtener las ventas. Intente nuevamente.',
                    });
                }
            });
        }

        function verPedidoCliente(e) {
        let numeroPedido = $(e).attr('id-numero-pedido');
        let widthValue = window.innerWidth <= 600 ? '100%' : '60%';
        $.ajax({
            url: "{{ route('ventas.administrador.visualizacionPedido', ':id') }}".replace(':id', numeroPedido),
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

                response.pedidos.forEach(item => {
                    const descuento = item.descripcion_descuento_porcentaje ?? 0;
                    const total = (item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100);

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
                            <td><strong>${total} Bs</strong></td>
                        </tr>`;
                });

                const totalPedido = response.pedidos.reduce((sum, item) => {
                    const descuento = item.descripcion_descuento_porcentaje ?? 0;
                    return sum + ((item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100));
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
                error: function (xhr, status, error) {
                    
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Error',
                        text: 'No se pudo cargar el pedido. Intenta de nuevo.',
                    });
                }
            });
        }
    </script>
@stop