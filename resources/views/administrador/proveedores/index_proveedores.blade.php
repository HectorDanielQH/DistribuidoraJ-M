@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de proveedores
            </span>
        </div>
    </div>
@stop

@section('content')

    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="card shadow-sm border-0 " style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
                
                <button class="btn" id="boton-agregar" data-toggle="modal" data-target="#modalAgregarProveedor" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar al proovedor por su nombre
                </p>
                <div class="row g-3 w-100 d-flex align-items-center">
                    <div class="col-md-12">
                        <label for="nombre" class="form-label text-muted">Nombre completo</label>
                        <input type="text" class="form-control shadow-sm border-0" name="nombre" id="nombreProveedor" placeholder="Ej: Proovedor"  style="border-radius: 8px;">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--AGREGAR USUARIO-->
    <x-adminlte-modal id="modalAgregarProveedor" size="lg" theme="dark" icon="fas fa-user-plus" title="Agregar Distribuidor">
        <div class="modal-body px-4">
            <form id="registro-proveedores" action="{{ route('administrador.proveedores.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <x-adminlte-input name="nombreproveedor" label="Nombre del Proovedor" placeholder="Ej: Linea 1" label-class="text-dark">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-users text-muted"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-md-12 w-100">
                        <label for="opciones" class="text-dark">Ingrese Marcas</label>
                        <select class="w-100" id="opciones" multiple="multiple" name="opciones[]">
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>


    <!--EDITAR USUARIO-->
    <x-adminlte-modal id="modalEditarProovedor" size="lg" theme="dark" icon="fas fa-user-edit" title="Editar Distribuidor">
        <div class="modal-body px-4">
            <form id="registro-proveedores-editar" method="POST">
                @method('PUT')
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <x-adminlte-input name="nombreproveedor" id="nombre-proveedor-editar" label="Nombre del Proovedor" placeholder="Ej: Linea 1" label-class="text-dark">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-users text-muted"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviar-editar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviar-cerrar-editar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>
    

    <div class="container table-responsive my-4">
        <table class="table table-bordered" id="tabla-proveedores">
            <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre de Proveedor</th>
                    <th scope="col">Descripcion de venta de Productos y Marcas</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">
                        <div class="alert alert-warning mb-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No se encontraron resultados para la búsqueda, quizas con otra coincidencia.
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-proveedores').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                "searching": false,

                "ajax": {
                    "url": "{{ route('administrador.proveedores.index') }}",
                    "type": "GET",
                    "data": function (d) {
                        d.proveedor = $('#nombreProveedor').val();
                    }
                },
                columns:[
                    { data: 'id',width: '5%' },
                    { data: 'nombre_proveedor',width: '20%'},
                    { data: 'producto_marcas',width: '65%', orderable: false, searchable: false},
                    { data: 'acciones',width: '10%', orderable: false, searchable: false }
                ],

            });

            $('#nombreProveedor').on('keyup', function() {
                $('#tabla-proveedores').DataTable().ajax.reload();
            });

            $('#opciones').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%',
                placeholder: 'Agregue marcas o productos',
                theme: "classic"
            });

            $('#opciones-editar').select2({
                tags: true,
                tokenSeparators: [','],
                width: '100%',
                placeholder: 'Agregue marcas o productos',
                theme: "classic"
            });
        });

        $('#botonenviar').click(function(){
            $('#registro-proveedores').submit();
        });

        $('#registro-proveedores').submit(function(e) {
            Swal.fire({
                title: 'Agregando Proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: "{{ route('administrador.proveedores.store') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Proveedor agregado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar').click();
                    $('#tabla-proveedores').DataTable().ajax.reload();
                    $('#registro-proveedores')[0].reset();
                    $('#opciones').val(null).trigger('change');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al agregar proveedor',
                        text: xhr.responseJSON.message || 'Por favor, intente nuevamente.',
                    });
                }
            });
        });


        function funcionEliminar(e){
            let id_usuario=e.getAttribute('id-usuario');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar este proveedor una vez eliminado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.proveedores.destroy', ':id') }}`.replace(':id', id_usuario),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proveedor eliminado con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar el proveedor',
                                text: "Tienes registros asociados a este proveedor, no puedes eliminarlo.",
                            });
                        }
                    });
                }
            });
        }

        let idProveedorEditar = null;

        function funcionEditar(e){
            Swal.fire({
                title: 'Cargando datos del proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let id_usuario=e.getAttribute('id-usuario-editar');
            idProveedorEditar = id_usuario;
            $.ajax({
                url: `{{ route('administrador.proveedores.show', ':id') }}`.replace(':id', id_usuario),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.close();
                    $('#nombre-proveedor-editar').val(response.usuario.nombre_proveedor);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el proveedor',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        }

        $('#botonenviar-editar').click(function(e){
            $('#registro-proveedores-editar').submit();
        });
        

        $('#registro-proveedores-editar').submit(function(event) {
            event.preventDefault();
            let formData = $(this).serialize();
            Swal.fire({
                title: 'Editando Proveedor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: `{{ route('administrador.proveedores.update', ':id') }}`.replace(':id', idProveedorEditar),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Proveedor editado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar-editar').click();
                    $('#tabla-proveedores').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Ocurrió un error inesperado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el proveedor',
                        text: errorMessage,
                    });
                }
            });
        });

        $('#limpiarboton').click(function(){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminará la búsqueda actual.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, limpiar búsqueda',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Limpiando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    })
                    $('#tabla-proveedores').DataTable().ajax.reload();
                }
            });
        });

        function eliminarMarcas(e){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar esta marca una vez eliminada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.marcas.destroy', ':id') }}`.replace(':id', e),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca eliminada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar la marca',
                                text: "Tienes registros asociados a esta marca, no puedes eliminarla.",
                            });
                        }
                    });
                }
            });
        }

        function anadirMarca(idProveedor){
            Swal.fire({
                title: 'Agregar Marca',
                input: 'text',
                inputPlaceholder: 'Ingrese la descripción de la marca',
                showCancelButton: true,
                confirmButtonText: 'Agregar',
                cancelButtonText: 'Cancelar',
                preConfirm: (marca) => {
                    if (!marca) {
                        Swal.showValidationMessage('Por favor, ingrese una marca válida.');
                        return false;
                    }
                    return marca;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Agregando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    let nuevaMarca = result.value;
                    $.ajax({
                        url: "{{ route('administrador.marcas.store') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { descripcion: nuevaMarca, proveedor_id: idProveedor },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca agregada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al agregar la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }


        function editarFuncion(e){
            let idMarca = e.getAttribute('id-marca');
            let nombreMarca = e.getAttribute('nombre-marca');
            Swal.fire({
                title: 'Editar Marca',
                input: 'text',
                inputValue: nombreMarca,
                inputPlaceholder: 'Ingrese la nueva descripción de la marca',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: (nuevaDescripcion) => {
                    if (!nuevaDescripcion) {
                        Swal.showValidationMessage('Por favor, ingrese una descripción válida.');
                        return false;
                    }
                    return nuevaDescripcion;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let descripcionActualizada = result.value;
                    Swal.fire({
                        title: 'Actualizando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: `{{ route('administrador.marcas.update', ':id') }}`.replace(':id', idMarca),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { descripcion: descripcionActualizada, _method: 'PUT' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca actualizada con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }

        function moverMarcas(idMarca) {
            Swal.fire({
                title: 'Mover Marca',
                text: "Selecciona el nuevo proveedor para esta marca.",
                input: 'select',
                inputOptions: {
                    @foreach ($proveedores as $proveedor)
                        '{{ $proveedor->id  }}': '{{ $proveedor->nombre_proveedor }}',
                    @endforeach
                },
                showCancelButton: true,
                confirmButtonText: 'Mover',
                cancelButtonText: 'Cancelar',
                preConfirm: (nuevoProveedorId) => {
                    if (!nuevoProveedorId) {
                        Swal.showValidationMessage('Por favor, selecciona un proveedor válido.');
                        return false;
                    }
                    return nuevoProveedorId;
                }
            }).then((result) => {
                Swal.fire({
                    title: 'Moviendo...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                if (result.isConfirmed) {
                    let nuevoProveedorId = result.value;
                    $.ajax({
                        url: `{{ route('administrador.marca.mover', ':id') }}`.replace(':id', idMarca),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { proveedor_id: nuevoProveedorId },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca movida con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-proveedores').DataTable().draw();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al mover la marca',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }
    </script>
@stop