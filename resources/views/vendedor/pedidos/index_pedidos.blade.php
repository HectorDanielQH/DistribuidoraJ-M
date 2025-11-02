@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="hero-container container py-4">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2 hero-title">
                <i class="fas fa-boxes mr-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ml-2"></i>
            </h1>
            <span class="text-white hero-subtitle">
                Panel de Pedidos - Vendedor
            </span>
        </div>
    </div>
@stop

@section('content')
    <!-- Aviso cliente -->
    <div class="container mt-3">
        <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <div>
                Se está creando un pedido para el cliente:
                <strong>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellidos }}</strong>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <x-adminlte-modal id="modalAgregarProducto" title="Agregar Pedido" size="lg" theme="teal" icon="fas fa-shopping-cart" v-centered static-backdrop scrollable>
        <div>
            <div class="row align-items-end">
                <div class="col-md-8 col-sm-12 mb-3">
                    <label for="caja-busqueda-producto" class="font-weight-bold">
                        Ingresa el código o alguna coincidencia
                    </label>
                    <select class="form-control" id="caja-busqueda-producto" name="caja-busqueda-producto" placeholder="Buscar producto por código o nombre">
                        <option value="">Selecciona un producto</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre_producto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-sm-12 mb-3 d-flex justify-content-md-end">
                    <button class="btn btn-primary btn-block btn-lg" id="btn-buscar-producto">
                        <i class="fas fa-search"></i> Buscar Producto
                    </button>
                </div>
            </div>
            <div class="row" id="resultado-busqueda"></div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="mr-auto" theme="success" label="Agregar" onclick="registrarTabla(this)"/>
            <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <div class="container mt-4">
        <div class="card modern-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-2 mb-md-0">
                    <i class="fas fa-shopping-cart"></i> Agregar Productos al Pedido
                </h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregarProducto">
                        <i class="fas fa-plus"></i> Agregar Producto
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
                                <td colspan="9" class="text-center">No hay productos agregados al pedido.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cards para móvil -->
                <div id="lista-cards-productos" class="d-md-none p-3">
                    <!-- Se renderiza en JS para móvil -->
                </div>

                <div class="total-bar px-3 py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total del Pedido: Bs.-</h5>
                    <h4 class="mb-0 font-weight-bold" id="total-pedido">0.00</h4>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end sticky-actions">
                <button class="btn btn-primary btn-lg" onclick="registrarPedido()">
                    <i class="fas fa-save"></i> Registrar Pedido
                </button>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Fuente y colores base */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            background: #f4f6f9;
        }

        /* Hero moderno */
        .hero-container {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.18);
            position: relative;
            overflow: hidden;
        }
        .hero-container::after {
            content: "";
            position: absolute;
            top: -20%;
            right: -10%;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,0.08);
            filter: blur(8px);
            border-radius: 50%;
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

        /* Select2 alto táctil */
        .select2-container--default .select2-selection--single {
            height: 48px;
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
            padding-left: 12px;
            font-size: 1rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        /* Botones táctiles */
        .btn {
            min-height: 44px;
            border-radius: .6rem;
        }

        /* Cards modernas */
        .modern-card {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            transition: box-shadow .3s ease;
        }
        .modern-card:hover {
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
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
        .alert { border-radius: 10px; }

        /* Modal móvil */
        @media (max-width: 576px) {
            .modal-dialog { margin: .5rem; max-width: calc(100% - 1rem); }
            .modal-content { border-radius: 12px; }
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
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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

        /* Table to card helpers (hidden by default, shown via JS on mobile) */
        .d-md-none .label {
            font-size: .8rem;
            color: #6b7280;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let idProducto_para_tabla="";
        let tablaProductos = [];

        $(document).ready(function(){
            $('#caja-busqueda-producto').select2({
                placeholder: 'Buscar producto por código o nombre',
                width: '100%',
                language: {
                    noResults: function() { return "Sin resultados"; }
                }
            });

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
                        },
                        error: function() {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener los pedidos pendientes.' });
                        }
                    });
                }
            });
        });

        $('#btn-buscar-producto').on('click', function() {
            let productoId = $('#caja-busqueda-producto').val();
            idProducto_para_tabla = productoId;
            if (productoId) {
                $.ajax({
                    url: "{{ route('pedidos.vendedor.obtenerProducto',':id') }}".replace(':id', productoId),
                    type: 'GET',
                    success: function(data) {
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
                                <div class="card border-0 shadow-sm">
                                    <input type="hidden" id="id-producto-agregar-pedido" value="${data.producto.id}" />
                                    <div class="card-body">
                                        <div class="d-flex flex-column align-items-center text-center">
                                            <img src="${foto}" class="img-fluid rounded mb-3" id="foto-producto-agregar-pedido"
                                                 alt="${data.producto.nombre_producto}" style="max-height: 160px; object-fit: contain;">
                                            <h5 class="font-weight-bold mb-1" id="id-texto-producto-agregar-pedido">${data.producto.nombre_producto}</h5>
                                            <p class="mb-1"><strong>Código:</strong> ${data.producto.codigo}</p>
                                            <p class="mb-1 text-truncate" style="max-width: 320px;">
                                                <strong>Descripción:</strong> ${data.producto.descripcion_producto || 'No disponible'}
                                            </p>
                                            <p class="mb-2">
                                                <strong>Stock:</strong> ${data.producto.cantidad || 'N/D'} ${data.producto.detalle_cantidad || ''}
                                            </p>
                                            ${promoHtml}
                                            <div class="w-100">
                                                <strong>Formas de Venta:</strong>
                                                <div class="table-responsive">${tabla_formas_venta}</div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="form-row">
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="forma-venta-agregar-producto-agregar">
                                                        <i class="fas fa-tag text-primary"></i> Forma de Venta
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
                                                        <i class="fas fa-sort-numeric-up text-info"></i> Cantidad
                                                    </label>
                                                    <input type="number" id="cantidad-precio-pedido" class="form-control text-center" value="0" min="1" oninput="calcularCantidadPedidoAgregar(this)">
                                                </div>
                                                <div class="form-group col-md-6 col-12">
                                                    <label class="font-weight-bold" for="total-precio-perdido">
                                                        <i class="fas fa-calculator text-warning"></i> Sub Total
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
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo obtener el producto' });
                    }
                });
            } else {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: 'Por favor, selecciona un producto.' });
            }
        });

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
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: `La cantidad no puede ser mayor a ${cantidad-1}.` });
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

        function construirTablaProductos(){
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
                        </div>
                    `);
                });
            } else {
                $cards.html('<div class="text-center text-muted">No hay productos agregados al pedido.</div>');
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
            $('#caja-busqueda-producto').val('').trigger('change');
            $('#resultado-busqueda').empty();

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

                    $.ajax({
                        url: "{{ route('pedidos.vendedor.registrarPedido') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            asignacion_id: '{{ $asignacion->id }}',
                            productos: JSON.stringify(tablaProductos),
                        },
                        beforeSend: function() {
                            Swal.fire({ title: 'Registrando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                        },
                        success: function() {
                            Swal.fire({ icon: 'success', title: 'Éxito', text: 'Pedido registrado correctamente.', timer: 1500, showConfirmButton: false })
                                .then(() => window.location.href = "{{ route('asignacionvendedor.index') }}");
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'No se pudo registrar el pedido.' });
                        }
                    });
                }
            });
        }
    </script>
@stop