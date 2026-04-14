@extends('adminlte::page')

@section('title', 'Mis Asignaciones')

@section('content_header')
  <div class="assignments-header">
    <div>
      <span class="eyebrow">Trabajo del dia</span>
      <h1>Mis asignaciones</h1>
      <p>Busca por nombre, ruta o estado.</p>
    </div>
    <a href="{{ route('pedidos.vendedor.obtenerPdfRutas') }}" class="btn btn-outline-success header-action" target="_blank">
      <i class="fas fa-file-pdf"></i>
      Descargar rutas
    </a>
  </div>
@stop

@section('content')
  <x-adminlte-modal id="verPedidoModal" size="lg" theme="success" icon="fas fa-receipt" title="Pedido del cliente" v-centered>
    <div class="modal-body px-3" id="crear-tabla-pedidos"></div>
    <x-slot name="footerSlot">
      <x-adminlte-button theme="secondary" label="Cerrar" data-dismiss="modal" icon="fas fa-times" class="px-4 py-2" />
    </x-slot>
  </x-adminlte-modal>

  <div class="assignments-page">
    <section class="summary-grid" aria-label="Resumen de asignaciones">
      <div class="summary-tile">
        <span>Total</span>
        <strong>{{ $resumen['total'] }}</strong>
      </div>
      <button type="button" class="summary-tile filter-status" data-state="pendiente">
        <span>Pendientes</span>
        <strong>{{ $resumen['pendientes'] }}</strong>
      </button>
      <button type="button" class="summary-tile filter-status" data-state="con_pedido">
        <span>Con pedido</span>
        <strong>{{ $resumen['con_pedido'] }}</strong>
      </button>
      <button type="button" class="summary-tile filter-status" data-state="sin_pedido">
        <span>Sin pedido</span>
        <strong>{{ $resumen['sin_pedido'] }}</strong>
      </button>
    </section>

    <section class="filter-panel" aria-label="Filtros para ubicar clientes">
      <div class="filter-title">
        <div>
          <h2>Encontrar cliente</h2>
          <p>Elige solo lo necesario para encontrar al cliente rapido.</p>
        </div>
        <button type="button" class="btn btn-light btn-clean" id="limpiar-filtros">
          <i class="fas fa-broom"></i>
          Limpiar
        </button>
      </div>

      <form id="filtros-asignaciones" class="filter-grid">
        <label>
          <span>Nombre o apellido</span>
          <input type="text" class="form-control" name="cliente" placeholder="Ej: Juan Perez">
        </label>
        <label>
          <span>Ruta</span>
          <select class="form-control" name="ruta_id">
            <option value="">Todas las rutas</option>
            @foreach($rutas as $ruta)
              <option value="{{ $ruta->id }}">{{ $ruta->nombre_ruta }}</option>
            @endforeach
          </select>
        </label>
        <label>
          <span>Estado</span>
          <select class="form-control" name="estado" id="estado-filtro">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente de visitar</option>
            <option value="con_pedido">Con pedido</option>
            <option value="sin_pedido">Atendido sin pedido</option>
            <option value="atendido">Todos los atendidos</option>
          </select>
        </label>
        <button type="submit" class="btn btn-success btn-search">
          <i class="fas fa-search"></i>
          Buscar
        </button>
      </form>
    </section>

    <section class="results-panel" aria-label="Lista de clientes asignados">
      <div class="results-heading">
        <div>
          <h2>Clientes asignados</h2>
          <p>Primero aparecen los clientes pendientes.</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover w-100" id="tabla-asignaciones">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Celular</th>
              <th>Ruta / zona</th>
              <th>Direccion</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
        </table>
      </div>
    </section>
  </div>
@stop

