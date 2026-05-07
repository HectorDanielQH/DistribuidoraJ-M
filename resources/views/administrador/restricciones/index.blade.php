@extends('adminlte::page')

@section('title', 'Restricciones')

@section('content_header')
    <div class="restriction-header">
        <div>
            <span>Panel administrador</span>
            <h1>Restricciones</h1>
            <p>Limites de venta por producto y preventista, con control de uso actual.</p>
        </div>
    </div>
@stop

@section('content')
    <section class="restriction-card">
        <form id="form-restriccion" class="restriction-form">
            <input type="hidden" id="restriccion_id">
            <input type="hidden" id="limite_convertido">
            <label>
                Producto
                <select id="producto_id" class="form-control" required>
                    <option value="">Selecciona un producto</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ trim($producto->codigo.' - '.$producto->nombre_producto) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Vendedor
                <select id="vendedor_id" class="form-control" required>
                    <option value="">Selecciona un vendedor</option>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Registrar limite segun
                <select id="base_limite" class="form-control" required disabled>
                    <option value="">Primero selecciona un producto</option>
                </select>
            </label>
            <label>
                Limite maximo
                <input type="number" id="limite" class="form-control" min="1" required>
                <small class="restriction-help" id="limite-ayuda">Selecciona un producto para ver equivalencias.</small>
            </label>
            <div class="restriction-actions">
                <button type="submit" class="btn btn-success restriction-btn" id="btn-guardar-restriccion">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" class="btn btn-outline-secondary restriction-btn" id="btn-limpiar-restriccion">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </form>

        <section class="restriction-context d-none" id="restriction-context">
            <div class="restriction-context-title">
                <strong id="context-producto">Producto</strong>
                <span id="context-unidad">Unidad base</span>
            </div>
            <p id="context-explicacion" class="mb-2"></p>
            <div id="context-formas" class="restriction-context-grid"></div>
        </section>

        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Vendedor</th>
                        <th>Stock actual</th>
                        <th>Limite configurado</th>
                        <th>Ya asignado</th>
                        <th>Disponible para nuevas asignaciones</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-restricciones-body">
                    <tr>
                        <td colspan="8" class="text-center text-muted">Aun no hay restricciones registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .content-wrapper { background: #eef3f1; }
        .restriction-header, .restriction-card {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .restriction-header {
            padding: 18px;
        }
        .restriction-header span {
            color: #0f766e;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .restriction-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .restriction-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .restriction-card {
            padding: 16px;
        }
        .restriction-form {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
            margin-bottom: 16px;
        }
        .restriction-form label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .restriction-help {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: .82rem;
            font-weight: 700;
        }
        .restriction-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .restriction-btn {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }
        .restriction-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 900;
        }
        .restriction-ok {
            background: #e7f6ec;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .restriction-near {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .restriction-over {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .restriction-context {
            margin-bottom: 16px;
            border: 1px solid #d7e4df;
            border-radius: 8px;
            background: #fbfdfc;
            padding: 14px;
        }
        .restriction-context-title {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 8px;
        }
        .restriction-context-title strong {
            color: #17211d;
            font-size: 1rem;
        }
        .restriction-context-title span {
            color: #0f766e;
            font-size: .84rem;
            font-weight: 900;
        }
        .restriction-context p {
            color: #475569;
            font-weight: 700;
        }
        .restriction-context-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .restriction-context-item {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            background: #ffffff;
            padding: 12px;
        }
        .restriction-context-item strong {
            display: block;
            color: #17211d;
        }
        .restriction-context-item span {
            color: #64748b;
            font-size: .85rem;
            font-weight: 700;
        }
        .restriction-stock-box {
            margin-top: 10px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            padding: 10px 12px;
            font-weight: 800;
        }
        .select2-container .select2-selection--single {
            height: 38px;
            padding-top: 4px;
        }
        @media (max-width: 991.98px) {
            .restriction-form {
                grid-template-columns: 1fr;
            }
            .restriction-context-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let contextoProductoActual = null;

        function estadoClase(estado) {
            if (estado === 'sin_stock') return 'restriction-pill restriction-over';
            if (estado === 'superado') return 'restriction-pill restriction-over';
            if (estado === 'cerca') return 'restriction-pill restriction-near';
            if (estado === 'ajustado_stock') return 'restriction-pill restriction-near';
            return 'restriction-pill restriction-ok';
        }

        function estadoTexto(item) {
            if (item.estado_limite === 'sin_stock') return 'Sin stock actual';
            if (item.estado_limite === 'superado') return 'Supero el limite global';
            if (item.estado_limite === 'cerca') return 'Cerca del limite global';
            if (item.estado_limite === 'ajustado_stock') return 'Limitado por stock actual';
            return 'Listo para usar';
        }

        function limpiarFormulario() {
            $('#restriccion_id').val('');
            $('#producto_id').val('').trigger('change');
            $('#vendedor_id').val('');
            $('#limite').val('');
            $('#limite_convertido').val('');
            $('#base_limite').html('<option value="">Primero selecciona un producto</option>').prop('disabled', true);
            $('#limite-ayuda').text('Selecciona un producto para ver equivalencias.');
            $('#restriction-context').addClass('d-none');
            $('#btn-guardar-restriccion').html('<i class="fas fa-save"></i> Guardar');
            contextoProductoActual = null;
        }

        function calcularLimiteConvertido() {
            const cantidad = Number($('#limite').val() || 0);
            const equivalencia = Number($('#base_limite').find(':selected').data('equivalencia') || 0);

            if (!cantidad || !equivalencia) {
                $('#limite_convertido').val('');
                return 0;
            }

            const convertido = cantidad * equivalencia;
            const unidad = contextoProductoActual?.producto?.detalle_cantidad || 'unidades';
            const stockActual = Number(contextoProductoActual?.producto?.cantidad || 0);

            if (stockActual > 0 && convertido > stockActual) {
                $('#limite_convertido').val(stockActual);
                const cantidadAjustada = Math.floor(stockActual / Math.max(equivalencia, 1));
                if (cantidadAjustada >= 0 && cantidadAjustada !== cantidad) {
                    $('#limite').val(cantidadAjustada || '');
                }
                $('#limite-ayuda').text(`El limite no puede superar el stock actual. Se ajusto a ${stockActual.toFixed(2)} ${unidad}.`);
                return stockActual;
            }

            $('#limite_convertido').val(convertido);
            const baseTexto = $('#base_limite option:selected').text() || 'unidad base';
            const restante = Math.max(stockActual - convertido, 0);
            $('#limite-ayuda').text(`Se guardaran ${convertido.toFixed(2)} ${unidad}. Base elegida: ${baseTexto}. Stock actual: ${stockActual} ${unidad}. Restaria: ${restante.toFixed(2)} ${unidad}.`);
            return convertido;
        }

        function renderContextoProducto(data) {
            contextoProductoActual = data;
            const unidad = data.producto.detalle_cantidad || 'unidades';
            const opciones = [
                `<option value="base" data-equivalencia="1">Unidad base de inventario (${unidad})</option>`
            ];

            (data.formas_venta || []).forEach(function (forma) {
                opciones.push(
                    `<option value="${forma.id}" data-equivalencia="${forma.equivalencia_cantidad}">${forma.tipo_venta} (${forma.equivalencia_cantidad} ${unidad})</option>`
                );
            });

            $('#base_limite').html(opciones.join('')).prop('disabled', false);
            $('#limite-ayuda').text(`El limite se guardara en ${unidad}. Puedes escribirlo segun una forma de venta y el sistema lo convierte.`);
            $('#context-producto').text(`${data.producto.codigo} - ${data.producto.nombre_producto}`);
            $('#context-unidad').text(`Unidad base: ${unidad}`);
            $('#context-explicacion').text(`Referencia para definir el limite sin adivinar equivalencias. El valor final se guarda en ${unidad} y no puede superar el stock actual.`);

            const tarjetas = [
                `<div class="restriction-context-item"><strong>Unidad base</strong><span>1 ${unidad}</span></div>`,
                `<div class="restriction-context-item"><strong>Stock actual</strong><span>${Number(data.producto.cantidad || 0).toFixed(2)} ${unidad}</span></div>`
            ];

            (data.formas_venta || []).forEach(function (forma) {
                tarjetas.push(`
                    <div class="restriction-context-item">
                        <strong>${forma.tipo_venta}</strong>
                        <span>1 ${forma.tipo_venta} = ${forma.equivalencia_cantidad} ${unidad}</span>
                    </div>
                `);
            });

            $('#context-formas').html(tarjetas.join(''));
            $('#restriction-context').removeClass('d-none');
            calcularLimiteConvertido();
        }

        function cargarContextoProducto(productoId, limiteConvertido = null) {
            if (!productoId) {
                contextoProductoActual = null;
                $('#base_limite').html('<option value="">Primero selecciona un producto</option>').prop('disabled', true);
                $('#restriction-context').addClass('d-none');
                $('#limite-ayuda').text('Selecciona un producto para ver equivalencias.');
                return;
            }

            $.get("{{ route('api.admin.restricciones.producto', ':id') }}".replace(':id', productoId), function (response) {
                renderContextoProducto(response);

                if (limiteConvertido !== null) {
                    const baseCoincidente = (response.formas_venta || []).find(function (forma) {
                        return Number(limiteConvertido) % Number(forma.equivalencia_cantidad) === 0;
                    });

                    if (baseCoincidente) {
                        $('#base_limite').val(String(baseCoincidente.id));
                        $('#limite').val(Number(limiteConvertido) / Number(baseCoincidente.equivalencia_cantidad));
                    } else {
                        $('#base_limite').val('base');
                        $('#limite').val(limiteConvertido);
                    }

                    calcularLimiteConvertido();
                }
            });
        }

        function cargarRestricciones() {
            $.get("{{ route('api.admin.restricciones.index') }}", function (response) {
                const filas = response.data || [];
                const $tbody = $('#tabla-restricciones-body');
                $tbody.empty();

                if (!filas.length) {
                    $tbody.append('<tr><td colspan="8" class="text-center text-muted">Aun no hay restricciones registradas.</td></tr>');
                    return;
                }

                filas.forEach(function (item) {
                    const unidad = item.detalle_cantidad || '';
                    $tbody.append(`
                        <tr>
                            <td>${item.producto}</td>
                            <td>${item.vendedor}</td>
                            <td>${Number(item.stock_actual || 0).toFixed(2)} ${unidad}</td>
                            <td>${Number(item.limite).toFixed(2)} ${unidad}</td>
                            <td>${Number(item.cantidad_asignada_global || 0).toFixed(2)} ${unidad}</td>
                            <td>${Number(item.cantidad_disponible_real || 0).toFixed(2)} ${unidad}</td>
                            <td><span class="${estadoClase(item.estado_limite)}">${estadoTexto(item)}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning mr-1" onclick="editarRestriccion(${item.id}, ${item.producto_id}, ${item.vendedor_id}, ${Number(item.limite)})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarRestriccion(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            });
        }

        function editarRestriccion(id, productoId, vendedorId, limite) {
            $('#restriccion_id').val(id);
            $('#producto_id').val(productoId).trigger('change');
            $('#vendedor_id').val(vendedorId);
            $('#limite_convertido').val(limite);
            $('#btn-guardar-restriccion').html('<i class="fas fa-save"></i> Actualizar');
            cargarContextoProducto(productoId, limite);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function eliminarRestriccion(id) {
            Swal.fire({
                title: 'Eliminar restriccion',
                text: 'Esta accion no modifica ventas ni pedidos, solo el limite configurado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('api.admin.restricciones.destroy', ':id') }}".replace(':id', id),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        Swal.fire('Eliminado', response.message, 'success');
                        limpiarFormulario();
                        cargarRestricciones();
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar.', 'error');
                    }
                });
            });
        }

        $(function () {
            $('#producto_id').select2({
                width: '100%',
                placeholder: 'Selecciona un producto'
            });

            cargarRestricciones();

            $('#btn-limpiar-restriccion').on('click', limpiarFormulario);
            $('#producto_id').on('change', function () {
                $('#limite').val('');
                $('#limite_convertido').val('');
                cargarContextoProducto($(this).val());
            });
            $('#base_limite, #limite').on('change keyup input', calcularLimiteConvertido);

            $('#form-restriccion').on('submit', function (event) {
                event.preventDefault();

                const id = $('#restriccion_id').val();
                const url = id
                    ? "{{ route('api.admin.restricciones.update', ':id') }}".replace(':id', id)
                    : "{{ route('api.admin.restricciones.store') }}";
                const type = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: {
                        _token: '{{ csrf_token() }}',
                        producto_id: $('#producto_id').val(),
                        vendedor_id: $('#vendedor_id').val(),
                        limite: $('#limite_convertido').val()
                    },
                    success: function (response) {
                        Swal.fire('Guardado', response.message, 'success');
                        limpiarFormulario();
                        cargarRestricciones();
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo guardar la restriccion.', 'error');
                    }
                });
            });
        });
    </script>
@stop
