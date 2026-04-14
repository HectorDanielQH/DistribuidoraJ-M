@extends('adminlte::page')

@section('title', 'Devoluciones')

@section('content_header')
    <div class="return-header">
        <div>
            <span>Repartidor / ajustes</span>
            <h1>Registrar devoluciones</h1>
            <p>Corrige lo que el repartidor no vendio antes de contabilizar y pasar a ventas.</p>
        </div>
        <a href="{{ route('pedidos.administrador.visualizacionDespachados') }}" class="btn btn-outline-secondary return-main-btn">
            <i class="fas fa-arrow-left"></i> Volver a despachados
        </a>
    </div>
@stop

@section('content')
    <section class="return-summary">
        <article>
            <span>En reparto</span>
            <strong>{{ $resumenPedidos['despachados'] ?? 0 }}</strong>
            <small>Disponibles para devolucion</small>
        </article>
        <article>
            <span>Total en reparto</span>
            <strong>Bs {{ number_format($resumenPedidos['despachados_total'] ?? 0, 2, '.', ',') }}</strong>
            <small>Antes de ajustes</small>
        </article>
        <article>
            <span>Pendientes</span>
            <strong>{{ $resumenPedidos['pendientes'] ?? 0 }}</strong>
            <small>Aun no salieron</small>
        </article>
    </section>

    <section class="return-filters">
        <label>
            Ruta
            <select id="filtro-ruta" class="form-control return-filter">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventista
            <select id="filtro-preventista" class="form-control return-filter">
                <option value="">Todos</option>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn btn-outline-secondary return-main-btn" id="limpiar-filtros">
            <i class="fas fa-eraser"></i> Limpiar
        </button>
    </section>

    <section class="return-table-shell">
        <table class="table table-striped table-bordered" id="tablaPedidosContabilizados">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha pedido</th>
                    <th>Fecha despacho</th>
                    <th>Productos</th>
                    <th>Total estimado</th>
                    <th>Preventista</th>
                    <th>Ruta</th>
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
        .return-header, .return-summary, .return-filters, .return-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .return-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .return-header span, .return-summary span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .return-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .return-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .return-main-btn, .return-action {
            border-radius: 8px;
            font-weight: 900;
            min-height: 40px;
        }
        .return-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .return-summary article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .return-summary strong {
            display: block;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .return-summary small {
            color: #64748b;
            font-weight: 800;
        }
        .return-filters {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: end;
        }
        .return-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .return-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .return-table-shell table {
            width: 100% !important;
        }
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            background-color: #0f766e;
            border-radius: 8px;
            box-shadow: none;
        }
        .return-order-number, .return-total {
            font-weight: 900;
            color: #0f766e;
            white-space: nowrap;
        }
        .return-pill {
            display: inline-flex;
            border-radius: 8px;
            padding: 6px 8px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 900;
            white-space: nowrap;
        }
        .return-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .return-detail-list {
            display: grid;
            gap: 10px;
            text-align: left;
        }
        .return-detail-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }
        .return-detail-grid {
            display: grid;
            grid-template-columns: 1fr 110px 150px;
            gap: 8px;
            align-items: end;
        }
        .return-detail-item strong {
            color: #17211d;
        }
        .return-detail-item label {
            color: #475569;
            font-weight: 900;
            margin: 0;
        }
        .return-add-product {
            background: #f8fafc;
        }
        .return-product-results {
            display: grid;
            gap: 8px;
            margin-top: 8px;
            max-height: 240px;
            overflow-y: auto;
        }
        .return-product-option {
            width: 100%;
            border: 1px solid #d7e4df;
            border-radius: 8px;
            background: #ffffff;
            padding: 10px;
            text-align: left;
            font-weight: 800;
        }
        .return-product-option strong,
        .return-selected-product strong {
            display: block;
            color: #17211d;
        }
        .return-product-option span,
        .return-selected-product span {
            display: block;
            color: #64748b;
            font-size: .88rem;
        }
        .return-selected-product {
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            padding: 10px;
            margin-top: 10px;
        }
        .return-add-grid {
            display: grid;
            grid-template-columns: 1fr 110px auto;
            gap: 8px;
            align-items: end;
            margin-top: 10px;
        }
        @media (max-width: 767.98px) {
            .return-header { flex-direction: column; }
            .return-main-btn, .return-action { width: 100%; }
            .return-summary, .return-filters, .return-detail-grid, .return-add-grid { grid-template-columns: 1fr; }
            .return-actions { flex-direction: column; }
            .return-table-shell { padding: 8px; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.js"></script>

    <script>
        let tablaDevoluciones;
        let productosDevolucionCache = {};
        let buscarProductoDevolucionTimer;

        $(document).ready(function () {
            tablaDevoluciones = $('#tablaPedidosContabilizados').DataTable({
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
                    searchPlaceholder: 'Cliente o numero'
                },
                ajax: {
                    url: "{{ route('pedidos.administrador.devolucionPedido') }}",
                    data: function (d) {
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                    }
                },
                columns: [
                    { data: 'numero_pedido', name: 'numero_pedido' },
                    { data: 'cliente', name: 'cliente', orderable: false },
                    { data: 'fecha_pedido', name: 'fecha_pedido' },
                    { data: 'fecha_entrega', name: 'fecha_entrega' },
                    { data: 'items', orderable: false, searchable: false },
                    { data: 'monto_estimado', name: 'monto_estimado', orderable: false },
                    { data: 'preventista', name: 'preventista', orderable: false },
                    { data: 'ruta', name: 'ruta', orderable: false },
                    { data: 'acciones', orderable: false, searchable: false },
                ],
                columnDefs: [
                    { className: 'dtr-control', targets: 0 },
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 8 },
                    { responsivePriority: 4, targets: 5 },
                    { responsivePriority: 10001, targets: [2, 3, 6, 7] },
                ],
                order: [[0, 'desc']],
            });

            $('.return-filter').on('change', function () {
                tablaDevoluciones.ajax.reload();
            });

            $('#limpiar-filtros').on('click', function () {
                $('.return-filter').val('');
                tablaDevoluciones.ajax.reload();
            });

            const pedidoDirecto = new URLSearchParams(window.location.search).get('pedido');
            if (pedidoDirecto) {
                tablaDevoluciones.on('draw', function abrirPedidoDirecto() {
                    tablaDevoluciones.off('draw', abrirPedidoDirecto);
                    abrirGestionDevolucionPorNumero(pedidoDirecto);
                });
            }
        });

        function abrirGestionDevolucion(e) {
            abrirGestionDevolucionPorNumero(e.getAttribute('data-numero-pedido'));
        }

        function abrirGestionDevolucionPorNumero(numeroPedido) {
            $.ajax({
                url: "{{ route('pedidos.administrador.devolucionPedidoDevolucion', ':pedido') }}".replace(':pedido', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({ title: 'Cargando pedido...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    const html = response.pedidos.map(item => {
                        const subtotal = Number(item.cantidad_pedido) * Number(item.precio_venta);
                        return `
                            <div class="return-detail-item" data-id-pedido="${item.id_pedido}" data-id-producto="${item.id_producto}">
                                <div class="return-detail-grid">
                                    <div>
                                        <strong>${item.nombre_producto}</strong>
                                        <div>Codigo: ${item.codigo}</div>
                                        <div>Actual: ${item.cantidad_pedido} ${item.tipo_venta} | Subtotal: Bs ${subtotal.toFixed(2)}</div>
                                    </div>
                                    <label>
                                        Cantidad final
                                        <input type="number" min="0" step="1" class="form-control return-cantidad" value="${item.cantidad_pedido}">
                                    </label>
                                    <label>
                                        Forma venta
                                        <select class="form-control return-forma" data-actual="${item.id_forma_venta}">
                                            <option value="${item.id_forma_venta}" selected>${item.tipo_venta}</option>
                                        </select>
                                    </label>
                                </div>
                                <div class="return-actions mt-2">
                                    <button class="btn btn-success btn-sm return-action" onclick="guardarCantidadDevuelta(${item.id_pedido}, this)">
                                        <i class="fas fa-save"></i> Guardar cantidad
                                    </button>
                                    <button class="btn btn-warning btn-sm return-action" onclick="guardarFormaVenta(${item.id_pedido}, this)">
                                        <i class="fas fa-exchange-alt"></i> Cambiar forma
                                    </button>
                                    <button class="btn btn-danger btn-sm return-action" onclick="eliminarProductoDevuelto(${item.id_pedido})">
                                        <i class="fas fa-trash"></i> Devolver todo
                                    </button>
                                </div>
                            </div>`;
                    }).join('');

                    const agregarProductoHtml = `
                        <div class="return-detail-item return-add-product">
                            <strong>Agregar producto vendido</strong>
                            <div>Usa esto si el repartidor vendio algo mas antes de contabilizar.</div>
                            <label class="mt-2">
                                Buscar producto
                                <input type="search" id="return-buscar-producto" class="form-control" placeholder="Codigo o nombre del producto">
                            </label>
                            <div id="return-product-results" class="return-product-results"></div>
                            <div id="return-selected-product" class="return-selected-product" style="display:none;"></div>
                            <div class="return-add-grid">
                                <label>
                                    Forma venta
                                    <select id="return-add-forma" class="form-control">
                                        <option value="">Primero elige producto</option>
                                    </select>
                                </label>
                                <label>
                                    Cantidad
                                    <input type="number" min="1" step="1" id="return-add-cantidad" class="form-control" value="1">
                                </label>
                                <button type="button" class="btn btn-primary return-action" onclick="agregarProductoAlPedido(${numeroPedido})">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>`;

                    Swal.fire({
                        title: `Gestionar devolucion #${numeroPedido}`,
                        html: `<div class="return-detail-list">${html}<div class="return-detail-item"><strong>Total actual: Bs ${Number(response.total || 0).toFixed(2)}</strong></div>${agregarProductoHtml}</div>`,
                        width: window.innerWidth <= 760 ? '96%' : '860px',
                        showCloseButton: true,
                        confirmButtonText: 'Cerrar',
                        didOpen: function () {
                            $('.return-detail-item').each(function () {
                                const productoId = $(this).data('id-producto');
                                const select = $(this).find('.return-forma');
                                cargarFormasVenta(productoId, select);
                            });
                            $('#return-buscar-producto').on('input', function () {
                                clearTimeout(buscarProductoDevolucionTimer);
                                const termino = this.value;
                                buscarProductoDevolucionTimer = setTimeout(() => buscarProductosParaAgregar(termino), 250);
                            });
                        }
                    }).then(() => {
                        tablaDevoluciones.ajax.reload(null, false);
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el pedido.', 'error');
                }
            });
        }

        function cargarFormasVenta(productoId, select) {
            $.get("{{ route('pedidos.administrador.producto.select.cantidad', ':id') }}".replace(':id', productoId), function (response) {
                const actual = String(select.data('actual'));
                select.empty();
                response.formas_venta.forEach(function (forma) {
                    select.append(`<option value="${forma.id}" ${String(forma.id) === actual ? 'selected' : ''}>${forma.tipo_venta}</option>`);
                });
            });
        }

        function buscarProductosParaAgregar(termino) {
            const contenedor = $('#return-product-results');
            productosDevolucionCache = {};

            if (!termino || termino.trim().length < 2) {
                contenedor.html('<div class="text-muted">Escribe al menos 2 letras o numeros.</div>');
                return;
            }

            contenedor.html('<div class="text-muted">Buscando...</div>');

            $.get("{{ route('pedidos.administrador.devolucionPedido.productos.buscar') }}", { q: termino }, function (response) {
                if (!response.productos.length) {
                    contenedor.html('<div class="text-muted">Sin productos disponibles.</div>');
                    return;
                }

                const html = response.productos.map(function (producto) {
                    productosDevolucionCache[producto.id] = producto;
                    return `
                        <button type="button" class="return-product-option" onclick="seleccionarProductoParaAgregar(${producto.id})">
                            <strong>${escapeHtml(producto.nombre_producto)}</strong>
                            <span>${escapeHtml(producto.codigo)} | Stock: ${Number(producto.cantidad).toFixed(0)} ${escapeHtml(producto.detalle_cantidad || '')}</span>
                        </button>`;
                }).join('');

                contenedor.html(html);
            }).fail(function () {
                contenedor.html('<div class="text-danger">No se pudo buscar productos.</div>');
            });
        }

        function seleccionarProductoParaAgregar(productoId) {
            const producto = productosDevolucionCache[productoId];
            const select = $('#return-add-forma');

            if (!producto) {
                return;
            }

            $('#return-selected-product')
                .show()
                .html(`<strong>${escapeHtml(producto.nombre_producto)}</strong><span>${escapeHtml(producto.codigo)} | Stock actual: ${Number(producto.cantidad).toFixed(0)} ${escapeHtml(producto.detalle_cantidad || '')}</span>`);

            select.empty();
            producto.formas_venta.forEach(function (forma) {
                select.append(`<option value="${forma.id}" data-producto="${producto.id}">${escapeHtml(forma.tipo_venta)} - Bs ${Number(forma.precio_venta).toFixed(2)}</option>`);
            });
        }

        function agregarProductoAlPedido(numeroPedido) {
            const option = $('#return-add-forma option:selected');
            const idProducto = option.data('producto');
            const idFormaVenta = option.val();
            const cantidad = $('#return-add-cantidad').val();

            if (!idProducto || !idFormaVenta) {
                Swal.fire('Falta producto', 'Primero busca y elige el producto que se va a agregar.', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('pedidos.administrador.devolucionPedido.producto.agregar', ':pedido') }}".replace(':pedido', numeroPedido),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_producto: idProducto,
                    id_forma_venta: idFormaVenta,
                    cantidad: cantidad
                },
                success: function (response) {
                    Swal.fire('Listo', response.message, 'success').then(() => {
                        tablaDevoluciones.ajax.reload(null, false);
                        abrirGestionDevolucionPorNumero(numeroPedido);
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo agregar el producto.', 'error');
                }
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

        function guardarCantidadDevuelta(idPedido, boton) {
            const cantidad = $(boton).closest('.return-detail-item').find('.return-cantidad').val();

            $.ajax({
                url: "{{ route('pedidos.administrador.devolucionPedidoDevolucion.cantidad', ':id') }}".replace(':id', idPedido),
                type: 'PUT',
                data: { _token: '{{ csrf_token() }}', cantidad: cantidad },
                success: function (response) {
                    Swal.showValidationMessage('');
                    Swal.fire('Listo', response.message, 'success').then(() => {
                        tablaDevoluciones.ajax.reload(null, false);
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo ajustar la cantidad.', 'error');
                }
            });
        }

        function guardarFormaVenta(idPedido, boton) {
            const tipoVentaId = $(boton).closest('.return-detail-item').find('.return-forma').val();

            $.ajax({
                url: "{{ route('pedidos.administrador.producto.select.actualizar', ':id') }}".replace(':id', idPedido),
                type: 'PUT',
                data: { _token: '{{ csrf_token() }}', tipo_venta_id: tipoVentaId },
                success: function (response) {
                    Swal.fire('Listo', response.message, 'success').then(() => {
                        tablaDevoluciones.ajax.reload(null, false);
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cambiar la forma de venta.', 'error');
                }
            });
        }

        function eliminarProductoDevuelto(idPedido) {
            Swal.fire({
                title: 'Devolver todo este producto?',
                text: 'Se quitara del pedido y volvera al inventario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, devolver',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('pedidos.administrador.producto.eliminar.promocion.total', ':id') }}".replace(':id', idPedido),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        Swal.fire('Listo', response.message, 'success').then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo devolver el producto.', 'error');
                    }
                });
            });
        }

        function anularPedidoDespachado(e) {
            const numeroPedido = e.getAttribute('data-numero-pedido');

            Swal.fire({
                title: `Anular pedido #${numeroPedido}?`,
                text: 'Todos los productos volveran al inventario. Esta accion solo aplica antes de contabilizar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, anular',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('pedidos.administrador.devolucionPedido.anular', ':pedido') }}".replace(':pedido', numeroPedido),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        Swal.fire('Listo', response.message, 'success');
                        tablaDevoluciones.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo anular el pedido.', 'error');
                    }
                });
            });
        }
    </script>
@stop
