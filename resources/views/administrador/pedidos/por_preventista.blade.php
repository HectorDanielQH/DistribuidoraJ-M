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

    <div class="container mt-4 mb-5 shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-dark font-weight-bold">
                <i class="fas fa-filter text-primary mr-2"></i>Filtros de Búsqueda
            </h5>
        </div>
    
        <div class="card-body">
            <div class="row">
                <div class="col-lg-7 col-md-12 mb-3">
                    <label class="font-weight-bold text-muted small uppercase">Preventistas Responsables</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 160px; overflow-y: auto;">
                        <div class="row">
                            @foreach($preventistas as $preventista)
                            <div class="col-sm-6">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" 
                                        id="prev_{{ $preventista->id }}" 
                                        name="preventistas[]" 
                                        value="{{ $preventista->id }}">
                                    <label class="custom-control-label text-secondary" for="prev_{{ $preventista->id }}">
                                        {{ $preventista->nombres }} {{ $preventista->apellido_paterno }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-md-12 mb-3">
                    <label class="font-weight-bold text-muted small uppercase">Estado del Despacho</label>
                    <div class="d-flex align-items-center pt-2">
                        <div class="custom-control custom-radio custom-control-inline mr-4">
                            <input type="radio" id="radio_despachados" name="estado_filtro" class="custom-control-input" value="despachados" checked>
                            <label class="custom-control-label" for="radio_despachados">
                                <span class="badge badge-success px-2 py-1">📦 Despachados</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="radio_pendientes" name="estado_filtro" class="custom-control-input" value="pendientes">
                            <label class="custom-control-label" for="radio_pendientes">
                                <span class="badge badge-warning px-2 py-1 text-white">⏳ Pendientes</span>
                            </label>
                        </div>
                    </div>
                    <p class="text-muted small mt-3 italic">Muestra productos según su estado actual en almacén.</p>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <button type="button" id="btnReset" class="btn btn-link btn-sm text-muted text-decoration-none">
                Limpiar selección
            </button>
            <button id="filtrar" class="btn btn-primary shadow-sm px-4 font-weight-bold">
                <i class="fas fa-sync-alt mr-1"></i> Actualizar Listado
            </button>
        </div>
    </div>

    <div class="container mb-4">
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
            <tbody>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <th colspan="5" class="text-right">Total estimado a recaudar: </th>
                    <th id="total-pagina" class="text-right text-primary"></th>
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
        $(document).ready(function() {
            let table = $('#tablaPedidosDespachados').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 1000,
                lengthMenu: [5, 10, 25, 50, 100, 500, 1000],
                language: {
                    url: '/i18n/es-ES.json',
                },
                // --- MODIFICACIÓN AQUÍ ---
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.visualizacionPorPreventista') }}",
                    data: function (d) {
                        // Capturamos los IDs de los preventistas marcados
                        let preventistas = [];
                        $('input[name="preventistas[]"]:checked').each(function() {
                            preventistas.push($(this).val());
                        });

                        d.preventistas = preventistas;
                        // Capturamos el valor del radio button (despachados o pendientes)
                        d.estado = $('input[name="estado_filtro"]:checked').val();

                    }
                },
                // -------------------------
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'imagen', name: 'imagen'},
                    { data: 'nombre_producto',  name: 'nombre_producto' },
                    { data: 'stock_producto',   name: 'stock_producto',   className: 'text-end' },
                    { data: 'cantidad_despacho',name: 'cantidad_despacho',className: 'text-end' },
                    { data: 'ingreso_estimado', name: 'ingreso_estimado', className: 'text-end' }
                ],
                dom:
                "<'row align-items-center mb-2'<'col-12 d-flex flex-wrap justify-content-between gap-2'\
                    <'d-flex flex-wrap align-items-center gap-2 dt-left'Bl>\
                    <'dt-right'f>>>\
                <'row'<'col-12'tr>>\
                <'row align-items-center mt-2'<'col-12 d-flex flex-wrap justify-content-between gap-2'\
                    <'dt-info'i><'dt-paging'p>>>",
                buttons: [
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                //sumar ingreso estimado
                drawCallback: function() {
                    Swal.close();
                    let api = this.api();
                    console.log('Respuesta AJAX:', api.ajax.json()); // Verifica la respuesta del servidor
                    let totalPagina = api.ajax.json().sumaGlobal || 0; // Asegúrate de que el servidor envíe este dato
                    $('#total-pagina').html('Bs. ' + totalPagina.toLocaleString('es-BO') + ''); // Formatear número a formato boliviano
                }
            });

            // Acción del botón Filtrar
            $('#filtrar').on('click', function(e) {
                e.preventDefault();
                table.draw(); // Esto dispara la petición AJAX con los nuevos datos

                Swal.fire({
                    icon: 'success',
                    title: '¡Datos actualizados!',
                    text: 'La tabla se ha actualizado con los filtros seleccionados.',
                    showConfirmButton: false
                });
            });

            // Acción del botón Limpiar (Opcional pero recomendado)
            $('#btnReset').on('click', function() {
                $('input[name="preventistas[]"]').prop('checked', false);
                $('#radio_despachados').prop('checked', true);
                table.draw();
            });
        });
    </script>
@stop