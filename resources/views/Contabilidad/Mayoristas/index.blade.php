@extends('adminlte::page')

@section('title', 'Mayoristas')

@section('content_header')
    <section class="wh-accounting-hero">
        <div>
            <span>Contabilidad / mayoristas</span>
            <h1>Analisis de ventas mayoristas</h1>
            <p>Visualiza la facturacion mayorista sin mezclarla con ventas comunes. Revisa rendimiento, clientes, productos y tendencia del periodo.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-success accounting-btn" id="btnAplicarMayoristasConta">
                <i class="fas fa-filter"></i> Actualizar
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="wh-accounting-filters">
        <div class="filter-grid">
            <label>Desde
                <input type="date" class="form-control" id="fechaInicioContaMayorista" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </label>
            <label>Hasta
                <input type="date" class="form-control" id="fechaFinContaMayorista" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>Rutas
                <select class="form-control" id="rutaContaMayorista" multiple>
                    @foreach ($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>Mayoristas
                <select class="form-control" id="usuarioContaMayorista" multiple>
                    @foreach ($mayoristas as $mayorista)
                        <option value="{{ $mayorista->id }}">{{ trim($mayorista->nombres.' '.$mayorista->apellido_paterno.' '.$mayorista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="wh-kpis">
        <article class="kpi-card"><span>Facturacion</span><strong id="kpiFacturacionMayorista">Bs 0.00</strong></article>
        <article class="kpi-card"><span>Ventas</span><strong id="kpiVentasMayoristaConta">0</strong></article>
        <article class="kpi-card"><span>Clientes</span><strong id="kpiClientesMayoristaConta">0</strong></article>
        <article class="kpi-card"><span>Productos</span><strong id="kpiProductosMayoristaConta">0</strong></article>
        <article class="kpi-card"><span>Ticket promedio</span><strong id="kpiTicketMayoristaConta">Bs 0.00</strong></article>
    </section>

    <section class="wh-kpis secondary">
        <article class="kpi-card"><span>Unidades</span><strong id="kpiUnidadesMayoristaConta">0</strong></article>
        <article class="kpi-card"><span>Mayoristas activos</span><strong id="kpiMayoristasActivos">0</strong></article>
        <article class="kpi-card"><span>Top mayorista</span><strong id="kpiTopMayorista">Sin datos</strong><small id="kpiTopMayoristaTotal">Bs 0.00</small></article>
        <article class="kpi-card"><span>Top producto</span><strong id="kpiTopProducto">Sin datos</strong><small id="kpiTopProductoTotal">Bs 0.00</small></article>
    </section>

    <section class="wh-grid">
        <article class="wh-panel">
            <div class="panel-heading"><span>Tendencia</span><h2>Facturacion por fecha</h2></div>
            <div class="chart-wrap"><canvas id="graficoMayoristasLinea"></canvas></div>
        </article>
        <article class="wh-panel">
            <div class="panel-heading"><span>Ranking</span><h2>Mayoristas con mayor facturacion</h2></div>
            <div class="chart-wrap"><canvas id="graficoMayoristasBarras"></canvas></div>
        </article>
    </section>

    <section class="wh-grid">
        <article class="wh-panel">
            <div class="panel-heading"><span>Ventas</span><h2>Detalle por venta</h2></div>
            <div class="table-responsive">
                <table id="tablaContaVentasMayoristas" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Nro.</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Ruta</th>
                            <th>Mayorista</th>
                            <th>Items</th>
                            <th>Unidades</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>
        <article class="wh-panel">
            <div class="panel-heading"><span>Productos</span><h2>Rendimiento por producto</h2></div>
            <div class="table-responsive">
                <table id="tablaContaProductosMayoristas" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Ventas</th>
                            <th>Present.</th>
                            <th>Unidades</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .content-wrapper{background:#eef3f1}
        .wh-accounting-hero,.wh-accounting-filters,.wh-panel,.kpi-card{background:#fff;border:1px solid #d8e4de;border-radius:8px}
        .wh-accounting-hero,.wh-accounting-filters,.wh-panel{padding:18px;margin-bottom:16px}
        .wh-accounting-hero{display:flex;justify-content:space-between;gap:16px}
        .wh-accounting-hero span,.panel-heading span{display:block;color:#15803d;font-size:.78rem;font-weight:900;text-transform:uppercase}
        .wh-accounting-hero h1,.panel-heading h2{margin:0;color:#17211d;font-weight:900}
        .wh-accounting-hero p{margin:6px 0 0;color:#64748b;font-weight:700}
        .accounting-btn{min-height:42px;border-radius:8px;font-weight:900}
        .filter-grid,.wh-kpis,.wh-grid{display:grid;gap:12px}
        .filter-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
        .wh-kpis{grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:16px}
        .wh-kpis.secondary{grid-template-columns:repeat(4,minmax(0,1fr))}
        .kpi-card{padding:14px}
        .kpi-card span,.kpi-card small{display:block;color:#64748b;font-weight:800}
        .kpi-card strong{display:block;margin-top:6px;color:#17211d;font-size:1.2rem;font-weight:900}
        .wh-grid{grid-template-columns:1fr 1fr}
        .chart-wrap{position:relative;height:320px}
        .panel-heading{margin-bottom:12px}
        .select2-container--default .select2-selection--multiple{border:1px solid #ced4da;border-radius:8px;min-height:38px}
        @media (max-width:1199.98px){.filter-grid,.wh-kpis,.wh-kpis.secondary,.wh-grid{grid-template-columns:1fr}}
        @media (max-width:767.98px){.wh-accounting-hero{flex-direction:column}.accounting-btn{width:100%}}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let tablaVentas = null;
        let tablaProductos = null;
        let graficoLinea = null;
        let graficoBarras = null;

        function money(value) {
            return 'Bs ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function filtrosMayoristas() {
            return {
                fecha_inicio: $('#fechaInicioContaMayorista').val(),
                fecha_fin: $('#fechaFinContaMayorista').val(),
                ruta_id: $('#rutaContaMayorista').val() || [],
                mayorista_id: $('#usuarioContaMayorista').val() || []
            };
        }

        function cargarResumen() {
            $.getJSON("{{ route('contabilidad.mayoristas.resumen') }}", filtrosMayoristas(), function (response) {
                $('#kpiFacturacionMayorista').text(money(response.total));
                $('#kpiVentasMayoristaConta').text(response.ventas);
                $('#kpiClientesMayoristaConta').text(response.clientes);
                $('#kpiProductosMayoristaConta').text(response.productos);
                $('#kpiTicketMayoristaConta').text(money(response.ticket_promedio));
                $('#kpiUnidadesMayoristaConta').text(Number(response.unidades || 0).toLocaleString('en-US'));
                $('#kpiMayoristasActivos').text(response.mayoristas);
                $('#kpiTopMayorista').text(response.top_mayorista.nombre);
                $('#kpiTopMayoristaTotal').text(money(response.top_mayorista.total));
                $('#kpiTopProducto').text(response.top_producto.nombre);
                $('#kpiTopProductoTotal').text(money(response.top_producto.total));
            });
        }

        function renderCharts(payload) {
            if (graficoLinea) graficoLinea.destroy();
            if (graficoBarras) graficoBarras.destroy();

            graficoLinea = new Chart(document.getElementById('graficoMayoristasLinea'), {
                type: 'line',
                data: {
                    labels: payload.serie.labels,
                    datasets: [{
                        label: 'Facturacion',
                        data: payload.serie.data,
                        borderColor: '#15803d',
                        backgroundColor: 'rgba(21,128,61,.12)',
                        fill: true,
                        tension: .3
                    }]
                },
                options: { maintainAspectRatio: false, responsive: true }
            });

            graficoBarras = new Chart(document.getElementById('graficoMayoristasBarras'), {
                type: 'bar',
                data: {
                    labels: payload.mayoristas.labels,
                    datasets: [{
                        label: 'Facturacion',
                        data: payload.mayoristas.data,
                        backgroundColor: '#0f766e'
                    }]
                },
                options: { maintainAspectRatio: false, responsive: true }
            });
        }

        function cargarSeries() {
            $.getJSON("{{ route('contabilidad.mayoristas.series') }}", filtrosMayoristas(), renderCharts);
        }

        $(function () {
            $('#rutaContaMayorista, #usuarioContaMayorista').select2({
                width: '100%',
                placeholder: 'Todos',
                allowClear: true
            });

            tablaVentas = $('#tablaContaVentasMayoristas').DataTable({
                ajax: {
                    url: "{{ route('contabilidad.mayoristas.reportes.ventas') }}",
                    data: d => Object.assign(d, filtrosMayoristas())
                },
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: { url: '/i18n/es-ES.json' },
                columns: [
                    { data: 'numero_venta' },
                    { data: 'fecha_venta' },
                    { data: 'cliente' },
                    { data: 'ruta' },
                    { data: 'mayorista' },
                    { data: 'items' },
                    { data: 'unidades' },
                    { data: 'total', render: data => money(data) }
                ]
            });

            tablaProductos = $('#tablaContaProductosMayoristas').DataTable({
                ajax: {
                    url: "{{ route('contabilidad.mayoristas.reportes.productos') }}",
                    data: d => Object.assign(d, filtrosMayoristas())
                },
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: { url: '/i18n/es-ES.json' },
                columns: [
                    { data: 'codigo' },
                    { data: 'producto' },
                    { data: 'ventas' },
                    { data: 'presentaciones' },
                    { data: 'unidades' },
                    { data: 'total', render: data => money(data) }
                ]
            });

            $('#btnAplicarMayoristasConta').on('click', function () {
                cargarResumen();
                cargarSeries();
                tablaVentas.ajax.reload();
                tablaProductos.ajax.reload();
            });

            cargarResumen();
            cargarSeries();
        });
    </script>
@stop
