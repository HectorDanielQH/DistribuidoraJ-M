@extends('adminlte::page')

@section('title', 'Ventas Mayoristas')

@section('content_header')
    <section class="wholesale-hero">
        <div>
            <span class="hero-kicker">Mayoristas / ventas con precio negociado</span>
            <h1>Panel mayorista</h1>
            <p>Registra ventas mayoristas con formas de venta, precio negociado, control de stock y total automatico. Tambien puedes reabrir y editar registros guardados.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-success wholesale-main-btn" id="btnGuardarPedidoMayorista">
                <i class="fas fa-save"></i> Guardar pedido
            </button>
            <button type="button" class="btn btn-outline-secondary wholesale-main-btn" id="btnNuevoPedidoMayorista">
                <i class="fas fa-plus"></i> Nuevo pedido
            </button>
        </div>
    </section>
@stop

@section('content')
    <section class="wholesale-section">
        <div class="step-grid">
            <article class="step-card">
                <span>Paso 1</span>
                <strong>Selecciona el cliente</strong>
                <small>Busca por nombre, apellido, codigo o celular.</small>
            </article>
            <article class="step-card">
                <span>Paso 2</span>
                <strong>Agrega productos</strong>
                <small>Elige la forma de venta, cantidad y precio negociado.</small>
            </article>
            <article class="step-card">
                <span>Paso 3</span>
                <strong>Guarda la venta</strong>
                <small>El stock se descuenta y el total se recalcula al instante.</small>
            </article>
        </div>
    </section>

    <section class="wholesale-layout">
        <article class="wholesale-panel">
            <div class="panel-heading">
                <div>
                    <span>Cliente</span>
                    <h2>Busca y selecciona el cliente mayorista</h2>
                </div>
                <div class="status-chip" id="estadoPedidoMayorista">Nueva venta</div>
            </div>

            <div class="search-block">
                <label for="buscarClienteMayorista">Buscar cliente</label>
                <input type="text" class="form-control" id="buscarClienteMayorista" placeholder="Ej: Maria, 76543210, CL-001">
                <div class="search-results" id="resultadoClientesMayorista"></div>
            </div>

            <div class="selected-client-card d-none" id="clienteSeleccionadoCard">
                <div>
                    <span class="client-label">Cliente seleccionado</span>
                    <h3 id="clienteNombreSeleccionado">Sin cliente</h3>
                    <p id="clienteMetaSeleccionado">Selecciona un cliente para continuar.</p>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCambiarCliente">
                    <i class="fas fa-exchange-alt"></i> Cambiar
                </button>
            </div>

            <hr class="section-divider">

            <div class="panel-heading">
                <div>
                    <span>Producto</span>
                    <h2>Busca un producto y agrega una linea</h2>
                </div>
            </div>

            <div class="search-block">
                <label for="buscarProductoMayorista">Buscar producto</label>
                <input type="text" class="form-control" id="buscarProductoMayorista" placeholder="Ej: Galletas, PROD-001">
                <div class="search-results" id="resultadoProductosMayorista"></div>
            </div>

            <div class="selected-product-card d-none" id="productoMayoristaCard">
                <div class="product-head">
                    <img src="{{ asset('images/logo_color.webp') }}" alt="Producto" id="productoMayoristaFoto">
                    <div>
                        <span class="client-label">Producto seleccionado</span>
                        <h3 id="productoMayoristaNombre">Sin producto</h3>
                        <p id="productoMayoristaMeta">Selecciona un producto para ver sus formas de venta.</p>
                        <div class="stock-chip" id="productoMayoristaStock">Stock: 0</div>
                    </div>
                </div>

                <div class="form-grid">
                    <label>
                        Forma de venta
                        <select id="formaVentaMayorista" class="form-control"></select>
                    </label>
                    <label>
                        Precio base
                        <input type="text" id="precioBaseMayorista" class="form-control text-right" readonly value="0.00">
                    </label>
                    <label>
                        Cantidad
                        <input type="number" id="cantidadMayorista" class="form-control text-center" min="1" value="1">
                    </label>
                    <label>
                        Precio negociado
                        <input type="number" id="precioNegociadoMayorista" class="form-control text-right" min="0.01" step="0.01" value="0.00">
                    </label>
                    <label>
                        Disponible en esta forma
                        <input type="text" id="unidadesRealesMayorista" class="form-control text-center" readonly value="0">
                    </label>
                    <label>
                        Subtotal
                        <input type="text" id="subtotalMayorista" class="form-control text-right" readonly value="0.00">
                    </label>
                </div>

                <div class="inline-actions">
                    <button type="button" class="btn btn-primary wholesale-main-btn" id="btnAgregarLineaMayorista">
                        <i class="fas fa-plus-circle"></i> Agregar a la venta
                    </button>
                    <button type="button" class="btn btn-outline-secondary wholesale-main-btn" id="btnLimpiarProductoMayorista">
                        <i class="fas fa-times"></i> Quitar producto
                    </button>
                </div>
            </div>
        </article>

        <aside class="wholesale-side">
            <section class="wholesale-panel">
                <div class="panel-heading">
                    <div>
                        <span>Resumen</span>
                        <h2>Totales del pedido</h2>
                    </div>
                </div>

                <div class="kpi-stack">
                    <article class="kpi-card">
                        <span>Lineas</span>
                        <strong id="kpiLineasMayorista">0</strong>
                    </article>
                    <article class="kpi-card">
                        <span>Unidades reales</span>
                        <strong id="kpiUnidadesMayorista">0</strong>
                    </article>
                    <article class="kpi-card">
                        <span>Total de la venta</span>
                        <strong id="kpiTotalMayorista">Bs 0.00</strong>
                    </article>
                </div>
            </section>

            <section class="wholesale-panel">
                <div class="panel-heading">
                    <div>
                        <span>Pedido actual</span>
                        <h2>Lineas agregadas</h2>
                    </div>
                </div>

                <div class="table-responsive desktop-only">
                    <table class="table table-striped table-bordered wholesale-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Forma</th>
                                <th>Precio</th>
                                <th>Cant.</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tablaLineasMayorista"></tbody>
                    </table>
                </div>

                <div class="mobile-cards" id="cardsLineasMayorista"></div>
            </section>
        </aside>
    </section>

    <section class="wholesale-section">
        <div class="panel-heading">
            <div>
                <span>Pedidos pendientes</span>
                <h2>Ventas mayoristas registradas</h2>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tablaPedidosMayorista" class="table table-striped table-bordered wholesale-table w-100">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Items</th>
                        <th>Unidades</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <style>
        .content-wrapper { background: #eef3f1; }
        .wholesale-hero, .wholesale-section, .wholesale-panel {
            background: #fff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .wholesale-hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 16px;
        }
        .hero-kicker, .panel-heading span, .client-label {
            color: #15803d;
            display: block;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .wholesale-hero h1, .panel-heading h2, .selected-client-card h3, .selected-product-card h3 {
            color: #17211d;
            margin: 0;
            font-weight: 900;
        }
        .wholesale-hero p, .selected-client-card p, .selected-product-card p {
            color: #64748b;
            margin: 6px 0 0;
            font-weight: 700;
        }
        .hero-actions, .inline-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .wholesale-main-btn, .wholesale-action-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
        }
        .wholesale-section, .wholesale-panel { padding: 18px; margin-bottom: 16px; }
        .step-grid, .form-grid, .kpi-stack {
            display: grid;
            gap: 12px;
        }
        .step-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .step-card, .kpi-card {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px;
        }
        .step-card span, .kpi-card span { color: #64748b; font-size: .82rem; font-weight: 800; display:block; }
        .step-card strong, .kpi-card strong { color: #17211d; font-weight: 900; display:block; margin-top:4px; }
        .step-card small { color: #64748b; font-weight: 700; display:block; margin-top: 6px; }
        .wholesale-layout {
            display: grid;
            grid-template-columns: 1.3fr .9fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .wholesale-side { display: grid; gap: 16px; }
        .panel-heading {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }
        .status-chip, .stock-chip {
            background: #edf7f1;
            border: 1px solid #cbe3d4;
            border-radius: 999px;
            color: #166534;
            font-size: .82rem;
            font-weight: 900;
            padding: 6px 10px;
            white-space: nowrap;
        }
        .search-block label, .form-grid label { color: #475569; font-weight: 900; margin: 0; }
        .search-results {
            display: grid;
            gap: 8px;
            margin-top: 10px;
            max-height: 280px;
            overflow-y: auto;
        }
        .search-result-btn {
            align-items: center;
            background: #fff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
            display: flex;
            gap: 12px;
            padding: 10px 12px;
            text-align: left;
            width: 100%;
        }
        .search-result-btn img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d7e4df;
        }
        .search-result-btn strong, .search-result-btn span { display:block; }
        .search-result-btn span { color: #64748b; font-size: .86rem; font-weight: 700; }
        .selected-client-card, .selected-product-card {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 16px;
            background: #f9fbfa;
        }
        .selected-client-card {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: start;
        }
        .product-head {
            display: flex;
            gap: 14px;
            align-items: start;
            margin-bottom: 14px;
        }
        .product-head img {
            width: 84px;
            height: 84px;
            border-radius: 8px;
            border: 1px solid #d7e4df;
            object-fit: cover;
        }
        .form-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 14px; }
        .section-divider { margin: 18px 0; border-color: #e4ece9; }
        .kpi-stack { grid-template-columns: 1fr; }
        .wholesale-table { width: 100% !important; }
        .mobile-cards { display: none; }
        .line-card {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }
        .line-card strong { display:block; color:#17211d; }
        .line-card span { display:block; color:#64748b; font-weight:700; margin-top:4px; }
        @media (max-width: 1199.98px) {
            .wholesale-layout, .step-grid, .form-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 767.98px) {
            .wholesale-hero, .hero-actions, .panel-heading, .selected-client-card { flex-direction: column; align-items: stretch; }
            .wholesale-main-btn { width: 100%; }
            .desktop-only { display:none; }
            .mobile-cards { display:block; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let clienteSeleccionado = null;
        let productoSeleccionado = null;
        let pedidoActual = null;
        let lineasPedido = [];
        let stockActual = {};
        let debounceCliente = null;
        let debounceProducto = null;
        let intervaloStock = null;
        let tablaPedidos = null;
        const ventaInicial = @json($ventaInicial);

        function money(value) {
            return 'Bs ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function actualizarResumenPedido() {
            const lineas = lineasPedido.length;
            const unidades = lineasPedido.reduce((sum, item) => sum + (Number(item.cantidad || 0) * Number(item.equivalencia_cantidad || 1)), 0);
            const total = lineasPedido.reduce((sum, item) => sum + Number(item.sub_total || 0), 0);

            $('#kpiLineasMayorista').text(lineas);
            $('#kpiUnidadesMayorista').text(unidades);
            $('#kpiTotalMayorista').text(money(total));

            const $tbody = $('#tablaLineasMayorista');
            const $cards = $('#cardsLineasMayorista');
            $tbody.empty();
            $cards.empty();

            if (!lineas) {
                $tbody.html('<tr><td colspan="6" class="text-center">Todavia no agregaste productos al pedido.</td></tr>');
                $cards.html('<div class="text-center text-muted">Todavia no agregaste productos al pedido.</div>');
                return;
            }

            lineasPedido.forEach(function (item, index) {
                $tbody.append(`
                    <tr>
                        <td>${escapeHtml(item.texto_producto)}</td>
                        <td>${escapeHtml(item.tipo_venta)}</td>
                        <td class="text-right">${Number(item.precio_venta).toFixed(2)}</td>
                        <td class="text-center">${item.cantidad}</td>
                        <td class="text-right">${Number(item.sub_total).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarLineaMayorista(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $cards.append(`
                    <div class="line-card">
                        <strong>${escapeHtml(item.texto_producto)}</strong>
                        <span>${escapeHtml(item.tipo_venta)}</span>
                        <span>Precio: ${money(item.precio_venta)}</span>
                        <span>Cantidad: ${item.cantidad}</span>
                        <span>Subtotal: ${money(item.sub_total)}</span>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="eliminarLineaMayorista(${index})">
                            <i class="fas fa-trash"></i> Quitar
                        </button>
                    </div>
                `);
            });
        }

        function limpiarProductoSeleccionado() {
            productoSeleccionado = null;
            $('#productoMayoristaCard').addClass('d-none');
            $('#buscarProductoMayorista').val('');
            $('#resultadoProductosMayorista').empty();
            $('#formaVentaMayorista').empty().append('<option value="">Selecciona una forma de venta</option>');
            $('#precioBaseMayorista').val('0.00');
            $('#precioNegociadoMayorista').val('0.00').data('editado-manual', false);
            $('#cantidadMayorista').val(1);
            $('#unidadesRealesMayorista').val(0);
            $('#subtotalMayorista').val('0.00');
        }

        function actualizarStockRelacionadoForma() {
            const $selected = $('#formaVentaMayorista').find(':selected');
            const equivalencia = Number($selected.data('equivalencia') || 1);
            const stock = Number(productoSeleccionado?.cantidad || 0);
            const disponible = equivalencia > 0 ? Math.floor(stock / equivalencia) : 0;
            const tipoVenta = $selected.text() && $selected.val() ? $selected.text() : 'forma';

            $('#unidadesRealesMayorista').val(`${disponible} ${tipoVenta}`);
        }

        function limpiarPedidoMayorista() {
            pedidoActual = null;
            lineasPedido = [];
            clienteSeleccionado = null;
            $('#estadoPedidoMayorista').text('Nueva venta');
            $('#clienteSeleccionadoCard').addClass('d-none');
            $('#buscarClienteMayorista').val('');
            $('#resultadoClientesMayorista').empty();
            limpiarProductoSeleccionado();
            actualizarResumenPedido();
        }

        function seleccionarCliente(cliente) {
            clienteSeleccionado = cliente;
            $('#clienteNombreSeleccionado').text(cliente.nombre);
            $('#clienteMetaSeleccionado').text(`${cliente.codigo_cliente} | ${cliente.celular} | ${cliente.ruta} | ${cliente.direccion || 'Sin direccion'}`);
            $('#clienteSeleccionadoCard').removeClass('d-none');
            $('#resultadoClientesMayorista').empty();
            $('#buscarClienteMayorista').val('');
        }

        function buscarClientesMayorista(termino) {
            const $resultados = $('#resultadoClientesMayorista');
            if (!termino.trim()) {
                $resultados.empty();
                return;
            }

            $resultados.html('<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin"></i> Buscando clientes...</div>');

            $.get("{{ route('mayoristas.clientes.buscar') }}", { q: termino }, function (response) {
                const clientes = response.clientes || [];
                if (!clientes.length) {
                    $resultados.html('<div class="alert alert-warning mb-0">No se encontro ningun cliente.</div>');
                    return;
                }

                $resultados.html(clientes.map(function (cliente) {
                    return `
                        <button type="button" class="search-result-btn cliente-result-btn"
                            data-id="${cliente.id}"
                            data-codigo="${escapeHtml(cliente.codigo_cliente)}"
                            data-nombre="${escapeHtml(cliente.nombre)}"
                            data-celular="${escapeHtml(cliente.celular)}"
                            data-direccion="${escapeHtml(cliente.direccion || '')}"
                            data-ruta="${escapeHtml(cliente.ruta)}">
                            <div>
                                <strong>${escapeHtml(cliente.nombre)}</strong>
                                <span>${escapeHtml(cliente.codigo_cliente)} | ${escapeHtml(cliente.celular)}</span>
                                <span>${escapeHtml(cliente.ruta)} | ${escapeHtml(cliente.direccion || 'Sin direccion')}</span>
                            </div>
                        </button>
                    `;
                }).join(''));
            }).fail(function () {
                $resultados.html('<div class="alert alert-danger mb-0">No se pudo buscar clientes.</div>');
            });
        }

        function buscarProductosMayorista(termino) {
            const $resultados = $('#resultadoProductosMayorista');
            if (!termino.trim()) {
                $resultados.empty();
                return;
            }

            $resultados.html('<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin"></i> Buscando productos...</div>');

            $.get("{{ route('mayoristas.productos.buscar') }}", { q: termino }, function (response) {
                const productos = response.productos || [];
                if (!productos.length) {
                    $resultados.html('<div class="alert alert-warning mb-0">No se encontro ningun producto con stock.</div>');
                    return;
                }

                $resultados.html(productos.map(function (producto) {
                    return `
                        <button type="button" class="search-result-btn" onclick="cargarProductoMayorista(${producto.id})">
                            <img src="${producto.foto}" alt="${escapeHtml(producto.nombre_producto)}">
                            <div>
                                <strong>${escapeHtml(producto.nombre_producto)}</strong>
                                <span>${escapeHtml(producto.codigo)}</span>
                                <span>Stock: ${producto.cantidad} ${escapeHtml(producto.detalle_cantidad || '')}</span>
                            </div>
                        </button>
                    `;
                }).join(''));
            }).fail(function () {
                $resultados.html('<div class="alert alert-danger mb-0">No se pudo buscar productos.</div>');
            });
        }

        function recalcularLineaMayorista() {
            const cantidad = Number($('#cantidadMayorista').val() || 0);
            const precio = Number($('#precioNegociadoMayorista').val() || 0);
            const equivalencia = Number($('#formaVentaMayorista').find(':selected').data('equivalencia') || 1);
            const stock = Number(productoSeleccionado?.cantidad || 0);
            const unidades = cantidad * equivalencia;
            actualizarStockRelacionadoForma();

            if (unidades > stock && cantidad > 0) {
                $('#cantidadMayorista').val(0);
                actualizarStockRelacionadoForma();
                $('#subtotalMayorista').val('0.00');
                Swal.fire('Stock insuficiente', `Solo hay ${stock} ${productoSeleccionado.detalle_cantidad || 'unidades'} disponibles para este producto.`, 'warning');
                return;
            }

            $('#subtotalMayorista').val((cantidad * precio).toFixed(2));
        }

        function cargarProductoMayorista(productoId) {
            $.get("{{ route('mayoristas.productos.detalle', ':id') }}".replace(':id', productoId), function (response) {
                productoSeleccionado = response.producto;
                stockActual[productoSeleccionado.id] = Number(productoSeleccionado.cantidad || 0);

                $('#productoMayoristaFoto').attr('src', productoSeleccionado.foto_producto
                    ? "{{ route('productos.imagen', ':id') }}".replace(':id', productoSeleccionado.id)
                    : "{{ asset('images/logo_color.webp') }}");
                $('#productoMayoristaNombre').text(productoSeleccionado.nombre_producto);
                $('#productoMayoristaMeta').text(`${productoSeleccionado.codigo} | ${productoSeleccionado.descripcion_producto || 'Producto disponible para pedido mayorista'}`);
                $('#productoMayoristaStock').text(`Stock: ${productoSeleccionado.cantidad} ${productoSeleccionado.detalle_cantidad || ''}`);
                $('#productoMayoristaCard').removeClass('d-none');
                $('#resultadoProductosMayorista').empty();
                $('#buscarProductoMayorista').val('');

                const $formas = $('#formaVentaMayorista');
                $formas.empty().append('<option value="">Selecciona una forma de venta</option>');
                (response.formasVenta || []).forEach(function (forma) {
                    $formas.append(`<option value="${forma.id}" data-precio="${forma.precio_venta}" data-equivalencia="${forma.equivalencia_cantidad}">${forma.tipo_venta}</option>`);
                });

                $('#precioBaseMayorista').val('0.00');
                $('#precioNegociadoMayorista').val('0.00').data('editado-manual', false);
                $('#cantidadMayorista').val(1);
                $('#unidadesRealesMayorista').val('0');
                $('#subtotalMayorista').val('0.00');

                if ((response.formasVenta || []).length) {
                    $formas.val(String(response.formasVenta[0].id)).trigger('change');
                }
            });
        }

        function agregarLineaMayorista() {
            if (!clienteSeleccionado) {
                Swal.fire('Cliente requerido', 'Primero selecciona el cliente para el pedido.', 'warning');
                return;
            }

            if (!productoSeleccionado) {
                Swal.fire('Producto requerido', 'Primero selecciona un producto.', 'warning');
                return;
            }

            const formaId = $('#formaVentaMayorista').val();
            const formaTexto = $('#formaVentaMayorista').find(':selected').text();
            const equivalencia = Number($('#formaVentaMayorista').find(':selected').data('equivalencia') || 1);
            const cantidad = Number($('#cantidadMayorista').val() || 0);
            const precio = Number($('#precioNegociadoMayorista').val() || 0);

            if (!formaId) {
                Swal.fire('Forma de venta requerida', 'Selecciona la forma de venta del producto.', 'warning');
                return;
            }

            if (cantidad <= 0 || precio <= 0) {
                Swal.fire('Datos incompletos', 'Ingresa una cantidad y un precio negociado validos.', 'warning');
                return;
            }

            lineasPedido.push({
                id_producto: productoSeleccionado.id,
                codigo_producto: productoSeleccionado.codigo,
                texto_producto: productoSeleccionado.nombre_producto,
                id_forma_venta: Number(formaId),
                tipo_venta: formaTexto,
                precio_venta: precio,
                cantidad: cantidad,
                equivalencia_cantidad: equivalencia,
                sub_total: Number((cantidad * precio).toFixed(2))
            });

            actualizarResumenPedido();
            limpiarProductoSeleccionado();
            Swal.fire({ icon: 'success', title: 'Linea agregada', timer: 1200, showConfirmButton: false });
        }

        function eliminarLineaMayorista(index) {
            lineasPedido.splice(index, 1);
            actualizarResumenPedido();
        }

        function guardarPedidoMayorista() {
            if (!clienteSeleccionado) {
                Swal.fire('Cliente requerido', 'Debes seleccionar un cliente.', 'warning');
                return;
            }

            if (!lineasPedido.length) {
                Swal.fire('Sin productos', 'Debes agregar al menos una linea al pedido.', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('mayoristas.pedidos.guardar') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cliente_id: clienteSeleccionado.id,
                    numero_pedido: pedidoActual,
                    productos: JSON.stringify(lineasPedido),
                },
                beforeSend: function () {
                    Swal.fire({ title: 'Guardando venta...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    Swal.fire({ icon: 'success', title: 'Venta guardada', text: response.message, timer: 1400, showConfirmButton: false });
                    limpiarPedidoMayorista();
                    tablaPedidos.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire('No se pudo guardar', xhr.responseJSON?.message || 'Revisa la venta e intenta nuevamente.', 'error');
                }
            });
        }

        function cargarPedidoMayorista(numeroPedido) {
            $.get("{{ route('mayoristas.pedidos.detalle', ':numero') }}".replace(':numero', numeroPedido), function (response) {
                pedidoActual = response.numero_pedido;
                $('#estadoPedidoMayorista').text(`Editando venta #${pedidoActual}`);
                seleccionarCliente(response.cliente);
                lineasPedido = response.items || [];
                actualizarResumenPedido();
                $('html, body').animate({ scrollTop: 0 }, 250);
            }).fail(function () {
                Swal.fire('No disponible', 'No se pudo abrir ese pedido para edicion.', 'error');
            });
        }

        function refrescarStockMayorista() {
            const ids = [...new Set(lineasPedido.map(item => item.id_producto).concat(productoSeleccionado ? [productoSeleccionado.id] : []))];
            if (!ids.length) {
                return;
            }

            $.get("{{ route('mayoristas.productos.stock') }}", { ids: ids.join(',') }, function (response) {
                (response.productos || []).forEach(function (producto) {
                    stockActual[producto.id] = Number(producto.cantidad || 0);
                    if (productoSeleccionado && Number(productoSeleccionado.id) === Number(producto.id)) {
                        productoSeleccionado.cantidad = Number(producto.cantidad || 0);
                        $('#productoMayoristaStock').text(`Stock: ${producto.cantidad} ${producto.detalle_cantidad || ''}`);
                        actualizarStockRelacionadoForma();
                    }
                });
            });
        }

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            actualizarResumenPedido();

            $('#buscarClienteMayorista').on('input', function () {
                clearTimeout(debounceCliente);
                const termino = $(this).val();
                debounceCliente = setTimeout(function () {
                    buscarClientesMayorista(termino);
                }, 250);
            });

            $('#buscarProductoMayorista').on('input', function () {
                clearTimeout(debounceProducto);
                const termino = $(this).val();
                debounceProducto = setTimeout(function () {
                    buscarProductosMayorista(termino);
                }, 250);
            });

            $('#btnCambiarCliente').on('click', function () {
                clienteSeleccionado = null;
                $('#clienteSeleccionadoCard').addClass('d-none');
            });

            $('#resultadoClientesMayorista').on('click', '.cliente-result-btn', function () {
                seleccionarCliente({
                    id: $(this).data('id'),
                    codigo_cliente: $(this).data('codigo'),
                    nombre: $(this).data('nombre'),
                    celular: $(this).data('celular'),
                    direccion: $(this).data('direccion'),
                    ruta: $(this).data('ruta'),
                });
            });

            $('#formaVentaMayorista').on('change', function () {
                const $selected = $(this).find(':selected');
                const precioBase = Number($selected.data('precio') || 0);
                $('#precioBaseMayorista').val(precioBase.toFixed(2));
                if (!$('#precioNegociadoMayorista').data('editado-manual') || Number($('#precioNegociadoMayorista').val() || 0) <= 0) {
                    $('#precioNegociadoMayorista').val(precioBase.toFixed(2));
                }
                recalcularLineaMayorista();
            });

            $('#cantidadMayorista').on('input', recalcularLineaMayorista);
            $('#precioNegociadoMayorista').on('input', function () {
                $(this).data('editado-manual', true);
                recalcularLineaMayorista();
            });
            $('#btnAgregarLineaMayorista').on('click', agregarLineaMayorista);
            $('#btnLimpiarProductoMayorista').on('click', limpiarProductoSeleccionado);
            $('#btnGuardarPedidoMayorista').on('click', guardarPedidoMayorista);
            $('#btnNuevoPedidoMayorista').on('click', limpiarPedidoMayorista);

            tablaPedidos = $('#tablaPedidosMayorista').DataTable({
                ajax: "{{ route('mayoristas.pedidos.listado') }}",
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                columns: [
                    { data: 'numero_pedido' },
                    { data: 'fecha_pedido' },
                    { data: 'cliente' },
                    { data: 'celular' },
                    { data: 'items' },
                    { data: 'unidades' },
                    { data: 'total', render: (data) => money(data) },
                    { data: 'acciones', orderable: false, searchable: false }
                ],
                language: { url: '/i18n/es-ES.json' }
            });

            $('#tablaPedidosMayorista').on('click', '.btn-editar-mayorista', function () {
                cargarPedidoMayorista($(this).data('pedido'));
            });

            $('#tablaPedidosMayorista').on('error.dt', function (e, settings, techNote, message) {
                console.error('DataTables:', message);
                Swal.fire('Error', 'No se pudo cargar la lista de pedidos mayoristas.', 'error');
            });

            intervaloStock = setInterval(refrescarStockMayorista, 7000);

            if (ventaInicial) {
                cargarPedidoMayorista(ventaInicial);
            }
        });
    </script>
@stop
