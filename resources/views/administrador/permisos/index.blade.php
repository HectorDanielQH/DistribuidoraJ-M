@extends('adminlte::page')

@section('title', 'Permisos')

@section('content_header')
    <div class="admin-header">
        <div>
            <span>Administracion</span>
            <h1>Roles y permisos</h1>
            <p>Define que puede hacer cada tipo de usuario.</p>
        </div>
        <button class="btn btn-success admin-main-btn" onclick="crearPermiso()">
            <i class="fas fa-plus"></i> Crear rol
        </button>
    </div>
@stop

@section('content')
    <div class="admin-page">
        <section class="admin-help">
            <i class="fas fa-user-shield"></i>
            <div>
                <strong>Un rol agrupa permisos.</strong>
                <span>Ejemplo: administrador, vendedor o contador.</span>
            </div>
        </section>

        <section class="admin-table-box">
            <table id="tablaPersmisos" class="table table-hover w-100">
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
        </section>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet">
    <style>
        :root {
            --surface: #ffffff;
            --soft: #eef3f1;
            --line: #dbe7e2;
            --text: #17211d;
            --muted: #64748b;
            --green: #15803d;
            --green-soft: #e7f6ec;
        }

        .content-wrapper {
            background: var(--soft);
        }

        input.form-control:focus, select.form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 .2rem rgba(21, 128, 61, .18);
        }

        .admin-header,
        .admin-help,
        .admin-table-box {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .admin-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
        }

        .admin-header span {
            color: var(--green);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .admin-header h1 {
            margin: 0;
            color: var(--text);
            font-size: 1.65rem;
            font-weight: 900;
        }

        .admin-header p,
        .admin-help span {
            margin: 4px 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .admin-main-btn,
        .btn-action {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        .admin-page {
            display: grid;
            gap: 12px;
        }

        .admin-help {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 10px;
            align-items: center;
            padding: 12px;
        }

        .admin-help i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--green-soft);
            color: var(--green);
            font-size: 1.2rem;
        }

        .admin-help strong {
            display: block;
            color: var(--text);
            font-weight: 900;
        }

        .admin-table-box {
            padding: 14px;
        }

        #tablaPersmisos {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        #tablaPersmisos thead th {
            border: 0;
            color: var(--muted);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #tablaPersmisos tbody td {
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
            font-weight: 800;
        }

        #tablaPersmisos tbody td:first-child {
            border-left: 1px solid var(--line);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #tablaPersmisos tbody td:last-child {
            border-right: 1px solid var(--line);
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(110px, 1fr));
            gap: 8px;
        }

        @media (max-width: 575.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .admin-header {
                align-items: stretch;
                flex-direction: column;
            }

            .admin-main-btn {
                width: 100%;
            }

            #tablaPersmisos,
            #tablaPersmisos tbody,
            #tablaPersmisos tr,
            #tablaPersmisos td {
                display: block;
                width: 100%;
            }

            #tablaPersmisos thead {
                display: none;
            }

            #tablaPersmisos tbody tr {
                margin-bottom: 10px;
                padding: 12px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--surface);
            }

            #tablaPersmisos tbody td,
            #tablaPersmisos tbody td:first-child,
            #tablaPersmisos tbody td:last-child {
                border: 0;
                border-radius: 0;
                padding: 7px 0;
            }

            #tablaPersmisos tbody td::before {
                content: attr(data-mobile-label);
                display: block;
                margin-bottom: 3px;
                color: var(--muted);
                font-size: .78rem;
                font-weight: 900;
                text-transform: uppercase;
            }

            .admin-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js"></script>

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
                createdRow: function (row) {
                    const labels = ['ID', 'Rol', 'Permisos', 'Acciones'];
                    $('td', row).each(function (index) {
                        $(this).attr('data-mobile-label', labels[index]);
                    });
                }
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
