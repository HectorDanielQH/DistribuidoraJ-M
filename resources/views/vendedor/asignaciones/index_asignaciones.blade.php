@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Mis Asignaciones
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
                <form method="GET" action="{{ route('asignacionvendedor.index') }}" class="row g-3">
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



    <x-adminlte-modal id="verPedidoModal" size="lg" theme="dark" icon="fas fa-box-open" title="Pedidos realizados" v-centered>
            <div class="modal-body px-4" id="crear-tabla-pedidos">
                
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>




    <div class="container py-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i> Mis Asignaciones
                        </h5>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-layer-group me-1"></i> Total: {{ $asignaciones->count() }}
                        </span>
                        <a href="{{route('pedidos.vendedor.obtenerPdfRutas')}}" class="btn btn-dark ms-2" style="font-weight: bold; border-radius: 8px;">
                            <i class="fas fa-file-pdf me-1"></i> Descargar mis rutas
                        </a>
                    </div>

                    <div class="card-body">
                        @if($asignaciones->isEmpty())
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i> No tienes asignaciones registradas.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th><i class="fas fa-user me-1"></i> Cliente</th>
                                            <th><i class="fas fa-phone-alt me-1"></i> Celular</th>
                                            <th><i class="fas fa-map-marker-alt me-1"></i> Ubicación</th>
                                            <th><i class="fas fa-calendar-plus me-1"></i> Asignación</th>
                                            <th><i class="fas fa-calendar-check me-1"></i> Atención</th>
                                            <th><i class="fas fa-box-open me-1"></i> Pedido</th>
                                            <th><i class="fas fa-cogs me-1"></i> Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($asignaciones as $asignacion)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellido_paterno }} {{ $asignacion->cliente->apellido_materno }}</td>
                                                <td><i class="fas fa-mobile-alt text-secondary me-1"></i>{{ $asignacion->cliente->celular }}</td>
                                                <td><i class="fas fa-map-pin text-danger me-1"></i> {{ $asignacion->cliente->ubicacion }}</td>
                                                <td><span class="badge bg-primary">{{ $asignacion->asignacion_fecha_hora }}</span></td>
                                                <td>
                                                    @if($asignacion->atencion_fecha_hora)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>{{ $asignacion->atencion_fecha_hora }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-clock me-1"></i> No atendido
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($asignacion->estado_pedido)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i> Sí
                                                        </span>
                                                        <br>
                                                        <button class="btn btn-outline-info btn-sm mt-2" id="verPedidoUsuario" id-pedido-cliente="{{ $asignacion->cliente->id }}" onclick="verPedidosCliente(this)" data-toggle="modal" data-target="#verPedidoModal ">
                                                            <i class="fas fa-eye me-1"></i> Ver Pedido
                                                        </button>
                                                    @else
                                                        @if($asignacion->atencion_fecha_hora)
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-times-circle me-1"></i> No pidió
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-ban me-1"></i> No atendido
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-success btn-sm" id-asignacion="{{ $asignacion->id }}" onclick="crearAtencion(this)">
                                                        <i class="fas fa-hand-pointer me-1"></i> Atender
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light text-muted d-flex justify-content-between align-items-center">
                        <small><i class="fas fa-info-circle me-1"></i> Se muestran las asignaciones relacionadas a tu cuenta.</small>
                        <div>
                            {{ $asignaciones->appends(request()->query())->links() }} <!-- Si usas paginación -->
                        </div>
                    </div>
                </div>
            </div>
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
        function crearAtencion(e) {
            let idAsignacion = e.getAttribute('id-asignacion');

            Swal.fire({
                title: '¿Qué deseas hacer?',
                text: 'Puedes crear un pedido o registrar la atención',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Crear Pedido',
                confirmButtonColor: '#28a745',
                denyButtonText: 'Registrar Atención',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `{{ route('pedidos.vendedor.crear', ':id') }}`.replace(':id', idAsignacion);
                } else if (result.isDenied) {
                    Swal.fire({
                        title: 'Registrar Atención',
                        text: '¿Estás seguro de que deseas registrar la atención?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, registrar',
                        cancelButtonText: 'No, cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Registrar Atención
                            $.ajax({
                                url: `{{ route('registrarAtencion.sinpedido', ':id') }}`.replace(':id', idAsignacion),
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    _method: 'PUT',
                                    id_asignacion: idAsignacion
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Atención registrada',
                                        text: 'La atención se ha registrado correctamente.',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        title: 'Error al registrar la atención',
                                        text: xhr.responseJSON.message || 'Ocurrió un error al procesar tu solicitud.',
                                        icon: 'error'
                                    });
                                }
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: 'Operación cancelada',
                                text: 'No se ha registrado ninguna atención.',
                                icon: 'info',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Cancelar
                    Swal.fire({
                        title: 'Operación cancelada',
                        text: 'No se ha realizado ninguna acción.',
                        icon: 'info',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        }


        $('#limpiarboton').click(function() {
            window.location.href = "{{ route('asignacionvendedor.index') }}";
        });


        let pedidosJson=[];
        function verPedidosCliente(e) {
            let id_pedido_cliente = $(e).attr('id-pedido-cliente');
            $('#crear-tabla-pedidos').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
            $.ajax({
                url: `{{ route('pedidos.vendedor.obtenerPedidosProceso', ':numero') }}`.replace(':numero', id_pedido_cliente),
                type: 'GET',
                success: function(response) {
                    $('#crear-tabla-pedidos').empty();
                    pedidosJson = response.pedidos;
                    response.cantidad_pedidos.forEach(function(pedido) {
                        $('#crear-tabla-pedidos').append(`
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title mb-2"> 
                                        Pedido de Atencion #${pedido.numero_pedido}

                                        <button class="btn btn-danger ml-4" disabled>
                                            <i class="fas fa-spinner fa-spin"></i> Pedido en proceso de entrega...
                                        </button>
                                    </h5>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Cod.</th>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Descuento</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${obtenerFilasPedido(pedido.numero_pedido)}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `);
                    });
                },
                error: function(xhr) {
                    $('#crear-tabla-pedidos').html('<div class="alert alert-danger">Error al cargar los pedidos.</div>');
                }
            });
        }

        function obtenerFilasPedido(numero_pedido) {
            let filas = '';
            pedidosJson.forEach(function(pedido) {
                if (pedido.numero_pedido === numero_pedido) {
                    const descuento = pedido.descripcion_descuento_porcentaje ? parseFloat(pedido.descripcion_descuento_porcentaje) : 0;
                    const precioUnitario = parseFloat(pedido.precio_venta);
                    const cantidad = parseFloat(pedido.cantidad);
                    const subtotal = cantidad * precioUnitario;
                    const totalConDescuento = subtotal - (subtotal * (descuento / 100));

                    filas += `
                        <tr>
                            <td>${pedido.codigo}</td>
                            <td>${pedido.nombre_producto}</td>
                            <td>${cantidad}</td>
                            <td>${precioUnitario.toFixed(2)}</td>
                            <td>${descuento} %</td>
                            <td>${totalConDescuento.toFixed(2)}</td>
                        </tr>
                    `;
                }
            });
            if (filas === '') {
                filas = '<tr><td colspan="6" class="text-center">No hay productos en este pedido.</td></tr>';
            }
            let totalPedido = pedidosJson.reduce((total, pedido) => {
                if (pedido.numero_pedido === numero_pedido) {
                    const descuento = pedido.descripcion_descuento_porcentaje ? parseFloat(pedido.descripcion_descuento_porcentaje) : 0;
                    const precioUnitario = parseFloat(pedido.precio_venta);
                    const cantidad = parseFloat(pedido.cantidad);
                    const subtotal = cantidad * precioUnitario;
                    return total + (subtotal - (subtotal * (descuento / 100)));
                }
                return total;
            }, 0);
            filas += `
                <tr>
                    <td colspan="5" class="text-end"><strong>Total del Pedido:</strong></td>
                    <td><strong>${totalPedido.toFixed(2)}</strong></td>
                </tr>
            `;

            return filas;
        }

    </script>
@stop