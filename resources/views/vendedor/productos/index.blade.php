@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de Mis Asignaciones
            </span>
        </div>
    </div>
    
@stop

@section('content')

    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f9f9fb; border-radius: 16px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #2c3e50; color: #ffffff; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i> Opciones de búsqueda
                </h5>
            </div>
            <div class="card-body" style="padding: 2rem;">
                <p class="text-muted" style="margin-top: -15px">
                    Puedes buscar el producto por nombre completo o por código con cualquier coincidencia.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label text-muted">Nombre</label>
                        <input type="text" class="form-control shadow-sm border-0" name="nombre" placeholder="Ej: Chocolate" value="{{ request('nombre')}}"  style="border-radius: 8px;">
                    </div>
                    <div class="col-md-6">
                        <label for="ci" class="form-label text-muted">Código</label>
                        <input type="text" class="form-control shadow-sm border-0" name="codigo" placeholder="Ej: 12345678" value="{{ request('codigo') }}"  style="border-radius: 8px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <table class="table table-striped table-bordered table-hover" id="productosTable" style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center">Cod.</th>
                    <th class="text-center">Imagen</th>
                    <th class="text-center">Nombre Producto</th>
                    <th class="text-center">Descripción</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Forma Venta</th>
                    <th class="text-center">Promocion</th>
                </tr>
            </thead>
        </table>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.css">

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/r-2.5.0/datatables.min.js"></script>


    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#productosTable').DataTable({
                processing:true,
                serverSide:true,
                responsive: true,
                autoWidth: false,
                language: {
                    url: '/i18n/es-ES.json'
                },
                pageLength: 5,
                lengthMenu: [ [5, 10, 25, 50], [5, 10, 25, 50] ],
                "ajax": {
                    "url": "{{ route('preventistas.productos.vendedor.obtenerProductos') }}",
                    "type": "GET",
                },
                columns: [
                    { data: 'codigo',           name: 'codigo' },
                    { data: 'imagen',           name: 'imagen', orderable: false, searchable: false },
                    { data: 'nombre_producto',  name: 'nombre_producto' },
                    { data: 'marca',            name: 'marca', orderable: false, searchable: false },
                    { data: 'stock',            name: 'stock', orderable: false, searchable: false },
                    { data: 'formas_venta',     name: 'formas_venta', orderable: false, searchable: false },
                    { data: 'promocion_vista',  name: 'promocion_vista', orderable: false, searchable: false },
                ],
                columnDefs: [
                    { targets: [0,1,2,3,4,5,6], className: 'align-middle text-center' },
                    { targets: [2,3], className: 'align-middle' } // si no quieres centrar nombre/descr, ajusta esto
                ],
                order: [[0, 'asc']]
            });
        });
    </script>

    <script>
        $('#limpiarboton').on('click', function() {
            window.location.href = "{{ route('preventistas.productos.vendedor.obtenerProductos') }}";
        });

        @if($contar_productos_promocion > 0)
            Swal.fire({
                title: '¡Atención!',
                text: 'Tienes {{ $contar_productos_promocion }} productos en promoción.',
                icon: 'info',
                confirmButtonText: 'Ver detalles',
                cancelButtonText: 'Cerrar',
                showCancelButton: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-danger'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Cargando...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    html=``;
                    $.ajax({
                        url: "{{ route('preventistas.productos.vendedor.verDetalleProductosPromocion') }}",
                        type: "GET",
                        success: function(data) {
                            let html = '';

                            data.productos.forEach(function(producto) {
                                const imagen = producto.foto_producto 
                                    ? `{{ route('productos.imagen', ':id') }}`.replace(':id', producto.id) 
                                    : 'https://via.placeholder.com/150';

                                html += `
                                    <div class="card mb-4 shadow" style="max-width: 600px; margin: 0 auto;">
                                        <div class="row g-0">
                                            <div class="col-md-4 d-flex align-items-center justify-content-center p-3">
                                                <img src="${imagen}" alt="Producto" class="img-fluid rounded" style="max-height: 150px;">
                                            </div>
                                            <div class="col-md-8">
                                                <div class="card-body">
                                                    <h5 class="card-title text-primary mb-3">
                                                        <i class="fas fa-box-open me-2"></i> ${producto.nombre_producto}
                                                    </h5>

                                                    <p class="card-text mb-2">
                                                        <i class="fas fa-align-left me-2 text-muted"></i> ${producto.descripcion_producto}
                                                    </p>

                                                    <p class="card-text mb-2">
                                                        <i class="fas fa-barcode me-2 text-dark"></i> <strong>Código:</strong> ${producto.codigo}
                                                    </p>

                                                    <p class="card-text mb-2">
                                                        <i class="fas fa-tags me-2 text-success"></i> <strong>Promoción:</strong> ${producto.promocion ? 'Sí' : 'No'}
                                                    </p>

                                                    ${producto.promocion ? `
                                                        <p class="card-text mb-2">
                                                            <i class="fas fa-percent me-2 text-danger"></i> <strong>Descuento:</strong> ${producto.descripcion_descuento_porcentaje} %
                                                        </p>
                                                        <p class="card-text mb-2">
                                                            <i class="fas fa-gift me-2 text-info"></i> <strong>Regalo:</strong> ${producto.descripcion_regalo ? producto.descripcion_regalo : 'No existe'}
                                                        </p>
                                                    ` : ''}

                                                    <p class="card-text mt-3">
                                                        <i class="fas fa-cubes me-2 text-primary"></i> <strong>Cantidad:</strong> ${producto.cantidad} ${producto.detalle_cantidad}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;


                            });

                            Swal.fire({
                                title: 'Productos en Promoción',
                                html: html,
                                icon: 'info',
                                confirmButtonText: 'Cerrar',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Algo salió mal'
                            });
                        }
                    });
                }
            });
        @endif
    </script>

    <script>
        function verDetalleFormaVenta(e){
            const idProducto = e.getAttribute('id-producto');
            Swal.fire({
                title: 'Cargando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('preventistas.productos.vendedor.verFormasVenta',':id') }}".replace(':id', idProducto),
                type: "GET",
                success: function(data) {
                    let html = `<table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Forma de Venta</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    data.formas_venta.forEach(function(formaVenta) {
                        html += `
                        <tr>
                            <td>${formaVenta.tipo_venta}</td>
                            <td>${formaVenta.precio_venta}</td>
                        </tr>`;
                    });
                    html += `</tbody></table>`;
                    Swal.fire({
                        title: 'Detalle de Forma de Venta',
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Cerrar',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Algo salió mal'
                    });
                }
            });
        }

        function verDetallePromocion(e){
            const idProducto = e.getAttribute('id-producto');
            Swal.fire({
                title: 'Cargando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('preventistas.productos.vendedor.verDetallePromocion',':id') }}".replace(':id', idProducto),
                type: "GET",
                success: function(data) {
                    let html = `<p><strong>Descuento:</strong> ${data.producto.descripcion_descuento_porcentaje} %</p>`;
                    if (data.producto.descripcion_regalo) {
                        html += `<p><strong>Regalo:</strong> ${data.producto.descripcion_regalo}</p>`;
                    } else {
                        html += `<p><strong>Regalo:</strong> No existe</p>`;
                    }
                    Swal.fire({
                        title: 'Detalle de Promoción',
                        html: html,
                        icon: 'info',
                        confirmButtonText: 'Cerrar',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Algo salió mal'
                    });
                }
            });
        }
    </script>
@stop