@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Pedidos Pendientes
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list"></i> Lista de Pedidos Pendientes
                        </h3>
                        <div class="card-tools d-flex">
                            <button class="btn btn-success btn-sm mr-4" id="btnDespacharPedidos">
                                <i class="fas fa-truck"></i> Despachar Pedidos 
                            </button>

                            <button class="btn btn-success btn-sm mr-4" id="btnCantidadPedidos">
                                <i class="fas fa-truck"></i> Ver cantidad para despacho
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <table class="table table-striped table-bordered" id="pedidosTabla">
            <thead>
                <tr>
                    <th>Nro. de Pedido</th>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Ruta</th>
                    <th>Preventista</th>
                    <th>Fecha Pedido</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-CaLdjDnDQsm4dp6FAi+hDGbnmYMabedJHm00x/JJgmTsQ495TW5sVn4B7kcyThok" crossorigin="anonymous">
  
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.js" integrity="sha384-SY2UJyI2VomTkRZaMzHTGWoCHGjNh2V7w+d6ebcRmybnemfWfy9nffyAuIG4GJvd" crossorigin="anonymous"></script>
    
    <script>
        $(document).ready(function () {
            $('#pedidosTabla').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: { url: '/i18n/es-ES.json' },
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.visualizacion') }}",
                },
                columns: [
                    { data:'numero_pedido'},
                    { data:'cliente' },
                    { data:'direccion' },
                    { data:'ruta' },
                    { data:'preventista' },
                    { data:'fecha_pedido' },
                    { data:'estado' },
                    { data: 'acciones', orderable: false, searchable: false }
                ],
                columnDefs: [
                    { targets: '_all', className: 'dt-head-center dt-body-center align-middle td-center' }
                ],
                drawCallback: function () {
                    const $w = $('#tabla-productos_wrapper');
                    $w.find('td.td-center .d-flex').addClass('justify-content-center');
                    $w.find('td.td-center img').addClass('d-block mx-auto');
                }
            });
        });



        function verPedidoCliente(e) {
        let numeroPedido = $(e).attr('id-numero-pedido');
        let widthValue = window.innerWidth <= 600 ? '100%' : '60%';
        $.ajax({
            url: "{{ route('pedidos.administrador.visualizacionPedido', ':id') }}".replace(':id', numeroPedido),
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

        $('#btnDespacharPedidos').on('click', function () {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas despachar todos los pedidos pendientes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '✅ Sí, despachar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pedidos.administrador.despacharPedido') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            
                        },
                        success: function (response) {
                            
                            Swal.fire({
                                icon: 'success',
                                title: '✅ Pedidos Despachados',
                                text: 'Todos los pedidos pendientes han sido despachados exitosamente.',
                                confirmButtonText: 'Cerrar'
                            }).then(() => {
                                //pedidos.administrador.visualizacionDespachados
                                window.location.href = "{{ route('pedidos.administrador.visualizacionDespachados') }}";
                            });
                        },
                        error: function (xhr, status, error) {
                            
                            Swal.fire({
                                icon: 'error',
                                title: '❌ Error al Despachar',
                                text: 'No se pudieron despachar los pedidos. Intenta de nuevo.',
                            });
                        }
                    });
                }
            });
        });

        $('#btnCantidadPedidos').on('click', function () {
            /*nueva venetana con otra ruta*/
            window.open("{{ route('pedidos.administrador.visualizacionParaDespachado') }}", '_blank');
        });

    </script>
@stop