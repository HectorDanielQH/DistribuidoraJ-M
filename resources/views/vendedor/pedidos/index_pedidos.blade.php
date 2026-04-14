@extends('adminlte::page')

@section('title', 'Tomar pedido')

@section('content_header')
    <div class="order-header">
        <div>
            <span class="order-eyebrow">Pedido del cliente</span>
            <h1>Tomar pedido</h1>
            <p>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellidos }}</p>
        </div>
        <a href="{{ route('asignacionvendedor.index') }}" class="btn btn-outline-secondary header-back">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <div class="step-strip">
        <div class="step-item is-active"><span>1</span> Agregar</div>
        <div class="step-item"><span>2</span> Revisar</div>
        <div class="step-item"><span>3</span> Registrar</div>
    </div>
@stop

@section('content')
    <!-- Aviso cliente -->
    <div class="order-page">
    <section class="client-box" aria-label="Cliente del pedido">
        <div class="client-icon"><i class="fas fa-store"></i></div>
        <div>
            <span>Cliente</span>
            <strong>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellidos }}</strong>
            <small>{{ $asignacion->cliente->zona_barrio ?: 'Sin zona' }} - {{ $asignacion->cliente->calle_avenida ?: 'Sin direccion' }}</small>
        </div>
    </section>
    <section class="quick-action-box">
        <div>
            <h2>1. Agrega productos</h2>
            <p>Toca el boton verde, busca el producto y escribe la cantidad.</p>
        </div>
        <button class="btn btn-success btn-add-product" data-toggle="modal" data-target="#modalAgregarProducto">
            <i class="fas fa-plus-circle"></i> Agregar producto
        </button>
    </section>
    <div class="container mt-3 legacy-client-alert">
        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <div>
                Se está creando un pedido para el cliente:
                <strong>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellidos }}</strong>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <x-adminlte-modal id="modalAgregarProducto" title="Agregar producto" size="lg" theme="success" icon="fas fa-cart-plus" v-centered static-backdrop scrollable>
        <div>
            <div class="modal-step-note">
                <strong>Busca y toca un producto.</strong>
                <span>Luego elige la forma de venta y la cantidad.</span>
            </div>
            <div class="row align-items-end" id="bloque-busqueda-producto">
                <div class="col-12 mb-3">
                    <label for="caja-busqueda-producto" class="font-weight-bold search-label">
                        Buscar producto
                    </label>
                    <div class="product-search-box">
                        <i class="fas fa-search"></i>
                        <input type="search" class="form-control" id="caja-busqueda-producto" autocomplete="off" placeholder="Escribe codigo o nombre del producto">
                    </div>
                    <div class="search-help">Escribe al menos 2 letras. Toca el producto para agregarlo al pedido.</div>
                    <div id="resultado-productos-busqueda" class="product-search-results"></div>
                </div>
            </div>
            <div class="row" id="resultado-busqueda"></div>
            <button type="button" class="btn btn-outline-secondary btn-block btn-back-search d-none" id="btn-cambiar-producto">
                <i class="fas fa-arrow-left"></i> Buscar otro producto
            </button>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="mr-auto btn-modal-add" theme="success" label="Agregar al pedido" icon="fas fa-plus" onclick="registrarTabla(this)"/>
            <x-adminlte-button theme="secondary" label="Cerrar" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <div class="container mt-4">
        <div class="card modern-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-2 mb-md-0">
                    <i class="fas fa-shopping-cart"></i> 2. Revisa el pedido
                </h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregarProducto">
                        <i class="fas fa-plus"></i> Agregar producto
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Tabla desktop / Cards móvil -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Cod. Prod</th>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Tipo de Compra</th>
                                <th class="text-right">Precio Unitario</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Descuento</th>
                                <th class="text-right">Sub Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-agregar-producto">
                            <tr>
                                <td colspan="9" class="text-center">Todavia no agregaste productos.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cards para móvil -->
                <div id="lista-cards-productos" class="d-md-none p-3">
                    <!-- Se renderiza en JS para móvil -->
                </div>

                <div class="total-bar px-3 py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total a cobrar: Bs</h5>
                    <h4 class="mb-0 font-weight-bold" id="total-pedido">0.00</h4>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end sticky-actions">
                <button class="btn btn-primary btn-lg" id="btn-registrar-pedido" onclick="registrarPedido()">
                    <i class="fas fa-check-circle"></i> Registrar pedido
                </button>
            </div>
        </div>
    </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />

    <style>
        /* Fuente y colores base */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            background: #f4f6f9;
        }

        /* Hero moderno */
        .hero-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: none;
            position: relative;
            overflow: hidden;
        }
        .hero-container::after {
            content: none;
        }
        .hero-title {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .hero-subtitle {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: .95;
        }
        @media (max-width: 768px) {
            .hero-title { font-size: 1.7rem !important; }
            .hero-subtitle { font-size: .95rem !important; }
        }

        /* Interacciones */
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }

        /* Botones táctiles */
        .btn {
            min-height: 44px;
            border-radius: .6rem;
        }

        /* Cards modernas */
        .modern-card {
            border: none;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: none;
            transition: box-shadow .3s ease;
        }
        .modern-card:hover {
            box-shadow: none;
        }

        /* Tabla compacta */
        .table th, .table td {
            padding: .6rem .5rem;
            vertical-align: middle;
            font-size: .95rem;
        }
        .table img {
            max-height: 50px; max-width: 50px; object-fit: cover; border-radius: 6px;
        }

        /* Total bar */
        .total-bar {
            background: #fdfefe;
            border-top: 1px solid #edf2f7;
        }

        /* Footer sticky en móvil */
        .sticky-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            z-index: 11;
        }

        /* Alertas redondeadas */
        .alert { border-radius: 8px; }

        /* Modal móvil */
        @media (max-width: 576px) {
            .modal-dialog { margin: .5rem; max-width: calc(100% - 1rem); }
            .modal-content { border-radius: 8px; }
            .modal-body { padding: 1rem; }
        }

        /* Grid modal */
        @media (max-width: 768px) {
            .card-header { flex-direction: column; align-items: flex-start !important; }
            .card-header .btn { margin-top: 10px; width: 100%; }
        }

        /* “Cards” de productos para móvil */
        .prod-card {
            border: 1px solid #eef2f7;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
            box-shadow: none;
            margin-bottom: 12px;
        }
        .prod-card .title { font-weight: 700; font-size: 1rem; }
        .badge-soft {
            background: #e6fffa;
            color: #0f766e;
            font-weight: 600;
            padding: .25rem .5rem;
            border-radius: .5rem;
        }

        .stock-live {
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            padding: 10px 12px;
            font-weight: 800;
        }

        .stock-live.stock-low {
            border-color: #fde68a;
            background: #fffbeb;
            color: #854d0e;
        }

        .stock-live.stock-empty {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .inventory-sync {
            color: #64748b;
            font-size: .86rem;
            font-weight: 700;
        }

        .search-label {
            font-size: 1rem;
            color: #17211d;
        }

        .product-search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 2px solid #16a34a;
            border-radius: 8px;
            background: #ffffff;
            padding: 0 12px;
        }

        .product-search-box i {
            color: #15803d;
            font-size: 1.1rem;
        }

        .product-search-box .form-control {
            min-height: 52px;
            border: 0;
            box-shadow: none;
            padding-left: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .search-help {
            margin-top: 6px;
            color: #64748b;
            font-size: .9rem;
            font-weight: 700;
        }

        .product-search-results {
            display: grid;
            gap: 8px;
            margin-top: 12px;
            max-height: 330px;
            overflow-y: auto;
        }

        .product-result {
            display: grid;
            grid-template-columns: 52px 1fr;
            gap: 10px;
            align-items: center;
            width: 100%;
            border: 1px solid #dbe7e2;
            border-radius: 8px;
            background: #ffffff;
            padding: 10px;
            color: #17211d;
            text-align: left;
        }

        .product-result:active,
        .product-result:hover {
            border-color: #15803d;
            background: #f0fdf4;
        }

        .product-result img {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            background: #eef3f1;
        }

        .product-result-name {
            display: block;
            font-weight: 900;
            line-height: 1.25;
        }

        .product-result-meta {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: .85rem;
            font-weight: 700;
        }

        .product-result-stock {
            display: inline-block;
            margin-top: 6px;
            border-radius: 8px;
            background: #e7f6ec;
            color: #15803d;
            padding: 3px 8px;
            font-size: .82rem;
            font-weight: 900;
        }

        .selected-product-box {
            border: 1px solid var(--order-line);
            border-radius: 8px;
            background: var(--order-surface);
            padding: 14px;
        }

        .selected-product-box label {
            color: var(--order-text);
            font-weight: 900;
        }

        .selected-product-box .form-control {
            min-height: 48px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 800;
        }

        .sale-options-table {
            display: none;
        }

        /* Table to card helpers (hidden by default, shown via JS on mobile) */
        .d-md-none .label {
            font-size: .8rem;
            color: #6b7280;
        }

        :root {
            --order-bg: #eef3f1;
            --order-surface: #ffffff;
            --order-line: #dbe7e2;
            --order-text: #17211d;
            --order-muted: #64748b;
            --order-green: #15803d;
            --order-green-soft: #e7f6ec;
            --order-yellow: #facc15;
            --order-red: #dc2626;
        }

        .content-wrapper {
            background: var(--order-bg);
        }

        .legacy-client-alert {
            display: none;
        }

        .order-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            background: var(--order-surface);
            border: 1px solid var(--order-line);
            border-radius: 8px;
        }

        .order-header h1 {
            margin: 0;
            color: var(--order-text);
            font-size: 1.55rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .order-header p {
            margin: 4px 0 0;
            color: var(--order-muted);
            font-weight: 800;
        }

        .order-eyebrow {
            color: var(--order-green);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .header-back,
        .btn-add-product,
        .btn-register,
        .btn-modal-add {
            min-height: 48px;
            border-radius: 8px;
            font-weight: 900;
        }

        .step-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .step-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 8px;
            border: 1px solid var(--order-line);
            border-radius: 8px;
            background: var(--order-surface);
            color: var(--order-muted);
            font-weight: 900;
        }

        .step-item span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 8px;
            background: #edf2f7;
            color: var(--order-text);
        }

        .step-item.is-active {
            border-color: var(--order-green);
            background: var(--order-green-soft);
            color: var(--order-green);
        }

        .order-page {
            display: grid;
            gap: 12px;
            padding-bottom: 96px;
        }

        .client-box,
        .quick-action-box,
        .modern-card {
            border: 1px solid var(--order-line) !important;
            border-radius: 8px !important;
            background: var(--order-surface);
            box-shadow: none !important;
        }

        .client-box {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 12px;
            align-items: center;
            padding: 14px;
        }

        .client-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: var(--order-green-soft);
            color: var(--order-green);
            font-size: 1.35rem;
        }

        .client-box span,
        .client-box small,
        .quick-action-box p,
        .card-title + p {
            color: var(--order-muted);
            font-weight: 800;
        }

        .client-box strong {
            display: block;
            color: var(--order-text);
            font-size: 1.08rem;
            font-weight: 900;
            line-height: 1.25;
        }

        .client-box small {
            display: block;
            margin-top: 3px;
        }

        .quick-action-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
        }

        .quick-action-box h2 {
            margin: 0;
            color: var(--order-text);
            font-size: 1.15rem;
            font-weight: 900;
        }

        .quick-action-box p {
            margin: 4px 0 0;
        }

        .modern-card .card-header {
            background: var(--order-surface) !important;
            color: var(--order-text) !important;
            border-bottom: 1px solid var(--order-line);
            padding: 14px;
        }

        .modern-card .card-title {
            color: var(--order-text);
            font-size: 1.15rem;
            font-weight: 900;
        }

        .modern-card .card-header .btn {
            border-radius: 8px;
            font-weight: 900;
        }

        .total-bar {
            border-top: 1px solid var(--order-line);
            background: #fbfdfc;
        }

        .total-bar h5 {
            color: var(--order-muted);
            font-weight: 900;
        }

        #total-pedido {
            color: var(--order-green);
            font-size: 1.5rem;
        }

        .sticky-actions {
            position: fixed;
            right: 12px;
            bottom: 12px;
            left: 12px;
            z-index: 1000;
            display: flex !important;
            align-items: center;
            justify-content: center !important;
            padding: 10px;
            border: 1px solid var(--order-line);
            border-radius: 8px;
            background: var(--order-surface);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
        }

        .btn-register {
            width: 100%;
            background: var(--order-green);
            border-color: var(--order-green);
            font-size: 1.05rem;
        }

        .btn-back-search {
            min-height: 46px;
            border-radius: 8px;
            font-weight: 900;
        }

        .modal-step-note {
            display: grid;
            gap: 3px;
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid var(--order-line);
            border-radius: 8px;
            background: var(--order-green-soft);
            color: var(--order-text);
        }

        .modal-step-note strong {
            font-size: 1rem;
            font-weight: 900;
        }

        .modal-step-note span {
            color: var(--order-muted);
            font-weight: 800;
        }

        .product-search-box {
            border-radius: 8px;
        }

        .product-result {
            min-height: 82px;
            border-radius: 8px;
        }

        .prod-card {
            border-color: var(--order-line);
            border-radius: 8px;
            box-shadow: none;
        }

        @media (max-width: 575.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .order-header {
                align-items: stretch;
                flex-direction: column;
            }

            .header-back,
            .btn-add-product {
                width: 100%;
            }

            .step-strip {
                gap: 6px;
            }

            .step-item {
                min-height: 46px;
                font-size: .9rem;
            }

            .quick-action-box {
                align-items: stretch;
                flex-direction: column;
            }

            .container {
                max-width: 100%;
                padding-left: 0;
                padding-right: 0;
            }

            .modern-card .card-header {
                align-items: stretch !important;
            }

            .modern-card .card-header .btn {
                width: 100%;
            }

            #lista-cards-productos {
                padding: 12px !important;
            }

            .modal-footer {
                align-items: stretch;
                flex-direction: column;
                gap: 8px;
            }

            .modal-footer .btn,
            .btn-modal-add {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let idProducto_para_tabla="";
        let tablaProductos = [];
        let stockActual = {};
        let productoAbiertoId = null;
        let intervaloStock = null;
        let temporizadorBusqueda = null;

        $(document).ready(function(){
            buscarProductosPedido('');

            // Carga inicial de pedidos pendientes
            Swal.fire({
                title: 'Cargando...',
                text: 'Verificando pedidos pendientes',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                    let idCliente = '{{ $asignacion->cliente->id }}';
                    $.ajax({
                        url: '{{ route("pedidos.vendedor.obtenerPedidosPendientes", ":id") }}'.replace(':id', idCliente),
                        type: 'GET',
                        success: function(response) {
                            Swal.close();
                            response.pedidos.forEach(function(pedido) {
                                let foto = '';
                                if (pedido.foto_producto == null || pedido.foto_producto == '') {
                                    foto = '{{ asset('images/logo_color.webp') }}?v={{ time() }}';
                                } else {
                                    foto = '{{ route("productos.imagen", ":foto") }}?v={{ time() }}'.replace(':foto', pedido.id_producto);
                                }

                                let producto={
                                    'id_producto': pedido.id_producto,
                                    'codigo_producto': pedido.codigo_producto,
                                    'imagen_producto' : foto,
                                    'texto_producto': pedido.nombre_producto,
                                    'id_forma_venta': pedido.id_forma_venta,
                                    'tipo_venta': pedido.tipo_venta,
                                    'precio_venta': pedido.precio_venta,
                                    'cantidad': pedido.cantidad,
                                    'sub_total': ((pedido.precio_venta * pedido.cantidad)-(pedido.precio_venta * pedido.cantidad * (pedido.descripcion_descuento_porcentaje / 100))),
                                    'promocion': pedido.promocion,
                                    'descripcion_regalo': pedido.descripcion_regalo,
                                    'descripcion_descuento_porcentaje': pedido.descripcion_descuento_porcentaje? pedido.descripcion_descuento_porcentaje : '0',
                                };
                                tablaProductos.push(producto);
                            });
                            construirTablaProductos();
                            refrescarStockProductos(true);
                        },
                        error: function() {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener los pedidos pendientes.' });
                        }
                    });
                }
            });

            intervaloStock = setInterval(function () {
                refrescarStockProductos(false);
            }, 7000);

            $('#modalAgregarProducto').on('shown.bs.modal', function () {
                if (!productoAbiertoId) {
                    $('#caja-busqueda-producto').trigger('focus');
                }
            });

            $('#modalAgregarProducto').on('hidden.bs.modal', function () {
                reiniciarModalProducto();
            });
        });

        $('#caja-busqueda-producto').on('input', function () {
            const termino = $(this).val();
            clearTimeout(temporizadorBusqueda);

            temporizadorBusqueda = setTimeout(function () {
                buscarProductosPedido(termino);
            }, 250);
        });

        $(document).on('click', '.product-result', function () {
            cargarProductoPedido($(this).data('id'));
        });

        $('#btn-cambiar-producto').on('click', function () {
            reiniciarModalProducto();
            $('#caja-busqueda-producto').trigger('focus');
        });

        function mostrarBusquedaProducto() {
            $('#bloque-busqueda-producto').removeClass('d-none');
            $('#btn-cambiar-producto').addClass('d-none');
        }

        function ocultarBusquedaProducto() {
            $('#bloque-busqueda-producto').addClass('d-none');
            $('#btn-cambiar-producto').removeClass('d-none');
        }

        function reiniciarModalProducto() {
            idProducto_para_tabla = "";
            productoAbiertoId = null;
            $('#caja-busqueda-producto').val('');
            $('#resultado-busqueda').empty();
            buscarProductosPedido('');
            mostrarBusquedaProducto();
        }

        function buscarProductosPedido(termino) {
            const $resultados = $('#resultado-productos-busqueda');

            if (termino.trim().length > 0 && termino.trim().length < 2) {
                $resultados.html('<div class="alert alert-info mb-0">Escribe al menos 2 letras para buscar.</div>');
                return;
            }

            $resultados.html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Buscando productos...</div>');

            $.ajax({
                url: "{{ route('pedidos.vendedor.buscarProductos') }}",
                type: 'GET',
                data: { q: termino }
            }).done(function (response) {
                const productos = response.productos || [];

                if (!productos.length) {
                    $resultados.html('<div class="alert alert-warning mb-0">No se encontro ningun producto con stock.</div>');
                    return;
                }

                $resultados.html(productos.map(function (producto) {
                    return `
                        <button type="button" class="product-result" data-id="${producto.id}">
                            <img src="${producto.foto}" alt="${escapeHtml(producto.nombre_producto)}">
                            <span>
                                <span class="product-result-name">${escapeHtml(producto.nombre_producto)}</span>
                                <span class="product-result-meta">Cod. ${escapeHtml(producto.codigo)}</span>
                                <span class="product-result-stock">Stock: ${producto.cantidad} ${escapeHtml(producto.detalle_cantidad || '')}</span>
                            </span>
                        </button>
                    `;
                }).join(''));
            }).fail(function () {
                $resultados.html('<div class="alert alert-danger mb-0">No se pudo buscar productos. Intenta nuevamente.</div>');
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function cargarProductoPedido(productoId) {
            idProducto_para_tabla = productoId;
            productoAbiertoId = productoId;
            if (productoId) {
                $.ajax({
                    url: "{{ route('pedidos.vendedor.obtenerProducto',':id') }}".replace(':id', productoId),
                    type: 'GET',
                    success: function(data) {
                        ocultarBusquedaProducto();
                        stockActual[data.producto.id] = {
                            cantidad: Number(data.producto.cantidad || 0),
                            detalle_cantidad: data.producto.detalle_cantidad || ''
                        };

                        let foto = '';
                        if (data.producto.foto_producto == null || data.producto.foto_producto == '') {
                            foto = '{{ asset('images/logo_color.webp') }}?v={{ time() }}';
                        } else {
                            foto = '{{ route("productos.imagen", ":foto") }}?v={{ time() }}'.replace(':foto', data.producto.id);
                        }

                        let opciones = '<option value="">Selecciona una forma de venta</option>';
                        data.formasVenta.forEach(function(forma) {
                            opciones += `<option value="${forma.id}">${forma.tipo_venta}</option>`;
                        });

                        let tabla_formas_venta = `
                            <table class="table table-sm table-bordered mb-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo de Venta</th>
                                        <th class="text-right">Precio de Venta</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        data.formasVenta.forEach(function(forma) {
                            tabla_formas_venta += `
                                <tr>
                                    <td>${forma.tipo_venta}</td>
                                    <td class="text-right">${Number(forma.precio_venta).toFixed(2)} Bs</td>
                                </tr>
                            `;
                        });
                        tabla_formas_venta += `</tbody></table>`;

                        let promoHtml = '';
                        if (data.producto.promocion) {
                            promoHtml = `
                                <div class="mb-2">
                                    <span class="badge badge-success mr-2">
                                        Descuento: ${data.producto.descripcion_descuento_porcentaje || 0}%
                                    </span>
                                    <span class="badge badge-info">
                                        Regalo: ${data.producto.descripcion_regalo || 'N/D'}
                                    </span>
                                </div>
                            `;
                        } else {
                            promoHtml = `<p class="text-muted mb-2">
                                <i class="fas fa-info-circle mr-1"></i> El producto no tiene promoción
                            </p>`;
                        }

                        $('#resultado-busqueda').empty().append(`
                            <div class="col-12">
                                <div class="selected-product-box">
                                    <input type="hidden" id="id-producto-agregar-pedido" value="${data.producto.id}" />
                                    <div>
                                        <div class="d-flex flex-column align-items-center text-center">
                                            <img src="${foto}" class="img-fluid rounded mb-3" id="foto-producto-agregar-pedido"
                                                 alt="${data.producto.nombre_producto}" style="max-height: 160px; object-fit: contain;">
                                            <h5 class="font-weight-bold mb-1" id="id-texto-producto-agregar-pedido">${data.producto.nombre_producto}</h5>
                                            <p class="mb-1"><strong>Codigo:</strong> ${data.producto.codigo}</p>
                                            <div id="stock-producto-texto" class="stock-live mb-2 w-100">
                                                Stock disponible: ${Number(data.producto.cantidad || 0)} ${data.producto.detalle_cantidad || ''}
                                            </div>
                                            <div class="inventory-sync mb-2">
                                                <i class="fas fa-sync-alt"></i> Se actualiza cada pocos segundos
                                            </div>
                                            ${promoHtml}
                                            <div class="w-100 sale-options-table">
                                                <strong>Formas de Venta:</strong>
                                                <div class="table-responsive">${tabla_formas_venta}</div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="form-row">
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="forma-venta-agregar-producto-agregar">
                                                        <i class="fas fa-tag text-success"></i> Como se vende
                                                    </label>
                                                    <select id="forma-venta-agregar-producto-agregar" class="form-control" onchange="actualizarPrecioPedidoAgregar(this)">
                                                        ${opciones}
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="precio-pedido-agregar">
                                                        <i class="fas fa-dollar-sign text-success"></i> Precio
                                                    </label>
                                                    <input id="precio-pedido-agregar" type="text" class="form-control text-right" value="0.00" readonly>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="cantidad-precio-pedido">
                                                        <i class="fas fa-sort-numeric-up text-success"></i> Cuanto lleva
                                                    </label>
                                                    <input type="number" id="cantidad-precio-pedido" class="form-control text-center" value="0" min="1" oninput="calcularCantidadPedidoAgregar(this)">
                                                </div>
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="total-precio-perdido">
                                                        <i class="fas fa-calculator text-success"></i> Total de este producto
                                                    </label>
                                                    <input type="number" id="total-precio-perdido" class="form-control text-right" value="0" min="0" readonly>
                                                </div>

                                                <input type="hidden" id="id-producto-promocion-pedido" value="${data.producto.promocion}" />
                                                <input type="hidden" id="id-producto-promocion-regalo-pedido" value="${data.producto.descripcion_regalo}" />
                                                <input type="hidden" id="id-producto-promocion-descuento-pedido" value="${data.producto.descripcion_descuento_porcentaje}" />
                                                <input type="hidden" id="id-producto-cantidad-pedido" value="${data.producto.cantidad}" />
                                                <input type="hidden" id="id-convalidacion-cantidad" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                        refrescarStockProductos(true);
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el producto' });
                    }
                });
            } else {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Por favor, selecciona un producto.' });
            }
        }

        function actualizarPrecioPedidoAgregar(select) {
            const $inputPrecio = $('#precio-pedido-agregar');
            $inputPrecio.val('').attr('placeholder', 'Cargando...');

            let cant_convalidacion = $('#id-convalidacion-cantidad');
            const url = "{{ route('pedidos.vendedor.obtenerformaventa', ':id') }}".replace(':id', select.value);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $inputPrecio.val(Number(data.precio_venta).toFixed(2));
                    $inputPrecio.attr('placeholder', '');
                    cant_convalidacion.val(data.equivalencia_cantidad || 1);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el precio de la forma de venta' });
                    $inputPrecio.attr('placeholder', 'Error');
                }
            });
        }

        function calcularCantidadPedidoAgregar(e){
            let cantidad = Number(e.value);
            let precioUnitario = parseFloat($('#precio-pedido-agregar').val() || 0);
            let total = $('#total-precio-perdido');
            let promocion = $('#id-producto-promocion-pedido').val();
            let promocionDescuento = parseFloat($('#id-producto-promocion-descuento-pedido').val() || 0);
            let cantidadProducto = parseFloat($('#id-producto-cantidad-pedido').val() || 0);
            let convalidacionCantidad = parseFloat($('#id-convalidacion-cantidad').val() || 1);

            if ((cantidad * convalidacionCantidad) > cantidadProducto && cantidad > 0) {
                Swal.fire({ icon: 'warning', title: 'Stock actualizado', text: `Solo hay ${cantidadProducto} unidades disponibles en inventario.` });
                e.value = cantidad-1;
                cantidad = cantidad-1;
            }

            let precioCalculo = precioUnitario;
            if( promocion === 'true' || promocion === '1'){
                if (promocionDescuento > 0) {
                    precioCalculo = precioUnitario - (precioUnitario * (promocionDescuento / 100));
                }
            }
            total.val((cantidad * precioCalculo).toFixed(2));
        }

        function idsParaActualizarStock() {
            const ids = tablaProductos.map(function (producto) {
                return producto.id_producto;
            });

            if (productoAbiertoId) {
                ids.push(productoAbiertoId);
            }

            return [...new Set(ids.filter(Boolean))];
        }

        function aplicarEstadoStock(productoId) {
            const stock = stockActual[productoId];
            if (!stock) {
                return;
            }

            if (String(productoId) === String(productoAbiertoId)) {
                const cantidad = Number(stock.cantidad || 0);
                const detalle = stock.detalle_cantidad || '';
                const $stock = $('#stock-producto-texto');
                const $cantidadHidden = $('#id-producto-cantidad-pedido');

                $cantidadHidden.val(cantidad);
                $stock
                    .removeClass('stock-low stock-empty')
                    .text(`Stock disponible: ${cantidad} ${detalle}`);

                if (cantidad <= 0) {
                    $stock.addClass('stock-empty').text(`Sin stock disponible (${cantidad} ${detalle})`);
                } else if (cantidad <= 5) {
                    $stock.addClass('stock-low');
                }

                const cantidadActual = Number($('#cantidad-precio-pedido').val() || 0);
                const equivalencia = Number($('#id-convalidacion-cantidad').val() || 1);
                if (cantidadActual > 0 && (cantidadActual * equivalencia) > cantidad) {
                    $('#cantidad-precio-pedido').val(0);
                    $('#total-precio-perdido').val('0.00');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock cambiado',
                        text: 'Otro vendedor actualizo este producto. Vuelve a ingresar la cantidad.'
                    });
                }
            }
        }

        function refrescarStockProductos(mostrarCambios) {
            const ids = idsParaActualizarStock();
            if (!ids.length) {
                return $.Deferred().resolve().promise();
            }

            return $.ajax({
                url: "{{ route('pedidos.vendedor.stockProductos') }}",
                type: 'GET',
                data: { ids: ids.join(',') }
            }).done(function (response) {
                (response.productos || []).forEach(function (producto) {
                    const anterior = stockActual[producto.id]?.cantidad;
                    stockActual[producto.id] = {
                        cantidad: Number(producto.cantidad || 0),
                        detalle_cantidad: producto.detalle_cantidad || ''
                    };

                    aplicarEstadoStock(producto.id);

                    if (mostrarCambios && anterior !== undefined && Number(anterior) !== Number(producto.cantidad)) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `Stock actualizado: ${producto.nombre_producto}`,
                            text: `Disponible: ${producto.cantidad} ${producto.detalle_cantidad || ''}`,
                            timer: 2200,
                            showConfirmButton: false
                        });
                    }
                });
                construirTablaProductos(false);
            });
        }

        function construirTablaProductos(mostrarVacio = true){
            // Desktop tabla
            const $tbody = $('#tabla-agregar-producto');
            $tbody.empty();

            if (tablaProductos.length === 0) {
                $tbody.append(`
                    <tr>
                        <td colspan="9" class="text-center">No hay productos agregados al pedido.</td>
                    </tr>
                `);
            } else {
                tablaProductos.forEach(function(producto, idx) {
                    $tbody.append(`
                        <tr data-index="${idx}">
                            <td>${producto.codigo_producto}</td>
                            <td><img src="${producto.imagen_producto}" alt="${producto.texto_producto}" class="img-fluid"></td>
                            <td>${producto.texto_producto}</td>
                            <td>${producto.tipo_venta}</td>
                            <td class="text-right">${Number(producto.precio_venta).toFixed(2)}</td>
                            <td class="text-center">${producto.cantidad}</td>
                            <td class="text-center">${producto.descripcion_descuento_porcentaje ? `<span class="badge badge-success">${producto.descripcion_descuento_porcentaje}%</span>` : 'N/A'}</td>
                            <td class="text-right">${Number(producto.sub_total).toFixed(2)} Bs.-</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }

            // Móvil cards
            const $cards = $('#lista-cards-productos');
            $cards.empty();
            if (tablaProductos.length) {
                tablaProductos.forEach(function(prod, idx) {
                    $cards.append(`
                        <div class="prod-card" data-index="${idx}">
                            <div class="d-flex align-items-center">
                                <img src="${prod.imagen_producto}" alt="${prod.texto_producto}" style="height:56px;width:56px;object-fit:cover;border-radius:8px;margin-right:12px;">
                                <div class="flex-grow-1">
                                    <div class="title">${prod.texto_producto}</div>
                                    <div class="small text-muted">Cod: ${prod.codigo_producto} • ${prod.tipo_venta}</div>
                                </div>
                                <button class="btn btn-danger" onclick="eliminarProducto(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="mt-2 d-flex justify-content-between">
                                <span class="label">Cant:</span><span class="font-weight-bold">${prod.cantidad}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">P. Unit:</span><span class="font-weight-bold">${Number(prod.precio_venta).toFixed(2)} Bs.-</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">Desc:</span>
                                <span>${prod.descripcion_descuento_porcentaje ? `<span class="badge-soft">${prod.descripcion_descuento_porcentaje}%</span>` : 'N/A'}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">Sub Total:</span><span class="font-weight-bold">${Number(prod.sub_total).toFixed(2)} Bs.-</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">Stock ahora:</span>
                                <span class="font-weight-bold">${stockActual[prod.id_producto]?.cantidad ?? '...'} ${stockActual[prod.id_producto]?.detalle_cantidad ?? ''}</span>
                            </div>
                        </div>
                    `);
                });
            } else {
                if (mostrarVacio) {
                    $cards.html('<div class="text-center text-muted">No hay productos agregados al pedido.</div>');
                }
            }

            // Total
            let totalPedido = 0;
            tablaProductos.forEach(function(prod) {
                totalPedido += parseFloat(prod.sub_total || 0);
            });
            $('#total-pedido').text(totalPedido.toFixed(2));
        }

        function registrarTabla(){
            let productos = @json($productos);
            let productoEncontrado = productos.find(p => p.id == idProducto_para_tabla);

            // Validaciones básicas
            const idForma = $('#forma-venta-agregar-producto-agregar').val();
            const cantidad = Number($('#cantidad-precio-pedido').val() || 0);
            if (!idProducto_para_tabla) {
                return Swal.fire({ icon: 'warning', title: 'Selecciona un producto' });
            }
            if (!idForma) {
                return Swal.fire({ icon: 'warning', title: 'Selecciona la forma de venta' });
            }
            if (!(cantidad > 0)) {
                return Swal.fire({ icon: 'warning', title: 'Cantidad inválida' });
            }

            const stock = stockActual[idProducto_para_tabla]?.cantidad ?? Number($('#id-producto-cantidad-pedido').val() || 0);
            const equivalencia = Number($('#id-convalidacion-cantidad').val() || 1);
            if ((cantidad * equivalencia) > stock) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `Solo hay ${stock} unidades disponibles. El pedido no puede agregarse así.`
                });
            }

            let producto={
                'id_producto': idProducto_para_tabla,
                'codigo_producto': productoEncontrado ? productoEncontrado.codigo : '',
                'imagen_producto' : $('#foto-producto-agregar-pedido').attr('src') || '',
                'texto_producto': $('#id-texto-producto-agregar-pedido').text() || '',
                'id_forma_venta': idForma,
                'tipo_venta': $('#forma-venta-agregar-producto-agregar').find(':selected').text() || '',
                'precio_venta': $('#precio-pedido-agregar').val() || 0,
                'cantidad': cantidad,
                'sub_total': $('#total-precio-perdido').val() || 0,
                'promocion': $('#id-producto-promocion-pedido').val() || '0',
                'descripcion_regalo': $('#id-producto-promocion-regalo-pedido').val() || '',
                'descripcion_descuento_porcentaje': $('#id-producto-promocion-descuento-pedido').val() || '0',
            };

            // Evitar duplicado exacto por código + forma venta (opcional)
            const dup = tablaProductos.find(p => p.codigo_producto === producto.codigo_producto && p.id_forma_venta == producto.id_forma_venta);
            if (dup) {
                return Swal.fire({ icon: 'info', title: 'Ya agregaste este producto con la misma forma de venta' });
            }

            tablaProductos.push(producto);

            // limpiar
            idProducto_para_tabla = "";
            reiniciarModalProducto();

            Swal.fire({
                icon: 'success',
                title: 'Producto agregado',
                text: 'El producto ha sido agregado correctamente.',
                timer: 1400,
                showConfirmButton: false,
            });

            construirTablaProductos();
        }

        function eliminarProducto(e){
            // detectar index según vista
            let $row = $(e).closest('[data-index]');
            if (!$row.length) {
                // Fallback para la tabla (usa el primer td)
                let cod = $(e).closest('tr').find('td:first').text().trim();
                tablaProductos = tablaProductos.filter(function(prod) {
                    return prod.codigo_producto !== cod;
                });
            } else {
                const idx = Number($row.attr('data-index'));
                tablaProductos.splice(idx, 1);
            }

            Swal.fire({
                icon: 'success',
                title: 'Producto eliminado',
                text: 'El producto ha sido eliminado correctamente.',
                timer: 1200,
                showConfirmButton: false,
            });

            construirTablaProductos();
        }

        function registrarPedido() {
            Swal.fire({
                title: 'Confirmar Pedido',
                text: "¿Estás seguro de que deseas registrar este pedido?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, registrar pedido',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (tablaProductos.length === 0) {
                        return Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'No hay productos en el pedido.' });
                    }

                    const $boton = $('#btn-registrar-pedido');
                    $boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando');

                    refrescarStockProductos(false).always(function () {
                        $.ajax({
                            url: "{{ route('pedidos.vendedor.registrarPedido') }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                asignacion_id: '{{ $asignacion->id }}',
                                productos: JSON.stringify(tablaProductos),
                            },
                            beforeSend: function() {
                                Swal.fire({ title: 'Registrando...', text: 'Verificando inventario actualizado', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                            },
                            success: function() {
                                if (intervaloStock) {
                                    clearInterval(intervaloStock);
                                }
                                Swal.fire({ icon: 'success', title: 'Pedido registrado', text: 'Inventario actualizado correctamente.', timer: 1500, showConfirmButton: false })
                                    .then(() => window.location.href = "{{ route('asignacionvendedor.index') }}");
                            },
                            error: function(xhr) {
                                $boton.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Pedido');
                                refrescarStockProductos(true);
                                Swal.fire({ icon: 'error', title: 'No se pudo registrar', text: xhr.responseJSON?.message || 'El inventario cambio. Revisa el pedido e intenta nuevamente.' });
                            }
                        });
                    });
                }
            });
        }
    </script>
@stop
