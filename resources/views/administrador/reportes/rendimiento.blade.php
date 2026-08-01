@extends('adminlte::page')

@section('title', 'Rendimiento del personal')

@section('content_header')
    <section class="performance-hero">
        <div>
            <span class="performance-kicker">RR.HH. · Inteligencia comercial</span>
            <h1>Panel de rendimiento de preventistas</h1>
            <p>Supervisa ventas, pedidos, cobertura, conversion y productividad operativa desde una sola vista ejecutiva.</p>
        </div>
        <div class="performance-hero__badge">
            <span>Vista consolidada</span>
            <strong>{{ now()->format('d/m/Y') }}</strong>
        </div>
    </section>
@stop

@section('content')
    <div class="performance-dashboard">
        <section class="filter-panel">
            <div class="filter-panel__title">
                <h2>Filtros del reporte visual</h2>
                <p>Analiza el comportamiento global o enfoca la vista por preventista y ruta.</p>
            </div>

            <div class="filter-grid">
                <div class="filter-field">
                    <label for="fecha_inicio">Fecha inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="filter-field">
                    <label for="fecha_fin">Fecha fin</label>
                    <input type="date" id="fecha_fin" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="filter-field">
                    <label for="periodo_dashboard">Granularidad</label>
                    <select id="periodo_dashboard" class="form-control">
                        <option value="dia">Por dias</option>
                        <option value="semana">Por semanas</option>
                        <option value="mes">Por meses</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label for="preventista_id">Preventista</label>
                    <select id="preventista_id" class="form-control">
                        <option value="">Todos los preventistas</option>
                        @foreach($personal as $p)
                            <option value="{{ $p->id }}">{{ trim($p->nombres.' '.$p->apellido_paterno.' '.$p->apellido_materno) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="ruta_id">Ruta</label>
                    <select id="ruta_id" class="form-control">
                        <option value="">Todas las rutas</option>
                        @foreach($rutas as $ruta)
                            <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-primary btn-dashboard-action" id="btnAplicarFiltro">
                        <i class="fas fa-chart-line"></i> Actualizar panel
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-dashboard-action" id="btnLimpiarFiltro">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                </div>
            </div>
        </section>

        <section class="kpi-grid" id="kpiGrid">
            <article class="kpi-card accent-emerald">
                <span class="kpi-card__label">Ventas netas</span>
                <strong id="kpiVentasNetas">Bs 0.00</strong>
                <small id="kpiTicketPromedio">Ticket promedio: Bs 0.00</small>
            </article>
            <article class="kpi-card accent-cobalt">
                <span class="kpi-card__label">Pedidos contabilizados</span>
                <strong id="kpiPedidos">0</strong>
                <small id="kpiUnidades">Unidades vendidas: 0</small>
            </article>
            <article class="kpi-card accent-amber">
                <span class="kpi-card__label">Cobertura de clientes</span>
                <strong id="kpiCobertura">0%</strong>
                <small id="kpiClientes">0 atendidos de 0 asignados</small>
            </article>
            <article class="kpi-card accent-rose">
                <span class="kpi-card__label">Conversion de pedido</span>
                <strong id="kpiConversion">0%</strong>
                <small id="kpiPendientes">Pendientes: 0</small>
            </article>
            <article class="kpi-card accent-slate">
                <span class="kpi-card__label">Preventistas activos</span>
                <strong id="kpiPreventistas">0</strong>
                <small id="kpiPromedioPreventista">Promedio venta/preventista: Bs 0.00</small>
            </article>
            <article class="kpi-card accent-violet">
                <span class="kpi-card__label">Rutas cubiertas</span>
                <strong id="kpiRutas">0</strong>
                <small id="kpiClientesVenta">Clientes con venta: 0</small>
            </article>
        </section>

        <section class="panel-grid">
            <article class="panel-card panel-card--main">
                <div class="panel-card__header">
                    <div>
                        <span class="panel-card__eyebrow">Tendencia operativa</span>
                        <h3>Ventas, pedidos y atenciones</h3>
                    </div>
                </div>
                <div id="performanceTrendChart" class="chart-surface"></div>
            </article>

            <article class="panel-card panel-card--profile">
                <div class="panel-card__header">
                    <div>
                        <span class="panel-card__eyebrow">Preventista destacado</span>
                        <h3>Ficha visual de rendimiento</h3>
                    </div>
                </div>
                <div id="featuredPreventista" class="featured-profile">
                    <div class="featured-profile__empty">Selecciona un periodo para cargar la ficha destacada.</div>
                </div>
            </article>
        </section>

        <section class="panel-tabs">
            <div class="panel-tabs__header">
                <button type="button" class="panel-tab is-active" data-tab="tab-ranking">Ranking</button>
                <button type="button" class="panel-tab" data-tab="tab-rutas">Rutas</button>
                <button type="button" class="panel-tab" data-tab="tab-alertas">Alertas RR.HH.</button>
                <button type="button" class="panel-tab" data-tab="tab-fotos">Reporte visual</button>
            </div>

            <div class="panel-tab-content is-active" id="tab-ranking">
                <div class="table-responsive">
                    <table class="table performance-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Preventista</th>
                                <th>Ventas</th>
                                <th>Pedidos</th>
                                <th>Ticket prom.</th>
                                <th>Atendidos</th>
                                <th>Conversion</th>
                                <th>Pendientes</th>
                            </tr>
                        </thead>
                        <tbody id="rankingTableBody">
                            <tr>
                                <td colspan="8" class="table-empty">Cargando ranking...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-tab-content" id="tab-rutas">
                <div class="panel-grid panel-grid--compact">
                    <article class="panel-card">
                        <div class="panel-card__header">
                            <div>
                                <span class="panel-card__eyebrow">Cobertura por ruta</span>
                                <h3>Mapa comercial de rutas</h3>
                            </div>
                        </div>
                        <div id="routesChart" class="chart-surface chart-surface--small"></div>
                    </article>
                    <article class="panel-card">
                        <div class="table-responsive">
                            <table class="table performance-table">
                                <thead>
                                    <tr>
                                        <th>Ruta</th>
                                        <th>Asignados</th>
                                        <th>Atendidos</th>
                                        <th>Con pedido</th>
                                        <th>Efectividad</th>
                                    </tr>
                                </thead>
                                <tbody id="routesTableBody">
                                    <tr>
                                        <td colspan="5" class="table-empty">Cargando rutas...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </div>

            <div class="panel-tab-content" id="tab-alertas">
                <div id="alertsList" class="alerts-grid">
                    <article class="alert-card alert-card--neutral">
                        <h4>Analizando datos</h4>
                        <p>El panel está generando observaciones para RR.HH.</p>
                    </article>
                </div>
            </div>

            <div class="panel-tab-content" id="tab-fotos">
                <div id="photoCards" class="photo-report-grid">
                    <div class="photo-report-empty">Cargando reporte visual...</div>
                </div>
            </div>
        </section>
    </div>
@stop

@section('css')
    <style>
        :root {
            --perf-bg: #f4f7fb;
            --perf-card: #ffffff;
            --perf-line: #dbe3ef;
            --perf-text: #16324f;
            --perf-muted: #6e7f94;
            --perf-shadow: 0 18px 40px rgba(19, 42, 67, 0.10);
            --perf-radius: 24px;
        }

        .content-wrapper,
        .content,
        .content-header {
            background: linear-gradient(180deg, #eef3f9 0%, #f8fbfd 48%, #f3f6fb 100%);
        }

        .performance-hero {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 28px 30px;
            border-radius: 28px;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.14), transparent 32%),
                linear-gradient(135deg, #0f2942 0%, #155e75 55%, #0f766e 100%);
            box-shadow: 0 24px 48px rgba(10, 35, 64, 0.24);
        }

        .performance-kicker {
            display: inline-flex;
            margin-bottom: 10px;
            font-size: 0.82rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.8;
        }

        .performance-hero h1 {
            margin: 0 0 10px;
            font-size: 2rem;
            font-weight: 800;
        }

        .performance-hero p {
            margin: 0;
            max-width: 700px;
            color: rgba(255, 255, 255, 0.84);
        }

        .performance-hero__badge {
            min-width: 210px;
            padding: 18px 20px;
            border-radius: 22px;
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
        }

        .performance-hero__badge span {
            display: block;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.78);
        }

        .performance-hero__badge strong {
            display: block;
            margin-top: 8px;
            font-size: 1.3rem;
        }

        .performance-dashboard {
            display: grid;
            gap: 24px;
            padding-bottom: 24px;
        }

        .filter-panel,
        .panel-card,
        .panel-tabs {
            background: var(--perf-card);
            border: 1px solid rgba(188, 204, 220, 0.55);
            border-radius: var(--perf-radius);
            box-shadow: var(--perf-shadow);
        }

        .filter-panel {
            padding: 24px;
        }

        .filter-panel__title h2,
        .panel-card__header h3 {
            margin: 0;
            color: var(--perf-text);
            font-weight: 700;
        }

        .filter-panel__title p,
        .panel-card__eyebrow,
        .table-empty,
        .featured-profile__meta,
        .featured-profile__metrics span,
        .alert-card p,
        .photo-card__meta {
            color: var(--perf-muted);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .filter-field label {
            display: block;
            margin-bottom: 8px;
            color: var(--perf-text);
            font-weight: 600;
            font-size: 0.92rem;
        }

        .filter-field .form-control {
            height: 46px;
            border-radius: 14px;
            border-color: #d5deea;
            box-shadow: none;
        }

        .filter-field .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 0.18rem rgba(15, 118, 110, 0.14);
        }

        .filter-actions {
            display: flex;
            align-items: end;
            gap: 12px;
            grid-column: span 1;
        }

        .btn-dashboard-action {
            height: 46px;
            border-radius: 14px;
            font-weight: 700;
            min-width: 150px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
        }

        .kpi-card {
            position: relative;
            overflow: hidden;
            min-height: 136px;
            padding: 20px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(192, 205, 220, 0.5);
            box-shadow: var(--perf-shadow);
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            inset: auto -24px -38px auto;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            opacity: 0.14;
        }

        .kpi-card__label {
            display: block;
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--perf-muted);
        }

        .kpi-card strong {
            display: block;
            margin-top: 12px;
            font-size: 1.85rem;
            color: var(--perf-text);
            line-height: 1.08;
        }

        .kpi-card small {
            display: block;
            margin-top: 10px;
            font-size: 0.88rem;
            color: #536579;
        }

        .accent-emerald::after { background: #0f766e; }
        .accent-cobalt::after { background: #2563eb; }
        .accent-amber::after { background: #d97706; }
        .accent-rose::after { background: #dc2626; }
        .accent-slate::after { background: #334155; }
        .accent-violet::after { background: #7c3aed; }

        .panel-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.9fr);
            gap: 20px;
        }

        .panel-grid--compact {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .panel-card {
            padding: 22px;
        }

        .panel-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .panel-card__eyebrow {
            display: block;
            margin-bottom: 6px;
            font-size: 0.78rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .chart-surface {
            width: 100%;
            min-height: 420px;
        }

        .chart-surface--small {
            min-height: 360px;
        }

        .featured-profile {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-height: 420px;
        }

        .featured-profile__empty,
        .photo-report-empty {
            display: grid;
            place-items: center;
            min-height: 260px;
            text-align: center;
            color: var(--perf-muted);
            border: 1px dashed #cdd8e6;
            border-radius: 22px;
            background: linear-gradient(180deg, #fbfdff 0%, #f4f7fb 100%);
        }

        .featured-profile__head {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .featured-profile__avatar {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            object-fit: cover;
            border: 4px solid rgba(21, 94, 117, 0.12);
        }

        .featured-profile__head h4 {
            margin: 0;
            color: var(--perf-text);
            font-size: 1.2rem;
            font-weight: 800;
        }

        .featured-profile__meta {
            margin-top: 6px;
            font-size: 0.92rem;
        }

        .featured-profile__badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .featured-profile__summary {
            padding: 16px 18px;
            border-radius: 22px;
            background: linear-gradient(135deg, #f8fbff 0%, #edf7f8 100%);
            border: 1px solid #dce8ef;
            color: #294559;
        }

        .featured-profile__metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .featured-profile__metric {
            padding: 14px;
            border-radius: 18px;
            background: #f7fafc;
            border: 1px solid #e0e8f0;
        }

        .featured-profile__metrics span {
            display: block;
            font-size: 0.82rem;
        }

        .featured-profile__metrics strong {
            display: block;
            margin-top: 8px;
            color: var(--perf-text);
            font-size: 1.1rem;
        }

        .panel-tabs {
            padding: 18px;
        }

        .panel-tabs__header {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .panel-tab {
            padding: 11px 18px;
            border: 1px solid #d8e2ec;
            border-radius: 999px;
            background: #f6f9fc;
            color: var(--perf-text);
            font-weight: 700;
        }

        .panel-tab.is-active {
            background: linear-gradient(135deg, #155e75 0%, #0f766e 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 14px 28px rgba(15, 118, 110, 0.22);
        }

        .panel-tab-content {
            display: none;
        }

        .panel-tab-content.is-active {
            display: block;
        }

        .performance-table {
            margin-bottom: 0;
        }

        .performance-table thead th {
            border-top: 0;
            border-bottom: 1px solid #dde6f1;
            color: #587089;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .performance-table tbody td {
            vertical-align: middle;
            border-color: #edf2f7;
            color: var(--perf-text);
        }

        .table-empty {
            text-align: center;
            padding: 28px 12px !important;
        }

        .person-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 240px;
        }

        .person-cell img {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            object-fit: cover;
            border: 2px solid #dde8f2;
        }

        .person-cell strong {
            display: block;
        }

        .person-cell span {
            display: block;
            color: var(--perf-muted);
            font-size: 0.84rem;
        }

        .metric-pill {
            display: inline-flex;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .metric-pill--good {
            background: #dcfce7;
            color: #166534;
        }

        .metric-pill--mid {
            background: #fef3c7;
            color: #92400e;
        }

        .metric-pill--risk {
            background: #fee2e2;
            color: #991b1b;
        }

        .alerts-grid,
        .photo-report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .alert-card,
        .photo-card {
            padding: 20px;
            border-radius: 22px;
            border: 1px solid #dde6f1;
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
        }

        .alert-card h4,
        .photo-card h4 {
            margin: 0 0 8px;
            color: var(--perf-text);
            font-weight: 800;
        }

        .alert-card--positivo { border-left: 5px solid #0f766e; }
        .alert-card--atencion { border-left: 5px solid #d97706; }
        .alert-card--critico { border-left: 5px solid #dc2626; }
        .alert-card--neutral { border-left: 5px solid #64748b; }

        .photo-card {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .photo-card img {
            width: 84px;
            height: 84px;
            border-radius: 24px;
            object-fit: cover;
            border: 3px solid #e4edf6;
        }

        .photo-card__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
            margin-top: 12px;
        }

        .photo-card__stats div {
            padding: 10px 12px;
            border-radius: 16px;
            background: #f8fbfd;
            border: 1px solid #e4ebf4;
        }

        .photo-card__stats span {
            display: block;
            font-size: 0.8rem;
            color: var(--perf-muted);
        }

        .photo-card__stats strong {
            display: block;
            margin-top: 5px;
            color: var(--perf-text);
        }

        @media (max-width: 1400px) {
            .filter-grid,
            .kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1100px) {
            .panel-grid,
            .panel-grid--compact,
            .alerts-grid,
            .photo-report-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .performance-hero {
                flex-direction: column;
                padding: 22px;
            }

            .filter-grid,
            .kpi-grid,
            .featured-profile__metrics {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                grid-column: auto;
                flex-direction: column;
                align-items: stretch;
            }

            .panel-tabs__header {
                flex-direction: column;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>

    <script>
        const trendChart = echarts.init(document.getElementById('performanceTrendChart'));
        const routesChart = echarts.init(document.getElementById('routesChart'));

        function formatoMoneda(valor) {
            return `Bs ${Number(valor || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function formatoNumero(valor) {
            return Number(valor || 0).toLocaleString('es-BO');
        }

        function obtenerClasePill(valor) {
            if (Number(valor) >= 75) return 'metric-pill metric-pill--good';
            if (Number(valor) >= 45) return 'metric-pill metric-pill--mid';
            return 'metric-pill metric-pill--risk';
        }

        function actualizarKPIs(resumen) {
            $('#kpiVentasNetas').text(formatoMoneda(resumen.ventas_netas));
            $('#kpiTicketPromedio').text(`Ticket promedio: ${formatoMoneda(resumen.ticket_promedio)}`);
            $('#kpiPedidos').text(formatoNumero(resumen.pedidos_contabilizados));
            $('#kpiUnidades').text(`Unidades vendidas: ${formatoNumero(resumen.unidades_vendidas)}`);
            $('#kpiCobertura').text(`${resumen.efectividad_atencion}%`);
            $('#kpiClientes').text(`${formatoNumero(resumen.clientes_atendidos)} atendidos de ${formatoNumero(resumen.clientes_asignados)} asignados`);
            $('#kpiConversion').text(`${resumen.conversion_pedido}%`);
            $('#kpiPendientes').text(`Pendientes: ${formatoNumero(resumen.clientes_pendientes)}`);
            $('#kpiPreventistas').text(`${formatoNumero(resumen.preventistas_activos)} / ${formatoNumero(resumen.total_preventistas)}`);
            $('#kpiPromedioPreventista').text(`Promedio venta/preventista: ${formatoMoneda(resumen.venta_promedio_por_preventista)}`);
            $('#kpiRutas').text(formatoNumero(resumen.rutas_cubiertas));
            $('#kpiClientesVenta').text(`Clientes con venta: ${formatoNumero(resumen.clientes_con_venta)}`);
        }

        function renderTrendChart(series) {
            trendChart.setOption({
                tooltip: { trigger: 'axis' },
                legend: {
                    top: 8,
                    textStyle: { color: '#49627d' }
                },
                grid: {
                    left: 30,
                    right: 20,
                    top: 60,
                    bottom: 30,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: series.categorias || [],
                    axisLine: { lineStyle: { color: '#c7d3df' } },
                    axisLabel: { color: '#60758c' }
                },
                yAxis: [
                    {
                        type: 'value',
                        name: 'Ventas',
                        axisLabel: { color: '#60758c' },
                        splitLine: { lineStyle: { color: '#edf2f7' } }
                    },
                    {
                        type: 'value',
                        name: 'Operaciones',
                        axisLabel: { color: '#60758c' },
                        splitLine: { show: false }
                    }
                ],
                series: [
                    {
                        name: 'Ventas netas',
                        type: 'bar',
                        data: series.ventas || [],
                        itemStyle: { color: '#0f766e', borderRadius: [8, 8, 0, 0] }
                    },
                    {
                        name: 'Pedidos',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        data: series.pedidos || [],
                        lineStyle: { color: '#2563eb', width: 3 },
                        itemStyle: { color: '#2563eb' }
                    },
                    {
                        name: 'Atendidos',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        data: series.atendidos || [],
                        lineStyle: { color: '#d97706', width: 3 },
                        itemStyle: { color: '#d97706' }
                    },
                    {
                        name: 'Con pedido',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        data: series.con_pedido || [],
                        lineStyle: { color: '#7c3aed', width: 3 },
                        itemStyle: { color: '#7c3aed' }
                    }
                ]
            });
        }

        function renderFeaturedPreventista(preventista) {
            if (!preventista) {
                $('#featuredPreventista').html('<div class="featured-profile__empty">No hay preventistas con datos para el filtro actual.</div>');
                return;
            }

            $('#featuredPreventista').html(`
                <div class="featured-profile__head">
                    <img src="${preventista.foto_url}" alt="${preventista.nombre}" class="featured-profile__avatar">
                    <div>
                        <span class="featured-profile__badge">Top #${preventista.posicion}</span>
                        <h4>${preventista.nombre}</h4>
                        <div class="featured-profile__meta">C.I. ${preventista.cedula} · ${preventista.rutas_cubiertas} rutas cubiertas</div>
                    </div>
                </div>
                <div class="featured-profile__summary">
                    ${preventista.nombre} registra ${formatoMoneda(preventista.ventas_netas)} en ventas netas, ${formatoNumero(preventista.pedidos)} pedidos contabilizados y una conversion de ${preventista.conversion_pedido}%.
                </div>
                <div class="featured-profile__metrics">
                    <div class="featured-profile__metric">
                        <span>Ventas netas</span>
                        <strong>${formatoMoneda(preventista.ventas_netas)}</strong>
                    </div>
                    <div class="featured-profile__metric">
                        <span>Ticket promedio</span>
                        <strong>${formatoMoneda(preventista.ticket_promedio)}</strong>
                    </div>
                    <div class="featured-profile__metric">
                        <span>Clientes atendidos</span>
                        <strong>${formatoNumero(preventista.clientes_atendidos)} / ${formatoNumero(preventista.clientes_asignados)}</strong>
                    </div>
                    <div class="featured-profile__metric">
                        <span>Conversion</span>
                        <strong>${preventista.conversion_pedido}%</strong>
                    </div>
                    <div class="featured-profile__metric">
                        <span>Unidades vendidas</span>
                        <strong>${formatoNumero(preventista.unidades_vendidas)}</strong>
                    </div>
                    <div class="featured-profile__metric">
                        <span>Clientes pendientes</span>
                        <strong>${formatoNumero(preventista.clientes_pendientes)}</strong>
                    </div>
                </div>
            `);
        }

        function renderRanking(ranking) {
            if (!ranking.length) {
                $('#rankingTableBody').html('<tr><td colspan="8" class="table-empty">No se encontraron preventistas para el filtro actual.</td></tr>');
                return;
            }

            const filas = ranking.map(item => `
                <tr>
                    <td><strong>#${item.posicion}</strong></td>
                    <td>
                        <div class="person-cell">
                            <img src="${item.foto_url}" alt="${item.nombre}">
                            <div>
                                <strong>${item.nombre}</strong>
                                <span>C.I. ${item.cedula}</span>
                            </div>
                        </div>
                    </td>
                    <td>${formatoMoneda(item.ventas_netas)}</td>
                    <td>${formatoNumero(item.pedidos)}</td>
                    <td>${formatoMoneda(item.ticket_promedio)}</td>
                    <td>${formatoNumero(item.clientes_atendidos)} / ${formatoNumero(item.clientes_asignados)}</td>
                    <td><span class="${obtenerClasePill(item.conversion_pedido)}">${item.conversion_pedido}%</span></td>
                    <td>${formatoNumero(item.clientes_pendientes)}</td>
                </tr>
            `).join('');

            $('#rankingTableBody').html(filas);
        }

        function renderRoutes(rutas) {
            if (!rutas.length) {
                $('#routesTableBody').html('<tr><td colspan="5" class="table-empty">No hay rutas con actividad en este periodo.</td></tr>');
                routesChart.clear();
                return;
            }

            const filas = rutas.map(item => `
                <tr>
                    <td>${item.ruta}</td>
                    <td>${formatoNumero(item.asignados)}</td>
                    <td>${formatoNumero(item.atendidos)}</td>
                    <td>${formatoNumero(item.con_pedido)}</td>
                    <td><span class="${obtenerClasePill(item.efectividad)}">${item.efectividad}%</span></td>
                </tr>
            `).join('');

            $('#routesTableBody').html(filas);

            routesChart.setOption({
                tooltip: { trigger: 'axis' },
                grid: {
                    left: 20,
                    right: 20,
                    top: 20,
                    bottom: 20,
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: { color: '#60758c' },
                    splitLine: { lineStyle: { color: '#edf2f7' } }
                },
                yAxis: {
                    type: 'category',
                    data: rutas.map(item => item.ruta),
                    axisLabel: { color: '#60758c' }
                },
                series: [
                    {
                        name: 'Con pedido',
                        type: 'bar',
                        data: rutas.map(item => item.con_pedido),
                        itemStyle: { color: '#155e75', borderRadius: [0, 10, 10, 0] }
                    }
                ]
            });
        }

        function renderAlerts(alertas) {
            if (!alertas.length) {
                $('#alertsList').html('<article class="alert-card alert-card--neutral"><h4>Sin observaciones</h4><p>No se detectaron alertas para el periodo actual.</p></article>');
                return;
            }

            const html = alertas.map(alerta => `
                <article class="alert-card alert-card--${alerta.nivel || 'neutral'}">
                    <h4>${alerta.titulo}</h4>
                    <p>${alerta.descripcion}</p>
                </article>
            `).join('');

            $('#alertsList').html(html);
        }

        function renderPhotoCards(ranking) {
            if (!ranking.length) {
                $('#photoCards').html('<div class="photo-report-empty">No existen registros visuales para mostrar en este filtro.</div>');
                return;
            }

            const top = ranking.slice(0, 6);
            const html = top.map(item => `
                <article class="photo-card">
                    <img src="${item.foto_url}" alt="${item.nombre}">
                    <div class="photo-card__content">
                        <h4>${item.nombre}</h4>
                        <div class="photo-card__meta">Posicion #${item.posicion} · ${item.rutas_cubiertas} rutas cubiertas</div>
                        <div class="photo-card__stats">
                            <div>
                                <span>Ventas netas</span>
                                <strong>${formatoMoneda(item.ventas_netas)}</strong>
                            </div>
                            <div>
                                <span>Pedidos</span>
                                <strong>${formatoNumero(item.pedidos)}</strong>
                            </div>
                            <div>
                                <span>Efectividad</span>
                                <strong>${item.efectividad_atencion}%</strong>
                            </div>
                            <div>
                                <span>Conversion</span>
                                <strong>${item.conversion_pedido}%</strong>
                            </div>
                        </div>
                    </div>
                </article>
            `).join('');

            $('#photoCards').html(html);
        }

        function cargarPanel() {
            Swal.fire({
                title: 'Actualizando panel',
                text: 'Estamos procesando las metricas del reporte visual.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('rendimientopersonal.panelData') }}",
                type: 'GET',
                data: {
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    periodo: $('#periodo_dashboard').val(),
                    preventista_id: $('#preventista_id').val(),
                    ruta_id: $('#ruta_id').val()
                },
                success: function(response) {
                    Swal.close();
                    actualizarKPIs(response.resumen || {});
                    renderTrendChart(response.series || {});
                    renderFeaturedPreventista(response.preventista_destacado || null);
                    renderRanking(response.ranking || []);
                    renderRoutes(response.rutas || []);
                    renderAlerts(response.alertas || []);
                    renderPhotoCards(response.ranking || []);
                },
                error: function(xhr) {
                    Swal.close();
                    const mensaje = xhr.responseJSON?.message || 'No fue posible generar el panel solicitado.';
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        $(document).ready(function () {
            cargarPanel();

            $('#btnAplicarFiltro').on('click', cargarPanel);

            $('#btnLimpiarFiltro').on('click', function () {
                $('#fecha_inicio').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
                $('#fecha_fin').val("{{ now()->format('Y-m-d') }}");
                $('#periodo_dashboard').val('dia');
                $('#preventista_id').val('');
                $('#ruta_id').val('');
                cargarPanel();
            });

            $(document).on('click', '.panel-tab', function () {
                const tab = $(this).data('tab');
                $('.panel-tab').removeClass('is-active');
                $(this).addClass('is-active');
                $('.panel-tab-content').removeClass('is-active');
                $('#' + tab).addClass('is-active');
                trendChart.resize();
                routesChart.resize();
            });

            $(window).on('resize', function () {
                trendChart.resize();
                routesChart.resize();
            });
        });
    </script>
@stop
