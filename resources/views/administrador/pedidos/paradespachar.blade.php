@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel en pedidos proceso de despacho y/o entrega
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
                            <i class="fas fa-list"></i> Lista de pedidos para despachar
                        </h3>
                        <div class="card-tools d-flex">
                            <a href="{{route('pedidos.administrador.visualizacionPdfDespachar')}}" target="_blank" class="btn btn-info btn-sm mr-4">
                                <i class="fas fa-file-pdf"></i> Imprimir Pedidos para el Repartidor
                            </a>

                            <button class="btn btn-success btn-sm mr-4" onclick="contabilizarTodosLosPendientes(this)">
                                <i class="fas fa-check"></i> Contabilizar Pedidos 
                            </button>

                            <a href="{{route('pedidos.administrador.devolucionPedido')}}" class="btn btn-danger btn-sm mr-4">
                                <i class="fas fa-undo-alt"></i> Devoluciones 
                            </a>
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
                                        <th>#</th>
                                        <th>Cod. Prod.</th>
                                        <th>Imagen Producto</th>
                                        <th>Nombre Producto</th>
                                        <th>Stock Producto</th>
                                        <th>Cant. Despacho</th>
                                        <th>Ingreso Estimado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedidos as $pedido)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$pedido->codigo}}</td>
                                            <td>
                                                @if($pedido->foto_producto)
                                                    <img src="{{ route('productos.imagen.codigo',$pedido->codigo) }}" alt="Imagen del Producto" class="img-fluid" style="max-width: 100px">
                                                @else
                                                    <img src="{{ asset('images/logo_color.webp') }}" alt="Imagen del Producto" class="img-fluid" style="max-width: 100px;">
                                                @endif
                                            </td>
                                            <td>{{$pedido->nombre_producto}}</td>
                                            <td>{{$pedido->cantidad_stock}} {{$pedido->detalle_cantidad}}</td>
                                            <td>{{$pedido->cantidad_pedido}} {{$pedido->detalle_cantidad}}</td>
                                            <td>{{$pedido->subtotal - ($pedido->subtotal*($pedido->descripcion_descuento_porcentaje/100)) }} Bs.-</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Total estimado de ingresos:</strong></td>
                                        <td colspan="2"><strong>
                                            @php
                                                $totalIngresos = $pedidos->sum(function($pedido) {
                                                    return $pedido->subtotal - ($pedido->subtotal * ($pedido->descripcion_descuento_porcentaje / 100));
                                                });
                                            @endphp
                                            {{ $totalIngresos > 0 ? $totalIngresos : '0' }}
                                            Bs.-</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet"></link>

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
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script>
        function verPedidoPorProducto(e){
            let codigoProducto = e.getAttribute('id-codigo-producto');
            Swal.fire({
                title: 'Cargando pedidos...',
                html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espere.',
                showConfirmButton: false,
                allowOutsideClick: false,
            });

        }

        function contabilizarTodosLosPendientes(e) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas contabilizar todos los pedidos pendientes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, contabilizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Contabilizando...',
                        html: '<i class="fas fa-spinner fa-spin"></i> Por favor, espere.',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                    });
                    $.ajax({
                        url: "{{ route('pedidos.administrador.contabilizarTodosLosPendientes') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Todos los pedidos pendientes han sido contabilizados.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al contabilizar los pedidos. Inténtalo de nuevo más tarde.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        }
    </script>
@stop