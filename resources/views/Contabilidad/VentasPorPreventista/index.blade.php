@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4"
         style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center px-3">
            <h1 class="text-white mb-2"
                style="font-weight: 700; letter-spacing: 1px; font-size: clamp(1.5rem, 4vw, 2.75rem);">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-weight: 500; font-size: clamp(1rem, 2.5vw, 1.4rem);">
                Panel de Ventar por Pre-Ventista
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1">
            <h2 class="text-dark mb-0" style="font-weight: 600; font-size: clamp(1.25rem, 3vw, 1.75rem);">
                Ventas por Día <i class="fas fa-calendar-day ms-2"></i>
            </h2>
        </div>
        <p class="text-muted px-1" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);">
            Aquí puedes visualizar y gestionar las ventas diarias de productos.
        </p>
    </div>

    <!--TABLA DE PRODUCTOS-->
    <div class="container-fluid mb-5">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-column px-1">
                    <label for="preventista" class="mb-1">
                        Seleccione fechas para realizar el cáculo: <span class="text-danger">*</span>
                    </label>

                    <!-- Selector + Botón: responsivo -->
                    <div class="d-flex flex-wrap align-items-stretch gap-2 mb-4">
                        <div class="flex-grow-1 min-w-0" style="min-width: 220px;">
                            <label for='fechaInicio' class="form-label">Fecha de Inicio:<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fechaInicio" placeholder="Fecha Inicio">
                            <label for='fechaInicio' class="form-label">Fecha de Fin:<span class="text-danger">*</span></label>
                            <input type="date" class="form-control mt-2" id="fechaFin" placeholder="Fecha Fin">
                        </div>
                        <button class="btn btn-primary flex-shrink-0 ml-2" id="btnBuscarVentasPorFecha">
                            Buscar
                        </button>
                    </div>

                    <h5 class="text-dark">
                        <strong>Lista de ventas por preventista</strong>
                    </h5>

                    <!-- Tabla responsiva -->
                    <div class="table-responsive mt-3">
                        <table id="tablaVentasPreventista" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Pre-Ventista</th>
                                    <th>Total Vendido</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Col derecha -->
            <div class="col-12 col-lg-7 d-flex flex-column px-1">
                <p class="text-muted mb-2" style="font-size: clamp(0.95rem, 2.2vw, 1.1rem);">
                    <strong>Detalle de pedidos.</strong>
                </p>

                <div class="table-responsive">
                    <table id="tablaDetallePedidos" class="table table-dark table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Nro. Pedido</th>
                                <th>Cliente</th>
                                <th>Total Pedido</th>
                                <th>Ruta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <p class="text-muted mt-4 mb-2" style="font-size: clamp(0.95rem, 2.2vw, 1.1rem);">
                    <strong>Detalle de productos.</strong>
                </p>

                <div class="table-responsive">
                    <table id="tablaDetalleProductos" class="table table-dark table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Cod. Prod.</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
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
        .card { transition: all 0.3s ease; }
        .card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); }
        .btn:hover { opacity: 0.9; }

        /* Select2 full width */
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single {
            height: 38px; padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        /* Ajustes responsive extra */
        @media (max-width: 576px) {
            /* Espaciado y stacking más cómodo en móviles */
            #btnBuscarVentasPorDia { width: 100%; }
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
        $(document).ready(function(){
            $('.select2').select2({ width: '100%' });

            // DataTables base, con responsive y scrollX para pantallas pequeñas
            $('#tablaVentasPreventista').DataTable({
                responsive: true,
                scrollX: true,
                language: {
                    url: '/i18n/es-ES.json'
                }
            });
            $('#tablaDetallePedidos').DataTable({
                responsive: true,
                scrollX: true,
                language: {
                    url: '/i18n/es-ES.json'
                }
            });
            $('#tablaDetalleProductos').DataTable({
                responsive: true,
                scrollX: true,
                language: {
                    url: '/i18n/es-ES.json'
                }
            });
        });

        $('#btnBuscarVentasPorFecha').on('click', function(){
            let fechaInicio=$('#fechaInicio').val();
            let fechaFin=$('#fechaFin').val();

            if(!fechaInicio || !fechaFin){
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione las fechas requeridas de Inicio y Fin',
                    text: 'Por favor, seleccione ambas fechas para continuar.',
                });
                return;
            }

            let url="{{ route('contabilidad.ventas.porPreventista.opciones', [':fechainicio', ':fechafin']) }}".replace(':fechainicio', fechaInicio).replace(':fechafin', fechaFin);

            $('#tablaVentasPreventista').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: { url: url, type: 'GET' },
                columns: [
                    { data: 'preventista', name: 'preventista' },
                    { data: 'total_vendido', name: 'total_vendido' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
            });
        });

        function verDetalleVenta(e){
            let fecha=e.getAttribute('data-fecha');
            let preventistaId=e.getAttribute('data-preventista');
            let url="{{ route('contabilidad.ventas.porDia.preventista.detallepedidos', [':fecha', ':idpreventista']) }}"
                     .replace(':fecha', fecha).replace(':idpreventista', preventistaId);

            $('#tablaDetallePedidos').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    url: '/i18n/es-ES.json'
                },
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: { url: url, type: 'GET' },
                columns: [
                    { data: 'nro_pedido', name: 'nro_pedido' },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'total_pedido', name: 'total_pedido' },
                    { data: 'ruta', name: 'ruta' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
            });
        }

        function verDetallePedido(idPedido){
            let url="{{ route('contabilidad.ventas.porDia.preventista.detallepedidos.detalle', ':idpedido') }}"
                      .replace(':idpedido', idPedido);

            $('#tablaDetalleProductos').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    url: '/i18n/es-ES.json'
                },
                destroy: true,
                responsive: true,
                ajax: { url: url, type: 'GET' },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'cantidad', name: 'cantidad' },
                    { data: 'precio_unitario', name: 'precio_unitario' },
                    { data: 'total', name: 'total' },
                ],
            });
        }
    </script>
@stop
