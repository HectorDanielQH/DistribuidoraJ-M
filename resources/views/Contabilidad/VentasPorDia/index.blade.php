@extends('adminlte::page')

@section('title', 'Ventas por Dia')

@section('content_header')
    <section class="day-sales-hero">
        <div>
            <span class="hero-kicker">Contabilidad / control diario de ventas</span>
            <h1>Ventas por dia</h1>
            <p>Revisa el comportamiento diario de la venta contabilizada, detecta dias fuertes o flojos y baja hasta el pedido y sus productos desde una sola pantalla.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-success day-sales-btn" id="btnBuscarVentasPorDia">
                <i class="fas fa-filter"></i> Actualizar panel
            </button>
            <button type="button" class="btn btn-outline-secondary day-sales-btn" id="btnLimpiarVentasPorDia">
                <i class="fas fa-eraser"></i> Limpiar filtros
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="day-sales-section">
        <div class="filters-grid">
            <label>
                Fecha inicio
                <input type="date" class="form-control" id="fechaInicio" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </label>
            <label>
                Fecha fin
                <input type="date" class="form-control" id="fechaFin" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Rutas
                <select id="ruta_id" class="form-control day-sales-select" multiple>
                    @foreach($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Preventistas
                <select id="preventista_id" class="form-control day-sales-select" multiple>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="insight-grid">
            <article class="period-info">
                <span>Periodo analizado</span>
                <strong id="periodoSeleccionado">{{ now()->startOfMonth()->format('d/m/Y') }} al {{ now()->format('d/m/Y') }}</strong>
                <small id="resumenFiltros">Todas las rutas y todos los preventistas</small>
            </article>
            <article class="period-info">
                <span>Dia seleccionado</span>
                <strong id="diaSeleccionado">Sin seleccionar</strong>
                <small id="diaSeleccionadoResumen">Elige un dia en la tabla para ver pedidos y productos</small>
            </article>
        </div>
    </section>

    <section class="day-sales-section">
        <div class="kpi-grid">
            <article class="kpi-card">
                <span>Total vendido</span>
                <strong id="kpiTotalVendido">Bs 0.00</strong>
                <small>Venta contabilizada del periodo</small>
            </article>
            <article class="kpi-card">
                <span>Dias con venta</span>
                <strong id="kpiDiasConVenta">0</strong>
                <small>Fechas con movimiento</small>
            </article>
            <article class="kpi-card">
                <span>Pedidos del periodo</span>
                <strong id="kpiPedidosPeriodo">0</strong>
                <small>Pedidos cerrados en el rango</small>
            </article>
            <article class="kpi-card">
                <span>Clientes atendidos</span>
                <strong id="kpiClientesPeriodo">0</strong>
                <small>Clientes unicos del periodo</small>
            </article>
            <article class="kpi-card">
                <span>Preventistas activos</span>
                <strong id="kpiPreventistasActivos">0</strong>
                <small>Con ventas contabilizadas</small>
            </article>
            <article class="kpi-card">
                <span>Promedio por dia</span>
                <strong id="kpiPromedioDia">Bs 0.00</strong>
                <small>Promedio diario del rango</small>
            </article>
            <article class="kpi-card">
                <span>Mejor dia</span>
                <strong id="kpiMejorDia">Sin datos</strong>
                <small id="kpiMejorDiaMonto">Bs 0.00</small>
            </article>
            <article class="kpi-card">
                <span>Dia en revision</span>
                <strong id="kpiPedidosDia">0 pedidos</strong>
                <small id="kpiClientesDia">0 clientes</small>
            </article>
        </div>
    </section>

    <section class="day-sales-section">
        <div class="panel-heading">
            <div>
                <span>Analisis visual</span>
                <h2>Tendencia diaria del periodo</h2>
            </div>
            <div class="chart-legend">
                <span class="legend-item"><i class="legend-dot legend-dot-sales"></i> Venta diaria</span>
                <span class="legend-item"><i class="legend-dot legend-dot-avg"></i> Promedio del periodo</span>
            </div>
        </div>
        <div class="chart-shell">
            <canvas id="graficoVentasPorDia"></canvas>
        </div>
    </section>

    <section class="day-sales-layout">
        <article class="day-sales-panel">
            <div class="panel-heading">
                <div>
                    <span>Resumen diario</span>
                    <h2>Totales agrupados por fecha</h2>
                </div>
                <div class="panel-help">Toca un dia para ver sus pedidos</div>
            </div>
            <div class="table-responsive">
                <table id="tablaVentasPorDia" class="table table-striped table-bordered day-sales-table w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Total vendido</th>
                            <th>Pedidos</th>
                            <th>Clientes</th>
                            <th>Preventistas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>

        <article class="day-sales-panel">
            <div class="panel-heading">
                <div>
                    <span>Detalle operativo</span>
                    <h2 id="tituloDetalleDia">Pedidos del dia</h2>
                </div>
                <div class="day-summary-badges">
                    <span class="summary-badge" id="badgePedidosDia">0 pedidos</span>
                    <span class="summary-badge" id="badgeClientesDia">0 clientes</span>
                    <span class="summary-badge" id="badgeTotalDia">Bs 0.00</span>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaDetallePedidos" class="table table-striped table-bordered day-sales-table w-100">
                    <thead>
                        <tr>
                            <th>Nro. Pedido</th>
                            <th>Cliente</th>
                            <th>Ruta</th>
                            <th>Preventista</th>
                            <th>Fecha pedido</th>
                            <th>Fecha entrega</th>
                            <th>Items</th>
                            <th>Total pedido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>
    </section>

    <section class="day-sales-section">
        <div class="panel-heading">
            <div>
                <span>Detalle comercial</span>
                <h2 id="tituloDetallePedido">Productos del pedido</h2>
            </div>
            <div class="detail-total" id="totalDetallePedido">Bs 0.00</div>
        </div>
        <div class="table-responsive">
            <table id="tablaDetalleProductos" class="table table-striped table-bordered day-sales-table w-100">
                <thead>
                    <tr>
                        <th>Cod. Prod.</th>
                        <th>Producto</th>
                        <th>Presentacion</th>
                        <th>Cantidad</th>
                        <th>Unidades</th>
                        <th>Precio unitario</th>
                        <th>Desc. %</th>
                        <th>Total</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <style>
        .content-wrapper {
            background: #eef3f1;
        }

        .day-sales-hero,
        .day-sales-section,
        .day-sales-panel {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }

        .day-sales-hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .hero-kicker,
        .panel-heading span {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .day-sales-hero h1,
        .panel-heading h2 {
            color: #17211d;
            margin: 0;
            font-weight: 900;
        }

        .day-sales-hero h1 {
            font-size: 1.85rem;
        }

        .day-sales-hero p {
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

        .day-sales-btn,
        .sales-action-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }

        .day-sales-section {
            padding: 18px;
            margin-bottom: 16px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }

        .filters-grid label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .period-info {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px 16px;
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

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 3px 6px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #e8f2ee;
            border: 1px solid #b8d5ca;
            border-radius: 8px;
            color: #17211d;
            font-weight: 800;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
            font-size: 1.35rem;
            font-weight: 900;
        }

        .kpi-card small {
            color: #15803d;
            display: block;
            font-size: .82rem;
            font-weight: 800;
            margin-top: 8px;
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 12px;
        }

        .panel-help {
            color: #64748b;
            font-size: .85rem;
            font-weight: 800;
        }

        .chart-shell {
            height: 320px;
        }

        .chart-legend {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .legend-item {
            align-items: center;
            color: #475569;
            display: inline-flex;
            font-size: .85rem;
            font-weight: 800;
            gap: 6px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .legend-dot-sales {
            background: #16a34a;
        }

        .legend-dot-avg {
            background: #0f172a;
        }

        .day-sales-layout {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .day-sales-panel {
            padding: 18px;
        }

        .day-summary-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .summary-badge {
            background: #edf7f1;
            border: 1px solid #cbe3d4;
            border-radius: 999px;
            color: #166534;
            font-size: .82rem;
            font-weight: 900;
            padding: 6px 10px;
            white-space: nowrap;
        }

        .detail-total {
            color: #166534;
            font-size: 1.25rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .day-sales-table {
            width: 100% !important;
        }

        .day-sales-table tbody td {
            vertical-align: middle;
        }

        .dataTables_wrapper .dt-buttons .btn {
            border-radius: 8px;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .day-sales-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .day-sales-hero,
            .hero-actions,
            .panel-heading {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-grid,
            .insight-grid,
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .day-sales-btn,
            .sales-action-btn {
                width: 100%;
            }

            .chart-shell {
                height: 280px;
            }

            .chart-legend,
            .day-summary-badges {
                justify-content: flex-start;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script>
        let tablaVentasPorDia = null;
        let tablaDetallePedidos = null;
        let tablaDetalleProductos = null;
        let graficoVentasPorDia = null;
        let fechaDetalleSeleccionada = null;
        let pedidoDetalleSeleccionado = null;

        function filtrosDia() {
            return {
                fecha_inicio: $('#fechaInicio').val(),
                fecha_fin: $('#fechaFin').val(),
                ruta_id: $('#ruta_id').val() || [],
                preventista_id: $('#preventista_id').val() || [],
            };
        }

        function formatoMoneda(valor) {
            return 'Bs ' + Number(valor || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatoEntero(valor) {
            return Number(valor || 0).toLocaleString('en-US');
        }

        function formatoFechaVisual(fechaIso) {
            if (!fechaIso) {
                return 'Sin seleccionar';
            }

            const partes = String(fechaIso).split('-');
            if (partes.length !== 3) {
                return fechaIso;
            }

            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

        function textoSeleccionados(selector, textoTodos) {
            const valores = $(selector).val() || [];
            if (!valores.length) {
                return textoTodos;
            }

            return $(selector).find('option:selected').map(function () {
                return $(this).text().trim();
            }).get().join(', ');
        }

        function actualizarEncabezados() {
            $('#periodoSeleccionado').text(`${formatoFechaVisual($('#fechaInicio').val())} al ${formatoFechaVisual($('#fechaFin').val())}`);
            $('#resumenFiltros').text(`${textoSeleccionados('#ruta_id', 'Todas las rutas')} | ${textoSeleccionados('#preventista_id', 'Todos los preventistas')}`);
        }

        function reiniciarDetalleDia() {
            fechaDetalleSeleccionada = null;
            pedidoDetalleSeleccionado = null;
            $('#diaSeleccionado').text('Sin seleccionar');
            $('#diaSeleccionadoResumen').text('Elige un dia en la tabla para ver pedidos y productos');
            $('#tituloDetalleDia').text('Pedidos del dia');
            $('#tituloDetallePedido').text('Productos del pedido');
            $('#kpiPedidosDia').text('0 pedidos');
            $('#kpiClientesDia').text('0 clientes');
            $('#badgePedidosDia').text('0 pedidos');
            $('#badgeClientesDia').text('0 clientes');
            $('#badgeTotalDia').text('Bs 0.00');
            $('#totalDetallePedido').text('Bs 0.00');
            if (tablaDetallePedidos) {
                tablaDetallePedidos.ajax.reload();
            }
            if (tablaDetalleProductos) {
                tablaDetalleProductos.ajax.reload();
            }
        }

        function cargarResumen() {
            $.getJSON("{{ route('contabilidad.ventas.porDia.panel.resumen') }}", filtrosDia(), function (response) {
                $('#kpiTotalVendido').text(formatoMoneda(response.total_vendido || 0));
                $('#kpiDiasConVenta').text(formatoEntero(response.dias_con_venta || 0));
                $('#kpiPedidosPeriodo').text(formatoEntero(response.pedidos || 0));
                $('#kpiClientesPeriodo').text(formatoEntero(response.clientes || 0));
                $('#kpiPreventistasActivos').text(formatoEntero(response.preventistas || 0));
                $('#kpiPromedioDia').text(formatoMoneda(response.promedio_dia || 0));
                $('#kpiMejorDia').text(response.mejor_dia?.fecha || 'Sin datos');
                $('#kpiMejorDiaMonto').text(formatoMoneda(response.mejor_dia?.total || 0));
            });
        }

        function renderizarGrafico(filas) {
            const etiquetas = filas.map((fila) => fila.fecha_venta).reverse();
            const ventas = filas.map((fila) => Number(fila.total_venta || 0)).reverse();
            const promedio = ventas.length
                ? ventas.reduce((suma, valor) => suma + valor, 0) / ventas.length
                : 0;
            const promedioSerie = ventas.map(() => promedio);

            const canvas = document.getElementById('graficoVentasPorDia');
            if (!canvas) {
                return;
            }

            if (graficoVentasPorDia) {
                graficoVentasPorDia.destroy();
            }

            graficoVentasPorDia = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Venta diaria',
                            data: ventas,
                            backgroundColor: '#16a34a',
                            borderRadius: 6,
                            maxBarThickness: 34
                        },
                        {
                            type: 'line',
                            label: 'Promedio del periodo',
                            data: promedioSerie,
                            borderColor: '#0f172a',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.25
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.dataset.label}: ${formatoMoneda(context.raw)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return formatoMoneda(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        function cargarGrafico() {
            $.getJSON("{{ route('contabilidad.ventas.porDia.panel.data') }}", Object.assign({
                draw: 1,
                start: 0,
                length: -1
            }, filtrosDia()), function (response) {
                renderizarGrafico(response.data || []);
            });
        }

        function cargarDetalleDia(fechaIso) {
            fechaDetalleSeleccionada = fechaIso;
            pedidoDetalleSeleccionado = null;
            const fechaVisual = formatoFechaVisual(fechaIso);

            $('#diaSeleccionado').text(fechaVisual);
            $('#diaSeleccionadoResumen').text('Pedidos contabilizados de la fecha elegida');
            $('#tituloDetalleDia').text(`Pedidos del dia ${fechaVisual}`);
            $('#tituloDetallePedido').text('Productos del pedido');
            $('#totalDetallePedido').text('Bs 0.00');

            tablaDetallePedidos.ajax.reload();
            tablaDetalleProductos.ajax.reload();
        }

        function cargarDetallePedido(numeroPedido) {
            pedidoDetalleSeleccionado = numeroPedido;
            $('#tituloDetallePedido').text(`Productos del pedido #${numeroPedido}`);
            tablaDetalleProductos.ajax.reload();
        }

        $(document).ready(function () {
            $('.day-sales-select').select2({
                placeholder: 'Todos',
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            tablaVentasPorDia = $('#tablaVentasPorDia').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                language: { url: '/i18n/es-ES.json' },
                ajax: {
                    url: "{{ route('contabilidad.ventas.porDia.panel.data') }}",
                    data: function (d) {
                        Object.assign(d, filtrosDia());
                    }
                },
                columns: [
                    { data: 'fecha_venta', name: 'fecha_venta' },
                    { data: 'total_venta', name: 'total_venta', render: (data) => formatoMoneda(data) },
                    { data: 'pedidos', name: 'pedidos' },
                    { data: 'clientes', name: 'clientes' },
                    { data: 'preventistas', name: 'preventistas' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Excel resumen' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', text: 'Imprimir resumen' }
                ]
            });

            tablaDetallePedidos = $('#tablaDetallePedidos').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                language: { url: '/i18n/es-ES.json' },
                ajax: function (data, callback) {
                    if (!fechaDetalleSeleccionada) {
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    $.getJSON("{{ route('contabilidad.ventas.porDia.panel.detallepedidos', ':fecha') }}".replace(':fecha', fechaDetalleSeleccionada), Object.assign({}, data, filtrosDia()), function (json) {
                        const filas = json.data || [];
                        const totalDia = filas.reduce((suma, fila) => suma + Number(fila.total_pedido || 0), 0);
                        const clientes = new Set(filas.map((fila) => fila.cliente)).size;

                        $('#kpiPedidosDia').text(`${formatoEntero(filas.length)} pedidos`);
                        $('#kpiClientesDia').text(`${formatoEntero(clientes)} clientes`);
                        $('#badgePedidosDia').text(`${formatoEntero(filas.length)} pedidos`);
                        $('#badgeClientesDia').text(`${formatoEntero(clientes)} clientes`);
                        $('#badgeTotalDia').text(formatoMoneda(totalDia));

                        callback(json);
                    });
                },
                columns: [
                    { data: 'numero_pedido', name: 'numero_pedido' },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'ruta', name: 'ruta' },
                    { data: 'preventista', name: 'preventista' },
                    { data: 'fecha_pedido', name: 'fecha_pedido' },
                    { data: 'fecha_entrega', name: 'fecha_entrega' },
                    { data: 'items', name: 'items' },
                    { data: 'total_pedido', name: 'total_pedido', render: (data) => formatoMoneda(data) },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ]
            });

            tablaDetalleProductos = $('#tablaDetalleProductos').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                language: { url: '/i18n/es-ES.json' },
                ajax: function (data, callback) {
                    if (!pedidoDetalleSeleccionado) {
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    $.getJSON("{{ route('contabilidad.ventas.porDia.preventista.detallepedidos.detalle', ':idpedido') }}".replace(':idpedido', pedidoDetalleSeleccionado), data, function (json) {
                        const total = (json.data || []).reduce((suma, fila) => suma + Number(fila.total || 0), 0);
                        $('#totalDetallePedido').text(formatoMoneda(total));
                        callback(json);
                    });
                },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'presentacion', name: 'presentacion' },
                    { data: 'cantidad', name: 'cantidad' },
                    { data: 'unidades', name: 'unidades' },
                    { data: 'precio_unitario', name: 'precio_unitario', render: (data) => formatoMoneda(data) },
                    { data: 'descuento', name: 'descuento', render: (data) => `${Number(data || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}%` },
                    { data: 'total', name: 'total', render: (data) => formatoMoneda(data) },
                ]
            });

            $('#btnBuscarVentasPorDia').on('click', function () {
                const filtros = filtrosDia();

                if (!filtros.fecha_inicio || !filtros.fecha_fin) {
                    Swal.fire('Fechas requeridas', 'Selecciona fecha inicio y fecha fin.', 'warning');
                    return;
                }

                if (filtros.fecha_inicio > filtros.fecha_fin) {
                    Swal.fire('Rango invalido', 'La fecha inicio no puede ser mayor que la fecha fin.', 'warning');
                    return;
                }

                actualizarEncabezados();
                reiniciarDetalleDia();
                cargarResumen();
                cargarGrafico();
                tablaVentasPorDia.ajax.reload();
            });

            $('#btnLimpiarVentasPorDia').on('click', function () {
                $('#fechaInicio').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
                $('#fechaFin').val("{{ now()->format('Y-m-d') }}");
                $('#ruta_id').val(null).trigger('change');
                $('#preventista_id').val(null).trigger('change');
                $('#btnBuscarVentasPorDia').trigger('click');
            });

            $('#tablaVentasPorDia').on('click', '.btn-ver-dia', function () {
                cargarDetalleDia($(this).data('fecha'));
            });

            $('#tablaDetallePedidos').on('click', '.btn-ver-pedido-dia', function () {
                cargarDetallePedido($(this).data('pedido'));
            });

            actualizarEncabezados();
            cargarResumen();
            cargarGrafico();
        });
    </script>
@stop
