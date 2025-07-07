@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Pedidos - Vendedor
            </span>
        </div>
    </div>
@stop

@section('content')
    <!--aviso para quien se esta creando el pedido-->
    <div class="container mt-4">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>
                Se está creando un pedido para el cliente:
                <strong>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellido_paterno }} {{ $asignacion->cliente->apellido_materno }}</strong>
            </div>
        </div>
    </div>


    <x-adminlte-modal id="modalAgregarProducto" title="Agregar Pedido" size="lg" theme="teal" icon="fas fa-shopping-cart" v-centered static-backdrop scrollable>
        <div>
            <div class="row d-flex justify-content-between align-items-end">
                <div class="col-md-6 col-sm-12 mb-3">
                    <label for="caja-busqueda-producto">
                        <strong>Ingresa el código o alguna coincidencia</strong>
                    </label>
                    <select class="form-control" id="caja-busqueda-producto" name="caja-busqueda-producto" placeholder="Buscar producto por código o nombre">
                        <option value="">Selecciona un producto</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre_producto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-sm-12 mb-3 d-flex justify-content-end">
                    <button class="btn btn-primary" id="btn-buscar-producto">
                        <i class="fas fa-search"></i> Buscar Producto
                    </button>
                </div>
            </div>
            <div class="row" id="resultado-busqueda">

            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="mr-auto" theme="success" label="Agregar" onclick="registrarTabla(this)"/>
            <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart"></i> Agregar Productos al Pedido
                </h3>
                <button class="btn btn-success float-right" data-toggle="modal" data-target="#modalAgregarProducto">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Cod. Prod</th>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Tipo de Compra</th>
                                <th>Precio Unitario</th>
                                <th>Cantidad</th>
                                <th>Descuento</th>
                                <th>Sub Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-agregar-producto">
                            <tr>
                                <td colspan="9" class="text-center">No hay productos agregados al pedido.</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Total del pedido -->
                    <div class="mt-3">
                        <h4>Total del Pedido:  
                            <span class="text-success" id="total-pedido">0.00</span>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button class="btn btn-primary" onclick="registrarPedido()">
                    <i class="fas fa-save"></i> Registrar Pedido
                </button>
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
        /*modificar el alto de select 2*/
        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let idProducto_para_tabla="";
        let tablaProductos = [];
        $(document).ready(function(){
            $('#caja-busqueda-producto').select2({
                placeholder: 'Buscar producto por código o nombre',
                width: '100%',
            });

            Swal.fire({
                title: 'Cargando...',
                text: 'Verificando pedidos pendientes',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                    let idCliente = '{{ $asignacion->cliente->id }}';
                    $.ajax({
                        url: '{{ route("pedidos.vendedor.obtenerPedidosPendientes", ":id") }}'.replace(':id', idCliente),
                        type: 'GET',
                        success: function(response) {
                            Swal.close();
                            response.pedidos.forEach(function(pedido) {
                                let foto = '';
                                if (pedido.foto_producto == null || pedido.foto_producto == '') {
                                    foto = '{{ asset('images/logo_color.webp') }} ?v={{ time() }}';
                                } else {
                                    foto = '{{ route("productos.imagen", ":foto") }} ?v={{ time() }}'.replace(':foto', pedido.id_producto);
                                }

                                let producto={
                                    'id_producto': pedido.id_producto,
                                    'codigo_producto': pedido.codigo_producto,
                                    'imagen_producto' : foto,
                                    'texto_producto': pedido.nombre_producto,
                                    'id_forma_venta': pedido.id_forma_venta,
                                    'tipo_venta': pedido.tipo_venta,
                                    'precio_venta': pedido.precio_venta,
                                    'cantidad': pedido.cantidad,
                                    'sub_total': ((pedido.precio_venta * pedido.cantidad)-(pedido.precio_venta * pedido.cantidad * (pedido.descripcion_descuento_porcentaje / 100))),
                                    'promocion': pedido.promocion,
                                    'descripcion_regalo': pedido.descripcion_regalo,
                                    'descripcion_descuento_porcentaje': pedido.descripcion_descuento_porcentaje? pedido.descripcion_descuento_porcentaje : '0',
                                };
                                tablaProductos.push(producto);
                            });
                            if (tablaProductos.length > 0) {
                                construirTablaProductos();
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sin Pedidos Pendientes',
                                    text: 'No hay pedidos pendientes para este cliente.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudieron obtener los pedidos pendientes.'
                            });
                        }
                    });
                }
            });

        });

        $('#btn-buscar-producto').on('click', function() {
            let productoId = $('#caja-busqueda-producto').val();
            idProducto_para_tabla = productoId;
            if (productoId) {
                $.ajax({
                    url: "{{ route('pedidos.vendedor.obtenerProducto',':id') }}".replace(':id', productoId),
                    type: 'GET',
                    success: function(data) {
                        let foto = '';
                        if (data.producto.foto_producto == null || data.producto.foto_producto == '') {
                            foto = '{{ asset('images/logo_color.webp') }} ?v={{ time() }}';
                        } else {
                            foto = '{{ route("productos.imagen", ":foto") }} ?v={{ time() }}'.replace(':foto', data.producto.id);
                        }

                        let opciones = '<option value="">Selecciona una forma de venta</option>';

                        data.formasVenta.forEach(function(forma) {
                            opciones += `<option value="${forma.id}">${forma.tipo_venta}</option>`;
                        });

                        let tabla_formas_venta = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo de Venta</th>
                                        <th>Precio de Venta</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        data.formasVenta.forEach(function(forma) {
                            tabla_formas_venta += `
                                <tr>
                                    <td>${forma.tipo_venta}</td>
                                    <td>${forma.precio_venta} Bs.-</td>
                                </tr>
                            `;
                        });
                        tabla_formas_venta += `
                                </tbody>
                            </table>
                        `;


                        let promoHtml = '';
                            if (data.producto.promocion) {
                                promoHtml = `
                                    <p class="card-text">
                                        <strong>Descuento del :</strong> 
                                        <span class="badge bg-success text-lg fs-6">
                                            ${data.producto.descripcion_descuento_porcentaje + "%" || 'No disponible'}
                                        </span>
                                        <br>
                                        <br>
                                        <strong>Regalo:</strong> 
                                        <span class="badge bg-info text-dark text-lg fs-6">
                                            ${data.producto.descripcion_regalo || 'No disponible'}
                                        </span>
                                    </p>
                                `;

                            } else {
                                promoHtml = `<p class="card-text text-muted">
                                            <i class="fas fa-info-circle me-2"></i> El producto no tiene promoción
                                        </p>`;
                        }
                        $('#resultado-busqueda').empty();
                        // Aquí puedes agregar el producto a la tabla
                        $('#resultado-busqueda').append(`
                            <div class="col-12">
                                <div class="card d-flex w-100">
                                    <hidden id="id-producto-agregar-pedido" value="${data.producto.id}" />  
                                    <div class="card-body d-flex flex-column align-items-center text-center">
                                        <img src="${foto}" 
                                            class="img-fluid rounded mb-3 shadow-sm" 
                                            id="foto-producto-agregar-pedido" 
                                            alt="${data.producto.nombre_producto}" 
                                            style="max-height: 150px; object-fit: contain;">
                                        
                                        <h5 class="card-title fw-bold" id="id-texto-producto-agregar-pedido">${data.producto.nombre_producto}</h5>
                                        
                                        <p class="card-text mb-1"><strong>Código:</strong> ${data.producto.codigo}</p>
                                        <p class="card-text mb-1 text-truncate" style="max-width: 320px;">
                                            <strong>Descripción:</strong> ${data.producto.descripcion_producto || 'No disponible'}
                                        </p>
                                        <p class="card-text">
                                            <strong>Stock:</strong> ${data.producto.cantidad || 'No disponible'} 
                                            ${data.producto.detalle_cantidad || ''}
                                        </p>

                                        ${promoHtml}

                                        <div class="card-text">
                                            <strong>Formas de Venta:</strong>
                                            <div class="table-responsive">
                                                ${tabla_formas_venta}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <label for="forma-venta-agregar-producto-agregar" class="form-label fw-bold mb-1">
                                                    <i class="fas fa-tag text-primary"></i> Forma de Venta
                                                </label>
                                                <select id="forma-venta-agregar-producto-agregar" class="form-control" onchange="actualizarPrecioPedidoAgregar(this)">
                                                    ${opciones}
                                                </select>
                                            </div>

                                            <div class="col-md-6 col-sm-12">
                                                <label for="precio-pedido-agregar" class="form-label fw-bold mb-1">
                                                    <i class="fas fa-dollar-sign text-success"></i> Precio
                                                </label>
                                                <input id="precio-pedido-agregar" type="text" class="form-control" value="0.0" readonly style="text-align: right;">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <label for="cantidad-precio-pedido" class="form-label fw-bold mb-1">
                                                    <i class="fas fa-sort-numeric-up text-info"></i> Cantidad
                                                </label>
                                                <input type="number" id="cantidad-precio-pedido" class="form-control" value="0" min="1" oninput="calcularCantidadPedidoAgregar(this)" style="text-align: center;">
                                            </div>

                                            <div class="col-md-6 col-sm-12">
                                                <label for="total-precio-perdido" class="form-label fw-bold mb-1">
                                                    <i class="fas fa-calculator text-warning"></i> Sub Total
                                                </label>
                                                <input type="number" id="total-precio-perdido" class="form-control" value="0" min="1" readonly style="text-align: right;">
                                            </div>
                                            <input type="hidden" id="id-producto-promocion-pedido" value="${data.producto.promocion}" />
                                            <input type="hidden" id="id-producto-promocion-regalo-pedido" value="${data.producto.descripcion_regalo}" />
                                            <input type="hidden" id="id-producto-promocion-descuento-pedido" value="${data.producto.descripcion_descuento_porcentaje}" />
                                            <input type="hidden" id="id-producto-cantidad-pedido" value="${data.producto.cantidad}" />
                                            <input type="hidden" id="id-convalidacion-cantidad" value="0" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        `);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener el producto',
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Por favor, selecciona un producto.',
                });
            }
        });

        function actualizarPrecioPedidoAgregar(select) {
            const $inputPrecio = $('#precio-pedido-agregar');

            $inputPrecio.val('');
            $inputPrecio.attr('placeholder', 'Cargando...');

            let cant_convalidacion=$('#id-convalidacion-cantidad');

            const url = "{{ route('pedidos.vendedor.obtenerformaventa', ':id') }}".replace(':id', select.value);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $inputPrecio.val(data.precio_venta);
                    $inputPrecio.attr('placeholder', '');
                    cant_convalidacion.val(data.equivalencia_cantidad);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo obtener el precio de la forma de venta',
                    });
                    $inputPrecio.attr('placeholder', 'Error');
                }
            });
        }

        function calcularCantidadPedidoAgregar(e){
            let cantidad = Number(e.value);
            let precioUnitario = $('#precio-pedido-agregar').val();
            let total = $('#total-precio-perdido');
            let promocion = $('#id-producto-promocion-pedido').val();
            let promocionDescuento = $('#id-producto-promocion-descuento-pedido').val();
            let cantidadProducto = $('#id-producto-cantidad-pedido').val();
            let convalidacionCantidad = $('#id-convalidacion-cantidad').val();
            console.log(cantidadProducto, convalidacionCantidad);
            if ((cantidad*convalidacionCantidad) > cantidadProducto) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: `La cantidad no puede ser mayor a ${cantidad-1}.`,
                });
                e.value = cantidad-1;
                cantidad = cantidad-1;
            }
            if( promocion === 'true' || promocion === '1'){
                if (promocionDescuento>0) {
                    let descuento = parseFloat(promocionDescuento);
                    let precioConDescuento = parseFloat(precioUnitario) - (parseFloat(precioUnitario) * (descuento / 100));
                    total.val((cantidad * precioConDescuento).toFixed(2));
                } else {
                    total.val((cantidad * parseFloat(precioUnitario)).toFixed(2));
                }
            } else {
                total.val((cantidad * parseFloat(precioUnitario)).toFixed(2));
            }
        }

        function construirTablaProductos(){
            $('#tabla-agregar-producto').empty();

            if (tablaProductos.length === 0) {
                $('#tabla-agregar-producto').append(`
                    <tr>
                        <td colspan="9" class="text-center">No hay productos agregados al pedido.</td>
                    </tr>
                `);
                return;
            }

            tablaProductos.forEach(function(producto) {
                $('#tabla-agregar-producto').append(`
                    <tr>
                        <td>${producto.codigo_producto}</td>
                        <td><img src="${producto.imagen_producto}" alt="${producto.texto_producto}" class="img-fluid"
                            style="max-height: 50px; max-width: 50px;"></td>
                        <td>${producto.texto_producto}</td>
                        <td>${producto.tipo_venta}</td>
                        <td>${producto.precio_venta}</td>
                        <td>${producto.cantidad}</td>
                        <td>${producto.descripcion_descuento_porcentaje ? `<span class="badge bg-success text-lg">${producto.descripcion_descuento_porcentaje} %</span>` : 'N/A'}</td>
                        <td>${producto.sub_total}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            // Actualizar el total del pedido
            let totalPedido = 0;
            tablaProductos.forEach(function(prod) {
                totalPedido += parseFloat(prod.sub_total);
            });
            $('.mt-3 h4').text(`Total del Pedido: ${totalPedido.toFixed(2)}`);
        }

        function registrarTabla(){

            let productos = @json($productos);
            let productoEncontrado = productos.find(p => p.id == idProducto_para_tabla);
            
            let producto={
                'id_producto': idProducto_para_tabla,
                'codigo_producto':productoEncontrado ? productoEncontrado.codigo : '',
                'imagen_producto' : $('#foto-producto-agregar-pedido').attr('src'),
                'texto_producto': $('#id-texto-producto-agregar-pedido').text(),
                'id_forma_venta': $('#forma-venta-agregar-producto-agregar').val(),
                'tipo_venta': $('#forma-venta-agregar-producto-agregar').find(':selected').text(),
                'precio_venta': $('#precio-pedido-agregar').val(),
                'cantidad': $('#cantidad-precio-pedido').val(),
                'sub_total': $('#total-precio-perdido').val(),
                'promocion': $('#id-producto-promocion-pedido').val(),
                'descripcion_regalo': $('#id-producto-promocion-regalo-pedido').val(),
                'descripcion_descuento_porcentaje': $('#id-producto-promocion-descuento-pedido').val(),
            };
            tablaProductos.push(producto);
            //limpiar todos los campos
            idProducto_para_tabla = "";
            $('#caja-busqueda-producto').val('').trigger('change');
            $('#resultado-busqueda').empty();
            $('#id-producto-agregar-pedido').val('');
            $('#foto-producto-agregar-pedido').attr('src', '');
            $('#id-texto-producto-agregar-pedido').text('');
            $('#forma-venta-agregar-producto-agregar').val('');
            $('#precio-pedido-agregar').val('0.0');
            $('#cantidad-precio-pedido').val('0');
            $('#total-precio-perdido').val('0.0');
            $('#id-producto-promocion-pedido').val('');
            $('#id-producto-promocion-regalo-pedido').val('');
            $('#id-producto-promocion-descuento-pedido').val('');
            Swal.fire({
                icon: 'success',
                title: 'Producto agregado',
                text: 'El producto ha sido agregado correctamente.',
                timer: 2000,
                showConfirmButton: false,
            });
            

            construirTablaProductos();
        }

        function eliminarProducto(e){
            let fila_obtener_cod_prod = $(e).closest('tr').find('td:first').text();
            //eliminar producto de la lista tablaProductos
            tablaProductos = tablaProductos.filter(function(prod) {
                return prod.codigo_producto !== fila_obtener_cod_prod;
            });
            // Actualizar el total del pedido
            let totalPedido = 0;
            tablaProductos.forEach(function(prod) {
                totalPedido += parseFloat(prod.sub_total);
            });
            $('.mt-3 h4').text(`Total del Pedido: ${totalPedido.toFixed(2)}`);
            Swal.fire({
                icon: 'success',
                title: 'Producto eliminado',
                text: 'El producto ha sido eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false,
            });

            construirTablaProductos();
        }

        function registrarPedido() {
            Swal.fire({
                title: 'Confirmar Pedido',
                text: "¿Estás seguro de que deseas registrar este pedido?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, registrar pedido',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    //---------
                    if (tablaProductos.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'No hay productos en el pedido.',
                        });
                        return;
                    }

                    $.ajax({
                        url: "{{ route('pedidos.vendedor.registrarPedido') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            asignacion_id: '{{ $asignacion->id }}',
                            productos: JSON.stringify(tablaProductos),
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Pedido registrado correctamente.',
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => {
                                // Redirigir a la página de pedidos
                                window.location.href = "{{ route('asignacionvendedor.index') }}";
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message || 'No se pudo registrar el pedido.',
                            });
                        }
                    });
                    //--------------
                }
            });
        }
    </script>
@stop