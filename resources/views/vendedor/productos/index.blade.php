@extends('adminlte::page')

@section('title', 'Productos')

@section('content_header')
    <div class="seller-products-header">
        <div>
            <span>Catalogo de venta</span>
            <h1>Productos disponibles</h1>
            <p>Busca por nombre o codigo, revisa stock, precios y promociones antes de registrar un pedido.</p>
        </div>

        <a href="{{ route('productos.vendedor.descargarCatalogo') }}" class="seller-catalog-btn" target="_blank">
            <i class="fas fa-file-pdf"></i>
            Descargar catalogo
        </a>
    </div>
@stop

@section('content')
    <div class="seller-products-shell">
        <section class="seller-summary">
            <div>
                <span class="seller-summary-label">Promociones activas</span>
                <strong>{{ $contar_productos_promocion }}</strong>
                <small>productos con oferta</small>
            </div>
            <button type="button" id="btnPromociones" class="seller-summary-action">
                Ver promociones
            </button>
        </section>

        <section class="seller-filter-panel" aria-label="Filtros de productos">
            <div class="seller-filter-title">
                <i class="fas fa-search"></i>
                <div>
                    <strong>Buscar producto</strong>
                    <span>Usa uno o varios filtros. En celular empieza por el nombre.</span>
                </div>
            </div>

            <div class="seller-filter-grid">
                <label>
                    Nombre
                    <input type="text" id="filtroNombre" class="form-control" placeholder="Ej: arroz, aceite, fideo">
                </label>

                <label>
                    Codigo
                    <input type="text" id="filtroCodigo" class="form-control" placeholder="Ej: PROD-000123">
                </label>

                <label>
                    Marca
                    <select id="filtroMarca" class="form-control">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->descripcion }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Linea
                    <select id="filtroLinea" class="form-control">
                        <option value="">Todas las lineas</option>
                        @foreach($lineas as $linea)
                            <option value="{{ $linea->id }}">{{ $linea->descripcion_linea }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Promocion
                    <select id="filtroPromocion" class="form-control">
                        <option value="">Todos</option>
                        <option value="si">Con promocion</option>
                        <option value="no">Sin promocion</option>
                    </select>
                </label>

                <label>
                    Stock
                    <select id="filtroStock" class="form-control">
                        <option value="">Todo stock</option>
                        <option value="disponible">Stock normal</option>
                        <option value="bajo">Poco stock</option>
                    </select>
                </label>
            </div>

            <div class="seller-filter-actions">
                <button type="button" id="btnBuscar" class="btn seller-search-btn">
                    <i class="fas fa-search"></i>
                    Buscar
                </button>
                <button type="button" id="btnLimpiar" class="btn seller-clear-btn">
                    Limpiar filtros
                </button>
            </div>
        </section>

        <section class="seller-table-panel">
            <div class="seller-table-head">
                <div>
                    <strong>Lista de productos</strong>
                    <span>Los precios aparecen por forma de venta.</span>
                </div>
            </div>

            <table class="table seller-products-table" id="productosTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Stock</th>
                        <th>Precios</th>
                        <th>Promocion</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </section>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.css">

    <style>
        .content-wrapper {
            background: #f4f7f6;
        }

        .seller-products-header {
            align-items: center;
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            padding: 22px;
        }

        .seller-products-header span,
        .seller-summary-label {
            color: #0f766e;
            display: block;
            font-size: 13px;
            font-weight: 850;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .seller-products-header h1 {
            color: #17211f;
            font-size: 28px;
            font-weight: 900;
            line-height: 1.15;
            margin: 4px 0 8px;
        }

        .seller-products-header p {
            color: #51615d;
            font-size: 16px;
            line-height: 1.4;
            margin: 0;
            max-width: 760px;
        }

        .seller-catalog-btn,
        .seller-summary-action,
        .seller-search-btn {
            align-items: center;
            background: #0f766e;
            border: 1px solid #0f766e;
            border-radius: 8px;
            color: #ffffff;
            display: inline-flex;
            font-weight: 850;
            gap: 8px;
            justify-content: center;
            min-height: 48px;
            padding: 12px 16px;
            text-decoration: none;
            white-space: nowrap;
        }

        .seller-catalog-btn:hover,
        .seller-summary-action:hover,
        .seller-search-btn:hover {
            background: #0b5c56;
            border-color: #0b5c56;
            color: #ffffff;
            text-decoration: none;
        }

        .seller-products-shell {
            margin: 0 auto 32px;
            max-width: 1280px;
        }

        .seller-summary,
        .seller-filter-panel,
        .seller-table-panel {
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 18px;
        }

        .seller-summary {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
        }

        .seller-summary strong {
            color: #17211f;
            display: block;
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
            margin: 6px 0;
        }

        .seller-summary small {
            color: #51615d;
            font-size: 15px;
            font-weight: 700;
        }

        .seller-filter-title {
            align-items: center;
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
        }

        .seller-filter-title i {
            align-items: center;
            background: #e9f6f3;
            border-radius: 8px;
            color: #0f766e;
            display: inline-flex;
            height: 44px;
            justify-content: center;
            width: 44px;
        }

        .seller-filter-title strong,
        .seller-table-head strong {
            color: #17211f;
            display: block;
            font-size: 19px;
            font-weight: 900;
            line-height: 1.2;
        }

        .seller-filter-title span,
        .seller-table-head span {
            color: #51615d;
            display: block;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            margin-top: 3px;
        }

        .seller-filter-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: 1.2fr .9fr 1fr 1fr .8fr .8fr;
        }

        .seller-filter-grid label {
            color: #17211f;
            font-size: 14px;
            font-weight: 850;
            margin: 0;
        }

        .seller-filter-grid .form-control {
            border-color: #cfdcd8;
            border-radius: 8px;
            font-size: 16px;
            height: 48px;
            margin-top: 7px;
        }

        .seller-filter-grid .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .16);
        }

        .seller-filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .seller-clear-btn {
            background: #ffffff;
            border: 1px solid #b8c8c4;
            border-radius: 8px;
            color: #17211f;
            font-weight: 850;
            min-height: 48px;
            padding: 12px 16px;
        }

        .seller-table-head {
            margin-bottom: 14px;
        }

        .seller-products-table {
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .seller-products-table thead th {
            background: #17211f;
            border: 0;
            color: #ffffff;
            font-size: 13px;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .seller-products-table tbody td {
            border-top: 1px solid #e2ebe8;
            color: #17211f;
            vertical-align: middle;
        }

        .seller-product-img {
            border: 1px solid #d8e1df;
            border-radius: 8px;
            height: 74px;
            object-fit: cover;
            padding: 4px;
            width: 74px;
        }

        .seller-code,
        .seller-brand,
        .seller-empty {
            border-radius: 8px;
            display: inline-flex;
            font-size: 13px;
            font-weight: 850;
            line-height: 1.2;
            padding: 8px 10px;
        }

        .seller-code {
            background: #fff8db;
            color: #4a3d00;
        }

        .seller-brand,
        .seller-empty {
            background: #eef3f1;
            color: #51615d;
        }

        .seller-product-name strong,
        .seller-product-name span {
            display: block;
            line-height: 1.3;
        }

        .seller-product-name strong {
            color: #17211f;
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .seller-product-name span {
            color: #51615d;
            font-size: 13px;
            font-weight: 700;
        }

        .seller-stock {
            border-radius: 8px;
            display: inline-flex;
            font-size: 14px;
            font-weight: 900;
            padding: 9px 10px;
        }

        .seller-stock-ok {
            background: #e6f6ee;
            color: #0c6b3c;
        }

        .seller-stock-low {
            background: #ffe7e7;
            color: #a51515;
        }

        .seller-sale-list,
        .seller-promo {
            display: grid;
            gap: 6px;
        }

        .seller-sale-list span,
        .seller-promo span {
            background: #f5faf8;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            color: #17211f;
            display: block;
            font-size: 13px;
            font-weight: 750;
            padding: 8px 10px;
            white-space: nowrap;
        }

        .seller-promo strong {
            color: #0f766e;
            display: block;
            font-size: 14px;
            font-weight: 900;
        }

        .seller-detail-btn {
            background: #ffffff;
            border: 1px solid #0f766e;
            border-radius: 8px;
            color: #0f766e;
            font-weight: 850;
            margin-top: 4px;
        }

        .dataTables_wrapper .dataTables_filter {
            display: none;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #51615d;
            font-size: 14px;
            font-weight: 700;
        }

        .swal2-popup {
            border-radius: 8px !important;
        }

        @media (max-width: 991.98px) {
            .seller-products-header,
            .seller-summary {
                align-items: stretch;
                flex-direction: column;
            }

            .seller-catalog-btn,
            .seller-summary-action {
                width: 100%;
            }

            .seller-filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .content-header {
                padding: 12px 12px 4px;
            }

            .content {
                padding: 0 12px 18px;
            }

            .seller-products-header h1 {
                font-size: 24px;
            }

            .seller-filter-grid {
                grid-template-columns: 1fr;
            }

            .seller-filter-actions,
            .seller-search-btn,
            .seller-clear-btn {
                width: 100%;
            }

            .seller-product-img {
                height: 64px;
                width: 64px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.js"></script>

    <script>
        let productosTable;

        function filtrosProductos() {
            return {
                nombre: $('#filtroNombre').val(),
                codigo: $('#filtroCodigo').val(),
                marca: $('#filtroMarca').val(),
                linea: $('#filtroLinea').val(),
                promocion: $('#filtroPromocion').val(),
                stock: $('#filtroStock').val()
            };
        }

        $(document).ready(function() {
            productosTable = $('#productosTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '/i18n/es-ES.json'
                },
                pageLength: 8,
                lengthMenu: [[8, 15, 25, 50], [8, 15, 25, 50]],
                ajax: {
                    url: "{{ route('preventistas.productos.vendedor.obtenerProductos') }}",
                    type: "GET",
                    data: function(data) {
                        return Object.assign(data, filtrosProductos());
                    }
                },
                columns: [
                    { data: 'codigo', name: 'codigo', responsivePriority: 2 },
                    { data: 'imagen', name: 'imagen', orderable: false, searchable: false, responsivePriority: 1 },
                    { data: 'nombre_producto', name: 'nombre_producto', responsivePriority: 1 },
                    { data: 'marca', name: 'marca', orderable: false, searchable: false, responsivePriority: 5 },
                    { data: 'stock', name: 'stock', orderable: false, searchable: false, responsivePriority: 3 },
                    { data: 'formas_venta', name: 'formas_venta', orderable: false, searchable: false, responsivePriority: 4 },
                    { data: 'promocion_vista', name: 'promocion_vista', orderable: false, searchable: false, responsivePriority: 6 }
                ],
                columnDefs: [
                    { targets: [0, 1, 3, 4, 5, 6], className: 'align-middle text-center' },
                    { targets: [2], className: 'align-middle' }
                ],
                order: [[2, 'asc']]
            });

            $('#btnBuscar').on('click', function() {
                productosTable.ajax.reload();
            });

            $('#btnLimpiar').on('click', function() {
                $('#filtroNombre').val('');
                $('#filtroCodigo').val('');
                $('#filtroMarca').val('');
                $('#filtroLinea').val('');
                $('#filtroPromocion').val('');
                $('#filtroStock').val('');
                productosTable.ajax.reload();
            });

            $('#filtroNombre, #filtroCodigo').on('keyup', function(event) {
                if (event.key === 'Enter') {
                    productosTable.ajax.reload();
                }
            });

            $('#filtroMarca, #filtroLinea, #filtroPromocion, #filtroStock').on('change', function() {
                productosTable.ajax.reload();
            });

            $('#btnPromociones').on('click', mostrarPromociones);
        });

        function mostrarPromociones() {
            @if($contar_productos_promocion <= 0)
                Swal.fire({
                    title: 'Sin promociones',
                    text: 'No hay productos en promocion en este momento.',
                    icon: 'info',
                    confirmButtonText: 'Cerrar'
                });
                return;
            @endif

            Swal.fire({
                title: 'Cargando promociones',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('preventistas.productos.vendedor.verDetalleProductosPromocion') }}",
                type: "GET",
                success: function(data) {
                    let html = '<div class="seller-promo-modal">';

                    data.productos.forEach(function(producto) {
                        const imagen = producto.foto_producto
                            ? `{{ route('productos.imagen', ':id') }}`.replace(':id', producto.id)
                            : "{{ asset('images/logo_color.webp') }}";

                        html += `
                            <div class="seller-promo-card">
                                <img src="${imagen}" alt="Producto">
                                <div>
                                    <strong>${producto.nombre_producto}</strong>
                                    <span>Codigo: ${producto.codigo}</span>
                                    <span>Stock: ${producto.cantidad} ${producto.detalle_cantidad}</span>
                                    <span>Descuento: ${producto.descripcion_descuento_porcentaje ?? 0}%</span>
                                    <span>Regalo: ${producto.descripcion_regalo ? producto.descripcion_regalo : 'No aplica'}</span>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';

                    Swal.fire({
                        title: 'Productos en promocion',
                        html: html,
                        width: 760,
                        confirmButtonText: 'Cerrar'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las promociones.'
                    });
                }
            });
        }

        function verDetalleFormaVenta(e) {
            const idProducto = e.getAttribute('id-producto');

            Swal.fire({
                title: 'Cargando precios',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('preventistas.productos.vendedor.verFormasVenta',':id') }}".replace(':id', idProducto),
                type: "GET",
                success: function(data) {
                    let html = `<table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Forma de venta</th>
                                <th>Precio</th>
                                <th>Equivalencia</th>
                            </tr>
                        </thead>
                        <tbody>`;

                    data.formas_venta.forEach(function(formaVenta) {
                        html += `
                            <tr>
                                <td>${formaVenta.tipo_venta}</td>
                                <td>Bs. ${Number(formaVenta.precio_venta).toFixed(2)}</td>
                                <td>${formaVenta.equivalencia_cantidad}</td>
                            </tr>`;
                    });

                    html += `</tbody></table>`;

                    Swal.fire({
                        title: 'Precios de venta',
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los precios.'
                    });
                }
            });
        }

        function verDetallePromocion(e) {
            const idProducto = e.getAttribute('id-producto');

            Swal.fire({
                title: 'Cargando promocion',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('preventistas.productos.vendedor.verDetallePromocion',':id') }}".replace(':id', idProducto),
                type: "GET",
                success: function(data) {
                    let html = `<div class="text-left">
                        <p><strong>Producto:</strong> ${data.producto.nombre_producto}</p>
                        <p><strong>Descuento:</strong> ${data.producto.descripcion_descuento_porcentaje ?? 0}%</p>
                        <p><strong>Regalo:</strong> ${data.producto.descripcion_regalo ? data.producto.descripcion_regalo : 'No aplica'}</p>
                    </div>`;

                    Swal.fire({
                        title: 'Detalle de promocion',
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la promocion.'
                    });
                }
            });
        }
    </script>

    <style>
        .seller-promo-modal {
            display: grid;
            gap: 12px;
            text-align: left;
        }

        .seller-promo-card {
            align-items: center;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            display: grid;
            gap: 12px;
            grid-template-columns: 86px 1fr;
            padding: 12px;
        }

        .seller-promo-card img {
            border-radius: 8px;
            height: 78px;
            object-fit: cover;
            width: 78px;
        }

        .seller-promo-card strong,
        .seller-promo-card span {
            display: block;
            line-height: 1.3;
        }

        .seller-promo-card strong {
            color: #17211f;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .seller-promo-card span {
            color: #51615d;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
@stop
