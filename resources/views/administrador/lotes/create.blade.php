@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de Lotes
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

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="producto_id" class="form-label text-muted">Busca el producto</label>
                        </div>
                        <div class="col-md-12">
                            <select id="producto_id" name="producto_id" style="width: 100%"></select>
                        </div>
                    </div>


                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="cantidadProducto" class="form-label text-muted">Cantidad del producto</label>
                            <x-adminlte-input name="cantidadProducto" id="cantidadProducto" type="number" placeholder="Ej: 1" min="1" value="1"
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>
                        <div class="col-md-6">
                            <label for="descripcionCantidad" class="form-label text-muted">Descripcion de la cantidad</label>
                            <x-adminlte-input name="descripcionCantidad" id="descripcionCantidad" type="text" placeholder="Ej: Cajas" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
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
                        <div class="col-md-6">
                            <label for="espacioVencimiento" class="form-label text-muted">LOTE</label>
                            <div class="col-md-12">
                                <select id="lote" name="lote" style="width: 100%">
                                    <option value="" disabled selected>Selecciona un lote existente</option>
                                    @forelse($lotes as $lote)
                                        <option value="{{ $lote->id }}">{{ $lote->codigo_lote }}</option>
                                    @empty
                                        <option value="" disabled>No hay lotes disponibles</option>
                                    @endforelse
                                </select>
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



    <!--REGISTRO DE PRODUCTO-->
    <x-adminlte-modal id="tabla-productos-bajo-stock-modal" size="lg" theme="dark" icon="fas fa-info-circle" title="Productos con bajo stock">
        <div class="modal-body px-4">
            <table class="table table-bordered table-striped" id="tabla-productos-bajo-stock">
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Stock</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>  
        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button theme="danger" id="boton-cerrar-bajostock-cerrar" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
            </div>
        </x-slot>
    </x-adminlte-modal>


    <!--TABLA DE PRODUCTOS-->

    <div class="container pb-5">
        <table class="table table-bordered table-hover table-striped" id="tabla-lotes">
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Producto</th>
                    <th>Fecha de creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="text-center">Cargando productos...</td>
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
                    "url": "{{ route('administrador.lotes.index') }}",
                    "type": "GET",
                },
                columns:[
                    {data: 'codigo_lote', name: 'codigo_lote'},
                    {data: 'producto', name: 'producto', searchable: true},
                    {data: 'created_at', name: 'created_at'},
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
                    },
                    cache: true
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
        $('#botonenviarlote').click(function(){
            var producto_id = $('#producto_id').val();
            var cantidadProducto = $('#cantidadProducto').val();
            var descripcionCantidad = $('#descripcionCantidad').val();
            var precioCompra = $('#precioCompra').val();
            var descripcionCompra = $('#descripcionCompra').val();
            var vencimientoProducto = $('#vencimientoProducto').val();

            if(!producto_id){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, selecciona un producto.',
                });
                return;
            }
            if(!cantidadProducto || cantidadProducto <= 0){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, ingresa una cantidad válida.',
                });
                return;
            }
            if(!descripcionCantidad){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, ingresa una descripción para la cantidad.',
                });
                return;
            }
            if(!precioCompra || precioCompra <= 0){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, ingresa un precio de compra válido.',
                });
                return;
            }
            if(!descripcionCompra){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, ingresa una descripción para el precio de compra.',
                });
                return;
            }
            if($('#habilitarVencimiento').is(':checked') && !vencimientoProducto){
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'Por favor, ingresa una fecha de vencimiento o desactiva la opción.',
                });
                return;
            }

            $.ajax({
                url: '{{ route("administrador.lotes.store") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    lote: $('#lote').val(),
                    producto_id: producto_id,
                    cantidadProducto: cantidadProducto,
                    descripcionCantidad: descripcionCantidad,
                    precioCompra: precioCompra,
                    descripcionCompra: descripcionCompra,
                    vencimientoProducto: vencimientoProducto
                },
                success: function(response) {
                    $('#producto_id').val(null).trigger('change');
                    $('#tabla-lotes').DataTable().ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Lote agregado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    var errorMessage = 'Ocurrió un error. Por favor, intenta nuevamente.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: errorMessage,
                    });
                }
            });
        });
    </script>
@stop