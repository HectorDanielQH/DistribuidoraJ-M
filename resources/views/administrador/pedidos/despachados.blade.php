@extends('adminlte::page')

@section('title', 'Pedidos despachados')

@section('content_header')
    <div class="dispatch-header">
        <div>
            <span>Repartidor / cierre pendiente</span>
            <h1>Pedidos despachados</h1>
            <p>Productos que ya salieron con el repartidor. Todavia no son venta hasta contabilizar.</p>
        </div>
        <div class="dispatch-actions">
            <button type="button" class="btn btn-info dispatch-main-btn" id="btn-pdf-despacho">
                <i class="fas fa-file-pdf"></i> PDF para despacho
            </button>
            <button type="button" class="btn btn-outline-info dispatch-main-btn" id="btn-hoja-pedidos">
                <i class="fas fa-file-alt"></i> Hoja de pedidos
            </button>
            <a href="{{ route('pedidos.administrador.devolucionPedido') }}" class="btn btn-danger dispatch-main-btn">
                <i class="fas fa-undo-alt"></i> Registrar devoluciones
            </a>
            <button class="btn btn-success dispatch-main-btn" onclick="contabilizarTodosLosPendientes(this)">
                <i class="fas fa-cash-register"></i> Contabilizar
            </button>
        </div>
    </div>
@stop

