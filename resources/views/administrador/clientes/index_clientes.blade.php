@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de clientes
            </span>
        </div>
        <div class="w-100 d-flex justify-content-center mt-4">
            <button class="btn mx-1" id="boton-agregar" data-toggle="modal" data-target="#agregar-cliente" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </button>
            <button class="btn mx-1" id="boton-excel" data-toggle="modal" data-target="#agregar-archivo-excel" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                <i class="fas fa-file-excel"></i> Cargar clientes de Excel
            </button>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css"/>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
@stop

@section('content')

    <!--Modal apra Excel-->
    <x-adminlte-modal id="agregar-archivo-excel" size="lg" theme="dark" icon="fas fa-user-plus" title="Agregar Clientes">
            <div class="modal-body px-4">
                <form id="archivo-registro-cliente" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <x-adminlte-input-file name="archivo_excel" label="Selecciona el archivo Excel" placeholder="Selecciona un archivo" label-class="text-dark" accept=".xlsx, .xls, .csv">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-file-excel text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input-file>
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted">
                                Asegúrate de que el archivo Excel tenga las siguientes columnas: C.I., Nombres, Apellido Paterno, Apellido Materno, Celular, Dirección, Ruta.
                            </p>
                            <p class="text-muted">
                                Puedes descargar un <a href="{{ asset('plantillas/plantilla_clientes.xlsx') }}" class="text-decoration-underline">ejemplo de plantilla</a> para cargar los clientes.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviararchivo" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviararchivo-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>


    <!--AGREGAR USUARIO-->
    <x-adminlte-modal id="agregar-cliente" size="lg" theme="dark" icon="fas fa-user-plus" title="Agregar Cliente">
            <div class="modal-body px-4">
                <form id="registro-cliente">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-adminlte-input name="cedulaidentidad" label="C.I." placeholder="Ej: 1234567" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-id-card text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="nombres" label="Nombres (*)" placeholder="Ej.: Juan Carlos" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="apellidos" label="Apellidos" placeholder="Ej: Mamani Roman" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="celular" label="Nro. de Celular" placeholder="Ej.: 78757879" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-mobile-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="calle_avenida" label="Dirección donde vive (*)" placeholder="Ej.: Calle Siempre Viva" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="zona_barrio" label="Zona de referencia (*)" placeholder="Ej.: Zona Mecanicos Barrio ETC" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="referencia_direccion" label="Referencia de la tienda" placeholder="Ej.: Casa color amarillo" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <label for="ruta" class="form-label text-muted">Selecciona la Ruta</label>
                            <select name="ruta" id="ruta" class="form-control shadow-sm border-0" style="border-radius: 8px;">
                                <option value="">Seleccione una ruta (*)</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
                                @endforeach
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
    <x-adminlte-modal id="modalEditarCliente" size="lg" theme="dark" icon="fas fa-user-edit" title="Editar Cliente">
            <div class="modal-body px-4">
                <form id="registro-cliente-editar">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="idcliente" id="idclienteeditar">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-adminlte-input name="cedulaidentidad" id="cedulaidentidadeditar" label="C.I." placeholder="Ej: 1234567" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-id-card text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="nombres" id="nombreseditar" label="Nombres" placeholder="Ej.: Juan Carlos" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        
                        <div class="col-md-6">
                            <x-adminlte-input name="apellidos" id="apellidoseditar" label="Apellidos" placeholder="Ej.: Romay" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>


                        <div class="col-md-6">
                            <x-adminlte-input name="celular" id="celulareditar" label="Nro. de Celular" placeholder="Ej.: 78757879" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-mobile-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="calle_avenida" id="calleAvenidaEditar" label="Dirección donde vive (*)" placeholder="Ej.: Calle Siempre Viva" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="zona_barrio" id='zonaBarrioEditar' label="Zona de referencia (*)" placeholder="Ej.: Zona Mecanicos Barrio ETC" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="referencia_direccion" id="referenciaDireccionEditar" label="Referencia de la tienda" placeholder="Ej.: Casa color amarillo" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <label for="rutaEditar" class="form-label text-muted">Selecciona la Ruta</label>
                            <select name="ruta" id="rutaEditar" class="form-control shadow-sm border-0" style="border-radius: 8px;">
                                <option value="">Seleccione una ruta</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}" {{ old('ruta') == $ruta->id ? 'selected' : '' }}>{{ $ruta->nombre_ruta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviar-editar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviar-cerrar-editar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>


    <!-- TABLA -->
    <div class="container pb-5">
        <table id="tabla-clientes" class="table table-striped table-bordered dt-responsive" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>Celular</th>
                    <th>Dirección</th>
                    <th>Zona</th>
                    <th>Ruta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.css">

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
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        #overlay-destacar {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 1050;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.js"></script>

    <script>
        $(document).ready(function(){
            // Inicializar Select2
            $('#ruta').select2({
                placeholder: 'Seleccione una ruta',
                width: '100%'
            });
            $('#rutaEditar').select2({
                placeholder: 'Seleccione una ruta',
                width: '100%'
            });

            $('#tabla-clientes').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '/i18n/es-ES.json'
                },
                ajax: {
                    url: "{{ route('administrador.clientes.index') }}",
                    type: 'GET',
                },
                columns: [
                    { data: 'id', name: 'id', searchable: false, responsivePriority: 1 },
                    { data: 'nombres_completos', name: 'nombres_completos', responsivePriority: 1 },
                    { data: 'celular', name: 'celular', responsivePriority: 3 },
                    { data: 'calle_avenida', name: 'calle_avenida', responsivePriority: 5 },
                    { data: 'zona_barrio', name: 'zona_barrio', responsivePriority: 6 },
                    { data: 'ruta', name: 'ruta', responsivePriority: 4 },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false, responsivePriority: 6}
                ],
                order: [[0, 'asc']],
            });
        });
    </script>

    <script>
        $('#botonenviar').click(function(){
            $('#registro-cliente').submit();
        })

        $('#registro-cliente').submit(function (e){
            e.preventDefault();
            var formData = $(this).serialize();
            Swal.fire({
                title: 'Agregando cliente...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: `{{ route('administrador.clientes.store') }}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cliente agregado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar').click();
                    $('#tabla-clientes').DataTable().ajax.reload();
                    $('#registro-cliente')[0].reset();
                    $('#ruta').val('').trigger('change'); // Limpiar el select2
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Ocurrió un error inesperado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al agregar cliente',
                        text: errorMessage,
                    });
                }
            });
        });

        function editarUsuario(element){
            const idCliente = $(element).attr('id-cliente');
            const idClienteCedula = $(element).attr('id-cliente-cedula');
            const idClienteNombres = $(element).attr('id-cliente-nombres');
            const idClienteApellidos = $(element).attr('id-cliente-apellidos');
            const idClienteCelular = $(element).attr('id-cliente-celular');
            const idClienteCalleAvenida = $(element).attr('id-cliente-calleavenida');
            const idClienteZonaBarrio = $(element).attr('id-cliente-zonabarrio');
            const idClienteReferenciaDireccion = $(element).attr('id-cliente-referenciadireccion');
            const idClienteRuta = $(element).attr('id-cliente-ruta');

            $('#idclienteeditar').val(idCliente);
            $('#cedulaidentidadeditar').val(idClienteCedula);
            $('#nombreseditar').val(idClienteNombres);
            $('#apellidoseditar').val(idClienteApellidos);
            $('#celulareditar').val(idClienteCelular);
            $('#calleAvenidaEditar').val(idClienteCalleAvenida);
            $('#zonaBarrioEditar').val(idClienteZonaBarrio);
            $('#referenciaDireccionEditar').val(idClienteReferenciaDireccion);
            $('#rutaEditar').val(idClienteRuta).trigger('change');

        }

        function eliminarUsuario(element){
            const idCliente = $(element).attr('id-cliente');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar este cliente una vez eliminado.",
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
                        url: `{{ route('administrador.clientes.destroy', ':id') }}`.replace(':id', idCliente),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cliente eliminado con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-clientes').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar el cliente',
                                text: 'Ocurrió un error al intentar eliminar el cliente. Por favor, inténtalo de nuevo más tarde.',
                            });
                        }
                    });
                }
            });
        }

        $('#botonenviar-editar').click(function(e){
            $('#registro-cliente-editar').submit();
        })


        $('#registro-cliente-editar').submit(function(e){
            e.preventDefault();
            let formData=$(this).serialize();
            let params = new URLSearchParams(formData);
            let id_cliente = params.get('idcliente'); 
            Swal.fire({
                title: 'Editando cliente...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: `{{ route('administrador.clientes.update', ':id') }}`.replace(':id', id_cliente),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cliente editado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar-editar').click();
                    $('#tabla-clientes').DataTable().ajax.reload(null, false);
                    $('#registro-cliente-editar')[0].reset();
                    $('#rutaEditar').val('').trigger('change'); // Limpiar el select2
                    $('#idclienteeditar').val('');
                    $('#cedulaidentidadeditar').val('');
                    $('#nombreseditar').val('');
                    $('#apellidoseditar').val('');
                    $('#celulareditar').val('');
                    $('#calleAvenidaEditar').val('');
                    $('#zonaBarrioEditar').val('');
                    $('#referenciaDireccionEditar').val('');
                    $('#rutaEditar').val('').trigger('change'); // Limpiar el select2

                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el cliente',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        });
    </script>

    <script>
        $('#botonenviararchivo').click(function() {
            $('#archivo-registro-cliente').submit();
        });

        $('#archivo-registro-cliente').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            Swal.fire({
                title: 'Cargando archivo...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: `{{ route('administrador.clientes.importar') }}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Clientes cargados con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviararchivo-cerrar').click();
                    $('#tabla-clientes').DataTable().ajax.reload(null, false);
                    $('#archivo-registro-cliente')[0].reset();
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Ocurrió un error inesperado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al cargar el archivo',
                        text: errorMessage,
                    });
                }
            });
        });
    </script>
@stop