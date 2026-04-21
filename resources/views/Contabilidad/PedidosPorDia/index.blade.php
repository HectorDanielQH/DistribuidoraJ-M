@extends('adminlte::page')

@section('title', 'Pedidos por Dia')

@section('content_header')
    <section class="orders-day-hero">
        <div>
            <span class="hero-kicker">Contabilidad / pedidos del dia</span>
            <h1>Lista de pedidos por dia</h1>
            <p>Consulta, revisa e imprime los pedidos contabilizados del dia con filtros por ruta y preventista.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-danger orders-day-btn" id="btnImprimirPedidos">
                <i class="fas fa-file-pdf"></i> Imprimir lista
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="orders-day-section">
        <div class="filters-grid">
            <label>
                Fecha
                <input type="date" id="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Rutas
                <select id="ruta_id" class="form-control orders-day-select" multiple>
                    @foreach($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Preventistas
                <select id="preventista_id" class="form-control orders-day-select" multiple>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="filter-actions">
                <button type="button" class="btn btn-success orders-day-btn" id="btnAplicarPedidosDia">
                    <i class="fas fa-filter"></i> Aplicar
                </button>
                <button type="button" class="btn btn-outline-secondary orders-day-btn" id="btnLimpiarPedidosDia">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </div>
    </section>

    <section class="orders-day-section">
        <div class="summary-grid">
            <article>
                <span>Pedidos</span>
                <strong id="sumPedidos">0</strong>
            </article>
            <article>
                <span>Clientes</span>
                <strong id="sumClientes">0</strong>
            </article>
            <article>
                <span>Items</span>
                <strong id="sumItems">0</strong>
            </article>
            <article>
                <span>Total</span>
                <strong id="sumTotal">Bs 0.00</strong>
            </article>
        </div>
    </section>

    <section class="orders-day-section">
        <div class="table-responsive">
            <table class="table table-striped table-bordered orders-day-table" id="tablaPedidosDia">
                <thead>
                    <tr>
                        <th>Nro. pedido</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Ruta</th>
                        <th>Preventista</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="orders-day-section">
        <div class="detail-header">
            <div>
                <span class="hero-kicker">Detalle del pedido</span>
                <h2 id="detalleTitulo">Selecciona un pedido para ver los productos</h2>
            </div>
            <div class="detail-total" id="detalleTotal">Bs 0.00</div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered orders-day-table" id="tablaDetallePedido">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Presentacion</th>
                        <th>Cantidad</th>
                        <th>Unidades</th>
                        <th>Precio unitario</th>
                        <th>Desc. %</th>
                        <th>Subtotal</th>
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
        .orders-day-hero, .orders-day-section {
            background: #fff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .orders-day-hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .hero-kicker {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .orders-day-hero h1 {
            color: #17211d;
            margin: 0;
            font-size: 1.85rem;
            font-weight: 900;
        }
        .orders-day-hero p {
            color: #64748b;
            margin: 6px 0 0;
            font-weight: 700;
        }
        .hero-actions {
            display: flex;
            align-items: center;
        }
        .orders-day-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }
        .orders-day-section {
            padding: 18px;
            margin-bottom: 16px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr 1.2fr auto;
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
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .summary-grid article {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px;
        }
        .summary-grid span {
            color: #64748b;
            display: block;
            font-size: .82rem;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .summary-grid strong {
            color: #17211d;
            display: block;
            font-size: 1.45rem;
            font-weight: 900;
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 14px;
        }
        .detail-header h2 {
            color: #17211d;
            margin: 0;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .detail-total {
            color: #166534;
            font-size: 1.35rem;
            font-weight: 900;
            white-space: nowrap;
        }
        .orders-day-table { width: 100% !important; }
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
        @media (max-width: 991.98px) {
            .orders-day-hero, .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .filters-grid, .summary-grid {
                grid-template-columns: 1fr;
            }
            .orders-day-btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script>
        let tablaPedidosDia = null;
        let tablaDetallePedido = null;

        function obtenerFiltrosPedidosDia() {
            return {
                fecha: $('#fecha').val(),
                ruta_id: $('#ruta_id').val() || [],
                preventista_id: $('#preventista_id').val() || [],
            };
        }

        function formatoMoneda(valor) {
            return 'Bs ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function actualizarResumen(data) {
            const pedidos = data.length;
            const clientes = new Set(data.map((fila) => fila.cliente)).size;
            const items = data.reduce((suma, fila) => {
                const numero = String(fila.items || '0').replace(/[^0-9]/g, '');
                return suma + Number(numero || 0);
            }, 0);
            const total = data.reduce((suma, fila) => suma + Number(String(fila.total || '0').replace(/[^0-9.-]/g, '')), 0);

            $('#sumPedidos').text(pedidos.toLocaleString('en-US'));
            $('#sumClientes').text(clientes.toLocaleString('en-US'));
            $('#sumItems').text(items.toLocaleString('en-US'));
            $('#sumTotal').text(formatoMoneda(total));
        }

        function cargarDetallePedido(numeroPedido) {
            $('#detalleTitulo').text(`Detalle del pedido #${numeroPedido}`);
            $('#detalleTotal').text('Cargando...');

            $.getJSON("{{ route('contabilidad.pedidos.porDia.detalle', ':pedido') }}".replace(':pedido', numeroPedido), function (response) {
                tablaDetallePedido.clear().rows.add(response.items || []).draw();
                $('#detalleTotal').text(formatoMoneda(response.total || 0));
            });
        }

        $(document).ready(function () {
            $('.orders-day-select').select2({
                placeholder: 'Todos',
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            tablaPedidosDia = $('#tablaPedidosDia').DataTable({
                ajax: {
                    url: "{{ route('contabilidad.pedidos.porDia.data') }}",
                    data: function (d) {
                        Object.assign(d, obtenerFiltrosPedidosDia());
                    },
                    dataSrc: function (json) {
                        actualizarResumen(json.data || []);
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'numero_pedido' },
                    { data: 'fecha_contable' },
                    { data: 'cliente' },
                    { data: 'ruta' },
                    { data: 'preventista' },
                    { data: 'items' },
                    { data: 'total' },
                    {
                        data: 'numero_pedido',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `<button type="button" class="btn btn-info btn-sm orders-day-btn btn-ver-detalle-pedido" data-pedido="${data}">
                                <i class="fas fa-eye"></i> Ver
                            </button>`;
                        }
                    },
                ],
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Excel' },
                    { extend: 'print', className: 'btn btn-secondary btn-sm', text: 'Imprimir tabla' }
                ],
                language: { url: '/i18n/es-ES.json' }
            });

            tablaDetallePedido = $('#tablaDetallePedido').DataTable({
                data: [],
                columns: [
                    { data: 'codigo' },
                    { data: 'producto' },
                    { data: 'presentacion' },
                    { data: 'cantidad' },
                    { data: 'unidades' },
                    { data: 'precio_unitario', render: (data) => formatoMoneda(data) },
                    { data: 'descuento', render: (data) => `${Number(data || 0).toFixed(2)}%` },
                    { data: 'subtotal', render: (data) => formatoMoneda(data) },
                ],
                responsive: true,
                autoWidth: false,
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                language: { url: '/i18n/es-ES.json' }
            });

            $('#btnAplicarPedidosDia').on('click', function () {
                tablaPedidosDia.ajax.reload();
            });

            $('#tablaPedidosDia').on('click', '.btn-ver-detalle-pedido', function () {
                cargarDetallePedido($(this).data('pedido'));
            });

            $('#btnLimpiarPedidosDia').on('click', function () {
                $('#fecha').val("{{ now()->format('Y-m-d') }}");
                $('#ruta_id').val(null).trigger('change');
                $('#preventista_id').val(null).trigger('change');
                tablaPedidosDia.ajax.reload();
            });

            $('#btnImprimirPedidos').on('click', function () {
                const url = new URL("{{ route('contabilidad.pedidos.porDia.pdf') }}", window.location.origin);
                const filtros = obtenerFiltrosPedidosDia();

                if (filtros.fecha) {
                    url.searchParams.set('fecha', filtros.fecha);
                }

                filtros.ruta_id.forEach((id) => url.searchParams.append('ruta_id[]', id));
                filtros.preventista_id.forEach((id) => url.searchParams.append('preventista_id[]', id));

                window.open(url.toString(), '_blank');
            });
        });
    </script>
@stop
