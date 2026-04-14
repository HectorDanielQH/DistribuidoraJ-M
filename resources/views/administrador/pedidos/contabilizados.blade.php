@extends('adminlte::page')

@section('title', 'Pedidos contabilizados')

@section('content_header')
    <div class="closed-header">
        <div>
            <span>Ventas cerradas</span>
            <h1>Pedidos contabilizados</h1>
            <p>Primero revisa el cierre por fecha y entra al dia para administrar sus pedidos.</p>
        </div>
        <a href="{{ route('pedidos.administrador.visualizacionDespachados') }}" class="btn btn-outline-secondary closed-main-btn">
            <i class="fas fa-arrow-left"></i> Volver a despachados
        </a>
    </div>
@stop

@section('content')
    <section class="closed-summary">
        <article>
            <span>Contabilizados</span>
            <strong>{{ $resumenContabilizados['pedidos'] ?? 0 }}</strong>
            <small>Pedidos cerrados</small>
        </article>
        <article>
            <span>Total vendido</span>
            <strong>Bs {{ number_format($resumenContabilizados['total'] ?? 0, 2, '.', ',') }}</strong>
            <small>Segun pedidos contabilizados</small>
        </article>
        <article>
            <span>Productos</span>
            <strong>{{ $resumenContabilizados['items'] ?? 0 }}</strong>
            <small>Lineas contabilizadas</small>
        </article>
        <article>
            <span>Hoy</span>
            <strong>{{ $resumenContabilizados['hoy'] ?? 0 }}</strong>
            <small>Pedidos con venta hoy</small>
        </article>
    </section>

    <section class="closed-filters">
        <label>
            Desde
            <input type="date" id="fecha-desde" class="form-control closed-filter">
        </label>
        <label>
            Hasta
            <input type="date" id="fecha-hasta" class="form-control closed-filter">
        </label>
        <label>
            Ruta
            <select id="filtro-ruta" class="form-control closed-filter">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventista
            <select id="filtro-preventista" class="form-control closed-filter">
                <option value="">Todos</option>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" class="btn btn-outline-secondary closed-main-btn" id="limpiar-filtros">
            <i class="fas fa-eraser"></i> Limpiar
        </button>
    </section>

    <section class="closed-table-shell">
        <table class="table table-striped table-bordered" id="tablaPedidosContabilizados">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Pedido / entrega</th>
                    <th>Pedidos</th>
                    <th>Productos</th>
                    <th>Preventistas</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.css" rel="stylesheet">
    <style>
        .content-wrapper { background: #eef3f1; }
        .closed-header, .closed-summary, .closed-filters, .closed-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .closed-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .closed-header span, .closed-summary span {
            color: #0f766e;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .closed-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .closed-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .closed-main-btn, .closed-action {
            border-radius: 8px;
            font-weight: 900;
            min-height: 40px;
        }
        .closed-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .closed-summary article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .closed-summary strong {
            display: block;
            color: #111827;
            font-size: 1.28rem;
            font-weight: 900;
        }
        .closed-summary small {
            color: #64748b;
            font-weight: 800;
        }
        .closed-filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: end;
        }
        .closed-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .closed-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .closed-table-shell table { width: 100% !important; }
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            background-color: #0f766e;
            border-radius: 8px;
            box-shadow: none;
        }
        .closed-order-number, .closed-total {
            color: #0f766e;
            font-weight: 900;
            white-space: nowrap;
        }
        .closed-client strong, .closed-client span {
            display: block;
        }
        .closed-client span {
            color: #64748b;
            font-size: .88rem;
            font-weight: 800;
        }
        .closed-date-stack strong,
        .closed-date-stack span {
            display: block;
            white-space: nowrap;
        }
        .closed-date-stack span {
            color: #64748b;
            font-weight: 800;
        }
        .closed-pill {
            display: inline-flex;
            border-radius: 8px;
            padding: 6px 8px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 900;
            white-space: nowrap;
        }
        .closed-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .closed-detail-list {
            display: grid;
            gap: 10px;
            text-align: left;
        }
        .closed-detail-head, .closed-detail-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }
        .closed-detail-item strong {
            color: #17211d;
        }
        .closed-order-card {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
            display: grid;
            gap: 8px;
        }
        .closed-order-card-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .closed-order-card-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        @media (max-width: 767.98px) {
            .closed-header { flex-direction: column; }
            .closed-summary, .closed-filters { grid-template-columns: 1fr; }
            .closed-main-btn, .closed-action { width: 100%; }
            .closed-actions, .closed-order-card-actions { flex-direction: column; }
            .closed-table-shell { padding: 8px; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.js"></script>

    <script>
        let tablaContabilizados;

        $(document).ready(function () {
            tablaContabilizados = $('#tablaPedidosContabilizados').DataTable({
                processing: true,
                serverSide: true,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar',
                    searchPlaceholder: 'Cliente o pedido'
                },
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.visualizacionContabilizados') }}",
                    data: function (d) {
                        d.fecha_desde = $('#fecha-desde').val();
                        d.fecha_hasta = $('#fecha-hasta').val();
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                    }
                },
                columns: [
                    { data: 'fecha_contabilizacion', name: 'fecha_contabilizacion' },
                    { data: 'fechas_operacion', orderable: false, searchable: false },
                    { data: 'pedidos', orderable: false, searchable: false },
                    { data: 'items', orderable: false, searchable: false },
                    { data: 'preventistas', orderable: false, searchable: false },
                    { data: 'total', orderable: false, searchable: false },
                    { data: 'acciones', orderable: false, searchable: false },
                ],
                columnDefs: [
                    { className: 'dtr-control', targets: 0 },
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 5 },
                    { responsivePriority: 4, targets: 6 },
                ],
                order: [[0, 'desc']],
            });

            $('.closed-filter').on('change', function () {
                tablaContabilizados.ajax.reload();
            });

            $('#limpiar-filtros').on('click', function () {
                $('.closed-filter').val('');
                tablaContabilizados.ajax.reload();
            });
        });

        function verPedidosDeFecha(button) {
            const fecha = button.getAttribute('data-fecha');

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.contabilizados.fecha.pedidos', ':fecha') }}".replace(':fecha', fecha),
                type: 'GET',
                data: {
                    ruta_id: $('#filtro-ruta').val(),
                    preventista_id: $('#filtro-preventista').val()
                },
                beforeSend: function () {
                    Swal.fire({ title: 'Cargando pedidos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    const pedidos = response.pedidos.map(function (pedido) {
                        return `
                            <div class="closed-order-card">
                                <div class="closed-order-card-head">
                                    <strong>#${String(pedido.numero_pedido).padStart(6, '0')} - ${escapeHtml(pedido.cliente)}</strong>
                                    <span class="closed-total">Bs ${Number(pedido.total).toFixed(2)}</span>
                                </div>
                                <div>Ruta: ${escapeHtml(pedido.ruta)}</div>
                                <div>Preventista: ${escapeHtml(pedido.preventista)}</div>
                                <div>Pedido: ${pedido.fecha_pedido} | Entrega: ${pedido.fecha_entrega}</div>
                                <div>${pedido.items} productos contabilizados</div>
                                <div class="closed-order-card-actions">
                                    <button type="button" class="btn btn-info btn-sm closed-action" onclick="verPedidoContabilizadoPorNumero('${pedido.numero_pedido}')">
                                        <i class="fas fa-eye"></i> Ver detalle
                                    </button>
                                    <a href="${pedido.editar_url}" class="btn btn-warning btn-sm closed-action">
                                        <i class="fas fa-edit"></i> Ajustar
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm closed-action" onclick="recontabilizarPedidoPorNumero('${pedido.numero_pedido}')">
                                        <i class="fas fa-calendar-check"></i> Cambiar fecha
                                    </button>
                                </div>
                            </div>`;
                    }).join('');

                    Swal.fire({
                        title: `Pedidos del ${response.fecha_formateada}`,
                        html: `
                            <div class="closed-detail-list">
                                <div class="closed-detail-head">
                                    <strong>${response.cantidad_pedidos} pedidos | ${response.items} productos</strong>
                                    <div>Total: Bs ${Number(response.total).toFixed(2)}</div>
                                </div>
                                ${pedidos || '<div class="closed-detail-item">No hay pedidos para esta fecha.</div>'}
                            </div>`,
                        width: window.innerWidth <= 760 ? '96%' : '920px',
                        confirmButtonText: 'Cerrar',
                        showCloseButton: true,
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar la fecha.', 'error');
                }
            });
        }

        function verPedidoContabilizado(button) {
            verPedidoContabilizadoPorNumero(button.getAttribute('data-numero-pedido'));
        }

        function verPedidoContabilizadoPorNumero(numeroPedido) {
            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.contabilizados.detalle', ':pedido') }}".replace(':pedido', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({ title: 'Cargando detalle...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    const lineas = response.lineas.map(function (item) {
                        const promocion = item.promocion ? '<div>Promocion aplicada</div>' : '';
                        return `
                            <div class="closed-detail-item">
                                <strong>${escapeHtml(item.producto)}</strong>
                                <div>Codigo: ${escapeHtml(item.codigo)}</div>
                                <div>Cantidad: ${item.cantidad} ${escapeHtml(item.forma_venta)}</div>
                                <div>Precio: Bs ${Number(item.precio).toFixed(2)}</div>
                                <strong>Subtotal: Bs ${Number(item.subtotal).toFixed(2)}</strong>
                                ${promocion}
                            </div>`;
                    }).join('');

                    Swal.fire({
                        title: `Pedido #${String(response.numero_pedido).padStart(6, '0')}`,
                        html: `
                            <div class="closed-detail-list">
                                <div class="closed-detail-head">
                                    <strong>${escapeHtml(response.cliente)}</strong>
                                    <div>Ruta: ${escapeHtml(response.ruta)}</div>
                                    <div>Preventista: ${escapeHtml(response.preventista)}</div>
                                    <div>Pedido: ${response.fecha_pedido} | Entrega: ${response.fecha_entrega}</div>
                                    <div>Contabilizado: ${response.fecha_contabilizacion}</div>
                                </div>
                                ${lineas}
                                <div class="closed-detail-item"><strong>Total: Bs ${Number(response.total).toFixed(2)}</strong></div>
                            </div>`,
                        width: window.innerWidth <= 760 ? '96%' : '820px',
                        confirmButtonText: 'Cerrar',
                        showCloseButton: true,
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el detalle.', 'error');
                }
            });
        }

        function recontabilizar_pedido(button) {
            recontabilizarPedidoPorNumero(button.getAttribute('data-id-pedido'));
        }

        function recontabilizarPedidoPorNumero(numeroPedido) {
            Swal.fire({
                title: `Cambiar fecha de pedido #${numeroPedido}`,
                text: 'Solo cambia la fecha contable de la venta. No mueve stock.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Guardar fecha',
                cancelButtonText: 'Cancelar',
                input: 'date',
                inputLabel: 'Nueva fecha de contabilizacion',
                preConfirm: (fecha) => {
                    if (!fecha) {
                        Swal.showValidationMessage('Selecciona una fecha valida.');
                    }
                    return fecha;
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '{{ route("administrador.pedidos.administrador.recontabilizarPedido", ":numero_pedido") }}'.replace(':numero_pedido', numeroPedido),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fecha_contabilizacion: result.value
                    },
                    success: function(response) {
                        Swal.fire('Listo', response.mensaje || 'Pedido recontabilizado correctamente.', 'success');
                        tablaContabilizados.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.error || 'No se pudo recontabilizar el pedido.', 'error');
                    }
                });
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];
            });
        }
    </script>
@stop
