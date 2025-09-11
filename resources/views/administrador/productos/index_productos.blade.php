@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de productos
            </span>
            <a
                class="btn btn-success mt-3"
                id="descargar-catalogo-productos"
                style="border-radius: 8px;"
            >
                <i class="fas fa-file-pdf"></i> Descargar Catalogo de Productos
            </a>
        </div>
    </div>
@stop

@section('content')

   
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


    <!--FORMAS DE VENTA-->
    <x-adminlte-modal 
        id="formas-venta-producto" 
        size="lg"
        theme="dark"
        icon="fas fa-list"
        title="Formas de Venta del Producto"
        data-backdrop="static"
    >
            <div class="modal-body px-4">
                <table class="table table-bordered table-striped" id="tabla-formas-venta-producto">
                    <thead>
                        <tr>
                            <th>Forma de Venta</th>
                            <th>Precio Venta</th>
                            <th>Conversión Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-fw fa-boxes me-2"></i> Productos
            </h2>
            <a href="{{route('administrador.productos.create')}}" class="btn btn-success" style="border-radius: 8px;">
                <i class="fas fa-plus me-2"></i> Crear nuevo producto
            </a>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar todos los productos de la distribuidora.
        </p>
    </div>


    <!--TABLA DE PRODUCTOS-->

    <div class="container pb-5">
        <table id="tabla-productos" class="table tabla-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Imagen</th>
                    <th>Nombre Prod.</th>
                    <th>Marca</th>
                    <th>Stock</th>
                    <th>Formas de Venta</th>
                    <th>Promocion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
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
            const tabla = $('#tabla-productos').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: { url: '/i18n/es-ES.json' },
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                ajax: {
                url: "{{ route('administrador.productos.index') }}",
                type: "GET",
                },
                columns: [
                    { data: 'codigo', width: '10%' },
                    { data: 'imagen', orderable: false, searchable: false, width: '10%' },
                    { data: 'nombre_producto' , width: '25%' },
                    { data: 'marca', orderable: false, searchable: false , width: '15%' },
                    { data: 'stock', orderable: false, searchable: false , width: '10%' },
                    { data: 'formas_venta', orderable: false, searchable: false , width: '15%' },
                    { data: 'promocion_vista', orderable: false, searchable: false , width: '10%' },
                    { data: 'acciones', orderable: false, searchable: false , width: '15%' },
                ],
                 // Centrar header y body en TODAS las columnas
                columnDefs: [
                    { targets: '_all', className: 'dt-head-center dt-body-center align-middle td-center' }
                ],
                // Tras cada draw, si hay flex/imagenes, céntralos también
                drawCallback: function () {
                    const $w = $('#tabla-productos_wrapper');
                    // Centrar flex internos de celdas centradas
                    $w.find('td.td-center .d-flex').addClass('justify-content-center');
                    // Centrar imágenes
                    $w.find('td.td-center img').addClass('d-block mx-auto');
                }
            });
        });

    </script>
    <script>
        $(document).ready(function() {
            $('#proveedor_id').select2({
                placeholder: 'Seleccione un proveedor',
                width: 'resolve',
                dropdownParent: $('#agregar-producto'),
            });

            $("#proveedor_id").on("select2:select", function(e){
                Swal.fire({
                    title: 'Cargando marcas...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                let proveedorId = e.params.data.id;
                $.ajax({
                    url: "{{ route('administrador.marcas.show', ':id') }}".replace(':id', proveedorId),
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        Swal.close();
                        $('#marca_id').empty().append('<option value="" disabled selected>Seleccione una marca...</option>');
                        data.forEach(function(marca) {
                            $('#marca_id').append('<option value="' + marca.id + '">' + marca.descripcion + '</option>');
                        });
                        $('#marca_id').select2({
                            placeholder: 'Seleccione una marca',
                            width: 'resolve',
                            dropdownParent: $('#agregar-producto'),
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar las marcas.',
                        });
                    }
                });
            });

            $('#marca_id').select2({
                placeholder: 'Seleccione una marca',
                width: 'resolve',
                dropdownParent: $('#agregar-producto'),
            });

            
            $("#marca_id").on("select2:select", function(e){
                Swal.fire({
                    title: 'Cargando lineas...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                let marcaId = e.params.data.id;
                $.ajax({
                    url: "{{ route('administrador.lineas.show', ':id') }}".replace(':id', marcaId),
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        Swal.close();
                        $('#linea_id').empty().append('<option value="" disabled selected>Seleccione una marca...</option>');
                        data.forEach(function(lineas) {
                            $('#linea_id').append('<option value="' + lineas.id + '">' + lineas.descripcion_linea + '</option>');
                        });
                        $('#linea_id').select2({
                            placeholder: 'Seleccione una linea',
                            width: 'resolve',
                            dropdownParent: $('#agregar-producto'),
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar las lineas.',
                        });
                    }
                });
            });

            $('#linea_id').select2({
                placeholder: 'Seleccione una linea',
                width: 'resolve',
                dropdownParent: $('#agregar-producto'),
            });
        });

        $('#habilitarVencimiento').change(function() {
            if ($(this).is(':checked')) {
                $('#vencimientoProducto').prop('disabled', false);
                $('#vencimientoProducto').val(''); 
            } else {
                $('#vencimientoProducto').prop('disabled', true).val('');
            }
        });

        $('#habilitarPresentacion').change(function() {
            if ($(this).is(':checked')) {
                $('#presentacionProducto').prop('disabled', false);
                $('#presentacionProducto').val(''); 
            } else {
                $('#presentacionProducto').prop('disabled', true).val('');
            }
        });

        $('#habilitarPromocion').change(function() {
            if ($(this).is(':checked')) {
                $('#habilitarPromocion').val(true);
                $('#promocionDescuento').prop('disabled', false).val(0);
                $('#promocionRegalo').prop('disabled', false).val('');
            } else {
                $('#habilitarPromocion').val(false);
                $('#promocionDescuento').prop('disabled', true).val(0);
                $('#promocionRegalo').prop('disabled', true).val('');
            }
        });

        $("#boton-agregar-forma-venta").click(function() {
            let nuevoInput = `
                <tr>
                    <td>
                        <input type="text" class="form-control forma-venta" name="forma_venta[]" placeholder="Ej: Unidad, Caja, Bolsa" required>
                    </td>
                    <td>
                        <input type="number" class="form-control cantidad-venta" name="cantidad_venta[]" placeholder="Ej: 1, 12, 24" min="0.01" value="0.01" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" class="form-control cantidad-venta" name="equivalencia_stock[]" placeholder="Ej: 1, 12, 24" min="1" value="1" step="1" required>
                    </td>
                    <td>
                        <button class="btn btn-danger" type="button" onclick="$(this).closest('tr').remove();">X</button>
                    </td>
                </tr>`;
            $("#grupodeinputs").append(nuevoInput);
        });


        $("#botonenviarproducto").click(function(){
            $("#registro-producto").submit();
        });

        $("#registro-producto").submit(function(event){
            //verificamos si el formulario es válido
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, completa todos los campos obligatorios.',
                });
                return;
            }
            event.preventDefault();
            let formData = new FormData(this);
            Swal.fire({
                title: 'Agregando producto...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.productos.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                responsive: true,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto agregado',
                        text: 'El producto se ha agregado correctamente.',
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        $('#tabla-productos').DataTable().ajax.reload(null, false);
                        $('#botonenviar-cerrar').click();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al agregar producto',
                        text: xhr.responseJSON.message || 'Ocurrió un error al agregar el producto.',
                    });
                }
            });
        });

        function editarFormaVenta(idFormaVenta){
            Swal.fire({
                title: 'Editar la conversión del stock',
                input: 'number',
                inputLabel: 'Forma de Venta',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const formaVenta = Swal.getInput().value;
                    if (!formaVenta) {
                        Swal.showValidationMessage('Por favor, ingresa una forma de venta válida');
                        return false;
                    }
                    return { idFormaVenta, formaVenta };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando forma de venta...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.editarStock', ':id') }}".replace(':id', result.value.idFormaVenta),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT',
                            equivalencia_cantidad: result.value.formaVenta,
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Forma de venta actualizada',
                                text: 'La forma de venta se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                            }).then(() => {
                                $('#tabla-productos').DataTable().ajax.reload(null, false);
                                $('#tabla-formas-venta-producto').DataTable().ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar forma de venta',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar la forma de venta.',
                            });
                        }
                    });
                }
            });
        }

        function editarCantidadProductoStock(e) {
            const idProducto = $(e).attr('id-cantidad-stock');
            const cantidadStock = $(e).attr('cantidad-stock');
            const detalleCantidadStock = $(e).attr('detalle-cantidad-stock');
            
            Swal.fire({
                title: "Edita la cantidad y descripcion de la cantidad del producto",
                html: `
                    <label for="cantidadStock" class="swal2-label">Cantidad en Stock</label>
                    <input type="number" id="cantidadStock" class="swal2-input" value="${cantidadStock}" min="1" step="1" required>
                    <label for="detalleCantidadStock" class="swal2-label">Detalle de la Cantidad</label>
                    <input type="text" id="detalleCantidadStock" class="swal2-input" value="${detalleCantidadStock}" required>
                `,
                showCancelButton: true,
                confirmButtonText: "Guardar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    const cantidad = $("#cantidadStock").val();
                    const detalle = $("#detalleCantidadStock").val();
                    try {
                        // Realizamos la solicitud AJAX con el método PUT
                        const response = await $.ajax({
                            url: "{{ route('administrador.productos.updateCantidadStock', ':id') }}".replace(':id', idProducto),
                            type: 'POST',  // Usamos POST por el método _method
                            data: {
                                cantidadStock: cantidad,
                                detalleCantidadStock: detalle,
                                _method: 'PUT', // Indicamos que se trata de una solicitud PUT
                                _token: "{{ csrf_token() }}" // Token CSRF
                            }
                        });

                        // Si todo salió bien, retornamos la respuesta
                        return response;
                    } catch (error) {
                        Swal.showValidationMessage(`Falló la actualización: ${error.message}`);
                        throw error;  // Re-lanzamos el error para evitar continuar con el flujo
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualización exitosa',
                        text: 'La cantidad y detalle del producto se han actualizado correctamente.',
                        showConfirmButton: false,  // Eliminamos el botón de confirmación
                        timer: 2000  // Se cierra automáticamente después de 2 segundos
                    }).then(() => {
                        $('#tabla-productos').DataTable().ajax.reload(null,false);
                    });
                }
            });
        }

        function agregarPromocion(e){
            let idProducto = $(e).attr('id-producto');
            Swal.fire({
                title: 'Agregar Promoción',
                html: `
                    <label for="descuento" class="swal2-label">Descuento (%)</label>
                    <br/>
                    <input type="number" id="descuento" class="swal2-input" placeholder="Ej: 10" min="0" value="1">
                    <br/>
                    <label for="regalo" class="swal2-label">Regalo</label>
                    <br/>
                    <input type="text" id="regalo" class="swal2-input" placeholder="Ej: Bañera de Regalo">
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const descuento = $('#descuento').val();
                    const regalo = $('#regalo').val();
                    if (!descuento && !regalo) {
                        Swal.showValidationMessage('Por favor, complete todos los campos');
                        return false;
                    }
                    return { descuento, regalo, idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Agregando promoción...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.agregarPromocion',':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            descuento: result.value.descuento,
                            regalo: result.value.regalo,
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Promoción agregada',
                                text: 'La promoción se ha agregado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al agregar promoción',
                                text: xhr.responseJSON.message || 'Ocurrió un error al agregar la promoción.',
                            });
                        }
                    });
                }
            });
        }

        function eliminarPromocion(e) {
            let idProducto = $(e).attr('id-producto');
            Swal.fire({
                title: '¿Estás seguro de eliminar la promoción?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando promoción...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.eliminarPromocion', ':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Promoción eliminada',
                                text: 'La promoción se ha eliminado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar promoción',
                                text: xhr.responseJSON.message || 'Ocurrió un error al eliminar la promoción.',
                            });
                        }
                    });
                }
            });
        }

        function editarPromocion(e) {
            let idProducto = $(e).attr('id-producto');
            let descuento = $(e).attr('editar-promocion-procentaje');
            let regalo = $(e).attr('editar-promocion-regalo');

            Swal.fire({
                title: 'Editar Promoción',
                html: `
                    <label for="descuento" class="swal2-label">Descuento (%)</label>
                    <br/>
                    <input type="number" id="descuento" class="swal2-input" placeholder="Ej: 10" min="0" value="${descuento}">
                    <br/>
                    <label for="regalo" class="swal2-label">Regalo</label>
                    <br/>
                    <input type="text" id="regalo" class="swal2-input" placeholder="Ej: Bañera de Regalo" value="${regalo}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const descuento = $('#descuento').val();
                    const regalo = $('#regalo').val();
                    return { descuento, regalo, idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Editando promoción...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.editarPromocion',':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            descuento: result.value.descuento,
                            regalo: result.value.regalo,
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Promoción editada',
                                text: 'La promoción se ha editado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al editar promoción',
                                text: xhr.responseJSON.message || 'Ocurrió un error al editar la promoción.',
                            });
                        }
                    });
                }
            }); 
        }

        function verFormasVenta(e) {
            let idProducto = $(e).attr('id-producto');

            if ($.fn.DataTable.isDataTable('#tabla-formas-venta-producto')) {
                $('#tabla-formas-venta-producto').DataTable().destroy();
            }

            $('#tabla-formas-venta-producto').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    url: "/i18n/es-ES.json"
                },
                ajax: {
                    url: "{{ route('administrador.formaventas.index', ':id') }}".replace(':id', idProducto),
                    type: "GET",
                },
                columns: [
                    { data: 'tipo_venta', width: '30%' },
                    { data: 'precio_venta', width: '25%' },
                    { data: 'conversion_stock', width: '25%' },
                    { data: 'acciones', width: '20%',orderable: false,searchable: false }
                ],
            });
        }

        function editarVisualizacion(e){
            let idFormaVenta = $(e).attr('id-visualizacion');
            Swal.fire({
                title: 'Modificando Visualización',
                showCancelButton: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('administrador.formaventas.updateVisualizacion', ':id') }}".replace(':id', idFormaVenta),
                type: 'POST',
                data: {
                    _method: 'PUT',
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    Swal.close();
                    $('#tabla-formas-venta-producto').DataTable().ajax.reload(null, false);
                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al cargar forma de venta',
                        text: xhr.responseJSON.message || 'Ocurrió un error al cargar la forma de venta.',
                    });
                }
            });
        }

        function eliminarFormaVenta(e){
            let idFormaVenta = e;
            Swal.fire({
                title: '¿Estás seguro de eliminar esta forma de venta?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando forma de venta...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.formaventas.destroy', ':id') }}".replace(':id', idFormaVenta),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#tabla-formas-venta-producto').DataTable().ajax.reload(null, false);
                            $('#tabla-productos').DataTable().ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Forma de venta eliminada',
                                text: 'La forma de venta se ha eliminado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar forma de venta',
                                text: 'Ocurrió un error al eliminar la forma de venta. Existen ventas registradas con esta forma de venta.',
                            });
                        }
                    });
                }
            });
        }

        function agregarFormasVenta(e) {
            let idProducto = $(e).attr('id-producto');
            Swal.fire({
                title: 'Agregar Forma de Venta',
                html: `
                    <label for="tipo_venta" class="swal2-label">Tipo de Venta</label>
                    <br/>
                    <input type="text" id="tipo_venta" class="swal2-input" placeholder="Ej: Unidad, Caja, Bolsa" required>
                    <br/>
                    <label for="precio_venta" class="swal2-label">Precio de Venta</label>
                    <br/>
                    <input type="number" id="precio_venta" class="swal2-input" placeholder="Ej: 100.00" min="0.01" step="0.01" required>
                    <br/>
                    <label for="equivalencia_cantidad" class="swal2-label">Equivalencia de Cantidad</label>
                    <br/>
                    <input type="number" id="equivalencia_cantidad" class="swal2-input" placeholder="Ej: 1, 12, 24" min="1" value="1" step="1" required>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const tipoVenta = $('#tipo_venta').val();
                    const precioVenta = $('#precio_venta').val();
                    const equivalenciaCantidad = $('#equivalencia_cantidad').val();
                    if (!tipoVenta || !precioVenta || !equivalenciaCantidad) {
                        Swal.showValidationMessage('Por favor, complete todos los campos');
                        return false;
                    }
                    return { tipoVenta, precioVenta, equivalenciaCantidad , idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Agregando forma de venta...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.formaventas.store') }}",
                        type: 'POST',
                        data: {
                            tipo_venta: result.value.tipoVenta,
                            precio_venta: result.value.precioVenta,
                            equivalencia_cantidad: result.value.equivalenciaCantidad,
                            id_producto: result.value.idProducto,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Forma de venta agregada',
                                text: 'La forma de venta se ha agregado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $('#tabla-productos').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al agregar forma de venta',
                                text: xhr.responseJSON.message || 'Ocurrió un error al agregar la forma de venta.',
                            });
                        }
                    });
                }
            });
        }

        function visualizarProducto(e){
            let idProducto = $(e).attr('data-id-producto');
            $('#id-producto-cambiar').val(idProducto);
            Swal.fire({
                title: 'Cargando detalles del producto...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.productos.show', ':id') }}".replace(':id', idProducto),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    Swal.close(); // Cerrar el swal de carga
                    if (data.producto.foto_producto) {
                        $('#foto_producto_visualizar').attr('src', '{{ route("productos.imagen", ":id") }}'.replace(':id', data.producto.id));
                    } else {
                        $('#foto_producto_visualizar').attr('src', '{{ asset("img/no-image.png") }}');
                    }
                    $('#codigo-mostrar-producto').text(data.producto.codigo);
                    $('#nombre-mostrar-producto').text(data.producto.nombre_producto);
                    $('#proveedor-mostrar-producto').text(data.proveedor.nombre_proveedor);
                    $('#marca-mostrar-producto').text(data.marca.descripcion);
                    $('#linea-mostrar-producto').text(data.linea.descripcion_linea);
                    $('#descripcion-mostrar-producto').text(data.producto.descripcion_producto);
                    $('#cantidad-mostrar-producto').text(data.producto.cantidad);
                    $('#descripcion-cantidad-mostrar-producto').text(data.producto.detalle_cantidad);
                    $('#precio-compra-mostrar-producto').text(data.producto.precio_compra);
                    $('#descripcion-mostrar-compra-producto').text(data.producto.detalle_precio_compra);
                    $('#presentacion-mostrar-producto').text(data.producto.presentacion || 'No disponible');
                    $('#promocion-mostrar-producto').text(
                        data.producto.promocion
                            ? `Descuento: ${data.producto.descripcion_descuento_porcentaje != 0 && data.producto.descripcion_descuento_porcentaje != null ? data.producto.descripcion_descuento_porcentaje : 'N/A'}% - Regalo: ${data.producto.descripcion_regalo && data.producto.descripcion_regalo.trim() !== '' ? data.producto.descripcion_regalo : 'N/A'}`
                            : 'No disponible'
                    );
                    
                    $('#tabla-formas-venta-mostrar-producto').empty();

                    data.formasVenta.forEach(function(formaVenta) {
                        if(formaVenta.activo) {
                            $('#tabla-formas-venta-mostrar-producto').append(`
                                <tr>
                                    <td>${formaVenta.tipo_venta}</td>
                                    <td>${formaVenta.precio_venta}</td>
                                    <td>${formaVenta.equivalencia_cantidad} ${data.producto.detalle_cantidad}</td>
                                </tr>
                            `);
                        }
                    });
                    if ($('#tabla-formas-venta-mostrar-producto').children().length === 0) {
                        $('#tabla-formas-venta-mostrar-producto').append(`
                            <tr>
                                <td colspan="2" class="text-center">No hay formas de venta registradas.</td>
                            </tr>
                        `);
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los detalles del producto.',
                    });
                }
            });
        }

        function cerrar_modal_actualizar_vista(){
            Swal.fire({
                title: 'Renderizando vista...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                timer: 2000,
                didOpen: () => {
                    Swal.showLoading();
                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                }
            })
        };


        // Función para cargar marcas según proveedor seleccionado
        function cargarMarcasYLineasVisualizarEditar(e){
            let idProveedor = $(e).val();
            let $marcaSelect = $('#proveedor-marca-visualizar-editar');
            let $lineaSelect = $('#proveedor_producto-linea-visualizar-editar');

            $marcaSelect.prop('disabled', true).empty().append('<option value="">Cargando marcas...</option>');
            $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');

            if (!idProveedor) {
                $marcaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una marca</option>');
                $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');
                return;
            }

            $.ajax({
                url: "{{ route('administrador.marcas.show', ':id') }}".replace(':id', idProveedor),
                type: 'GET',
                success: function(data) {
                    $marcaSelect.prop('disabled', false).empty().append('<option value="">Seleccione una marca</option>');
                    data.forEach(function(marca) {
                        $marcaSelect.append(`<option value="${marca.id}">${marca.descripcion}</option>`);
                    });
                    $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las marcas del proveedor.',
                    });
                    $marcaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una marca</option>');
                    $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');
                }
            });
        }

        // Función para cargar líneas según marca seleccionada
        function cargarLineasVisualizarEditar(e){
            let idMarca = $(e).val();
            let $lineaSelect = $('#proveedor_producto-linea-visualizar-editar');

            $lineaSelect.prop('disabled', true).empty().append('<option value="">Cargando líneas...</option>');

            if (!idMarca) {
                $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');
                return;
            }

            $.ajax({
                url: "{{ route('administrador.lineas.show', ':id') }}".replace(':id', idMarca),
                type: 'GET',
                success: function(data) {
                    $lineaSelect.prop('disabled', false).empty().append('<option value="">Seleccione una línea</option>');
                    data.forEach(function(linea) {
                        $lineaSelect.append(`<option value="${linea.id}">${linea.descripcion_linea}</option>`);
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las líneas de la marca.',
                    });
                    $lineaSelect.prop('disabled', true).empty().append('<option value="">Seleccione una línea</option>');
                }
            });
        }


        function eliminarProducto(e) {
            let id = $(e).attr('id-producto');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás recuperar este producto una vez eliminado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#28a745',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando producto...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Producto eliminado',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al eliminar producto',
                                text: xhr.responseJSON.message || 'Ocurrió un error al eliminar el producto.',
                            });
                        }
                    });
                }
            });
        }

        function ProductoDeAlta(e){
            let id = $(e).attr('id-producto');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Este producto será dado de alta y estará disponible para la venta.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Sí, dar de alta',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.dardealtaobaja', ':id') }}".replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Producto dado de alta',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al dar de alta el producto',
                                text: xhr.responseJSON.message || 'Ocurrió un error al dar de alta el producto.',
                            });
                        }
                    });
                }
            });
        }

        function ProductoDeBaja(e){
            let id = $(e).attr('id-producto');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Este producto será dado baja y no estará disponible para la venta.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Sí, dar de baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('administrador.productos.dardealtaobaja', ':id') }}".replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Producto dado de baja',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al dar de baja el producto',
                                text: xhr.responseJSON.message || 'Ocurrió un error al dar de baja el producto.',
                            });
                        }
                    });
                }
            });
        }


        $('#descargar-catalogo-productos').click(function() {
            Swal.fire({
                title: 'Descargando catálogo de productos...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    window.location.href = "{{ route('productos.vendedor.descargarCatalogo') }}";
                }
            });
        });

        function editarNombreFormaVenta(e){
            let idFormaVenta = $(e).attr('id-forma-venta');
            let nombreFormaVenta = $(e).attr('nombre-forma-venta');
            Swal.fire({
                title: 'Editar Nombre de Forma de Venta',
                input: 'text',
                inputLabel: 'Nombre de la Forma de Venta',
                inputValue: nombreFormaVenta,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: (newNombre) => {
                    if (!newNombre) {
                        Swal.showValidationMessage('El nombre no puede estar vacío');
                        return false;
                    }
                    return newNombre;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando nombre...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.formaventas.editarNombreFormaVenta', ':id') }}".replace(':id', idFormaVenta),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method:'PUT',
                            nuevo_nombre: result.value
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Nombre actualizado',
                                text: 'El nombre de la forma de venta se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-formas-venta-producto').DataTable().ajax.reload(null, false);
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar el nombre',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar el nombre de la forma de venta.',
                            });
                        }
                    });
                }
            });
        }
    </script>
    @if($contar_productos_menores > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'warning',
                    title: 'Productos con stock bajo',
                    html: `
                    <p class="mb-3">Hay productos con stock menor a 15 unidades. Por favor, revisa el inventario.</p>
                    <div class="d-flex justify-content-center">
                        <button id="btn-revisar" class="btn btn-danger mr-2" data-toggle="modal" data-target="#tabla-productos-bajo-stock-modal">Revisar</button>
                        <button id="btn-later"  class="btn btn-secondary">Más tarde</button>
                    </div>
                    `,
                    showConfirmButton: false,
                    showCancelButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        // click "Revisar" -> cerrar swal y abrir modal por código
                        document.getElementById('btn-revisar').addEventListener('click', () => {
                            Swal.close();
                            $('#tabla-productos-bajo-stock').DataTable({
                                processing: true,
                                serverSide: true,
                                language: {
                                    url: '/i18n/es-ES.json'
                                },
                                ajax: {
                                    url: "{{ route('administrador.productos.bajostock') }}",
                                    type: 'GET',
                                },
                                columns: [
                                    { data: 'codigo', name: 'codigo' },
                                    { data: 'nombre_producto', name: 'nombre_producto' },
                                    { data: 'stock', name: 'stock', searchable: false}
                                ],
                            });
                        });

                        // click "Más tarde"
                        document.getElementById('btn-later').addEventListener('click', () => Swal.close());
                    }
                });
            });
        </script>
    @endif
@stop