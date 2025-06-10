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
@stop

@section('content')
    <div class="modal fade" id="asignarCliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title text-lg" id="staticBackdropLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Asignar Cliente
                </h1>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-select mb-3 text-black" id="clienteSelect" name="clientes" multiple="multiple" style="width: 100%; height: 25px;" aria-label="Seleccionar cliente">
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" class="text-black">{{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer d-flex justify-content-between align-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="cancelar-asignacion-cliente">Cancelar</button>
                <button type="button" class="btn btn-success" id="guardarclientesasignados">Guardar</button>
            </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="visualizarCliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">C.I.</th>
                                    <th scope="col">Nombre Completo</th>
                                    <th scope="col">Celular</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Acciones</th>
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

    <div class="container">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active btn btn-primary" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">
                    <i class="fas fa-users me-2"></i> Vendedores Asignados
                </button>
                <button class="nav-link btn btn-primary ml-2" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                    <i class="fas fa-route mr-2"></i> Controles de rutas
                </button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                <div class="container py-5">
                    <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
                        <table class="table table-bordered align-middle text-center" style="min-width: 800px;">
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
                                @forelse($vendedores as $vendedor)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $vendedor->cedulaidentidad }}</td>
                                        <td>{{ $vendedor->nombres }} {{ $vendedor->apellido_paterno }} {{ $vendedor->apellido_materno }}</td>
                                        <td>{{ $vendedor->celular }}</td>
                                        <td>
                                            @if($vendedor->asignaciones->count() > 0)
                                                <span class="badge bg-success">{{ $vendedor->asignaciones->count() }}</span>
                                            @else
                                                <span class="badge bg-danger">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-target="#visualizarCliente" data-bs-toggle="modal" data-id="{{ $vendedor->id }}" onclick="clientesAsignados(this)">
                                                <i class="fas fa-eye mr-1"></i>Ver Asignaciones 
                                            </button>
                                            <button class="btn btn-success btn-sm" data-bs-target="#asignarCliente" data-bs-toggle="modal" data-id="{{ $vendedor->id }}" onclick="valordeusuariovendedor(this)">
                                                <i class="fas fa-plus mr-1"></i> Asignar Clientes
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay vendedores asignados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $asignaciones->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                <!--caja para seleccionar el vendedor-->
                <div class="container py-5">
                    <div class="row mb-3 d-flex justify-content-center align-items-center">
                        <div class="col-md-6 d-flex flex-column">
                            <label for="vendedorSelectControl" class="form-label me-2">Seleccionar Vendedor:</label>
                            <select class="form-select" id="vendedorSelectControl" onchange="valordeusuariovendedor(this)" style="width: 100%;">
                                <option value="" disabled selected>Seleccione un vendedor</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" data-id="{{ $vendedor->id }}">{{ $vendedor->nombres }} {{ $vendedor->apellido_paterno }} {{ $vendedor->apellido_materno }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex justify-content-center align-items-center">
                            <button class="btn btn-primary mt-3" id="buscarRutaVendedor">
                                <i class="fas fa-search me-2"></i> Buscar
                            </button>
                            <button class="btn btn-danger mt-3 ml-2" id="buscarRutaVendedorReset">
                                <i class="fas fa-undo-alt me-2"></i> Resetear Asignaciones
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
                        <table class="table table-bordered align-middle text-center" style="min-width: 800px;">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Fecha de Asignacion</th>
                                    <th scope="col">Fecha de Atencion</th>
                                    <th scope="col">Pedido</th>
                                </tr>
                            </thead>
                            <tbody id="clientesAsignadosBodyrutas">
                                <tr>
                                    <td colspan="6" class="text-center">Seleccione un vendedor para ver los clientes asignados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

    <script>
        $('#clienteSelect').select2({
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
            id_vendedor = $(e).attr('data-id');
            $('#clienteSelect').val(null).trigger('change');
            $.ajax({
                url: "{{ route('asignacionclientes.getClientesNoAsignados') }}",
                type: 'GET',
                success: function(data) {
                    $('#clienteSelect').empty();
                    data.forEach(cliente => {
                        $('#clienteSelect').append(new Option(cliente.nombres + ' ' + cliente.apellido_paterno + ' ' + cliente.apellido_materno, cliente.id, false, false));
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
                    text: 'Debe seleccionar al menos un cliente.',
                });
                return;
            }
            $.ajax({
                url: "{{ route('asignacionclientes.store') }}",
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
                        text: 'Clientes asignados correctamente.',
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#asignarCliente').modal('hide');
                        $('#clienteSelect').val(null).trigger('change');
                        location.reload();
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
            $.ajax({
                url: "{{ route('asignacionclientes.getClientesAsignados',':id') }}".replace(':id', id_vendedor),
                type: 'GET',
                data: { id_vendedor: id_vendedor },
                success: function(data) {
                    $('#clientesAsignadosBody').empty();
                    if (data.length > 0) {
                        data.forEach((cliente, index) => {
                            $('#clientesAsignadosBody').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${cliente.cedula_identidad}</td>
                                    <td>${cliente.nombres} ${cliente.apellido_paterno} ${cliente.apellido_materno}</td>
                                    <td>${cliente.celular}</td>
                                    <td>${cliente.ubicacion}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="eliminarCliente(${cliente.id}, ${id_vendedor})">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#clientesAsignadosBody').append('<tr><td colspan="6">No hay clientes asignados.</td></tr>');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cargar los clientes asignados.',
                    });
                }
            });
        }

        function eliminarCliente(clienteId, vendedorId) {
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
                        url: `/clientesasignadosavendedoreseliminar/${clienteId}/${vendedorId}`,
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
                                window.location.reload();
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

        $('#buscarRutaVendedor').click(function(){
            let id_vendedor = $('#vendedorSelectControl').val();
            if (!id_vendedor) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor, seleccione un vendedor.',
                });
                return;
            }
            $.ajax({
                url: "{{ route('vendedores.obtenerRuta', ':id') }}".replace(':id', id_vendedor),
                type: 'GET',
                data: {
                    _token: '{{ csrf_token() }}',
                 },

                success: function(data) {
                    $('#clientesAsignadosBodyrutas').empty();
                    if (data.length > 0) {
                        data.forEach((cliente, index) => {
                            $('#clientesAsignadosBodyrutas').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${cliente.nombres} ${cliente.apellido_paterno} ${cliente.apellido_materno}</td>
                                    <td>${cliente.ubicacion}</td>
                                    <td>${cliente.asignacion_fecha_hora}</td>
                                    <td>${cliente.atencion_fecha_hora || '<span class="badge bg-danger">No atendido</span>'}</td>
                                    <td>${
                                        cliente.atencion_fecha_hora ? 
                                            cliente.estado_pedido ?
                                                `<span class="badge bg-success">Existe Pedido</span>` :
                                                `<span class="badge bg-danger">No Existe Pedido</span>` 
                                            : 
                                            `<span class="badge bg-danger">No Atendido</span>`
                                        }</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#clientesAsignadosBodyrutas').append('<tr><td colspan="6">No hay clientes asignados.</td></tr>');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cargar los clientes asignados.',
                    });
                }
            })
        });

        $('#buscarRutaVendedorReset').click(()=>{
            let id_vendedor = $('#vendedorSelectControl').val();
            if (!id_vendedor) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor, seleccione un vendedor.',
                });
                return;
            }

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡Esta acción reseteará todas las asignaciones del vendedor!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, resetear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('vendedores.resetearRuta', ':id') }}".replace(':id', id_vendedor),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Asignaciones de ruta reiniciadas correctamente.',
                                showCancelButton: false,
                                showConfirmButton: false,
                                timer: 2000,
                            }).then(() => {
                                $('#vendedorSelectControl').val(null).trigger('change');
                                $('#clientesAsignadosBodyrutas').empty();
                                $('#clientesAsignadosBodyrutas').append('<tr><td colspan="6">Seleccione un vendedor para ver los clientes asignados.</td></tr>');
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al reiniciar las asignaciones.',
                            });
                        }
                    });
                }
            });
        })
    </script>
@stop