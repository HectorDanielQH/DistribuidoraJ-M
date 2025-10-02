@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.2rem; font-weight: 500;">
                Panel para ventas realizadas
            </span>

            <button onclick="" class="btn btn-success mt-3" data-toggle="modal" data-target="#modal-crear-venta">
                <i class="fas fa-plus mr-2"></i> Agregar Venta
            </button>
        </div>
    </div>
@stop

@section('content')

    <!--FORMAS DE VENTA-->
    <x-adminlte-modal id="modal-crear-venta" size="lg" theme="dark" icon="fas fa-plus" title="Agregar Producto al Pedido" data-backdrop="static">
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
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button theme="success" label="Agregar" data-dismiss="modal" icon="fas fa-check" class="rounded-3 px-4 py-2" onclick="agregarPedido()" />
                <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    
    {{-- DETALLE AGRUPADO POR USUARIO → PEDIDOS --}}
    <div class="container mt-4">
        <table id="tabla-ventas" class="table table-striped">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Tipo Venta</th>
                    <th>Monto Venta</th>
                    <th>Cantidad</th>
                    <th>Sub Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">TOTAL GENERAL</th>
                    <th id="total-general">0 Bs.-</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="container">
        <button class="btn btn-primary" style="background-color: #27ae60; border-radius: 8px; font-weight: 600;" onclick="guardarVenta()">
            <i class="fas fa-save mr-2"></i> Guardar Venta
        </button>
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
        //inicio 
        $(document).ready(function(){
            $('#tabla-ventas').DataTable();
            $('#tabla-ventas').DataTable().destroy();
            $('#tabla-ventas').on('click', '.eliminar-fila-btn', function() {
                $(this).closest('tr').remove();
                // Recalcular el total después de eliminar una fila
                let total = 0;
                $('#tabla-ventas tbody tr').each(function() {
                    let subTotalText = $(this).find('td').eq(8).text(); // Obtener el texto del séptimo <td> (Sub Total)
                    let subTotal = parseFloat(subTotalText.replace(' Bs.-', '')); // Convertir a número, eliminando " Bs.-"
                    if (!isNaN(subTotal)) {
                        total += subTotal; // Sumar al total si es un número válido
                    }
                });
                $('#total-general').text(total.toFixed(2) + ' Bs.-');
            });
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



        function agregarPedido(){
            $('#tabla-ventas tbody').append(`
                <tr>
                    <td style="display:none;">${$('#producto_id').val()}</td>
                    <td style="display:none;">${$('#tipo-venta-select').val()}</td>

                    <td>${$('#codigo-del-producto').text()}</td>
                    <td><img src="${$('#imagen-producto').attr('src')}" alt="Imagen del Producto" class="img-fluid" style="max-height: 50px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);"></td>
                    <td>${$('#producto_id option:selected').text()}</td>
                    <td>${$('#tipo-venta-select option:selected').text()}</td>
                    <td class="text-end">
                        ${$('#tipo-venta-select option:selected').text().split(' - ')[1].replace(' Bs.-','')}
                    </td>
                    <td class="text-end">
                        ${$('#cantidad-venta-input').val()}
                    </td>
                    <td class="text-end">
                        ${(parseFloat($('#tipo-venta-select option:selected').text().split(' - ')[1].replace(' Bs.-','')) * parseInt($('#cantidad-venta-input').val())).toFixed(2)} Bs.-
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm eliminar-fila-btn" style="background-color: #e74c3c; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `);

            // Resetear el formulario del modal
            $('#producto_id').val(null).trigger('change');
            $('#codigo-del-producto').text('N/A');
            $('#imagen-producto').attr('src', "{{ asset('images/logo_color.webp') }}");
            $('#stock-disponible').text('N/A');
            $('#tabla-tipos-venta').empty().append('<tr><td colspan="2" class="text-center">N/A</td></tr>');
            $('#tipo-venta-select').empty().append('<option>-----Selecciona-----</option>');
            $('#cantidad-venta-input').val(1);

            // sumar el total
            let total = 0;

            $('#tabla-ventas tbody tr').each(function() {
                let subTotalText = $(this).find('td').eq(8).text(); // Obtener el texto del séptimo <td> (Sub Total)
                let subTotal = parseFloat(subTotalText.replace(' Bs.-', '')); // Convertir a número, eliminando " Bs.-"
                if (!isNaN(subTotal)) {
                    total += subTotal; // Sumar al total si es un número válido
                }
            });
            $('#total-general').text(total.toFixed(2) + ' Bs.-');

        }
    </script>

    <script>
        function guardarVenta(){
            let detalles = [];
            $('#tabla-ventas tbody tr').each(function() {
                let productoId = $(this).find('td').eq(0).text();
                let tipoVentaId = $(this).find('td').eq(1).text();
                let cantidad = $(this).find('td').eq(7).text();

                detalles.push({
                    producto_id: productoId,
                    tipo_venta_id: tipoVentaId,
                    cantidad: cantidad
                });
            });
            if(detalles.length === 0){
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'No hay productos agregados para guardar la venta.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            //antres de guardar un select a nombre del preventista y la fecha a guardar
            Swal.fire({
                title: 'A quién se le asigna la venta?',
                html:
                    `<select id="select-preventista" class="swal2-input" style="width: 100%;">
                        <option disabled selected>Selecciona un preventista</option>
                        @foreach($preventistas as $usuario)
                            <option value="{{$usuario->id}}">{{$usuario->nombres}} {{$usuario->apellido_paterno}} {{$usuario->apellido_materno}}</option>
                        @endforeach
                    </select>
                    <input type="date" id="fecha-venta" class="swal2-input" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                    `,
                focusConfirm: false,
                preConfirm: () => {
                    const preventistaId = Swal.getPopup().querySelector('#select-preventista').value;
                    const fechaVenta = Swal.getPopup().querySelector('#fecha-venta').value;
                    if (!preventistaId || !fechaVenta) {
                        Swal.showValidationMessage(`Por favor, completa todos los campos.`);
                    }
                    return { preventistaId: preventistaId, fechaVenta: fechaVenta };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let preventistaId = result.value.preventistaId;
                    let fechaVenta = result.value.fechaVenta;

                    Swal.fire({
                        title: 'Guardando venta...',
                        text: 'Por favor, espera mientras se procesa la información.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{route('administrador.ventas.administrador.guardarVenta')}}",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: "{{csrf_token()}}",
                            preventista_id: preventistaId,
                            fecha_venta: fechaVenta,
                            detalles: detalles
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'La venta se ha guardado correctamente.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Entendido'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo guardar la venta. Inténtalo de nuevo.',
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
