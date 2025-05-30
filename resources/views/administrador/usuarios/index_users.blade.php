@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de usuarios
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
                
                <button class="btn" id="boton-agregar" data-toggle="modal" data-target="#modalPurple" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
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
                    Puedes buscar usuarios por nombre completo o cédula de identidad con cualquier coincidencia.
                </p>
                <form method="GET" action="{{ route('usuarios.index') }}" class="row g-3">
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
    <x-adminlte-modal id="modalPurple" size="lg" theme="dark" icon="fas fa-user-plus" title="Agregar Usuario">
            <div class="modal-body px-4">
                <form id="registro-usuario" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
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
                            <x-adminlte-input name="email" label="Correo electrónico" placeholder="Ej.: juancarlos@gmail.com" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-envelope text-muted"></i>
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
                            <x-adminlte-select name="rol" label="Asignar Rol" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="
                                            fas fa-list-alt
                                        "></i>
                                    </div>
                                </x-slot>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->name }}">{{ strtoupper($rol->name) }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-select name="estado" label="Estado" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="
                                            fas fa-check-circle
                                        "></i>
                                    </div>
                                </x-slot>
                                <option value="ACTIVO" >ACTIVO</option>
                                <option value="DE BAJA">DE BAJA</option>
                            </x-adminlte-select>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input-file name="fotoperfil" label="Foto de Perfil" igroup-size="lg" placeholder="Ingrese la foto de perfil">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input-file>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviar" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>



    <!--MODAL VISUALIZAR-->

    <x-adminlte-modal id="modalVisualizar" size="lg" theme="dark">
        <x-slot name="title">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/logo_white.webp') }}" 
                    alt="Foto de perfil" 
                    class="rounded-circle mr-3" 
                    id="fotoperfilview"
                    style="width: 50px; height: 50px; object-fit: cover;">
                <span class="ml-3"><i class="fas fa-user-plus mr-2"></i>Información del Usuario</span>
            </div>
        </x-slot>

        <div class="modal-body px-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>C.I.:</strong> <p id="ciview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Nombres:</strong> <p id="nombresview"></p></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Apellido Paterno:</strong> <p id="apellidopaternoview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Apellido Materno:</strong> <p id="apellidomaternoview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Celular:</strong> <p id="celularview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Correo electrónico:</strong> <p id="emailview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Dirección:</strong> <p id="direccionview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Rol:</strong> <p id="rolview"></p> </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Estado:</strong> <p id="idestado"></p> </p>
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>


    <!--MODAL EDITAR-->

    <x-adminlte-modal id="modalEditarUsuario" size="lg" theme="dark" icon="fas fa-user-edit" title="Editar Usuario">
            <div class="modal-body px-4">
                <form id="registro-usuario-editar" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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
                            <x-adminlte-input name="apellidopaterno" id=apellidopaternoeditar label="Apellido Paterno" placeholder="Ej: Mamani" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input name="apellidomaterno" id=apellidomaternoeditar label="Apellido Materno" placeholder="Ej.: Romay" label-class="text-dark">
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
                            <x-adminlte-input name="email" id="emaileditar" label="Correo electrónico" placeholder="Ej.: juancarlos@gmail.com" label-class="text-dark">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-envelope text-muted"></i>
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
                            <x-adminlte-select name="rol" id="roleditar" label="Asignar Rol" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="
                                            fas fa-list-alt
                                        "></i>
                                    </div>
                                </x-slot>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->name }}">{{ strtoupper($rol->name) }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-select name="estado" id="estadoeditar" label="Estado" label-class="text-dark" igroup-size="lg">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="
                                            fas fa-check-circle
                                        "></i>
                                    </div>
                                </x-slot>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="DE BAJA">DE BAJA</option>
                            </x-adminlte-select>
                        </div>

                        <div class="col-md-6">
                            <x-adminlte-input-file name="fotoperfil" id="fotoperfileditar" label="Foto de Perfil" igroup-size="lg" placeholder="Ingrese la foto de perfil">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-dark">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input-file>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonenviareditar" theme="success" icon="fas fa-check" label="Guardar Cambios" class="rounded-3 px-4 py-2" />
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
                        <th scope="col">Perfil</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Celular</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td style="text-align: left;">
                                <i class="fas fa-id-card mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $usuario->cedulaidentidad }}
                            </td>
                            <td>
                                @if ($usuario->foto_perfil)
                                    <img src="{{ route('usuarios.imagenperfil', $usuario->id) }}" alt="Foto de perfil" class="img-fluid rounded-circle" style="width: 40px; height: 50px;">
                                @else
                                    <img src="{{ asset('images/logo_color.webp') }}" alt="Foto de perfil" class="img-fluid rounded-circle" style="width: 40px; height: 50px;">
                                @endif
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-user-circle mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $usuario->nombres }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}

                                <br>
                                
                                @if ($usuario->estado == 'DE BAJA')
                                    <span class="badge bg-danger">De baja</span>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-mobile-alt mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ $usuario->celular }}
                            </td>
                            <td style="text-align: left;">
                                <i class="fas fa-user-tag mr-3" style="font-size: 1.5rem; color: #2c3e50;"></i>
                                {{ strtoupper($usuario->getRoleNames()->first()) }}
                                
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Acciones del usuario">
                                    <button 
                                        class="btn btn-warning btn-sm rounded-3 me-2" 
                                        data-toggle="modal"
                                        data-target="#modalEditarUsuario"
                                        id-usuario="{{ $usuario->id }}"
                                        onclick="editarUsuario(this)">
                                        <i class="fas fa-user-edit"></i>
                                    </button>

                                    <button class="btn btn-danger btn-sm rounded-3" id-usuario="{{ $usuario->id }}" onclick="eliminarUsuario(this)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <button class="btn btn-info btn-sm rounded-3 " data-toggle="modal" data-target="#modalVisualizar"  id-usuario="{{ $usuario->id }}" onclick="visualizarUsuario(this)">
                                        <i class="fas fa-eye
                                        "></i>
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
            {{ $usuarios->appends(request()->query())->links() }}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                form.addEventListener('submit', function () {
                    Swal.fire({
                        title: 'Cargando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
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
                    window.location.href = "{{ route('usuarios.index') }}";
                }
            });
        });

        $('#botonenviar').click(()=>{
            $('#registro-usuario').submit();
        })

        $('#registro-usuario').submit(function(event) {
            event.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('usuarios.store') }}",
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
                        title: 'Usuario creado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar').click();
                    location.reload();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al crear el usuario',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        });


        function eliminarUsuario(element) {
            const idUsuario = $(element).attr('id-usuario');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar este usuario una vez eliminado.",
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
                        url: `{{ route('usuarios.destroy', ':id') }}`.replace(':id', idUsuario),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Usuario eliminado con éxito',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar el usuario',
                                text: xhr.responseJSON.message,
                            });
                        }
                    });
                }
            });
        }

        function visualizarUsuario(element) {
            $('#ciview').text('');
            $('#nombresview').text('');
            $('#apellidopaternoview').text('');
            $('#apellidomaternoview').text('');
            $('#celularview').text('');
            $('#emailview').text('');
            $('#direccionview').text('');
            $('#rolview').text('');
            $('#idestado').text('');
            $('#fotoperfilview').attr('src', '{{ asset('images/logo_white.webp') }}');

            const idUsuario = $(element).attr('id-usuario');
            $.ajax({
                url: `{{ route('usuarios.show', ':id') }}`.replace(':id', idUsuario),
                type: 'GET',
                success: function(response) {
                    $('#ciview').text(response.usuario.cedulaidentidad);
                    $('#nombresview').text(response.usuario.nombres);
                    $('#apellidopaternoview').text(response.usuario.apellido_paterno);
                    $('#apellidomaternoview').text(response.usuario.apellido_materno);
                    $('#celularview').text(response.usuario.celular);
                    $('#emailview').text(response.usuario.email);
                    $('#direccionview').text(response.usuario.direccion);
                    $('#rolview').text(response.rol);
                    $('#idestado').text(response.usuario.estado);

                    if(response.usuario.foto_perfil) {
                        $('#fotoperfilview').attr('src', `{{ route('usuarios.imagenperfil', ':id') }}`.replace(':id', response.usuario.id));

                    } else {
                        $('#fotoperfilview').attr('src', '{{ asset('images/logo_white.webp') }}');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Datos cargados con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al obtener los datos del usuario',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        }




        let idUsuarioEditar = null;

        function editarUsuario(element) {
            $('#cedulaidentidadeditar').val('');
            $('#nombreseditar').val('');
            $('#apellidopaternoeditar').val('');
            $('#apellidomaternoeditar').val('');
            $('#celulareditar').val('');
            $('#emaileditar').val('');
            $('#direccioneditar').val('');
            $('#roleditar').val('');
            $('#estadoeditar').val('');
            $('#fotoperfileditar').val('');

            idUsuarioEditar = $(element).attr('id-usuario');
            $.ajax({
                url: `{{ route('usuarios.show', ':id') }}`.replace(':id', idUsuarioEditar),
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    $('#cedulaidentidadeditar').val(response.usuario.cedulaidentidad);
                    $('#nombreseditar').val(response.usuario.nombres);
                    $('#apellidopaternoeditar').val(response.usuario.apellido_paterno);
                    $('#apellidomaternoeditar').val(response.usuario.apellido_materno);
                    $('#celulareditar').val(response.usuario.celular);
                    $('#emaileditar').val(response.usuario.email);
                    $('#direccioneditar').val(response.usuario.direccion);
                    $('#roleditar').val(response.rol);
                    $('#estadoeditar').val(response.usuario.estado);

                    Swal.fire({
                        icon: 'success',
                        title: 'Datos cargados con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al obtener los datos del usuario',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        }

        $('#botonenviareditar').click(()=>{
            $('#registro-usuario-editar').submit();
        });

        $('#registro-usuario-editar').submit(function(event) {
            event.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: `{{ route('usuarios.update', ':id') }}`.replace(':id', idUsuarioEditar),
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
                        title: 'Usuario editado con éxito',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#botonenviar-cerrar-editar').click();
                    location.reload();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al editar el usuario',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        });
    </script>
@stop