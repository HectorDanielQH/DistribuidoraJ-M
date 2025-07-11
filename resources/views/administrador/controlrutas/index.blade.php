@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container py-4" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de asignaciones
            </span>
        </div>
    </div>
     <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-route me-2"></i> Control de Rutas a Preventistas
            </h2>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar las rutas asignadas a los vendedores, permitiendo una mejor organización y control de las asignaciones.
        </p>
    </div>
@stop

@section('content')

    <div class="container">
        <div class="table-responsive rounded shadow-sm" style="overflow-x: auto;">
            <h3 class="text-center mb-4" style="font-size: 1.5rem; font-weight: 600;">
                <i class="fas fa-users me-2"></i> Información de Clientes Asignados a Preventistas
            </h3>
            <div  class="mb-4 d-flex flex-column justify-content-center align-items-center">
                <label 
                    for="vendedorSelectControl" 
                    class="form-label" 
                >
                    Seleccione un vendedor para ver sus clientes asignados:
                </label>
                <select id="vendedorSelectControl" class="form-select mb-3" style="width: 300px;" onchange="valordeusuariovendedor(this)">
                    <option value="" disabled selected>Seleccione un vendedor</option>
                    @foreach($vendedores as $vendedor)
                        <option value="{{ $vendedor->id }}" data-id="{{ $vendedor->id }}">{{ $vendedor->nombres }} {{ $vendedor->apellido_paterno }} {{ $vendedor->apellido_materno }}</option>
                    @endforeach
                </select>
            </div>
            <table id="tabla-asignaciones" class="table table-bordered align-middle text-center" style="min-width: 800px;">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">C.I.</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Ubicación</th>
                        <th scope="col">Ruta</th>
                        <th scope="col">F. Asignacion</th>
                        <th scope="col">F. Atencion</th>
                        <th scope="col">Pedido</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
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
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.2/datatables.min.js" integrity="sha384-JRUjeYWWUGO171YFugrU0ksSC6CaWnl4XzwP6mNjnnDh4hfFGRyYbEXwryGwLsEp" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-asignaciones').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing":true,
                "serverSide":true,
                "ajax": {
                    "url": "{{ route('administrador.controlrutas.index') }}",
                    "type": "GET",
                },
                columns:[
                    {data: 'id', orderable: false, searchable: false},
                    {data: 'ci', name: 'ci'},
                    {data: 'nombre_completo', name: 'nombre_completo'},
                    {data: 'ubicacion', name: 'ubicacion'},
                    {data: 'ruta', name: 'ruta'},
                    {data: 'fecha_asignacion', name: 'fecha_asignacion'},
                    {data: 'fecha_atencion', name: 'fecha_atencion'},
                    {data: 'pedido', name: 'pedido'}
                ],
                
            });
        });
    </script>

    <script>
        $('#vendedorSelectControl').select2({
            placeholder: "Seleccione un vendedor",
            width: '100%',
            theme: 'classic',
            parents: true
        });

        function valordeusuariovendedor(e){
            let id = $(e).val();
            let url = "{{ route('administrador.controlrutas.preventista', ':id') }}".replace(':id', id);
            $('#tabla-asignaciones').DataTable().destroy();
            $('#tabla-asignaciones').DataTable({
                language: {
                    url: '/i18n/es-ES.json'
                },
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": url,
                    "type": "GET",
                },
                columns: [
                    {data: 'id', orderable: false, searchable: false},
                    {data: 'ci', name: 'ci'},
                    {data: 'nombre_completo', name: 'nombre_completo'},
                    {data: 'ubicacion', name: 'ubicacion'},
                    {data: 'ruta', name: 'ruta'},
                    {data: 'fecha_asignacion', name: 'fecha_asignacion'},
                    {data: 'fecha_atencion', name: 'fecha_atencion'},
                    {data: 'pedido', name: 'pedido'}
                ],
                "order": [[0, "desc"]]
            });
        }
    </script>
        
@stop