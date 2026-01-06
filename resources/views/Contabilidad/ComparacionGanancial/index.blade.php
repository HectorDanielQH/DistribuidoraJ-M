@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container-fluid py-4"
         style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center px-3">
            <h1 class="text-white mb-2"
                style="font-weight: 700; letter-spacing: 1px; font-size: clamp(1.5rem, 4vw, 2.75rem);">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-weight: 500; font-size: clamp(1rem, 2.5vw, 1.4rem);">
                Panel de comparación ganancial
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1">
            <h2 class="text-dark mb-0" style="font-weight: 600; font-size: clamp(1.25rem, 3vw, 1.75rem);">
                Comparación Ganancial <i class="fas fa-balance-scale-left ms-2"></i>
            </h2>
        </div>
        <p class="text-muted px-1" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);">
            En este módulo, podrá comparar las ganancias mensuales.
        </p>
    </div>

    <div>
        <div class="container d-flex justify-content-center align-items-center mb-5">
            <div class="card w-100" style="max-width: 600px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                <div class="card-body p-4">
                    <label for="mes">Seleccione el Mes a Consultar: <span class="text-danger">*</span></label>
                    <input type="month" name="mes" id="mes" class="form-control mb-3" style="height: 45px; font-size: 1rem; border-radius: 8px; border: 1px solid #ced4da; padding: 10px;">
                    <div class="d-flex justify-content-center">
                        <button id="btnBuscarComparacionGanancial" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-size: 1rem;">
                            <i class="fas fa-search me-2"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--TABLA DE PRODUCTOS-->
    <div class="container-fluid mb-5">
        <table class="table table-bordered table-hover table-striped" id="tabla-comparacion-ganancial">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen Prod.</th>
                    <th>Nombre Prod.</th>
                    <th>Cantidad Ventas</th>
                    <th>Precio de Compra</th>
                    <th>Costo Total Mes Actual</th>
                    <th>Ventas Mes Actual</th>
                    <th>Ganancia Mes Actual</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <th colspan="3" style="text-align:right">Totales:</th>
                    <th id="total-cantidad"></th>
                    <th></th> <th id="total-costo"></th>
                    <th id="total-ventas"></th>
                    <th id="total-ganancia"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-CaLdjDnDQsm4dp6FAi+hDGbnmYMabedJHm00x/JJgmTsQ495TW5sVn4B7kcyThok" crossorigin="anonymous">

    <style>
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }
        .card { transition: all 0.3s ease; }
        .card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); }
        .btn:hover { opacity: 0.9; }

        /* Select2 full width */
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single {
            height: 38px; padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        /* Ajustes responsive extra */
        @media (max-width: 576px) {
            /* Espaciado y stacking más cómodo en móviles */
            #btnBuscarVentasPorDia { width: 100%; }
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.js" integrity="sha384-SY2UJyI2VomTkRZaMzHTGWoCHGjNh2V7w+d6ebcRmybnemfWfy9nffyAuIG4GJvd" crossorigin="anonymous"></script>

    <script>

        $(document).ready(function() {
            let tabla = $('#tabla-comparacion-ganancial').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 100,
                lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
                columns: [
                    { data: 'codigo_producto' },
                    { data: 'imagen_producto' },
                    { data: 'nombre_producto' },
                    { data: 'cantidad_ventas' },
                    { data: 'precio_compra' },
                    { data: 'costo_total_mes_actual' },
                    { data: 'ventas_mes_actual' },
                    { data: 'ganancia_mes_actual' }
                ],
                // --- DISEÑO ESTÉTICO ---
                dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>',
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columnas',
                        className: 'btn btn-info btn-sm shadow-sm',
                    },
                    {
                        extend: 'pageLength',
                        className: 'btn btn-secondary',
                        text: '<i class="fas fa-list-ol"></i> Mostrar filas',
                        titleAttr: 'Mostrar filas'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm shadow-sm',
                        exportOptions: { columns: [0, 2, 3, 4, 5, 6, 7] }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm shadow-sm',
                        orientation: 'landscape',
                        exportOptions: { columns: [0, 2, 3, 4, 5, 6, 7] }
                    }
                ],
                // --- CÁLCULO DE TOTALES ---
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Función para limpiar el formato moneda y sumar
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[Bs\.\,\-\s]/g, '') * 1 :
                            typeof i === 'number' ? i : 0;
                    };

                    // Columnas a sumar: 3(Cant), 5(Costo), 6(Ventas), 7(Ganancia)
                    [3, 5, 6, 7].forEach(function (colIndex) {
                        let total = api.column(colIndex, { page: 'current' }).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                        // Aplicar formato según la columna
                        let format = (colIndex === 3) ? total : 'Bs.- ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
                        $(api.column(colIndex).footer()).html(format);
                    });
                },
                language: { url: '/i18n/es-ES.json' }
            });
        });


        $('#btnBuscarComparacionGanancial').on('click', function() {
            let mesSeleccionado = $('#mes').val(); // Ejemplo: "2023-10"

            if (!mesSeleccionado) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione un mes.' });
                return;
            }

            // 1. Descomponer año y mes
            let partes = mesSeleccionado.split('-');
            let anio = partes[0];
            let mes = partes[1];

            // 2. Construir la URL reemplazando los parámetros de tu ruta
            // Usamos una URL base limpia generada por Blade
            let urlBase = "{{ route('contabilidad.ventas.comparacionGanancial.filtro', ['mes' => ':MES', 'anio' => ':ANIO']) }}";
            let urlNueva = urlBase.replace(':MES', mes).replace(':ANIO', anio);

            // 3. Actualizar la URL de DataTables y recargar
            let tabla = $('#tabla-comparacion-ganancial').DataTable();
            tabla.ajax.url(urlNueva).load();

            // Notificación de éxito
            Swal.fire({
                icon: 'success',
                title: 'Búsqueda realizada',
                text: 'Mes: ' + mes + ' Año: ' + anio,
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>
@stop
