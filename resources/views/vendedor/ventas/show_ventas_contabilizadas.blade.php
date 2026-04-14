@extends('adminlte::page')

@section('title', 'Detalle de ventas')

@section('content_header')
    <div class="sales-header">
        <div>
            <span>Detalle del dia</span>
            <h1>Pedidos vendidos</h1>
            <p>{{ \Carbon\Carbon::parse($fecha_contabilizacion)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('preventistas.ventas.vendedor.misVentas') }}" class="btn btn-outline-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="sales-page">
        <section class="help-box">
            <i class="fas fa-receipt"></i>
            <div>
                <strong>Toca “Ver productos”.</strong>
                <span>Asi veras lo que compro cada cliente.</span>
            </div>
        </section>

        <section class="sales-list">
            <div class="section-title">
                <h2>Pedidos del dia</h2>
                <p>Clientes y montos contabilizados.</p>
            </div>
            <div class="table-responsive">
                <table id="tabla-ventas" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Ruta</th>
                            <th>Pedido</th>
                            <th>Total</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </section>
    </div>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/r-3.0.6/datatables.min.css" rel="stylesheet">
    <style>
        :root {
            --surface: #ffffff;
            --soft: #eef3f1;
            --line: #dbe7e2;
            --text: #17211d;
            --muted: #64748b;
            --green: #15803d;
            --green-soft: #e7f6ec;
        }

        .content-wrapper {
            background: var(--soft);
        }

        .sales-header,
        .help-box,
        .sales-list {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .sales-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
        }

        .sales-header span {
            color: var(--green);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .sales-header h1,
        .section-title h2 {
            margin: 0;
            color: var(--text);
            font-weight: 900;
            letter-spacing: 0;
        }

        .sales-header h1 {
            font-size: 1.55rem;
        }

        .sales-header p,
        .section-title p,
        .help-box span {
            margin: 4px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .btn-back,
        .btn-action {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        .sales-page {
            display: grid;
            gap: 12px;
            padding-bottom: 20px;
        }

        .help-box {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 10px;
            align-items: center;
            padding: 12px;
        }

        .help-box i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--green-soft);
            color: var(--green);
            font-size: 1.25rem;
        }

        .help-box strong {
            display: block;
            color: var(--text);
            font-weight: 900;
        }

        .sales-list {
            padding: 14px;
        }

        .section-title {
            margin-bottom: 12px;
        }

        #tabla-ventas {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        #tabla-ventas thead th {
            border: 0;
            color: var(--muted);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #tabla-ventas tbody tr {
            background: #fbfdfc;
        }

        #tabla-ventas tbody td {
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
            font-weight: 800;
        }

        #tabla-ventas tbody td:first-child {
            border-left: 1px solid var(--line);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #tabla-ventas tbody td:last-child {
            border-right: 1px solid var(--line);
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .sale-product-list {
            display: grid;
            gap: 8px;
            text-align: left;
        }

        .sale-product-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px;
            background: #fbfdfc;
        }

        .sale-product-card strong {
            display: block;
            color: var(--text);
            font-size: 1rem;
        }

        .sale-product-card span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-weight: 800;
        }

        .sale-product-total {
            color: var(--green) !important;
            font-size: 1.05rem;
            font-weight: 900 !important;
        }

        @media (max-width: 575.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .sales-header {
                align-items: stretch;
                flex-direction: column;
            }

            .btn-back {
                width: 100%;
            }

            .table-responsive {
                overflow-x: visible;
            }

            div.dataTables_wrapper div.dataTables_filter {
                display: none;
            }

            #tabla-ventas,
            #tabla-ventas tbody,
            #tabla-ventas tr,
            #tabla-ventas td {
                display: block;
                width: 100%;
            }

            #tabla-ventas thead {
                display: none;
            }

            #tabla-ventas {
                border-collapse: collapse;
                border-spacing: 0;
            }

            #tabla-ventas tbody tr {
                margin-bottom: 10px;
                padding: 12px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--surface);
            }

            #tabla-ventas tbody td,
            #tabla-ventas tbody td:first-child,
            #tabla-ventas tbody td:last-child {
                border: 0;
                border-radius: 0;
                padding: 7px 0;
                text-align: left !important;
            }

            #tabla-ventas tbody td::before {
                content: attr(data-mobile-label);
                display: block;
                margin-bottom: 3px;
                color: var(--muted);
                font-size: .78rem;
                font-weight: 900;
                text-transform: uppercase;
            }

            .btn-action {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/r-3.0.6/datatables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tabla-ventas').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                searching: false,
                language: { url: '/i18n/es-ES.json' },
                ajax: "{{ route('preventistas.ventas.vendedor.detalleVentasPorFechaContabilizacion', ['fecha_contabilizacion' => request()->route('fecha_contabilizacion')]) }}",
                columns: [
                    { data: 'cliente', orderable: false, searchable: false },
                    { data: 'ruta', orderable: false, searchable: false },
                    { data: 'numero_pedido', orderable: false, searchable: false },
                    { data: 'sub_total', orderable: false, searchable: false },
                    { data: 'acciones', orderable: false, searchable: false }
                ],
                order: [],
                createdRow: function (row) {
                    const labels = ['Cliente', 'Ruta', 'Pedido', 'Total', 'Accion'];
                    $('td', row).each(function (index) {
                        $(this).attr('data-mobile-label', labels[index]);
                    });
                }
            });
        });

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function verDetalleVenta(e){
            let numeroPedido = e.getAttribute('data-numero-pedido');
            Swal.fire({
                title: 'Cargando productos...',
                html: 'Espera un momento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.ajax({
                        url: "{{ route('preventistas.ventas.vendedor.miNumeroDePedido', ':numero_pedido') }}".replace(':numero_pedido', numeroPedido),
                        method: 'GET',
                        success: function(response) {
                            let total = 0;
                            let detalleHtml = '<div class="sale-product-list">';
                            response.forEach(item => {
                                const itemTotal = Number(item.cantidad || 0) * Number(item.precio_venta || 0);
                                total += itemTotal;
                                detalleHtml += `
                                    <div class="sale-product-card">
                                        <strong>${escapeHtml(item.nombre_producto)}</strong>
                                        <span>Cantidad: ${escapeHtml(item.cantidad)} ${escapeHtml(item.detalle_cantidad)}</span>
                                        <span>Precio: Bs ${Number(item.precio_venta || 0).toFixed(2)}</span>
                                        <span class="sale-product-total">Total: Bs ${itemTotal.toFixed(2)}</span>
                                    </div>
                                `;
                            });
                            detalleHtml += `<div class="sale-product-card"><strong>Total del pedido</strong><span class="sale-product-total">Bs ${total.toFixed(2)}</span></div></div>`;

                            Swal.fire({
                                title: `Pedido #${numeroPedido}`,
                                html: detalleHtml || '<div class="alert alert-info mb-0">Sin productos.</div>',
                                width: '520px',
                                confirmButtonText: 'Cerrar'
                            });
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo cargar el detalle de la venta.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@stop
