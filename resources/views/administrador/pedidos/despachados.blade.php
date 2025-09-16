@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel en pedidos proceso de despacho y/o entrega
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
                            <i class="fas fa-list"></i> Lista de pedidos para despachar
                        </h3>
                        <div class="card-tools d-flex">
                            <a href="{{route('pedidos.administrador.visualizacionPdfDespachar')}}" target="_blank" class="btn btn-info btn-sm mr-4">
                                <i class="fas fa-file-pdf"></i> Imprimir Pedidos para el Repartidor
                            </a>

                            <button class="btn btn-success btn-sm mr-4" onclick="contabilizarTodosLosPendientes(this)">
                                <i class="fas fa-check"></i> Contabilizar Pedidos 
                            </button>

                            <a href="{{route('pedidos.administrador.devolucionPedido')}}" class="btn btn-danger btn-sm mr-4">
                                <i class="fas fa-undo-alt"></i> Devoluciones 
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <table class="table table-striped table-bordered" id="tablaPedidosDespachados">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen Producto</th>
                    <th>Nombre Producto</th>
                    <th>Stock Producto</th>
                    <th>Cant. Despacho</th>
                    <th>Ingreso Estimado</th>
                </tr>
            </thead>
            <tfoot>
                <tr colspan="6">
                    <th colspan="6" style="text-align: right;">Total estimado a recaudar: {{ $suma_total_estimada }} Bs.-</th>
                </tr>
            </tfoot>
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
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        /* separación entre botones y controles */
.dt-left .btn { margin-right: .5rem; }
.dt-left .dt-buttons { margin-right: .5rem; }

/* tamaño mínimo del buscador y del selector de filas */
.dataTables_filter input { min-width: 240px; }
.dataTables_length select { min-width: 90px; }

/* encabezados sin salto y celdas centradas verticalmente */
table.dataTable thead th { white-space: nowrap; }
table.dataTable th, table.dataTable td { vertical-align: middle; }

/* si hay muchas columnas, permite scroll horizontal */
.dataTables_wrapper .dataTables_scrollBody { overflow-x: auto !important; }

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
        $('#tablaPedidosDespachados').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            deferRender: true,
            pagingType: 'full_numbers',
            pageLength: 10,
            lengthChange: true,                   // << muestra selector de cantidad
            lengthMenu: [5, 10, 25, 50, 100],
            language: {
                url: '/i18n/es-ES.json',
                lengthMenu: 'Mostrar _MENU_ filas'  // etiqueta del selector
            },

            ajax: "{{ route('pedidos.administrador.visualizacionDespachados') }}",
            columns: [
                { data: 'codigo_producto', name: 'codigo_producto' },
                { data: 'imagen', name: 'imagen'},
                { data: 'nombre_producto',  name: 'nombre_producto' },
                { data: 'stock_producto',   name: 'stock_producto',   className: 'text-end' },
                { data: 'cantidad_despacho',name: 'cantidad_despacho',className: 'text-end' },
                { data: 'ingreso_estimado', name: 'ingreso_estimado', className: 'text-end' }
            ],
            order: [[0, 'desc']],

            // TOP: [Botones + Length]  ——  [Filtro]
            // MIDDLE: tabla
            // FOOTER: [Info] —— [Paginación]
            dom:
                "<'row align-items-center mb-2'<'col-12 d-flex flex-wrap justify-content-between gap-2'\
                    <'d-flex flex-wrap align-items-center gap-2 dt-left'Bl>\
                    <'dt-right'f>>>\
                <'row'<'col-12'tr>>\
                <'row align-items-center mt-2'<'col-12 d-flex flex-wrap justify-content-between gap-2'\
                    <'dt-info'i><'dt-paging'p>>>",

            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                    className: 'btn btn-danger',
                    titleAttr: 'Exportar a PDF',
                    exportOptions: { columns: [0,2,3,4,5] },
                    customize: function (doc) {
                            doc.styles.title = { color: '#4a4a4a', fontSize: 20, alignment: 'center' };
                            doc.styles.tableHeader = { fillColor: '#1abc9c', color: 'white', alignment: 'center' };
                            if (doc.content[1]) {
                            doc.content[1].margin = [0,0,0,0];
                            doc.content[1].layout = {
                                hLineWidth: () => 0.5, vLineWidth: () => 0.5,
                                hLineColor: () => '#aaa', vLineColor: () => '#aaa',
                                paddingLeft: () => 4, paddingRight: () => 4
                            };
                            }
                        }
                    },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-info',
                    titleAttr: 'Imprimir',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i> Columnas',
                    className: 'btn btn-secondary',
                    titleAttr: 'Columnas'
                }
            ]
        });
    </script>

    <script>
        function verPedidoPorProducto(e){
            let codigoProducto = e.getAttribute('id-codigo-producto');
            Swal.fire({
                title: 'Cargando pedidos...',
                html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espere.',
                showConfirmButton: false,
                allowOutsideClick: false,
            });

        }

        function contabilizarTodosLosPendientes(e) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas contabilizar todos los pedidos pendientes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, contabilizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Contabilizando...',
                        html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espere.',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                    });
                    $.ajax({
                        url: "{{ route('pedidos.administrador.contabilizarTodosLosPendientes') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Todos los pedidos pendientes han sido contabilizados.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al contabilizar los pedidos. Inténtalo de nuevo más tarde.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        }
    </script>
@stop