@section('content')
    <section class="dispatch-summary">
        <article>
            <span>Despachados</span>
            <strong>{{ $resumenPedidos['despachados'] ?? 0 }}</strong>
            <small>En manos del repartidor</small>
        </article>
        <article>
            <span>Total a cobrar</span>
            <strong>Bs {{ number_format($suma_total_estimada ?? 0, 2, '.', ',') }}</strong>
            <small>Antes de devoluciones</small>
        </article>
        <article>
            <span>Pendientes nuevos</span>
            <strong>{{ $resumenPedidos['pendientes'] ?? 0 }}</strong>
            <small>Aun no salieron</small>
        </article>
        <article>
            <span>Contabilizados</span>
            <strong>{{ $resumenPedidos['contabilizados'] ?? 0 }}</strong>
            <small>Ya pasaron a ventas</small>
        </article>
    </section>

    <section class="dispatch-filters">
        <label>
            Rutas
            <small>Deja vacio para todas. Puedes elegir varias.</small>
            <select id="filtro-ruta" class="form-control dispatch-filter dispatch-select2" multiple>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventistas
            <small>Deja vacio para todos. Puedes elegir varios.</small>
            <select id="filtro-preventista" class="form-control dispatch-filter dispatch-select2" multiple>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Fecha despacho
            <input type="date" id="filtro-fecha-entrega" class="form-control dispatch-filter">
        </label>
        <label>
            Filas por pagina
            <select id="filas-pagina" class="form-control">
                <option value="10">10 productos</option>
                <option value="25">25 productos</option>
                <option value="50">50 productos</option>
                <option value="100">100 productos</option>
                <option value="-1">Todos</option>
            </select>
        </label>
        <button class="btn btn-outline-secondary dispatch-main-btn" id="limpiar-filtros">
            <i class="fas fa-eraser"></i> Limpiar
        </button>
    </section>

    <section class="dispatch-table-shell">
        <table class="table table-striped table-bordered" id="tablaPedidosDespachados">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Stock del producto</th>
                    <th>Cantidad a sacar</th>
                    <th>Ingreso estimado</th>
                </tr>
            </thead>
        </table>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .content-wrapper { background: #eef3f1; }
        .dispatch-header, .dispatch-summary, .dispatch-filters, .dispatch-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .dispatch-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .dispatch-header span, .dispatch-summary span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .dispatch-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .dispatch-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .dispatch-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .dispatch-main-btn, .dispatch-action {
            border-radius: 8px;
            font-weight: 900;
            min-height: 40px;
        }
        .dispatch-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .dispatch-summary article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .dispatch-summary strong {
            display: block;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .dispatch-summary small {
            color: #64748b;
            font-weight: 800;
        }
        .dispatch-filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: end;
        }
        .dispatch-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .dispatch-filters small {
            display: block;
            color: #64748b;
            font-size: .78rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 3px 6px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #0f766e;
            box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .15);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #e8f2ee;
            border: 1px solid #b8d5ca;
            border-radius: 8px;
            color: #17211d;
            font-weight: 800;
            margin-top: 4px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #0f766e;
            margin-right: 4px;
        }
        .dispatch-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .dispatch-product-image {
            width: 72px;
            height: 72px;
            object-fit: contain;
            background: #ffffff;
        }
        .dispatch-quantity {
            color: #0f766e;
            white-space: nowrap;
        }
        .dispatch-money {
            color: #166534;
            white-space: nowrap;
        }
        .dispatch-pill, .dispatch-warning {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            padding: 6px 8px;
            font-weight: 900;
            white-space: nowrap;
        }
        .dispatch-pill {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .dispatch-warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .dispatch-detail-list {
            display: grid;
            gap: 10px;
            text-align: left;
        }
        .dispatch-detail-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }
        @media (max-width: 767.98px) {
            .dispatch-header, .dispatch-actions { flex-direction: column; }
            .dispatch-main-btn, .dispatch-action { width: 100%; }
            .dispatch-summary, .dispatch-filters { grid-template-columns: 1fr; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#filtro-ruta').select2({
                closeOnSelect: false,
                allowClear: true,
                placeholder: 'Todas las rutas',
                width: '100%',
                language: {
                    noResults: function () {
                        return 'Sin resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    }
                }
            });

            $('#filtro-preventista').select2({
                closeOnSelect: false,
                allowClear: true,
                placeholder: 'Todos los preventistas',
                width: '100%',
                language: {
                    noResults: function () {
                        return 'Sin resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    }
                }
            });

            const tabla = $('#tablaPedidosDespachados').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar producto',
                    searchPlaceholder: 'Codigo o nombre'
                },
                ajax: {
                    url: "{{ route('pedidos.administrador.visualizacionDespachados') }}",
                    data: function (d) {
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                        d.fecha_entrega = $('#filtro-fecha-entrega').val();
                    }
                },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'imagen', name: 'imagen', orderable: false, searchable: false },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'stock_producto', name: 'stock_producto', orderable: false },
                    { data: 'cantidad_despacho', name: 'cantidad_despacho', orderable: false },
                    { data: 'ingreso_estimado', name: 'ingreso_estimado', orderable: false }
                ],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger dispatch-main-btn',
                        exportOptions: { columns: [0, 2, 3, 4, 5] },
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info dispatch-main-btn',
                        exportOptions: { columns: [0, 2, 3, 4, 5] },
                    }
                ]
            });

            $('.dispatch-filter').on('change', function () {
                tabla.ajax.reload();
            });

            $('#filas-pagina').on('change', function () {
                tabla.page.len(Number(this.value)).draw();
            });

            $('#btn-pdf-despacho').on('click', function () {
                const url = new URL("{{ route('pedidos.administrador.consolidadoDespacho.pdf', 'despachados') }}", window.location.origin);
                const filtros = {
                    fecha_entrega: $('#filtro-fecha-entrega').val(),
                };

                Object.entries(filtros).forEach(([key, value]) => {
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                });

                ($('#filtro-ruta').val() || []).forEach((id) => {
                    url.searchParams.append('ruta_id[]', id);
                });

                ($('#filtro-preventista').val() || []).forEach((id) => {
                    url.searchParams.append('preventista_id[]', id);
                });

                window.open(url.toString(), '_blank');
            });

            $('#btn-hoja-pedidos').on('click', function () {
                const url = new URL("{{ route('pedidos.administrador.visualizacionPdfDespachar') }}", window.location.origin);
                const fechaEntrega = $('#filtro-fecha-entrega').val();

                if (fechaEntrega) {
                    url.searchParams.set('fecha_entrega', fechaEntrega);
                }

                ($('#filtro-ruta').val() || []).forEach((id) => {
                    url.searchParams.append('ruta_id[]', id);
                });

                ($('#filtro-preventista').val() || []).forEach((id) => {
                    url.searchParams.append('preventista_id[]', id);
                });

                window.open(url.toString(), '_blank');
            });

            $('#limpiar-filtros').on('click', function () {
                $('.dispatch-filter').val('').trigger('change');
                $('.dispatch-select2').val(null).trigger('change');
                $('#filas-pagina').val('10');
                tabla.page.len(10);
                tabla.ajax.reload();
            });
        });

        function verPedidosDespachadosPorProducto(e) {
            const idProducto = e.getAttribute('id-producto');

            $.ajax({
                url: "{{ route('pedidos.administrador.despachadosPorProducto', ':id') }}".replace(':id', idProducto),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({ title: 'Cargando pedidos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    const detalle = response.pedidos.map(item => {
                        const urlDevolucion = "{{ route('pedidos.administrador.devolucionPedido') }}" + '?pedido=' + encodeURIComponent(item.numero_pedido);
                        return `
                        <div class="dispatch-detail-item">
                            <strong>Pedido #${item.numero_pedido} - ${item.cliente}</strong>
                            <div>Ruta: ${item.ruta}</div>
                            <div>Preventista: ${item.preventista}</div>
                            <div>Despacho: ${item.fecha_entrega}</div>
                            <div>Cantidad: ${item.cantidad} (${item.unidades} unidades)</div>
                            <strong>Subtotal: Bs ${Number(item.subtotal).toFixed(2)}</strong>
                            <a href="${urlDevolucion}" class="btn btn-danger btn-sm dispatch-action mt-2">
                                <i class="fas fa-undo-alt"></i> Registrar devolucion
                            </a>
                        </div>
                    `;
                    }).join('');

                    Swal.fire({
                        title: 'Pedidos despachados con este producto',
                        html: `<div class="dispatch-detail-list">${detalle}<div class="dispatch-detail-item"><strong>Total esperado: Bs ${Number(response.total).toFixed(2)}</strong><div>Unidades en reparto: ${response.unidades}</div></div></div>`,
                        width: window.innerWidth <= 700 ? '96%' : '720px',
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el detalle.', 'error');
                }
            });
        }

        function contabilizarTodosLosPendientes(e) {
            Swal.fire({
                title: 'Contabilizar pedidos despachados?',
                text: 'Usa esta accion cuando el repartidor ya trajo el dinero. Se moveran a ventas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, contabilizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({ title: 'Contabilizando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ route('pedidos.administrador.contabilizarTodosLosPendientes') }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            title: 'Listo',
                            text: `${response.message} Total: Bs ${Number(response.total || 0).toFixed(2)}`,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo contabilizar.', 'error');
                    }
                });
            });
        }
    </script>
@stop
