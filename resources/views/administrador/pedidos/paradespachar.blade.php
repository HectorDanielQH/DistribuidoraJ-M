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
    <x-adminlte-modal id="modalMin" title="Descargar por preventistas">
        <p>
            Descargar los pedidos pendientes de despacho organizados por rutas facilitará la logística de entrega y optimizará el tiempo del repartidor.
        </p>

        @foreach($preventistas as $preventista)
            <div class="preventista-container mb-3">
                <input type="checkbox" class="d-none" id="check{{ $preventista->id }}" name="preventistas[]" value="{{ $preventista->id }}">
                
                <label for="check{{ $preventista->id }}" class="glass-card">
                    <div class="user-profile">
                        <div class="user-info">
                            <span class="role-tag">Personal de Campo</span>
                            <h4 class="user-name">
                                {{ $preventista->nombres }} 
                                <span class="last-name">{{ $preventista->apellido_paterno }} {{ $preventista->apellido_materno }}</span>
                            </h4>
                        </div>
                    </div>

                    <div class="status-section">
                        <div class="indicator-ring">
                            <div class="ring-fill"></div>
                            <i class="fas fa-plus icon-add"></i>
                            <i class="fas fa-check icon-check"></i>
                        </div>
                    </div>
                </label>
            </div>
        @endforeach

        <x-slot name="footerSlot">
            <x-adminlte-button class="mr-auto" theme="success" label="Descargar" onclick="descargarPdfRutas()"/>
            <x-adminlte-button theme="danger" label="Cancelar" data-dismiss="modal"/>
        </x-slot>        
    </x-adminlte-modal>


    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list"></i> Lista de pedidos para despachar
                        </h3>
                        <div class="card-tools d-flex justify-content-center align-items-center">
                            <a href="{{route('pedidos.administrador.visualizacionPdfDespachar.pedidosPendientes')}}" target="_blank" class="btn btn-info btn-sm mr-4">
                                <i class="fas fa-file-pdf"></i> Imprimir Pedidos para el Repartidor
                            </a>
                            
                            <x-adminlte-button label="Descagar por rutas" data-toggle="modal" data-target="#modalMin" class="btn btn-success btn-sm"/>
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
        /* Estética General: Moderno y Espacioso */
        .preventista-container {
            perspective: 1000px;
        }

        .glass-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: #ffffff;
            border: 2px solid #f1f5f9;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        /* Tipografía y Textos */
        .role-tag {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6366f1;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }

        .user-name {
            margin: 0;
            font-weight: 800;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .last-name {
            color: #94a3b8;
            font-weight: 400;
        }

        /* El "Anillo" de selección - El detalle visual clave */
        .indicator-ring {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .icon-check { display: none; color: white; }
        .icon-add { color: #cbd5e1; transition: all 0.3s ease; }

        /* --- EFECTOS DE ESTADO (MÁGICA) --- */

        /* Hover */
        .glass-card:hover {
            border-color: #6366f1;
            transform: scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.1);
        }

        .glass-card:hover .indicator-ring {
            border-color: #6366f1;
        }

        /* Seleccionado (Checkbox Checked) */
        input:checked + .glass-card {
            background: #0f172a; /* Fondo oscuro tipo Dark Mode */
            border-color: #0f172a;
        }

        input:checked + .glass-card .user-name { color: #ffffff; }
        input:checked + .glass-card .role-tag { color: #818cf8; }
        
        input:checked + .glass-card .indicator-ring {
            background: #6366f1;
            border-color: #6366f1;
            transform: rotate(360deg);
        }

        input:checked + .glass-card .icon-add { display: none; }
        input:checked + .glass-card .icon-check { display: block; }
    </style>
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

            ajax: "{{ route('pedidos.administrador.visualizacionParaDespachado') }}",
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


        function descargarPdfRutas() {
            let selectedPreventistas = [];
            document.querySelectorAll('input[name="preventistas[]"]:checked').forEach((checkbox) => {
                selectedPreventistas.push(checkbox.value);
            });

            if (selectedPreventistas.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor, selecciona al menos un personal de campo.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }

            // Convertimos el array en una cadena separada por comas o parámetros
            // Creamos la URL con los IDs seleccionados
            let ids = selectedPreventistas.join(',');
            let url = "{{ route('pedidos.administrador.visualizacionPdfDespachar.pedidosPendientes.porPreventista') }}?id_preventistas=" + ids;

            // Abrimos en pestaña nueva
            window.open(url, '_blank');
        }
    </script>
@stop