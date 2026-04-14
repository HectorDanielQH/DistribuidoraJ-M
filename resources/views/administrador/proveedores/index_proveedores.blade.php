@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
    <div class="provider-header">
        <div>
            <span>Administrador</span>
            <h1>Proveedores y marcas</h1>
            <p>Gestiona proveedores, marcas asociadas y movimientos de marca entre proveedores.</p>
        </div>
        <button class="btn btn-success provider-main-btn" id="boton-agregar" data-toggle="modal" data-target="#modalAgregarProveedor">
            <i class="fas fa-plus"></i> Nuevo proveedor
        </button>
    </div>
    <div class="container py-4 provider-legacy-header" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de proveedores
            </span>
        </div>
    </div>
@stop

@section('content')

    <div class="provider-page">
        <section class="provider-summary" aria-label="Resumen de proveedores">
            <article class="provider-summary-card">
                <span>Proveedores</span>
                <strong>{{ $resumenProveedores['proveedores'] ?? 0 }}</strong>
            </article>
            <article class="provider-summary-card">
                <span>Marcas registradas</span>
                <strong>{{ $resumenProveedores['marcas'] ?? 0 }}</strong>
            </article>
            <article class="provider-summary-card provider-warning-card">
                <span>Sin marcas</span>
                <strong>{{ $resumenProveedores['sin_marcas'] ?? 0 }}</strong>
            </article>
        </section>

        <section class="provider-search-box">
            <div>
                <strong>Busqueda tecnica</strong>
                <span>Filtra por nombre del proveedor. Las marcas se administran desde cada fila.</span>
            </div>
            <div class="provider-search-row">
                <label for="nombreProveedor">Proveedor</label>
                <input type="text" class="form-control" name="nombre" id="nombreProveedor" placeholder="Ej: PIL ANDINA">
                <button type="button" class="btn btn-outline-secondary provider-main-btn" id="limpiarboton">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </section>
    </div>

    <div class="d-flex flex-column justify-content-center align-items-center provider-legacy-search">
        <div class="card shadow-sm border-0 " style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
                
                <button class="btn" id="boton-agregar-legacy" data-toggle="modal" data-target="#modalAgregarProveedor" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar al proveedor por su nombre
                </p>
                <div class="row g-3 w-100 d-flex align-items-center">
                    <div class="col-md-12">
                        <label for="nombre" class="form-label text-muted">Nombre completo</label>
                        <input type="text" class="form-control shadow-sm border-0" name="nombre" id="nombreProveedorLegacy" placeholder="Ej: Proveedor"  style="border-radius: 8px;">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--AGREGAR PROVEEDOR-->
    <x-adminlte-modal id="modalAgregarProveedor" size="lg" theme="dark" icon="fas fa-industry" title="Registrar proveedor y marcas">
        <div class="modal-body px-4">
            <form id="registro-proveedores" action="{{ route('administrador.proveedores.store') }}">
                @csrf
                <div class="provider-modal-flow">
                    <section class="provider-modal-step">
                        <div class="provider-step-number">1</div>
                        <div>
                            <strong>Datos del proveedor</strong>
                            <span>Registra la empresa o distribuidor que abastece productos.</span>
                        </div>
                    </section>

                    <div class="provider-modal-field">
                        <x-adminlte-input name="nombreproveedor" label="Nombre del proveedor" placeholder="Ej: PIL ANDINA" label-class="text-dark">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-users text-muted"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>

                    <section class="provider-modal-step">
                        <div class="provider-step-number">2</div>
                        <div>
                            <strong>Marcas que distribuye</strong>
                            <span>Escribe una marca y presiona Enter. Puedes agregar varias antes de guardar.</span>
                        </div>
                    </section>

                    <div class="provider-modal-field">
                        <label for="opciones" class="text-dark">Marcas</label>
                        <select class="w-100" id="opciones" multiple="multiple" name="opciones[]">
                        </select>
                        <small class="provider-modal-help">Ejemplo: NESTLE, PIL, FINO. Cada marca quedara asociada al proveedor.</small>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="provider-modal-footer">
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2 provider-modal-action" />
                <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Guardar proveedor" class="rounded-3 px-4 py-2 provider-modal-action" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    <!--EDITAR USUARIO-->
    <x-adminlte-modal id="modalEditarProovedor" size="lg" theme="dark" icon="fas fa-edit" title="Editar proveedor">
        <div class="modal-body px-4">
            <form id="registro-proveedores-editar" method="POST">
                @method('PUT')
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <x-adminlte-input name="nombreproveedor" id="nombre-proveedor-editar" label="Nombre del proveedor" placeholder="Ej: PIL ANDINA" label-class="text-dark">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-users text-muted"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="provider-modal-footer">
                <x-adminlte-button theme="danger" id="botonenviar-cerrar-editar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2 provider-modal-action" />
                <x-adminlte-button type="submit" id="botonenviar-editar" theme="success" icon="fas fa-check" label="Guardar cambios" class="rounded-3 px-4 py-2 provider-modal-action" />
            </div>
        </x-slot>
    </x-adminlte-modal>
    

    <div class="container table-responsive my-4 provider-table-box">
        <table class="table table-bordered" id="tabla-proveedores">
            <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Proveedor</th>
                    <th scope="col">Marcas</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">
                        <div class="alert alert-warning mb-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No se encontraron resultados para la búsqueda, quizas con otra coincidencia.
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet">

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

        :root {
            --provider-bg: #eef3f1;
            --provider-surface: #ffffff;
            --provider-line: #d7e4df;
            --provider-text: #17211d;
            --provider-muted: #64748b;
            --provider-green: #15803d;
            --provider-green-soft: #e7f6ec;
            --provider-red: #b91c1c;
            --provider-red-soft: #fee2e2;
        }

        .content-wrapper {
            background: var(--provider-bg);
        }

        .provider-legacy-header,
        .provider-legacy-search {
            display: none !important;
        }

        .provider-header,
        .provider-search-box,
        .provider-summary-card,
        .provider-table-box {
            background: var(--provider-surface);
            border: 1px solid var(--provider-line);
            border-radius: 8px;
        }

        .provider-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
        }

        .provider-header span {
            color: var(--provider-green);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .provider-header h1 {
            margin: 0;
            color: var(--provider-text);
            font-size: 1.65rem;
            font-weight: 900;
        }

        .provider-header p,
        .provider-search-box span,
        .provider-summary-card span,
        .provider-name-block small {
            margin: 4px 0 0;
            color: var(--provider-muted);
            font-weight: 700;
        }

        .provider-main-btn,
        .provider-mini-btn,
        .provider-actions .btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
            white-space: normal;
        }

        .provider-page {
            display: grid;
            gap: 12px;
            margin-bottom: 12px;
        }

        .provider-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .provider-summary-card {
            padding: 14px;
        }

        .provider-summary-card strong {
            display: block;
            margin-top: 4px;
            color: var(--provider-text);
            font-size: 1.75rem;
            font-weight: 900;
        }

        .provider-warning-card {
            background: var(--provider-red-soft);
            border-color: #fecaca;
        }

        .provider-warning-card span,
        .provider-warning-card strong {
            color: var(--provider-red);
        }

        .provider-search-box {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .provider-search-box strong,
        .provider-search-row label {
            color: var(--provider-text);
            font-weight: 900;
        }

        .provider-search-row {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr) 150px;
            gap: 10px;
            align-items: center;
        }

        .provider-search-row .form-control {
            min-height: 42px;
            border-radius: 8px;
        }

        .provider-table-box {
            padding: 14px;
        }

        #tabla-proveedores {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        #tabla-proveedores thead th {
            border: 0;
            color: var(--provider-muted);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #tabla-proveedores tbody td {
            border-top: 1px solid var(--provider-line);
            border-bottom: 1px solid var(--provider-line);
            vertical-align: middle;
            font-weight: 800;
        }

        #tabla-proveedores tbody td:first-child {
            border-left: 1px solid var(--provider-line);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #tabla-proveedores tbody td:last-child {
            border-right: 1px solid var(--provider-line);
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .provider-name-block {
            display: grid;
            gap: 2px;
        }

        .provider-name-block strong {
            color: var(--provider-text);
            font-weight: 900;
        }

        .provider-brand-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .provider-brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px;
            background: var(--provider-green-soft);
            border: 1px solid #b7e4c7;
            border-radius: 8px;
            color: var(--provider-text);
            font-weight: 900;
        }

        .provider-brand-empty {
            padding: 10px;
            background: #f8fafc;
            border: 1px dashed var(--provider-line);
            border-radius: 8px;
            color: var(--provider-muted);
            font-weight: 900;
        }

        .provider-actions {
            display: grid;
            gap: 8px;
        }

        #modalAgregarProveedor .modal-body,
        #modalEditarProovedor .modal-body {
            background: #f8fafc;
        }

        .provider-modal-flow {
            display: grid;
            gap: 12px;
        }

        .provider-modal-step,
        .provider-modal-field {
            background: #ffffff;
            border: 1px solid var(--provider-line);
            border-radius: 8px;
            padding: 12px;
        }

        .provider-modal-step {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 10px;
            align-items: center;
        }

        .provider-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--provider-green-soft);
            color: var(--provider-green);
            font-weight: 900;
        }

        .provider-modal-step strong,
        .provider-modal-field label {
            display: block;
            margin: 0;
            color: var(--provider-text);
            font-weight: 900;
        }

        .provider-modal-step span,
        .provider-modal-help {
            display: block;
            margin-top: 4px;
            color: var(--provider-muted);
            font-weight: 700;
        }

        .provider-modal-field .form-control,
        .provider-modal-field .select2-selection {
            min-height: 42px;
            border-radius: 8px !important;
        }

        .provider-modal-field .select2-container--classic .select2-selection--multiple {
            border-color: var(--provider-line);
            padding: 4px;
        }

        .provider-modal-field .select2-selection__choice {
            background: var(--provider-green-soft) !important;
            border: 1px solid #b7e4c7 !important;
            border-radius: 8px !important;
            color: var(--provider-text) !important;
            font-weight: 900;
            padding: 4px 8px !important;
        }

        .provider-modal-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .provider-modal-action {
            width: 100%;
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        @media (max-width: 767.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .provider-header {
                flex-direction: column;
            }

            .provider-main-btn {
                width: 100%;
            }

            .provider-summary,
            .provider-search-row {
                grid-template-columns: 1fr;
            }

            #tabla-proveedores,
            #tabla-proveedores tbody,
            #tabla-proveedores tr,
            #tabla-proveedores td {
                display: block;
                width: 100%;
            }

            #tabla-proveedores thead {
                display: none;
            }

            #tabla-proveedores tbody tr {
                margin-bottom: 12px;
                padding: 12px;
                background: var(--provider-surface);
                border: 1px solid var(--provider-line);
                border-radius: 8px;
            }

            #tabla-proveedores tbody td {
                display: grid;
                grid-template-columns: 112px 1fr;
                gap: 8px;
                align-items: start;
                border: 0;
                padding: 8px 0;
            }

            #tabla-proveedores tbody td::before {
                content: attr(data-label);
                color: var(--provider-muted);
                font-size: .75rem;
                font-weight: 900;
                text-transform: uppercase;
            }

            #tabla-proveedores tbody td:first-child,
            #tabla-proveedores tbody td:last-child {
                border: 0;
            }

            #tabla-proveedores tbody td:nth-child(3),
            #tabla-proveedores tbody td:nth-child(4) {
                grid-template-columns: 1fr;
            }

            .provider-brand-chip,
            .provider-actions .btn,
            .provider-mini-btn {
                width: 100%;
                justify-content: center;
            }

            .provider-modal-footer {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-proveedores').DataTable({
                language: {
                    url: '/i18n/es-ES.json',
                    emptyTable: 'No hay proveedores registrados.',
                    processing: 'Cargando proveedores...',
                },
                "processing":true,
                "serverSide":true,
                "searching": false,
                "responsive": false,

                "ajax": {
                    "url": "{{ route('administrador.proveedores.index') }}",
                    "type": "GET",
                    "data": function (d) {
                        d.proveedor = $('#nombreProveedor').val();
                    }
                },
                columns:[
                    { data: 'id',width: '5%' },
                    { data: 'nombre_proveedor',width: '20%'},
                    { data: 'producto_marcas',width: '65%', orderable: false, searchable: false},
                    { data: 'acciones',width: '10%', orderable: false, searchable: false }
                ],
                createdRow: function(row) {
                    const labels = ['ID', 'Proveedor', 'Marcas', 'Acciones'];
                    $('td', row).each(function(index) {
                        $(this).attr('data-label', labels[index] || '');
                    });
                },

            });

            $('#nombreProveedor').on('keyup', function() {
                $('#tabla-proveedores').DataTable().ajax.reload();
            });

            $('#opciones').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%',
                placeholder: 'Escribe una marca y presiona Enter',
                theme: "classic"
            });

            $('#opciones-editar').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%',
                placeholder: 'Escribe una marca y presiona Enter',
                theme: "classic"
            });
        });

        $('#botonenviar').click(function(){
            $('#registro-proveedores').submit();
        });

        $('#registro-proveedores').submit(function(e) {
            Swal.fire({
                title: 'Agregando Proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: "{{ route('administrador.proveedores.store') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Proveedor agregado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar').click();
                    $('#tabla-proveedores').DataTable().ajax.reload();
                    $('#registro-proveedores')[0].reset();
                    $('#opciones').val(null).trigger('change');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al agregar proveedor',
                        text: xhr.responseJSON.message || 'Por favor, intente nuevamente.',
                    });
                }
            });
        });


        function funcionEliminar(e){
            let id_usuario=e.getAttribute('id-usuario');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar este proveedor una vez eliminado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.proveedores.destroy', ':id') }}`.replace(':id', id_usuario),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proveedor eliminado con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar el proveedor',
                                text: "Tienes registros asociados a este proveedor, no puedes eliminarlo.",
                            });
                        }
                    });
                }
            });
        }

        let idProveedorEditar = null;

        function funcionEditar(e){
            Swal.fire({
                title: 'Cargando datos del proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let id_usuario=e.getAttribute('id-usuario-editar');
            idProveedorEditar = id_usuario;
            $.ajax({
                url: `{{ route('administrador.proveedores.show', ':id') }}`.replace(':id', id_usuario),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.close();
                    $('#nombre-proveedor-editar').val(response.usuario.nombre_proveedor);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el proveedor',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        }

        $('#botonenviar-editar').click(function(e){
            $('#registro-proveedores-editar').submit();
        });
        

        $('#registro-proveedores-editar').submit(function(event) {
            event.preventDefault();
            let formData = $(this).serialize();
            Swal.fire({
                title: 'Editando Proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: `{{ route('administrador.proveedores.update', ':id') }}`.replace(':id', idProveedorEditar),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Proveedor editado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar-editar').click();
                    $('#tabla-proveedores').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Ocurrió un error inesperado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el proveedor',
                        text: errorMessage,
                    });
                }
            });
        });

            $('#limpiarboton').click(function(){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminará la búsqueda actual.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, limpiar búsqueda',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Limpiando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    })
                    $('#nombreProveedor').val('');
                    $('#tabla-proveedores').DataTable().ajax.reload();
                    Swal.close();
                }
            });
        });

        function eliminarMarcas(e){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar esta marca una vez eliminada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.marcas.destroy', ':id') }}`.replace(':id', e),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca eliminada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar la marca',
                                text: "Tienes registros asociados a esta marca, no puedes eliminarla.",
                            });
                        }
                    });
                }
            });
        }

        function anadirMarca(idProveedor){
            Swal.fire({
                title: 'Agregar Marca',
                input: 'text',
                inputPlaceholder: 'Ingrese la descripción de la marca',
                showCancelButton: true,
                confirmButtonText: 'Agregar',
                cancelButtonText: 'Cancelar',
                preConfirm: (marca) => {
                    if (!marca) {
                        Swal.showValidationMessage('Por favor, ingrese una marca válida.');
                        return false;
                    }
                    return marca;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Agregando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    let nuevaMarca = result.value;
                    $.ajax({
                        url: "{{ route('administrador.marcas.store') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { descripcion: nuevaMarca, proveedor_id: idProveedor },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca agregada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al agregar la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }


        function editarFuncion(e){
            let idMarca = e.getAttribute('id-marca');
            let nombreMarca = e.getAttribute('nombre-marca');
            Swal.fire({
                title: 'Editar Marca',
                input: 'text',
                inputValue: nombreMarca,
                inputPlaceholder: 'Ingrese la nueva descripción de la marca',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: (nuevaDescripcion) => {
                    if (!nuevaDescripcion) {
                        Swal.showValidationMessage('Por favor, ingrese una descripción válida.');
                        return false;
                    }
                    return nuevaDescripcion;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let descripcionActualizada = result.value;
                    Swal.fire({
                        title: 'Actualizando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.marcas.update', ':id') }}`.replace(':id', idMarca),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { descripcion: descripcionActualizada, _method: 'PUT' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca actualizada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }

        function moverMarcas(idMarca) {
            Swal.fire({
                title: 'Mover Marca',
                text: "Selecciona el nuevo proveedor para esta marca.",
                input: 'select',
                inputOptions: {
                    @foreach ($proveedores as $proveedor)
                        '{{ $proveedor->id  }}': '{{ $proveedor->nombre_proveedor }}',
                    @endforeach
                },
                showCancelButton: true,
                confirmButtonText: 'Mover',
                cancelButtonText: 'Cancelar',
                preConfirm: (nuevoProveedorId) => {
                    if (!nuevoProveedorId) {
                        Swal.showValidationMessage('Por favor, selecciona un proveedor válido.');
                        return false;
                    }
                    return nuevoProveedorId;
                }
            }).then((result) => {
                Swal.fire({
                    title: 'Moviendo...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                if (result.isConfirmed) {
                    let nuevoProveedorId = result.value;
                    $.ajax({
                        url: `{{ route('administrador.marca.mover', ':id') }}`.replace(':id', idMarca),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { proveedor_id: nuevoProveedorId },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca movida con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().draw();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al mover la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }
    </script>
@stop
