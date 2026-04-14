@extends('adminlte::page')

@section('title', 'Lineas')

@section('content_header')
    <div class="inventory-simple-header">
        <div>
            <span>Inventario</span>
            <h1>Lineas por marca</h1>
            <p>Organiza la jerarquia marca -> linea -> producto con reglas consistentes.</p>
        </div>
        <button class="btn btn-success inventory-simple-btn" data-toggle="modal" data-target="#agregar-linea" onclick="anadirlineasboton()">
            <i class="fas fa-plus"></i> Asignar lineas
        </button>
    </div>
    <div class="container py-4 inventory-legacy-header" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de productos
            </span>
        </div>
    </div>
@stop

@section('content')


    <!--REGISTRO DE LINEA-->
    <x-adminlte-modal id="agregar-linea" size="lg" theme="dark" icon="fas fa-sitemap" title="Asignar lineas a una marca">
            <div class="modal-body px-4">
                <form id="registro-cliente">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="descripcion_linea" class="form-label text-muted">Agregar</label>
                        </div>
                        <div class="line-modal-flow">
                            <section class="line-modal-step">
                                <div class="line-step-number">1</div>
                                <div>
                                    <strong>Selecciona la marca</strong>
                                    <span>Las lineas quedaran agrupadas dentro de esta marca.</span>
                                </div>
                            </section>
                            <div class="line-modal-field">
                                <label for="marca-select">Marca</label>
                        <select name="marcas" id="marca-select">
                            <option value="" selected>Seleccione marca...</option>
                            @forelse($marcas_busqueda as $marca)
                                <option value="{{$marca->id}}">{{$marca->descripcion}}</option>
                            @empty
                                <option value="">Marcas no disponibles</option>
                            @endforelse
                        </select>
                            </div>
                            <section class="line-modal-step">
                                <div class="line-step-number">2</div>
                                <div>
                                    <strong>Escribe las lineas</strong>
                                    <span>Ejemplo: Galletas, Lacteos, Limpieza, Bebidas.</span>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="row g3 mt-3 line-modal-field">
                        <div class="col-md-12">
                            <label for="descripcion_linea" class="form-label text-muted">Descripción de la línea <button class="btn btn-success ml-2" type="button" id="boton-agregar">+</button></label>
                            <div id="registro-lineas">
                                <div class='d-flex justify-content-center align-items-center my-2' id='0'>
                                    <input type="text" name="descripcion_linea[]" class="descripcion-linea form-control shadow-sm border-1" placeholder="Descripción de la línea">
                                    <button class='btn btn-danger' type="button" onclick='botonEliminar(this)'> - </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <div class="line-modal-footer">
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2 line-modal-action" />
                <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Guardar lineas" class="rounded-3 px-4 py-2 line-modal-action" onclick="guardarLinea()" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    <div class="line-page">
        <section class="line-summary" aria-label="Resumen de lineas">
            <article class="line-summary-card">
                <span>Marcas</span>
                <strong>{{ $resumenLineas['marcas'] ?? 0 }}</strong>
            </article>
            <article class="line-summary-card">
                <span>Lineas registradas</span>
                <strong>{{ $resumenLineas['lineas'] ?? 0 }}</strong>
            </article>
            <article class="line-summary-card line-warning-card">
                <span>Marcas sin lineas</span>
                <strong>{{ $resumenLineas['marcas_sin_lineas'] ?? 0 }}</strong>
            </article>
        </section>

        <section class="line-search-box">
            <div>
                <strong>Busqueda por marca</strong>
                <span>Filtra una marca y administra sus lineas desde la misma fila.</span>
            </div>
            <div class="line-search-row">
                <label for="nombre-marca-lineas">Marca</label>
                <input type="text" class="form-control" name="nombre" id="nombre-marca-lineas" placeholder="Ej: NESTLE">
                <button type="button" class="btn btn-outline-secondary inventory-simple-btn" id="limpiarboton">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </section>
    </div>

    <div class="d-flex flex-column justify-content-center align-items-center line-legacy-search">
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
                
                <button class="btn" id="boton-agregar-linea-modal" data-toggle="modal" data-target="#agregar-linea" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;" onclick="anadirlineasboton()">
                    <i class="fas fa-plus mr-2"></i> Asignar Lineas
                </button>

            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar por el nombre de la marca para ubicar sus lineas.
                </p>
                <label for="nombre" class="form-label text-muted">Nombre de la Marca</label>
                <input type="text" class="form-control shadow-sm border-0" name="nombre" id="nombre-marca-lineas-legacy" placeholder="Ej: Chocolates" style="border-radius: 8px;">
            </div>
        </div>
    </div>

    <div class="container line-table-box">
        <table class="table table-bordered" id="tabla-lineas">
            <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Marca</th>
                        <th scope="col">Lineas</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
        </table>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet"></link>
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/slim-select@2.6.0/dist/slimselect.css" rel="stylesheet" />

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

        .content-wrapper {
            background: #eef3f1;
        }

        .inventory-legacy-header {
            display: none;
        }

        .line-legacy-search {
            display: none !important;
        }

        .inventory-simple-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }

        .inventory-simple-header span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .inventory-simple-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.65rem;
            font-weight: 900;
        }

        .inventory-simple-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }

        .inventory-simple-btn {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        .line-page {
            display: grid;
            gap: 12px;
            margin-bottom: 12px;
        }

        .line-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .line-summary-card,
        .line-search-box,
        .line-table-box {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }

        .line-summary-card {
            padding: 14px;
        }

        .line-summary-card span,
        .line-search-box span,
        .line-brand-block small,
        .line-chip small {
            color: #64748b;
            font-weight: 700;
        }

        .line-summary-card strong {
            display: block;
            margin-top: 4px;
            color: #17211d;
            font-size: 1.75rem;
            font-weight: 900;
        }

        .line-warning-card {
            background: #fee2e2;
            border-color: #fecaca;
        }

        .line-warning-card span,
        .line-warning-card strong {
            color: #b91c1c;
        }

        .line-search-box {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .line-search-box strong,
        .line-search-row label,
        .line-brand-block strong {
            color: #17211d;
            font-weight: 900;
        }

        .line-search-row {
            display: grid;
            grid-template-columns: 100px minmax(0, 1fr) 150px;
            gap: 10px;
            align-items: center;
        }

        .line-search-row .form-control {
            min-height: 42px;
            border-radius: 8px;
        }

        .line-table-box {
            overflow-x: auto;
            padding: 14px;
        }

        #tabla-lineas {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        #tabla-lineas thead th {
            border: 0;
            color: #64748b;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #tabla-lineas tbody td {
            border-top: 1px solid #d7e4df;
            border-bottom: 1px solid #d7e4df;
            vertical-align: middle;
            font-weight: 800;
        }

        #tabla-lineas tbody td:first-child {
            border-left: 1px solid #d7e4df;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #tabla-lineas tbody td:last-child {
            border-right: 1px solid #d7e4df;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .line-brand-block,
        .line-chip span {
            display: grid;
            gap: 2px;
        }

        .line-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .line-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: #e7f6ec;
            border: 1px solid #b7e4c7;
            border-radius: 8px;
            color: #17211d;
        }

        .line-chip .btn {
            border-radius: 8px;
            font-weight: 900;
        }

        .line-empty-state {
            padding: 10px;
            background: #f8fafc;
            border: 1px dashed #d7e4df;
            border-radius: 8px;
            color: #64748b;
            font-weight: 900;
        }

        #agregar-linea .modal-body {
            background: #f8fafc;
        }

        #agregar-linea .modal-body .row.g-3 > .col-md-12:first-child {
            display: none;
        }

        .line-modal-flow {
            display: grid;
            gap: 12px;
            width: 100%;
            padding: 4px;
        }

        .line-modal-step,
        .line-modal-field {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 12px;
        }

        .line-modal-step {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 10px;
            align-items: center;
        }

        .line-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #e7f6ec;
            color: #15803d;
            font-weight: 900;
        }

        .line-modal-step strong,
        .line-modal-field label {
            display: block;
            margin: 0;
            color: #17211d;
            font-weight: 900;
        }

        .line-modal-step span {
            display: block;
            color: #64748b;
            font-weight: 700;
        }

        .line-modal-field select,
        .line-modal-field .form-control {
            min-height: 42px;
            border-radius: 8px;
        }

        .line-modal-field label[for="descripcion_linea"] {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 0;
            margin-bottom: 10px;
        }

        .line-modal-field label[for="descripcion_linea"]::before {
            content: 'Lineas de la marca';
            color: #17211d;
            font-size: 1rem;
            font-weight: 900;
        }

        #boton-agregar {
            min-height: 38px;
            border-radius: 8px;
            font-size: 0;
            font-weight: 900;
        }

        #boton-agregar::before {
            content: 'Agregar otra linea';
            font-size: .9rem;
        }

        #registro-lineas > div {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) 112px;
            gap: 8px;
            align-items: center;
        }

        #registro-lineas > div + div {
            margin-top: 8px;
        }

        #registro-lineas .btn-danger {
            min-height: 42px;
            border-radius: 8px;
            font-size: 0;
            font-weight: 900;
        }

        #registro-lineas .btn-danger::before {
            content: 'Quitar';
            font-size: .9rem;
        }

        .line-modal-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .line-modal-action {
            width: 100%;
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        @media (max-width: 767.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .inventory-simple-header {
                flex-direction: column;
            }

            .inventory-simple-btn {
                width: 100%;
            }

            .line-summary,
            .line-search-row {
                grid-template-columns: 1fr;
            }

            #tabla-lineas,
            #tabla-lineas tbody,
            #tabla-lineas tr,
            #tabla-lineas td {
                display: block;
                width: 100%;
            }

            #tabla-lineas thead {
                display: none;
            }

            #tabla-lineas tbody tr {
                margin-bottom: 12px;
                padding: 12px;
                background: #ffffff;
                border: 1px solid #d7e4df;
                border-radius: 8px;
            }

            #tabla-lineas tbody td {
                display: grid;
                grid-template-columns: 112px 1fr;
                gap: 8px;
                align-items: start;
                border: 0;
                padding: 8px 0;
            }

            #tabla-lineas tbody td::before {
                content: attr(data-label);
                color: #64748b;
                font-size: .75rem;
                font-weight: 900;
                text-transform: uppercase;
            }

            #tabla-lineas tbody td:first-child,
            #tabla-lineas tbody td:last-child {
                border: 0;
            }

            #tabla-lineas tbody td:nth-child(3) {
                grid-template-columns: 1fr;
            }

            .line-chip,
            .line-chip .btn {
                width: 100%;
                justify-content: center;
            }

            #registro-lineas > div,
            .line-modal-footer {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slim-select@2.6.0/dist/slimselect.min.js"></script>

    <script>
        let slimMarca = new SlimSelect({
            select: '#marca-select',
            settings: {
                placeholderText: "Seleccione una marca",
                searchText: "No se encontraron resultados",
                searchingText: "Buscando...",
                searchPlaceholder: "Buscar marca",
            }
        });
        $(document).ready(function(){
            $('#tabla-lineas').DataTable({
                language: {
                    url: '/i18n/es-ES.json',
                    emptyTable: 'No hay marcas registradas.',
                    processing: 'Cargando lineas...',
                },
                processing:true,
                serverSide:true,
                searching: false,
                responsive: false,

                ajax: {
                    url: "{{ route('administrador.lineas.index') }}",
                    type: "GET",
                    data: function (d) {
                        d.descripcion_marca = $('#nombre-marca-lineas').val();
                    }
                },
                columns: [
                    { data: 'id', width: '5%', searchable: false},
                    { data: 'descripcion_marca', width: '15%'},
                    { data: 'lineas',width: '80%',orderable: false, searchable: false}
                ],
                createdRow: function(row) {
                    const labels = ['ID', 'Marca', 'Lineas'];
                    $('td', row).each(function(index) {
                        $(this).attr('data-label', labels[index] || '');
                    });
                },
            });

            $('#nombre-marca-lineas').on('keyup', function() {
                $('#tabla-lineas').DataTable().ajax.reload();
            });
        });

        $('#boton-agregar').click(function(){
            let nuevoinput = `
                <div>
                    <input type="text" name="descripcion_linea[]" class="descripcion-linea form-control" placeholder="Ej: Galletas">
                    <button class="btn btn-danger" type="button" onclick="botonEliminar(this)">Quitar</button>
                </div>`;
            $('#registro-lineas').append(nuevoinput);
        });


        function botonEliminar(e){
            if ($('.descripcion-linea').length <= 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe haber al menos una línea.',
                });
                return;
            }
            e.parentNode.remove();
        }

        function anadirlineasboton(){
            botonkey = 0;
        }

        function guardarLinea(){
            $('#registro-cliente').submit();
        }

        $('#registro-cliente').submit(function (e) {
            e.preventDefault();
            let descripcion_linea = $('[name="descripcion_linea[]"]').map(function() {
                return $(this).val().trim();
            }).get();
            if (descripcion_linea.length === 0 || descripcion_linea.every(linea => linea === '')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe ingresar al menos una descripción de línea.',
                });
                return;
            }

            let marca_id = $('#marca-select').val();

            let camposIncompletos = false;

            if (!marca_id || camposIncompletos || descripcion_linea.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, complete todos los campos requeridos.',
                });
                return;
            }

            Swal.fire({
                title: 'Guardando...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false
            });

            $.ajax({
                url: "{{ route('administrador.lineas.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    marca_id: marca_id,
                    descripcion_linea: descripcion_linea
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Linea o lineas agregadas con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#tabla-lineas').DataTable().ajax.reload();
                    $('#registro-cliente')[0].reset();

                    // Vacía y vuelve a agregar una línea inicial
                    $('#registro-lineas').empty();
                    $('#registro-lineas').append(`
                        <div id="0">
                            <input type="text" name="descripcion_linea[]" class="descripcion-linea form-control" placeholder="Ej: Galletas">
                            <button class="btn btn-danger" type="button" onclick="botonEliminar(this)">Quitar</button>
                        </div>
                    `);
                    // Limpia el select de marcas
                    slimMarca.setSelected({ value: '', text: 'Seleccione marca...' });

                    $('#botonenviar-cerrar').click();
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Ocurrió un error al guardar la línea.',
                    });
                }
            });
        });

        function eliminarLinea(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        allowOutsideClick: false
                    });
                    $.ajax({
                        url: `{{ route('administrador.lineas.destroy', ':id')}}`.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Linea eliminada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-lineas').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                xhr.responseJSON?.message || 'Ocurrió un error al eliminar la línea.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function editarLinea(elemento) {
            const id = $(elemento).attr('id-linea');
            const nombreActual = $(elemento).attr('id-nombre-linea');

            Swal.fire({
                title: 'Editar nombre de la Línea',
                input: 'text',
                inputValue: nombreActual,
                inputPlaceholder: 'Ingrese la descripción de la línea',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: (nuevaDescripcion) => {
                    if (!nuevaDescripcion || nuevaDescripcion.trim() === '') {
                        Swal.showValidationMessage('Por favor, ingrese una descripción válida.');
                        return false;
                    }
                    return nuevaDescripcion.trim();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando...',
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        allowOutsideClick: false
                    })
                    $.ajax({
                        url: `{{ route('administrador.lineas.update', ':id') }}`.replace(':id', id),
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT',
                            descripcion_linea: result.value
                        },
                        success: () => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Línea actualizada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-lineas').DataTable().ajax.reload();
                        },
                        error: (xhr) => {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON?.message || 'Ocurrió un error al actualizar la línea.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        $('#limpiarboton').click(function() {
            $('#nombre-marca-lineas').val('');
            $('#tabla-lineas').DataTable().ajax.reload();
        });
    </script>

@stop
