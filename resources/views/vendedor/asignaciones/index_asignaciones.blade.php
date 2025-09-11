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
    <!-- Modal pedidos (optimizado para móvil) -->
    <x-adminlte-modal id="verPedidoModal" size="lg" theme="dark" icon="fas fa-box-open" title="Pedidos realizados" v-centered>
      <div class="modal-body px-3" id="crear-tabla-pedidos"></div>
      <x-slot name="footerSlot">
        <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2" />
      </x-slot>
    </x-adminlte-modal>

    <div class="container py-3">
      <table class="table table-striped table-bordered table-hover nowrap w-100 shadow-sm" id="tabla-asignaciones">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Celular</th>
            <th>Ubicación</th>
            <th>Pedido</th>
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
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.js" integrity="sha384-SY2UJyI2VomTkRZaMzHTGWoCHGjNh2V7w+d6ebcRmybnemfWfy9nffyAuIG4GJvd" crossorigin="anonymous"></script>
    
  <script>
    $(document).ready(function () {
      $('#tabla-asignaciones').DataTable({
        processing: true,
        serverSide: true,
        // 👇 Hace que, si hay columnas ocultas por responsive, se abra el detalle automáticamente
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            type: 'none',
            renderer: function (api, rowIdx, columns) {
              // Solo mostrar las columnas ocultas
              var rows = $.map(columns, function (col) {
                if (col.hidden) {
                  return `
                    <tr data-dt-column="${col.columnIndex}">
                      <td class="fw-bold pe-3">${col.title}</td>
                      <td>${col.data ?? ''}</td>
                    </tr>`;
                }
                return '';
              }).join('');

              return rows
                ? $('<table class="table table-sm table-borderless mb-0"><tbody/></table>').append(rows)
                : false; // si no hay ocultas, no muestra child
            }
          }
        },

        language: { url: '/i18n/es-ES.json' },
        ajax: "{{ route('asignacionvendedor.index') }}",

        columns: [
          { data: 'cliente'  },
          { data: 'celular' },
          { data: 'ubicacion', orderable:false, searchable:false },
          { data: 'tiene_pedido', orderable:false, searchable:false },
          { data: 'acciones', orderable:false, searchable:false }
        ],

        // ⚠ Si 'acciones' (índice 4) es no ordenable, evita ordenar por esa col
        // order: [[0, 'asc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],

        columnDefs: [

          { responsivePriority: 1, targets: 0 },
          { responsivePriority: 2, targets: 4 },

          { targets: [0,1,2], className: 'align-middle' },
          { targets: 3, className: 'text-center align-middle' },
          { targets: 4, className: 'text-center align-middle', width:'110px' }
        ],

        //eliminar "mostrando _ registros"
        dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3"<"mb-2 mb-md-0"l><"mb-2 mb-md-0"f><"mb-2 mb-md-0"B>>rtip',
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
