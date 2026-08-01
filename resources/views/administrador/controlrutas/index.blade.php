@extends('adminlte::page')

@section('title', 'Control de rutas')

@section('content_header')
    <div class="routes-header">
        <div>
            <span>Supervision operativa</span>
            <h1>Control de trabajo de preventistas</h1>
            <p>Detecta rapidamente quienes ya comenzaron a trabajar, quienes siguen sin iniciar y quienes ya completaron sus asignaciones del dia.</p>
        </div>
        <div class="routes-header-summary">
            <span>Clientes pendientes</span>
            <strong>{{ $resumen['clientes_pendientes'] }}</strong>
        </div>
    </div>

    <div class="routes-kpi-grid">
        <article class="routes-kpi-card">
            <span>Preventistas activos</span>
            <strong>{{ $resumen['preventistas'] }}</strong>
            <small>Personal disponible para la jornada actual.</small>
        </article>
        <article class="routes-kpi-card routes-kpi-card-working">
            <span>Trabajando</span>
            <strong>{{ $resumen['trabajando'] }}</strong>
            <small>Ya registraron movimiento en sus clientes.</small>
        </article>
        <article class="routes-kpi-card routes-kpi-card-idle">
            <span>Sin iniciar</span>
            <strong>{{ $resumen['sin_iniciar'] }}</strong>
            <small>Tienen asignaciones, pero aun no registran atencion.</small>
        </article>
        <article class="routes-kpi-card routes-kpi-card-done">
            <span>Jornada completada</span>
            <strong>{{ $resumen['finalizado'] }}</strong>
            <small>Atendieron todo lo asignado.</small>
        </article>
        <article class="routes-kpi-card">
            <span>Sin asignaciones</span>
            <strong>{{ $resumen['sin_asignaciones'] }}</strong>
            <small>No tienen clientes cargados en esta jornada.</small>
        </article>
        <article class="routes-kpi-card">
            <span>Clientes con pedido</span>
            <strong>{{ $resumen['clientes_con_pedido'] }}</strong>
            <small>Asignaciones convertidas en pedido.</small>
        </article>
    </div>
@stop

