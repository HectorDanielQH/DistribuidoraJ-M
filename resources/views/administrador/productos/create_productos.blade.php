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
        </div>
    </div>
@stop

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm" style="border-radius: 12px;">
            <div class="bg-secondary d-flex align-items-center justify-content-between p-3 rounded-lg">
                <h3 class="card-title mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-plus-circle me-2"></i> Agregar Nuevo Producto
                </h3>
                <a href="{{ route('administrador.productos.index') }}" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-left mx-2"></i>Volver a la lista de productos
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('administrador.productos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="proveedor" class="form-label" style="font-weight: 500;">Selecciona el proveedor</label>
                                <select name="proveedor" id="proveedor" class="form-control select2 @error('proveedor') is-invalid @enderror" style="width: 100%;" required>
                                    <option value="" disabled selected>-- Selecciona un proveedor --</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre_proveedor }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="marca-producto" class="form-label" style="font-weight: 500;">Marca del Producto</label>
                                <select name="marca_producto" id="marca-producto" class="form-control @error('marca_producto') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Selecciona una marca --</option>
                                </select>
                                @error('marca_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="linea-producto" class="form-label" style="font-weight: 500;">Selecciona la linea del Producto</label>
                                <select name="linea_producto" id="linea-producto" class="form-control select2 @error('linea-producto') is-invalid @enderror" style="width: 100%;" required>
                                    <option value="" disabled selected>-- Selecciona una linea --</option>
                                </select>
                                @error('linea_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="codigo-producto" class="form-label" style="font-weight: 500;">Código del Producto</label>
                                <div class="d-flex align-items-center justify-content-start">
                                    <input type="text" name="codigo_producto" id="codigo-producto" class="form-control @error('codigo_producto') is-invalid @enderror" value="{{ old('codigo_producto') }}" placeholder="Ingresa el código del producto" readonly required>
                                    <button type="button" class="btn btn-info ml-2" onclick="generarCodigoAleatorio()">
                                        <i class="fas fa-random me-1"></i> 
                                    </button>
                                </div>
                                @error('codigo_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="nombre-producto" class="form-label" style="font-weight: 500;">Nombre del Producto</label>
                                <input type="text" name="nombre_producto" id="nombre-producto" class="form-control @error('nombre_producto') is-invalid @enderror" value="{{ old('nombre_producto') }}" placeholder="Ingresa el nombre del producto" required>
                                @error('nombre_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="descripcion-producto" class="form-label" style="font-weight: 500;">Descripción del Producto</label>
                                <input type="text" name="descripcion_producto" id="descripcion-producto" class="form-control @error('descripcion_producto') is-invalid @enderror" value="{{ old('descripcion_producto') }}" placeholder="Ingresa la descripción del producto" required>
                                @error('descripcion_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="cantidad" class="form-label" style="font-weight: 500;">Cantidad</label>
                                <input type="number" step="1" name="cantidad" id="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad') }}" placeholder="Ingresa la cantidad" required>
                                @error('cantidad')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="detalle-cantidad" class="form-label" style="font-weight: 500;">Detalle Cantidad</label>
                                <input type="text" name="detalle_cantidad" id="detalle-cantidad" class="form-control @error('detalle_cantidad') is-invalid @enderror" value="{{ old('detalle_cantidad') }}" placeholder="Ingresa el detalle de la cantidad" required>
                                @error('detalle_cantidad')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="precio-compra" class="form-label" style="font-weight: 500;">Precio de Compra</label>
                                <input type="number" step="0.01" name="precio_compra" id="precio-compra" class="form-control @error('precio_compra') is-invalid @enderror" value="{{ old('precio_compra') }}" placeholder="Ingresa el precio de compra" required>
                                @error('precio_compra')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="detalle-precio-compra" class="form-label" style="font-weight: 500;">Detalle de la compra</label>
                                <input type="text" name="detalle_precio_compra" id="detalle-precio-compra" class="form-control @error('detalle_precio_compra') is-invalid @enderror" value="{{ old('detalle_precio_compra') }}" placeholder="Ingresa el detalle de la compra" required>
                                @error('detalle_precio_compra')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="presentacion" class="form-label" style="font-weight: 500;">Presentación del Producto</label>
                                <input type="text" name="presentacion" id="presentacion" class="form-control @error('presentacion') is-invalid @enderror" value="{{ old('presentacion') }}" placeholder="Ingresa la presentación del producto" required>
                                @error('presentacion')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="promocion" class="form-label" style="font-weight: 500;">Promoción del Producto</label>
                                <input type="checkbox" name="promocion" id="promocion"  {{ old('promocion') ? 'checked' : '' }} onclick="togglePromocionFields()" value="1">
                                <div class="form-text">
                                    <label for="descuento-porcentaje" class="form-label" style="font-weight: 500;">Descuento por porcentaje</label>
                                    <input type="number" step="1" name="descripcion_descuento_porcentaje" id="descripcion-descuento-porcentaje" class="form-control @error('descripcion_descuento_porcentaje') is-invalid @enderror" value="{{ old('descripcion_descuento_porcentaje') }}" placeholder="Ingresa el descuento por porcentaje" disabled>
                                </div>

                                <div class="form-text">
                                    <label for="descripcion-regalo" class="form-label" style="font-weight: 500;">Regalo</label>
                                    <input type="text" name="descuento_promocion" id="descripcion-regalo" class="form-control @error('descuento_promocion') is-invalid @enderror" value="{{ old('descuento_promocion') }}" placeholder="Ingresa el regalo" disabled>
                                </div>

                                @error('promocion' || 'descripcion_descuento_porcentaje' || 'descuento_promocion')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label for="imagen-producto" class="form-label" style="font-weight: 500;">Imagen del Producto</label>
                                <div class="">
                                    <img src="" alt="imagen del producto" id="preview-imagen-producto" style="max-width: 200px; max-height: 200px; display: none; margin-bottom: 10px;">
                                </div>
                                <input type="file" name="imagen_producto" id="imagen-producto" class="form-control @error('imagen_producto') is-invalid @enderror" required>
                                @error('imagen_producto')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="form-label d-block" style="font-weight: 500;">Fecha de Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" id="fecha-vencimiento" class="form-control @error('fecha_vencimiento') is-invalid @enderror" value="{{ old('fecha_vencimiento') }}" required>
                                @error('fecha_vencimiento')
                                    <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <label class="form-label d-flex justify-content-start align-items-center" style="font-weight: 500;">
                        <strong> Formas de venta</strong><p class="text-danger ml-2">*</p>
                        <button class="btn btn-success btn-sm ml-3" type="button" onclick="agregarFormaVenta()">
                            <i class="fas fa-plus-circle me-1"></i>
                        </button>
                    </label>

                    <div class="row" id="formas-venta-container">
                        <div class="col-4">
                            <label for="nombre-forma-venta" class="form-label" style="font-weight: 500;">
                                Nombre de la forma de venta
                            </label>
                            <input type="text" id='nombre-forma-venta' name="nombre_forma_venta[]" class="form-control mb-2" placeholder="Ingresa el nombre de la forma de venta" required>
                        </div>
                        <div class="col-4">
                            <label for="precio-forma-venta" class="form-label" style="font-weight: 500;">
                                Precio de la forma de venta
                            </label>
                            <input type="number" step="0.01" id='precio-forma-venta' name="precio_forma_venta[]" class="form-control mb-2" placeholder="Ingresa el precio de la forma de venta" required>
                        </div>
                        <div class="col-4">
                            <label for="equivalencia" class="form-label" style="font-weight: 500;">
                                Equivalencia
                            </label>
                            <input type="number" step="1" id='equivalencia' name="equivalencia[]" class="form-control mb-2" placeholder="Ingresa la equivalencia" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary ml-3" style="border-radius: 8px; height: 40px; align-self: flex-end;">
                            <i class="fas fa-save me-1"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            $('#proveedor').select2({
                placeholder: 'Selecciona un proveedor',
            });
            $('#marca-producto').select2({
                placeholder: 'Selecciona una marca',
            });
            $('#linea-producto').select2({
                placeholder: 'Selecciona una linea',
            });
        });

        $('#proveedor').on('select2:select', function (e) {
            let proveedorId = $(this).val();
            Swal.fire({
                title: 'Cargando marcas...',
                text: 'Por favor, espera mientras se cargan las marcas del proveedor seleccionado.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            if(proveedorId) {
                $.ajax({
                    url: "{{route('administrador.marcas.show', ':id')}}".replace(':id', proveedorId),
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();
                        $('#marca-producto').empty();
                        $('#marca-producto').append('<option value="" disabled selected>-- Selecciona una marca --</option>');
                        $.each(data, function(key, value) {
                            $('#marca-producto').append('<option value="'+ value.id +'">'+ value.descripcion +'</option>');
                        });
                    }
                });
            } else {
                $('#marca-producto').empty();
                $('#marca-producto').append('<option value="" disabled selected>-- Selecciona una marca --</option>');
            }
        });

        $('#marca-producto').on('select2:select', function (e) {
            let lineaId = $(this).val();

            Swal.fire({
                title: 'Cargando marcas...',
                text: 'Por favor, espera mientras se cargan las marcas de la marca seleccionada.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            if(lineaId) {
                $.ajax({
                    url: "{{route('administrador.lineas.show', ':id')}}".replace(':id', lineaId),
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();
                        $('#linea-producto').empty();
                        $('#linea-producto').append('<option value="" disabled selected>-- Selecciona una marca --</option>');
                        $.each(data, function(key, value) {
                            $('#linea-producto').append('<option value="'+ value.id +'">'+ value.descripcion_linea +'</option>');
                        });
                    }
                });
            } else {
                $('#linea-producto').empty();
                $('#linea-producto').append('<option value="" disabled selected>-- Selecciona una marca --</option>');
            }
        });

        function generarCodigoAleatorio() {
            Swal.fire({
                title: 'Generando código...',
                text: 'Por favor, espera mientras se genera el código aleatorio.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('administrador.productos.autogenerar_codigo') }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    $('#codigo-producto').val(data.codigo);
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo generar el código. Inténtalo de nuevo.',
                    });
                }
            });
        }


        function togglePromocionFields() {
            const promocionCheckbox = document.getElementById('promocion');
            const descuentoField = document.getElementById('descripcion-descuento-porcentaje');
            const regaloField = document.getElementById('descripcion-regalo');

            if (promocionCheckbox.checked) {
                descuentoField.disabled = false;
                regaloField.disabled = false;
            } else {
                descuentoField.disabled = true;
                regaloField.disabled = true;
                descuentoField.value = '';
                regaloField.value = '';
            }
        }

        function agregarFormaVenta(){
            const container = document.getElementById('formas-venta-container');
            const index = container.children.length / 3; // Cada forma de venta tiene 3 campos

            const nombreDiv = document.createElement('div');
            nombreDiv.className = 'col-4';
            nombreDiv.innerHTML = `
                <label for="nombre-forma-venta-${index}" class="form-label" style="font-weight: 500;">
                    Nombre de la forma de venta
                </label>
                <input type="text" id="nombre-forma-venta-${index}" name="nombre_forma_venta[]" class="form-control mb-2" placeholder="Ingresa el nombre de la forma de venta">
            `;

            const precioDiv = document.createElement('div');
            precioDiv.className = 'col-4';
            precioDiv.innerHTML = `
                <label for="precio-forma-venta-${index}" class="form-label" style="font-weight: 500;">
                    Precio de la forma de venta
                </label>
                <input type="number" step="0.01" id="precio-forma-venta-${index}" name="precio_forma_venta[]" class="form-control mb-2" placeholder="Ingresa el precio de la forma de venta">
            `;

            const equivalenciaDiv = document.createElement('div');
            equivalenciaDiv.className = 'col-4';
            equivalenciaDiv.innerHTML = `
                <label for="equivalencia-${index}" class="form-label" style="font-weight: 500;">
                    Equivalencia
                </label>
                <input type="number" step="1" id="equivalencia-${index}" name="equivalencia[]" class="form-control mb-2" placeholder="Ingresa la equivalencia">
            `;

            container.appendChild(nombreDiv);
            container.appendChild(precioDiv);
            container.appendChild(equivalenciaDiv);
        }

        $('#imagen-producto').on('change', function() {
            const [file] = this.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-imagen-producto').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#preview-imagen-producto').hide();
            }
        });
    </script>
@stop