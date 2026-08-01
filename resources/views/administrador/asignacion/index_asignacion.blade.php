@extends('adminlte::page')

@section('title', 'Asignacion de clientes')

@section('content_header')
    <div class="assignment-header">
        <div class="assignment-header-copy">
            <span class="assignment-kicker">Gestion comercial</span>
            <h1>Panel de asignacion de clientes y rutas</h1>
            <p>Administra preventistas activos, distribuye rutas disponibles y revisa rapidamente el estado de cobertura comercial desde un solo lugar.</p>
        </div>
        <div class="assignment-header-mark">
            <div class="assignment-header-brand">
                <i class="fas fa-route"></i>
                <strong>Distribuidora H&amp;J</strong>
            </div>
            <small>Panel operativo de asignaciones</small>
        </div>
    </div>

    <div class="assignment-summary-grid">
        <article class="assignment-summary-card">
            <span>Preventistas activos</span>
            <strong>{{ $vendedores->count() }}</strong>
            <small>Usuarios listos para recibir rutas y clientes.</small>
        </article>
        <article class="assignment-summary-card">
            <span>Rutas registradas</span>
            <strong>{{ $rutas->count() }}</strong>
            <small>Rutas configuradas para distribucion comercial.</small>
        </article>
        <article class="assignment-summary-card">
            <span>Clientes no atendidos</span>
            <strong>{{ $no_atendidos->count() }}</strong>
            <small>Observaciones que requieren seguimiento o subsanacion.</small>
        </article>
        <article class="assignment-summary-card assignment-summary-card-accent">
            <span>Accion principal</span>
            <strong>Asignar y monitorear</strong>
            <small>Visualiza clientes, valida rutas y asigna de forma masiva o puntual.</small>
        </article>
    </div>

    <div class="assignment-intro">
        <div>
            <h2>Asignacion de rutas a preventistas</h2>
            <p>Busca un preventista, revisa su carga actual y abre las acciones operativas que necesitas desde la tabla principal.</p>
        </div>
        <div class="assignment-intro-tags">
            <span><i class="fas fa-users"></i> Clientes asignados</span>
            <span><i class="fas fa-route"></i> Rutas activas</span>
            <span><i class="fas fa-user-plus"></i> Asignacion puntual</span>
        </div>
    </div>

    @if($no_atendidos->count() > 0)
        <div class="assignment-alert">
            <div>
                <strong><i class="fas fa-exclamation-triangle me-2"></i>Hay clientes que no han sido atendidos.</strong>
                <p class="mb-0">Revisa el detalle, descarga el reporte o marca observaciones como subsanadas cuando corresponda.</p>
            </div>
            <button class="btn btn-dark assignment-alert-btn" onclick="abrirModalNoAtendidos()">Ver detalle</button>
        </div>
    @endif
@stop

