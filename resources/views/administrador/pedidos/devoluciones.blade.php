@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel en pedidos proceso de devolucion y/o edición
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="container py-5">
        <div class="row mb-3 d-flex justify-content-center align-items-center">
            <div class="col-md-6 d-flex flex-column">
                <label for="pedidoSelectControl" class="form-label me-2">Seleccionar Nro. de Pedido:</label>
                <select class="form-control" id="pedidoSelectControl" style="width: 100%;">
                    <option value="" disabled selected>Seleccione un pedido</option>
                    @foreach($lista_de_pedidos as $pedido)
                        <option value="{{ $pedido->numero_pedido}}" data-numero-pedido="{{ $pedido->numero_pedido}}">Pedido Nro.{{ $pedido->numero_pedido}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex justify-content-center align-items-center">
                <button class="btn btn-primary mt-3" id="buscarRutaVendedor">
                    <i class="fas fa-search me-2"></i> Buscar
                </button>
            </div>
        </div>
        <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
            <table class="table table-bordered align-middle text-center" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Cod. Prod.</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Nombre Producto</th>
                        <th scope="col">Cantidad Pedido</th>
                        <th scope="col">Forma Venta</th>
                        <th scope="col">Promoción</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="pedidosBodyDevolucion">
                    <tr>
                        <td colspan="8" class="text-center">Seleccione un pedido.</td>
                    </tr>
                </tbody>
            </table>
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
        $('#buscarRutaVendedor').on('click', function (){
            let selectedPedido = $('#pedidoSelectControl').val();
            if (!selectedPedido) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione un pedido',
                    text: 'Por favor, seleccione un pedido para continuar.',
                });
                return;
            }
            Swal.fire({
                title: 'Cargando pedidos...',
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            dibujarTabla(selectedPedido);
        })

        function dibujarTabla(selectedPedido){
            $.ajax({
                url: "{{ route('pedidos.administrador.devolucionPedidoDevolucion',':id') }}".replace(':id', selectedPedido),
                type: 'GET',
                success: function (response) {
                    Swal.close();
                    $('#pedidosBodyDevolucion').empty();
                    if (response.pedidos.length > 0) {
                        response.pedidos.forEach(function (pedido, index) {
                            let defaultImage = "{{ asset('images/logo_color.webp') }}";
                            let baseImageUrl = "{{ route('productos.imagen.codigo', ['codigo' => '__CODIGO__']) }}";

                            // Reemplaza el marcador con el código real
                            let imageUrl = pedido.foto_producto
                                ? baseImageUrl.replace('__CODIGO__', pedido.codigo)
                                : defaultImage;

                            let imagenProducto = `
                                <img src="${imageUrl}" alt="Imagen Producto" class="img-fluid" style="max-width: 100px; max-height: 100px;">
                            `;
                            $('#pedidosBodyDevolucion').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${pedido.codigo}</td>
                                    <td>
                                        <img src="${imageUrl}" alt="Imagen Producto" class="img-fluid" style="max-width: 100px; max-height: 100px;">
                                    </td>
                                    <td>${pedido.nombre_producto}</td>
                                    <td>
                                        ${pedido.cantidad_pedido} 
                                        <br>
                                        <button class="btn btn-warning btn-sm" id-pedido="${pedido.id_pedido}" id-numero-pedido="${pedido.numero_pedido}" onclick="modificarCantidadPedido(this)">
                                            <i class="fas fa-info-circle"></i> Modificar
                                        </button>
                                    </td>
                                    <td>
                                        ${pedido.tipo_venta}
                                        <br>
                                        <button class="btn btn-warning btn-sm" id-pedido="${pedido.id_pedido}" id-numero-pedido="${pedido.numero_pedido}" id-producto="${pedido.id_producto}" onclick="modificarTipoVentaPedido(this)">
                                            <i class="fas fa-info-circle"></i> Modificar
                                        </button>
                                    </td>
                                    <td>${pedido.promocion? 
                                        `<span class="badge bg-success">Tiene Promocion</span>
                                        <br>
                                        <button class="btn btn-warning btn-sm" id-pedido="${pedido.id_pedido}" id-numero-pedido="${pedido.numero_pedido}" onclick="modificarPromocionPedido(this)">
                                            <i class="fas fa-info-circle"></i> Modificar
                                        </button>` :
                                        '<span class="badge bg-secondary">N/A</span>'}
                                        <br>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" id-pedido="${pedido.id_pedido}" id-numero-pedido="${pedido.numero_pedido}" onclick="eliminarPedidoTotal(this)">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#pedidosBodyDevolucion').append(`
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron pedidos para este número de pedido.</td>
                            </tr>
                        `);
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al cargar los pedidos',
                        text: 'Por favor, intente nuevamente más tarde.',
                    });
                }
            });
        }

        function modificarCantidadPedido(button) {
            let idPedido = $(button).attr('id-pedido');
            let numeroPedido = $(button).attr('id-numero-pedido');
            Swal.fire({
                title: 'Modificar Cantidad',
                input: 'number',
                inputValue: '1',
                inputLabel: 'Ingrese la nueva cantidad',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: (cantidad) => {
                    if (!cantidad || cantidad <= 0) {
                        Swal.showValidationMessage('Por favor, ingrese una cantidad válida.');
                    } else {
                        return $.ajax({
                            url: "{{ route('pedidos.administrador.devolucionPedidoDevolucion.cantidad', ':id') }}".replace(':id', idPedido),
                            type: 'POST',
                            data: {
                                cantidad: cantidad,
                                _token: '{{ csrf_token() }}',
                                _method: 'PUT'
                            },
                            success: function (response) {
                                Swal.fire('Éxito', 'Cantidad actualizada correctamente.', 'success');
                                dibujarTabla(numeroPedido);
                            },
                            error: function () {
                                Swal.fire('Error', 'No se pudo actualizar la cantidad. Intente nuevamente.', 'error');
                            }
                        });
                    }
                }
            });
        }

        function modificarTipoVentaPedido(e){
            let idPedido = $(e).attr('id-pedido');
            let numeroPedido = $(e).attr('id-numero-pedido');
            let idProducto = $(e).attr('id-producto');
            Swal.fire({
                title: 'cargando tipos de venta...',
                didOpen: () => {
                    Swal.showLoading();
                }
            })
            $.ajax({
                url:"{{ route ('pedidos.administrador.producto.select.cantidad', ':id') }}".replace(':id', idProducto),
                type: 'GET',
                success: function (response) {
                    Swal.close();
                    let options = response.formas_venta.map(function(tipo) {
                        return `<option value="${tipo.id}">${tipo.tipo_venta}</option>`;
                    }).join('');
                    Swal.fire({
                        title: 'Modificar Tipo de Venta',
                        html: `<select id="tipoVentaSelect" class="form-control">${options}</select>`,
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            let tipoVentaId = $('#tipoVentaSelect').val();
                            if (!tipoVentaId) {
                                Swal.showValidationMessage('Por favor, seleccione un tipo de venta.');
                            } else {
                                return $.ajax({
                                    url: "{{ route('pedidos.administrador.producto.select.actualizar', ':id') }}".replace(':id', idPedido),
                                    type: 'POST',
                                    data: {
                                        tipo_venta_id: tipoVentaId,
                                        _token: '{{ csrf_token() }}',
                                        _method: 'PUT'
                                    },
                                    success: function (response) {
                                        Swal.fire('Éxito', 'Tipo de venta actualizado correctamente.', 'success');
                                        dibujarTabla(numeroPedido);
                                    },
                                    error: function () {
                                        Swal.fire('Error', 'No se pudo actualizar el tipo de venta. Intente nuevamente.', 'error');
                                    }
                                });
                            }
                        }
                    });
                },
            })
        }

        function modificarPromocionPedido(e){
            let idPedido = $(e).attr('id-pedido');
            //esta opcion solo elimina la promocion, no agrega una nueva
            Swal.fire({
                title: '¿Está seguro de eliminar la promoción?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pedidos.administrador.producto.eliminar.promocion', ':id') }}".replace(':id', idPedido),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire('Éxito', 'Promoción eliminada correctamente.', 'success');
                            dibujarTabla($(e).attr('id-numero-pedido'));
                        },
                        error: function () {
                            Swal.fire('Error', 'No se pudo eliminar la promoción. Intente nuevamente.', 'error');
                        }
                    });
                }
            });
        }

        function eliminarPedidoTotal(e){
            let idPedido = $(e).attr('id-pedido');
            let numeroPedido = $(e).attr('id-numero-pedido');
            Swal.fire({
                title: '¿Está seguro de eliminar este pedido?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pedidos.administrador.producto.eliminar.promocion.total', ':id') }}".replace(':id', idPedido),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire('Éxito', 'Pedido eliminado correctamente.', 'success');
                            dibujarTabla(numeroPedido);
                        },
                        error: function () {
                            Swal.fire('Error', 'No se pudo eliminar el pedido. Intente nuevamente.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@stop