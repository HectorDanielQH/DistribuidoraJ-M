@extends('adminlte::page')

@section('title', 'Cantidad para despacho')

@section('content_header')
    <div class="dispatch-header">
        <div>
            <span>Preventa / almacen</span>
            <h1>Cantidad para despacho</h1>
            <p>Consolidado de productos que deben salir del almacen para entregar al repartidor.</p>
        </div>
        <div class="dispatch-actions">
            <a href="{{ route('administrador.pedidos.administrador.visualizacion') }}" class="btn btn-outline-secondary dispatch-main-btn">
                <i class="fas fa-arrow-left"></i> Volver a pendientes
            </a>
            <a href="{{ route('pedidos.administrador.visualizacionPdfDespachar.pedidosPendientes') }}" target="_blank" class="btn btn-info dispatch-main-btn">
                <i class="fas fa-file-pdf"></i> Imprimir para repartidor
            </a>
        </div>
    </div>
@stop

@section('content')
    <section class="dispatch-summary">
        <article>
            <span>Pedidos pendientes</span>
            <strong>{{ $resumenPedidos['pendientes'] ?? 0 }}</strong>
            <small>Por preparar</small>
        </article>
        <article>
            <span>Lineas reservadas</span>
            <strong>{{ $resumenPedidos['pendientes_items'] ?? 0 }}</strong>
            <small>Productos en pedidos</small>
        </article>
        <article>
            <span>Total estimado</span>
            <strong>Bs {{ number_format($suma_total_estimada ?? 0, 2, '.', ',') }}</strong>
            <small>Referencia, no caja</small>
        </article>
    </section>

    <section class="dispatch-filters">
        <label>
            Ruta
            <select id="filtro-ruta" class="form-control dispatch-filter">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventista
            <select id="filtro-preventista" class="form-control dispatch-filter">
                <option value="">Todos</option>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
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
                    <th>Stock actual</th>
                    <th>Cantidad a sacar</th>
                    <th>Pedidos</th>
                    <th>Estado</th>
                    <th>Total estimado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </section>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/dt-2.3.3/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/r-3.0.6/datatables.min.css" rel="stylesheet">
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
            grid-template-columns: 1fr 1fr auto;
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
        .dispatch-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .dispatch-pill, .dispatch-ok, .dispatch-risk {
            display: inline-flex;
            align-items: center;
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
        .dispatch-ok {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .dispatch-risk {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
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

    <script>
        $(document).ready(function () {
            const tabla = $('#tablaPedidosDespachados').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar producto',
                    searchPlaceholder: 'Codigo o nombre'
                },
                ajax: {
                    url: "{{ route('pedidos.administrador.visualizacionParaDespachado') }}",
                    data: function (d) {
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                    }
                },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'imagen', name: 'imagen', orderable: false, searchable: false },
                    { data: 'nombre_producto', name: 'nombre_producto' },
                    { data: 'stock_producto', name: 'stock_producto', orderable: false },
                    { data: 'cantidad_despacho', name: 'cantidad_despacho', orderable: false },
                    { data: 'pedidos_involucrados', orderable: false, searchable: false },
                    { data: 'estado_stock', orderable: false, searchable: false },
                    { data: 'ingreso_estimado', name: 'ingreso_estimado', orderable: false },
                    { data: 'acciones', orderable: false, searchable: false }
                ],
                dom: "<'row align-items-center mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger dispatch-main-btn',
                        exportOptions: { columns: [0, 2, 3, 4, 5, 7] },
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info dispatch-main-btn',
                        exportOptions: { columns: [0, 2, 3, 4, 5, 7] },
                    }
                ]
            });

            $('.dispatch-filter').on('change', function () {
                tabla.ajax.reload();
            });

            $('#limpiar-filtros').on('click', function () {
                $('.dispatch-filter').val('');
                tabla.ajax.reload();
            });
        });

        function verPedidosPorProducto(e) {
            const idProducto = e.getAttribute('id-producto');

            $.ajax({
                url: "{{ route('pedidos.administrador.pendientesPorProducto', ':id') }}".replace(':id', idProducto),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({ title: 'Cargando pedidos...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (response) {
                    const detalle = response.pedidos.map(item => `
                        <div class="dispatch-detail-item">
                            <strong>Pedido #${item.numero_pedido} - ${item.cliente}</strong>
                            <div>Ruta: ${item.ruta}</div>
                            <div>Preventista: ${item.preventista}</div>
                            <div>Cantidad: ${item.cantidad} (${item.unidades} unidades)</div>
                            <strong>Subtotal: Bs ${Number(item.subtotal).toFixed(2)}</strong>
                        </div>
                    `).join('');

                    Swal.fire({
                        title: 'Pedidos que usan este producto',
                        html: `<div class="dispatch-detail-list">${detalle}<div class="dispatch-detail-item"><strong>Total: Bs ${Number(response.total).toFixed(2)}</strong><div>Unidades a sacar: ${response.unidades}</div></div></div>`,
                        width: window.innerWidth <= 700 ? '96%' : '720px',
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el detalle.', 'error');
                }
            });
        }
    </script>
@stop
