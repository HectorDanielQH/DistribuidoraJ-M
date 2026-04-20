@extends('adminlte::page')

@section('title', 'Pedidos pendientes')

@section('content_header')
    <div class="orders-header">
        <div>
            <span>Preventa / preparacion</span>
            <h1>Pedidos pendientes</h1>
            <p>Pedidos tomados por preventistas. Aun no son venta: estan reservados para preparar despacho.</p>
        </div>
        <div class="orders-header-actions">
            <a href="{{ route('pedidos.administrador.visualizacionParaDespachado') }}" class="btn btn-info orders-main-btn">
                <i class="fas fa-boxes"></i> Ver cantidad para despacho
            </a>
            <button class="btn btn-success orders-main-btn" id="btnDespacharPedidos">
                <i class="fas fa-truck"></i> Entregar al repartidor
            </button>
        </div>
    </div>
@stop

@section('content')
    <section class="orders-flow">
        <article>
            <span>Pendientes</span>
            <strong>{{ $resumenPedidos['pendientes'] ?? 0 }}</strong>
            <small>Pedidos por preparar</small>
        </article>
        <article>
            <span>Productos</span>
            <strong>{{ $resumenPedidos['pendientes_items'] ?? 0 }}</strong>
            <small>Lineas reservadas</small>
        </article>
        <article>
            <span>Total estimado</span>
            <strong>Bs {{ number_format($resumenPedidos['pendientes_total'] ?? 0, 2, '.', ',') }}</strong>
            <small>No es caja cerrada</small>
        </article>
        <article>
            <span>Despachados</span>
            <strong>{{ $resumenPedidos['despachados'] ?? 0 }}</strong>
            <small>En manos del repartidor</small>
        </article>
    </section>

    <section class="orders-filters" aria-label="Filtros de pedidos pendientes">
        <label>
            Ruta
            <select id="filtro-ruta" class="form-control orders-filter">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventista
            <select id="filtro-preventista" class="form-control orders-filter">
                <option value="">Todos</option>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Fecha pedido
            <input type="date" id="filtro-fecha" class="form-control orders-filter">
        </label>
        <label>
            Filas por pagina
            <select id="filas-pagina" class="form-control">
                <option value="10">10 pedidos</option>
                <option value="25">25 pedidos</option>
                <option value="50">50 pedidos</option>
                <option value="100">100 pedidos</option>
                <option value="-1">Todos</option>
            </select>
        </label>
        <button class="btn btn-outline-secondary orders-main-btn" id="limpiar-filtros">
            <i class="fas fa-eraser"></i> Limpiar filtros
        </button>
    </section>

    <section class="orders-table-shell">
        <table class="table table-striped table-bordered" id="pedidosTabla">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Direccion</th>
                    <th>Ruta</th>
                    <th>Preventista</th>
                    <th>Fecha</th>
                    <th>Resumen</th>
                    <th>Total</th>
                    <th>Estado</th>
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
        .orders-header, .orders-flow, .orders-filters, .orders-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .orders-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .orders-header span, .orders-flow span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .orders-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .orders-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .orders-header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .orders-main-btn, .order-action-btn {
            border-radius: 8px;
            font-weight: 900;
            min-height: 40px;
        }
        .orders-flow {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .orders-flow article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .orders-flow strong {
            display: block;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .orders-flow small {
            color: #64748b;
            font-weight: 800;
        }
        .orders-filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: end;
        }
        .orders-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .orders-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .orders-table-shell table {
            width: 100% !important;
        }
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            background-color: #0f766e;
            border-radius: 8px;
            box-shadow: none;
        }
        .order-number {
            font-weight: 900;
            color: #0f766e;
            white-space: nowrap;
        }
        .order-client {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }
        .order-client span, .order-summary-mini span {
            color: #64748b;
            font-weight: 700;
        }
        .order-summary-mini {
            display: flex;
            flex-direction: column;
        }
        .order-total {
            color: #166534;
            white-space: nowrap;
        }
        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            padding: 6px 8px;
            font-weight: 900;
            white-space: nowrap;
        }
        .order-status-pending {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .order-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .order-detail-list {
            display: grid;
            gap: 10px;
            text-align: left;
        }
        .order-detail-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }
        .order-detail-item strong {
            display: block;
            color: #17211d;
        }
        .order-detail-meta {
            color: #64748b;
            font-weight: 800;
        }
        @media (max-width: 767.98px) {
            .orders-header, .orders-header-actions { flex-direction: column; }
            .orders-main-btn, .order-action-btn { width: 100%; }
            .orders-flow, .orders-filters { grid-template-columns: 1fr; }
            .order-actions { flex-direction: column; }
            .orders-table-shell { padding: 8px; }
            .orders-table-shell .table td,
            .orders-table-shell .table th {
                font-size: .9rem;
                vertical-align: middle;
            }
            .order-client {
                min-width: 0;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.js"></script>

    <script>
        $(document).ready(function () {
            const tabla = $('#pedidosTabla').DataTable({
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
                    search: 'Buscar pedido',
                    searchPlaceholder: 'Cliente, pedido o ruta'
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                dom: "<'row align-items-center mb-2'<'col-md-12'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.visualizacion') }}",
                    data: function (d) {
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                        d.fecha_pedido = $('#filtro-fecha').val();
                    }
                },
                columns: [
                    { data:'numero_pedido', name: 'numero_pedido' },
                    { data:'cliente', name: 'cliente' },
                    { data:'direccion', name: 'direccion', orderable: false },
                    { data:'ruta', name: 'ruta', orderable: false },
                    { data:'preventista', name: 'preventista', orderable: false },
                    { data:'fecha_pedido', name: 'fecha_pedido' },
                    { data:'resumen', orderable: false, searchable: false },
                    { data:'total_estimado', orderable: false, searchable: false },
                    { data:'estado', orderable: false, searchable: false },
                    { data:'acciones', orderable: false, searchable: false }
                ],
                columnDefs: [
                    { className: 'dtr-control', targets: 0 },
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 7 },
                    { responsivePriority: 4, targets: 9 },
                    { responsivePriority: 10001, targets: [2, 3, 4, 5, 6, 8] },
                ],
            });

            $('.orders-filter').on('change', function () {
                tabla.ajax.reload();
            });

            $('#filas-pagina').on('change', function () {
                tabla.page.len(Number(this.value)).draw();
            });

            $('#limpiar-filtros').on('click', function () {
                $('.orders-filter').val('');
                $('#filas-pagina').val('10');
                tabla.page.len(10);
                tabla.ajax.reload();
            });
        });

        function verPedidoCliente(e) {
            const numeroPedido = $(e).attr('id-numero-pedido');

            $.ajax({
                url: "{{ route('pedidos.administrador.visualizacionPedido', ':id') }}".replace(':id', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Cargando pedido',
                        html: 'Revisando productos reservados...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    const totalPedido = response.pedidos.reduce((sum, item) => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        return sum + ((item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100));
                    }, 0);

                    const detalle = response.pedidos.map(item => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        const total = (item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100);
                        const promo = item.promocion ? `Promocion: ${descuento}% ${item.descripcion_regalo || ''}` : 'Sin promocion';

                        return `<div class="order-detail-item">
                            <strong>${item.nombre_producto}</strong>
                            <div class="order-detail-meta">Codigo: ${item.codigo}</div>
                            <div class="order-detail-meta">Solicitado: ${item.cantidad_pedido} ${item.tipo_venta} | Stock actual: ${item.cantidad_stock} ${item.detalle_cantidad}</div>
                            <div class="order-detail-meta">Precio: Bs ${Number(item.precio_venta).toFixed(2)} | ${promo}</div>
                            <strong>Subtotal: Bs ${total.toFixed(2)}</strong>
                        </div>`;
                    }).join('');

                    Swal.fire({
                        title: `Pedido #${response.numero_pedido}`,
                        html: `<div class="order-detail-list">${detalle}<div class="order-detail-item"><strong>Total estimado: Bs ${totalPedido.toFixed(2)}</strong></div></div>`,
                        width: window.innerWidth <= 700 ? '96%' : '720px',
                        showCloseButton: true,
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el pedido.', 'error');
                }
            });
        }

        $('#btnDespacharPedidos').on('click', function () {
            Swal.fire({
                title: 'Entregar pedidos al repartidor?',
                text: 'Los pedidos pendientes pasaran a despachados. Todavia no se registran como venta.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, entregar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('pedidos.administrador.despacharPedido') }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    beforeSend: function () {
                        Swal.fire({ title: 'Despachando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    },
                    success: function (response) {
                        Swal.fire('Listo', response.message, 'success').then(() => {
                            window.location.href = "{{ route('pedidos.administrador.visualizacionDespachados') }}";
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudieron despachar los pedidos.', 'error');
                    }
                });
            });
        });
    </script>
@stop
