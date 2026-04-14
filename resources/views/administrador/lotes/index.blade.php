@extends('adminlte::page')

@section('title', 'Lotes')

@section('content_header')
    <div class="inventory-simple-header">
        <div>
            <span>Inventario</span>
            <h1>Lotes de productos</h1>
            <p>Revisa ingresos de mercaderia y entra al detalle de cada lote.</p>
        </div>
        <a href="{{ route('administrador.lotes.create') }}" class="btn btn-success inventory-simple-btn">
            <i class="fas fa-plus"></i> Nuevo lote
        </a>
    </div>
    <div class="container py-4 inventory-legacy-header" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de Lotes
            </span>
            <a
                href={{ route('administrador.lotes.create') }}
                class="btn btn-success mt-3 mb-2 px-4 py-2"
                id="agregar-nuevo-lote-legacy"
                style="border-radius: 8px;"
            >
                    <i class="fas fa-plus"></i> Agregar nuevo lote de productos
            </a>
        </div>
    </div>
@stop

@section('content')

    <!--REGISTRO DE PRODUCTO-->
    <x-adminlte-modal id="agregar-lote" size="lg" theme="dark" icon="fas fa-plus-circle" title="Agregar lote">
            <div class="modal-body px-4">
                <form id="registro-producto" enctype="multipart/form-data">
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
                        <div class="col-md-12">
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


    <div class="lot-summary">
        <article><span>Lotes activos</span><strong>{{ $resumenLotes['lotes'] ?? 0 }}</strong></article>
        <article><span>Ingresos registrados</span><strong>{{ $resumenLotes['items'] ?? 0 }}</strong></article>
        <article><span>Unidades ingresadas</span><strong>{{ $resumenLotes['unidades'] ?? 0 }}</strong></article>
        <article><span>Anulados historicos</span><strong>{{ $resumenLotes['anulados'] ?? 0 }}</strong></article>
    </div>

    <div class="container pb-5 lot-table-shell">
        <table class="table table-bordered table-hover table-striped" id="tabla-lotes">
            <thead>
                <tr>
                    <th>Lote</th>
                    <th>Items</th>
                    <th>Cantidad total</th>
                    <th>Ultimo ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">Cargando lotes...</td>
                </tr>
            </tbody>
        </table>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.css" rel="stylesheet">


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

        .content-wrapper {
            background: #eef3f1;
        }

        .inventory-legacy-header {
            display: none;
        }

        .inventory-simple-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
        }

        .inventory-simple-header span {
            color: #15803d;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .inventory-simple-header h1 {
            margin: 0;
            color: #17211d;
            font-size: 1.65rem;
            font-weight: 900;
        }

        .inventory-simple-header p {
            margin: 4px 0 0;
            color: #64748b;
            font-weight: 700;
        }

        .inventory-simple-btn {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }
        .lot-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .lot-summary article, .lot-table-shell {
            background: #ffffff;
            border: 1px solid #d7e4df;
            border-radius: 8px;
            padding: 14px;
        }
        .lot-summary span {
            display: block;
            color: #64748b;
            font-weight: 900;
        }
        .lot-summary strong {
            color: #17211d;
            font-size: 1.35rem;
            font-weight: 900;
        }
        .lot-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .lot-action-btn {
            border-radius: 8px;
            font-weight: 900;
        }

        @media (max-width: 767.98px) {
            .inventory-simple-header {
                flex-direction: column;
            }

            .inventory-simple-btn {
                width: 100%;
            }
            .lot-summary {
                grid-template-columns: 1fr;
            }
        }

    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js"></script>

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
                    {data: 'items', name: 'items', orderable: false, searchable: false},
                    {data: 'cantidad', name: 'cantidad', orderable: false, searchable: false},
                    {data: 'ultimo_ingreso', name: 'ultimo_ingreso'},
                    {data: 'acciones', name: 'acciones', orderable: false, searchable: false},
                ],
                
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
    </script>
@stop
