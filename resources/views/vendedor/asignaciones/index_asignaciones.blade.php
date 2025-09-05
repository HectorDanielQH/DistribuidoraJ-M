@extends('adminlte::page')

@section('title', 'Mis Asignaciones')

@section('content_header')
  <div class="container py-3"
       style="background: linear-gradient(135deg,#2c3e50,#34495e); border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12);">
    <div class="d-flex flex-column justify-content-center align-items-center text-center">
      <h1 class="text-white mb-1" style="font-size:1.6rem; font-weight:800; letter-spacing:.3px">
        <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J
      </h1>
      <span class="text-white-50" style="font-size:.95rem">Panel de Mis Asignaciones</span>
    </div>
  </div>
@stop

@section('content')

  <!-- Filtros (compactos y sticky en mobile) -->
  <div class="container mt-3">
    <div class="card shadow-sm border-0 sticky-filters">
      <div class="card-header d-flex justify-content-between align-items-center"
           style="background:#2c3e50; color:#fff; border-radius:12px 12px 0 0;">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Búsqueda</h5>
        @if ($eliminar_busqueda)
          <button class="btn btn-danger btn-sm" id="limpiarboton">
            <i class="fas fa-times"></i> Limpiar
          </button>
        @endif
      </div>
      <div class="card-body py-3">
        <form method="GET" action="{{ route('asignacionvendedor.index') }}">
          <div class="form-row">
            <div class="col-12 col-md-5 mb-2">
              <label for="nombre" class="sr-only">Nombre completo</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                <input type="text" class="form-control" id="nombre" name="nombre"
                       placeholder="Ej: Juan Pérez" value="{{ $request->nombre ?? '' }}">
              </div>
            </div>
            <div class="col-12 col-md-5 mb-2">
              <label for="ci" class="sr-only">Cédula de identidad</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                <input type="text" class="form-control" id="ci" name="ci"
                       placeholder="Ej: 12345678" value="{{ $request->ci ?? '' }}">
              </div>
            </div>
            <div class="col-12 col-md-2 mb-2">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-search"></i> Buscar
              </button>
            </div>
          </div>
        </form>
        <div class="d-flex justify-content-between align-items-center mt-2">
          <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Busca por nombre o CI (coincidencia parcial).</small>
          <a href="{{ route('pedidos.vendedor.obtenerPdfRutas') }}" class="btn btn-dark btn-sm">
            <i class="fas fa-file-pdf me-1"></i> Mis rutas
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal pedidos (optimizado para móvil) -->
  <x-adminlte-modal id="verPedidoModal" size="lg" theme="dark" icon="fas fa-box-open" title="Pedidos realizados" v-centered>
    <div class="modal-body px-3" id="crear-tabla-pedidos"></div>
    <x-slot name="footerSlot">
      <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
    </x-slot>
  </x-adminlte-modal>

  <div class="container py-3">
    @if($asignaciones->isEmpty())
      <div class="alert alert-info text-center mb-0">
        <i class="fas fa-info-circle me-2"></i> No tienes asignaciones registradas.
      </div>
    @else
      <!-- Lista tipo tarjetas (MÓVIL) -->
      <div class="d-block d-md-none">
        <div class="row">
          @foreach($asignaciones as $asignacion)
            @php
              $cliente = $asignacion->cliente;
              $direccion = $cliente->calle_avenida ?? '';
              $maps = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($direccion);
            @endphp
            <div class="col-12 mb-3">
              <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                  <div class="d-flex align-items-start">
                    <div class="avatar-circle mr-3">
                      <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-1" style="font-weight:700">
                          {{ $cliente->nombres }} {{ $cliente->apellidos }}
                        </h5>
                        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($asignacion->asignacion_fecha_hora)->format('d/m H:i') }}</span>
                      </div>

                      <div class="small text-muted mt-1">
                        <div class="mb-1">
                          <i class="fas fa-mobile-alt text-secondary mr-1"></i>
                          <a href="tel:{{ $cliente->celular }}" class="text-reset text-decoration-none">{{ $cliente->celular }}</a>
                        </div>
                        <div class="mb-1">
                          <i class="fas fa-map-pin text-danger mr-1"></i>
                          <a href="{{ $maps }}" target="_blank" class="text-reset text-decoration-none">{{ $direccion ?: 'Sin dirección' }}</a>
                        </div>
                        <div class="mt-1">
                          @if($asignacion->atencion_fecha_hora)
                            <span class="badge bg-success"><i class="fas fa-check-circle mr-1"></i>Atendido</span>
                          @else
                            <span class="badge bg-secondary"><i class="fas fa-clock mr-1"></i>No atendido</span>
                          @endif

                          @if($asignacion->estado_pedido)
                            <span class="badge bg-success ml-1"><i class="fas fa-check mr-1"></i>Pedido</span>
                          @else
                            <span class="badge bg-danger ml-1"><i class="fas fa-times mr-1"></i>Sin pedido</span>
                          @endif
                        </div>
                      </div>

                      <div class="d-grid gap-2 mt-3">
                        @if($asignacion->estado_pedido)
                          <button class="btn btn-outline-info btn-sm btn-ver-pedido" 
                                  data-toggle="modal" data-target="#verPedidoModal"
                                  data-cliente-id="{{ $cliente->id }}">
                            <i class="fas fa-eye mr-1"></i> Ver pedido
                          </button>
                        @endif
                        <button class="btn btn-success btn-block btn-sm btn-atender" data-asignacion-id="{{ $asignacion->id }}">
                          <i class="fas fa-hand-pointer mr-1"></i> Atender
                        </button>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="d-flex justify-content-center mt-2">
          {{ $asignaciones->appends(request()->query())->links() }}
        </div>
      </div>

      <!-- Tabla (DESKTOP / TABLET) -->
      <div class="d-none d-md-block">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Mis Asignaciones</h5>
            <span class="badge bg-light text-dark">
              <i class="fas fa-layer-group me-1"></i> Total: {{ $asignaciones->total() ?? $asignaciones->count() }}
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle text-center mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Ubicación</th>
                    <th>Asignación</th>
                    <th>Atención</th>
                    <th>Pedido</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($asignaciones as $asignacion)
                    @php
                      $cliente = $asignacion->cliente;
                      $direccion = $cliente->calle_avenida ?? '';
                      $maps = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($direccion);
                    @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $cliente->nombres }} {{ $cliente->apellidos }}</td>
                      <td><a href="tel:{{ $cliente->celular }}" class="text-reset"><i class="fas fa-mobile-alt text-secondary me-1"></i>{{ $cliente->celular }}</a></td>
                      <td><a href="{{ $maps }}" target="_blank" class="text-reset"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $direccion }}</a></td>
                      <td><span class="badge bg-primary">{{ $asignacion->asignacion_fecha_hora }}</span></td>
                      <td>
                        @if($asignacion->atencion_fecha_hora)
                          <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>{{ $asignacion->atencion_fecha_hora }}</span>
                        @else
                          <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i> No atendido</span>
                        @endif
                      </td>
                      <td>
                        @if($asignacion->estado_pedido)
                          <span class="badge bg-success"><i class="fas fa-check me-1"></i> Sí</span>
                          <br>
                          <button class="btn btn-outline-info btn-sm mt-2 btn-ver-pedido" 
                                  data-toggle="modal" data-target="#verPedidoModal"
                                  data-cliente-id="{{ $cliente->id }}">
                            <i class="fas fa-eye me-1"></i> Ver Pedido
                          </button>
                        @else
                          @if($asignacion->atencion_fecha_hora)
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> No pidió</span>
                          @else
                            <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i> No atendido</span>
                          @endif
                        @endif
                      </td>
                      <td>
                        <button class="btn btn-success btn-sm btn-atender" data-asignacion-id="{{ $asignacion->id }}">
                          <i class="fas fa-hand-pointer me-1"></i> Atender
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer bg-light text-muted d-flex justify-content-between align-items-center">
            <small><i class="fas fa-info-circle me-1"></i> Se muestran las asignaciones relacionadas a tu cuenta.</small>
            <div>{{ $asignaciones->appends(request()->query())->links() }}</div>
          </div>
        </div>
      </div>
    @endif
  </div>