@section('content')
    <div class="modal fade" id="asignarCliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="asignarClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="asignarClienteLabel">
                        <i class="fas fa-random me-2"></i>
                        Asignar rutas al preventista
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-3 text-black" id="clienteSelect" name="clientes" multiple="multiple" style="width: 100%;" aria-label="Seleccionar rutas">
                        @foreach($rutas as $ruta)
                            <option value="{{ $ruta->id }}" class="text-black">{{ $ruta->nombre_ruta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardarclientesasignados">Asignar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarClienteUnitario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="asignarClienteUnitarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="asignarClienteUnitarioLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Asignar cliente al preventista
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" id="id_vendedor_cliente_unitario" value="">
                <div class="modal-body">
                    <select class="form-select mb-3 text-black" id="clienteunitario" name="clientesunitarios" multiple="multiple" style="width: 100%;" aria-label="Seleccionar cliente">
                    </select>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente-unitario">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardarclientesasignadosunitarios">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarPorClientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="asignarPorClientesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="asignarPorClientesLabel">
                        <i class="fas fa-users me-2"></i>
                        Asignar clientes al preventista
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-3 text-black" id="porclientesSelect" name="clientes" multiple="multiple" style="width: 100%;" aria-label="Seleccionar clientes">
                    </select>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="cancelar-asignacion-por-clientes">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardarclientesasignadosporclientes">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visualizarClientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="visualizarClientesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="visualizarClientesLabel">
                        <i class="fas fa-users me-2"></i>
                        Ver clientes asignados
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="clientesAsignadosTable">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre completo</th>
                                    <th scope="col">Celular</th>
                                    <th scope="col">Direccion</th>
                                    <th scope="col">Ruta</th>
                                </tr>
                            </thead>
                            <tbody id="clientesAsignadosBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="cancelar-visualizar-clientes">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visualizarRutas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="visualizarRutasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="visualizarRutasLabel">
                        <i class="fas fa-route me-2"></i>
                        Ver rutas asignadas
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="rutasAsignadasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre ruta</th>
                                    <th scope="col">Nro. clientes</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal" id="cancelar-asignacion-rutas">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="assignment-table-shell">
        <div class="assignment-table-topbar">
            <div>
                <span class="assignment-section-kicker">Listado operativo</span>
                <h3>Preventistas y estado de asignaciones</h3>
            </div>
            <div class="assignment-topbar-note">
                <i class="fas fa-info-circle"></i>
                <span>Busca por nombre, CI o celular para ubicar rapidamente al preventista.</span>
            </div>
        </div>

        <div class="table-responsive assignment-table-wrap">
            <table id="tabla-asignaciones" class="table table-bordered align-middle text-center mb-0" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">C.I.</th>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Celular</th>
                        <th scope="col">Asignaciones</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet" integrity="sha384-d76uxpdVr9QyCSR9vVSYdOAZeRzNUN8A4JVqUHBVXyGxZ+oOfrZVHC/1Y58mhyNg" crossorigin="anonymous">

    <style>
        .content-wrapper {
            background:
                radial-gradient(circle at top right, rgba(21, 128, 61, 0.08), transparent 22%),
                linear-gradient(180deg, #eef5f1 0%, #f7faf8 100%);
        }
        .assignment-header,
        .assignment-summary-card,
        .assignment-intro,
        .assignment-alert,
        .assignment-table-shell {
            background: #fff;
            border: 1px solid #d7e4df;
            border-radius: 12px;
        }
        .assignment-header {
            align-items: stretch;
            background: linear-gradient(135deg, #13332d 0%, #1f5c4a 42%, #2f8d69 100%);
            border: 0;
            box-shadow: 0 20px 50px rgba(19, 51, 45, 0.2);
            color: #fff;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin-bottom: 18px;
            overflow: hidden;
            padding: 24px 26px;
            position: relative;
        }
        .assignment-header::after {
            background: linear-gradient(135deg, rgba(255,255,255,.14), rgba(255,255,255,0));
            border-radius: 50%;
            content: "";
            height: 220px;
            position: absolute;
            right: -60px;
            top: -80px;
            width: 220px;
        }
        .assignment-header-copy,
        .assignment-header-mark {
            position: relative;
            z-index: 1;
        }
        .assignment-kicker,
        .assignment-section-kicker {
            color: #d1fae5;
            display: inline-block;
            font-size: .77rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .assignment-header h1 {
            font-size: 2rem;
            font-weight: 900;
            margin: 6px 0 10px;
        }
        .assignment-header p {
            color: rgba(255,255,255,.88);
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
            max-width: 720px;
        }
        .assignment-header-mark {
            align-items: flex-end;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 220px;
        }
        .assignment-header-brand {
            align-items: center;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            display: inline-flex;
            gap: 10px;
            padding: 10px 14px;
        }
        .assignment-header-brand i {
            color: #fef08a;
        }
        .assignment-header-mark small {
            color: rgba(255,255,255,.75);
            font-size: .88rem;
            font-weight: 700;
        }
        .assignment-summary-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 16px;
        }
        .assignment-summary-card {
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            padding: 18px;
        }
        .assignment-summary-card span {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .assignment-summary-card strong {
            color: #0f172a;
            display: block;
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1.1;
            margin-top: 8px;
        }
        .assignment-summary-card small {
            color: #64748b;
            display: block;
            font-size: .9rem;
            line-height: 1.45;
            margin-top: 8px;
        }
        .assignment-summary-card-accent {
            background: linear-gradient(135deg, #f3faf6 0%, #edf8ff 100%);
            border-color: #c8e7d7;
        }
        .assignment-intro,
        .assignment-alert,
        .assignment-table-shell {
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
        }
        .assignment-intro {
            align-items: center;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 18px 20px;
        }
        .assignment-intro h2 {
            color: #0f172a;
            font-size: 1.35rem;
            font-weight: 900;
            margin: 0 0 6px;
        }
        .assignment-intro p {
            color: #64748b;
            margin: 0;
        }
        .assignment-intro-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }
        .assignment-intro-tags span {
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #d7e4df;
            border-radius: 999px;
            color: #334155;
            display: inline-flex;
            font-size: .88rem;
            font-weight: 800;
            gap: 8px;
            padding: 9px 12px;
        }
        .assignment-intro-tags i {
            color: #0f766e;
        }
        .assignment-alert {
            align-items: center;
            background: linear-gradient(135deg, #fff9db 0%, #fff4bf 100%);
            border-color: #f6d365;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 16px 18px;
        }
        .assignment-alert strong {
            color: #854d0e;
            display: block;
            margin-bottom: 6px;
        }
        .assignment-alert p {
            color: #7c5a10;
        }
        .assignment-alert-btn {
            border-radius: 10px;
            font-weight: 800;
            min-width: 140px;
        }
        .assignment-table-shell {
            overflow: hidden;
            padding: 14px;
        }
        .assignment-table-topbar {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .assignment-table-topbar h3 {
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 900;
            margin: 4px 0 0;
        }
        .assignment-topbar-note {
            align-items: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            color: #475569;
            display: inline-flex;
            gap: 8px;
            padding: 10px 12px;
        }
        .assignment-topbar-note i {
            color: #0f766e;
        }
        .assignment-table-wrap {
            border: 1px solid #dbe4ec;
            border-radius: 12px;
            overflow: auto;
        }
        #tabla-asignaciones thead th,
        #clientesAsignadosTable thead th,
        #rutasAsignadasTable thead th {
            background: #0f172a;
            border-color: #111827;
            color: #fff;
            font-size: .8rem;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        #tabla-asignaciones tbody td,
        #clientesAsignadosTable tbody td,
        #rutasAsignadasTable tbody td {
            vertical-align: middle;
        }
        #tabla-asignaciones tbody tr:nth-child(even),
        #clientesAsignadosTable tbody tr:nth-child(even),
        #rutasAsignadasTable tbody tr:nth-child(even) {
            background: #fbfefd;
        }
        #tabla-asignaciones tbody tr:hover,
        #clientesAsignadosTable tbody tr:hover,
        #rutasAsignadasTable tbody tr:hover {
            background: #f1f9f5;
        }
        .assignment-badge {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: .82rem;
            font-weight: 800;
            gap: 8px;
            padding: 8px 12px;
        }
        .assignment-badge-active {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .assignment-badge-empty {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
        }
        .assignment-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }
        .assignment-action-btn {
            border-radius: 10px;
            min-width: 38px;
        }
        .modal-content {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
        }
        .modal-header {
            border-bottom: 1px solid #e5ece9;
            padding: 16px 18px;
        }
        .modal-title {
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 900;
        }
        .modal-body {
            padding: 18px;
        }
        .modal-footer {
            border-top: 1px solid #e5ece9;
            padding: 14px 18px 18px;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--multiple,
        .select2-container .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            min-height: 44px;
        }
        .select2-container--classic .select2-selection--multiple .select2-selection__choice {
            background: #0f766e !important;
            border: 0 !important;
            color: #fff !important;
            padding: 4px 8px !important;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            min-height: 40px;
            padding: 6px 10px;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus,
        input.form-control:focus,
        select.form-control:focus,
        .form-select:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.15);
            outline: 0;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
        }
        @media (max-width: 991.98px) {
            .assignment-header,
            .assignment-intro,
            .assignment-table-topbar,
            .assignment-alert {
                align-items: flex-start;
                flex-direction: column;
            }
            .assignment-header-mark {
                align-items: flex-start;
                min-width: 0;
            }
            .assignment-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .assignment-intro-tags {
                justify-content: flex-start;
            }
        }
        @media (max-width: 767.98px) {
            .assignment-summary-grid {
                grid-template-columns: 1fr;
            }
            .assignment-header {
                padding: 20px 18px;
            }
            .assignment-header h1 {
                font-size: 1.55rem;
            }
            .assignment-table-shell,
            .assignment-intro,
            .assignment-alert {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-asignaciones').DataTable({
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar preventista',
                    searchPlaceholder: 'Nombre, CI o celular'
                },
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6'p>>",
                ajax: {
                    url: "{{ route('administrador.asignacionclientes.index') }}",
                    type: "GET",
                },
                columns: [
                    { data: 'id', width: '5%'},
                    { data: 'cedulaidentidad', width: '15%'},
                    { data: 'nombre_completo', width: '35%'},
                    { data: 'celular', width: '15%'},
                    { data: 'asignacion', width: '15%'},
                    { data: 'action', width: '15%', orderable: false, searchable: false }
                ],
            });
        });
    </script>

    <script>
        $('#clienteSelect').select2({
            placeholder: "Seleccione una ruta",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        $('#clienteunitario').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        $('#vendedorSelectControl').select2({
            placeholder: "Seleccione un vendedor",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        let id_vendedor = null;

        function valordeusuariovendedor(e){
            Swal.fire({
                title: 'Cargando rutas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            id_vendedor = $(e).attr('data-id');
            $('#clienteSelect').val(null).trigger('change');
            $.ajax({
                url: "{{ route('asignacionclientes.getRutasNoAsignados') }}",
                type: 'GET',
                data: {
                    id_vendedor: id_vendedor
                },
                success: function(data) {
                    Swal.close();
                    $('#clienteSelect').empty();
                    data.forEach(cliente => {
                        $('#clienteSelect').append(new Option(cliente.nombre_ruta, cliente.id, false, false));
                    });
                    $('#clienteSelect').trigger('change');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un error al cargar las rutas.',
                    });
                }
            });
        }

        $('#guardarclientesasignados').click(function(){
            let selectclientes = $('#clienteSelect').val();

            if (selectclientes.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar al menos una ruta.',
                });
                return;
            }
            Swal.fire({
                title: 'Asignando rutas con clientes...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.asignacionclientes.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_vendedor: id_vendedor,
                    rutas: selectclientes
                },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Exito',
                        text: 'Clientes asignados correctamente.',
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#asignarCliente').modal('hide');
                        $('#clienteSelect').val(null).trigger('change');
                        $('#tabla-asignaciones').DataTable().ajax.reload();
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un error al asignar los clientes.',
                    });
                }
            });
        });

        $('#cancelar-asignacion-cliente').click(function() {
            $('#clienteSelect').val(null).trigger('change');
        });

        function clientesAsignados(e) {
            let id_vendedor = $(e).attr('data-id');
            Swal.fire({
                title: 'Cargando clientes asignados...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $('#clientesAsignadosTable').DataTable().destroy();
            $('#clientesAsignadosTable').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('administrador.asignacionclientes.getClientesAsignados', ':id') }}".replace(':id', id_vendedor),
                    type: "GET",
                },
                columns: [
                    { data: 'id', width: '5%' },
                    { data: 'nombre_completo', width: '35%' },
                    { data: 'celular', width: '15%' },
                    { data: 'calle_avenida', width: '30%' },
                    { data: 'nombre_ruta', width: '15%' }
                ],
                destroy: true,
                initComplete: function() {
                    Swal.close();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un error al cargar los clientes asignados.',
                    });
                }
            });
        }

        function eliminarRutaAsignada(e) {
            let rutaId = $(e).attr('data-id');
            let idVendedor = $(e).attr('data-id-vendedor');
            Swal.fire({
                title: 'Estas seguro?',
                text: 'Esta accion no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('administrador.asignacionrutas.destroyasignacion', ':id') }}`.replace(':id', rutaId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id_vendedor: idVendedor
                        },
                        success: function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Exito',
                                text: 'Cliente eliminado correctamente.',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 2000,
                            }).then(() => {
                                $('#tabla-asignaciones').DataTable().ajax.reload();
                                $('#rutasAsignadasTable').DataTable().ajax.reload();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrio un error al eliminar el cliente.',
                            });
                        }
                    });
                }
            });
        }

        function rutasAsignadas(e) {
            let id_vendedor = $(e).attr('data-id');
            Swal.fire({
                title: 'Cargando rutas asignadas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $('#rutasAsignadasTable').DataTable().destroy();
            $('#rutasAsignadasTable').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('administrador.asignacionclientes.getRutasAsignadas', ':id') }}".replace(':id', id_vendedor),
                    type: "GET",
                },
                columns: [
                    { data: 'id', width: '5%' },
                    { data: 'nombre_ruta', width: '75%' },
                    { data: 'numero_clientes', width: '10%' },
                    { data: 'action', width: '10%', orderable: false, searchable: false }
                ],
                initComplete: function() {
                    Swal.close();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un error al cargar las rutas asignadas.',
                    });
                }
            });
        }

        $('#porclientesSelect').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        $('#clienteunitario').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('administrador.clientes.buscar') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(cliente => ({
                            id: cliente.id,
                            text: cliente.nombres + ' ' + cliente.apellidos + ' - RUTA:' + cliente.ruta.nombre_ruta,
                        }))
                    };
                },
                cache: true
            }
        });

        function agregarClienteUnitario(e)
        {
            let id_vendedor = $(e).attr('data-id');
            $('#id_vendedor_cliente_unitario').val(id_vendedor);
        }

        $('#cancelar-asignacion-cliente-unitario').click(function() {
            $('#clienteunitario').val(null).trigger('change');
            $('#id_vendedor_cliente_unitario').val('');
        });

        $('#guardarclientesasignadosunitarios').click(function() {
            let selectclientes = $('#clienteunitario').val();
            let id_vendedor = $('#id_vendedor_cliente_unitario').val();

            if (selectclientes.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar al menos un cliente.',
                });
                return;
            }
            Swal.fire({
                title: 'Asignando cliente unitario...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.asignacionclientes.storeUnitario') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_vendedor: id_vendedor,
                    clientes: selectclientes
                },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Exito',
                        text: 'Cliente asignado correctamente.',
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#cancelar-asignacion-cliente-unitario').click();
                        $('#clienteunitario').val(null).trigger('change');
                        $('#tabla-asignaciones').DataTable().ajax.reload();
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrio un error al asignar el cliente.',
                    });
                }
            });
        });
    </script>

    <script>
        function abrirModalNoAtendidos() {
            Swal.fire({
                title: 'Clientes no atendidos',
                html: `
                    <p>Hay clientes que no han sido atendidos. Que deseas hacer?</p>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
                        <button id="btn-reporte" class="swal2-confirm swal2-styled" style="background-color:#3085d6;">Si, ver reporte</button>
                        <button id="btn-subsanadas" class="swal2-confirm swal2-styled" style="background-color:#28a745;">Subsanar observaciones</button>
                        <button id="btn-luego" class="swal2-cancel swal2-styled" style="background-color:#d33;">En otro momento</button>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didRender: () => {
                    document.getElementById('btn-reporte').addEventListener('click', () => {
                        Swal.close();
                        window.open("{{ route('administrador.noatendidos.pdf') }}", '_blank');
                    });

                    document.getElementById('btn-subsanadas').addEventListener('click', () => {
                        Swal.fire({
                            title: 'Observaciones subsanadas',
                            text: 'Por favor, asegurate de que las observaciones de los clientes no atendidos hayan sido subsanadas.',
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            cancelButtonText: 'Cancelar',
                            showCancelButton: true,
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Subsanando observaciones...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                                $.ajax({
                                    url: "{{ route('administrador.noatendidos.subsanadas') }}",
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function() {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Exito',
                                            text: 'Observaciones subsanadas correctamente.',
                                            showConfirmButton: false,
                                            timer: 2000,
                                        });
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Ocurrio un error al subsanar las observaciones.',
                                        });
                                    }
                                });
                            }
                        });
                    });

                    document.getElementById('btn-luego').addEventListener('click', () => {
                        Swal.fire({
                            title: 'Recordatorio',
                            text: 'Recuerda revisar los clientes no atendidos mas tarde.',
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            allowOutsideClick: false,
                        });
                    });
                }
            });
        }
    </script>

    @if($no_atendidos->count() > 0)
        <script>
            abrirModalNoAtendidos();
        </script>
    @endif
@stop
