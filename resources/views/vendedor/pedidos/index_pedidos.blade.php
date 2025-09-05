@extends('adminlte::page')

@section('title', 'Pedidos | Vendedor')

@section('content_header')
  <div class="container py-3" style="background:linear-gradient(135deg,#2c3e50,#34495e); border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12);">
    <div class="d-flex flex-column justify-content-center align-items-center text-center">
      <h1 class="text-white mb-1" style="font-size:1.6rem;font-weight:800;letter-spacing:.3px">
        <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J
      </h1>
      <span class="text-white-50" style="font-size:.95rem">Panel de Pedidos – Vendedor</span>
    </div>
  </div>
@stop

@section('content')
  <!-- Aviso cliente -->
  <div class="container mt-3">
    <div class="alert alert-info d-flex align-items-center" role="alert">
      <i class="fas fa-info-circle me-2"></i>
      <div>
        Se está creando un pedido para:
        <strong>{{ $asignacion->cliente->nombres }} {{ $asignacion->cliente->apellidos }}</strong>
      </div>
    </div>
  </div>

  <!-- Modal Agregar (fullscreen en móvil) -->
  <x-adminlte-modal id="modalAgregarProducto" title="Agregar producto" size="lg" theme="teal" icon="fas fa-shopping-cart" v-centered static-backdrop scrollable>
    <div>
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-8">
          <label for="caja-busqueda-producto" class="form-label mb-1"><strong>Código o coincidencia</strong></label>
          <select class="form-control" id="caja-busqueda-producto" name="caja-busqueda-producto" placeholder="Buscar producto por código o nombre">
            <option value="">Selecciona un producto</option>
            @foreach($productos as $producto)
              <option value="{{ $producto->id }}">{{ $producto->codigo }} — {{ $producto->nombre_producto }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4 d-grid">
          <button class="btn btn-primary" id="btn-buscar-producto">
            <i class="fas fa-search"></i> Buscar
          </button>
        </div>
      </div>

      <hr class="my-3">

      <!-- Detalle producto -->
      <div id="panel-detalle" class="d-none">
        <div class="d-flex align-items-center gap-3 mb-3">
          <img id="foto-producto" alt="Producto" class="rounded shadow-sm" style="width:90px;height:90px;object-fit:contain">
          <div class="flex-grow-1">
            <h5 id="nombre-producto" class="mb-1 fw-bold">—</h5>
            <div class="small text-muted">
              <span id="codigo-producto" class="me-3"></span>
              <span><strong>Stock:</strong> <span id="stock-producto">—</span></span>
            </div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-md-6">
            <label class="form-label" for="forma-venta">Forma de venta</label>
            <select id="forma-venta" class="form-control"></select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label" for="precio-unitario">Precio</label>
            <input id="precio-unitario" type="text" class="form-control text-end" value="0.00" readonly>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label" for="cantidad">Cantidad</label>
            <div class="input-group">
              <button class="btn btn-outline-secondary" type="button" id="qty-menos">−</button>
              <input id="cantidad" type="number" class="form-control text-center" value="1" min="1">
              <button class="btn btn-outline-secondary" type="button" id="qty-mas">+</button>
            </div>
          </div>
        </div>

        <div id="promo-box" class="mt-3 d-none">
          <div class="alert alert-success py-2 mb-0">
            <i class="fas fa-gift me-1"></i>
            <span id="promo-descuento" class="me-3"></span>
            <span id="promo-regalo"></span>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="h5 mb-0">Subtotal: <span id="sub-total" class="text-success fw-bold">0.00</span> Bs</div>
          <button id="btn-agregar" class="btn btn-success">
            <i class="fas fa-cart-plus"></i> Agregar al pedido
          </button>
        </div>

        <!-- Hidden snapshot -->
        <input type="hidden" id="id-producto">
        <input type="hidden" id="promocion">
        <input type="hidden" id="promocion-descuento">
        <input type="hidden" id="promocion-regalo">
        <input type="hidden" id="equivalencia">
        <input type="hidden" id="stock-base">
      </div>

      <div id="placeholder-detalle" class="text-muted d-flex align-items-center justify-content-center py-4">
        <i class="far fa-hand-point-up me-2"></i> Selecciona un producto y pulsa “Buscar”
      </div>
    </div>
    <x-slot name="footerSlot">
      <x-adminlte-button theme="danger" label="Cerrar" data-dismiss="modal"/>
    </x-slot>
  </x-adminlte-modal>

  <!-- Contenedor del pedido -->
  <div class="container mt-3 mb-5 pb-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Productos del pedido</h5>
        <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalAgregarProducto">
          <i class="fas fa-plus"></i> Agregar
        </button>
      </div>

      <div class="card-body p-2">
        <!-- Lista tipo tarjetas (mobile-first) -->
        <div id="cart-list"></div>

        <!-- Placeholder sin productos -->
        <div id="cart-empty" class="text-center text-muted py-4">
          <i class="far fa-box-open mb-2" style="font-size:1.6rem;"></i>
          <div>Aún no agregaste productos.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Barra inferior sticky (total + registrar) -->
  <div class="checkout-bar shadow-lg">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="h5 mb-0">Total: <span id="total-pedido" class="text-success fw-bold">0.00</span> Bs</div>
      <button class="btn btn-primary btn-lg" onclick="registrarPedido()">
        <i class="fas fa-save"></i> Registrar Pedido
      </button>
    </div>
  </div>
@stop

@section('css')
  <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css"/>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

  <style>
    /* Focus accesible */
    input.form-control:focus, select.form-control:focus, .select2-selection:focus {
      border-color:#1abc9c !important;
      box-shadow:0 0 0 .2rem rgba(26,188,156,.25) !important;
      outline:none;
    }
    /* Select2 altura */
    .select2-container--default .select2-selection--single{
      height:38px; border:1px solid #ced4da; border-radius:.25rem; display:flex; align-items:center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height:36px; padding-left:.5rem;
    }
    .card{ transition: box-shadow .2s; }
    .card:hover{ box-shadow:0 6px 20px rgba(0,0,0,.08); }
    .btn:hover{ opacity:.95; }

    /* Tarjetas de ítems del carrito */
    .item-card{
      border:1px solid #eee; border-radius:12px; padding:.75rem; margin-bottom:.75rem;
      display:flex; gap:.75rem; align-items:stretch;
    }
    .item-thumb{
      width:64px; height:64px; object-fit:contain; border-radius:8px; background:#fff;
      box-shadow: inset 0 0 0 1px rgba(0,0,0,.05);
    }
    .item-body{ flex:1; display:flex; flex-direction:column; gap:.35rem; }
    .item-row{ display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
    .qty-group { display:flex; align-items:center; gap:.25rem; }
    .qty-btn { min-width:36px; }
    .checkout-bar{
      position:fixed; left:0; right:0; bottom:0; background:#fff; padding:.6rem 0; z-index:1000;
      border-top:1px solid #eee;
    }

    /* Modal full-height en móviles */
    @media (max-width: 575.98px){
      #modalAgregarProducto .modal-dialog{ margin:0; height:100%; max-width:100%; }
      #modalAgregarProducto .modal-content{ height:100%; border-radius:0; }
      #modalAgregarProducto .modal-body{ overflow-y:auto; }
    }
  </style>
@stop

@section('js')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    // ===== Estado =====
    let CART = []; // { id_producto, codigo_producto, imagen_producto, texto_producto, id_forma_venta, tipo_venta, precio_venta, cantidad, sub_total, promocion, descripcion_regalo, descripcion_descuento_porcentaje, eq }
    let idProductoSeleccionado = "";

    const money = n => (Number(n || 0)).toFixed(2);

    // ===== Init =====
    $(function(){
      $('#caja-busqueda-producto').select2({ placeholder:'Buscar producto', width:'100%' });

      // Cargar pendientes del cliente y construir lista
      Swal.fire({title:'Cargando...', text:'Verificando pedidos pendientes', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
      const idCliente = '{{ $asignacion->cliente->id }}';
      $.get('{{ route("pedidos.vendedor.obtenerPedidosPendientes", ":id") }}'.replace(':id', idCliente))
        .done(resp=>{
          Swal.close();
          (resp.pedidos || []).forEach(p => {
            const foto = (!p.foto_producto || p.foto_producto==='')
              ? '{{ asset('images/logo_color.webp') }}?v={{ time() }}'
              : '{{ route("productos.imagen", ":foto") }}?v={{ time() }}'.replace(':foto', p.id_producto);

            const desc = Number(p.descripcion_descuento_porcentaje || 0);
            const unit = Number(p.precio_venta);
            const qty  = Number(p.cantidad);
            const sub  = (unit * qty) * (1 - desc/100);

            CART.push({
              id_producto: p.id_producto,
              codigo_producto: p.codigo_producto,
              imagen_producto: foto,
              texto_producto: p.nombre_producto,
              id_forma_venta: p.id_forma_venta,
              tipo_venta: p.tipo_venta,
              precio_venta: unit,
              cantidad: qty,
              sub_total: money(sub),
              promocion: p.promocion ? '1' : '0',
              descripcion_regalo: p.descripcion_regalo || '',
              descripcion_descuento_porcentaje: desc,
              eq: 1
            });
          });
          renderCart();
        })
        .fail(()=>{
          Swal.close();
          Swal.fire({icon:'error', title:'Error', text:'No se pudieron obtener los pedidos pendientes.'});
        });

      // Modal: eventos
      $('#btn-buscar-producto').on('click', buscarProducto);
      $('#forma-venta').on('change', onFormaChange);
      $('#cantidad').on('input', recalcSubtotal);
      $('#qty-menos').on('click', ()=>{ let v = Math.max(1, Number($('#cantidad').val()||1)-1); $('#cantidad').val(v); recalcSubtotal(); });
      $('#qty-mas').on('click', ()=>{ let v = Math.max(1, Number($('#cantidad').val()||1)+1); $('#cantidad').val(v); recalcSubtotal(); });
      $('#btn-agregar').on('click', agregarAlPedido);
    });

    // ===== Buscar producto =====
    function buscarProducto(){
      const productoId = $('#caja-busqueda-producto').val();
      idProductoSeleccionado = productoId;
      if(!productoId){
        Swal.fire({icon:'warning', title:'Selecciona', text:'Elige un producto.'});
        return;
      }

      $.get("{{ route('pedidos.vendedor.obtenerProducto',':id') }}".replace(':id', productoId))
        .done(data=>{
          const p  = data.producto;
          const fv = data.formasVenta || [];
          const foto = (!p.foto_producto)
            ? '{{ asset('images/logo_color.webp') }}?v={{ time() }}'
            : '{{ route("productos.imagen", ":foto") }}?v={{ time() }}'.replace(':foto', p.id);

          // pintar
          $('#panel-detalle').removeClass('d-none');
          $('#placeholder-detalle').addClass('d-none');

          $('#id-producto').val(p.id);
          $('#foto-producto').attr('src', foto);
          $('#nombre-producto').text(p.nombre_producto);
          $('#codigo-producto').text(`Código: ${p.codigo}`);
          $('#stock-producto').text(p.cantidad + (p.detalle_cantidad ? (' ' + p.detalle_cantidad) : ''));
          $('#stock-base').val(p.cantidad);
          $('#promocion').val(p.promocion ? '1':'0');
          $('#promocion-descuento').val(p.descripcion_descuento_porcentaje || 0);
          $('#promocion-regalo').val(p.descripcion_regalo || '');
          if(p.promocion){
            $('#promo-box').removeClass('d-none');
            $('#promo-descuento').text(`Descuento: ${p.descripcion_descuento_porcentaje || 0}%`);
            $('#promo-regalo').text(p.descripcion_regalo ? `Regalo: ${p.descripcion_regalo}` : '');
          }else{
            $('#promo-box').addClass('d-none');
          }

          const $fv = $('#forma-venta');
          $fv.empty().append(`<option value="">Selecciona una forma</option>`);
          fv.forEach(x => $fv.append(`<option value="${x.id}" data-precio="${x.precio_venta}" data-eq="${x.equivalencia_cantidad}">${x.tipo_venta}</option>`));
          $('#precio-unitario').val('0.00');
          $('#cantidad').val(1);
          $('#equivalencia').val(0);
          $('#sub-total').text('0.00');
        })
        .fail(()=> Swal.fire({icon:'error', title:'Error', text:'No se pudo obtener el producto.'}));
    }

    function onFormaChange(){
      const opt = $('#forma-venta option:selected');
      const precio = Number(opt.data('precio') || 0);
      const eq = Number(opt.data('eq') || 0);
      $('#precio-unitario').val(money(precio));
      $('#equivalencia').val(eq);
      recalcSubtotal();
    }

    function recalcSubtotal(){
      let qty = Math.max(1, Number($('#cantidad').val()||1));
      $('#cantidad').val(qty);
      const unit = Number($('#precio-unitario').val()||0);
      const descPct = Number($('#promocion-descuento').val()||0);
      const promoOn = $('#promocion').val()==='1';
      const unitFinal = promoOn ? unit * (1 - descPct/100) : unit;
      const sub = qty * unitFinal;
      $('#sub-total').text(money(sub));

      // validar contra stock base según equivalencia
      const eq = Number($('#equivalencia').val()||0);
      const stockBase = Number($('#stock-base').val()||0);
      if(eq>0 && qty*eq>stockBase){
        const max = Math.floor(stockBase/eq);
        if(max<=0){
          $('#cantidad').val(0);
          $('#sub-total').text('0.00');
          Swal.fire({icon:'warning', title:'Sin stock', text:'No hay stock suficiente para esta forma.'});
        }else{
          $('#cantidad').val(max);
          recalcSubtotal();
          Swal.fire({icon:'warning', title:'Ajustado', text:`Cantidad máxima disponible: ${max}.`});
        }
      }
    }

    function agregarAlPedido(){
      const prodId = Number($('#id-producto').val());
      const nombre = $('#nombre-producto').text();
      const codigo = ($('#codigo-producto').text()||'').replace('Código: ','').trim();
      const formaId = Number($('#forma-venta').val()||0);
      const formaText = $('#forma-venta option:selected').text();
      const unitPrice = Number($('#precio-unitario').val()||0);
      const qty = Number($('#cantidad').val()||0);
      const eq = Number($('#equivalencia').val()||0);
      const promo = $('#promocion').val();
      const descPct = Number($('#promocion-descuento').val()||0);
      const regalo = $('#promocion-regalo').val()||'';
      const img = $('#foto-producto').attr('src');

      if(!prodId || !formaId){
        Swal.fire({icon:'warning', title:'Faltan datos', text:'Selecciona la forma de venta.'});
        return;
      }
      if(qty<=0){
        Swal.fire({icon:'warning', title:'Cantidad inválida', text:'La cantidad debe ser mayor a 0.'});
        return;
      }

      const unitFinal = promo==='1' ? unitPrice*(1-descPct/100) : unitPrice;
      const subtotal = unitFinal*qty;

      // Unificar líneas iguales
      const idx = CART.findIndex(x => x.id_producto===prodId && x.id_forma_venta===formaId);
      if(idx>=0){
        CART[idx].cantidad = Number(CART[idx].cantidad) + qty;
        const unitF = CART[idx].promocion==='1' ? CART[idx].precio_venta*(1-Number(CART[idx].descripcion_descuento_porcentaje||0)/100) : CART[idx].precio_venta;
        CART[idx].sub_total = money(unitF*Number(CART[idx].cantidad));
      }else{
        CART.push({
          id_producto: prodId,
          codigo_producto: codigo,
          imagen_producto: img,
          texto_producto: nombre,
          id_forma_venta: formaId,
          tipo_venta: formaText,
          precio_venta: unitPrice,
          cantidad: qty,
          sub_total: money(subtotal),
          promocion: promo,
          descripcion_regalo: regalo,
          descripcion_descuento_porcentaje: descPct,
          eq: eq
        });
      }

      renderCart();
      // feedback
      Swal.fire({icon:'success', title:'Agregado', text:'Producto agregado al pedido.', timer:1100, showConfirmButton:false});
      // reset cantidad rápido
      $('#cantidad').val(1); recalcSubtotal();
    }

    // ===== Render del carrito =====
    function renderCart(){
      const $list = $('#cart-list');
      const $empty = $('#cart-empty');
      $list.empty();

      if(!CART.length){
        $empty.show();
        $('#total-pedido').text('0.00');
        return;
      }
      $empty.hide();

      let total = 0;

      CART.forEach((it, i)=>{
        const desc = Number(it.descripcion_descuento_porcentaje||0);
        const promoOn = (it.promocion==='1' || it.promocion===1 || it.promocion===true);
        const unitFinal = promoOn ? it.precio_venta*(1-desc/100) : it.precio_venta;
        const subtotal = unitFinal * Number(it.cantidad||0);
        total += subtotal;

        const $row = $(`
          <div class="item-card">
            <img class="item-thumb" src="${it.imagen_producto}" alt="${it.texto_producto}">
            <div class="item-body">
              <div class="item-row">
                <div class="fw-bold">${it.texto_producto}</div>
                <button class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
              </div>
              <div class="item-row small text-muted">
                <div>Cód: ${it.codigo_producto || '-'}</div>
                <div>${promoOn ? `<span class="badge bg-success">-${desc}%</span>` : ''}</div>
              </div>
              <div class="item-row">
                <div class="small">${it.tipo_venta}</div>
                <div class="small">P.Unit: <strong>${money(unitFinal)}</strong> Bs</div>
              </div>
              <div class="item-row">
                <div class="qty-group">
                  <button class="btn btn-outline-secondary btn-sm qty-btn btn-menos">−</button>
                  <input type="number" min="1" class="form-control form-control-sm text-center qty-input" style="width:80px" value="${it.cantidad}">
                  <button class="btn btn-outline-secondary btn-sm qty-btn btn-mas">+</button>
                </div>
                <div>Subt: <strong class="text-success">${money(subtotal)}</strong> Bs</div>
              </div>
            </div>
          </div>
        `);

        // Eventos por fila
        $row.find('.btn-menos').on('click', ()=>{
          let q = Math.max(1, Number(it.cantidad)-1);
          it.cantidad = q; it.sub_total = money(unitFinal*q);
          renderCart();
        });
        $row.find('.btn-mas').on('click', ()=>{
          let q = Math.max(1, Number(it.cantidad)+1);
          it.cantidad = q; it.sub_total = money(unitFinal*q);
          renderCart();
        });
        $row.find('.qty-input').on('input', function(){
          let q = Math.max(1, Number(this.value||1));
          // Validación orientativa con equivalencia si se conoce
          if(it.eq && Number.isFinite(Number(it.eq))){
            // aquí podrías limitar con stock actual si lo conoces por producto seleccionado
          }
          it.cantidad = q; it.sub_total = money(unitFinal*q);
          renderCart();
        });
        $row.find('.btn-outline-danger').on('click', ()=>{
          CART.splice(i,1);
          renderCart();
          Swal.fire({icon:'success', title:'Eliminado', timer:900, showConfirmButton:false});
        });

        $list.append($row);
      });

      $('#total-pedido').text(money(total));
    }

    // ===== Registrar pedido =====
    function registrarPedido(){
      if(!CART.length){
        Swal.fire({icon:'warning', title:'Sin productos', text:'Agrega productos antes de registrar.'});
        return;
      }
      Swal.fire({
        title:'Confirmar Pedido', text:'¿Deseas registrar este pedido?', icon:'question',
        showCancelButton:true, confirmButtonText:'Sí, registrar', cancelButtonText:'Cancelar'
      }).then(r=>{
        if(!r.isConfirmed) return;

        // Enviar mismo formato que usabas (mantiene compatibilidad)
        $.ajax({
          url: "{{ route('pedidos.vendedor.registrarPedido') }}",
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            asignacion_id: '{{ $asignacion->id }}',
            productos: JSON.stringify(CART)
          }
        }).done(()=>{
          Swal.fire({icon:'success', title:'Éxito', text:'Pedido registrado correctamente.', timer:1400, showConfirmButton:false})
            .then(()=> window.location.href = "{{ route('asignacionvendedor.index') }}");
        }).fail(xhr=>{
          Swal.fire({
            icon:'error', 
            title:'Error', 
            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo registrar el pedido.'
        });
        });
      });
    }
  </script>
@stop