@stop

@section('css')
  <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
  <style>
    /* Enfasis y accesibilidad */
    input.form-control:focus, select.form-control:focus {
      border-color:#1abc9c; box-shadow:0 0 0 .2rem rgba(26,188,156,.25);
    }
    .card { transition: box-shadow .2s; }
    .card:hover { box-shadow:0 6px 20px rgba(0,0,0,.08); }
    .btn:hover { opacity:.95; }

    /* Sticky filtros en mobile */
    .sticky-filters { position:sticky; top:.5rem; z-index: 9; }

    /* Avatar redondo para tarjetas */
    .avatar-circle{
      width:44px; height:44px; border-radius:50%;
      background:#eef2f5; color:#2c3e50; display:flex; align-items:center; justify-content:center;
      font-size:1rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,.06);
    }

    /* Modal "fullscreen" en móviles (BS4) */
    @media (max-width: 575.98px){
      #verPedidoModal .modal-dialog { margin:0; height:100%; max-width:100%; }
      #verPedidoModal .modal-content { height:100%; border-radius:0; }
      #verPedidoModal .modal-body { overflow-y:auto; }
    }
  </style>
@stop

@section('js')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Limpiar filtros
    $('#limpiarboton').on('click', function(){ window.location.href = "{{ route('asignacionvendedor.index') }}"; });

    // Delegación: Atender
    $(document).on('click', '.btn-atender', function(){
      const idAsignacion = $(this).data('asignacion-id');

      Swal.fire({
        title:'¿Qué deseas hacer?', text:'Puedes crear un pedido o registrar la atención',
        icon:'question', showCancelButton:true, showDenyButton:true,
        confirmButtonText:'Crear Pedido', confirmButtonColor:'#28a745', denyButtonText:'Registrar Atención', cancelButtonText:'Cancelar'
      }).then(res=>{
        if (res.isConfirmed) {
          window.location.href = `{{ route('pedidos.vendedor.crear', ':id') }}`.replace(':id', idAsignacion);
        } else if (res.isDenied) {
          Swal.fire({
            title:'Registrar Atención', text:'¿Confirmas registrar la atención sin pedido?',
            icon:'warning', showCancelButton:true, confirmButtonText:'Sí, registrar', cancelButtonText:'Cancelar'
          }).then(rr=>{
            if (!rr.isConfirmed) return;
            $.ajax({
              url: `{{ route('registrarAtencion.sinpedido', ':id') }}`.replace(':id', idAsignacion),
              type:'POST',
              data:{ _token:'{{ csrf_token() }}', _method:'PUT', id_asignacion:idAsignacion }
            }).done(()=>{
              Swal.fire({icon:'success', title:'Atención registrada', timer:1200, showConfirmButton:false})
                .then(()=> location.reload());
            }).fail(xhr=>{
              Swal.fire({icon:'error', title:'Error', text:(xhr.responseJSON?.message || 'No se pudo registrar la atención.')});
            });
          });
        }
      });
    });

    // Delegación: Ver pedidos del cliente
    $(document).on('click', '.btn-ver-pedido', function(){
      const clienteId = $(this).data('cliente-id');
      $('#crear-tabla-pedidos').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

      $.ajax({
        url: `{{ route('pedidos.vendedor.obtenerPedidosProceso', ':numero') }}`.replace(':numero', clienteId),
        type:'GET'
      }).done(resp=>{
        const pedidos = resp.pedidos || [];
        const agrupa = resp.cantidad_pedidos || [];

        const filasPorNumero = (num) => {
          let total = 0;
          const filas = pedidos.filter(p=>p.numero_pedido===num).map(p=>{
            const desc = Number(p.descripcion_descuento_porcentaje || 0);
            const unit = Number(p.precio_venta);
            const qty  = Number(p.cantidad);
            const sub  = unit * qty;
            const tot  = sub * (1 - desc/100);
            total += tot;
            return `
              <tr>
                <td>${p.codigo}</td>
                <td>${p.nombre_producto}</td>
                <td class="text-center">${qty}</td>
                <td class="text-right">${unit.toFixed(2)}</td>
                <td class="text-center">${desc}%</td>
                <td class="text-right">${tot.toFixed(2)}</td>
              </tr>`;
          }).join('');

          return filas || `<tr><td colspan="6" class="text-center">Sin productos.</td></tr>`
               + `<tr><td colspan="5" class="text-right"><strong>Total del Pedido:</strong></td><td class="text-right"><strong>${total.toFixed(2)}</strong></td></tr>`;
        };

        let html = '';
        agrupa.forEach(g=>{
          html += `
            <div class="card mb-3">
              <div class="card-body">
                <h5 class="card-title mb-2">
                  Pedido #${g.numero_pedido}
                  <button class="btn btn-danger btn-sm ml-2" disabled>
                    <i class="fas fa-spinner fa-spin"></i> En proceso de entrega...
                  </button>
                </h5>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                      <tr>
                        <th>Cod.</th><th>Producto</th><th class="text-center">Cant</th>
                        <th class="text-right">P.Unit</th><th class="text-center">Desc</th><th class="text-right">Total</th>
                      </tr>
                    </thead>
                    <tbody>${filasPorNumero(g.numero_pedido)}</tbody>
                  </table>
                </div>
              </div>
            </div>`;
        });

        $('#crear-tabla-pedidos').html(html || '<div class="alert alert-info mb-0">No hay pedidos para este cliente.</div>');
      }).fail(()=>{
        $('#crear-tabla-pedidos').html('<div class="alert alert-danger">Error al cargar los pedidos.</div>');
      });
    });
  </script>
@stop