@section('css')
  <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
  <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-CaLdjDnDQsm4dp6FAi+hDGbnmYMabedJHm00x/JJgmTsQ495TW5sVn4B7kcyThok" crossorigin="anonymous">

  <style>
    :root {
      --surface: #ffffff;
      --surface-soft: #f4f7f6;
      --line: #dfe7e4;
      --text: #17211d;
      --muted: #64746d;
      --green: #15803d;
      --green-soft: #e7f6ec;
      --yellow: #facc15;
      --red: #dc2626;
      --ink: #26332e;
    }

    .content-wrapper {
      background: #eef3f1;
    }

    .assignments-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 18px;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 8px;
    }

    .assignments-header h1,
    .filter-title h2,
    .results-heading h2 {
      margin: 0;
      color: var(--text);
      font-weight: 800;
      letter-spacing: 0;
    }

    .assignments-header h1 {
      font-size: 1.65rem;
    }

    .assignments-header p,
    .filter-title p,
    .results-heading p {
      margin: 4px 0 0;
      color: var(--muted);
    }

    .eyebrow {
      display: inline-block;
      margin-bottom: 4px;
      color: var(--green);
      font-weight: 800;
      text-transform: uppercase;
      font-size: .78rem;
      letter-spacing: 0;
    }

    .header-action {
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border-radius: 8px;
      font-weight: 800;
      white-space: nowrap;
    }

    .assignments-page {
      display: grid;
      gap: 16px;
      padding-bottom: 24px;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr 1fr auto;
      gap: 12px;
    }

    .summary-tile {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      min-height: 76px;
      padding: 16px;
      background: var(--surface);
      color: var(--text);
      border: 1px solid var(--line);
      border-radius: 8px;
      text-align: left;
      width: 100%;
    }

    button.summary-tile {
      cursor: pointer;
    }

    .summary-tile span {
      color: var(--muted);
      font-weight: 700;
    }

    .summary-tile strong {
      font-size: 1.8rem;
      line-height: 1;
    }

    .summary-tile.is-active {
      border-color: var(--green);
      background: var(--green-soft);
    }

    .filter-panel,
    .results-panel {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 16px;
    }

    .filter-title,
    .results-heading {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 14px;
    }

    .filter-title h2,
    .results-heading h2 {
      font-size: 1.2rem;
    }

    .filter-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr 1fr auto;
      gap: 12px;
      align-items: end;
    }

    .filter-grid label {
      margin: 0;
      color: var(--text);
      font-weight: 800;
    }

    .filter-grid label span {
      display: block;
      margin-bottom: 6px;
      font-size: .88rem;
    }

    .filter-grid .form-control {
      min-height: 44px;
      border-radius: 8px;
      border-color: var(--line);
      color: var(--text);
      font-size: 1rem;
    }

    .filter-grid .form-control:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 .2rem rgba(21, 128, 61, .18);
    }

    .btn-clean,
    .btn-search {
      min-height: 44px;
      border-radius: 8px;
      font-weight: 800;
    }

    .btn-search {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
    }

    #tabla-asignaciones {
      border-collapse: separate;
      border-spacing: 0 8px;
    }

    #tabla-asignaciones thead th {
      border: 0;
      color: var(--muted);
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: 0;
    }

    #tabla-asignaciones tbody tr {
      background: #fbfdfc;
    }

    #tabla-asignaciones tbody td {
      border-top: 1px solid var(--line);
      border-bottom: 1px solid var(--line);
      vertical-align: middle;
      color: var(--text);
    }

    #tabla-asignaciones tbody td:first-child {
      border-left: 1px solid var(--line);
      border-top-left-radius: 8px;
      border-bottom-left-radius: 8px;
    }

    #tabla-asignaciones tbody td:last-child {
      border-right: 1px solid var(--line);
      border-top-right-radius: 8px;
      border-bottom-right-radius: 8px;
    }

    .client-cell,
    .route-cell,
    .address-cell {
      display: grid;
      gap: 3px;
      min-width: 0;
    }

    .client-name,
    .route-name {
      font-weight: 900;
      color: var(--text);
    }

    .client-meta,
    .address-cell small {
      color: var(--muted);
      font-weight: 700;
      white-space: normal;
    }

    .phone-link {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: var(--green);
      font-weight: 900;
      min-height: 36px;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      min-height: 34px;
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: 900;
      white-space: nowrap;
    }

    .status-order {
      background: var(--green-soft);
      color: var(--green);
    }

    .status-done {
      background: #edf2f7;
      color: var(--ink);
    }

    .status-pending {
      background: #fff7cf;
      color: #6b5200;
    }

    .assignment-actions {
      display: grid;
      grid-template-columns: repeat(2, minmax(120px, 1fr));
      gap: 8px;
      min-width: 250px;
    }

    .btn-action {
      min-height: 42px;
      border-radius: 8px;
      font-weight: 900;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    div.dataTables_wrapper div.dataTables_filter input,
    div.dataTables_wrapper div.dataTables_length select {
      border-radius: 8px;
      min-height: 38px;
      border-color: var(--line);
    }

    .pedido-card {
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 14px;
      margin-bottom: 12px;
      background: #fbfdfc;
    }

    .pedido-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 10px;
      font-weight: 900;
      color: var(--text);
    }

    .pedido-products {
      display: grid;
      gap: 8px;
    }

    .pedido-product {
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 10px;
      background: var(--surface);
    }

    .pedido-product-name {
      margin-bottom: 8px;
      color: var(--text);
      font-size: 1rem;
      font-weight: 900;
      line-height: 1.25;
    }

    .pedido-product-code {
      display: block;
      margin-top: 2px;
      color: var(--muted);
      font-size: .82rem;
      font-weight: 800;
    }

    .pedido-product-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px;
    }

    .pedido-product-grid span {
      display: block;
      color: var(--muted);
      font-size: .76rem;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0;
    }

    .pedido-product-grid strong {
      display: block;
      margin-top: 2px;
      color: var(--text);
      font-size: 1rem;
    }

    .pedido-total-box {
      margin-top: 10px;
      padding: 10px;
      border-radius: 8px;
      background: var(--green-soft);
      color: var(--green);
      text-align: right;
      font-weight: 900;
    }

    .pedido-total-box strong {
      display: block;
      font-size: 1.25rem;
    }

    @media (max-width: 991.98px) {
      .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 575.98px) {
      .content-header,
      .content {
        padding-left: 8px;
        padding-right: 8px;
      }

      .assignments-header,
      .filter-title,
      .results-heading {
        align-items: stretch;
        flex-direction: column;
      }

      .assignments-header h1 {
        font-size: 1.4rem;
      }

      .header-action,
      .btn-clean {
        width: 100%;
      }

      .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
      }

      .filter-grid {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .summary-tile {
        min-height: 58px;
        padding: 12px;
      }

      .summary-tile strong {
        font-size: 1.4rem;
      }

      .filter-panel,
      .results-panel,
      .assignments-header {
        padding: 12px;
      }

      .table-responsive {
        overflow-x: visible;
      }

      div.dataTables_wrapper div.dataTables_length,
      div.dataTables_wrapper div.dataTables_filter {
        text-align: left;
      }

      div.dataTables_wrapper div.dataTables_filter {
        display: none;
      }

      div.dataTables_wrapper div.dataTables_length label,
      div.dataTables_wrapper div.dataTables_info,
      div.dataTables_wrapper div.dataTables_paginate {
        width: 100%;
        color: var(--muted);
        font-weight: 700;
      }

      div.dataTables_wrapper div.dataTables_length select {
        width: 100%;
        margin: 6px 0 0;
      }

      #tabla-asignaciones {
        border-collapse: collapse;
        border-spacing: 0;
      }

      #tabla-asignaciones thead {
        display: none;
      }

      #tabla-asignaciones,
      #tabla-asignaciones tbody,
      #tabla-asignaciones tr,
      #tabla-asignaciones td {
        display: block;
        width: 100%;
      }

      #tabla-asignaciones tbody tr {
        margin-bottom: 12px;
        padding: 12px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
      }

      #tabla-asignaciones tbody td,
      #tabla-asignaciones tbody td:first-child,
      #tabla-asignaciones tbody td:last-child {
        border: 0;
        border-radius: 0;
        padding: 8px 0;
        text-align: left !important;
      }

      #tabla-asignaciones tbody td:not(:first-child)::before {
        content: attr(data-mobile-label);
        display: block;
        margin-bottom: 4px;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0;
      }

      #tabla-asignaciones tbody td:first-child {
        padding-top: 0;
        padding-bottom: 10px;
      }

      #tabla-asignaciones tbody td:nth-child(5) {
        padding-top: 10px;
        border-top: 1px solid var(--line);
      }

      .assignment-actions {
        grid-template-columns: 1fr;
        min-width: 0;
        width: 100%;
      }

      #verPedidoModal .modal-dialog {
        margin: 0;
        height: 100%;
        max-width: 100%;
      }

      #verPedidoModal .modal-content {
        height: 100%;
        border-radius: 0;
      }

      #verPedidoModal .modal-body {
        overflow-y: auto;
      }

      .pedido-title {
        align-items: flex-start;
        flex-direction: column;
      }

      .pedido-product-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@stop

