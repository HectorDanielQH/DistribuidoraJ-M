@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de rutas
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-fw fa-route"></i>Rutas de Distribución
            </h2>
            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalCrearRuta">
                <i class="fas fa-plus"></i> Crear Nueva Ruta
            </button>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar las rutas de distribución.
        </p>
    </div>

     <!--REGISTRO DE LINEA-->
    <x-adminlte-modal id="modalCrearRuta" size="lg" theme="dark" icon="fas fa-plus" title="Agregar Ruta">
            <div class="modal-body px-4">
                <form id="registro-ruta">
                    @csrf
                    <div class="row g3 mt-3">
                        <div class="col-md-12">
                            <label for="nueva-ruta" class="form-label text-muted">Nueva Ruta</label>
                            <input type="text" name="nueva_ruta" id="nueva-ruta" class="descripcion-linea form-control shadow-sm border-1" placeholder="Ej: Zona Alto Potosi">
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" onclick="guardarRuta()" />
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" onclick="cerrarModal()" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <div class="container pb-5">
        <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
            <table id="tablaRutas" class="table table-striped table-bordered" style="width: 100%;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre de Ruta (Zona)</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center">Cargando datos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
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
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function(){
            $('#tablaRutas').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                "ajax": {
                    "url": "{{ route('administrador.rutas.index') }}",
                    "type": "GET",
                    /*"data": function (d) {
                        d.nombres_completos = $('#cajabusquedanombre').val();
                        d.cedulaidentidad = $('#cajabusquedacedula').val();
                    }*/
                },
                columns:[
                    { data: 'id', width: '10%'},
                    { data: 'nombre_ruta', width: '70%'},
                    { data: 'action', width: '20%', orderable: false, searchable: false }
                ],
                
            });
        });

        function cerrarModal() {
            $('#registro-ruta')[0].reset();
        }

        function guardarRuta(){
            let nuevaRuta=$('#nueva-ruta').val();
            if(nuevaRuta.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El nombre de la ruta no puede estar vacío.',
                });
                return;
            }
            Swal.fire({
                title: 'Creando Ruta',
                text: 'Por favor, espera mientras se crea la ruta.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.rutas.store') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    nueva_ruta: nuevaRuta
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Ruta creada exitosamente.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#botonenviar-cerrar').click();
                        $('#registro-ruta')[0].reset();
                        $('#tablaRutas').DataTable().ajax.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al crear la ruta. Inténtalo de nuevo.',
                    });
                }
            });
        }


        function eliminarRutas(e){
            let idRuta = $(e).attr('data-id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás deshacer esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando Ruta',
                        text: 'Por favor, espera mientras se elimina la ruta.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.rutas.destroy', ':id') }}".replace(':id', idRuta),
                        type: "DELETE",
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Ruta eliminada exitosamente.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                $('#tablaRutas').DataTable().ajax.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar la ruta. Inténtalo de nuevo.',
                            });
                        }
                    });
                }
            });
        }


        function editarRutas(e){
            let idRuta = $(e).attr('data-id');
            let nombreRuta = $(e).attr('data-nombre');
            Swal.fire({
                title: 'Editar Ruta',
                html: `<input type="text" id="editar-ruta" class="swal2-input" value="${nombreRuta}" placeholder="Nombre de la ruta">`,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const nuevaRuta = $('#editar-ruta').val().trim();
                    if (!nuevaRuta) {
                        Swal.showValidationMessage('El nombre de la ruta no puede estar vacío.');
                    }
                    return nuevaRuta;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando Ruta',
                        text: 'Por favor, espera mientras se actualiza la ruta.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.rutas.update', ':id') }}".replace(':id', idRuta),
                        type: "POST",
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT',
                            nombre_ruta: result.value
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Ruta actualizada exitosamente.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                $('#tablaRutas').DataTable().ajax.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error || 'Ocurrió un error al actualizar la ruta. Inténtalo de nuevo.',
                            });
                        }
                    });
                }
            });
        }
    </script> 
@stop