@section('content')
    <div class="routes-page">
        <section class="routes-status-panel">
            <div class="routes-panel-head">
                <div>
                    <span class="routes-section-kicker">Lectura rapida</span>
                    <h2>Estado actual por preventista</h2>
                    <p>Usa este tablero para saber en segundos quien esta trabajando y enfocar el seguimiento donde haga falta.</p>
                </div>
            </div>

            <div class="routes-status-grid">
                @foreach($estadoPreventistas as $preventista)
                    <button
                        type="button"
                        class="routes-staff-card {{ $preventista->estado_clase }}"
                        onclick="seleccionarPreventista('{{ $preventista->id }}')"
                    >
                        <div class="routes-staff-card-top">
                            <strong>{{ $preventista->nombre }}</strong>
                            <span class="routes-staff-status">{{ $preventista->estado_label }}</span>
                        </div>
                        <p>{{ $preventista->detalle }}</p>
                        <div class="routes-staff-metrics">
                            <span>Total: {{ $preventista->total }}</span>
                            <span>Pendientes: {{ $preventista->pendientes }}</span>
                            <span>Con pedido: {{ $preventista->con_pedido }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="routes-filter-panel">
            <div class="routes-panel-head">
                <div>
                    <span class="routes-section-kicker">Filtro operativo</span>
                    <h2>Ver detalle por preventista o de toda la operacion</h2>
                    <p>Selecciona un preventista para profundizar en su jornada o vuelve a la vista global para comparar a todo el equipo.</p>
                </div>
                <button class="btn btn-danger routes-close-btn" onclick="cerrarAsignaciones()">
                    <i class="fas fa-lock"></i> Cerrar asignaciones del personal
                </button>
            </div>

            <div class="routes-filter-row">
                <label class="routes-filter-field">
                    <span>Preventista</span>
                    <select id="vendedorSelectControl" class="form-select" onchange="valordeusuariovendedor(this)">
                        <option value="">Todos los preventistas</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}" data-id="{{ $vendedor->id }}">{{ $vendedor->nombres }} {{ $vendedor->apellido_paterno }} {{ $vendedor->apellido_materno }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="button" class="btn btn-outline-secondary routes-reset-btn" onclick="limpiarFiltroPreventista()">
                    <i class="fas fa-undo"></i> Ver todos
                </button>
            </div>
        </section>

        <section class="routes-table-panel">
            <div class="routes-panel-head">
                <div>
                    <span class="routes-section-kicker">Detalle de asignaciones</span>
                    <h2 id="routes-table-title">Vista general de clientes asignados</h2>
                    <p id="routes-table-subtitle">Consulta el detalle completo de la operacion actual, ordenado para identificar primero los pendientes.</p>
                </div>
            </div>

            <div class="table-responsive routes-table-wrap">
                <table id="tabla-asignaciones" class="table table-bordered align-middle text-center mb-0" style="min-width: 1100px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Preventista</th>
                            <th>C.I. cliente</th>
                            <th>Cliente</th>
                            <th>Ubicacion</th>
                            <th>Ruta</th>
                            <th>F. asignacion</th>
                            <th>F. atencion</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet" integrity="sha384-d76uxpdVr9QyCSR9vVSYdOAZeRzNUN8A4JVqUHBVXyGxZ+oOfrZVHC/1Y58mhyNg" crossorigin="anonymous">

    <style>
        .content-wrapper {
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 22%),
                linear-gradient(180deg, #edf4f2 0%, #f8fbfa 100%);
        }
        .routes-header,
        .routes-kpi-card,
        .routes-status-panel,
        .routes-filter-panel,
        .routes-table-panel {
            background: #fff;
            border: 1px solid #d8e5e1;
            border-radius: 12px;
        }
        .routes-header {
            align-items: center;
            background: linear-gradient(135deg, #102a43 0%, #1f4f65 48%, #2f7a7b 100%);
            border: 0;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.18);
            color: #fff;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 24px 26px;
        }
        .routes-header span,
        .routes-section-kicker {
            color: #c7f9f1;
            display: inline-block;
            font-size: .76rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .routes-header h1 {
            font-size: 2rem;
            font-weight: 900;
            margin: 6px 0 10px;
        }
        .routes-header p {
            color: rgba(255,255,255,.86);
            margin: 0;
            max-width: 760px;
        }
        .routes-header-summary {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 16px;
            min-width: 220px;
            padding: 14px 16px;
            text-align: right;
        }
        .routes-header-summary span {
            color: rgba(255,255,255,.76);
        }
        .routes-header-summary strong {
            display: block;
            font-size: 1.8rem;
            font-weight: 900;
            margin-top: 6px;
        }
        .routes-kpi-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            margin-bottom: 16px;
        }
        .routes-kpi-card {
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            padding: 16px;
        }
        .routes-kpi-card span {
            color: #0f766e;
            display: block;
            font-size: .76rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .routes-kpi-card strong {
            color: #0f172a;
            display: block;
            font-size: 1.55rem;
            font-weight: 900;
            margin-top: 8px;
        }
        .routes-kpi-card small {
            color: #64748b;
            display: block;
            line-height: 1.45;
            margin-top: 8px;
        }
        .routes-kpi-card-working {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border-color: #bbf7d0;
        }
        .routes-kpi-card-idle {
            background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
            border-color: #fed7aa;
        }
        .routes-kpi-card-done {
            background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%);
            border-color: #bfdbfe;
        }
        .routes-page {
            display: grid;
            gap: 16px;
            padding-bottom: 24px;
        }
        .routes-status-panel,
        .routes-filter-panel,
        .routes-table-panel {
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            padding: 16px;
        }
        .routes-panel-head {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .routes-panel-head h2 {
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 900;
            margin: 4px 0 6px;
        }
        .routes-panel-head p {
            color: #64748b;
            margin: 0;
        }
        .routes-status-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .routes-staff-card {
            background: #f8fafc;
            border: 1px solid #dbe7e3;
            border-radius: 14px;
            cursor: pointer;
            padding: 14px;
            text-align: left;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            width: 100%;
        }
        .routes-staff-card:hover {
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .routes-staff-card-top {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .routes-staff-card-top strong {
            color: #0f172a;
            font-size: .98rem;
        }
        .routes-staff-status {
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 900;
            padding: 6px 10px;
            white-space: nowrap;
        }
        .route-status-working .routes-staff-status {
            background: #dcfce7;
            color: #166534;
        }
        .route-status-idle .routes-staff-status {
            background: #ffedd5;
            color: #9a3412;
        }
        .route-status-done .routes-staff-status {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .route-status-empty .routes-staff-status {
            background: #e5e7eb;
            color: #374151;
        }
        .routes-staff-card p {
            color: #64748b;
            font-size: .9rem;
            line-height: 1.45;
            margin: 0 0 10px;
        }
        .routes-staff-metrics {
            color: #334155;
            display: flex;
            flex-wrap: wrap;
            font-size: .82rem;
            font-weight: 800;
            gap: 8px 14px;
        }
        .routes-filter-row {
            align-items: end;
            display: flex;
            gap: 12px;
        }
        .routes-filter-field {
            flex: 1 1 auto;
            margin: 0;
        }
        .routes-filter-field span {
            color: #334155;
            display: block;
            font-size: .85rem;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .routes-close-btn,
        .routes-reset-btn {
            border-radius: 10px;
            font-weight: 800;
        }
        .routes-table-wrap {
            border: 1px solid #dbe4ec;
            border-radius: 12px;
            overflow: auto;
        }
        #tabla-asignaciones thead th {
            background: #0f172a;
            border-color: #111827;
            color: #fff;
            font-size: .8rem;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        #tabla-asignaciones tbody tr:nth-child(even) {
            background: #fbfefd;
        }
        #tabla-asignaciones tbody tr:hover {
            background: #f1f9f5;
        }
        .route-staff-cell,
        .route-address-cell,
        .route-order-cell {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: left;
        }
        .route-staff-cell strong,
        .route-address-cell strong {
            color: #0f172a;
        }
        .route-staff-cell span,
        .route-address-cell span {
            color: #64748b;
            font-size: .82rem;
        }
        .route-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: .78rem;
            font-weight: 800;
            padding: 6px 10px;
        }
        .route-pill-dark {
            background: #e2e8f0;
            color: #0f172a;
        }
        .route-pill-success {
            background: #dcfce7;
            color: #166534;
        }
        .route-pill-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .route-pill-order {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .route-pill-neutral {
            background: #e5e7eb;
            color: #374151;
        }
        .route-pill-muted {
            background: #f1f5f9;
            color: #64748b;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            min-height: 44px;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            min-height: 42px;
            padding: 6px 10px;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus,
        .form-select:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.15);
            outline: 0;
        }
        @media (max-width: 1199.98px) {
            .routes-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .routes-status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 991.98px) {
            .routes-header,
            .routes-panel-head,
            .routes-filter-row {
                align-items: flex-start;
                flex-direction: column;
            }
            .routes-header-summary {
                min-width: 0;
                text-align: left;
                width: 100%;
            }
        }
        @media (max-width: 767.98px) {
            .routes-kpi-grid,
            .routes-status-grid {
                grid-template-columns: 1fr;
            }
            .routes-header {
                padding: 20px 18px;
            }
            .routes-header h1 {
                font-size: 1.6rem;
            }
            .routes-status-panel,
            .routes-filter-panel,
            .routes-table-panel {
                padding: 14px;
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
        let tablaAsignacionesControl;

        $(document).ready(function() {
            $('#vendedorSelectControl').select2({
                placeholder: "Seleccione un vendedor",
                width: '100%',
                theme: 'classic'
            });

            tablaAsignacionesControl = $('#tabla-asignaciones').DataTable({
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar cliente',
                    searchPlaceholder: 'Nombre, CI o ruta'
                },
                processing: true,
                serverSide: true,
                responsive: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6'p>>",
                ajax: {
                    url: "{{ route('administrador.controlrutas.index') }}",
                    type: "GET",
                },
                columns: [
                    { data: 'id', orderable: false, searchable: false },
                    { data: 'preventista', name: 'preventista', orderable: false, searchable: false },
                    { data: 'ci', name: 'ci' },
                    { data: 'nombre_completo', name: 'nombre_completo' },
                    { data: 'ubicacion', name: 'ubicacion', orderable: false, searchable: false },
                    { data: 'ruta', name: 'ruta' },
                    { data: 'fecha_asignacion', name: 'fecha_asignacion', orderable: false, searchable: false },
                    { data: 'fecha_atencion', name: 'fecha_atencion', orderable: false, searchable: false },
                    { data: 'pedido', name: 'pedido', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']]
            });
        });

        function seleccionarPreventista(id) {
            $('#vendedorSelectControl').val(id).trigger('change');
            recargarTablaPreventista(id);
        }

        function limpiarFiltroPreventista() {
            $('#vendedorSelectControl').val('').trigger('change');
            $('#routes-table-title').text('Vista general de clientes asignados');
            $('#routes-table-subtitle').text('Consulta el detalle completo de la operacion actual, ordenado para identificar primero los pendientes.');
            tablaAsignacionesControl.ajax.url("{{ route('administrador.controlrutas.index') }}").load();
        }

        function valordeusuariovendedor(e) {
            let id = $(e).val();

            if (!id) {
                limpiarFiltroPreventista();
                return;
            }

            recargarTablaPreventista(id);
        }

        function recargarTablaPreventista(id) {
            let nombre = $('#vendedorSelectControl option:selected').text().trim();
            let url = "{{ route('administrador.controlrutas.preventista', ':id') }}".replace(':id', id);
            $('#routes-table-title').text(`Detalle operativo de ${nombre}`);
            $('#routes-table-subtitle').text('Se muestran solo los clientes asignados a este preventista, priorizando los pendientes de atencion.');
            tablaAsignacionesControl.ajax.url(url).load();
        }

        function cerrarAsignaciones() {
            Swal.fire({
                title: 'Estas seguro?',
                text: 'Esta accion cerrara las asignaciones de todo el personal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Si, cerrar asignaciones',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('administrador.controlrutas.cerrarAsignaciones') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            Swal.fire('Cerrado', 'Las asignaciones han sido cerradas.', 'success').then(() => {
                                window.location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudieron cerrar las asignaciones. Intentalo de nuevo mas tarde.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@stop