@section('js')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/fc-5.0.4/fh-4.0.3/r-3.0.6/rg-1.5.2/sc-2.4.3/sb-1.8.3/sp-2.3.5/datatables.min.js" integrity="sha384-SY2UJyI2VomTkRZaMzHTGWoCHGjNh2V7w+d6ebcRmybnemfWfy9nffyAuIG4GJvd" crossorigin="anonymous"></script>

  <script>
    $(document).ready(function () {
      const filtros = $('#filtros-asignaciones');

      const dt = $('#tabla-asignaciones').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        language: { url: '/i18n/es-ES.json' },
        ajax: {
          url: "{{ route('asignacionvendedor.index') }}",
          data: function (data) {
            data.cliente = filtros.find('[name="cliente"]').val();
            data.ruta_id = filtros.find('[name="ruta_id"]').val();
            data.estado = filtros.find('[name="estado"]').val();
          }
        },
        columns: [
          { data: 'cliente', name: 'cliente', orderable: false },
          { data: 'celular', name: 'celular', orderable: false, searchable: false },
          { data: 'ruta', name: 'ruta', orderable: false, searchable: false },
          { data: 'ubicacion', name: 'ubicacion', orderable: false, searchable: false },
          { data: 'tiene_pedido', name: 'tiene_pedido', orderable: false, searchable: false },
          { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        columnDefs: [
          { targets: [4, 5], className: 'text-center align-middle' }
        ],
        createdRow: function (row) {
          const labels = ['Cliente', 'Celular', 'Ruta / zona', 'Direccion', 'Estado', 'Acciones'];
          $('td', row).each(function (index) {
            $(this).attr('data-mobile-label', labels[index]);
          });
        }
      });

      filtros.on('submit', function (event) {
        event.preventDefault();
        $('.summary-tile').removeClass('is-active');
        dt.ajax.reload();
      });

      filtros.find('input, select').on('change', function () {
        $('.summary-tile').removeClass('is-active');
        dt.ajax.reload();
      });

      $('#limpiar-filtros').on('click', function () {
        filtros[0].reset();
        $('.summary-tile').removeClass('is-active');
        dt.search('');
        dt.ajax.reload();
      });

      $('.filter-status').on('click', function () {
        const estado = $(this).data('state');
        $('#estado-filtro').val(estado);
        $('.summary-tile').removeClass('is-active');
        $(this).addClass('is-active');
        dt.ajax.reload();
      });

      $(document).on('click', '.btn-sin-pedido', function () {
        const boton = $(this);
        const asignacionId = boton.data('id');

        Swal.fire({
          title: 'Marcar sin pedido',
          text: 'Se registrara que visitaste al cliente, pero no hizo pedido.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#15803d',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Si, marcar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (!result.isConfirmed) {
            return;
          }

          $.ajax({
            url: "{{ route('registrarAtencion.sinpedido', ':id') }}".replace(':id', asignacionId),
            type: 'PUT',
            data: { _token: '{{ csrf_token() }}' },
            beforeSend: function () {
              boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando');
            }
          }).done(() => {
            Swal.fire({
              icon: 'success',
              title: 'Atencion registrada',
              timer: 1200,
              showConfirmButton: false
            });
            dt.ajax.reload(null, false);
          }).fail((xhr) => {
            boton.prop('disabled', false).html('<i class="fas fa-user-check"></i> Sin pedido');
            Swal.fire({
              icon: 'error',
              title: 'No se pudo registrar',
              text: xhr.responseJSON?.message || 'Intenta nuevamente.'
            });
          });
        });
      });
    });

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    $(document).on('click', '.btn-ver-pedido', function(){
      const asignacionId = $(this).data('asignacion-id');
      $('#crear-tabla-pedidos').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando pedido...</div>');
      $('#verPedidoModal').modal('show');

      $.ajax({
        url: `{{ route('pedidos.vendedor.obtenerPedidosProceso', ':numero') }}`.replace(':numero', asignacionId),
        type:'GET'
      }).done(resp => {
        const pedidos = resp.pedidos || [];
        const grupos = resp.cantidad_pedidos || [];

        const filasPorNumero = (numero) => {
          let total = 0;
          const productosHtml = pedidos.filter(p => String(p.numero_pedido) === String(numero)).map(p => {
            const desc = Number(p.descripcion_descuento_porcentaje || 0);
            const unit = Number(p.precio_venta || 0);
            const qty = Number(p.cantidad || 0);
            const sub = unit * qty;
            const totalFila = sub * (1 - desc / 100);
            total += totalFila;

            return `
              <div class="pedido-product">
                <div class="pedido-product-name">
                  ${escapeHtml(p.nombre_producto)}
                  <span class="pedido-product-code">Cod. ${escapeHtml(p.codigo)} | ${escapeHtml(p.tipo_venta)}</span>
                </div>
                <div class="pedido-product-grid">
                  <div>
                    <span>Cantidad</span>
                    <strong>${qty}</strong>
                  </div>
                  <div>
                    <span>Precio</span>
                    <strong>Bs ${unit.toFixed(2)}</strong>
                  </div>
                  <div>
                    <span>Descuento</span>
                    <strong>${desc}%</strong>
                  </div>
                  <div>
                    <span>Total</span>
                    <strong>Bs ${totalFila.toFixed(2)}</strong>
                  </div>
                </div>
              </div>`;
          }).join('');

          const cuerpo = productosHtml || '<div class="alert alert-info mb-0">No hay productos en este pedido.</div>';

          return `
            ${cuerpo}
            <div class="pedido-total-box">
              Total del pedido
              <strong>Bs ${total.toFixed(2)}</strong>
            </div>`;
        };

        const html = grupos.map(g => `
          <div class="pedido-card">
            <div class="pedido-title">
              <span>Pedido #${escapeHtml(g.numero_pedido)}</span>
              <span class="status-pill status-order"><i class="fas fa-spinner"></i> En proceso</span>
            </div>
            <div class="pedido-products">
              ${filasPorNumero(g.numero_pedido)}
            </div>
          </div>`).join('');

        $('#crear-tabla-pedidos').html(html || '<div class="alert alert-info mb-0">Este cliente no tiene pedidos en proceso.</div>');
      }).fail(() => {
        $('#crear-tabla-pedidos').html('<div class="alert alert-danger mb-0">Error al cargar los pedidos.</div>');
      });
    });
  </script>
@stop
