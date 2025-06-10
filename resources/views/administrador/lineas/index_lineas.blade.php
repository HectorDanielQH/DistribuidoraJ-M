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

                @if ($eliminar_busqueda)                    
                    <button class="btn btn-danger ms-2" id="limpiarboton" style="font-weight: bold; border-radius: 8px;">
                        <i class="fas fa-times"></i> Limpiar búsqueda
                    </button>
                @endif
            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar cliente por nombre completo o cédula de identidad con cualquier coincidencia.
                </p>
                <form method="GET" action="{{ route('lineas.index') }}" class="row g-3">
                    <div class="col-md-8">
                        <label for="nombre" class="form-label text-muted">Nombre de la Marca</label>
                        <input type="text" class="form-control shadow-sm border-0" name="nombre" placeholder="Ej: Chocolates" value="{{ $request->nombre ?? '' }}"  style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn w-100" style="background-color: #3498db; color: white; font-weight: bold; border-radius: 8px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container" style="overflow-x: auto;">
        <table class="table table-bordered">
            <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Marca</th>
                        <th scope="col">Lineas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($marcas as $marca)
                        <tr class="w-100">
                            <th style="width: 5%" scope="row">
                                {{ $loop->iteration }}
                            </th>
                            <td style="width: 25%">
                                {{ $marca->descripcion }}
                            </td>
                            <td style="width: 70%">
                                @forelse($marca->linea as $linea)
                                    <span class="badge bg-dark m-1 p-1">
                                        {{$linea->descripcion_linea}}
                                        <button type="button" class="btn btn-warning btn-sm ms-1 mx-2" id-linea="{{ $linea->id }}" id-nombre-linea="{{$linea->descripcion_linea}}" onclick="editarLinea(this)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm ms-1 mr-1" onclick="eliminarLinea({{ $linea->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </span>
                                @empty
                                    <span class="badge bg-warning">{{__('No se agregaron lineas')}}</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No se encontraron resultados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $marcas->appends(request()->query())->links() }}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet"></link>

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

    <script>
        $(document).ready(function(){
            new SlimSelect({
                select: '#marca-select',
                settings:{
                    placeholderText: "Seleccione una marca",
                    searchText: "No se encontraron resultados",
                    searchingText: "Buscando...",
                    searchPlaceholder: "Buscar marca",
                }
            })
        });

        let botonkey=0;

        $('#boton-agregar').click(function(){
            let nuevoinput = `<div class='d-flex justify-content-center align-items-center my-2' id='${botonkey}'><input type="text" name="descripcion_linea" class="descripcion-linea form-control shadow-sm border-1" placeholder="Descripción de la línea"> <button class='btn btn-danger' type="button" onclick='botonEliminar(${botonkey})'> - </button></div>`;
            $('#registro-cliente').append(nuevoinput);
            botonkey++;
        });


        function botonEliminar(id){
            $(`#${id}`).remove();
        }

        function anadirlineasboton(){
            botonkey = 0;
        }

        function guardarLinea(){
            $('#registro-cliente').submit();
        }

        $('#registro-cliente').submit(function (e) {
            e.preventDefault();

            let marca_id = $('#marca-select').val();
            let descripcion_linea = [];

            let camposIncompletos = false;
            $('.descripcion-linea').each(function () {
                const valor = $(this).val().trim();
                if (valor === "") {
                    camposIncompletos = true;
                    return false;
                }
                descripcion_linea.push(valor);
            });

            if (!marca_id || camposIncompletos || descripcion_linea.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, complete todos los campos requeridos.',
                });
                return;
            }

            $.ajax({
                url: "{{ route('lineas.store') }}",
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
                    $('#botonenviar-cerrar').click();
                    location.reload();
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
                    $.ajax({
                        url: `{{ route('lineas.destroy', ':id')}}`.replace(':id', id),
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
                            location.reload();
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
                    $.ajax({
                        url: `{{ route('lineas.update', ':id') }}`.replace(':id', id),
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
                            setTimeout(() => location.reload(), 1600);
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
                window.location.href = "{{ route('lineas.index') }}";
        });
    </script>

@stop