@extends('adminlte::page')

@section('title', 'Ventas por Preventista')

@section('content_header')
    <section class="sales-hero">
        <div>
            <span class="hero-kicker">Contabilidad / rendimiento comercial</span>
            <h1>Ventas por preventista</h1>
            <p>Analiza ventas, pedidos, clientes atendidos y detalle de productos por cada preventista dentro del periodo elegido.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-success sales-main-btn" id="btnBuscarVentasPorFecha">
                <i class="fas fa-filter"></i> Aplicar analisis
            </button>
            <button type="button" class="btn btn-outline-secondary sales-main-btn" id="btnLimpiarAnalisis">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="sales-section">
        <div class="filters-grid">
            <label>
                Fecha inicio
                <input type="date" class="form-control" id="fechaInicio" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </label>
            <label>
                Fecha fin
                <input type="date" class="form-control" id="fechaFin" value="{{ now()->format('Y-m-d') }}">
            </label>
            <article class="period-info">
                <span>Periodo analizado</span>
                <strong id="periodoSeleccionado">{{ now()->startOfMonth()->format('d/m/Y') }} al {{ now()->format('d/m/Y') }}</strong>
                <small>Contabilizado y agrupado por preventista</small>
            </article>
        </div>
    </section>

    <section class="sales-section">
        <div class="kpi-grid">
            <article class="kpi-card">
                <span>Total vendido</span>
                <strong id="kpiTotalVendido">Bs 0.00</strong>
                <small>Todos los preventistas del periodo</small>
            </article>
            <article class="kpi-card">
                <span>Preventistas activos</span>
                <strong id="kpiPreventistasActivos">0</strong>
                <small>Con ventas registradas</small>
            </article>
            <article class="kpi-card">
                <span>Pedidos revisados</span>
                <strong id="kpiPedidos">0</strong>
                <small>Del preventista seleccionado</small>
            </article>
            <article class="kpi-card">
                <span>Clientes atendidos</span>
                <strong id="kpiClientes">0</strong>
                <small>Del preventista seleccionado</small>
            </article>
            <article class="kpi-card">
                <span>Ticket promedio</span>
                <strong id="kpiTicketPromedio">Bs 0.00</strong>
                <small>Promedio por pedido del detalle</small>
            </article>
            <article class="kpi-card">
                <span>Preventista en foco</span>
                <strong id="kpiPreventistaFoco">Sin seleccionar</strong>
                <small id="kpiPreventistaFocoMonto">Bs 0.00</small>
            </article>
        </div>
    </section>

    <section class="sales-layout">
        <article class="sales-panel">
            <div class="panel-heading">
                <div>
                    <span>Resumen</span>
                    <h2>Ranking de preventistas</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaVentasPreventista" class="table table-striped table-bordered sales-table w-100">
                    <thead>
                        <tr>
                            <th>Preventista</th>
                            <th>Total vendido</th>
                            <th>Pedidos</th>
                            <th>Clientes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-right">Total:</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </article>

        <article class="sales-panel">
            <div class="panel-heading">
                <div>
                    <span>Detalle operativo</span>
                    <h2 id="tituloDetallePreventista">Pedidos del preventista</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaDetallePedidos" class="table table-striped table-bordered sales-table w-100">
                    <thead>
                        <tr>
                            <th>Nro. Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha de Pedido</th>
                            <th>Fecha de Entrega</th>
                            <th>Ruta</th>
                            <th>Items</th>
                            <th>Total Pedido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>
    </section>

    <section class="sales-section">
        <div class="panel-heading">
            <div>
                <span>Detalle comercial</span>
                <h2 id="tituloDetallePedido">Productos del pedido</h2>
            </div>
            <div class="detail-total" id="totalDetallePedido">Bs 0.00</div>
        </div>
        <div class="table-responsive">
            <table id="tablaDetalleProductos" class="table table-striped table-bordered sales-table w-100">
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
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <style>
        .content-wrapper { background: #eef3f1; }
        .sales-hero, .sales-section, .sales-panel {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .sales-hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .hero-kicker, .panel-heading span {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .sales-hero h1, .panel-heading h2 {
            color: #17211d;
            margin: 0;
            font-weight: 900;
        }
        .sales-hero h1 { font-size: 1.85rem; }
        .sales-hero p {
            color: #64748b;
            margin: 6px 0 0;
            font-weight: 700;
            max-width: 760px;
        }
        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .sales-main-btn, .sales-action-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }
        .sales-section {
            padding: 18px;
            margin-bottom: 16px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 12px;
            align-items: end;
        }
        .filters-grid label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .period-info {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .period-info span {
            color: #64748b;
            display: block;
            font-size: .82rem;
            font-weight: 800;
        }
        .period-info strong {
            color: #17211d;
            display: block;
            font-size: 1.05rem;
            font-weight: 900;
            margin-top: 4px;
        }
        .period-info small {
            color: #64748b;
            display: block;
            font-weight: 700;
            margin-top: 6px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .kpi-card {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 16px;
        }
        .kpi-card span {
            color: #64748b;
            display: block;
            font-size: .82rem;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .kpi-card strong {
            color: #17211d;
            display: block;
            font-size: 1.45rem;
            font-weight: 900;
        }
        .kpi-card small {
            color: #15803d;
            display: block;
            font-size: .82rem;
            font-weight: 800;
            margin-top: 8px;
        }
        .sales-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .sales-panel {
            padding: 18px;
        }
        .panel-heading {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 12px;
        }
        .detail-total {
            color: #166534;
            font-size: 1.25rem;
            font-weight: 900;
            white-space: nowrap;
        }
        .sales-table { width: 100% !important; }
        .dataTables_wrapper .dt-buttons .btn {
            border-radius: 8px;
            font-weight: 800;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
        }
        @media (max-width: 991.98px) {
            .sales-hero, .hero-actions, .panel-heading {
                flex-direction: column;
                align-items: stretch;
            }
            .filters-grid, .kpi-grid {
                grid-template-columns: 1fr;
            }
            .sales-main-btn, .sales-action-btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script>
        let tablaVentasPreventista = null;
        let tablaDetallePedidos = null;
        let tablaDetalleProductos = null;
        let preventistaSeleccionado = null;

        function formatoMoneda(valor) {
            return 'Bs ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function actualizarPeriodo() {
            const inicio = $('#fechaInicio').val();
            const fin = $('#fechaFin').val();

            if (!inicio || !fin) {
                return;
            }

            const [anioI, mesI, diaI] = inicio.split('-');
            const [anioF, mesF, diaF] = fin.split('-');
            $('#periodoSeleccionado').text(`${diaI}/${mesI}/${anioI} al ${diaF}/${mesF}/${anioF}`);
        }

        function actualizarResumenPreventistas(data) {
            const totalVendido = data.reduce((suma, fila) => suma + Number(fila.total_vendido || 0), 0);
            $('#kpiTotalVendido').text(formatoMoneda(totalVendido));
            $('#kpiPreventistasActivos').text(data.length.toLocaleString('en-US'));

            if (!preventistaSeleccionado && data.length) {
                $('#kpiPreventistaFoco').text(data[0].preventista || 'Sin nombre');
                $('#kpiPreventistaFocoMonto').text(formatoMoneda(data[0].total_vendido || 0));
            } else if (!data.length) {
                $('#kpiPreventistaFoco').text('Sin seleccionar');
                $('#kpiPreventistaFocoMonto').text('Bs 0.00');
            }
        }

        function actualizarResumenPedidos(data) {
            const pedidos = data.length;
            const clientes = new Set(data.map((fila) => fila.cliente)).size;
            const total = data.reduce((suma, fila) => suma + Number(fila.total_pedido || 0), 0);
            const ticket = pedidos > 0 ? total / pedidos : 0;

            $('#kpiPedidos').text(pedidos.toLocaleString('en-US'));
            $('#kpiClientes').text(clientes.toLocaleString('en-US'));
            $('#kpiTicketPromedio').text(formatoMoneda(ticket));
        }

        function actualizarTotalDetalleProductos(data) {
            const total = data.reduce((suma, fila) => suma + Number(fila.total || 0), 0);
            $('#totalDetallePedido').text(formatoMoneda(total));
        }

        function limpiarDetallePedido() {
            $('#tituloDetallePedido').text('Productos del pedido');
            $('#totalDetallePedido').text('Bs 0.00');
            tablaDetalleProductos.clear().draw();
        }

        function cargarRankingPreventistas() {
            const fechaInicio = $('#fechaInicio').val();
            const fechaFin = $('#fechaFin').val();

            let url = "{{ route('contabilidad.ventas.porPreventista.opciones', [':fechainicio', ':fechafin']) }}"
                .replace(':fechainicio', fechaInicio)
                .replace(':fechafin', fechaFin);

            tablaVentasPreventista.ajax.url(url).load();
        }

        function cargarDetallePreventista(idPreventista, nombrePreventista, totalVendido) {
            preventistaSeleccionado = idPreventista;
            $('#tituloDetallePreventista').text(`Pedidos de ${nombrePreventista}`);
            $('#kpiPreventistaFoco').text(nombrePreventista);
            $('#kpiPreventistaFocoMonto').text(formatoMoneda(totalVendido));
            limpiarDetallePedido();

            const fechaInicio = $('#fechaInicio').val();
            const fechaFin = $('#fechaFin').val();

            let url = "{{ route('contabilidad.ventas.porPreventista.detallepedidos', [':fechainicio', ':fechafin', ':idpreventista']) }}"
                .replace(':fechainicio', fechaInicio)
                .replace(':fechafin', fechaFin)
                .replace(':idpreventista', idPreventista);

            tablaDetallePedidos.ajax.url(url).load();
        }

        function cargarDetallePedido(numeroPedido) {
            $('#tituloDetallePedido').text(`Productos del pedido #${numeroPedido}`);
            let url = "{{ route('contabilidad.ventas.porDia.preventista.detallepedidos.detalle', ':idpedido') }}"
                .replace(':idpedido', numeroPedido);

            tablaDetalleProductos.ajax.url(url).load();
        }

        $(document).ready(function () {
            tablaVentasPreventista = $('#tablaVentasPreventista').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                language: { url: '/i18n/es-ES.json' },
                ajax: {
                    url: "{{ route('contabilidad.ventas.porPreventista.opciones', [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')]) }}",
                    type: 'GET',
                    dataSrc: function (json) {
                        actualizarResumenPreventistas(json.data || []);
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'preventista', name: 'preventista' },
                    { data: 'total_vendido', name: 'total_vendido', render: (data) => formatoMoneda(data) },
                    { data: 'pedidos', name: 'pedidos' },
                    { data: 'clientes', name: 'clientes' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Excel' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', text: 'Imprimir' }
                ],
                footerCallback: function () {
                    let api = this.api();
                    let total = api.column(1, { search: 'applied' }).data().reduce((a, b) => Number(a || 0) + Number(b || 0), 0);
                    let pedidos = api.column(2, { search: 'applied' }).data().reduce((a, b) => Number(a || 0) + Number(b || 0), 0);
                    let clientes = api.column(3, { search: 'applied' }).data().reduce((a, b) => Number(a || 0) + Number(b || 0), 0);
                    $(api.column(1).footer()).html(`<strong>${formatoMoneda(total)}</strong>`);
                    $(api.column(2).footer()).html(`<strong>${pedidos}</strong>`);
                    $(api.column(3).footer()).html(`<strong>${clientes}</strong>`);
                }
            });

            tablaDetallePedidos = $('#tablaDetallePedidos').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                language: { url: '/i18n/es-ES.json' },
                ajax: {
                    url: "{{ route('contabilidad.ventas.porPreventista.detallepedidos', [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d'), 0]) }}",
                    type: 'GET',
                    dataSrc: function (json) {
                        actualizarResumenPedidos(json.data || []);
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'nro_pedido', name: 'nro_pedido' },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'fecha_pedido', name: 'fecha_pedido' },
                    { data: 'fecha_entrega', name: 'fecha_entrega' },
                    { data: 'ruta', name: 'ruta' },
                    { data: 'items', name: 'items' },
                    { data: 'total_pedido', name: 'total_pedido', render: (data) => formatoMoneda(data) },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ]
            });

            tablaDetalleProductos = $('#tablaDetalleProductos').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                responsive: true,
                autoWidth: false,
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                language: { url: '/i18n/es-ES.json' },
                ajax: {
                    url: "{{ route('contabilidad.ventas.porDia.preventista.detallepedidos.detalle', 0) }}",
                    type: 'GET',
                    dataSrc: function (json) {
                        actualizarTotalDetalleProductos(json.data || []);
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'cantidad', name: 'cantidad' },
                    { data: 'precio_unitario', name: 'precio_unitario', render: (data) => formatoMoneda(data) },
                    { data: 'total', name: 'total', render: (data) => formatoMoneda(data) },
                ],
            });

            $('#btnBuscarVentasPorFecha').on('click', function () {
                const fechaInicio = $('#fechaInicio').val();
                const fechaFin = $('#fechaFin').val();

                if (!fechaInicio || !fechaFin) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fechas requeridas',
                        text: 'Selecciona fecha inicio y fecha fin para continuar.',
                    });
                    return;
                }

                if (fechaInicio > fechaFin) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rango invalido',
                        text: 'La fecha inicio no puede ser mayor que la fecha fin.',
                    });
                    return;
                }

                actualizarPeriodo();
                preventistaSeleccionado = null;
                $('#tituloDetallePreventista').text('Pedidos del preventista');
                $('#kpiPedidos').text('0');
                $('#kpiClientes').text('0');
                $('#kpiTicketPromedio').text('Bs 0.00');
                cargarRankingPreventistas();
                tablaDetallePedidos.clear().draw();
                limpiarDetallePedido();
            });

            $('#btnLimpiarAnalisis').on('click', function () {
                $('#fechaInicio').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
                $('#fechaFin').val("{{ now()->format('Y-m-d') }}");
                actualizarPeriodo();
                preventistaSeleccionado = null;
                $('#tituloDetallePreventista').text('Pedidos del preventista');
                $('#kpiPedidos').text('0');
                $('#kpiClientes').text('0');
                $('#kpiTicketPromedio').text('Bs 0.00');
                cargarRankingPreventistas();
                tablaDetallePedidos.clear().draw();
                limpiarDetallePedido();
            });

            $('#tablaVentasPreventista').on('click', '.btn-ver-preventista', function () {
                cargarDetallePreventista(
                    $(this).data('preventista'),
                    $(this).data('preventista-nombre'),
                    $(this).data('preventista-total')
                );
            });

            $('#tablaDetallePedidos').on('click', '.btn-ver-pedido', function () {
                cargarDetallePedido($(this).data('pedido'));
            });

            actualizarPeriodo();
            cargarRankingPreventistas();
            tablaDetallePedidos.clear().draw();
            limpiarDetallePedido();
        });
    </script>
@stop
