@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de permisos
            </span>
        </div>
    </div>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-user-shield me-2"></i> Permisos
            </h2>
            <button class="btn btn-success" onclick="crearPermiso()">
                <i class="fas fa-plus me-2"></i> Crear nuevo rol
            </button>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar los roles de los usuarios.
        </p>
    </div>
@stop

@section('content')
    <div class="container">
        <table id="tablaPersmisos" class="table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>
                        Nombre del rol
                    </th>
                    <th>
                        Permisos
                    </th>
                    <th>
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
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
            $('#tablaPersmisos').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                "ajax": {
                    "url": "{{ route('administrador.permisos.index') }}",
                    "type": "GET",
                },
                columns:[
                    { data: 'id', width: '10%' },
                    { data: 'name', width: '30%' },
                    { data: 'permissions', width: '40%' },
                    { data: 'actions', orderable: false, searchable: false, width: '20%' }
                ],
                
            });
        });
    </script>

    <script>
        function crearPermiso() {
            Swal.fire({
                title: 'Crear nuevo rol',
                html: `
                    <input type="text" id="nombrePermiso" class="swal2-input" placeholder="Nombre del permiso">
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const nombrePermiso = document.getElementById('nombrePermiso').value;
                    if (!nombrePermiso) {
                        Swal.showValidationMessage('Por favor, ingresa un nombre para el permiso');
                    } else {
                        return nombrePermiso;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Creando permiso...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.permisos.store') }}",
                        type: "POST",
                        data: {
                            name: result.value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.close();
                            $('#tablaPersmisos').DataTable().ajax.reload();
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Permiso creado exitosamente',
                                icon: 'success',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo crear el permiso', 'error');
                        }
                    });
                }
            });
        }

        function deleteRole(e) 
        { 
            let id=$(e).attr('data-id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando permiso...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.permisos.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.close();
                            $('#tablaPersmisos').DataTable().ajax.reload();
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Permiso eliminado exitosamente',
                                icon: 'success',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo eliminar el permiso', 'error');
                        }
                    });
                }
            });
        }

        function editRole(e){
            let id=$(e).attr('data-id');
            Swal.fire({
                title: 'Editar rol',
                html: `
                    <input type="text" id="editRoleName" class="swal2-input" placeholder="Nombre del rol">
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const editRoleName = document.getElementById('editRoleName').value;
                    if (!editRoleName) {
                        Swal.showValidationMessage('Por favor, ingresa un nombre para el rol');
                    } else {
                        return editRoleName;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando rol...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.permisos.update', ':id') }}".replace(':id', id),
                        type: "PUT",
                        data: {
                            name: result.value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.close();
                            $('#tablaPersmisos').DataTable().ajax.reload();
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Rol actualizado exitosamente',
                                icon: 'success',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo actualizar el rol', 'error');
                        }
                    });
                }
            });
        }
    </script>
@stop