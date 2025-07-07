@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Rendimiento de Personal
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container">
        <h3>
            <i class="fas fa-chart-bar me-2"></i> Selecciona el periodo de tiempo y el personal para el reporte
        </h3>
        <div class="row mt-4">
            <div class="col-4">
                <label for="personal">
                    <i class="fas fa-users me-2"></i> Selecciona el personal:
                </label>
                <select id="personal" class="form-control">
                    <option value="" selected>Selecciona el preventista...</option>
                    @foreach($personal as $p)
                        <option value="{{ $p->id }}">{{ $p->nombres }} {{ $p->apellido_paterno }} {{ $p->apellido_materno }}</option>
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
        <div id="main" style="width: 600px;height:400px;"></div>
    </div>
@stop

@section('css')
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
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.6.0/dist/echarts.min.js"></script>

    <script>
        let datax = [];
        let seriesData = [];
        let myChart = echarts.init(document.getElementById('main'));
        let option = {
            xAxis: {
                data: datax,
            },
            yAxis: {},
            series: [
                {
                    type: 'bar',
                    data: seriesData,
                }
            ]
        };
        myChart.setOption(option);


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
            let personalId = $('#personal').val();
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
                let mesInicio = $('#mesInicio').val();
                let mesFin = $('#mesFin').val();
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
                url:"{{ route('rendimientopersonal.obtenerRendimientoPersonal', ':personalId') }}".replace(':personalId', personalId),
                type: 'GET',
                data: {
                    periodo: periodo,
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    semanaInicio: semanaInicio,
                    semanaFin: semanaFin,
                    mesInicio: $('#mesInicio').val(),
                    mesFin: $('#mesFin').val(),
                    anioInicio: anioInicio,
                    anioFin: anioFin,
                },
                success: function(response) {
                    Swal.close();
                    datax = [];
                    seriesData = [];
                    myChart.clear();
                    if( response.fechas.length === 0) {
                        Swal.fire('Sin Datos', 'No se encontraron datos para el periodo seleccionado.', 'info');
                        return;
                    }
                    if(periodo == 'dias'){
                        response.fechas.forEach(function(fecha) {
                            datax.push(fecha.dia);
                            seriesData.push(fecha.total);
                        });
                        myChart.setOption({
                            xAxis: {
                                data: datax,
                            },
                            yAxis: {},
                            series: [{
                                type: 'bar',
                                data: seriesData,
                            }]
                        });
                    }
                    else if(periodo == 'semanas'){
                        response.fechas.forEach(function(fecha) {
                            datax.push(fecha.semana);
                            seriesData.push(fecha.total);
                        });
                        myChart.setOption({
                            xAxis: {
                                data: datax,
                            },
                            yAxis: {},
                            series: [{
                                type: 'bar',
                                data: seriesData,
                            }]
                        });
                    }
                    else if(periodo == 'meses'){
                        response.fechas.forEach(function(fecha) {
                            datax.push(fecha.mes);
                            seriesData.push(fecha.total);
                        });
                        myChart.setOption({
                            xAxis: {
                                data: datax,
                            },
                            yAxis: {},
                            series: [{
                                type: 'bar',
                                data: seriesData,
                            }]
                        });
                    }
                    else if(periodo == 'anios'){
                        response.fechas.forEach(function(fecha) {
                            datax.push(fecha.anio);
                            seriesData.push(fecha.total);
                        });
                        myChart.setOption({
                            xAxis: {
                                data: datax,
                            },
                            yAxis: {},
                            series: [{
                                type: 'bar',
                                data: seriesData,
                            }]
                        });
                    }
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