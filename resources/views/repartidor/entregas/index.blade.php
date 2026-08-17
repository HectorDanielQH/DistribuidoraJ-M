@extends('adminlte::page')

@section('title', 'Entregas despachadas')

@section('content_header')
    <div class="delivery-hero">
        <div>
            <span class="delivery-eyebrow">Panel de reparto</span>
            <h1>Entregas despachadas</h1>
            <p>Visualiza pedidos entregados al repartidor, rutas, preventistas y ubicaciones disponibles.</p>
        </div>
        <div class="delivery-hero-date">
            <i class="fas fa-calendar-check"></i>
            <span id="delivery-date-label">{{ date('d/m/Y', strtotime($fechaEntrega)) }}</span>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .delivery-hero {
            align-items: center;
            background: linear-gradient(135deg, #123c36 0%, #1f7a62 48%, #f2b84b 100%);
            border-radius: 24px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            min-height: 150px;
            overflow: hidden;
            padding: 26px 30px;
            position: relative;
        }

        .content-wrapper > .content {
            overflow-x: hidden;
        }

        .delivery-hero::after {
            background: rgba(255, 255, 255, .14);
            border-radius: 999px;
            content: "";
            height: 190px;
            position: absolute;
            right: -54px;
            top: -64px;
            width: 190px;
        }

        .delivery-eyebrow {
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .26);
            border-radius: 999px;
            display: inline-flex;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 10px;
            padding: 7px 12px;
            text-transform: uppercase;
        }

        .delivery-hero h1 {
            font-size: clamp(1.7rem, 3vw, 2.55rem);
            font-weight: 900;
            margin: 0;
        }

        .delivery-hero p {
            color: rgba(255, 255, 255, .86);
            font-size: 1rem;
            margin: 8px 0 0;
            max-width: 760px;
        }

        .delivery-hero-date {
            align-items: center;
            background: rgba(12, 39, 35, .32);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 18px;
            display: flex;
            font-size: 1.05rem;
            font-weight: 800;
            gap: 10px;
            padding: 15px 18px;
            position: relative;
            z-index: 1;
        }

        .delivery-shell {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            margin-top: 18px;
        }

        .delivery-card {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 22px;
            box-shadow: 0 14px 36px rgba(20, 44, 38, .08);
        }

        .delivery-card-header {
            border-bottom: 1px solid #edf2ee;
            padding: 18px 20px;
        }

        .delivery-card-header h3 {
            color: #173f37;
            font-size: 1rem;
            font-weight: 900;
            margin: 0;
        }

        .delivery-card-body {
            padding: 18px 20px 20px;
        }

        .delivery-filter label {
            color: #52635f;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .delivery-filter .form-control {
            border-color: #dbe8df;
            border-radius: 13px;
            font-size: 16px;
            min-height: 46px;
        }

        .delivery-filter select[multiple] {
            min-height: 132px;
        }

        .delivery-filter .select2-container {
            width: 100% !important;
        }

        .delivery-filter .select2-container--default .select2-selection--multiple {
            border: 1px solid #dbe8df;
            border-radius: 13px;
            min-height: 48px;
            padding: 4px 6px;
        }

        .delivery-filter .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #1f7a62;
            box-shadow: 0 0 0 .18rem rgba(31, 122, 98, .14);
        }

        .delivery-filter .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #e5f4ee;
            border: 1px solid #bddccd;
            border-radius: 999px;
            color: #145343;
            font-size: .82rem;
            font-weight: 800;
            margin-top: 5px;
            padding: 4px 9px 4px 24px;
        }

        .delivery-filter .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right: 0;
            color: #176b57;
            font-size: 1rem;
            left: 6px;
            top: 3px;
        }

        .delivery-filter .select2-container--default .select2-search--inline .select2-search__field {
            font-family: inherit;
            min-height: 28px;
        }

        .select2-dropdown {
            border-color: #bddccd;
            border-radius: 13px;
            box-shadow: 0 18px 40px rgba(20, 44, 38, .16);
            overflow: hidden;
            z-index: 2060;
        }

        .select2-results__option {
            font-size: .95rem;
            padding: 11px 14px;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #dbe8df;
            border-radius: 10px;
            font-size: 16px;
            min-height: 42px;
            padding: 8px 10px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #176b57;
        }

        .delivery-button {
            border: 0;
            border-radius: 14px;
            font-weight: 900;
            padding: 11px 14px;
        }

        .delivery-button-primary {
            background: #176b57;
            color: #fff;
        }

        .delivery-button-light {
            background: #eef6f2;
            color: #176b57;
        }

        .delivery-stats {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .delivery-stat {
            background: #f6faf7;
            border: 1px solid #e2eee6;
            border-radius: 17px;
            padding: 14px;
        }

        .delivery-stat span {
            color: #6d7d79;
            display: block;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .delivery-stat strong {
            color: #123c36;
            display: block;
            font-size: 1.35rem;
            font-weight: 900;
            line-height: 1.1;
            margin-top: 7px;
        }

        .delivery-map-card {
            min-width: 0;
        }

        #delivery-map {
            border-radius: 0 0 22px 22px;
            height: min(68vh, 620px);
            min-height: 440px;
            width: 100%;
        }

        .delivery-list {
            margin-top: 18px;
        }

        .delivery-table-wrap {
            overflow-x: auto;
            width: 100%;
        }

        .delivery-table {
            margin: 0;
            min-width: 980px;
        }

        .delivery-table thead th {
            background: #f3f8f5;
            border-bottom: 1px solid #dfeae3;
            color: #32534c;
            font-size: .78rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .delivery-table td {
            vertical-align: middle;
        }

        .delivery-badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: .76rem;
            font-weight: 900;
            padding: 6px 10px;
            white-space: nowrap;
        }

        .delivery-badge-ok {
            background: #dff7ea;
            color: #13663b;
        }

        .delivery-badge-warn {
            background: #fff1d0;
            color: #8d5d00;
        }

        .delivery-empty {
            color: #6c7773;
            padding: 34px 12px;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .content-header {
                padding: 10px 8px 0;
            }

            .content {
                padding: 0 8px 16px;
            }

            .delivery-hero,
            .delivery-shell {
                display: block;
            }

            .delivery-hero {
                border-radius: 18px;
                min-height: auto;
                padding: 18px;
            }

            .delivery-hero h1 {
                font-size: 1.55rem;
                line-height: 1.08;
            }

            .delivery-hero p {
                font-size: .92rem;
                line-height: 1.35;
            }

            .delivery-eyebrow {
                font-size: .68rem;
                padding: 6px 10px;
            }

            .delivery-hero-date,
            .delivery-map-card {
                margin-top: 16px;
            }

            .delivery-hero-date {
                border-radius: 14px;
                justify-content: center;
                padding: 12px;
                width: 100%;
            }

            .delivery-card {
                border-radius: 18px;
            }

            .delivery-card-header {
                align-items: flex-start !important;
                gap: 6px;
                padding: 15px;
            }

            .delivery-card-header h3 {
                font-size: .95rem;
            }

            .delivery-card-body {
                padding: 15px;
            }

            .delivery-filter .form-group {
                margin-bottom: 14px;
            }

            .delivery-filter label {
                font-size: .72rem;
            }

            .delivery-button {
                min-height: 48px;
                padding: 13px 14px;
            }

            .delivery-stats {
                gap: 9px;
            }

            .delivery-stat {
                border-radius: 14px;
                padding: 12px;
            }

            .delivery-stat span {
                font-size: .66rem;
            }

            .delivery-stat strong {
                font-size: 1.12rem;
            }

            #delivery-map {
                border-radius: 0 0 18px 18px;
                height: 52vh;
                min-height: 320px;
            }

            .leaflet-control-zoom a {
                height: 36px;
                line-height: 36px;
                width: 36px;
            }

            .delivery-list {
                margin-top: 14px;
            }

            .delivery-table-wrap {
                overflow-x: visible;
            }

            .delivery-table {
                min-width: 0;
            }

            .delivery-table thead {
                display: none;
            }

            .delivery-table,
            .delivery-table tbody,
            .delivery-table tr,
            .delivery-table td {
                display: block;
                width: 100%;
            }

            .delivery-table tbody {
                padding: 10px;
            }

            .delivery-table tr {
                background: #fff;
                border: 1px solid #e4efe8;
                border-radius: 16px;
                box-shadow: 0 10px 26px rgba(20, 44, 38, .07);
                margin-bottom: 12px;
                overflow: hidden;
                padding: 10px 12px;
            }

            .delivery-table td {
                align-items: flex-start;
                border: 0;
                display: flex;
                gap: 12px;
                justify-content: space-between;
                padding: 8px 0;
                text-align: right;
            }

            .delivery-table td::before {
                color: #6d7d79;
                content: attr(data-label);
                flex: 0 0 38%;
                font-size: .72rem;
                font-weight: 900;
                letter-spacing: .04em;
                text-align: left;
                text-transform: uppercase;
            }

            .delivery-table td:first-child {
                border-bottom: 1px solid #edf4ef;
                color: #123c36;
                font-size: 1.05rem;
                padding-bottom: 10px;
            }

            .delivery-table td:first-child::before {
                color: #123c36;
            }

            .delivery-empty {
                display: block !important;
                padding: 24px 10px !important;
                text-align: center !important;
            }

            .delivery-empty::before {
                display: none !important;
            }

            .select2-container--open .select2-dropdown {
                left: 0 !important;
                max-width: calc(100vw - 24px);
            }
        }

        @media (max-width: 420px) {
            .delivery-stats {
                grid-template-columns: 1fr 1fr;
            }

            #delivery-map {
                height: 48vh;
                min-height: 300px;
            }
        }
    </style>
