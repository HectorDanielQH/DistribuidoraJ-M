@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de asignaciones
            </span>
        </div>
    </div>
     <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-route me-2"></i> Asignación de Rutas a Preventistas
            </h2>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes asignar rutas a los preventistas y gestionar sus asignaciones
        </p>
    </div>
    @if($no_atendidos->count() > 0)
        <div class="container">
            <div class="alert alert-warning m-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Hay clientes que no han sido atendidos. <button class="btn btn-dark" onclick="abrirModalNoAtendidos()">Haz clic aquí</button> para más detalles.
            </div>
        </div>
    @endif
@stop

@section('content')
    <div class="modal fade" id="asignarCliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="staticBackdropLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Asignar rutas al Preventista
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-3 text-black" id="clienteSelect" name="clientes" multiple="multiple" style="width: 100%; height: 25px;" aria-label="Seleccionar rutas">
                        @foreach($rutas as $ruta)
                            <option value="{{ $ruta->id }}" class="text-black">{{ $ruta->nombre_ruta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardarclientesasignados">Asignar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="asignarClienteUnitario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="staticBackdropLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        Asignar Cliente al Preventista
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" id="id_vendedor_cliente_unitario" value="">
                <div class="modal-body">
                    <select class="form-select mb-3 text-black" id="clienteunitario" name="clientesunitarios" multiple="multiple" style="width: 100%; height: 25px;" aria-label="Seleccionar cliente">
                    </select>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente-unitario">Cancelar</button>
                    <button type="button" class="btn btn-success" id="guardarclientesasignadosunitarios">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarPorClientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-lg" id="staticBackdropLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Asignar clientes al Preventista
                </h1>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-select mb-3 text-black" id="porclientesSelect" name="clientes" multiple="multiple" style="width: 100%; height: 25px;" aria-label="Seleccionar rutas">
                </select>
            </div>
            <div class="modal-footer d-flex justify-content-between align-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarclientesasignados">Guardar</button>
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visualizarClientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="staticBackdropLabel">
                        <i class="fas fa-users me-2"></i>
                            Ver Clientes Asignados
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="clientesAsignadosTable">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre Completo</th>
                                    <th scope="col">Celular</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Ruta</th>
                                </tr>
                            </thead>
                            <tbody id="clientesAsignadosBody">
                                <!-- Aquí se llenarán los clientes asignados mediante AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente">Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="visualizarRutas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title text-lg" id="staticBackdropLabel">
                        <i class="fas fa-route me-2"></i>
                            Ver Rutas Asignadas
                    </h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="rutasAsignadasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre Ruta</th>
                                    <th scope="col">Nro. Clientes</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí se llenarán los clientes asignados mediante AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-rutas">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    


    <div class="container">
        <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
            <table id="tabla-asignaciones" class="table table-bordered align-middle text-center" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">C.I.</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Celular</th>
                        <th scope="col">Asignaciones</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-asignaciones').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                "ajax": {
                    "url": "{{ route('administrador.asignacionclientes.index') }}",
                    "type": "GET",
                },
                columns:[
                    { data: 'id', width: '5%'},
                    { data: 'cedulaidentidad', width: '15%'},
                    { data: 'nombre_completo', width: '35%'},
                    { data: 'celular', width: '15%'},
                    { data: 'asignacion', width: '15%'},
                    { data: 'action', width: '15%', orderable: false, searchable: false }
                ],
                
            });
        });
    </script>
    <script>
        $('#clienteSelect').select2({
            placeholder: "Seleccione una ruta",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        $('#clienteunitario').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        $('#vendedorSelectControl').select2({
            placeholder: "Seleccione un vendedor",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        let id_vendedor = null;

        function valordeusuariovendedor(e){
            Swal.fire({
                title: 'Cargando rutas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            id_vendedor = $(e).attr('data-id');
            $('#clienteSelect').val(null).trigger('change');
            $.ajax({
                url: "{{ route('asignacionclientes.getRutasNoAsignados') }}",
                type: 'GET',
                success: function(data) {
                    Swal.close();
                    $('#clienteSelect').empty();
                    data.forEach(cliente => {
                        $('#clienteSelect').append(new Option(cliente.nombre_ruta, cliente.id, false, false));
                    });
                    $('#clienteSelect').trigger('change');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cargar los clientes.',
                    });
                }
            });
        }

        $('#guardarclientesasignados').click(function(){
            let selectclientes=$('#clienteSelect').val();

            if(selectclientes.length === 0){
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar al menos una ruta.',
                });
                return;
            }
            Swal.fire({
                title: 'Asignando rutas con clientes...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.asignacionclientes.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_vendedor: id_vendedor,
                    rutas: selectclientes
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Clientes asignados correctamente.',
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#asignarCliente').modal('hide');
                        $('#clienteSelect').val(null).trigger('change');
                        $('#tabla-asignaciones').DataTable().ajax.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al asignar los clientes.',
                    });
                }
            });
        });

        $('#cancelar-asignacion-cliente').click(function() {
            $('#clienteSelect').val(null).trigger('change');
        });

        function clientesAsignados(e) {
            let id_vendedor = $(e).attr('data-id');
            Swal.fire({
                title: 'Cargando clientes asignados...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $('#clientesAsignadosTable').DataTable().destroy();
            $('#clientesAsignadosTable').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('administrador.asignacionclientes.getClientesAsignados', ':id') }}".replace(':id', id_vendedor),
                    "type": "GET",
                },
                columns: [
                    { data: 'id', width: '5%' },
                    { data: 'cedula_identidad', width: '15%' },
                    { data: 'nombre_completo', width: '35%' },
                    { data: 'celular', width: '15%' },
                    { data: 'ubicacion', width: '30%' }
                ],
                "destroy": true,
                "initComplete": function(settings, json) {
                    Swal.close();
                },
                "error": function(xhr, error, code) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cargar los clientes asignados.',
                    });
                }
            });
        }
 
        function eliminarRutaAsignada(e) {
            let rutaId = $(e).attr('data-id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡Esta acción no se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('administrador.asignacionrutas.destroyasignacion', ':id') }}`.replace(':id', rutaId),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Cliente eliminado correctamente.',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 2000,
                            }).then(() => {
                                $('#tabla-asignaciones').DataTable().ajax.reload();
                                $('#rutasAsignadasTable').DataTable().ajax.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al eliminar el cliente.',
                            });
                        }
                    });
                }
            });
        }

        function rutasAsignadas(e) {
            let id_vendedor = $(e).attr('data-id');
            Swal.fire({
                title: 'Cargando rutas asignadas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $('#rutasAsignadasTable').DataTable().destroy();
            $('#rutasAsignadasTable').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('administrador.asignacionclientes.getRutasAsignadas', ':id') }}".replace(':id', id_vendedor),
                    "type": "GET",
                },
                columns: [
                    { data: 'id', width: '5%' },
                    { data: 'nombre_ruta', width: '75%' },
                    { data: 'numero_clientes', width: '10%' },
                    { data: 'action', width: '10%', orderable: false, searchable: false }
                ],
                "initComplete": function(settings, json) {
                    Swal.close();
                },  
                "error": function(xhr, error, code) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cargar las rutas asignadas.',
                    });
                }
            });
        }

        $('#porclientesSelect').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        //cliente unitario busqueda por ajax select2
        $('#clienteunitario').select2({
            placeholder: "Seleccione un cliente",
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('administrador.clientes.buscar') }}",
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term // este es el texto buscado
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(cliente => ({
                            id: cliente.id,
                            text: cliente.nombres + ' ' + cliente.apellidos,
                        }))
                    };
                },
                cache: true
            }
        });


        function agregarClienteUnitario(e)
        {
            let id_vendedor = $(e).attr('data-id');
            $('#id_vendedor_cliente_unitario').val(id_vendedor);
        }

        $('#cancelar-asignacion-cliente-unitario').click(function() {
            $('#clienteunitario').val(null).trigger('change');
            $('#id_vendedor_cliente_unitario').val('');
        });

        $('#guardarclientesasignadosunitarios').click(function() {
            let selectclientes = $('#clienteunitario').val();
            let id_vendedor = $('#id_vendedor_cliente_unitario').val();

            if (selectclientes.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar al menos un cliente.',
                });
                return;
            }
            Swal.fire({
                title: 'Asignando cliente unitario...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.asignacionclientes.storeUnitario') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_vendedor: id_vendedor,
                    clientes: selectclientes
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Cliente asignado correctamente.',
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#cancelar-asignacion-cliente-unitario').click();
                        $('#clienteunitario').val(null).trigger('change');
                        $('#tabla-asignaciones').DataTable().ajax.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al asignar el cliente.',
                    });
                }
            });
        });
    </script>

    <script>
        function abrirModalNoAtendidos() {
            Swal.fire({
                title: 'Clientes No Atendidos',
                html: `
                    <p>Hay clientes que no han sido atendidos. ¿Qué deseas hacer?</p>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                        <button id="btn-reporte" class="swal2-confirm swal2-styled" style="background-color: #3085d6;">Sí, ver reporte</button>
                        <button id="btn-subsanadas" class="swal2-confirm swal2-styled" style="background-color: #28a745;">Subsanar Observaciones</button>
                        <button id="btn-luego" class="swal2-cancel swal2-styled" style="background-color: #d33;">En otro momento</button>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didRender: () => {
                    // Botón 1: Descargar PDF
                    document.getElementById('btn-reporte').addEventListener('click', () => {
                        Swal.close();
                        // Reemplaza esta URL por la ruta real de tu PDF
                        window.open("{{ route('administrador.noatendidos.pdf') }}", '_blank');
                    });
                    // Botón 2: Subsanadas
                    document.getElementById('btn-subsanadas').addEventListener('click', () => {
                        Swal.fire({
                            title: 'Observaciones Subsanadas',
                            text: 'Por favor, asegúrate de que las observaciones de los clientes no atendidos hayan sido subsanadas.',
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            cancelButtonText: 'Cancelar',
                            showCancelButton: true,
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Subsanando observaciones...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                                $.ajax({
                                    url: "{{ route('administrador.noatendidos.subsanadas') }}",
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Éxito',
                                            text: 'Observaciones subsanadas correctamente.',
                                            showConfirmButton: false,
                                            timer: 2000,
                                        });
                                    },
                                    error: function(xhr) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Ocurrió un error al subsanar las observaciones.',
                                        });
                                    }
                                });
                            }
                        });
                    });
                    // Botón 3: Más tarde
                    document.getElementById('btn-luego').addEventListener('click', () => {
                        Swal.fire({
                            title: 'Recordatorio',
                            text: 'Recuerda revisar los clientes no atendidos más tarde.',
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            allowOutsideClick: false,
                        });
                    });
                }
            });
        }
    </script>

    @if($no_atendidos->count() > 0)
        <script>
            abrirModalNoAtendidos();
        </script>
    @endif

@stop