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
        <div class="mt-4 text-center">
            <h3 class="text-lg text-white">
                <i class="fas fa-shopping-cart mr-2"></i> Ventas Realizadas
                <small class="text-white d-block mt-1">Seleccione un rango de fechas para ver las ventas realizadas.</small>
            </h3>
        </div>
    </div>
@stop

@section('content')
    {{-- DETALLE AGRUPADO POR USUARIO → PEDIDOS --}}
    <div class="container mt-4">
        <div class="table-responsive">
            <table id="tabla-ventas" class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha de Arqueo</th>
                        <th>Monto Contabilizado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
            </table>
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
        $(document).ready(function () {
            $('#tabla-ventas').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: { url: '/i18n/es-ES.json' },
                ajax: "{{ route('administrador.ventas.administrador.ventasPorPedido') }}",
                columns: [
                    { data: 'fecha_contabilizacion' },
                    { data: 'monto_contabilizado' },
                    { data: 'acciones', orderable: false, searchable: false },
                ],
            })
        });

        function abrirModalMoverFechaArqueo(e){
            let fecha_contabilizacion = $(e).attr('fecha-contabilizacion');
            Swal.fire({
                title: 'Mover Fecha de Arqueo',
                html: `
                    <p>Está a punto de mover la fecha de arqueo <strong>${fecha_contabilizacion}</strong>.</p>
                    <p>Seleccione la nueva fecha de arqueo:</p>
                    <input type="date" id="nueva-fecha-arqueo" class="form-control" />
                `,
                showCancelButton: true,
                confirmButtonText: 'Mover Fecha',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const nuevaFecha = Swal.getPopup().querySelector('#nueva-fecha-arqueo').value;
                    if (!nuevaFecha) {
                        Swal.showValidationMessage('Por favor, seleccione una nueva fecha de arqueo.');
                    }
                    return { nuevaFecha: nuevaFecha };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let nuevaFecha = result.value.nuevaFecha;
                    $.ajax({
                        url: "{{ route('administrador.ventas.administrador.moverFechaArqueo', ['fecha_arqueo' => ':fecha_contabilizacion']) }}".replace(':fecha_contabilizacion', fecha_contabilizacion),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT',
                            nueva_fecha: nuevaFecha,
                        },
                        success: function(response) {
                            Swal.fire('Éxito', 'La fecha de arqueo ha sido movida exitosamente.', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Hubo un problema al mover la fecha de arqueo. Inténtelo de nuevo.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@stop
