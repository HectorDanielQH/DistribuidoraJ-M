@extends('adminlte::page')

@section('title', 'Ventas por pedido')

@section('content_header')
    <div class="sales-orders-header">
        <div>
            <span>Ventas por pedido</span>
            <h1>Arqueo contabilizado del {{ \Carbon\Carbon::parse($fecha_arqueo)->format('d/m/Y') }}</h1>
            <p>Administra pedidos ya contabilizados sin salir de esta vista. El modal conserva la logica actual de stock y reconstruccion de ventas.</p>
        </div>
        <div class="sales-orders-summary">
            <span>Total del arqueo</span>
            <strong id="arqueo-total-header">Bs {{ number_format((float) ($total_monto_contabilizado ?? 0), 2, '.', ',') }}</strong>
        </div>
    </div>
@stop

@section('content')
    <div class="sales-orders-shell">
        <div class="sales-orders-table-shell">
            <table id="tabla-ventas" class="table table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Nro. Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha pedido</th>
                        <th>Fecha entrega</th>
                        <th>Monto contabilizado</th>
                        <th>Preventista</th>
                        <th>Ruta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total</th>
                        <th id="arqueo-total-footer">Bs {{ number_format((float) ($total_monto_contabilizado ?? 0), 2, '.', ',') }}</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="modal fade" id="editarPedidoContabilizadoModal" tabindex="-1" role="dialog" aria-labelledby="editarPedidoContabilizadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl edit-order-modal-dialog" role="document">
            <div class="modal-content edit-order-modal-content">
                <form id="editarPedidoContabilizadoForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header edit-order-modal-header">
                        <div class="edit-order-modal-heading">
                            <span class="edit-order-kicker">Edicion en linea</span>
                            <h5 class="modal-title" id="editarPedidoContabilizadoModalLabel">Editar pedido contabilizado</h5>
                            <small class="text-muted" id="editar-pedido-contabilizado-resumen">Cargando informacion del pedido...</small>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body edit-order-modal-body">
                        <div class="edit-order-layout">
                            <section class="edit-order-panel edit-order-panel-info">
                                <div class="edit-order-panel-head">
                                    <div>
                                        <h6 class="mb-1">Datos generales</h6>
                                        <p class="mb-0">Puedes ajustar cliente, preventista, fecha del pedido y el detalle de productos sin abandonar esta pantalla.</p>
                                    </div>
                                    <div class="edit-order-total-chip">
                                        <span>Total actualizado</span>
                                        <strong id="edit-total-pedido-contabilizado">Bs 0.00</strong>
                                    </div>
                                </div>

                                <div class="edit-order-grid">
                                    <label>
                                        Nro. pedido
                                        <input type="text" class="form-control" id="edit-pedido-contabilizado-numero-visible" readonly>
                                        <input type="hidden" id="editar-numero-pedido-contabilizado" name="numero_pedido">
                                    </label>
                                    <label>
                                        Cliente
                                        <select id="edit-id-cliente-contabilizado" name="id_cliente" class="form-control"></select>
                                    </label>
                                    <label>
                                        Preventista
                                        <select id="edit-id-usuario-contabilizado" name="id_usuario" class="form-control">
                                            @foreach($preventistas as $preventista)
                                                <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>
                                        Fecha pedido
                                        <input type="datetime-local" class="form-control" id="edit-fecha-pedido-contabilizado" name="fecha_pedido" required>
                                    </label>
                                </div>

                                <div class="edit-order-client-meta mt-3" id="edit-cliente-meta-contabilizado"></div>
                            </section>

                            <section class="edit-order-panel edit-order-panel-lines">
                                <div class="edit-order-toolbar">
                                    <div>
                                        <h6 class="mb-1">Productos del pedido</h6>
                                        <p class="mb-0">Agrega, corrige o elimina filas. El cuerpo del modal mantiene scroll vertical propio para pedidos extensos.</p>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm edit-order-add-btn" id="btnAgregarFilaPedidoContabilizado">
                                        <i class="fas fa-plus"></i> Agregar fila
                                    </button>
                                </div>

                                <div class="edit-order-hintbar">
                                    <span><i class="fas fa-info-circle"></i> Al guardar se reconstruyen las lineas de venta contabilizada con la fecha de arqueo actual.</span>
                                    <span><i class="fas fa-box-open"></i> El stock se recalcula devolviendo primero las lineas actuales y aplicando luego el estado final del pedido.</span>
                                </div>

                                <div class="edit-order-meta-strip">
                                    <span><strong>Fecha entrega:</strong> <span id="edit-fecha-entrega-contabilizado">N/A</span></span>
                                    <span><strong>Fecha contabilizacion:</strong> <span id="edit-fecha-contabilizacion-contabilizado">N/A</span></span>
                                </div>

                                <div class="edit-order-table-shell">
                                    <table class="table table-bordered align-middle mb-0" id="tablaEditarPedidoContabilizado">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Forma de venta</th>
                                                <th>Cantidad</th>
                                                <th>Stock</th>
                                                <th>Precio</th>
                                                <th>Subtotal</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-right">Total</th>
                                                <th id="edit-total-pedido-contabilizado-footer" class="text-right">Bs 0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div>
                    <div class="modal-footer edit-order-modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnGuardarPedidoContabilizadoModal">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eliminarPedidoContabilizadoModal" tabindex="-1" role="dialog" aria-labelledby="eliminarPedidoContabilizadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarPedidoContabilizadoModalLabel">Confirmar eliminacion de pedido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong id="eliminar-pedido-contabilizado-numero"></strong></p>
                    <p class="mb-0">¿Está seguro de eliminar este pedido? Esta acción devolverá los productos al inventario y no se podrá deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminarPedidoContabilizado">
                        <i class="fas fa-trash"></i> Sí, eliminar pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        .content-wrapper { background: #eef3f1; }
        .sales-orders-header,
        .sales-orders-shell,
        .sales-orders-table-shell {
            background: #fff;
            border: 1px solid #d7e4df;
            border-radius: 10px;
        }
        .sales-orders-header {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 20px;
        }
        .sales-orders-header span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .sales-orders-header h1 {
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
            margin: 0;
        }
        .sales-orders-header p {
            color: #64748b;
            font-weight: 700;
            margin: 6px 0 0;
            max-width: 760px;
        }
        .sales-orders-summary {
            background: linear-gradient(135deg, #0f766e, #14532d);
            border-radius: 14px;
            color: #fff;
            min-width: 220px;
            padding: 14px 18px;
            text-align: right;
        }
        .sales-orders-summary span {
            color: rgba(255, 255, 255, .78);
            display: block;
        }
        .sales-orders-summary strong {
            display: block;
            font-size: 1.55rem;
            font-weight: 900;
            margin-top: 4px;
        }
        .sales-orders-shell {
            padding: 14px;
        }
        .sales-orders-table-shell {
            overflow-x: auto;
            padding: 10px;
        }
        .order-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .order-action-btn {
            border-radius: 8px;
            font-weight: 800;
        }
        .edit-order-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .edit-order-grid label {
            color: #334155;
            font-size: .92rem;
            font-weight: 800;
            margin: 0;
        }
        .edit-order-modal-dialog {
            margin: 1rem auto;
            max-width: min(1440px, calc(100vw - 2rem));
            width: calc(100vw - 2rem);
        }
        .edit-order-modal-content {
            background: linear-gradient(180deg, #f8fbfa 0%, #edf4f2 100%);
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
            display: block;
            overflow: visible;
        }
        .edit-order-modal-header,
        .edit-order-modal-footer {
            background: rgba(255, 255, 255, .96);
            border: 0;
            flex-shrink: 0;
            position: relative;
            z-index: 3;
        }
        .edit-order-modal-header {
            align-items: flex-start;
            border-bottom: 1px solid #dde7e4;
            padding: 18px 22px 16px;
        }
        .edit-order-modal-heading { min-width: 0; }
        .edit-order-kicker {
            color: #0f766e;
            display: inline-block;
            font-size: .72rem;
            font-weight: 900;
            letter-spacing: .08em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .edit-order-modal-header .modal-title {
            color: #0f172a;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .edit-order-modal-body {
            display: block;
            overflow: visible;
            padding: 18px 22px 14px;
        }
        .edit-order-layout {
            display: block;
            gap: 16px;
            min-width: 100%;
            overflow: visible;
            width: 100%;
        }
        .edit-order-panel {
            background: rgba(255, 255, 255, .95);
            border: 1px solid #dce8e4;
            border-radius: 16px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
        }
        .edit-order-panel-info { padding: 16px; }
        .edit-order-panel-lines {
            display: block;
            margin-top: 16px;
            min-width: 100%;
            overflow: visible;
            padding: 16px;
        }
        .edit-order-panel-head,
        .edit-order-toolbar {
            align-items: flex-start;
            display: flex;
            gap: 14px;
            justify-content: space-between;
        }
        .edit-order-panel-head { margin-bottom: 14px; }
        .edit-order-panel-head h6,
        .edit-order-toolbar h6 {
            color: #0f172a;
            font-size: 1rem;
            font-weight: 900;
        }
        .edit-order-panel-head p,
        .edit-order-toolbar p {
            color: #64748b;
            font-size: .88rem;
            font-weight: 700;
            line-height: 1.35;
        }
        .edit-order-total-chip {
            background: linear-gradient(135deg, #0f766e, #115e59);
            border-radius: 14px;
            color: #fff;
            min-width: 180px;
            padding: 12px 14px;
            text-align: right;
        }
        .edit-order-total-chip span {
            display: block;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
            opacity: .85;
            text-transform: uppercase;
        }
        .edit-order-total-chip strong {
            display: block;
            font-size: 1.3rem;
            font-weight: 900;
            line-height: 1.1;
            margin-top: 2px;
        }
        .edit-order-panel-info .form-control,
        .edit-order-panel-info .select2-selection {
            background: #fcfffe;
        }
        .edit-order-client-meta,
        .edit-order-meta-strip {
            background: linear-gradient(135deg, #f8fafc, #eef6f3);
            border: 1px solid #dbe7e3;
            border-radius: 12px;
            color: #334155;
            font-weight: 700;
            min-height: 44px;
            padding: 10px 12px;
        }
        .edit-order-meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            margin-bottom: 12px;
        }
        .edit-order-toolbar {
            align-items: center;
            margin-bottom: 14px;
        }
        .edit-order-add-btn {
            border-radius: 999px;
            box-shadow: 0 10px 18px rgba(37, 99, 235, .18);
            font-weight: 900;
            padding-left: 14px;
            padding-right: 14px;
            white-space: nowrap;
        }
        .edit-order-hintbar {
            align-items: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            color: #475569;
            display: flex;
            flex-wrap: wrap;
            font-size: .82rem;
            font-weight: 700;
            gap: 10px 18px;
            margin-bottom: 12px;
            padding: 10px 12px;
        }
        .edit-order-hintbar i {
            color: #0f766e;
            margin-right: 6px;
        }
        .edit-order-table-shell {
            background: #fff;
            border: 1px solid #dbe5ee;
            border-radius: 14px;
            min-width: 100%;
            overflow-y: visible;
            overflow-x: visible;
        }
        #tablaEditarPedidoContabilizado {
            margin-bottom: 0;
            min-width: 100%;
            table-layout: fixed;
            width: 100%;
        }
        #tablaEditarPedidoContabilizado th:nth-child(1),
        #tablaEditarPedidoContabilizado td:nth-child(1) { width: 27%; }
        #tablaEditarPedidoContabilizado th:nth-child(2),
        #tablaEditarPedidoContabilizado td:nth-child(2) { width: 22%; }
        #tablaEditarPedidoContabilizado th:nth-child(3),
        #tablaEditarPedidoContabilizado td:nth-child(3) { width: 10%; }
        #tablaEditarPedidoContabilizado th:nth-child(4),
        #tablaEditarPedidoContabilizado td:nth-child(4) { width: 14%; }
        #tablaEditarPedidoContabilizado th:nth-child(5),
        #tablaEditarPedidoContabilizado td:nth-child(5) { width: 10%; }
        #tablaEditarPedidoContabilizado th:nth-child(6),
        #tablaEditarPedidoContabilizado td:nth-child(6) { width: 11%; }
        #tablaEditarPedidoContabilizado th:nth-child(7),
        #tablaEditarPedidoContabilizado td:nth-child(7) { width: 6%; }
        #tablaEditarPedidoContabilizado thead th {
            background: #f1f5f9;
            border-top: 0;
            color: #334155;
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        #tablaEditarPedidoContabilizado tfoot th {
            background: #f8fafc;
            color: #0f172a;
            font-size: .88rem;
            font-weight: 900;
        }
        #tablaEditarPedidoContabilizado tbody td,
        #tablaEditarPedidoContabilizado tfoot th,
        #tablaEditarPedidoContabilizado thead th {
            vertical-align: top;
            white-space: normal;
            word-break: break-word;
        }
        #tablaEditarPedidoContabilizado tbody tr:nth-child(even) {
            background: #fcfefd;
        }
        #tablaEditarPedidoContabilizado tbody tr:hover {
            background: #f8fbff;
        }
        #tablaEditarPedidoContabilizado .form-control {
            border-radius: 10px;
            min-height: 42px;
            min-width: 0;
            width: 100%;
        }
        #tablaEditarPedidoContabilizado .row-remove-btn {
            border-radius: 10px;
            font-weight: 800;
            min-width: 42px;
        }
        .edit-stock-badge {
            background: #effaf6;
            border: 1px solid #b7e4cf;
            border-radius: 10px;
            color: #166534;
            display: inline-block;
            font-weight: 800;
            padding: 6px 8px;
        }
        .edit-subtotal,
        .edit-precio {
            color: #0f172a;
            font-weight: 800;
            white-space: nowrap;
        }
        .edit-order-empty td {
            background: #fcfffd;
            color: #64748b;
            font-size: .92rem;
            font-weight: 700;
            padding: 24px 16px;
            text-align: center;
        }
        .edit-order-modal-footer {
            border-top: 1px solid #dde7e4;
            padding: 14px 22px 18px;
        }
        .edit-order-modal-footer .btn {
            border-radius: 10px;
            font-weight: 800;
            min-width: 132px;
        }
        .select2-container {
            max-width: none;
            min-width: 0;
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            background: #fcfffe;
            border: 1px solid #cbd5e1;
            border-radius: 10px !important;
            height: 46px !important;
            line-height: 44px;
            overflow: hidden;
            transition: border-color .2s ease, box-shadow .2s ease;
            width: 100%;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            line-height: 44px !important;
            overflow: hidden;
            padding-left: 12px !important;
            padding-right: 38px !important;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            position: absolute;
            right: 10px;
            top: 0;
            width: 18px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: 6px;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
        }
        .select2-container--open {
            z-index: 3000;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
            overflow: hidden;
            z-index: 3000;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
            outline: 0;
        }
        .select2-search--dropdown {
            background: #f8fafc;
            padding: 10px;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            min-height: 38px;
            padding: 8px 10px;
        }
        .select2-results__option {
            font-size: .92rem;
            line-height: 1.35;
            padding: 9px 12px;
            white-space: normal;
            word-break: break-word;
        }
        .select2-results {
            max-height: 260px;
            overflow-y: auto;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #0f766e;
            color: #fff;
        }
        .modal-open .modal {
            overflow-y: auto !important;
        }
        @media (max-width: 991.98px) {
            .sales-orders-header,
            .edit-order-panel-head,
            .edit-order-toolbar {
                flex-direction: column;
            }
            .sales-orders-summary,
            .edit-order-total-chip {
                min-width: 0;
                text-align: left;
                width: 100%;
            }
            .edit-order-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .edit-order-table-shell {
                overflow-x: auto;
            }
            #tablaEditarPedidoContabilizado {
                min-width: 900px;
                table-layout: auto;
            }
        }
        @media (max-width: 767.98px) {
            .edit-order-grid {
                grid-template-columns: 1fr;
            }
            .edit-order-modal-dialog {
                margin: .5rem auto;
                max-width: calc(100vw - 1rem);
                width: calc(100vw - 1rem);
            }
            .edit-order-modal-body,
            .edit-order-modal-header,
            .edit-order-modal-footer {
                padding-left: 14px;
                padding-right: 14px;
            }
            .edit-order-modal-footer .btn {
                min-width: 0;
                width: 100%;
            }
            .edit-order-table-shell {
                overflow-x: auto;
            }
            #tablaEditarPedidoContabilizado {
                min-width: 860px;
                table-layout: auto;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let tablaVentasPorPedido;
        let pedidoContabilizadoEditando = null;
        let pedidoContabilizadoAEliminar = null;
        let filaPedidoContabilizadoIndice = 0;

        $(document).ready(function () {
            tablaVentasPorPedido = $('#tabla-ventas').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar Nro. pedido',
                    searchPlaceholder: 'Ej.: 12855'
                },
                ajax: "{{ route('administrador.ventas.administrador.verVentaPorFechaArqueo', ':fecha_arqueo') }}".replace(':fecha_arqueo', '{{ $fecha_arqueo }}'),
                columns: [
                    { data: 'numero_pedido', name: 'numero_pedido' },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'fecha_pedido', name: 'fecha_pedido' },
                    { data: 'fecha_entrega', name: 'fecha_entrega' },
                    { data: 'monto_contabilizado', name: 'monto_contabilizado' },
                    { data: 'preventista', name: 'preventista', orderable: true, searchable: true },
                    { data: 'ruta', name: 'ruta' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
                order: [[0, 'asc']]
            });

            inicializarClienteSelectContabilizado();

            $('#btnAgregarFilaPedidoContabilizado').on('click', function () {
                agregarFilaPedidoContabilizado();
            });

            $('#editarPedidoContabilizadoForm').on('submit', function (event) {
                event.preventDefault();
                guardarEdicionPedidoContabilizado();
            });

            $('#editarPedidoContabilizadoModal').on('hidden.bs.modal', function () {
                limpiarModalPedidoContabilizado();
            });

            $('#btnConfirmarEliminarPedidoContabilizado').on('click', function () {
                ejecutarEliminacionPedidoContabilizado();
            });
        });

        function abrirModalEditarPedidoContabilizado(button) {
            const numeroPedido = $(button).data('numero-pedido');
            pedidoContabilizadoEditando = numeroPedido;

            $('#editarPedidoContabilizadoModal').modal('show');
            $('#editar-pedido-contabilizado-resumen').text('Cargando informacion del pedido...');
            renderEstadoVacioPedidoContabilizado('Cargando lineas del pedido contabilizado...');
            $('#edit-total-pedido-contabilizado').text('Bs 0.00');
            $('#edit-total-pedido-contabilizado-footer').text('Bs 0.00');

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.editar.contabilizados.datos', ':pedido') }}".replace(':pedido', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    $('#btnGuardarPedidoContabilizadoModal').prop('disabled', true);
                },
                success: function (response) {
                    cargarPedidoContabilizadoEnModal(response);
                },
                error: function (xhr) {
                    $('#editarPedidoContabilizadoModal').modal('hide');
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el pedido contabilizado.', 'error');
                },
                complete: function () {
                    $('#btnGuardarPedidoContabilizadoModal').prop('disabled', false);
                }
            });
        }

        function abrirModalEliminarPedidoContabilizado(button) {
            pedidoContabilizadoAEliminar = $(button).data('numero-pedido');
            $('#eliminar-pedido-contabilizado-numero').text(`Pedido #${String(pedidoContabilizadoAEliminar).padStart(6, '0')}`);
            $('#eliminarPedidoContabilizadoModal').modal('show');
        }

        function cargarPedidoContabilizadoEnModal(response) {
            const pedido = response.pedido;

            $('#editar-numero-pedido-contabilizado').val(pedido.numero_pedido);
            $('#edit-pedido-contabilizado-numero-visible').val(`#${String(pedido.numero_pedido).padStart(6, '0')}`);
            $('#edit-id-usuario-contabilizado').val(String(pedido.id_usuario));
            $('#edit-fecha-pedido-contabilizado').val(pedido.fecha_pedido);
            $('#edit-fecha-entrega-contabilizado').text(pedido.fecha_entrega || 'N/A');
            $('#edit-fecha-contabilizacion-contabilizado').text(pedido.fecha_contabilizacion || 'N/A');
            $('#editar-pedido-contabilizado-resumen').text(`Pedido #${String(pedido.numero_pedido).padStart(6, '0')} listo para editar.`);
            $('#edit-cliente-meta-contabilizado').html(`
                <strong>Ruta:</strong> ${pedido.cliente_ruta || 'Sin ruta'}
                <span class="mx-2">|</span>
                <strong>Direccion:</strong> ${pedido.cliente_direccion || 'Sin direccion'}
            `);

            const clienteOption = new Option(pedido.cliente_texto, pedido.id_cliente, true, true);
            $('#edit-id-cliente-contabilizado').append(clienteOption).trigger('change');

            $('#tablaEditarPedidoContabilizado tbody').empty();
            response.items.forEach(item => agregarFilaPedidoContabilizado(item));
            recalcularTotalPedidoContabilizado();
        }

        function limpiarModalPedidoContabilizado() {
            pedidoContabilizadoEditando = null;
            $('#editarPedidoContabilizadoForm')[0].reset();
            $('#edit-id-cliente-contabilizado').empty().trigger('change');
            renderEstadoVacioPedidoContabilizado('Agrega productos para comenzar a editar el pedido contabilizado.');
            $('#edit-cliente-meta-contabilizado').empty();
            $('#editar-pedido-contabilizado-resumen').text('Cargando informacion del pedido...');
            $('#edit-total-pedido-contabilizado').text('Bs 0.00');
            $('#edit-total-pedido-contabilizado-footer').text('Bs 0.00');
            $('#edit-fecha-entrega-contabilizado').text('N/A');
            $('#edit-fecha-contabilizacion-contabilizado').text('N/A');
        }

        function renderEstadoVacioPedidoContabilizado(mensaje) {
            $('#tablaEditarPedidoContabilizado tbody').html(`
                <tr class="edit-order-empty">
                    <td colspan="7">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.15rem;color:#94a3b8;"></i>
                        ${mensaje}
                    </td>
                </tr>
            `);
        }

        function inicializarClienteSelectContabilizado() {
            $('#edit-id-cliente-contabilizado').select2({
                dropdownParent: $('#editarPedidoContabilizadoModal .modal-content'),
                placeholder: 'Buscar cliente por nombre o apellido',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('administrador.clientes.buscar') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(cliente => ({
                                id: cliente.id,
                                text: `${cliente.codigo_cliente || ''} ${cliente.nombres || ''} ${cliente.apellidos || ''}`.trim(),
                                ruta: cliente.ruta?.nombre_ruta || 'Sin ruta',
                                direccion: `${cliente.calle_avenida || ''} ${cliente.zona_barrio || ''}`.trim()
                            }))
                        };
                    }
                }
            }).on('select2:select', function (event) {
                const cliente = event.params.data;
                $('#edit-cliente-meta-contabilizado').html(`
                    <strong>Ruta:</strong> ${cliente.ruta || 'Sin ruta'}
                    <span class="mx-2">|</span>
                    <strong>Direccion:</strong> ${cliente.direccion || 'Sin direccion'}
                `);
            });
        }

        function agregarFilaPedidoContabilizado(item = null) {
            filaPedidoContabilizadoIndice += 1;
            const rowKey = `pedido-contabilizado-row-${filaPedidoContabilizadoIndice}`;
            $('#tablaEditarPedidoContabilizado tbody .edit-order-empty').remove();

            const rowHtml = `
                <tr data-row-key="${rowKey}">
                    <td>
                        <input type="hidden" class="row-pedido-id" value="${item?.pedido_id || ''}">
                        <select class="form-control row-producto-select"></select>
                    </td>
                    <td>
                        <select class="form-control row-forma-venta-select"></select>
                    </td>
                    <td>
                        <input type="number" min="1" class="form-control row-cantidad-input" value="${item?.cantidad || 1}">
                    </td>
                    <td>
                        <span class="edit-stock-badge row-stock-label">${item ? `${item.stock_actual} ${item.detalle_cantidad}` : 'Sin producto'}</span>
                    </td>
                    <td class="text-right">
                        <span class="edit-precio row-precio-label">Bs ${(Number(item?.precio_unitario || 0)).toFixed(2)}</span>
                    </td>
                    <td class="text-right">
                        <span class="edit-subtotal row-subtotal-label">Bs ${(Number(item?.subtotal || 0)).toFixed(2)}</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm row-remove-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#tablaEditarPedidoContabilizado tbody').append(rowHtml);
            const $row = $(`#tablaEditarPedidoContabilizado tbody tr[data-row-key="${rowKey}"]`);

            inicializarSelectProductoFilaContabilizado($row, item);
            poblarFormasVentaFilaContabilizado($row, item?.formas_venta || [], item?.forma_venta_id || null, item?.precio_unitario || null);

            $row.on('change', '.row-forma-venta-select', function () {
                actualizarPrecioDeFilaContabilizado($row);
                recalcularSubtotalFilaContabilizado($row);
            });

            $row.on('input', '.row-cantidad-input', function () {
                recalcularSubtotalFilaContabilizado($row);
            });

            $row.on('click', '.row-remove-btn', function () {
                $row.remove();
                if (!$('#tablaEditarPedidoContabilizado tbody tr').length) {
                    renderEstadoVacioPedidoContabilizado('Agrega productos para comenzar a editar el pedido contabilizado.');
                }
                recalcularTotalPedidoContabilizado();
            });

            recalcularSubtotalFilaContabilizado($row);

            const tableShell = document.querySelector('#editarPedidoContabilizadoModal .edit-order-table-shell');
            if (tableShell) {
                requestAnimationFrame(() => {
                    tableShell.scrollTop = tableShell.scrollHeight;
                });
            }
        }

        function inicializarSelectProductoFilaContabilizado($row, item = null) {
            const $productoSelect = $row.find('.row-producto-select');

            $productoSelect.select2({
                dropdownParent: $('#editarPedidoContabilizadoModal .modal-content'),
                placeholder: 'Buscar producto',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('administrador.productos.obtenerProductosParaEdicion') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(producto => ({
                                id: producto.id,
                                text: `${producto.codigo} - ${producto.nombre_producto}`,
                                codigo: producto.codigo,
                                detalle_cantidad: producto.detalle_cantidad,
                                stock_actual: Number(producto.cantidad || 0)
                            }))
                        };
                    }
                }
            }).on('select2:select', function (event) {
                const producto = event.params.data;
                actualizarMetaProductoFilaContabilizado($row, producto);
                cargarFormasVentaProductoContabilizado($row, producto.id);
            });

            if (item) {
                const selectedOption = new Option(item.producto_texto, item.producto_id, true, true);
                $productoSelect.append(selectedOption).trigger('change');
                actualizarMetaProductoFilaContabilizado($row, {
                    stock_actual: item.stock_actual,
                    detalle_cantidad: item.detalle_cantidad,
                    codigo: item.producto_codigo
                });
            }
        }

        function actualizarMetaProductoFilaContabilizado($row, producto) {
            const stockTexto = `${Number(producto.stock_actual || 0)} ${producto.detalle_cantidad || ''}`.trim();
            $row.find('.row-stock-label').text(stockTexto || 'Sin stock');
            $row.data('producto-codigo', producto.codigo || '');
            $row.data('detalle-cantidad', producto.detalle_cantidad || '');
            $row.data('stock-actual', Number(producto.stock_actual || 0));
        }

        function cargarFormasVentaProductoContabilizado($row, productoId) {
            $.ajax({
                url: "{{ route('administrador.productos.mostrarFormasVenta', ':id') }}".replace(':id', productoId),
                type: 'GET',
                success: function (formasVenta) {
                    poblarFormasVentaFilaContabilizado($row, formasVenta, null, null);
                    recalcularSubtotalFilaContabilizado($row);
                },
                error: function () {
                    poblarFormasVentaFilaContabilizado($row, [], null, null);
                    recalcularSubtotalFilaContabilizado($row);
                }
            });
        }

        function poblarFormasVentaFilaContabilizado($row, formasVenta, selectedId = null, precioActual = null) {
            const $formaSelect = $row.find('.row-forma-venta-select');
            $formaSelect.empty();

            if (!formasVenta.length) {
                $formaSelect.append('<option value="">Sin formas de venta</option>');
                $row.find('.row-precio-label').text('Bs 0.00');
                return;
            }

            formasVenta.forEach(function (forma) {
                const selected = String(forma.id) === String(selectedId) ? 'selected' : '';
                const inactiva = forma.activo === false ? ' (inactiva)' : '';
                $formaSelect.append(`<option value="${forma.id}" data-precio="${forma.precio_venta}" ${selected}>${forma.tipo_venta}${inactiva}</option>`);
            });

            if (selectedId === null) {
                $formaSelect.prop('selectedIndex', 0);
            }

            if (precioActual !== null && selectedId !== null) {
                $row.find('.row-precio-label').text(`Bs ${Number(precioActual).toFixed(2)}`);
            } else {
                actualizarPrecioDeFilaContabilizado($row);
            }
        }

        function actualizarPrecioDeFilaContabilizado($row) {
            const selectedOption = $row.find('.row-forma-venta-select option:selected');
            const precio = Number(selectedOption.data('precio') || 0);
            $row.find('.row-precio-label').text(`Bs ${precio.toFixed(2)}`);
        }

        function recalcularSubtotalFilaContabilizado($row) {
            const cantidad = Number($row.find('.row-cantidad-input').val() || 0);
            const precioTexto = $row.find('.row-precio-label').text().replace('Bs', '').trim();
            const precio = Number(precioTexto || 0);
            const subtotal = cantidad * precio;
            $row.find('.row-subtotal-label').text(`Bs ${subtotal.toFixed(2)}`);
            recalcularTotalPedidoContabilizado();
        }

        function recalcularTotalPedidoContabilizado() {
            let total = 0;

            $('#tablaEditarPedidoContabilizado tbody tr').each(function () {
                const subtotalTexto = $(this).find('.row-subtotal-label').text().replace('Bs', '').trim();
                total += Number(subtotalTexto || 0);
            });

            $('#edit-total-pedido-contabilizado').text(`Bs ${total.toFixed(2)}`);
            $('#edit-total-pedido-contabilizado-footer').text(`Bs ${total.toFixed(2)}`);
        }

        function construirPayloadPedidoContabilizado() {
            const items = [];
            let filaInvalida = false;

            $('#tablaEditarPedidoContabilizado tbody tr').each(function () {
                const $row = $(this);
                const productoId = $row.find('.row-producto-select').val();
                const formaVentaId = $row.find('.row-forma-venta-select').val();
                const cantidad = $row.find('.row-cantidad-input').val();

                if (!productoId || !formaVentaId || !cantidad || Number(cantidad) < 1) {
                    filaInvalida = true;
                    return;
                }

                items.push({
                    pedido_id: $row.find('.row-pedido-id').val() || null,
                    producto_id: Number(productoId),
                    tipo_venta_id: Number(formaVentaId),
                    cantidad: Number(cantidad)
                });
            });

            return {
                fila_invalida: filaInvalida,
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                id_cliente: $('#edit-id-cliente-contabilizado').val(),
                id_usuario: $('#edit-id-usuario-contabilizado').val(),
                fecha_pedido: $('#edit-fecha-pedido-contabilizado').val(),
                items: items
            };
        }

        function guardarEdicionPedidoContabilizado() {
            const payload = construirPayloadPedidoContabilizado();

            if (!payload.id_cliente || !payload.id_usuario || !payload.fecha_pedido) {
                Swal.fire('Atencion', 'Completa los datos generales del pedido antes de guardar.', 'warning');
                return;
            }

            if (payload.fila_invalida) {
                Swal.fire('Atencion', 'Hay filas incompletas o con cantidad invalida. Revísalas antes de guardar.', 'warning');
                return;
            }

            if (!payload.items.length) {
                Swal.fire('Atencion', 'Debes dejar al menos una linea de producto en el pedido.', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.actualizarCompleto.contabilizado', ':pedido') }}".replace(':pedido', pedidoContabilizadoEditando),
                type: 'POST',
                data: payload,
                beforeSend: function () {
                    $('#btnGuardarPedidoContabilizadoModal').prop('disabled', true);
                    Swal.fire({
                        title: 'Guardando cambios',
                        html: 'Recalculando stock y reconstruyendo la venta contabilizada...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    actualizarTotalesArqueo(response);
                    Swal.fire('Listo', response.message, 'success');
                    $('#editarPedidoContabilizadoModal').modal('hide');
                    tablaVentasPorPedido.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const response = xhr.responseJSON || {};
                    let mensaje = response.message || 'No se pudo actualizar el pedido contabilizado.';

                    if (response.errors) {
                        const primerCampo = Object.keys(response.errors)[0];
                        if (primerCampo && response.errors[primerCampo]?.length) {
                            mensaje = response.errors[primerCampo][0];
                        }
                    }

                    Swal.fire('Error', mensaje, 'error');
                },
                complete: function () {
                    $('#btnGuardarPedidoContabilizadoModal').prop('disabled', false);
                }
            });
        }

        function ejecutarEliminacionPedidoContabilizado() {
            if (!pedidoContabilizadoAEliminar) {
                $('#eliminarPedidoContabilizadoModal').modal('hide');
                return;
            }

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.eliminarCompleto.contabilizado', ':pedido') }}".replace(':pedido', pedidoContabilizadoAEliminar),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                beforeSend: function () {
                    $('#btnConfirmarEliminarPedidoContabilizado').prop('disabled', true);
                    Swal.fire({
                        title: 'Eliminando pedido',
                        html: 'Devolviendo productos al inventario y retirando la venta contabilizada...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    $('#eliminarPedidoContabilizadoModal').modal('hide');
                    actualizarTotalesArqueo(response);
                    Swal.fire('Listo', response.message, 'success');
                    tablaVentasPorPedido.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const mensaje = xhr.responseJSON?.message || 'No se pudo eliminar el pedido contabilizado.';
                    Swal.fire('Error', mensaje, 'error');
                },
                complete: function () {
                    $('#btnConfirmarEliminarPedidoContabilizado').prop('disabled', false);
                    pedidoContabilizadoAEliminar = null;
                }
            });
        }

        function actualizarTotalesArqueo(response) {
            if (typeof response.arqueo_total === 'undefined') {
                return;
            }

            const monto = `Bs ${Number(response.arqueo_total || 0).toFixed(2)}`;
            $('#arqueo-total-header').text(monto);
            $('#arqueo-total-footer').text(monto);
        }
    </script>
@stop
