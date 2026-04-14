@extends('adminlte::page')

@section('title', 'Mis ventas')

@section('content_header')
    <div class="sales-header">
        <div>
            <span>Ventas contabilizadas</span>
            <h1>Mis ventas</h1>
            <p>Revisa por dia cuanto se vendio y entra al detalle.</p>
        </div>
    </div>
@stop

@section('content')
    <div class="sales-page">
        <section class="summary-grid">
            <div class="summary-box">
                <span>Total vendido</span>
                <strong>Bs {{ number_format((float) ($resumen->total ?? 0), 2, '.', ',') }}</strong>
            </div>
            <div class="summary-box">
                <span>Dias</span>
                <strong>{{ $resumen->dias ?? 0 }}</strong>
            </div>
            <div class="summary-box">
                <span>Pedidos</span>
                <strong>{{ $resumen->pedidos ?? 0 }}</strong>
            </div>
        </section>

        <section class="help-box">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Toca “Ver pedidos”.</strong>
                <span>Asi veras que clientes compraron en esa fecha.</span>
            </div>
        </section>

        <section class="sales-list">
            <div class="section-title">
                <h2>Ventas por dia</h2>
                <p>Primero aparecen las ventas mas recientes.</p>
            </div>

            <div class="table-responsive">
                <table id="tabla-ventas" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
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
        .summary-box,
        .help-box,
        .sales-list {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .sales-header {
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
            font-size: 1.6rem;
        }

        .sales-header p,
        .section-title p,
        .help-box span {
            margin: 4px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .sales-page {
            display: grid;
            gap: 12px;
            padding-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 10px;
        }

        .summary-box {
            display: grid;
            gap: 4px;
            padding: 14px;
        }

        .summary-box span {
            color: var(--muted);
            font-weight: 800;
        }

        .summary-box strong {
            color: var(--text);
            font-size: 1.4rem;
            font-weight: 900;
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

        .btn-action {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        div.dataTables_wrapper div.dataTables_filter input,
        div.dataTables_wrapper div.dataTables_length select {
            border-radius: 8px;
            min-height: 38px;
            border-color: var(--line);
        }

        @media (max-width: 575.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
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
                ajax: "{{ route('preventistas.ventas.vendedor.misVentas') }}",
                columns: [
                    { data: 'fecha_contabilizacion', orderable: false, searchable: false },
                    { data: 'monto_contabilizado', orderable: false, searchable: false },
                    { data: 'acciones', orderable: false, searchable: false },
                ],
                order: [],
                createdRow: function (row) {
                    const labels = ['Fecha', 'Total', 'Accion'];
                    $('td', row).each(function (index) {
                        $(this).attr('data-mobile-label', labels[index]);
                    });
                }
            });
        });
    </script>
@stop
