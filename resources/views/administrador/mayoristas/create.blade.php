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
        </div>
    </div>
@stop

@section('content')
    <div class="container pb-5">
        <h2 class="mb-4">Crear Producto Mayorista</h2>
        <!-- Aquí va el formulario para crear un producto mayorista -->
         <form>
            <!-- Campos del formulario -->
            <div class="mb-3">
                <label for="codigoProducto" class="form-label">Código de Producto</label>
                <select class="form-control" id="codigoProducto">
                    <option value="">Seleccione un producto</option>
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="nombreProducto" class="form-label">Tipo de Venta</label>
                    <input type="text" class="form-control" id="nombreProducto" placeholder="Ej: BOLSA, JAVA, ETC">
                </div>
                <div class="col-6">
                    <label for="precioVenta" class="form-label">Precio de Venta</label>
                    <input type="number" step="0.01" class="form-control" id="precioVenta" placeholder="Ingrese el precio de venta">
                </div>
            </div>
            <div class="row mb3">
                <div class="col-6">
                    <label for="precioMayorista" class="form-label">Equivalencia Cantidad (Descuento Inventario)</label>
                    <input type="number" class="form-control" id="precioMayorista" placeholder="Ingrese la cantidad para venta mayorista" min="0">
                </div>
                <div class="col-6">
                    <label for="cantidadMinima" class="form-label">Cantidad Mínima de Venta</label>
                    <input type="number" class="form-control" id="cantidadMinima" placeholder="Ingrese la cantidad mínima para venta mayorista">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="estadoProducto" class="form-label">Estado del Producto</label>
                    <select class="form-control" id="estadoProducto">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="col-6">
                    <label for="imagenProducto" class="form-label">Imagen del Producto</label>
                    <image src="{{ asset('images/logo_color.webp') }}" alt="Imagen del Producto" id="imagenProducto" class="img-fluid rounded" style="max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Producto Mayorista</button


         </form>
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
        $('#codigoProducto').select2({
            placeholder: 'Seleccione un producto',
            minimumInputLength: 2, // Recomendado para no saturar el servidor
            ajax: {
                url: '{{ route("administrador.mayoristas.buscarProductoMayorista") }}',
                dataType: 'json',
                delay: 250, // Espera 250ms antes de disparar la petición
                data: function (params) {
                    return {
                        palabra_clave: params.term // Select2 envía el texto escrito aquí
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (producto) {
                            return {
                                id: producto.id,
                                text: producto.codigo + ' - ' + producto.nombre_producto+' - '+producto.descripcion_producto + ' - PRESENTACION: ' + producto.presentacion
                            };
                        })
                    };
                },
                cache: true
            }
        });
    </script>
@stop