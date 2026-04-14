@extends('adminlte::page')

@section('title', 'Detalle de lote')

@section('content_header')
    <div class="lot-header">
        <div>
            <span>Inventario / ingresos</span>
            <h1>Detalle del lote {{ $lotes->codigo_lote ?? 'N/A' }}</h1>
            <p>Controla cada producto ingresado, su costo historico y el impacto en stock.</p>
        </div>
        <button class="btn btn-success lot-main-btn" data-toggle="modal" data-target="#agregar-lote" id="agregar-nuevo-lote">
            <i class="fas fa-plus"></i> Agregar producto
        </button>
    </div>
@stop

@section('content')
    <x-adminlte-modal id="agregar-lote" size="lg" theme="dark" icon="fas fa-plus-circle" title="Agregar producto al lote">
        <div class="modal-body px-4">
            <form id="registro-lote">
                @csrf
                <div class="lot-form-help">
                    <strong>Ingreso de mercaderia</strong>
                    <span>Al guardar se suma al inventario y queda registrado el precio de ingreso.</span>
                </div>

                <label class="lot-field">
                    Producto
                    <select id="producto_id" name="producto_id" style="width: 100%"></select>
                </label>

                <div class="lot-form-grid">
                    <label class="lot-field">
                        Cantidad que ingresa
                        <input name="cantidadProducto" id="cantidadProducto" type="number" placeholder="Ej: 12" min="1" value="1" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Unidad
                        <input name="descripcionCantidad" id="descripcionCantidad" type="text" placeholder="Ej: UNIDADES, CAJAS" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Precio de ingreso
                        <input name="precioCompra" id="precioCompra" type="number" placeholder="Ej: 25.40" min="0.01" value="0.01" step="0.01" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Detalle del precio
                        <input name="descripcionCompra" id="descripcionCompra" type="text" placeholder="Ej: caja x 12" class="form-control" required>
                    </label>
                </div>

                <div class="lot-form-grid">
                    <label class="lot-field">
                        Fecha de vencimiento
                        <input name="vencimientoProducto" id="vencimientoProducto" type="date" class="form-control" disabled>
                    </label>
                    <label class="lot-field">
                        Observacion
                        <input name="observacion" id="observacionLote" type="text" placeholder="Ej: factura, proveedor, motivo" class="form-control">
                    </label>
                </div>

                <label class="lot-check">
                    <input type="checkbox" id="habilitarVencimiento">
                    <span>Este producto tiene vencimiento</span>
                </label>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="lot-modal-footer">
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="lot-modal-btn" />
                <x-adminlte-button type="button" id="botonenviarlote" theme="success" icon="fas fa-check" label="Registrar ingreso" class="lot-modal-btn" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="editar-lote" size="lg" theme="dark" icon="fas fa-edit" title="Editar ingreso del lote">
        <div class="modal-body px-4">
            <form id="editar-lote-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="editar_lote_id">
                <input type="hidden" id="editar_producto_id" name="producto_id">

                <div class="lot-form-help lot-warning">
                    <strong>Cuidado con el stock</strong>
                    <span>Si cambias la cantidad, el sistema suma o resta solo la diferencia.</span>
                </div>

                <label class="lot-field">
                    Producto
                    <input type="text" id="editar_producto_nombre" class="form-control" readonly>
                </label>

                <div class="lot-form-grid">
                    <label class="lot-field">
                        Cantidad ingresada
                        <input name="cantidad_producto" id="editar_cantidad_producto" type="number" min="1" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Unidad
                        <input name="descripcion_cantidad" id="editar_descripcion_cantidad" type="text" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Precio de ingreso
                        <input name="precio_compra" id="editar_precio_compra" type="number" min="0.01" step="0.01" class="form-control" required>
                    </label>
                    <label class="lot-field">
                        Detalle del precio
                        <input name="descripcion_precio_compra" id="editar_descripcion_precio_compra" type="text" class="form-control" required>
                    </label>
                </div>

                <div class="lot-form-grid">
                    <label class="lot-field">
                        Fecha de vencimiento
                        <input name="vencimiento_producto" id="editar_vencimiento_producto" type="date" class="form-control">
                    </label>
                    <label class="lot-field">
                        Observacion
                        <input name="observacion" id="editar_observacion" type="text" class="form-control">
                    </label>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="lot-modal-footer">
                <x-adminlte-button theme="danger" id="editar-lote-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="lot-modal-btn" />
                <x-adminlte-button type="button" id="editar-lote-guardar" theme="success" icon="fas fa-save" label="Guardar cambios" class="lot-modal-btn" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <div class="lot-summary">
        <article>
            <span>Lote</span>
            <strong>{{ $lotes->codigo_lote ?? 'N/A' }}</strong>
        </article>
        <article>
            <span>Ultimo ingreso</span>
            <strong>{{ optional($lotes->ingreso_lote)->format('d/m/Y') ?? 'N/A' }}</strong>
        </article>
        <article>
            <span>Control</span>
            <strong>Historico activo</strong>
        </article>
    </div>

    <div class="lot-table-shell">
        <table class="table table-bordered table-hover table-striped" id="tabla-lotes">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen</th>
                    <th>Descripcion</th>
                    <th>Cantidad</th>
                    <th>Precio ingreso</th>
                    <th>Vencimiento</th>
                    <th>Stock</th>
                    <th>Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="text-center">Cargando ingresos...</td>
                </tr>
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet">
    <style>
        .content-wrapper { background: #eef3f1; }
        .lot-header, .lot-summary, .lot-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .lot-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
        }
        .lot-header span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .lot-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.65rem;
            font-weight: 900;
        }
        .lot-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .lot-main-btn, .lot-modal-btn, .lot-action-btn {
            border-radius: 8px;
            font-weight: 900;
        }
        .lot-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .lot-summary article {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .lot-summary span, .lot-field, .lot-check {
            color: #475569;
            font-weight: 800;
        }
        .lot-summary strong {
            display: block;
            color: #111827;
            font-size: 1.1rem;
        }
        .lot-table-shell {
            padding: 14px;
            overflow-x: auto;
        }
        .lot-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .lot-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .lot-field {
            display: block;
            margin-bottom: 12px;
        }
        .lot-field input, .select2-container--default .select2-selection--single {
            border-radius: 8px;
            min-height: 40px;
        }
        .lot-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .lot-form-help {
            display: flex;
            flex-direction: column;
            gap: 2px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
        }
        .lot-warning {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
        }
        .lot-modal-footer {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
        }
        .lot-product-image {
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d7e4df;
        }
        .lot-stock-move {
            white-space: nowrap;
            font-weight: 900;
            color: #0f766e;
        }
        @media (max-width: 767.98px) {
            .lot-header, .lot-modal-footer { flex-direction: column; }
            .lot-main-btn, .lot-modal-btn { width: 100%; }
            .lot-summary, .lot-form-grid { grid-template-columns: 1fr; }
            .lot-actions { flex-direction: column; }
            .lot-action-btn { width: 100%; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js"></script>

    <script>
        const codigoLote = '{{ $lotes->codigo_lote }}';

        $(document).ready(function() {
            $('#tabla-lotes').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                language: { url: '/i18n/es-ES.json' },
                pageLength: 10,
                ajax: {
                    url: "{{ route('administrador.lote.productos.obtenerLotesProducto', ':id') }}".replace(':id', codigoLote),
                    type: 'GET',
                },
                columns: [
                    { data: 'codigo_producto', name: 'codigo_producto' },
                    { data: 'imagen', name: 'imagen', orderable: false, searchable: false },
                    { data: 'descripcion', name: 'descripcion' },
                    { data: 'cantidad_anadida', name: 'cantidad_anadida' },
                    { data: 'nuevo_precio', name: 'nuevo_precio' },
                    { data: 'fecha_vencimiento', name: 'fecha_vencimiento' },
                    { data: 'stock_movimiento', name: 'stock_movimiento', orderable: false, searchable: false },
                    { data: 'ingreso_lote', name: 'ingreso_lote' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                ],
            });

            $('#producto_id').select2({
                placeholder: 'Buscar producto por nombre o codigo',
                dropdownParent: $('#agregar-lote'),
                ajax: {
                    url: '{{ route("administrador.lote.productos.buscarProducto") }}',
                    dataType: 'json',
                    type: 'GET',
                    delay: 250,
                    data: params => ({ query: params.term }),
                    processResults: data => ({
                        results: data.map(item => ({
                            id: item.id,
                            text: item.nombre_producto + ' (Codigo: ' + item.codigo + ')'
                        }))
                    })
                },
                minimumInputLength: 1,
            });
        });

        $('#producto_id').on('select2:select', function(e) {
            $.get('{{ route("administrador.lote.productos.detalleProducto", ":id") }}'.replace(':id', e.params.data.id), function(data) {
                $('#descripcionCantidad').val(data.detalle_cantidad);
                $('#precioCompra').val(data.precio_compra);
                $('#descripcionCompra').val(data.detalle_precio_compra);
            });
        });

        $('#habilitarVencimiento').change(function() {
            $('#vencimientoProducto').prop('disabled', !$(this).is(':checked'));
            if (!$(this).is(':checked')) {
                $('#vencimientoProducto').val('');
            }
        });

        $('#botonenviarlote').click(function() {
            guardarLote('{{ route("administrador.lotes.store") }}', 'POST', {
                codigo_lote: codigoLote,
                producto_id: $('#producto_id').val(),
                cantidad_producto: $('#cantidadProducto').val(),
                descripcion_cantidad: $('#descripcionCantidad').val(),
                precio_compra: $('#precioCompra').val(),
                descripcion_precio_compra: $('#descripcionCompra').val(),
                vencimiento_producto: $('#vencimientoProducto').val(),
                observacion: $('#observacionLote').val(),
            }, function() {
                $('#registro-lote')[0].reset();
                $('#producto_id').val(null).trigger('change');
                $('#botonenviar-cerrar').click();
            });
        });

        $('#editar-lote-guardar').click(function() {
            const id = $('#editar_lote_id').val();
            guardarLote('{{ route("administrador.lotes.update", ":id") }}'.replace(':id', id), 'POST', {
                _method: 'PUT',
                codigo_lote: codigoLote,
                producto_id: $('#editar_producto_id').val(),
                cantidad_producto: $('#editar_cantidad_producto').val(),
                descripcion_cantidad: $('#editar_descripcion_cantidad').val(),
                precio_compra: $('#editar_precio_compra').val(),
                descripcion_precio_compra: $('#editar_descripcion_precio_compra').val(),
                vencimiento_producto: $('#editar_vencimiento_producto').val(),
                observacion: $('#editar_observacion').val(),
            }, function() {
                $('#editar-lote-cerrar').click();
            });
        });

        function guardarLote(url, method, payload, onSuccess) {
            if (!payload.producto_id) {
                Swal.fire('Atencion', 'Selecciona un producto.', 'warning');
                return;
            }
            if (Number(payload.cantidad_producto) <= 0) {
                Swal.fire('Atencion', 'La cantidad debe ser mayor a cero.', 'warning');
                return;
            }
            if (Number(payload.precio_compra) <= 0) {
                Swal.fire('Atencion', 'El precio debe ser mayor a cero.', 'warning');
                return;
            }

            Swal.fire({ title: 'Sincronizando inventario...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: url,
                type: method,
                data: Object.assign({ _token: '{{ csrf_token() }}' }, payload),
                success: function(response) {
                    $('#tabla-lotes').DataTable().ajax.reload(null, false);
                    if (onSuccess) {
                        onSuccess();
                    }
                    Swal.fire({ icon: 'success', title: 'Listo', text: response.message, timer: 1800, showConfirmButton: false });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    const message = errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'No se pudo completar la operacion.');
                    Swal.fire('Error', message, 'error');
                }
            });
        }

        function verLote(e) {
            const id = e.getAttribute('id-lote');
            $.get('{{ route("administrador.lotes.show", ":id") }}'.replace(':id', id), function(data) {
                Swal.fire({
                    icon: 'info',
                    title: data.producto?.nombre_producto || 'Ingreso de lote',
                    html: `
                        <div class="text-left">
                            <p><strong>Codigo lote:</strong> ${data.lote.codigo_lote}</p>
                            <p><strong>Cantidad:</strong> ${data.lote.cantidad} ${data.lote.detalle_cantidad || ''}</p>
                            <p><strong>Precio:</strong> ${data.precio_formateado}</p>
                            <p><strong>Stock:</strong> ${data.lote.stock_antes ?? 'N/D'} -> ${data.lote.stock_despues ?? 'N/D'}</p>
                            <p><strong>Observacion:</strong> ${data.lote.observacion || 'Sin observacion'}</p>
                        </div>
                    `,
                });
            });
        }

        function editarLote(e) {
            const id = e.getAttribute('id-lote');
            $.get('{{ route("administrador.lotes.show", ":id") }}'.replace(':id', id), function(data) {
                const lote = data.lote;
                $('#editar_lote_id').val(lote.id);
                $('#editar_producto_id').val(lote.producto_id);
                $('#editar_producto_nombre').val(data.producto?.nombre_producto || 'Producto no encontrado');
                $('#editar_cantidad_producto').val(lote.cantidad);
                $('#editar_descripcion_cantidad').val(lote.detalle_cantidad);
                $('#editar_precio_compra').val(lote.precio_ingreso);
                $('#editar_descripcion_precio_compra').val(lote.detalle_precio_ingreso);
                $('#editar_vencimiento_producto').val(lote.fecha_vencimiento ? lote.fecha_vencimiento.substring(0, 10) : '');
                $('#editar_observacion').val(lote.observacion || '');
                $('#editar-lote').modal('show');
            });
        }

        function eliminarLote(e) {
            const id = e.getAttribute('id-lote');
            Swal.fire({
                title: 'Anular ingreso?',
                text: 'Se descontara del inventario, pero el historial quedara guardado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, anular',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({ title: 'Anulando ingreso...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ route('administrador.lote.productos.eliminarLote', ':id') }}".replace(':id', id),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        $('#tabla-lotes').DataTable().ajax.reload(null, false);
                        Swal.fire('Listo', response.message, 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo anular el ingreso.', 'error');
                    }
                });
            });
        }
    </script>
@stop
