@extends('adminlte::page')

@section('title', 'Rentabilidad Comparativa')

@section('content_header')
    <section class="profit-hero">
        <div>
            <span class="hero-kicker">Contabilidad / analisis de rentabilidad</span>
            <h1>Rentabilidad comparativa</h1>
            <p>Compara utilidad, margen y volumen de venta por periodo, preventista, ruta y producto para tomar decisiones de compra, precio y enfoque comercial.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-success profit-main-btn" id="btnAplicarAnalisis">
                <i class="fas fa-filter"></i> Actualizar analisis
            </button>
            <button type="button" class="btn btn-outline-secondary profit-main-btn" id="btnLimpiarAnalisis">
                <i class="fas fa-eraser"></i> Limpiar filtros
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="profit-section">
        <div class="preset-row">
            <button type="button" class="preset-btn active" data-preset="today">Hoy</button>
            <button type="button" class="preset-btn" data-preset="week">Semana</button>
            <button type="button" class="preset-btn" data-preset="month">Mes</button>
            <button type="button" class="preset-btn" data-preset="year">Ano</button>
            <button type="button" class="preset-btn" data-preset="custom">Personalizado</button>
        </div>

        <div class="filters-grid">
            <label>
                Fecha inicio
                <input type="date" class="form-control" id="fecha_inicio" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Fecha fin
                <input type="date" class="form-control" id="fecha_fin" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Rutas
                <select id="ruta_id" class="form-control profit-select" multiple>
                    @foreach($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Preventistas
                <select id="preventista_id" class="form-control profit-select" multiple>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="period-summary-grid">
            <article class="period-box">
                <span>Periodo comparado</span>
                <strong id="etiquetaPeriodo">Hoy</strong>
                <small id="etiquetaFiltros">Todas las rutas y todos los preventistas</small>
            </article>
            <article class="period-box">
                <span>Lectura rapida</span>
                <strong id="insightPrincipal">Sin datos</strong>
                <small id="insightSecundario">Aplica filtros para analizar el rendimiento</small>
            </article>
        </div>
    </section>

    <section class="profit-section">
        <div class="kpi-grid">
            <article class="kpi-card">
                <span>Ventas netas</span>
                <strong id="kpiVentasNetas">Bs 0.00</strong>
                <small id="kpiCrecimientoVentas">0.00% vs periodo anterior</small>
            </article>
            <article class="kpi-card">
                <span>Utilidad estimada</span>
                <strong id="kpiUtilidad">Bs 0.00</strong>
                <small id="kpiCrecimientoUtilidad">0.00% vs periodo anterior</small>
            </article>
            <article class="kpi-card">
                <span>Costo estimado</span>
                <strong id="kpiCosto">Bs 0.00</strong>
                <small>Base de costos por lote y producto</small>
            </article>
            <article class="kpi-card">
                <span>Margen general</span>
                <strong id="kpiMargen">0.00%</strong>
                <small id="kpiTicket">Ticket promedio Bs 0.00</small>
            </article>
            <article class="kpi-card">
                <span>Mejor preventista</span>
                <strong id="kpiMejorPreventista">Sin datos</strong>
                <small id="kpiMejorPreventistaTotal">Bs 0.00</small>
            </article>
            <article class="kpi-card">
                <span>Mejor ruta</span>
                <strong id="kpiMejorRuta">Sin datos</strong>
                <small id="kpiMejorRutaTotal">Bs 0.00</small>
            </article>
        </div>
    </section>

    <section class="profit-layout">
        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Comparacion temporal</span>
                    <h2>Ventas y utilidad del periodo</h2>
                </div>
            </div>
            <div class="chart-shell large">
                <canvas id="chartVentasUtilidad"></canvas>
            </div>
        </article>

        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Alertas</span>
                    <h2>Puntos de atencion</h2>
                </div>
            </div>
            <div class="alerts-list" id="alertsList"></div>
        </article>
    </section>

    <section class="profit-layout">
        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Comparacion comercial</span>
                    <h2>Ranking de preventistas</h2>
                </div>
            </div>
            <div class="chart-shell medium">
                <canvas id="chartPreventistas"></canvas>
            </div>
        </article>

        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Comparacion geografica</span>
                    <h2>Distribucion por rutas</h2>
                </div>
            </div>
            <div class="chart-shell medium">
                <canvas id="chartRutas"></canvas>
            </div>
        </article>

        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Productos</span>
                    <h2>Mayor aporte a la utilidad</h2>
                </div>
            </div>
            <div class="chart-shell medium">
                <canvas id="chartProductos"></canvas>
            </div>
        </article>
    </section>

    <section class="profit-section">
        <div class="panel-heading">
            <div>
                <span>Cierres</span>
                <h2>Rentabilidad por periodo</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tablaCierres" class="table table-striped table-bordered profit-table w-100">
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

    <section class="profit-layout">
        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Preventistas</span>
                    <h2>Rentabilidad por preventista</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaPreventistas" class="table table-striped table-bordered profit-table w-100">
                    <thead>
                        <tr>
                            <th>Preventista</th>
                            <th>Ventas netas</th>
                            <th>Utilidad estimada</th>
                            <th>Pedidos</th>
                            <th>Clientes</th>
                            <th>Ticket promedio</th>
                            <th>Margen</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>

        <article class="profit-panel">
            <div class="panel-heading">
                <div>
                    <span>Rutas</span>
                    <h2>Rentabilidad por ruta</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaRutas" class="table table-striped table-bordered profit-table w-100">
                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Ventas netas</th>
                            <th>Utilidad estimada</th>
                            <th>Pedidos</th>
                            <th>Clientes</th>
                            <th>Ticket promedio</th>
                            <th>Margen</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>
    </section>

    <section class="profit-section">
        <div class="panel-heading">
            <div>
                <span>Productos</span>
                <h2>Rentabilidad por producto</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tablaProductos" class="table table-striped table-bordered profit-table w-100">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Linea</th>
                        <th>Unidades</th>
                        <th>Ventas netas</th>
                        <th>Utilidad estimada</th>
                        <th>Margen</th>
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

        .profit-hero,
        .profit-section,
        .profit-panel {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }

        .profit-hero {
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

        .profit-hero h1,
        .panel-heading h2 {
            color: #17211d;
            margin: 0;
            font-weight: 900;
        }

        .profit-hero h1 {
            font-size: 1.85rem;
        }

        .profit-hero p {
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

        .profit-main-btn,
        .preset-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }

        .profit-section {
            padding: 18px;
            margin-bottom: 16px;
        }

        .preset-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .preset-btn {
            background: #f4f7f6;
            border: 1px solid #d7e4df;
            color: #17211d;
            padding: 9px 14px;
        }

        .preset-btn.active {
            background: #166534;
            border-color: #166534;
            color: #ffffff;
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

        .period-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .period-box {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .period-box span {
            color: #64748b;
            display: block;
            font-size: .82rem;
            font-weight: 800;
        }

        .period-box strong {
            color: #17211d;
            display: block;
            font-size: 1.05rem;
            font-weight: 900;
            margin-top: 4px;
        }

        .period-box small {
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

        .profit-layout {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .profit-layout:nth-of-type(5) {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .profit-panel {
            padding: 18px;
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 12px;
        }

        .chart-shell {
            position: relative;
        }

        .chart-shell.large {
            height: 340px;
        }

        .chart-shell.medium {
            height: 300px;
        }

        .alerts-list {
            display: grid;
            gap: 10px;
        }

        .alert-row {
            border-radius: 8px;
            padding: 12px 14px;
            display: grid;
            gap: 4px;
        }

        .alert-row strong {
            font-size: .95rem;
            font-weight: 900;
        }

        .alert-row span {
            font-size: .88rem;
            font-weight: 700;
        }

        .alert-success {
            background: #eefaf1;
            border: 1px solid #b8e0c1;
            color: #166534;
        }

        .alert-warning {
            background: #fff7e8;
            border: 1px solid #f7d08a;
            color: #9a5b00;
        }

        .alert-danger {
            background: #fff0f0;
            border: 1px solid #f1b6b6;
            color: #b42318;
        }

        .alert-info {
            background: #eef7ff;
            border: 1px solid #b9d8ff;
            color: #1d4ed8;
        }

        .alert-secondary {
            background: #f5f7fa;
            border: 1px solid #d7dde7;
            color: #475569;
        }

        .profit-table {
            width: 100% !important;
        }

        .profit-table tbody td {
            vertical-align: middle;
        }

        .dataTables_wrapper .dt-buttons .btn {
            border-radius: 8px;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .filters-grid,
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .profit-layout,
            .profit-layout:nth-of-type(5) {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .profit-hero,
            .hero-actions,
            .panel-heading,
            .filters-grid,
            .period-summary-grid,
            .kpi-grid {
                grid-template-columns: 1fr;
                flex-direction: column;
                align-items: stretch;
            }

            .profit-main-btn,
            .preset-btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            return 'Bs ' + Number(valor || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatoNumero(valor) {
            return Number(valor || 0).toLocaleString('en-US');
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

        function actualizarEtiquetas() {
            $('#etiquetaFiltros').text(`${textoSeleccionados('#ruta_id', 'Todas las rutas')} | ${textoSeleccionados('#preventista_id', 'Todos los preventistas')}`);
        }

        function cargarResumen() {
            $.getJSON("{{ route('contabilidad.dashboard.resumen') }}", obtenerFiltros(), function (response) {
                $('#etiquetaPeriodo').text(response.periodo.etiqueta);
                $('#kpiVentasNetas').text(formatoMoneda(response.kpis.ventas_netas));
                $('#kpiUtilidad').text(formatoMoneda(response.kpis.utilidad_estimada));
                $('#kpiCosto').text(formatoMoneda(response.kpis.costo_estimado));
                $('#kpiMargen').text(`${Number(response.kpis.margen || 0).toFixed(2)}%`);
                $('#kpiTicket').text(`Ticket promedio ${formatoMoneda(response.kpis.ticket_promedio)}`);
                $('#kpiCrecimientoVentas').text(`${Number(response.kpis.crecimiento_ventas || 0).toFixed(2)}% vs periodo anterior`);
                $('#kpiCrecimientoUtilidad').text(`${Number(response.kpis.crecimiento_utilidad || 0).toFixed(2)}% vs periodo anterior`);
                $('#kpiMejorPreventista').text(response.kpis.mejor_preventista.nombre || 'Sin datos');
                $('#kpiMejorPreventistaTotal').text(formatoMoneda(response.kpis.mejor_preventista.total || 0));
                $('#kpiMejorRuta').text(response.kpis.mejor_ruta.nombre || 'Sin datos');
                $('#kpiMejorRutaTotal').text(formatoMoneda(response.kpis.mejor_ruta.total || 0));

                const margen = Number(response.kpis.margen || 0);
                const crecimiento = Number(response.kpis.crecimiento_utilidad || 0);
                let insightPrincipal = 'Rentabilidad estable';
                let insightSecundario = 'Revisa la comparacion para identificar oportunidades';

                if (margen >= 25 && crecimiento >= 0) {
                    insightPrincipal = 'Rentabilidad fuerte';
                    insightSecundario = 'El margen y la utilidad vienen respondiendo bien en el periodo';
                } else if (margen < 15 && crecimiento < 0) {
                    insightPrincipal = 'Rentabilidad en alerta';
                    insightSecundario = 'La utilidad esta cediendo y conviene revisar costos, rutas y productos';
                } else if (margen < 15) {
                    insightPrincipal = 'Margen ajustado';
                    insightSecundario = 'Las ventas existen, pero el margen esta bajo para el periodo elegido';
                }

                $('#insightPrincipal').text(insightPrincipal);
                $('#insightSecundario').text(insightSecundario);

                const alertas = (response.alertas || []).map((alerta) => `
                    <div class="alert-row alert-${alerta.tipo}">
                        <strong>${alerta.titulo}</strong>
                        <span>${alerta.detalle}</span>
                    </div>
                `).join('');

                $('#alertsList').html(alertas || '<div class="alert-row alert-secondary"><strong>Sin alertas</strong><span>No hay observaciones relevantes para este filtro.</span></div>');
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
            actualizarEtiquetas();
            cargarResumen();
            cargarSeries();
            Object.values(tablas).forEach((tabla) => tabla.ajax.reload());
        }

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('.profit-select').select2({
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

            $('.preset-btn').on('click', function () {
                $('.preset-btn').removeClass('active');
                $(this).addClass('active');
                presetActual = $(this).data('preset');
                aplicarPresetFechas(presetActual);
            });

            $('#btnAplicarAnalisis').on('click', function () {
                const filtros = obtenerFiltros();

                if (!filtros.fecha_inicio || !filtros.fecha_fin) {
                    Swal.fire('Fechas requeridas', 'Selecciona la fecha de inicio y la fecha final.', 'warning');
                    return;
                }

                if (filtros.fecha_inicio > filtros.fecha_fin) {
                    Swal.fire('Rango invalido', 'La fecha de inicio no puede ser mayor que la fecha final.', 'warning');
                    return;
                }

                recargarTodo();
            });

            $('#btnLimpiarAnalisis').on('click', function () {
                presetActual = 'today';
                $('.preset-btn').removeClass('active');
                $('.preset-btn[data-preset="today"]').addClass('active');
                $('#ruta_id').val(null).trigger('change');
                $('#preventista_id').val(null).trigger('change');
                aplicarPresetFechas(presetActual);
                recargarTodo();
            });

            $('#tablaCierres, #tablaPreventistas, #tablaRutas, #tablaProductos').on('error.dt', function (e, settings, techNote, message) {
                console.error('DataTables:', message);
                Swal.fire('Error al cargar el analisis', 'No se pudieron consultar los datos del reporte. Recarga la pagina y vuelve a intentar.', 'error');
            });

            recargarTodo();
        });
    </script>
@stop
