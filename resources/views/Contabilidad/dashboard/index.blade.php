@extends('adminlte::page')

@section('title', 'Dashboard Contable')

@section('content_header')
    <section class="accounting-hero">
        <div>
            <span class="hero-kicker">Contabilidad / inteligencia comercial</span>
            <h1>Dashboard contable</h1>
            <p>Control diario, semanal, mensual y anual para ventas, utilidad estimada, preventistas, rutas y productos.</p>
        </div>
        <div class="hero-actions">
            <a href="{{ route('contabilidad.ventas.porPreventista') }}" class="btn btn-outline-success accounting-btn">
                <i class="fas fa-users"></i> Reporte por preventista
            </a>
            <a href="{{ route('contabilidad.ventas.comparacionGanancial') }}" class="btn btn-outline-secondary accounting-btn">
                <i class="fas fa-balance-scale"></i> Comparacion ganancial
            </a>
        </div>
    </section>
@stop

@section('content')
    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Filtros operativos</span>
                <h2>Periodo y responsables</h2>
            </div>
            <div class="preset-group" id="presetGroup">
                <button type="button" class="btn btn-light preset-btn active" data-preset="today">Hoy</button>
                <button type="button" class="btn btn-light preset-btn" data-preset="week">Semana</button>
                <button type="button" class="btn btn-light preset-btn" data-preset="month">Mes</button>
                <button type="button" class="btn btn-light preset-btn" data-preset="year">Anio</button>
                <button type="button" class="btn btn-light preset-btn" data-preset="custom">Personalizado</button>
            </div>
        </div>

        <div class="filters-grid">
            <label>
                Fecha inicio
                <input type="date" id="fecha_inicio" class="form-control">
            </label>
            <label>
                Fecha fin
                <input type="date" id="fecha_fin" class="form-control">
            </label>
            <label>
                Rutas
                <select id="ruta_id" class="form-control accounting-select" multiple>
                    @foreach($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Preventistas
                <select id="preventista_id" class="form-control accounting-select" multiple>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="filter-actions">
                <button type="button" class="btn btn-success accounting-btn" id="btnAplicarDashboard">
                    <i class="fas fa-filter"></i> Aplicar
                </button>
                <button type="button" class="btn btn-outline-secondary accounting-btn" id="btnLimpiarDashboard">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </div>
    </section>

    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Resumen ejecutivo</span>
                <h2 id="etiquetaPeriodo">Periodo</h2>
            </div>
        </div>

        <div class="kpi-grid">
            <article class="kpi-card">
                <span>Ventas netas</span>
                <strong id="kpi_ventas_netas">Bs 0.00</strong>
                <small id="kpi_crecimiento_ventas">0.00%</small>
            </article>
            <article class="kpi-card">
                <span>Utilidad estimada</span>
                <strong id="kpi_utilidad_estimada">Bs 0.00</strong>
                <small id="kpi_crecimiento_utilidad">0.00%</small>
            </article>
            <article class="kpi-card">
                <span>Costo estimado</span>
                <strong id="kpi_costo_estimado">Bs 0.00</strong>
                <small>Base promedio por lote y producto</small>
            </article>
            <article class="kpi-card">
                <span>Pedidos contabilizados</span>
                <strong id="kpi_pedidos">0</strong>
                <small id="kpi_crecimiento_pedidos">0.00%</small>
            </article>
            <article class="kpi-card">
                <span>Clientes atendidos</span>
                <strong id="kpi_clientes">0</strong>
                <small>Clientes unicos en el periodo</small>
            </article>
            <article class="kpi-card">
                <span>Ticket promedio</span>
                <strong id="kpi_ticket_promedio">Bs 0.00</strong>
                <small id="kpi_margen">Margen 0.00%</small>
            </article>
            <article class="kpi-card">
                <span>Mejor preventista</span>
                <strong id="kpi_mejor_preventista">Sin datos</strong>
                <small id="kpi_mejor_preventista_total">Bs 0.00</small>
            </article>
            <article class="kpi-card">
                <span>Mejor ruta</span>
                <strong id="kpi_mejor_ruta">Sin datos</strong>
                <small id="kpi_mejor_ruta_total">Bs 0.00</small>
            </article>
        </div>
    </section>

    <section class="accounting-section analytics-grid">
        <article class="chart-panel wide">
            <div class="panel-heading">
                <div>
                    <span>Tendencia</span>
                    <h3>Ventas y utilidad</h3>
                </div>
            </div>
            <div class="chart-frame chart-frame-lg">
                <canvas id="chartVentasUtilidad"></canvas>
            </div>
        </article>

        <article class="chart-panel">
            <div class="panel-heading">
                <div>
                    <span>Ranking</span>
                    <h3>Preventistas</h3>
                </div>
            </div>
            <div class="chart-frame chart-frame-md">
                <canvas id="chartPreventistas"></canvas>
            </div>
        </article>

        <article class="chart-panel">
            <div class="panel-heading">
                <div>
                    <span>Distribucion</span>
                    <h3>Rutas</h3>
                </div>
            </div>
            <div class="chart-frame chart-frame-md">
                <canvas id="chartRutas"></canvas>
            </div>
        </article>

        <article class="chart-panel wide">
            <div class="panel-heading">
                <div>
                    <span>Portafolio</span>
                    <h3>Top productos por venta</h3>
                </div>
            </div>
            <div class="chart-frame chart-frame-lg">
                <canvas id="chartProductos"></canvas>
            </div>
        </article>
    </section>

    <section class="accounting-section split-grid">
        <article>
            <div class="section-heading compact">
                <div>
                    <span>Alertas contables</span>
                    <h2>Riesgos y pendientes</h2>
                </div>
            </div>
            <div class="alerts-list" id="alertsList"></div>
        </article>

        <article>
            <div class="section-heading compact">
                <div>
                    <span>Accesos rapidos</span>
                    <h2>Herramientas del contador</h2>
                </div>
            </div>
            <div class="quick-links">
                <a href="{{ route('contabilidad.ventas.porDia') }}" class="quick-link">
                    <strong>Dashboard principal</strong>
                    <span>Seguimiento ejecutivo del periodo</span>
                </a>
                <a href="{{ route('contabilidad.ventas.porPreventista') }}" class="quick-link">
                    <strong>Ventas por preventista</strong>
                    <span>Analisis detallado por vendedor</span>
                </a>
                <a href="{{ route('contabilidad.ventas.comparacionGanancial') }}" class="quick-link">
                    <strong>Comparacion ganancial</strong>
                    <span>Vista comparativa historica</span>
                </a>
            </div>
        </article>
    </section>

    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Centro de reportes</span>
                <h2>Cierre y productividad</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered accounting-table" id="tablaCierres">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Ventas netas</th>
                        <th>Utilidad estimada</th>
                        <th>Pedidos</th>
                        <th>Clientes</th>
                        <th>Ticket promedio</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Rentabilidad por equipo</span>
                <h2>Preventistas</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered accounting-table" id="tablaPreventistas">
                <thead>
                    <tr>
                        <th>Preventista</th>
                        <th>Ventas netas</th>
                        <th>Utilidad estimada</th>
                        <th>Pedidos</th>
                        <th>Clientes</th>
                        <th>Ticket promedio</th>
                        <th>Margen %</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Rentabilidad territorial</span>
                <h2>Rutas</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered accounting-table" id="tablaRutas">
                <thead>
                    <tr>
                        <th>Ruta</th>
                        <th>Ventas netas</th>
                        <th>Utilidad estimada</th>
                        <th>Pedidos</th>
                        <th>Clientes</th>
                        <th>Ticket promedio</th>
                        <th>Margen %</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="accounting-section">
        <div class="section-heading">
            <div>
                <span>Mix comercial</span>
                <h2>Productos, linea y marca</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered accounting-table" id="tablaProductos">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Linea</th>
                        <th>Unidades</th>
                        <th>Ventas netas</th>
                        <th>Utilidad estimada</th>
                        <th>Margen %</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css">
    <style>
        .content-wrapper { background: #eef3f1; }
        .accounting-hero,
        .accounting-section {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .accounting-hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .hero-kicker,
        .section-heading span,
        .panel-heading span {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .accounting-hero h1,
        .section-heading h2,
        .panel-heading h3 {
            color: #17211d;
            margin: 0;
            font-weight: 900;
        }
        .accounting-hero h1 { font-size: 1.9rem; }
        .accounting-hero p {
            color: #64748b;
            margin: 6px 0 0;
            font-weight: 700;
            max-width: 720px;
        }
        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .accounting-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }
        .accounting-section {
            padding: 18px;
            margin-bottom: 16px;
        }
        .section-heading {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-heading.compact { margin-bottom: 12px; }
        .preset-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .preset-btn {
            border-radius: 8px;
            font-weight: 800;
        }
        .preset-btn.active {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }
        .filters-grid label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .filter-actions {
            display: grid;
            gap: 8px;
        }
        .select2-container { width: 100% !important; }
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
            min-height: 118px;
        }
        .kpi-card span {
            color: #64748b;
            display: block;
            font-size: .85rem;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .kpi-card strong {
            color: #17211d;
            display: block;
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1.15;
        }
        .kpi-card small {
            color: #15803d;
            display: block;
            font-size: .82rem;
            font-weight: 800;
            margin-top: 8px;
        }
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .chart-panel {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 16px;
        }
        .chart-frame {
            position: relative;
            width: 100%;
        }
        .chart-frame-lg {
            height: 320px;
        }
        .chart-frame-md {
            height: 280px;
        }
        .chart-panel.wide {
            grid-column: 1 / -1;
        }
        .panel-heading {
            margin-bottom: 10px;
        }
        .split-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 16px;
        }
        .alerts-list,
        .quick-links {
            display: grid;
            gap: 10px;
        }
        .alert-row,
        .quick-link {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px;
        }
        .alert-row strong,
        .quick-link strong {
            color: #17211d;
            display: block;
            font-weight: 900;
        }
        .alert-row span,
        .quick-link span {
            color: #64748b;
            display: block;
            font-weight: 700;
            margin-top: 4px;
        }
        .alert-warning { border-left: 4px solid #d97706; }
        .alert-danger { border-left: 4px solid #dc2626; }
        .alert-info { border-left: 4px solid #0284c7; }
        .alert-secondary { border-left: 4px solid #64748b; }
        .quick-link {
            text-decoration: none !important;
            transition: border-color .2s ease;
        }
        .quick-link:hover { border-color: #0f766e; }
        .accounting-table { width: 100% !important; }
        .dataTables_wrapper .dt-buttons .btn {
            border-radius: 8px;
            font-weight: 800;
        }
        @media (max-width: 991.98px) {
            .accounting-hero,
            .section-heading,
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .filters-grid,
            .kpi-grid,
            .analytics-grid,
            .split-grid {
                grid-template-columns: 1fr;
            }
            .chart-panel.wide {
                grid-column: auto;
            }
            .accounting-btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script>
        let presetActual = 'today';
        let chartVentasUtilidad = null;
        let chartPreventistas = null;
        let chartRutas = null;
        let chartProductos = null;

        const tablas = {};

        function obtenerFiltros() {
            return {
                preset: presetActual,
                fecha_inicio: $('#fecha_inicio').val(),
                fecha_fin: $('#fecha_fin').val(),
                ruta_id: $('#ruta_id').val() || [],
                preventista_id: $('#preventista_id').val() || [],
            };
        }

        function formatoMoneda(valor) {
            return 'Bs ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatoNumero(valor) {
            return Number(valor || 0).toLocaleString('en-US');
        }

        function aplicarPresetFechas(preset) {
            const hoy = new Date();
            const toInput = (fecha) => {
                const anio = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia = String(fecha.getDate()).padStart(2, '0');
                return `${anio}-${mes}-${dia}`;
            };

            let inicio = new Date(hoy);
            let fin = new Date(hoy);

            if (preset === 'week') {
                const diaSemana = (hoy.getDay() + 6) % 7;
                inicio.setDate(hoy.getDate() - diaSemana);
                fin = new Date(inicio);
                fin.setDate(inicio.getDate() + 6);
            } else if (preset === 'month') {
                inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
            } else if (preset === 'year') {
                inicio = new Date(hoy.getFullYear(), 0, 1);
                fin = new Date(hoy.getFullYear(), 11, 31);
            }

            if (preset !== 'custom') {
                $('#fecha_inicio').val(toInput(inicio));
                $('#fecha_fin').val(toInput(fin));
            }
        }

        function cargarResumen() {
            $.getJSON("{{ route('contabilidad.dashboard.resumen') }}", obtenerFiltros(), function (response) {
                $('#etiquetaPeriodo').text(response.periodo.etiqueta);
                $('#kpi_ventas_netas').text(formatoMoneda(response.kpis.ventas_netas));
                $('#kpi_utilidad_estimada').text(formatoMoneda(response.kpis.utilidad_estimada));
                $('#kpi_costo_estimado').text(formatoMoneda(response.kpis.costo_estimado));
                $('#kpi_pedidos').text(formatoNumero(response.kpis.pedidos));
                $('#kpi_clientes').text(formatoNumero(response.kpis.clientes));
                $('#kpi_ticket_promedio').text(formatoMoneda(response.kpis.ticket_promedio));
                $('#kpi_margen').text(`Margen ${Number(response.kpis.margen || 0).toFixed(2)}%`);
                $('#kpi_crecimiento_ventas').text(`${Number(response.kpis.crecimiento_ventas || 0).toFixed(2)}% vs periodo anterior`);
                $('#kpi_crecimiento_utilidad').text(`${Number(response.kpis.crecimiento_utilidad || 0).toFixed(2)}% vs periodo anterior`);
                $('#kpi_crecimiento_pedidos').text(`${Number(response.kpis.crecimiento_pedidos || 0).toFixed(2)}% vs periodo anterior`);
                $('#kpi_mejor_preventista').text(response.kpis.mejor_preventista.nombre || 'Sin datos');
                $('#kpi_mejor_preventista_total').text(formatoMoneda(response.kpis.mejor_preventista.total || 0));
                $('#kpi_mejor_ruta').text(response.kpis.mejor_ruta.nombre || 'Sin datos');
                $('#kpi_mejor_ruta_total').text(formatoMoneda(response.kpis.mejor_ruta.total || 0));

                const alertas = response.alertas.map((alerta) => `
                    <div class="alert-row alert-${alerta.tipo}">
                        <strong>${alerta.titulo}</strong>
                        <span>${alerta.detalle}</span>
                    </div>
                `).join('');

                $('#alertsList').html(alertas);
            });
        }

        function destruirGrafico(instancia) {
            if (instancia) {
                instancia.destroy();
            }
        }

        function reiniciarCanvas(idCanvas) {
            const anterior = document.getElementById(idCanvas);
            const contenedor = anterior.parentNode;
            const nuevo = document.createElement('canvas');
            nuevo.id = idCanvas;
            contenedor.replaceChild(nuevo, anterior);
            return nuevo;
        }

        function cargarSeries() {
            $.getJSON("{{ route('contabilidad.dashboard.series') }}", obtenerFiltros(), function (response) {
                destruirGrafico(chartVentasUtilidad);
                destruirGrafico(chartPreventistas);
                destruirGrafico(chartRutas);
                destruirGrafico(chartProductos);

                chartVentasUtilidad = new Chart(reiniciarCanvas('chartVentasUtilidad'), {
                    type: 'line',
                    data: {
                        labels: response.serie.labels,
                        datasets: [
                            {
                                label: 'Ventas netas',
                                data: response.serie.ventas,
                                borderColor: '#0f766e',
                                backgroundColor: 'rgba(15, 118, 110, .12)',
                                tension: .35,
                                fill: true
                            },
                            {
                                label: 'Utilidad estimada',
                                data: response.serie.utilidad,
                                borderColor: '#d97706',
                                backgroundColor: 'rgba(217, 119, 6, .10)',
                                tension: .35,
                                fill: true
                            }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                chartPreventistas = new Chart(reiniciarCanvas('chartPreventistas'), {
                    type: 'bar',
                    data: {
                        labels: response.preventistas.labels,
                        datasets: [{ label: 'Ventas', data: response.preventistas.data, backgroundColor: '#16a34a' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                chartRutas = new Chart(reiniciarCanvas('chartRutas'), {
                    type: 'doughnut',
                    data: {
                        labels: response.rutas.labels,
                        datasets: [{
                            data: response.rutas.data,
                            backgroundColor: ['#0f766e', '#16a34a', '#d97706', '#dc2626', '#0284c7', '#7c3aed', '#ca8a04', '#475569']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                chartProductos = new Chart(reiniciarCanvas('chartProductos'), {
                    type: 'bar',
                    data: {
                        labels: response.productos.labels,
                        datasets: [{ label: 'Ventas', data: response.productos.data, backgroundColor: '#15803d' }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            });
        }

        function inicializarTabla(selector, url, columnas) {
            return $(selector).DataTable({
                ajax: {
                    url: url,
                    data: function (d) {
                        Object.assign(d, obtenerFiltros());
                    }
                },
                columns: columnas,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Excel' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', text: 'Imprimir' }
                ],
                language: { url: '/i18n/es-ES.json' }
            });
        }

        function recargarTodo() {
            cargarResumen();
            cargarSeries();
            Object.values(tablas).forEach((tabla) => tabla.ajax.reload());
        }

        $(document).ready(function () {
            $('.accounting-select').select2({
                placeholder: 'Todos',
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            aplicarPresetFechas(presetActual);

            tablas.cierres = inicializarTabla('#tablaCierres', "{{ route('contabilidad.dashboard.reportes.cierres') }}", [
                { data: 'periodo' },
                { data: 'ventas_netas', render: (data) => formatoMoneda(data) },
                { data: 'utilidad_estimada', render: (data) => formatoMoneda(data) },
                { data: 'pedidos', render: (data) => formatoNumero(data) },
                { data: 'clientes', render: (data) => formatoNumero(data) },
                { data: 'ticket_promedio', render: (data) => formatoMoneda(data) },
            ]);

            tablas.preventistas = inicializarTabla('#tablaPreventistas', "{{ route('contabilidad.dashboard.reportes.preventistas') }}", [
                { data: 'preventista' },
                { data: 'ventas_netas', render: (data) => formatoMoneda(data) },
                { data: 'utilidad_estimada', render: (data) => formatoMoneda(data) },
                { data: 'pedidos', render: (data) => formatoNumero(data) },
                { data: 'clientes', render: (data) => formatoNumero(data) },
                { data: 'ticket_promedio', render: (data) => formatoMoneda(data) },
                { data: 'margen', render: (data) => `${Number(data || 0).toFixed(2)}%` },
            ]);

            tablas.rutas = inicializarTabla('#tablaRutas', "{{ route('contabilidad.dashboard.reportes.rutas') }}", [
                { data: 'ruta' },
                { data: 'ventas_netas', render: (data) => formatoMoneda(data) },
                { data: 'utilidad_estimada', render: (data) => formatoMoneda(data) },
                { data: 'pedidos', render: (data) => formatoNumero(data) },
                { data: 'clientes', render: (data) => formatoNumero(data) },
                { data: 'ticket_promedio', render: (data) => formatoMoneda(data) },
                { data: 'margen', render: (data) => `${Number(data || 0).toFixed(2)}%` },
            ]);

            tablas.productos = inicializarTabla('#tablaProductos', "{{ route('contabilidad.dashboard.reportes.productos') }}", [
                { data: 'codigo' },
                { data: 'producto' },
                { data: 'marca' },
                { data: 'linea' },
                { data: 'unidades', render: (data) => formatoNumero(data) },
                { data: 'ventas_netas', render: (data) => formatoMoneda(data) },
                { data: 'utilidad_estimada', render: (data) => formatoMoneda(data) },
                { data: 'margen', render: (data) => `${Number(data || 0).toFixed(2)}%` },
            ]);

            recargarTodo();

            $('.preset-btn').on('click', function () {
                $('.preset-btn').removeClass('active');
                $(this).addClass('active');
                presetActual = $(this).data('preset');
                aplicarPresetFechas(presetActual);
            });

            $('#btnAplicarDashboard').on('click', function () {
                if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
                    window.Swal?.fire('Fechas requeridas', 'Selecciona fecha inicio y fecha fin para continuar.', 'warning');
                    return;
                }

                recargarTodo();
            });

            $('#btnLimpiarDashboard').on('click', function () {
                presetActual = 'today';
                $('.preset-btn').removeClass('active');
                $('.preset-btn[data-preset="today"]').addClass('active');
                aplicarPresetFechas(presetActual);
                $('#ruta_id').val(null).trigger('change');
                $('#preventista_id').val(null).trigger('change');
                recargarTodo();
            });
        });
    </script>
@stop
