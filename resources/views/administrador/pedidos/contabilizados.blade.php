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
    <div class="container">
        <table class="table table-striped table-bordered" id="tablaPedidosContabilizados" >
            <thead>
                <tr>
                    <th>Nro. Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha pedido</th>
                    <th>Fecha entrega</th>
                    <th>Monto Contabilizado</th>
                    <th>Preventista</th>
                    <th>Ruta</th>
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
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
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
        $('#tablaPedidosContabilizados').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {url: '/i18n/es-ES.json'},
            ajax: "{{ route('administrador.pedidos.administrador.visualizacionContabilizados') }}",
            lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
            columns: [
                { data: 'numero_pedido', name: 'numero_pedido' },
                { data: 'cliente', name: 'cliente' },
                { data: 'fecha_pedido', name: 'fecha_entrega' },
                { data: 'fecha_entrega', name: 'fecha_entrega' },
                { data: 'monto_contabilizado', name: 'monto_contabilizado' },
                { data: 'preventista', name: 'preventista' },
                { data: 'ruta', name: 'ruta' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']],
            dom: `
                    <'row mb-2'<'col-12 d-flex justify-content-between align-items-center'Bf>>
                    <'row'<'col-12'tr>>
                    <'row'<'col-12 d-flex justify-content-between align-items-center'ip>>
                `,
            buttons: [
                {
                    extend: 'pageLength',
                    className: 'btn btn-secondary',
                    text: '<i class="fas fa-list-ol"></i> Mostrar filas',
                    titleAttr: 'Mostrar filas'
                },
                {
                    extend: 'colvis',
                    className: 'btn btn-secondary',
                    text: '<i class="fas fa-columns"></i> Columnas',
                    titleAttr: 'Columnas'
                },
                /*html*/
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-danger',
                    text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                    titleAttr: 'Exportar a PDF',
                },
                /*imprimir*/
                {
                    extend: 'print',
                    className: 'btn btn-info',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    titleAttr: 'Imprimir',
                }
            ],
        });

        function recontabilizar_pedido(button) {
            let numero_pedido = $(button).attr('data-id-pedido');
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Esta acción recontabilizará el pedido Nro. ${numero_pedido}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, recontabilizar',
                cancelButtonText: 'Cancelar',
                input: 'date',
                inputLabel: 'Selecciona la nueva fecha de contabilización',
                preConfirm: (fecha) => {
                    if (!fecha) {
                        Swal.showValidationMessage('Por favor, selecciona una fecha válida.');
                    }
                    return fecha;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let nueva_fecha = result.value;
                    $.ajax({
                        url: '{{ route("administrador.pedidos.administrador.recontabilizarPedido", ":numero_pedido") }}'.replace(':numero_pedido', numero_pedido),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT',
                            fecha_contabilizacion: nueva_fecha
                        },
                        success: function(response) {
                            Swal.fire(
                                'Recontabilizado',
                                `El pedido Nro. ${numero_pedido} ha sido recontabilizado.`,
                                'success'
                            );
                            $('#tablaPedidosContabilizados').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                'Hubo un problema al recontabilizar el pedido.',
                                'error'
                            );
                        }
                    });
                }
            })
            .catch(() => {
                Swal.fire(
                    'Cancelado',
                    'La acción ha sido cancelada.',
                    'info'
                );
            });
        }
        
    </script>
@stop