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
            <button
                class="btn btn-success mt-3"
                id="descargar-catalogo-productos"
                style="border-radius: 8px;"
            >
                <i class="fas fa-file-pdf"></i> Descargar Catalogo de Productos
            </button>
        </div>
    </div>
@stop

@section('content')

    <!--REGISTRO DE PRODUCTO-->
    <x-adminlte-modal id="agregar-producto" size="lg" theme="dark" icon="fas fa-plus" title="Agregar Producto">
            <div class="modal-body px-4">
                <form id="registro-producto" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="descripcion_linea" class="form-label text-muted">Selecciona el Proveedor</label>
                        </div>
                        <div class="col-md-12">
                            <select id="proveedor_id" name="proveedor_id" style="width: 100%">
                                <option value="" disabled selected>Seleccione un proveedor...</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="marca_id" class="form-label text-muted">Selecciona la marca</label>
                        </div>
                        <div class="col-md-12">
                            <select id="marca_id" name="marca_id" style="width: 100%"></select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="linea_id" class="form-label text-muted">Seleccione la linea</label>
                        </div>
                        <div class="col-md-12">
                            <select id="linea_id" name="linea_id" style="width: 100%"></select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="codigo" class="form-label text-muted">Ingrese el código de producto</label>
                            <x-adminlte-input name="codigo" id="codigo" type="text" placeholder="Ej: 12345678" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                            <button type="button" class="btn btn-primary" id="autogenerar-codigo" style="border-radius: 8px;">
                                <i class="fas fa-magic"></i> Autogenerar Código
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="nombreProducto" class="form-label text-muted">Nombre del producto</label>
                            <x-adminlte-input name="nombreProducto" id="nombreProducto" type="text" placeholder="Ej: Chocolate con almendras" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="descripcionProducto" class="form-label text-muted">Descripción del producto</label>
                            <x-adminlte-input name="descripcionProducto" id="descripcionProducto" type="text" placeholder="Ej:  chocolate de sabor naraja" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
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
                            <label for="precioCompra" class="form-label text-muted">Precio de compra del producto</label>
                            <x-adminlte-input name="precioCompra" id="precioCompra" type="number" placeholder="Ej: 25.4" min="0,01" value="0.01" step="0.01" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;"/>
                        </div>

                        <div class="col-md-6">
                            <label for="descripcionCompra" class="form-label text-muted">Detalle de la compra</label>
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
                            <label for="presentacionProducto" class="form-label text-muted">Presentación del Producto</label>
                            <x-adminlte-input name="presentacionProducto" id="presentacionProducto" type="text" placeholder="Ej: 25gr cada bolsa" required
                                class="form-control shadow-sm border-2" style="border-radius: 8px;" disabled/>
                            <!--check-->
                            <div class="d-flex">
                                <input type="checkbox" id="habilitarPresentacion" class="form-check mr-2">
                                <label for="habilitarPresentacion" class="form-check-label text-muted">Habilitar caja de presentación</label>
                            </div>
                        </div>
                    </div>


                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label for="habilitarPromocion" class="form-label text-muted">Promocion del Producto</label>
                            <div class="d-flex">
                                <input type="checkbox" id="habilitarPromocion" class="form-check mr-2" name="habilitarPromocion" value="{{false}}">
                                <label for="habilitarPromocion" class="form-check-label text-muted">¿El producto tiene promocion?</label>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="promocionDescuento" class="text-muted">Ingresa números enteros (%)</label>
                                    <x-adminlte-input name="promocionDescuento" id="promocionDescuento" type="number" placeholder="Ej: numeros enteros" required
                                        min="0" value="0"  class="form-control shadow-sm border-2" style="border-radius: 8px;" disabled/>
                                    <span class="text-muted">Si el producto no tiene promocion en descuento por pocentaje, dejar en 0</span>
                                </div>

                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <label for="promocionDescuento">O</label>
                                </div>

                                <div class="col-md-5">
                                    <label for="promocionRegalo" class="text-muted">Ingresa el regalo de promocion</label>
                                    <x-adminlte-input name="promocionRegalo" id="promocionRegalo" type="text" placeholder="Ej: Bañeras de Regalo" required
                                        class="form-control shadow-sm border-2" style="border-radius: 8px;" disabled/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">     
                            <x-adminlte-input-file name="imagen_producto" label="Imagen del Producto" igroup-size="md" placeholder="Seleccione el archivo..." 
                                legend="Buscar Imagen" accept="image/*"
                            >
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-lightblue">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input-file>
                        </div>
                    </div>

                    <div class="row g3 mt-3">
                        <div class="col-md-12">
                            <label for="descripcion_linea" class="form-label text-muted">Forma de Venta del Producto <button class="btn btn-success ml-2" type="button" id="boton-agregar-forma-venta">+</button></label>
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 50%">Forma de Venta</th>
                                        <th scope="col" style="width: 30%">Precio</th>
                                        <th scope="col" style="width: 20%">Equivalencia Stock</th>
                                        <th scope="col" style="width: 20%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="grupodeinputs">
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
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between">
                <x-adminlte-button type="submit" id="botonenviarproducto" theme="success" icon="fas fa-check" label="Aceptar" class="rounded-3 px-4 py-2"/>
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


    <!--MOSTRAR PRODUCTO-->
    <x-adminlte-modal id="ver-distribuidora-producto"
        size="lg"
        theme="dark"
        icon="fas fa-eye"
        title="Visualizar Producto"
        data-backdrop="static"
        data-keyboard="false"
    >
        <div class="modal-body px-4" id="modificar-producto-visualizar">
            <input type="hidden" id="id-producto-cambiar" value="">
            <!-- Foto del Producto -->
            <div class="mb-4 text-center">
                <label class="form-label text-muted">Foto del Producto</label>
                <br>
                <img id="foto_producto_visualizar" src="{{asset('images/logo_color.webp')}}" alt="Foto del Producto" class="img-fluid rounded shadow" style="max-height: 200px;">

                <!-- Botón para cambiar la foto -->
                <div class="mt-2">
                    <button class="btn btn-primary" id="cambiar-foto-producto">
                        <i class="fas fa-edit"></i> Cambiar Foto
                    </button>
                </div>
            </div>

            <!-- Información Básica -->
            <div class="row g-3">
                <div class="col-md-6">
                    <x-adminlte-card theme="dark" title="Código del Producto">
                        <p id="codigo-mostrar-producto" class="mb-0 fw-bold text-dark"></p>
                        <!-- Botón para cambiar el código -->
                        <div class="mt-2 d-flex flex-column justify-content-center">
                            <button class="btn btn-primary" id="cambiar-codigos-producto-visualizar">
                                <i class="fas fa-edit"></i> Cambiar Código Manualmente
                            </button>
                            <button class="btn btn-secondary mt-2" id="autogenerar-codigo-producto-visualizar">
                                <i class="fas fa-magic"></i> Autogenerar Código
                            </button>
                        </div>
                    </x-adminlte-card>
                </div>
                <div class="col-md-6">
                    <x-adminlte-card theme="dark" title="Nombre del Producto">
                        <p id="nombre-mostrar-producto" class="mb-0 fw-bold text-dark"></p>
                        <!-- Botón para cambiar el nombre -->
                        <div class="mt-2 d-flex flex-column justify-content-center">
                            <button class="btn btn-primary" id="cambiar-nombre-producto-visualizar">
                                <i class="fas fa-edit"></i> Cambiar Nombre
                            </button>
                        </div>

                    </x-adminlte-card>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Proveedor</label>
                    <p id="proveedor-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Marca</label>
                    <p id="marca-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Línea</label>
                    <p id="linea-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
                <div class="col-md-12 mt-3">
                    <button class="btn btn-primary" id="cambiar-proveedor-producto-visualizar" modal-toggle="modal" data-target="#ver-distribuidora-proveedor-producto">
                        <i class="fas fa-edit"></i> Cambiar Proveedor, Marca o Línea
                    </button>
                </div>
            </div>

            <!-- Descripción -->
            <div class="mt-3">
                <label class="form-label text-muted">Descripción</label>
                <p id="descripcion-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                <div class="mt-2 d-flex flex-column justify-content-center">
                    <button class="btn btn-primary" id="cambiar-descripcion-producto-visualizar">
                        <i class="fas fa-edit"></i> Cambiar Descripción
                    </button>
                </div>
            </div>

            <!-- Cantidad y Precio -->
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Cantidad</label>
                    <p id="cantidad-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Descripción de Cantidad</label>
                    <p id="descripcion-cantidad-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Precio de Compra</label>
                    <p id="precio-compra-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                    <div class="mt-2 d-flex flex-column justify-content-center">
                        <button class="btn btn-primary" id="cambiar-precio-compra-producto-visualizar">
                            <i class="fas fa-edit"></i> Cambiar Precio de Compra
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Descripción de Compra</label>
                    <p id="descripcion-mostrar-compra-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                    <div class="mt-2 d-flex flex-column justify-content-center">
                        <button class="btn btn-primary" id="cambiar-descripcion-compra-producto-visualizar">
                            <i class="fas fa-edit"></i> Cambiar Descripción de Compra
                        </button>
                    </div>
                </div>
            </div>

            <!-- Presentación y Promoción -->
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Presentación</label>
                    <p id="presentacion-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                    <div class="mt-2 d-flex flex-column justify-content-center">
                        <button class="btn btn-primary" id="cambiar-presentacion-producto-visualizar">
                            <i class="fas fa-edit"></i> Cambiar Presentación
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Promoción</label>
                    <p id="promocion-mostrar-producto" class="form-control-plaintext fw-semibold text-dark"></p>
                </div>
            </div>

            <!-- Formas de Venta (Tabla) -->
            <div class="mt-4">
                <label class="form-label text-muted">Formas de Venta</label>
                <table class="table table-sm table-striped table-bordered text-white bg-dark">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Forma de Venta</th>
                            <th>Precio</th>
                            <th>Conversión Stock</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-formas-venta-mostrar-producto">
                        <!-- Contenido dinámico -->
                    </tbody>
                </table>
            </div>
        </div>

        <x-slot name="footerSlot">
            <div class="w-100 d-flex justify-content-between" id="botones-modal-visualizar-editar">
                <x-adminlte-button theme="danger" id="cerrar-modal-actualizar-vista" icon="fas fa-times" label="Cerrar" class="rounded-3 px-4 py-2" data-dismiss="modal"/>
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
            <button class="btn btn-success" id="boton-agregar-producto" data-toggle="modal" data-target="#agregar-producto" style="border-radius: 8px;">
                <i class="fas fa-plus me-2"></i> Crear nuevo producto
            </button>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar todos los productos de la distribuidora.
        </p>
    </div>


    <!--TABLA DE PRODUCTOS-->

    <div class="container pb-5">
        <table class="table table-bordered table-hover table-striped" id="tabla-productos">
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
            <tbody>
                <tr>
                    <td colspan="8" class="text-center">Cargando productos...</td>
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
            $('#tabla-productos').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                pageLength: 5,
                lengthMenu: [ [5, 10, 25, 50], [5, 10, 25, 50] ],
                "ajax": {
                    "url": "{{ route('administrador.productos.index') }}",
                    "type": "GET",
                },
                columns:[
                    { data: 'codigo'},
                    { data: 'imagen', orderable: false, searchable:false},
                    { data: 'nombre_producto'},
                    { data: 'marca', orderable: false, searchable:false},
                    { data: 'stock', orderable: false, searchable:false},
                    { data: 'formas_venta', orderable: false, searchable:false},
                    { data: 'promocion_vista', orderable: false, searchable:false},
                    { data: 'acciones', orderable: false, searchable:false}
                ],
                
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

        $('#autogenerar-codigo').click(function () {
            Swal.fire({
                title: 'Generando código...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('productos.autogenerar_codigo') }}",
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    Swal.close();
                    $('#codigo').val(data.codigo);
                },
                error: function(
                    xhr, status, error
                ) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo autogenerar el código.',
                    });
                    console.error('Error al autogenerar el código:', error);
                }
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
                "language": {
                    "url": "/i18n/es-ES.json"
                },
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('administrador.formaventas.index', ':id') }}".replace(':id', idProducto),
                    "type": "GET",
                },
                "columns": [
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


        $('#cambiar-foto-producto').click(async function(){
            let idProducto = $('#id-producto-cambiar').val();
            const { value: file } = await Swal.fire({
                    title: "Selecciona una nueva imagen",
                    input: "file",
                    inputAttributes: {
                        "accept": "image/*",
                        "aria-label": "Busca una imagen"
                    }
                });
            if (file) {
                const reader = new FileReader();
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('foto_producto', file);
                $.ajax({
                    url: "{{ route('administrador.productos.editarFotografia', ':id') }}".replace(':id', idProducto),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        reader.onload = function(e) {
                            $('#foto_producto_visualizar').attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                        Swal.fire({
                            icon: 'success',
                            title: 'Foto actualizada',
                            text: 'La foto del producto se ha actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al actualizar foto',
                            text: xhr.responseJSON.message || 'Ocurrió un error al actualizar la foto.',
                        });
                    }
                });
            }
        });

        $('#cambiar-codigos-producto-visualizar').click(function () {
            let idProducto = $('#id-producto-cambiar').val();
            $('#cerrar-modal-actualizar-vista').click();

            setTimeout(() => {
                Swal.fire({
                    title: 'Cambiar Código del Producto',
                    html: `
                        <label for="codigo_producto_nuevo" class="swal2-label">Nuevo Código</label>
                        <input type="text" id="codigo_producto_nuevo" class="swal2-input" placeholder="Ej: 1234567890" required>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    preConfirm: () => {
                        const input = Swal.getPopup().querySelector('#codigo_producto_nuevo');
                        const nuevoCodigo = input.value.trim();

                        if (!nuevoCodigo) {
                            Swal.showValidationMessage('Por favor, ingresa un código válido');
                            return false;
                        }

                        return { nuevoCodigo, idProducto };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Actualizando código del producto...',
                            html: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: "{{ route('administrador.productos.updateCodigo', ':id') }}".replace(':id', result.value.idProducto),
                            type: 'POST',
                            data: {
                                codigo_producto: result.value.nuevoCodigo,
                                _token: "{{ csrf_token() }}",
                                _method: 'PUT'
                            },
                            success: function (response) {
                                $('#codigo-mostrar-producto').text(response.codigo);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Código actualizado',
                                    text: 'El código del producto se ha actualizado correctamente.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    didClose: () => {
                                        $('#tabla-productos').DataTable().ajax.reload(null, false);
                                    }
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al actualizar código',
                                    text: xhr.responseJSON?.message || 'Ocurrió un error al actualizar el código.',
                                });
                            }
                        });
                    }
                });
            }, 200);
        });


        $('#autogenerar-codigo-producto-visualizar').click(function() {
            let idProducto = $('#id-producto-cambiar').val();
            Swal.fire({
                title: 'Autogenerar Código del Producto',
                text: '¿Estás seguro de que deseas autogenerar un nuevo código para este producto?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, autogenerar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Autogenerando código...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.productos.autogenerarCodigo', ':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            $('#codigo-mostrar-producto').text(response.codigo);
                            Swal.fire({
                                icon: 'success',
                                title: 'Código autogenerado',
                                text: 'El código del producto se ha autogenerado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al autogenerar código',
                                text: xhr.responseJSON.message || 'Ocurrió un error al autogenerar el código.',
                            });
                        }
                    });
                }
            });
        });

        $('#cambiar-nombre-producto-visualizar').click(function(){
            let idProducto = $('#id-producto-cambiar').val();
            $('#cerrar-modal-actualizar-vista').click();
            setTimeout(() => {
                Swal.fire({
                    title: 'Cambiar Nombre del Producto',
                    html: `
                        <label for="nuevo_nombre_producto" class="swal2-label">Nuevo Nombre</label>
                        <input type="text" id="nuevo_nombre_producto" class="swal2-input" placeholder="Ej: Jabón Líquido" required>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    preConfirm: () => {
                        const input = Swal.getPopup().querySelector('#nuevo_nombre_producto');
                        const nuevoNombre = input.value.trim();

                        if (!nuevoNombre) {
                            Swal.showValidationMessage('Por favor, ingresa un nombre válido');
                            return false;
                        }

                        return { nuevoNombre, idProducto };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Actualizando nombre del producto...',
                            html: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: "{{ route('administrador.productos.updateNombre', ':id') }}".replace(':id', result.value.idProducto),
                            type: 'POST',
                            data: {
                                nombre_producto: result.value.nuevoNombre,
                                _token: "{{ csrf_token() }}",
                                _method: 'PUT'
                            },
                            success: function(response) {
                                $('#nombre-mostrar-producto').text(response.nombre);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Nombre actualizado',
                                    text: 'El nombre del producto se ha actualizado correctamente.',
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
                                    title: 'Error al actualizar nombre',
                                    text: xhr.responseJSON?.message || 'Ocurrió un error al actualizar el nombre.',
                                });
                            }
                        });
                    }
                });
            }, 200); // Delay para evitar el bug del aria-hidden
        });


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


        $('#cambiar-proveedor-producto-visualizar').click(function(){
            let idProducto = $('#id-producto-cambiar').val();
            $('#cerrar-modal-actualizar-vista').click();
            setTimeout(() => {
            Swal.fire({
                title: 'Cambiar proveedor del producto',
                html: `
                    <div class="form-group text-left">
                        <label for="proveedor_producto-visualizar-editar" class="swal2-label">Seleccionar Proveedor</label>
                        <select id="proveedor_producto-visualizar-editar" class="form-control form-select mb-2">
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-left">
                        <label for="proveedor-marca-visualizar-editar" class="swal2-label">Seleccionar Marca</label>
                        <select id="proveedor-marca-visualizar-editar" class="form-control form-select mb-2" disabled>
                            <option value="">Seleccione una marca</option>
                        </select>
                    </div>
                    <div class="form-group text-left">
                        <label for="proveedor_producto-linea-visualizar-editar" class="swal2-label">Seleccionar Línea</label>
                        <select id="proveedor_producto-linea-visualizar-editar" class="form-control form-select" disabled>
                            <option value="">Seleccione una línea</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar Cambios',
                cancelButtonText: 'Cerrar',
                allowOutsideClick: false,
                didOpen: () => {
                    // Cuando abra el Swal, asignamos eventos a selects:
                    $('#proveedor_producto-visualizar-editar').on('change', function() {
                        cargarMarcasYLineasVisualizarEditar(this);
                    });
                    $('#proveedor-marca-visualizar-editar').on('change', function() {
                        cargarLineasVisualizarEditar(this);
                    });
                },
                preConfirm: () => {
                    // Validar campos al hacer click en guardar
                    let idProveedor = $('#proveedor_producto-visualizar-editar').val();
                    let idMarca = $('#proveedor-marca-visualizar-editar').val();
                    let idLinea = $('#proveedor_producto-linea-visualizar-editar').val();

                    if (!idProveedor || !idMarca || !idLinea) {
                        Swal.showValidationMessage('Por favor, complete todos los campos.');
                        return false;
                    }

                    return { idProveedor, idMarca, idLinea };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hacer AJAX para guardar los cambios
                    let { idProveedor, idMarca, idLinea } = result.value;

                    Swal.fire({
                        title: 'Guardando cambios...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $.ajax({
                                url: "{{ route('administrador.productos.updateProveedor', ':id') }}".replace(':id', idProducto),
                                type: 'POST',
                                data: {
                                    id_proveedor: idProveedor,
                                    id_marca: idMarca,
                                    id_linea: idLinea,
                                    _token: "{{ csrf_token() }}",
                                    _method: 'PUT'
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Proveedor actualizado',
                                        text: 'El proveedor del producto se ha actualizado correctamente.',
                                        timer: 2000,
                                        showConfirmButton: false,
                                        didClose: () => {
                                            $('#tabla-productos').DataTable().ajax.reload(null, false);
                                        }
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error al actualizar proveedor',
                                        text: xhr.responseJSON?.message || 'Ocurrió un error al actualizar el proveedor.',
                                    });
                                }
                            });
                        }
                    });
                }
            });
            }, 200); // Delay para evitar el bug del aria-hidden
        });

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


        $('#cambiar-descripcion-producto-visualizar').click(function(e){
            $('#cerrar-modal-actualizar-vista').click();
            let idProducto = $('#id-producto-cambiar').val();
            setTimeout(() => {
                Swal.fire({
                    title: 'Cambiar Descripción del Producto',
                    html: `
                        <label for="descripcion_producto" class="swal2-label">Nueva Descripción</label>
                        <textarea id="descripcion_producto" class="swal2-textarea" placeholder="Ingrese una nueva descripción" required></textarea>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    preConfirm: () => {
                        const descripcionProducto = $('#descripcion_producto').val();
                        if (!descripcionProducto) {
                            Swal.showValidationMessage('Por favor, ingresa una descripción válida');
                            return false;
                        }
                        return { descripcionProducto, idProducto };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Actualizando descripción del producto...',
                            html: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: "{{ route('administrador.productos.updateDescripcion', ':id') }}".replace(':id', idProducto),
                            type: 'POST',
                            data: {
                                descripcion_producto: result.value.descripcionProducto,
                                _token: "{{ csrf_token() }}",
                                _method:'PUT'
                            },
                            success: function(response) {
                                $('#descripcion-mostrar-producto').text(response.descripcion);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Descripción actualizada',
                                    text: 'La descripción del producto se ha actualizado correctamente.',
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
                                    title: 'Error al actualizar descripción',
                                    text: xhr.responseJSON.message || 'Ocurrió un error al actualizar la descripción.',
                                });
                            }
                        });
                    }
                });
            }, 200); // Delay para evitar el bug del aria-hidden
        });

        $('#cambiar-precio-compra-producto-visualizar').click(function(){
            $('#cerrar-modal-actualizar-vista').click();
            let idProducto = $('#id-producto-cambiar').val();
            setTimeout(() => {
            Swal.fire({
                title: 'Cambiar Precio de Compra del Producto',
                html: `
                    <label for="precio_compra_producto" class="swal2-label">Nuevo Precio de Compra</label>
                    <input type="number" id="precio_compra_producto" class="swal2-input" placeholder="Ej: 100.00" required>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const precioCompraProducto = $('#precio_compra_producto').val();
                    if (!precioCompraProducto || isNaN(precioCompraProducto) || parseFloat(precioCompraProducto) <= 0) {
                        Swal.showValidationMessage('Por favor, ingresa un precio válido');
                        return false;
                    }
                    return { precioCompraProducto, idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('administrador.productos.updatePrecioCompraProducto', ':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            precio_compra_producto: result.value.precioCompraProducto,
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            $('#precio-compra-mostrar-producto').text(response.precio_compra);
                            $('#descripcion-mostrar-compra-producto').text(response.detalle_precio_compra);
                            Swal.fire({
                                icon: 'success',
                                title: 'Precio de compra actualizado',
                                text: 'El precio de compra del producto se ha actualizado correctamente.',
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
                                title: 'Error al actualizar precio de compra',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar el precio de compra.',
                            });
                        }
                    });
                }
            });
            }, 200); // Delay para evitar el bug del aria-hidden
        });
        $('#cambiar-descripcion-compra-producto-visualizar').click(function(){
            $('#cerrar-modal-actualizar-vista').click();
            let idProducto = $('#id-producto-cambiar').val();
            setTimeout(() => {
            Swal.fire({
                title: 'Cambiar Descripción del Precio de Compra',
                html: `
                    <label for="descripcion_precio_compra_producto" class="swal2-label">Nueva Descripción</label>
                    <textarea id="descripcion_precio_compra_producto" class="swal2-textarea" placeholder="Ingrese una nueva descripción" required></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const descripcionPrecioCompraProducto = $('#descripcion_precio_compra_producto').val();
                    if (!descripcionPrecioCompraProducto) {
                        Swal.showValidationMessage('Por favor, ingresa una descripción válida');
                        return false;
                    }
                    return { descripcionPrecioCompraProducto, idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('administrador.productos.updatePrecioDescripcionProducto', ':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            descripcion_precio_compra_producto: result.value.descripcionPrecioCompraProducto,
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            $('#descripcion-mostrar-compra-producto').text(response.detalle_precio_compra);
                            Swal.fire({
                                icon: 'success',
                                title: 'Descripción del precio de compra actualizada',
                                text: 'La descripción del precio de compra del producto se ha actualizado correctamente.',
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
                                title: 'Error al actualizar descripción del precio de compra',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar la descripción del precio de compra.',
                            });
                        }
                    });
                }
            });
            }, 200); // Delay para evitar el bug del aria-hidden
        });
        $('#cambiar-presentacion-producto-visualizar').click(function(){
            $('#cerrar-modal-actualizar-vista').click();
            let idProducto = $('#id-producto-cambiar').val();
            setTimeout(() => {
            Swal.fire({
                title: 'Cambiar Presentación del Producto',
                html: `
                    <label for="presentacion_producto" class="swal2-label">Nueva Presentación</label>
                    <input type="text" id="presentacion_producto" class="swal2-input" placeholder="Ej: Botella de 500ml" required>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: () => {
                    const presentacionProducto = $('#presentacion_producto').val();
                    if (!presentacionProducto) {
                        Swal.showValidationMessage('Por favor, ingresa una presentación válida');
                        return false;
                    }
                    return { presentacionProducto, idProducto };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('administrador.productos.updatePresentacionProducto', ':id') }}".replace(':id', idProducto),
                        type: 'POST',
                        data: {
                            presentacion_producto: result.value.presentacionProducto,
                            _token: "{{ csrf_token() }}",
                            _method:'PUT'
                        },
                        success: function(response) {
                            $('#presentacion-mostrar-producto').text(response.presentacion);
                            Swal.fire({
                                icon: 'success',
                                title: 'Presentación actualizada',
                                text: 'La presentación del producto se ha actualizado correctamente.',
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
                                title: 'Error al actualizar presentación',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar la presentación.',
                            });
                        }
                    });
                }
            });
            }, 200); // Delay para evitar el bug del aria-hidden
        });

        $('#limpiarboton').click(function() {
            window.location.href = "{{ route('administrador.productos.index') }}";
        });


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