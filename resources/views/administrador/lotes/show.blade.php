@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Gestión de Lotes de Productos
            </span>
            <span class="text-white mt-2" style="font-size: 1rem; font-weight: 400; color: #bdc3c7;">
                Codigo: {{ $lotes->codigo_lote ?? 'N/A' }}
            </span>
            <button
                class="btn btn-success mt-3 mb-2 px-4 py-2"
                data-toggle="modal"
                data-target="#agregar-lote"
                id="agregar-nuevo-lote"
                style="border-radius: 8px;"
            >
                <i class="fas fa-plus"></i> Agregar producto al lote
            </button>
        </div>
    </div>
@stop

@section('content')

    <!--REGISTRO DE PRODUCTO-->
    <x-adminlte-modal id="agregar-lote" size="lg" theme="dark" icon="fas fa-plus-circle" title="Agregar lote">
            <div class="modal-body px-4">
                <form id="registro-lote" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="producto_id" class="form-label text-muted">Busca el producto</label>
                        </div>
                        <div class="col-md-12">
                            <select id="producto_id" name="producto_id" style="width: 100%"></select>
                        </div>
                    </div>


                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="cantidadProducto" class="form-label text-muted">Cantidad del producto por agregar</label>
                            <x-adminlte-input name="cantidadProducto" id="cantidadProducto" type="number" placeholder="Ej: 1" min="0" value="0"
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>
                        <div class="col-md-6">
                            <label for="descripcionCantidad" class="form-label text-muted">Descripcion de la cantidad</label>
                            <x-adminlte-input name="descripcionCantidad" id="descripcionCantidad" type="text" placeholder="Ej: Cajas" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;" readonly/>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="precioCompra" class="form-label text-muted">Precio de ingreso del Producto</label>
                            <x-adminlte-input name="precioCompra" id="precioCompra" type="number" placeholder="Ej: 25.4" min="0,01" value="0.01" step="0.01" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>

                        <div class="col-md-6">
                            <label for="descripcionCompra" class="form-label text-muted">Detalle del precio de ingreso</label>
                            <x-adminlte-input name="descripcionCompra" id="descripcionCompra" type="text" placeholder="Ej: se compro 25 cajas, cada caja a 10Bs.-" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="vencimientoProducto" class="form-label text-muted">Fecha de Vencimiento</label>
                            <x-adminlte-input name="vencimientoProducto" id="vencimientoProducto" type="date" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;" disabled/>
                            <!--check-->
                            <div class="d-flex">
                                <input type="checkbox" id="habilitarVencimiento" class="form-check mr-2">
                                <label for="habilitarVencimiento" class="form-check-label text-muted">Habilitar vencimiento</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button type="submit" id="botonenviarlote" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2"/>
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    <!--TABLA DE PRODUCTOS-->

    <div class="container pb-5">
        <table class="table table-bordered table-hover table-striped" id="tabla-lotes">
            <thead>
                <tr>
                    <th>Cod. Prod.</th>
                    <th>Imagen</th>
                    <th>Descripcion</th>
                    <th>Cantidad añadida</th>
                    <th>Nuevo precio</th>
                    <th>Fecha Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Cargando productos...</td>
                </tr>
            </tbody>
        </table>
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
        .select2-container .select2-selection--single {
            height: 35px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        #overlay-destacar {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 1050;
        }

    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function(){
            $('#tabla-lotes').DataTable({
                processing:true,
                serverSide:true,
                responsive:true,
                language: {
                    url: '/i18n/es-ES.json'
                },
                pageLength: 5,
                lengthMenu: [ [5, 10, 25, 50], [5, 10, 25, 50] ],
                "ajax": {
                    "url": "{{ route('administrador.lote.productos.obtenerLotesProducto', ':id') }}".replace(':id', '{{ $lotes->codigo_lote }}'),
                    "type": "GET",
                },
                columns:[
                    {data: 'codigo_producto', name: 'codigo_producto'},
                    {data: 'imagen', name: 'imagen', orderable: false, searchable: false},
                    {data: 'descripcion', name: 'descripcion'},
                    {data: 'cantidad_anadida', name: 'cantidad_anadida'},
                    {data: 'nuevo_precio', name: 'nuevo_precio'},
                    {data: 'fecha_vencimiento', name: 'fecha_vencimiento'},
                    {data: 'acciones', name: 'acciones', orderable: false, searchable: false},
                ],
                
            });

            $('#producto_id').select2({
                placeholder: 'Buscar producto por nombre o código',
                ajax: {
                    url: '{{ route("administrador.lote.productos.buscarProducto") }}',
                    dataType: 'json',
                    type: 'GET',
                    data: function (params) {
                        return {
                            query: params.term
                        };
                    },
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: item.id,
                                    text: item.nombre_producto + ' (Código: ' + item.codigo + ')'
                                };
                            })
                        };
                    }
                },
                minimumInputLength: 1,
            });

        });

        

        $('#habilitarVencimiento').change(function() {
            if($(this).is(':checked')) {
                $('#vencimientoProducto').prop('disabled', false);
            } else {
                $('#vencimientoProducto').prop('disabled', true);
                $('#vencimientoProducto').val('');
            }
        });

        function eliminarLote(e){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let id = e.getAttribute('id-lote');
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor espera.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.lote.productos.eliminarLote', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#tabla-lotes').DataTable().ajax.reload();
                            Swal.fire(
                                '¡Eliminado!',
                                'El lote ha sido eliminado.',
                                'success'
                            )
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                'Hubo un problema al eliminar el lote.',
                                'error'
                            )
                        }
                    })
                }
            });
        }


        $('#producto_id').on('select2:select', function (e) {
            let productoId = e.params.data.id;
            $.ajax({
                url: '{{ route("administrador.lote.productos.detalleProducto", ":id") }}'.replace(':id', productoId),
                type: 'GET',
                success: function(data) {
                    $('#descripcionCantidad').val(data.detalle_cantidad);
                    $('#precioCompra').val(data.precio_compra);
                    $('#descripcionCompra').val(data.detalle_precio_compra);
                },
                error: function() {
                    $('#descripcionCantidad').val('');
                }
            });
        });

        $('#habilitarVencimiento').change(function() {
            if($(this).is(':checked')) {
                $('#vencimientoProducto').prop('disabled', false);
            } else {
                $('#vencimientoProducto').prop('disabled', true);
                $('#vencimientoProducto').val('');
            }
        });

        $('#botonenviarlote').click(function(){
            let codigoLote = '{{ $lotes->codigo_lote }}';
            let productoId = $('#producto_id').val();
            let cantidadProducto = $('#cantidadProducto').val();
            let descripcionCantidad = $('#descripcionCantidad').val();
            let precioCompra = $('#precioCompra').val();
            let descripcionCompra = $('#descripcionCompra').val();
            let vencimientoProducto = $('#vencimientoProducto').val();
            if(!productoId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, selecciona un producto.',
                });
                return;
            }
            if(cantidadProducto <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'La cantidad del producto debe ser mayor a cero.',
                });
                return;
            }
            if(precioCompra <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'El precio de compra debe ser mayor a cero.',
                });
                return;
            }
            if(!descripcionCompra) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, ingresa una descripción del precio de compra.',
                });
                return;
            }
            if($('#habilitarVencimiento').is(':checked') && !vencimientoProducto) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, ingresa una fecha de vencimiento o desactiva la opción.',
                });
                return;
            }
            Swal.fire({
                title: 'Registrando lote...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false
            })
            $.ajax({
                url: '{{ route("administrador.lotes.store") }}',
                type: 'POST',
                data: {
                    _token: `{{ csrf_token() }}`,
                    codigo_lote: codigoLote,
                    producto_id: productoId,
                    cantidad_producto: cantidadProducto,
                    descripcion_cantidad: descripcionCantidad,
                    precio_compra: precioCompra,
                    descripcion_precio_compra: descripcionCompra,
                    vencimiento_producto: vencimientoProducto
                },
                success: function(response) {
                    $('#registro-lote')[0].reset();
                    $('#producto_id').val(null).trigger('change');
                    $('#tabla-lotes').DataTable().ajax.reload(null, false);
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'El lote se ha registrado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = Object.values(errors).map(function(errorArray) {
                        return errorArray.join(' ');
                    }).join(' ');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessages,
                    });
                }
            });
        });
    </script>
@stop