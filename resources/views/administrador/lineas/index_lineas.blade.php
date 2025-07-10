@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
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
    <x-adminlte-modal id="agregar-linea" size="lg" theme="dark" icon="fas fa-plus" title="Agregar Linea">
            <div class="modal-body px-4">
                <form id="registro-cliente">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="descripcion_linea" class="form-label text-muted">Agregar</label>
                        </div>
                        <select name="marcas" id="marca-select">
                            <option value="" selected>Seleccione marca...</option>
                            @forelse($marcas_busqueda as $marca)
                                <option value="{{$marca->id}}">{{$marca->descripcion}}</option>
                            @empty
                                <option value="">Marcas no disponibles</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="row g3 mt-3">
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
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" onclick="guardarLinea()" />
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
                
                <button class="btn" id="boton-agregar" data-toggle="modal" data-target="#agregar-linea" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;" onclick="anadirlineasboton()">
                    <i class="fas fa-plus mr-2"></i> Asignar Lineas
                </button>

            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar cliente por nombre completo o cédula de identidad con cualquier coincidencia.
                </p>
                <label for="nombre" class="form-label text-muted">Nombre de la Marca</label>
                <input type="text" class="form-control shadow-sm border-0" name="nombre" id="nombre-marca-lineas" placeholder="Ej: Chocolates" style="border-radius: 8px;">
            </div>
        </div>
    </div>

    <div class="container" style="overflow-x: auto;">
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
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet" integrity="sha384-d76uxpdVr9QyCSR9vVSYdOAZeRzNUN8A4JVqUHBVXyGxZ+oOfrZVHC/1Y58mhyNg" crossorigin="anonymous">

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
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

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
                    url: '/i18n/es-ES.json'
                },
                processing:true,
                serverSide:true,
                searching: false,

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
            });

            $('#nombre-marca-lineas').on('keyup', function() {
                $('#tabla-lineas').DataTable().ajax.reload();
            });
        });

        $('#boton-agregar').click(function(){
            let nuevoinput = `
                <div class='d-flex justify-content-center align-items-center my-2'>
                    <input type="text" name="descripcion_linea[]" class="descripcion-linea form-control shadow-sm border-1" placeholder="Descripción de la línea">
                    <button class='btn btn-danger' type="button" onclick='botonEliminar(this)'> - </button>
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
                        <div class='d-flex justify-content-center align-items-center my-2' id='0'>
                            <input type="text" name="descripcion_linea[]" class="descripcion-linea form-control shadow-sm border-1" placeholder="Descripción de la línea">
                            <button class='btn btn-danger' type="button" onclick='botonEliminar(this)'> - </button>
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
                window.location.href = "{{ route('administrador.lineas.index') }}";
        });
    </script>

@stop