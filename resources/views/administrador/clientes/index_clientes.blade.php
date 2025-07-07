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
    </div>
@stop

@section('content')


    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
                
                <button class="btn" id="boton-agregar" data-toggle="modal" data-target="#agregar-cliente" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
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
                <form method="GET" action="{{ route('clientes.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label for="nombre" class="form-label text-muted">Nombre completo</label>
                        <input type="text" class="form-control shadow-sm border-0" name="nombre" placeholder="Ej: Juan Pérez" value="{{ $request->nombre ?? '' }}"  style="border-radius: 8px;">
                    </div>
                    <div class="col-md-5">
                        <label for="ci" class="form-label text-muted">Cédula de identidad</label>
                        <input type="text" class="form-control shadow-sm border-0" name="ci" placeholder="Ej: 12345678" value="{{ $request->ci ?? '' }}"  style="border-radius: 8px;">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn w-100" style="background-color: #3498db; color: white; font-weight: bold; border-radius: 8px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



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
                            <x-adminlte-input name="nombres" label="Nombres" placeholder="Ej.: Juan Carlos" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="apellidopaterno" label="Apellido Paterno" placeholder="Ej: Mamani" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="apellidomaterno" label="Apellido Materno" placeholder="Ej.: Romay" label-class="text-dark">
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
                            <x-adminlte-input name="direccion" label="Dirección donde vive" placeholder="Ej.: Calle Siempre Viva" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-select name="ruta" id="ruta" label="Selecciona la Ruta" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="fas fa-route"></i>
                                    </div>
                                </x-slot>
                                <option value="">Seleccione una ruta</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}" {{ old('ruta') == $ruta->id ? 'selected' : '' }}>{{ $ruta->nombre_ruta }}</option>
                                @endforeach
                            </x-adminlte-select>
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
                            <x-adminlte-input name="apellidopaterno" id="apellidopaternoeditar" label="Apellido Paterno" placeholder="Ej: Mamani" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="apellidomaterno" id="apellidomaternoeditar" label="Apellido Materno" placeholder="Ej.: Romay" label-class="text-dark">
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
                            <x-adminlte-input name="direccion" id="direccioneditar" label="Dirección donde vive" placeholder="Ej.: Calle Siempre Viva" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-select name="ruta"  id="rutaEditar" label="Selecciona la Ruta" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="fas fa-route"></i>
                                    </div>
                                </x-slot>
                                <option value="">Seleccione una ruta</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}" {{ old('ruta') == $ruta->id ? 'selected' : '' }}>{{ $ruta->nombre_ruta }}</option>
                                @endforeach
                            </x-adminlte-select>
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
        <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
            <table class="table table-bordered align-middle text-center" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">C.I.</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Celular</th>
                        <th scope="col">Direccion</th>
                        <th scope="col">Ruta</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td style="text-align: left;">
                                <i class="fas fa-id-card mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $cliente->cedula_identidad }}
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-user-circle mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-mobile-alt mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $cliente->celular }}
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-map-marker-alt mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $cliente->ubicacion }}
                                
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-route mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $cliente->ruta ? $cliente->ruta->nombre_ruta : 'Sin ruta asignada' }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Acciones del usuario">
                                    <button 
                                        class="btn btn-warning btn-sm rounded-3 me-2" 
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        id-cliente="{{ $cliente->id }}"
                                        id-cliente-cedula="{{ $cliente->cedula_identidad }}"
                                        id-cliente-nombres="{{ $cliente->nombres }}"
                                        id-cliente-paterno="{{ $cliente->apellido_paterno }}"
                                        id-cliente-materno="{{ $cliente->apellido_materno }}"
                                        id-cliente-celular="{{ $cliente->celular }}"
                                        id-cliente-ubicacion="{{ $cliente->ubicacion }}"
                                        onclick="editarUsuario(this)">
                                        <i class="fas fa-user-edit"></i>
                                    </button>

                                    <button class="btn btn-danger btn-sm rounded-3" id-cliente="{{ $cliente->id }}" onclick="eliminarUsuario(this)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No se encontraron resultados para la búsqueda, quizas con otra coincidencia.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $clientes->appends(request()->query())->links() }}
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
        $('#botonenviar').click(function(){
            $('#registro-cliente').submit();
        })

        $('#registro-cliente').submit(function (e){
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: `{{ route('clientes.store') }}`,
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
                    setTimeout(() => location.reload(), 1600);
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
            const idClientePaterno = $(element).attr('id-cliente-paterno');
            const idClienteMaterno = $(element).attr('id-cliente-materno');
            const idClienteCelular = $(element).attr('id-cliente-celular');
            const idClienteUbicacion = $(element).attr('id-cliente-ubicacion');

            $('#idclienteeditar').val(idCliente);
            $('#cedulaidentidadeditar').val(idClienteCedula);
            $('#nombreseditar').val(idClienteNombres);
            $('#apellidopaternoeditar').val(idClientePaterno);
            $('#apellidomaternoeditar').val(idClienteMaterno);
            $('#celulareditar').val(idClienteCelular);
            $('#direccioneditar').val(idClienteUbicacion);
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
                        url: `{{ route('clientes.destroy', ':id') }}`.replace(':id', idCliente),
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
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar el cliente',
                                text: xhr.responseJSON.message,
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
            $.ajax({
                url: `{{ route('clientes.update', ':id') }}`.replace(':id', id_cliente),
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
                    location.reload();
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


         $('#limpiarboton').click(function() {
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
                    window.location.href = "{{ route('clientes.index') }}";
                }
            });
        });
    </script>
@stop