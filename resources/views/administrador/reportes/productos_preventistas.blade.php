@extends('adminlte::page')

@section('title', 'Reportes')

@section('content_header')
    <div class="report-shell-header">
        <div>
            <span>Panel administrador</span>
            <h1>Reportes</h1>
            <p>Ventas contabilizadas y despachos pendientes por preventista, sin modificar registros.</p>
        </div>
    </div>
@stop

@section('content')
    <section class="report-card">
        <div class="report-filters">
            <label>
                Fecha inicio
                <input type="date" id="fecha_inicio" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </label>
            <label>
                Fecha fin
                <input type="date" id="fecha_fin" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </label>
            <label>
                Preventista
                <select id="vendedor_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}">{{ trim($preventista->nombres.' '.$preventista->apellido_paterno.' '.$preventista->apellido_materno) }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="btn btn-success report-btn" id="btn-buscar-reporte">
                <i class="fas fa-search"></i> Consultar
            </button>
        </div>

        <div class="report-summary" id="report-summary">
            <article>
                <span>Filas</span>
                <strong id="summary-filas">0</strong>
            </article>
            <article>
                <span>Vendida</span>
                <strong id="summary-vendida">0</strong>
            </article>
            <article>
                <span>Despachada</span>
                <strong id="summary-despachada">0</strong>
            </article>
            <article>
                <span>Total</span>
                <strong id="summary-total">0</strong>
            </article>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Preventista</th>
                        <th>Cantidad vendida</th>
                        <th>Cantidad despachada</th>
                        <th>Total</th>
                        <th>Limite</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-reporte-body">
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aun no hay resultados.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@stop

@section('css')
    <style>
        .content-wrapper { background: #eef3f1; }
        .report-shell-header, .report-card {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }
        .report-shell-header {
            padding: 18px;
        }
        .report-shell-header span {
            color: #0f766e;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .report-shell-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.7rem;
            font-weight: 900;
        }
        .report-shell-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }
        .report-card {
            padding: 16px;
        }
        .report-filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
            margin-bottom: 16px;
        }
        .report-filters label {
            margin: 0;
            color: #475569;
            font-weight: 900;
        }
        .report-btn {
            border-radius: 8px;
            font-weight: 900;
            min-height: 42px;
        }
        .report-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .report-summary article {
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px;
            background: #fbfdfc;
        }
        .report-summary span {
            color: #64748b;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .report-summary strong {
            display: block;
            color: #17211d;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .limit-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 900;
        }
        .limit-ok {
            background: #e7f6ec;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .limit-near {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .limit-over {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .limit-none {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        @media (max-width: 991.98px) {
            .report-filters,
            .report-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function claseLimite(estado) {
            if (estado === 'superado') return 'limit-pill limit-over';
            if (estado === 'cerca') return 'limit-pill limit-near';
            if (estado === 'ok') return 'limit-pill limit-ok';
            return 'limit-pill limit-none';
        }

        function textoLimite(fila) {
            if (fila.estado_limite === 'superado') {
                return `Supero el limite (${fila.porcentaje_usado}%)`;
            }
            if (fila.estado_limite === 'cerca') {
                return `Cerca del limite (${fila.porcentaje_usado}%)`;
            }
            if (fila.estado_limite === 'ok') {
                return `Dentro del limite (${fila.porcentaje_usado}%)`;
            }
            return 'Sin limite asignado';
        }

        function pintarTabla(filas) {
            const $tbody = $('#tabla-reporte-body');
            $tbody.empty();

            if (!filas.length) {
                $tbody.append('<tr><td colspan="7" class="text-center text-muted">No hay datos en el rango seleccionado.</td></tr>');
                return;
            }

            filas.forEach(function (fila) {
                const unidad = fila.detalle_cantidad || '';
                const limite = fila.limite === null ? 'Sin limite' : `${fila.limite} ${unidad}`.trim();
                const total = Number(fila.cantidad_consumida || 0).toFixed(2);

                $tbody.append(`
                    <tr>
                        <td>${fila.producto}</td>
                        <td>${fila.vendedor}</td>
                        <td>${Number(fila.cantidad_vendida || 0).toFixed(2)} ${unidad}</td>
                        <td>${Number(fila.cantidad_despachada || 0).toFixed(2)} ${unidad}</td>
                        <td><strong>${total} ${unidad}</strong></td>
                        <td>${limite}</td>
                        <td><span class="${claseLimite(fila.estado_limite)}">${textoLimite(fila)}</span></td>
                    </tr>
                `);
            });
        }

        function pintarResumen(filas) {
            const vendida = filas.reduce((acc, fila) => acc + Number(fila.cantidad_vendida || 0), 0);
            const despachada = filas.reduce((acc, fila) => acc + Number(fila.cantidad_despachada || 0), 0);
            const total = filas.reduce((acc, fila) => acc + Number(fila.cantidad_consumida || 0), 0);

            $('#summary-filas').text(filas.length);
            $('#summary-vendida').text(vendida.toFixed(2));
            $('#summary-despachada').text(despachada.toFixed(2));
            $('#summary-total').text(total.toFixed(2));
        }

        function consultarReporte() {
            $.ajax({
                url: "{{ route('api.admin.reportes.productosPreventistas') }}",
                type: 'GET',
                data: {
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    vendedor_id: $('#vendedor_id').val()
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Consultando reporte',
                        text: 'Preparando datos de ventas y despachos',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success: function (response) {
                    Swal.close();
                    const filas = response.data || [];
                    pintarTabla(filas);
                    pintarResumen(filas);
                },
                error: function (xhr) {
                    Swal.close();
                    pintarTabla([]);
                    pintarResumen([]);
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo consultar el reporte.', 'error');
                }
            });
        }

        $(function () {
            $('#btn-buscar-reporte').on('click', consultarReporte);
            consultarReporte();
        });
    </script>
@stop
