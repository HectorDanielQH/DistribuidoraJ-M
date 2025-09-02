@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Pedidos Pendientes
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list"></i> Lista de Pedidos Pendientes
                        </h3>
                        <div class="card-tools d-flex">
                            <button href="#" class="btn btn-success btn-sm mr-4" id="btnDespacharPedidos">
                                <i class="fas fa-truck"></i> Despachar Pedidos 
                            </button>


                            <button href="#" class="btn btn-success btn-sm mr-4" id="btnCantidadPedidos">
                                <i class="fas fa-truck"></i> Ver cantidad para despacho
                            </button>
                        </div>
                    </div>
                <div class="card-body">
                    @if($pedidos->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No hay pedidos pendientes.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nro. de Pedido</th>
                                        <th>Cliente</th>
                                        <th>Dirección</th>
                                        <th>Zona</th>
                                        <th>Preventista</th>
                                        <th>Fecha Pedido</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedidos as $pedido)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>Pedido #{{$pedido->numero_pedido}}</td>
                                            <td>{{$pedido->cliente->nombres}} {{$pedido->cliente->apellidos}}</td>
                                            <td>{{$pedido->cliente->calle_avenida}}</td>
                                            <td>
                                                @php
                                                    $pedido_ob=\App\Models\Pedido::where('numero_pedido', $pedido->numero_pedido)->first();
                                                    $cliente=\App\Models\Cliente::where('id', $pedido_ob->id_cliente)->first();
                                                @endphp
                                                {{$cliente->ruta->nombre_ruta ?? 'Sin ruta'}}
                                            </td>
                                            <td>
                                                {{$pedido_ob->usuario->nombres}} {{$pedido_ob->usuario->apellido_paterno}} 
                                                {{$pedido_ob->usuario->apellido_materno}}
                                            </td>
                                            <td>{{$pedido->fecha_pedido}}</td>
                                            <td>
                                                <!--spiner-->
                                                <span class="badge bg-danger text-white">
                                                    <i class="fas fa-spinner fa-spin"></i> Pendiente
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button onclick="verPedidoCliente(this)" id-numero-pedido="{{$pedido->numero_pedido}}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $pedidos->links() }}
                        </div>
                    @endif
                </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
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
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script>
        function verPedidoCliente(e) {
        let numeroPedido = $(e).attr('id-numero-pedido');
        let widthValue = window.innerWidth <= 600 ? '100%' : '60%';
        $.ajax({
            url: "{{ route('pedidos.administrador.visualizacionPedido', ':id') }}".replace(':id', numeroPedido),
            type: 'GET',
            beforeSend: function () {
                Swal.fire({
                    title: 'Cargando Pedido...',
                    html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espera un momento.',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    width: widthValue
                });
            },
            success: function (response) {
                Swal.close();
                let html_tabla = `
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>📦 Código</th>
                                    <th>🧾 Producto</th>
                                    <th>📊 Stock</th>
                                    <th>🛒 Solicitado</th>
                                    <th>💵 Precio</th>
                                    <th>🎁 Promoción</th>
                                    <th>🧮 Total</th>
                                </tr>
                            </thead>
                            <tbody>`;

                response.pedidos.forEach(item => {
                    const descuento = item.descripcion_descuento_porcentaje ?? 0;
                    const total = (item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100);

                    html_tabla += `
                        <tr class="text-center">
                            <td><code>${item.codigo}</code></td>
                            <td>${item.nombre_producto}</td>
                            <td>${item.cantidad_stock} ${item.detalle_cantidad}</td>
                            <td><strong>${item.cantidad_pedido} ${item.tipo_venta}</strong></td>
                            <td>${item.precio_venta} Bs</td>
                            <td>
                                ${item.promocion
                                    ? `<span class="badge bg-success mb-1">${descuento}%</span><br>
                                    <span class="badge bg-info">${item.descripcion_regalo ?? '🎁 Regalo'}</span>`
                                    : `<span class="badge bg-secondary">Sin Promoción</span>`}
                            </td>
                            <td><strong>${total} Bs</strong></td>
                        </tr>`;
                });

                const totalPedido = response.pedidos.reduce((sum, item) => {
                    const descuento = item.descripcion_descuento_porcentaje ?? 0;
                    return sum + ((item.cantidad_pedido * item.precio_venta) - ((item.cantidad_pedido * item.precio_venta * descuento) / 100));
                }, 0);

                html_tabla += `
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <h5 class="text-success"><strong>🧾 Total Pedido: ${totalPedido.toFixed(2)} Bs</strong></h5>
                    </div>`;

                    Swal.fire({
                        title: `📋 Pedido N.º ${response.numero_pedido}`,
                        html: html_tabla,
                        icon: 'info',
                        width: widthValue,
                        showCloseButton: true,
                        confirmButtonText: 'Cerrar',
                    });
                },
                error: function (xhr, status, error) {
                    
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Error',
                        text: 'No se pudo cargar el pedido. Intenta de nuevo.',
                    });
                }
            });
        }

        $('#btnDespacharPedidos').on('click', function () {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas despachar todos los pedidos pendientes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '✅ Sí, despachar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pedidos.administrador.despacharPedido') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            
                        },
                        success: function (response) {
                            
                            Swal.fire({
                                icon: 'success',
                                title: '✅ Pedidos Despachados',
                                text: 'Todos los pedidos pendientes han sido despachados exitosamente.',
                                confirmButtonText: 'Cerrar'
                            }).then(() => {
                                //pedidos.administrador.visualizacionDespachados
                                window.location.href = "{{ route('pedidos.administrador.visualizacionDespachados') }}";
                            });
                        },
                        error: function (xhr, status, error) {
                            
                            Swal.fire({
                                icon: 'error',
                                title: '❌ Error al Despachar',
                                text: 'No se pudieron despachar los pedidos. Intenta de nuevo.',
                            });
                        }
                    });
                }
            });
        });

        $('#btnCantidadPedidos').on('click', function () {
            /*nueva venetana con otra ruta*/
            window.open("{{ route('pedidos.administrador.visualizacionParaDespachado') }}", '_blank');
        });

    </script>
@stop