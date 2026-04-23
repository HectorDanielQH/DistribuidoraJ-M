@extends('adminlte::page')

@section('title', 'Ventas Mayoristas')

@section('content_header')
    <section class="major-admin-hero">
        <div>
            <span>Administrador / mayoristas</span>
            <h1>Ventas mayoristas</h1>
            <p>Controla lo que registraron los mayoristas, revisa a quién vendieron, cuánto facturaron y entra directo a editar cuando haga falta.</p>
        </div>
        <div class="hero-actions">
            <a href="{{ route('administrador.mayoristas.pdf') }}" class="btn btn-outline-secondary admin-btn" id="btnReporteMayoristas" target="_blank">
                <i class="fas fa-file-pdf"></i> Reporte PDF
            </a>
        </div>
    </section>
@stop

@section('content')
    <section class="major-admin-filters">
        <div class="filter-grid">
            <label>
                Desde
                <input type="date" class="form-control" id="fechaInicioMayorista" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </label>
            <label>
                Hasta
                <input type="date" class="form-control" id="fechaFinMayorista" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Rutas
                <select class="form-control" id="rutaMayorista" multiple>
                    @foreach ($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Mayoristas
                <select class="form-control" id="usuarioMayorista" multiple>
                    @foreach ($mayoristas as $mayorista)
                        <option value="{{ $mayorista->id }}">{{ trim($mayorista->nombres.' '.$mayorista->apellido_paterno.' '.$mayorista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="filter-wide">
                Cliente
                <input type="text" class="form-control" id="clienteMayorista" placeholder="Nombre, apellido o codigo">
            </label>
        </div>
        <div class="filter-actions">
            <button type="button" class="btn btn-success admin-btn" id="btnAplicarMayoristas">
                <i class="fas fa-filter"></i> Aplicar filtros
            </button>
            <button type="button" class="btn btn-outline-secondary admin-btn" id="btnLimpiarMayoristas">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
    </section>

    <section class="major-admin-kpis">
        <article class="kpi-card"><span>Ventas</span><strong id="kpiVentas">0</strong></article>
        <article class="kpi-card"><span>Clientes</span><strong id="kpiClientes">0</strong></article>
        <article class="kpi-card"><span>Unidades</span><strong id="kpiUnidades">0</strong></article>
        <article class="kpi-card"><span>Total facturado</span><strong id="kpiTotal">Bs 0.00</strong></article>
        <article class="kpi-card"><span>Ticket promedio</span><strong id="kpiTicket">Bs 0.00</strong></article>
    </section>

    <section class="major-admin-layout">
        <article class="major-panel">
            <div class="panel-heading">
                <div>
                    <span>Registros</span>
                    <h2>Ventas registradas</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaVentasMayoristasAdmin" class="table table-striped table-bordered w-100">
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </article>

        <aside class="major-panel detail-panel">
            <div class="panel-heading">
                <div>
                    <span>Detalle</span>
                    <h2>Venta seleccionada</h2>
                </div>
            </div>
            <div id="detalleMayoristaVacio" class="detail-empty">Selecciona una venta para ver qué se vendió, a quién y por cuánto.</div>
            <div id="detalleMayoristaCard" class="d-none">
                <div class="detail-meta">
                    <div><strong id="detalleNumeroVenta">#0</strong><span id="detalleFechaVenta">Sin fecha</span></div>
                    <a href="#" class="btn btn-warning btn-sm" id="btnEditarVentaMayorista">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
                <div class="detail-block"><strong>Cliente</strong><span id="detalleCliente"></span></div>
                <div class="detail-block"><strong>Ruta</strong><span id="detalleRuta"></span></div>
                <div class="detail-block"><strong>Mayorista</strong><span id="detalleMayorista"></span></div>
                <div class="detail-block"><strong>Celular</strong><span id="detalleCelular"></span></div>
                <div class="detail-block"><strong>Direccion</strong><span id="detalleDireccion"></span></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered detail-table">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th>Forma</th>
                                <th>Cant.</th>
                                <th>Unid.</th>
                                <th>P/U</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detalleMayoristaItems"></tbody>
                    </table>
                </div>
                <div class="detail-total">
                    <span>Total</span>
                    <strong id="detalleTotalVenta">Bs 0.00</strong>
                </div>
            </div>
        </aside>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .content-wrapper{background:#eef3f1}
        .major-admin-hero,.major-admin-filters,.major-panel,.kpi-card{background:#fff;border:1px solid #d8e4de;border-radius:8px}
        .major-admin-hero,.major-admin-filters,.major-panel{padding:18px;margin-bottom:16px}
        .major-admin-hero{display:flex;justify-content:space-between;gap:16px;align-items:start}
        .major-admin-hero span,.panel-heading span{display:block;color:#15803d;font-size:.78rem;font-weight:900;text-transform:uppercase}
        .major-admin-hero h1,.panel-heading h2{margin:0;color:#17211d;font-weight:900}
        .major-admin-hero p{margin:6px 0 0;color:#64748b;font-weight:700;max-width:760px}
        .admin-btn{min-height:42px;border-radius:8px;font-weight:900}
        .filter-grid,.major-admin-kpis,.major-admin-layout{display:grid;gap:12px}
        .filter-grid{grid-template-columns:repeat(5,minmax(0,1fr))}
        .filter-wide{grid-column:span 1}
        .filter-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
        .major-admin-kpis{grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:16px}
        .kpi-card{padding:14px}
        .kpi-card span{display:block;color:#64748b;font-size:.8rem;font-weight:800}
        .kpi-card strong{display:block;margin-top:6px;color:#17211d;font-size:1.25rem;font-weight:900}
        .major-admin-layout{grid-template-columns:1.35fr .9fr}
        .panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .admin-actions{display:flex;gap:8px;flex-wrap:wrap}
        .detail-empty{color:#64748b;font-weight:700;padding:12px 0}
        .detail-meta{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px}
        .detail-meta strong{display:block;color:#17211d;font-size:1.1rem}
        .detail-meta span,.detail-block span{display:block;color:#475569;font-weight:700}
        .detail-block{margin-bottom:8px}
        .detail-block strong{display:block;color:#17211d;font-size:.82rem;text-transform:uppercase}
        .detail-table th{font-size:.78rem;text-transform:uppercase}
        .detail-total{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #d8e4de;padding-top:12px;margin-top:12px}
        .detail-total span{color:#64748b;font-weight:800}
        .detail-total strong{color:#17211d;font-size:1.2rem}
        .select2-container--default .select2-selection--multiple{border:1px solid #ced4da;border-radius:8px;min-height:38px}
        @media (max-width:1199.98px){.filter-grid,.major-admin-kpis,.major-admin-layout{grid-template-columns:1fr}}
        @media (max-width:767.98px){.major-admin-hero,.filter-actions,.detail-meta{flex-direction:column;align-items:stretch}.admin-btn{width:100%}}
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let tablaVentasMayoristas = null;

        function money(value) {
            return 'Bs ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function filtros() {
            return {
                fecha_inicio: $('#fechaInicioMayorista').val(),
                fecha_fin: $('#fechaFinMayorista').val(),
                ruta_id: $('#rutaMayorista').val() || [],
                mayorista_id: $('#usuarioMayorista').val() || [],
                cliente: $('#clienteMayorista').val()
            };
        }

        function actualizarUrlReporte() {
            const query = $.param(filtros(), true);
            $('#btnReporteMayoristas').attr('href', "{{ route('administrador.mayoristas.pdf') }}" + '?' + query);
        }

        function cargarResumen() {
            $.getJSON("{{ route('administrador.mayoristas.resumen') }}", filtros(), function (response) {
                $('#kpiVentas').text(response.ventas);
                $('#kpiClientes').text(response.clientes);
                $('#kpiUnidades').text(Number(response.unidades || 0).toLocaleString('en-US'));
                $('#kpiTotal').text(money(response.total));
                $('#kpiTicket').text(money(response.ticket_promedio));
            });
        }

        function cargarDetalle(numeroVenta) {
            $.getJSON("{{ route('administrador.mayoristas.detalle', ':numero') }}".replace(':numero', numeroVenta), function (response) {
                $('#detalleMayoristaVacio').addClass('d-none');
                $('#detalleMayoristaCard').removeClass('d-none');
                $('#detalleNumeroVenta').text('#' + response.venta.numero);
                $('#detalleFechaVenta').text(response.venta.fecha);
                $('#detalleCliente').text(response.venta.cliente + ' (' + response.venta.codigo_cliente + ')');
                $('#detalleRuta').text(response.venta.ruta);
                $('#detalleMayorista').text(response.venta.mayorista);
                $('#detalleCelular').text(response.venta.celular);
                $('#detalleDireccion').text(response.venta.direccion);
                $('#detalleTotalVenta').text(money(response.total));
                $('#btnEditarVentaMayorista').attr('href', "{{ route('mayoristas.pedidos.index') }}" + '?venta=' + response.venta.numero);

                $('#detalleMayoristaItems').html(response.items.map(function (item) {
                    return `<tr>
                        <td>${item.codigo}</td>
                        <td>${item.producto}</td>
                        <td>${item.forma_venta}</td>
                        <td>${item.cantidad}</td>
                        <td>${item.unidades}</td>
                        <td>${Number(item.precio_unitario).toFixed(2)}</td>
                        <td>${Number(item.subtotal).toFixed(2)}</td>
                    </tr>`;
                }).join(''));
            }).fail(function () {
                Swal.fire('No disponible', 'No se pudo cargar el detalle de la venta mayorista.', 'error');
            });
        }

        $(function () {
            $('#rutaMayorista, #usuarioMayorista').select2({
                width: '100%',
                placeholder: 'Todos',
                allowClear: true
            });

            tablaVentasMayoristas = $('#tablaVentasMayoristasAdmin').DataTable({
                ajax: {
                    url: "{{ route('administrador.mayoristas.data') }}",
                    data: function (d) {
                        Object.assign(d, filtros());
                    }
                },
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                dom: 'Bfrtip',
                buttons: ['excelHtml5', 'print'],
                columns: [
                    { data: 'numero_venta' },
                    { data: 'fecha_venta' },
                    { data: 'cliente' },
                    { data: 'ruta' },
                    { data: 'mayorista' },
                    { data: 'items' },
                    { data: 'unidades' },
                    { data: 'total', render: data => money(data) },
                    { data: 'acciones', orderable: false, searchable: false }
                ],
                language: { url: '/i18n/es-ES.json' }
            });

            $('#tablaVentasMayoristasAdmin').on('click', '.btn-ver-mayorista', function () {
                cargarDetalle($(this).data('venta'));
            });

            $('#btnAplicarMayoristas').on('click', function () {
                actualizarUrlReporte();
                cargarResumen();
                tablaVentasMayoristas.ajax.reload();
            });

            $('#btnLimpiarMayoristas').on('click', function () {
                $('#fechaInicioMayorista').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
                $('#fechaFinMayorista').val("{{ now()->format('Y-m-d') }}");
                $('#rutaMayorista').val(null).trigger('change');
                $('#usuarioMayorista').val(null).trigger('change');
                $('#clienteMayorista').val('');
                $('#detalleMayoristaCard').addClass('d-none');
                $('#detalleMayoristaVacio').removeClass('d-none');
                $('#btnAplicarMayoristas').trigger('click');
            });

            actualizarUrlReporte();
            cargarResumen();
        });
    </script>
@stop
