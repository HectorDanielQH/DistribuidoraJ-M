@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <adminlte-card class="mt-4" theme="light">
                <h3 class="text-center mb-3" style="font-weight: 600; color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Editar Pedido N.º {{ $pedido->numero_pedido }}
                </h3>
                <p class="text-center mb-4" style="font-size: 1.1rem; color: #f7f7f7;">
                    Aquí puedes modificar los productos y cantidades del pedido seleccionado.
                </p>
            </adminlte-card>
            <button class="btn mx-1" id="boton-excel" data-toggle="modal" data-target="#agregarProductoModal" style="background-color: #1abc9c; color: white; font-weight: 600; border-radius: 8px;">
                <i class="fas fa-receipt me-2"></i> Agregar Productos al Pedido
            </button>
        </div>
    </div>
@stop

@section('content')
    
    <x-adminlte-modal id="agregarProductoModal" size="lg" theme="dark" icon="fas fa-receipt" title="Agregar Productos al Pedido" static-backdrop scrollable>
            <div class="modal-body px-4">
                <div class="row">
                    <div class="col-6">
                        <label for="producto_id" style="font-weight: 600;">Producto:</label>
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <select id="producto_id" name="producto_id" class="form-control select2" style="width: 100%;">
                                <option disabled selected>Selecciona un producto</option>
                            </select>
                            <button class="btn btn-primary my-2" id="buscar-producto-btn" style="background-color: #2980b9; border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-search mt-2"></i> Ejecutar Búsqueda
                            </button>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="cantidad" style="font-weight: 600;">Código del Producto:</label>
                        <p id="codigo-del-producto">N/A</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <label for="cantidad" style="font-weight: 600;">Imagen:</label>
                        <br>
                        <img id="imagen-producto" src="{{ asset('images/logo_color.webp') }}" 
                        alt="Imagen del Producto" class="img-fluid" style="max-height: 100px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                    </div>
                    <div class="col-6">
                        <div>
                            <label for="stock-disponible" style="font-weight: 600;">Stock Disponible:</label>
                            <p id="stock-disponible">N/A</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tipo Venta</th>
                                    <th>Precio Venta</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-tipos-venta">
                                <tr>
                                    <td colspan="2" class="text-center">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <div>
                            <label for="stock-disponible" style="font-weight: 600;">Tipo Venta:</label>
                            <br>
                            <select id="tipo-venta-select" class="form-control select2" style="width: 100%;">
                                <option>-----Selecciona-----</option>
                            </select>
                        </div>
                        <div>
                            <label for="stock-disponible" style="font-weight: 600;">Cantidad de Venta:</label>
                            <input type="number" id="cantidad-venta-input" class="form-control" min="1" value="1">
                        </div>
                    </div>
                </div>
            </div>
        <x-slot name="footerSlot">
            <x-adminlte-button type="submit" id="botonregistrarpedido" theme="success" icon="fas fa-check" label="Agregar" class="rounded-3 px-4 py-2" />
            <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
        </x-slot>
    </x-adminlte-modal>

    <div class="container">
        <div class="alert alert-red alert-dismissible fade show mt-3" role="alert" style="background: #db3434; color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);">
            <i class="fas fa-info-circle me-2"></i>
            Recuerda que si eliminas todos los productos del pedido, el pedido será cancelado automáticamente.
        </div>
    </div>

    <div class="container">  
        <table class="table table-striped table-bordered" id="pedidosTabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Venta</th>
                    <th>Cantidad</th>
                    <th>Stock Inventario</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">TOTAL</th>
                    <th id="total-general" class="text-end">{{$suma_pedido}} Bs.-</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-CaLdjDnDQsm4dp6FAi+hDGbnmYMabedJHm00x/JJgmTsQ495TW5sVn4B7kcyThok" crossorigin="anonymous">
  
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
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.js" integrity="sha384-SY2UJyI2VomTkRZaMzHTGWoCHGjNh2V7w+d6ebcRmybnemfWfy9nffyAuIG4GJvd" crossorigin="anonymous"></script>
    
    <script>
        $(document).ready(function () {
            $('#pedidosTabla').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: { url: '/i18n/es-ES.json' },
                ajax: {
                    url: "{{ route('administrador.pedidos.administrador.editar', $pedido->numero_pedido) }}",
                },
                columns: [
                    { data: 'producto',   name: 'producto',   defaultContent: 'N/A' },
                    { data: 'precio_venta', className: 'text-end', orderable: false, searchable: false },
                    { data: 'cantidad',     className: 'text-center' },
                    { data: 'cantidad_stock', className: 'text-center' },
                    { data: 'subtotal',     className: 'text-end', orderable: false, searchable: false},
                    { data: 'acciones',     orderable: false, searchable: false, className: 'text-center' }
                ],
                columnDefs: [
                    { targets: '_all', className: 'align-middle' }
                ],
            });
        });
        $('#producto_id').select2({
            dropdownParent: $('#agregarProductoModal'),
            placeholder: 'Selecciona un producto',
            minimumInputLength: 2,
        });
        $('#tipo-venta-select').select2({
            dropdownParent: $('#agregarProductoModal'),
            placeholder: 'Selecciona un tipo de venta',
        });

        $('#producto_id').select2({
            placeholder: 'Buscar producto...',
            minimumInputLength: 2, 
            ajax: {
                url: "{{route('administrador.productos.obtenerProductosParaEdicion')}}",
                dataType: 'json',
                delay: 250, // retardo para no saturar el server
                data: function (params) {
                    return {
                        term: params.term // lo que el usuario tipea
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.codigo +" - "+item.nombre_producto // lo que se muestra en la lista
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#buscar-producto-btn').on('click', function() {
            $('#producto_id').val() ? buscarProducto() : Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor, selecciona un producto antes de buscar.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Entendido'
            });
        });
        function buscarProducto() {
            let productoId = $('#producto_id').val();
            Swal.fire({
                title: 'Buscando producto...',
                text: 'Por favor, espera mientras obtenemos la información.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{route('administrador.productos.busquedaIdProducto', ':id')}}".replace(':id', productoId),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    $.ajax({
                        url: "{{route('administrador.productos.mostrarFormasVenta', ':id')}}".replace(':id', productoId),
                        type: 'GET',
                        dataType: 'json',
                        success: function(formasVenta) {
                            console.log(formasVenta);
                            $('#codigo-del-producto').text(data.codigo);
                            $('#imagen-producto').attr('src',"{{route('productos.imagen.codigo', ':codigo')}}".replace(':codigo', data.codigo));
                            $('#stock-disponible').text(data.cantidad + ' '+ data.detalle_cantidad);
                            $('#tabla-tipos-venta').empty();
                            $('#tipo-venta-select').empty().append('<option>-----Selecciona-----</option>');
                            if (formasVenta.length > 0) {
                                formasVenta.forEach(function(forma) {
                                    let fila = `<tr>
                                                    <td>${forma.tipo_venta}</td>
                                                    <td class="text-end">${forma.precio_venta} Bs.-</td>
                                                </tr>`;
                                    $('#tabla-tipos-venta').append(fila);
                                    let opcion = `<option value="${forma.id}">${forma.tipo_venta} - ${forma.precio_venta} Bs.-</option>`;
                                    $('#tipo-venta-select').append(opcion);
                                });
                            } else {
                                $('#tabla-tipos-venta').append('<tr><td colspan="2" class="text-center">No hay formas de venta disponibles.</td></tr>');
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo obtener la información de las formas de venta. Inténtalo de nuevo.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    })
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo obtener la información del producto. Inténtalo de nuevo.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        }

        $('#botonregistrarpedido').on('click', function() {
            let productoId = $('#producto_id').val();
            let tipoVentaId = $('#tipo-venta-select').val();
            let cantidadVenta = parseInt($('#cantidad-venta-input').val());
            if (!productoId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, selecciona un producto.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            if (!tipoVentaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, selecciona un tipo de venta.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            if (isNaN(cantidadVenta) || cantidadVenta < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, ingresa una cantidad válida (mayor a 0).',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            Swal.fire({
                title: 'Agregando producto al pedido...',
                text: 'Por favor, espera mientras procesamos tu solicitud.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{route('administrador.pedidos.administrador.agregarProducto', ':id')}}".replace(':id', "{{ $pedido->numero_pedido }}"),
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: 'PUT',
                    producto_id: productoId,
                    tipo_venta_id: tipoVentaId,
                    cantidad: cantidadVenta
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.mensaje,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        $('#botonenviar-cerrar').click();
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                    Swal.close();
                    let errorMessage = xhr.responseJSON.error && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo agregar el producto al pedido. Inténtalo de nuevo.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        function eliminarProductoPedido(button) {
            let pedidoId = $(button).attr('data-id-pedido');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el producto del pedido.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando producto...',
                        text: 'Por favor, espera mientras procesamos tu solicitud.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.pedidos.administrador.eliminarProducto', ':id') }}".replace(':id', pedidoId),
                        type: 'DELETE',
                        dataType: 'json',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Entendido'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.close();
                            let errorMessage = xhr.responseJSON.error && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo eliminar el producto del pedido. Inténtalo de nuevo.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    });
                }
            });
        }

    </script>
@stop