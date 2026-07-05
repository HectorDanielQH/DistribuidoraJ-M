@extends('adminlte::page')

@section('title', 'Pedidos pendientes')

@section('content_header')
    <div class="orders-header">
        <div>
            <span>Preventa / preparacion</span>
            <h1>Pedidos pendientes</h1>
            <p>Pedidos tomados por preventistas. Aun no son venta: estan reservados para preparar despacho.</p>
        </div>
        <div class="orders-header-actions">
            <a href="{{ route('pedidos.administrador.visualizacionParaDespachado') }}" class="btn btn-info orders-main-btn">
                <i class="fas fa-boxes"></i> Ver cantidad para despacho
            </a>
            <button class="btn btn-success orders-main-btn" id="btnDespacharPedidos">
                <i class="fas fa-truck"></i> Entregar al repartidor
            </button>
        </div>
    </div>
@stop

@section('content')
    <section class="orders-flow">
        <article>
            <span>Pendientes</span>
            <strong>{{ $resumenPedidos['pendientes'] ?? 0 }}</strong>
            <small>Pedidos por preparar</small>
        </article>
        <article>
            <span>Productos</span>
            <strong>{{ $resumenPedidos['pendientes_items'] ?? 0 }}</strong>
            <small>Lineas reservadas</small>
        </article>
        <article>
            <span>Total estimado</span>
            <strong>Bs {{ number_format($resumenPedidos['pendientes_total'] ?? 0, 2, '.', ',') }}</strong>
            <small>No es caja cerrada</small>
        </article>
        <article>
            <span>Despachados</span>
            <strong>{{ $resumenPedidos['despachados'] ?? 0 }}</strong>
            <small>En manos del repartidor</small>
        </article>
    </section>

    <section class="orders-filters" aria-label="Filtros de pedidos pendientes">
        <label>
            Ruta
            <select id="filtro-ruta" class="form-control orders-filter">
                <option value="">Todas las rutas</option>
                @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Preventista
            <select id="filtro-preventista" class="form-control orders-filter">
                <option value="">Todos</option>
                @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Fecha pedido
            <input type="date" id="filtro-fecha" class="form-control orders-filter">
        </label>
        <label>
            Filas por pagina
            <select id="filas-pagina" class="form-control">
                <option value="10">10 pedidos</option>
                <option value="25">25 pedidos</option>
                <option value="50">50 pedidos</option>
                <option value="100">100 pedidos</option>
                <option value="-1">Todos</option>
            </select>
        </label>
        <button class="btn btn-outline-secondary orders-main-btn" id="limpiar-filtros">
            <i class="fas fa-eraser"></i> Limpiar filtros
        </button>
    </section>

    <section class="orders-table-shell">
        <table class="table table-striped table-bordered" id="pedidosTabla">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Direccion</th>
                    <th>Ruta</th>
                    <th>Preventista</th>
                    <th>Fecha</th>
                    <th>Resumen</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </section>

    <div class="modal fade" id="editarPedidoModal" tabindex="-1" role="dialog" aria-labelledby="editarPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl edit-order-modal-dialog" role="document">
            <div class="modal-content edit-order-modal-content">
                <div class="modal-header edit-order-modal-header">
                    <div class="edit-order-modal-heading">
                        <span class="edit-order-kicker">Gestion interna</span>
                        <h5 class="modal-title mb-1" id="editarPedidoModalLabel">Editar pedido pendiente</h5>
                        <small class="text-muted" id="editar-pedido-resumen">Cargando informacion del pedido...</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editarPedidoForm">
                    @csrf
                    <div class="modal-body edit-order-modal-body">
                        <input type="hidden" id="editar-numero-pedido" name="numero_pedido">

                        <div class="edit-order-layout">
                            <section class="edit-order-panel edit-order-panel-info">
                                <div class="edit-order-panel-head">
                                    <div>
                                        <h6 class="mb-1">Datos generales</h6>
                                        <p class="mb-0">Actualiza cabecera, cliente y preventista sin salir del listado.</p>
                                    </div>
                                    <div class="edit-order-total-chip">
                                        <span>Total actual</span>
                                        <strong id="edit-total-pedido">Bs 0.00</strong>
                                    </div>
                                </div>

                                <div class="edit-order-grid">
                                    <label>
                                        Numero pedido
                                        <input type="text" class="form-control" id="edit-pedido-numero-visible" readonly>
                                    </label>
                                    <label>
                                        Cliente
                                        <select id="edit-id-cliente" name="id_cliente" class="form-control"></select>
                                    </label>
                                    <label>
                                        Preventista
                                        <select id="edit-id-usuario" name="id_usuario" class="form-control">
                                            @foreach($preventistas as $preventista)
                                                <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>
                                        Fecha pedido
                                        <input type="datetime-local" class="form-control" id="edit-fecha-pedido" name="fecha_pedido" required>
                                    </label>
                                </div>

                                <div class="edit-order-client-meta" id="edit-cliente-meta"></div>
                            </section>

                            <section class="edit-order-panel edit-order-panel-lines">
                                <div class="edit-order-toolbar">
                                    <div>
                                        <h6 class="mb-1">Productos del pedido</h6>
                                        <p class="mb-0">Agrega, corrige o elimina filas. La tabla mantiene scroll interno cuando el pedido crece.</p>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm edit-order-add-btn" id="btnAgregarFilaPedido">
                                        <i class="fas fa-plus"></i> Agregar fila
                                    </button>
                                </div>

                                <div class="edit-order-hintbar">
                                    <span><i class="fas fa-info-circle"></i> El precio se conserva por linea cuando no cambias producto ni forma de venta.</span>
                                    <span><i class="fas fa-box-open"></i> El stock mostrado corresponde al inventario disponible actual.</span>
                                </div>

                                <div class="edit-order-table-shell">
                                    <table class="table table-bordered align-middle mb-0" id="tablaEditarPedido">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 280px;">Producto</th>
                                                <th style="min-width: 230px;">Forma de venta</th>
                                                <th style="width: 120px;">Cantidad</th>
                                                <th style="width: 190px;">Stock</th>
                                                <th style="width: 140px;">Precio</th>
                                                <th style="width: 150px;">Subtotal</th>
                                                <th style="width: 96px;">Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-right">Total</th>
                                                <th id="edit-total-pedido-footer" class="text-right">Bs 0.00</th>
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
                        <button type="submit" class="btn btn-success" id="btnGuardarPedidoModal">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eliminarPedidoModal" tabindex="-1" role="dialog" aria-labelledby="eliminarPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarPedidoModalLabel">Confirmar eliminacion de pedido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong id="eliminar-pedido-numero"></strong></p>
                    <p class="mb-0">¿Está seguro de eliminar este pedido? Esta acción devolverá los productos al inventario y no se podrá deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminarPedido">
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
        .orders-header, .orders-flow, .orders-filters, .orders-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .orders-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .orders-header span, .orders-flow span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .orders-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .orders-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .orders-header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .orders-main-btn, .order-action-btn {
            border-radius: 8px;
            font-weight: 900;
            min-height: 40px;
        }
        .orders-flow {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .orders-flow article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .orders-flow strong {
            display: block;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .orders-flow small {
            color: #64748b;
            font-weight: 800;
        }
        .orders-filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
            align-items: end;
        }
        .orders-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .orders-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .orders-table-shell table {
            width: 100% !important;
        }
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before {
            background-color: #0f766e;
            border-radius: 8px;
            box-shadow: none;
        }
        .order-number {
            font-weight: 900;
            color: #0f766e;
            white-space: nowrap;
        }
        .order-client {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }
        .order-client span, .order-summary-mini span {
            color: #64748b;
            font-weight: 700;
        }
        .order-summary-mini {
            display: flex;
            flex-direction: column;
        }
        .order-total {
            color: #166534;
            white-space: nowrap;
        }
        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            padding: 6px 8px;
            font-weight: 900;
            white-space: nowrap;
        }
        .order-status-pending {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .order-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .order-detail-list {
            display: grid;
            gap: 10px;
            text-align: left;
        }
        .order-detail-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }
        .order-detail-item strong {
            display: block;
            color: #17211d;
        }
        .order-detail-meta {
            color: #64748b;
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
            display: flex;
            margin: 1rem auto;
            max-width: min(1380px, calc(100vw - 2rem));
        }
        .edit-order-modal-content {
            background: linear-gradient(180deg, #f8fbfa 0%, #edf4f2 100%);
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            height: 100%;
            max-height: 100%;
            overflow: hidden;
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
        .edit-order-modal-heading {
            min-width: 0;
        }
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
        .edit-order-modal-header small {
            display: block;
            font-size: .92rem;
            line-height: 1.4;
        }
        .edit-order-modal-body {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            overflow-x: hidden;
            padding: 18px 22px 14px;
        }
        .edit-order-layout {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            gap: 16px;
            min-height: 0;
            min-width: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            width: 100%;
        }
        .edit-order-panel {
            background: rgba(255, 255, 255, .95);
            border: 1px solid #dce8e4;
            border-radius: 16px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
        }
        .edit-order-panel-info {
            padding: 16px;
        }
        .edit-order-panel-lines {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 420px;
            min-width: 0;
            overflow: hidden;
            padding: 16px;
        }
        .edit-order-panel-head {
            align-items: flex-start;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            margin-bottom: 14px;
        }
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
        .edit-order-client-meta {
            background: linear-gradient(135deg, #f8fafc, #eef6f3);
            border: 1px solid #dbe7e3;
            border-radius: 12px;
            color: #334155;
            font-weight: 700;
            min-height: 44px;
            padding: 10px 12px;
        }
        .edit-order-toolbar {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 12px;
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
            border: 1px solid #dbe5ee;
            border-radius: 14px;
            flex: 1 1 auto;
            min-height: 280px;
            min-width: 0;
            max-height: min(52vh, 560px);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-gutter: stable both-edges;
            background: #fff;
        }
        #tablaEditarPedido {
            margin-bottom: 0;
            table-layout: fixed;
            width: 100%;
        }
        #tablaEditarPedido th:nth-child(1),
        #tablaEditarPedido td:nth-child(1) { width: 27%; }
        #tablaEditarPedido th:nth-child(2),
        #tablaEditarPedido td:nth-child(2) { width: 22%; }
        #tablaEditarPedido th:nth-child(3),
        #tablaEditarPedido td:nth-child(3) { width: 10%; }
        #tablaEditarPedido th:nth-child(4),
        #tablaEditarPedido td:nth-child(4) { width: 14%; }
        #tablaEditarPedido th:nth-child(5),
        #tablaEditarPedido td:nth-child(5) { width: 10%; }
        #tablaEditarPedido th:nth-child(6),
        #tablaEditarPedido td:nth-child(6) { width: 11%; }
        #tablaEditarPedido th:nth-child(7),
        #tablaEditarPedido td:nth-child(7) { width: 6%; }
        #tablaEditarPedido thead th {
            background: #f1f5f9;
            border-top: 0;
            color: #334155;
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .03em;
            position: sticky;
            text-transform: uppercase;
            top: 0;
            z-index: 2;
        }
        #tablaEditarPedido tfoot th {
            background: #f8fafc;
            bottom: 0;
            color: #0f172a;
            font-size: .88rem;
            font-weight: 900;
            position: sticky;
            z-index: 2;
        }
        #tablaEditarPedido tbody td,
        #tablaEditarPedido tfoot th,
        #tablaEditarPedido thead th {
            vertical-align: middle;
        }
        #tablaEditarPedido tbody tr:nth-child(even) {
            background: #fcfefd;
        }
        #tablaEditarPedido tbody tr:hover {
            background: #f8fbff;
        }
        #tablaEditarPedido .form-control {
            border-radius: 10px;
            min-height: 42px;
            min-width: 0;
            width: 100%;
        }
        #tablaEditarPedido .row-remove-btn {
            border-radius: 10px;
            font-weight: 800;
            min-width: 42px;
        }
        #tablaEditarPedido .row-cantidad-input {
            min-width: 0;
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
            min-width: 150px;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            height: 42px;
            padding: 6px 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a;
            font-weight: 700;
            line-height: 28px;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-left: 0;
            white-space: nowrap;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        @media (max-width: 767.98px) {
            .orders-header, .orders-header-actions { flex-direction: column; }
            .orders-main-btn, .order-action-btn { width: 100%; }
            .orders-flow, .orders-filters { grid-template-columns: 1fr; }
            .edit-order-grid { grid-template-columns: 1fr; }
            .order-actions { flex-direction: column; }
            .orders-table-shell { padding: 8px; }
            .orders-table-shell .table td,
            .orders-table-shell .table th {
                font-size: .9rem;
                vertical-align: middle;
            }
            .order-client {
                min-width: 0;
            }
            .edit-order-modal-dialog {
                margin: .5rem auto;
                max-width: calc(100vw - 1rem);
            }
            .edit-order-modal-content {
                height: 100%;
                max-height: 100%;
                border-radius: 16px;
            }
            .edit-order-modal-header,
            .edit-order-modal-body,
            .edit-order-modal-footer {
                padding-left: 14px;
                padding-right: 14px;
            }
            .edit-order-panel-head,
            .edit-order-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .edit-order-hintbar {
                align-items: flex-start;
                flex-direction: column;
            }
            .edit-order-total-chip {
                min-width: 0;
                text-align: left;
                width: 100%;
            }
            .edit-order-panel-info,
            .edit-order-panel-lines {
                padding: 12px;
            }
            .edit-order-table-shell {
                max-height: min(48vh, 440px);
            }
            .edit-order-modal-footer .btn {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.3/r-3.0.6/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let tablaPedidos;
        let pedidoEditando = null;
        let pedidoAEliminar = null;
        let filaPedidoIndice = 0;

        $(document).ready(function () {
            tablaPedidos = $('#pedidosTabla').DataTable({
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
                    searchPlaceholder: 'Cliente, pedido o ruta'
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
                dom: "<'row align-items-center mb-2'<'col-md-12'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.visualizacion') }}",
                    data: function (d) {
                        d.ruta_id = $('#filtro-ruta').val();
                        d.preventista_id = $('#filtro-preventista').val();
                        d.fecha_pedido = $('#filtro-fecha').val();
                    }
                },
                columns: [
                    { data:'numero_pedido', name: 'numero_pedido' },
                    { data:'cliente', name: 'cliente' },
                    { data:'direccion', name: 'direccion', orderable: false },
                    { data:'ruta', name: 'ruta', orderable: false },
                    { data:'preventista', name: 'preventista', orderable: false },
                    { data:'fecha_pedido', name: 'fecha_pedido' },
                    { data:'resumen', orderable: false, searchable: false },
                    { data:'total_estimado', orderable: false, searchable: false },
                    { data:'estado', orderable: false, searchable: false },
                    { data:'acciones', orderable: false, searchable: false }
                ],
                columnDefs: [
                    { className: 'dtr-control', targets: 0 },
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 7 },
                    { responsivePriority: 4, targets: 9 },
                    { responsivePriority: 10001, targets: [2, 3, 4, 5, 6, 8] },
                ],
            });

            $('.orders-filter').on('change', function () {
                tablaPedidos.ajax.reload();
            });

            $('#filas-pagina').on('change', function () {
                tablaPedidos.page.len(Number(this.value)).draw();
            });

            $('#limpiar-filtros').on('click', function () {
                $('.orders-filter').val('');
                $('#filas-pagina').val('10');
                tablaPedidos.page.len(10);
                tablaPedidos.ajax.reload();
            });

            inicializarClienteSelect();

            $('#btnAgregarFilaPedido').on('click', function () {
                agregarFilaPedido();
            });

            $('#editarPedidoForm').on('submit', function (event) {
                event.preventDefault();
                guardarEdicionPedido();
            });

            $('#editarPedidoModal').on('hidden.bs.modal', function () {
                limpiarModalPedido();
            });

            $('#btnConfirmarEliminarPedido').on('click', function () {
                ejecutarEliminacionPedido();
            });
        });

        function verPedidoCliente(e) {
            const numeroPedido = $(e).attr('id-numero-pedido');

            $.ajax({
                url: "{{ route('pedidos.administrador.visualizacionPedido', ':id') }}".replace(':id', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Cargando pedido',
                        html: 'Revisando productos reservados...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    const totalPedido = response.pedidos.reduce((sum, item) => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        return sum + ((item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100));
                    }, 0);

                    const detalle = response.pedidos.map(item => {
                        const descuento = item.descripcion_descuento_porcentaje ?? 0;
                        const total = (item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100);
                        const promo = item.promocion ? `Promocion: ${descuento}% ${item.descripcion_regalo || ''}` : 'Sin promocion';

                        return `<div class="order-detail-item">
                            <strong>${item.nombre_producto}</strong>
                            <div class="order-detail-meta">Codigo: ${item.codigo}</div>
                            <div class="order-detail-meta">Solicitado: ${item.cantidad_pedido} ${item.tipo_venta} | Stock actual: ${item.cantidad_stock} ${item.detalle_cantidad}</div>
                            <div class="order-detail-meta">Precio: Bs ${Number(item.precio_venta).toFixed(2)} | ${promo}</div>
                            <strong>Subtotal: Bs ${total.toFixed(2)}</strong>
                        </div>`;
                    }).join('');

                    Swal.fire({
                        title: `Pedido #${response.numero_pedido}`,
                        html: `<div class="order-detail-list">${detalle}<div class="order-detail-item"><strong>Total estimado: Bs ${totalPedido.toFixed(2)}</strong></div></div>`,
                        width: window.innerWidth <= 700 ? '96%' : '720px',
                        showCloseButton: true,
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el pedido.', 'error');
                }
            });
        }

        $('#btnDespacharPedidos').on('click', function () {
            Swal.fire({
                title: 'Entregar pedidos al repartidor?',
                text: 'Los pedidos pendientes pasaran a despachados. Todavia no se registran como venta.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, entregar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('pedidos.administrador.despacharPedido') }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    beforeSend: function () {
                        Swal.fire({ title: 'Despachando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    },
                    success: function (response) {
                        Swal.fire('Listo', response.message, 'success').then(() => {
                            window.location.href = "{{ route('pedidos.administrador.visualizacionDespachados') }}";
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudieron despachar los pedidos.', 'error');
                    }
                });
            });
        });

        function abrirModalEditarPedido(button) {
            const numeroPedido = $(button).data('numero-pedido');
            pedidoEditando = numeroPedido;

            $('#editarPedidoModal').modal('show');
            $('#editar-pedido-resumen').text('Cargando informacion del pedido...');
            renderEstadoVacioPedido('Cargando lineas del pedido...');
            $('#edit-total-pedido').text('Bs 0.00');
            $('#edit-total-pedido-footer').text('Bs 0.00');

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.editar.datos', ':pedido') }}".replace(':pedido', numeroPedido),
                type: 'GET',
                beforeSend: function () {
                    $('#btnGuardarPedidoModal').prop('disabled', true);
                },
                success: function (response) {
                    cargarPedidoEnModal(response);
                },
                error: function (xhr) {
                    $('#editarPedidoModal').modal('hide');
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cargar el pedido para edicion.', 'error');
                },
                complete: function () {
                    $('#btnGuardarPedidoModal').prop('disabled', false);
                }
            });
        }

        function abrirModalEliminarPedido(button) {
            pedidoAEliminar = $(button).data('numero-pedido');
            $('#eliminar-pedido-numero').text(`Pedido #${String(pedidoAEliminar).padStart(6, '0')}`);
            $('#eliminarPedidoModal').modal('show');
        }

        function cargarPedidoEnModal(response) {
            const pedido = response.pedido;

            $('#editar-numero-pedido').val(pedido.numero_pedido);
            $('#edit-pedido-numero-visible').val(`#${String(pedido.numero_pedido).padStart(6, '0')}`);
            $('#edit-id-usuario').val(String(pedido.id_usuario));
            $('#edit-fecha-pedido').val(pedido.fecha_pedido);
            $('#editar-pedido-resumen').text(`Pedido #${String(pedido.numero_pedido).padStart(6, '0')} listo para editar.`);
            $('#edit-cliente-meta').html(`
                <strong>Ruta:</strong> ${pedido.cliente_ruta || 'Sin ruta'}
                <span class="mx-2">|</span>
                <strong>Direccion:</strong> ${pedido.cliente_direccion || 'Sin direccion'}
            `);

            const clienteOption = new Option(pedido.cliente_texto, pedido.id_cliente, true, true);
            $('#edit-id-cliente').append(clienteOption).trigger('change');

            $('#tablaEditarPedido tbody').empty();
            response.items.forEach(item => agregarFilaPedido(item));
            recalcularTotalPedido();
        }

        function limpiarModalPedido() {
            pedidoEditando = null;
            $('#editarPedidoForm')[0].reset();
            $('#edit-id-cliente').empty().trigger('change');
            renderEstadoVacioPedido('Agrega productos para comenzar a editar el pedido.');
            $('#edit-cliente-meta').empty();
            $('#editar-pedido-resumen').text('Cargando informacion del pedido...');
            $('#edit-total-pedido').text('Bs 0.00');
            $('#edit-total-pedido-footer').text('Bs 0.00');
        }

        function renderEstadoVacioPedido(mensaje) {
            $('#tablaEditarPedido tbody').html(`
                <tr class="edit-order-empty">
                    <td colspan="7">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.15rem;color:#94a3b8;"></i>
                        ${mensaje}
                    </td>
                </tr>
            `);
        }

        function inicializarClienteSelect() {
            $('#edit-id-cliente').select2({
                dropdownParent: $('#editarPedidoModal'),
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
                $('#edit-cliente-meta').html(`
                    <strong>Ruta:</strong> ${cliente.ruta || 'Sin ruta'}
                    <span class="mx-2">|</span>
                    <strong>Direccion:</strong> ${cliente.direccion || 'Sin direccion'}
                `);
            });
        }

        function agregarFilaPedido(item = null) {
            filaPedidoIndice += 1;
            const rowKey = `pedido-row-${filaPedidoIndice}`;
            $('#tablaEditarPedido tbody .edit-order-empty').remove();
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

            $('#tablaEditarPedido tbody').append(rowHtml);
            const $row = $(`#tablaEditarPedido tbody tr[data-row-key="${rowKey}"]`);

            inicializarSelectProductoFila($row, item);
            poblarFormasVentaFila($row, item?.formas_venta || [], item?.forma_venta_id || null, item?.precio_unitario || null);

            $row.on('change', '.row-forma-venta-select', function () {
                actualizarPrecioDeFila($row);
                recalcularSubtotalFila($row);
            });

            $row.on('input', '.row-cantidad-input', function () {
                recalcularSubtotalFila($row);
            });

            $row.on('click', '.row-remove-btn', function () {
                $row.remove();
                if (!$('#tablaEditarPedido tbody tr').length) {
                    renderEstadoVacioPedido('Agrega productos para comenzar a editar el pedido.');
                }
                recalcularTotalPedido();
            });

            recalcularSubtotalFila($row);

            const tableShell = document.querySelector('.edit-order-table-shell');
            if (tableShell) {
                requestAnimationFrame(() => {
                    tableShell.scrollTop = tableShell.scrollHeight;
                });
            }
        }

        function inicializarSelectProductoFila($row, item = null) {
            const $productoSelect = $row.find('.row-producto-select');

            $productoSelect.select2({
                dropdownParent: $('#editarPedidoModal'),
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
                actualizarMetaProductoFila($row, producto);
                cargarFormasVentaProducto($row, producto.id);
            });

            if (item) {
                const selectedOption = new Option(item.producto_texto, item.producto_id, true, true);
                $productoSelect.append(selectedOption).trigger('change');
                actualizarMetaProductoFila($row, {
                    stock_actual: item.stock_actual,
                    detalle_cantidad: item.detalle_cantidad,
                    codigo: item.producto_codigo
                });
            }
        }

        function actualizarMetaProductoFila($row, producto) {
            const stockTexto = `${Number(producto.stock_actual || 0)} ${producto.detalle_cantidad || ''}`.trim();
            $row.find('.row-stock-label').text(stockTexto || 'Sin stock');
            $row.data('producto-codigo', producto.codigo || '');
            $row.data('detalle-cantidad', producto.detalle_cantidad || '');
            $row.data('stock-actual', Number(producto.stock_actual || 0));
        }

        function cargarFormasVentaProducto($row, productoId) {
            $.ajax({
                url: "{{ route('administrador.productos.mostrarFormasVenta', ':id') }}".replace(':id', productoId),
                type: 'GET',
                success: function (formasVenta) {
                    poblarFormasVentaFila($row, formasVenta, null, null);
                    recalcularSubtotalFila($row);
                },
                error: function () {
                    poblarFormasVentaFila($row, [], null, null);
                    recalcularSubtotalFila($row);
                }
            });
        }

        function poblarFormasVentaFila($row, formasVenta, selectedId = null, precioActual = null) {
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
                actualizarPrecioDeFila($row);
            }
        }

        function actualizarPrecioDeFila($row) {
            const selectedOption = $row.find('.row-forma-venta-select option:selected');
            const precio = Number(selectedOption.data('precio') || 0);
            $row.find('.row-precio-label').text(`Bs ${precio.toFixed(2)}`);
        }

        function recalcularSubtotalFila($row) {
            const cantidad = Number($row.find('.row-cantidad-input').val() || 0);
            const precioTexto = $row.find('.row-precio-label').text().replace('Bs', '').trim();
            const precio = Number(precioTexto || 0);
            const subtotal = cantidad * precio;
            $row.find('.row-subtotal-label').text(`Bs ${subtotal.toFixed(2)}`);
            recalcularTotalPedido();
        }

        function recalcularTotalPedido() {
            let total = 0;

            $('#tablaEditarPedido tbody tr').each(function () {
                const subtotalTexto = $(this).find('.row-subtotal-label').text().replace('Bs', '').trim();
                total += Number(subtotalTexto || 0);
            });

            $('#edit-total-pedido').text(`Bs ${total.toFixed(2)}`);
            $('#edit-total-pedido-footer').text(`Bs ${total.toFixed(2)}`);
        }

        function construirPayloadPedido() {
            const items = [];
            let filaInvalida = false;

            $('#tablaEditarPedido tbody tr').each(function () {
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
                id_cliente: $('#edit-id-cliente').val(),
                id_usuario: $('#edit-id-usuario').val(),
                fecha_pedido: $('#edit-fecha-pedido').val(),
                items: items
            };
        }

        function guardarEdicionPedido() {
            const payload = construirPayloadPedido();

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
                url: "{{ route('administrador.pedidos.administrador.actualizarCompleto', ':pedido') }}".replace(':pedido', pedidoEditando),
                type: 'POST',
                data: payload,
                beforeSend: function () {
                    $('#btnGuardarPedidoModal').prop('disabled', true);
                    Swal.fire({
                        title: 'Guardando cambios',
                        html: 'Aplicando ajustes al pedido y al stock...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    Swal.fire('Listo', response.message, 'success');
                    $('#editarPedidoModal').modal('hide');
                    tablaPedidos.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const response = xhr.responseJSON || {};
                    let mensaje = response.message || 'No se pudo actualizar el pedido.';

                    if (response.errors) {
                        const primerCampo = Object.keys(response.errors)[0];
                        if (primerCampo && response.errors[primerCampo]?.length) {
                            mensaje = response.errors[primerCampo][0];
                        }
                    }

                    Swal.fire('Error', mensaje, 'error');
                },
                complete: function () {
                    $('#btnGuardarPedidoModal').prop('disabled', false);
                }
            });
        }

        function ejecutarEliminacionPedido() {
            if (!pedidoAEliminar) {
                $('#eliminarPedidoModal').modal('hide');
                return;
            }

            $.ajax({
                url: "{{ route('administrador.pedidos.administrador.eliminarCompleto', ':pedido') }}".replace(':pedido', pedidoAEliminar),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                beforeSend: function () {
                    $('#btnConfirmarEliminarPedido').prop('disabled', true);
                    Swal.fire({
                        title: 'Eliminando pedido',
                        html: 'Devolviendo productos al inventario...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    $('#eliminarPedidoModal').modal('hide');
                    Swal.fire('Listo', response.message, 'success');
                    tablaPedidos.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const mensaje = xhr.responseJSON?.message || 'No se pudo eliminar el pedido.';
                    Swal.fire('Error', mensaje, 'error');
                },
                complete: function () {
                    $('#btnConfirmarEliminarPedido').prop('disabled', false);
                    pedidoAEliminar = null;
                }
            });
        }
    </script>
@stop