@stop

@section('content')
    <div class="delivery-shell">
        <aside class="delivery-card">
            <div class="delivery-card-header">
                <h3><i class="fas fa-filter mr-2"></i>Filtros de entrega</h3>
            </div>
            <div class="delivery-card-body">
                <form id="delivery-filter-form" class="delivery-filter">
                    <div class="form-group">
                        <label for="fecha_entrega">Fecha de despacho</label>
                        <input type="date" id="fecha_entrega" name="fecha_entrega" value="{{ $fechaEntrega }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="ruta_id">Rutas</label>
                        <select id="ruta_id" name="ruta_id[]" class="form-control" multiple>
                            @foreach ($rutas as $ruta)
                                <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Al elegir ruta se muestran solo los preventistas relacionados.</small>
                    </div>

                    <div class="form-group">
                        <label for="preventista_id">Preventistas</label>
                        <select id="preventista_id" name="preventista_id[]" class="form-control" multiple>
                            @foreach ($preventistas as $preventista)
                                <option value="{{ $preventista->id }}">
                                    {{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Al elegir preventista se muestran solo sus rutas despachadas.</small>
                    </div>

                    <div class="d-flex flex-column">
                        <button type="submit" class="delivery-button delivery-button-primary mb-2">
                            <i class="fas fa-sync-alt mr-1"></i> Actualizar entregas
                        </button>
                        <button type="button" id="delivery-clear" class="delivery-button delivery-button-light">
                            <i class="fas fa-eraser mr-1"></i> Limpiar filtros
                        </button>
                    </div>
                </form>

                <hr>

                <div class="delivery-stats">
                    <div class="delivery-stat">
                        <span>Pedidos</span>
                        <strong id="stat-pedidos">{{ number_format($resumen['pedidos']) }}</strong>
                    </div>
                    <div class="delivery-stat">
                        <span>Items</span>
                        <strong id="stat-items">{{ number_format($resumen['items']) }}</strong>
                    </div>
                    <div class="delivery-stat">
                        <span>Con GPS</span>
                        <strong id="stat-con-ubicacion">{{ number_format($resumen['con_ubicacion']) }}</strong>
                    </div>
                    <div class="delivery-stat">
                        <span>Sin GPS</span>
                        <strong id="stat-sin-ubicacion">{{ number_format($resumen['sin_ubicacion']) }}</strong>
                    </div>
                </div>
            </div>
        </aside>

        <main class="delivery-card delivery-map-card">
            <div class="delivery-card-header d-flex align-items-center justify-content-between">
                <h3><i class="fas fa-map-marked-alt mr-2"></i>Mapa de ubicaciones</h3>
                <span class="text-muted small" id="delivery-map-counter">Cargando...</span>
            </div>
            <div id="delivery-map"></div>
        </main>
    </div>

    <section class="delivery-card delivery-list">
        <div class="delivery-card-header d-flex align-items-center justify-content-between">
            <h3><i class="fas fa-clipboard-list mr-2"></i>Listado de pedidos despachados</h3>
            <span class="text-muted small">Máximo 500 registros por consulta</span>
        </div>
        <div class="delivery-table-wrap">
            <table class="table delivery-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Ruta</th>
                        <th>Preventista</th>
                        <th>Dirección</th>
                        <th>Celular</th>
                        <th>Fecha entrega</th>
                        <th>Items</th>
                        <th>Monto estimado</th>
                        <th>GPS</th>
                    </tr>
                </thead>
                <tbody id="delivery-table-body">
                    <tr>
                        <td colspan="10" class="delivery-empty">Cargando entregas...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const deliveryRoutes = {
            datos: @json(route('repartidor.entregas.datos')),
            mapa: @json(route('repartidor.entregas.mapa')),
            opciones: @json(route('repartidor.entregas.opciones')),
        };

        const defaultMapCenter = [-17.7833, -63.1821];
        const map = L.map('delivery-map', {
            preferCanvas: true,
            scrollWheelZoom: true,
        }).setView(defaultMapCenter, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        let markerLayer = L.layerGroup().addTo(map);
        let syncingFilters = false;

        function hasSelect2() {
            return window.jQuery && $.fn && $.fn.select2;
        }

        function initDeliverySelect2() {
            if (!hasSelect2()) {
                return;
            }

            $('#ruta_id').select2({
                placeholder: 'Todas las rutas',
                allowClear: true,
                closeOnSelect: false,
                width: '100%',
                dropdownParent: $('#ruta_id').closest('.form-group'),
            });

            $('#preventista_id').select2({
                placeholder: 'Todos los preventistas',
                allowClear: true,
                closeOnSelect: false,
                width: '100%',
                dropdownParent: $('#preventista_id').closest('.form-group'),
            });
        }

        function buildQuery() {
            const params = new URLSearchParams();
            const fecha = document.getElementById('fecha_entrega').value;

            if (fecha) {
                params.append('fecha_entrega', fecha);
            }

            Array.from(document.getElementById('ruta_id').selectedOptions)
                .forEach(option => params.append('ruta_id[]', option.value));

            Array.from(document.getElementById('preventista_id').selectedOptions)
                .forEach(option => params.append('preventista_id[]', option.value));

            return params.toString();
        }

        function money(value) {
            return Number(value || 0).toLocaleString('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));
        }

        function updateStats(resumen) {
            document.getElementById('stat-pedidos').textContent = Number(resumen.pedidos || 0).toLocaleString('es-BO');
            document.getElementById('stat-items').textContent = Number(resumen.items || 0).toLocaleString('es-BO');
            document.getElementById('stat-con-ubicacion').textContent = Number(resumen.con_ubicacion || 0).toLocaleString('es-BO');
            document.getElementById('stat-sin-ubicacion').textContent = Number(resumen.sin_ubicacion || 0).toLocaleString('es-BO');
        }

        function renderTable(pedidos) {
            const tbody = document.getElementById('delivery-table-body');

            if (!pedidos.length) {
                tbody.innerHTML = '<tr><td colspan="10" class="delivery-empty">No hay pedidos despachados para estos filtros.</td></tr>';
                return;
            }

            tbody.innerHTML = pedidos.map(pedido => `
                <tr>
                    <td data-label="Pedido"><strong>${escapeHtml(pedido.numero_pedido_formateado)}</strong></td>
                    <td data-label="Cliente">${escapeHtml(pedido.cliente || 'N/A')}<br><small class="text-muted">${escapeHtml(pedido.referencia || '')}</small></td>
                    <td data-label="Ruta">${escapeHtml(pedido.ruta)}</td>
                    <td data-label="Preventista">${escapeHtml(pedido.preventista)}</td>
                    <td data-label="Direccion">${escapeHtml(pedido.direccion)}</td>
                    <td data-label="Celular">${escapeHtml(pedido.celular)}</td>
                    <td data-label="Fecha">${escapeHtml(pedido.fecha_entrega)}</td>
                    <td data-label="Items"><strong>${pedido.items}</strong></td>
                    <td data-label="Monto"><strong>Bs ${money(pedido.monto_estimado)}</strong></td>
                    <td data-label="GPS">
                        <span class="delivery-badge ${pedido.tiene_ubicacion ? 'delivery-badge-ok' : 'delivery-badge-warn'}">
                            ${pedido.tiene_ubicacion ? 'Con GPS' : 'Sin GPS'}
                        </span>
                    </td>
                </tr>
            `).join('');
        }

        function renderMap(ubicaciones) {
            markerLayer.clearLayers();
            document.getElementById('delivery-map-counter').textContent = `${ubicaciones.length} ubicaciones`;

            if (!ubicaciones.length) {
                map.setView(defaultMapCenter, 12);
                return;
            }

            const bounds = [];

            ubicaciones.forEach(item => {
                const latLng = [item.latitud, item.longitud];
                bounds.push(latLng);

                L.marker(latLng)
                    .bindPopup(`
                        <strong>${item.numero_pedido_formateado}</strong><br>
                        ${item.cliente}<br>
                        <small>${item.ruta}</small><br>
                        <small>${item.direccion}</small><br>
                        <small>${item.celular}</small>
                    `)
                    .addTo(markerLayer);
            });

            map.fitBounds(bounds, { padding: [34, 34], maxZoom: 16 });
        }

        function selectedValues(selectId) {
            if (hasSelect2()) {
                return $(`#${selectId}`).val() || [];
            }

            return Array.from(document.getElementById(selectId).selectedOptions).map(option => option.value);
        }

        function syncSelectOptions(selectId, options, keepValues) {
            const select = document.getElementById(selectId);
            const keep = new Set(keepValues.map(String));
            const validSelectedValues = options
                .filter(option => keep.has(String(option.id)))
                .map(option => String(option.id));

            if (hasSelect2()) {
                const $select = $(`#${selectId}`);
                $select.empty();

                options.forEach(option => {
                    const optionElement = new Option(option.text, option.id, false, keep.has(String(option.id)));
                    $select.append(optionElement);
                });

                $select.val(validSelectedValues).trigger('change.select2');
                return;
            }

            select.innerHTML = options.map(option => {
                const selected = keep.has(String(option.id)) ? 'selected' : '';
                return `<option value="${escapeHtml(option.id)}" ${selected}>${escapeHtml(option.text)}</option>`;
            }).join('');
        }

        async function updateLinkedOptions(changedFilter = null) {
            const query = buildQuery();
            const suffix = query ? `?${query}` : '';
            const response = await fetch(deliveryRoutes.opciones + suffix, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar las opciones relacionadas.');
            }

            const payload = await response.json();
            syncingFilters = true;

            if (changedFilter !== 'ruta') {
                syncSelectOptions('ruta_id', payload.rutas || [], selectedValues('ruta_id'));
            }

            if (changedFilter !== 'preventista') {
                syncSelectOptions('preventista_id', payload.preventistas || [], selectedValues('preventista_id'));
            }

            syncingFilters = false;
        }

        async function loadDeliveries() {
            const query = buildQuery();
            const suffix = query ? `?${query}` : '';
            const fecha = document.getElementById('fecha_entrega').value;

            document.getElementById('delivery-map-counter').textContent = 'Cargando...';
            document.getElementById('delivery-date-label').textContent = fecha
                ? fecha.split('-').reverse().join('/')
                : 'Todas las fechas';

            const [datosResponse, mapaResponse] = await Promise.all([
                fetch(deliveryRoutes.datos + suffix, { headers: { 'Accept': 'application/json' } }),
                fetch(deliveryRoutes.mapa + suffix, { headers: { 'Accept': 'application/json' } }),
            ]);

            if (!datosResponse.ok || !mapaResponse.ok) {
                throw new Error('No se pudieron cargar las entregas.');
            }

            const datos = await datosResponse.json();
            const mapa = await mapaResponse.json();

            updateStats(datos.resumen || {});
            renderTable(datos.pedidos || []);
            renderMap(mapa.ubicaciones || []);
        }

        document.getElementById('delivery-filter-form').addEventListener('submit', event => {
            event.preventDefault();
            Promise.resolve()
                .then(() => updateLinkedOptions())
                .then(() => loadDeliveries())
                .catch(error => {
                document.getElementById('delivery-map-counter').textContent = 'Error';
                document.getElementById('delivery-table-body').innerHTML = `<tr><td colspan="10" class="delivery-empty">${error.message}</td></tr>`;
            });
        });

        document.getElementById('delivery-clear').addEventListener('click', () => {
            document.getElementById('fecha_entrega').value = @json(now()->toDateString());
            Array.from(document.getElementById('ruta_id').options).forEach(option => option.selected = false);
            Array.from(document.getElementById('preventista_id').options).forEach(option => option.selected = false);
            updateLinkedOptions()
                .then(() => loadDeliveries())
                .catch(() => loadDeliveries());
        });

        document.getElementById('ruta_id').addEventListener('change', () => {
            if (syncingFilters) {
                return;
            }

            updateLinkedOptions('ruta')
                .then(() => loadDeliveries())
                .catch(() => loadDeliveries());
        });

        document.getElementById('preventista_id').addEventListener('change', () => {
            if (syncingFilters) {
                return;
            }

            updateLinkedOptions('preventista')
                .then(() => loadDeliveries())
                .catch(() => loadDeliveries());
        });

        document.getElementById('fecha_entrega').addEventListener('change', () => {
            updateLinkedOptions()
                .then(() => loadDeliveries())
                .catch(() => loadDeliveries());
        });

        document.addEventListener('DOMContentLoaded', () => {
            initDeliverySelect2();
            setTimeout(() => map.invalidateSize(), 250);
            updateLinkedOptions()
                .then(() => loadDeliveries())
                .catch(() => loadDeliveries());
        });
    </script>
@stop
