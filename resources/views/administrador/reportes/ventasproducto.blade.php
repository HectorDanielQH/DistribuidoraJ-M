@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Rendimiento de Ventas por Producto
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container">
        <h3>
            <i class="fas fa-chart-bar me-2"></i> Selecciona el rendimiento de ventas por producto
        </h3>
        <div class="row mt-4">
            <div class="col-4">
                <label for="producto">
                    <i class="fas fa-box me-2"></i> Selecciona el producto:
                </label>
                <select id="producto" class="form-control">
                    <option value="" selected>Selecciona el producto...</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre_producto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label for="periodo">
                    <i class="fas fa-calendar-alt me-2"></i> Selecciona el periodo:
                </label>
                <div>
                    <select id="periodo" class="form-control" onchange="cambiarPeriodo(this)">
                        <option value="" selected>Selecciona el periodo...</option>
                        <option value="dias">Por días</option>
                        <option value="semanas">Por Semanas</option>
                        <option value="meses">Por Meses</option>
                        <option value="anios">Por años</option>
                    </select>
                </div>
            </div>
            <div class="col-2">
                <button id="btnGenerar" class="btn btn-success mt-4" onclick="generarReporte()">
                    <i class="fas fa-file-alt me-2"></i> Generar Reporte
                </button>
            </div>
        </div>
        <div class="container mt-4">
            <div class="row" id="caja-opciones">

            </div>
        </div>
    </div>
    <div class="container d-flex justify-content-center align-items-center mt-5">
        <canvas id="myChart" width="600" height="400"></canvas>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            opacity: 0.9;
        }
        input.form-control:focus, select.form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            opacity: 0.9;
        }
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js @4.4.1/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#producto').select2();
        });
    </script>

    <script>
        let myChart;
        let datax = [];        // etiquetas del eje X
        let seriesData = [];   // valores de la serie
        document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('myChart').getContext('2d');
    window.myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Ventas',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Rendimiento de Ventas por Producto',
                    color: '#333',
                    font: {
                        size: 18,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Período' }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Ventas Totales' }
                }
            }
        }
    });
});

        function cambiarPeriodo(){
            $('#caja-opciones').empty();
            let periodo = $('#periodo').val();
            if (periodo === 'dias') {
                $('#caja-opciones').append(`
                    <div class="form-group col-6">
                        <label for="fechaInicio">
                            <i class="fas fa-calendar-day me-2"></i> Fecha de Inicio:
                        </label>
                        <input type="date" id="fechaInicio" class="form-control">
                    </div>
                    <div class="form-group col-6">
                        <label for="fechaFin">
                            <i class="fas fa-calendar-day me-2"></i> Fecha de Fin:
                        </label>
                        <input type="date" id="fechaFin" class="form-control">
                    </div>
                `);
            } else if (periodo === 'semanas') {
                $('#caja-opciones').append(`
                    <div class="form-group col-6">
                        <label for="semanaInicio">
                            <i class="fas fa-calendar-week me-2"></i> Semana de Inicio:
                        </label>
                        <input type="week" id="semanaInicio" class="form-control">
                    </div>
                    <div class="form-group col-6">
                        <label for="semanaFin">
                            <i class="fas fa-calendar-week me-2"></i> Semana de Fin:
                        </label>
                        <input type="week" id="semanaFin" class="form-control">
                    </div>
                `);
            }
            else if (periodo === 'meses') {
                $('#caja-opciones').append(`
                    <div class="form-group col-6">
                        <label for="mesInicio">
                            <i class="fas fa-calendar-alt me-2"></i> Mes de Inicio:
                        </label>
                        <input type="month" id="mesInicio" class="form-control">
                    </div>
                    <div class="form-group col-6">
                        <label for="mesFin">
                            <i class="fas fa-calendar-alt me-2"></i> Mes de Fin:
                        </label>
                        <input type="month" id="mesFin" class="form-control">
                    </div>
                `);
            } 
            else if (periodo === 'anios') {
                $('#caja-opciones').append(`
                    <div class="form-group col-6">
                        <label for="anioInicio">
                            <i class="fas fa-calendar-year me-2"></i> Año de Inicio:
                        </label>
                        <input type="number" id="anioInicio" class="form-control" placeholder="Año de inicio (ej. 2023)">
                    </div>
                    <div class="form-group col-6">
                        <label for="anioFin">
                            <i class="fas fa-calendar-year me-2"></i> Año de Fin:
                        </label>
                        <input type="number" id="anioFin" class="form-control" placeholder="Año de fin (ej. 2024)">
                    </div>
                `);
            }
        }

        function generarReporte(){
    let personalId = $('#producto').val();
    let periodo = $('#periodo').val();
    let fechaInicio, fechaFin, semanaInicio, semanaFin, mesInicio, mesFin , anioInicio, anioFin;

    if (periodo === 'dias') {
        fechaInicio = $('#fechaInicio').val();
        fechaFin = $('#fechaFin').val();
        if (!fechaInicio || !fechaFin) {
            Swal.fire('Error', 'Por favor selecciona las fechas de inicio y fin.', 'error');
            return;
        }
    } else if (periodo === 'semanas') {
        semanaInicio = $('#semanaInicio').val();
        semanaFin = $('#semanaFin').val();
        if (!semanaInicio || !semanaFin) {
            Swal.fire('Error', 'Por favor selecciona las semanas de inicio y fin.', 'error');
            return;
        }
    } else if (periodo === 'meses') {
        mesInicio = $('#mesInicio').val();
        mesFin = $('#mesFin').val();
        if (!mesInicio || !mesFin) {
            Swal.fire('Error', 'Por favor selecciona los meses de inicio y fin.', 'error');
            return;
        }
    } else if (periodo === 'anios') {
        anioInicio = $('#anioInicio').val();
        anioFin = $('#anioFin').val();
        if (!anioInicio || !anioFin) {
            Swal.fire('Error', 'Por favor ingresa los años de inicio y fin.', 'error');
            return;
        }
    } else {
        Swal.fire('Error', 'Por favor selecciona un periodo válido.', 'error');
        return;
    }

    Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor espera mientras se genera el reporte.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url:"{{ route('rendimientopersonal.obtenerVentasProductos', ':personalId') }}".replace(':personalId', personalId),
        type: 'GET',
        data: {
            periodo: periodo,
            fechaInicio: $('#fechaInicio').val(),
            fechaFin: $('#fechaFin').val(),
            semanaInicio: semanaInicio,
            semanaFin: semanaFin,
            mesInicio: $('#mesInicio').val(),
            mesFin: $('#mesFin').val(),
            anioInicio: anioInicio,
            anioFin: anioFin,
        },
        success: function(response) {
            Swal.close();

            let datax = [];
            let seriesData = [];

            if (!window.myChart) {
                Swal.fire('Error', 'El gráfico no está listo aún. Inténtalo nuevamente en unos segundos.', 'error');
                return;
            }

            if (response.fechas.length === 0) {
                Swal.fire('Sin Datos', 'No se encontraron datos para el periodo seleccionado.', 'info');
                return;
            }

            if (periodo == 'dias') {
                response.fechas.forEach(function(fecha) {
                    datax.push(fecha.dia);
                    seriesData.push(fecha.total);
                });
            } else if (periodo == 'semanas') {
                response.fechas.forEach(function(fecha) {
                    datax.push(fecha.semana);
                    seriesData.push(fecha.total);
                });
            } else if (periodo == 'meses') {
                response.fechas.forEach(function(fecha) {
                    datax.push(fecha.mes);
                    seriesData.push(fecha.total);
                });
            } else if (periodo == 'anios') {
                response.fechas.forEach(function(fecha) {
                    datax.push(fecha.anio);
                    seriesData.push(fecha.total);
                });
            }

            window.myChart.data.labels = datax;
            window.myChart.data.datasets[0].data = seriesData;
            window.myChart.update();
        },
        error: function(xhr, status, error) {
            Swal.close();
            Swal.fire('Error', 'Ocurrió un error al generar el reporte. Por favor intenta nuevamente.', 'error');
            console.error('Error:', error);
        }
    });
}
    </script>
@stop