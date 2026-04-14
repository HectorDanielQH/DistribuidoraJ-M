@extends('adminlte::page')

@section('title', 'Inventario')

@section('content_header')
    <div class="inventory-header">
        <div>
            <span>Inventario</span>
            <h1>Productos</h1>
            <p>Controla stock, precios de venta, promociones y estado de cada producto.</p>
        </div>
        <div class="inventory-header-actions">
            <button type="button" class="btn btn-success inventory-main-btn" data-toggle="modal" data-target="#agregar-producto">
                <i class="fas fa-plus"></i> Nuevo producto
            </button>
            <button type="button" class="btn btn-outline-success inventory-main-btn" id="descargar-catalogo-productos">
                <i class="fas fa-file-pdf"></i> Descargar catalogo
            </button>
        </div>
    </div>
    <div class="container py-4 inventory-legacy-header" style="background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 16px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);">
        <div class="d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="text-white mb-2" style="font-size: 2.75rem; font-weight: 700; letter-spacing: 1px;">
                <i class="fas fa-boxes me-2"></i> DISTRIBUIDORA H&J <i class="fas fa-chart-line ms-2"></i>
            </h1>
            <span class="text-white" style="font-size: 1.4rem; font-weight: 500; color: #ecf0f1;">
                Panel de administración de productos
            </span>
            <a
                class="btn btn-success mt-3"
                id="descargar-catalogo-productos-legacy"
                style="border-radius: 8px;"
            >
                <i class="fas fa-file-pdf"></i> Descargar Catalogo de Productos
            </a>
        </div>
    </div>
@stop

@section('content')

   
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

    <x-adminlte-modal
        id="agregar-producto"
        size="xl"
        theme="dark"
        icon="fas fa-box-open"
        title="Registrar nuevo producto"
        data-backdrop="static"
    >
        <div class="modal-body px-4 product-modal-body">
            <form id="registro-producto" enctype="multipart/form-data">
                @csrf
                <div class="product-modal-flow">
                    <section class="product-modal-step">
                        <div class="product-step-number">1</div>
                        <div>
                            <strong>Clasificacion</strong>
                            <span>Elige proveedor, marca y linea para ubicar el producto dentro del inventario.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label>
                            Proveedor
                            <select name="proveedor" id="proveedor_id" class="form-control" required>
                                <option value="" disabled selected>Seleccione proveedor...</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Marca
                            <select name="marca_producto" id="marca_id" class="form-control" required>
                                <option value="" disabled selected>Seleccione una marca...</option>
                            </select>
                        </label>
                        <label>
                            Linea
                            <select name="linea_producto" id="linea_id" class="form-control" required>
                                <option value="" disabled selected>Seleccione una linea...</option>
                            </select>
                        </label>
                    </div>

                    <section class="product-modal-step">
                        <div class="product-step-number">2</div>
                        <div>
                            <strong>Datos del producto</strong>
                            <span>Registra nombre, codigo, stock inicial y costo de compra.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label>
                            Codigo
                            <div class="product-code-row">
                                <input type="text" name="codigo_producto" id="codigoProducto" class="form-control" placeholder="Generar codigo" readonly required>
                                <button type="button" class="btn btn-outline-primary product-inline-btn" id="generar-codigo-producto">
                                    <i class="fas fa-random"></i> Generar
                                </button>
                            </div>
                        </label>
                        <label>
                            Nombre
                            <input type="text" name="nombre_producto" class="form-control" placeholder="Ej: ARROZ GRANO DE ORO 1KG" required>
                        </label>
                        <label>
                            Descripcion
                            <input type="text" name="descripcion_producto" class="form-control" placeholder="Detalle para identificar el producto" required>
                        </label>
                        <label>
                            Stock inicial
                            <input type="number" name="cantidad" class="form-control" min="0" step="1" value="0" required>
                        </label>
                        <label>
                            Unidad de stock
                            <input type="text" name="detalle_cantidad" class="form-control" placeholder="Ej: UNIDADES, CAJAS" required>
                        </label>
                        <label>
                            Precio de compra
                            <input type="number" name="precio_compra" class="form-control" min="0" step="0.01" value="0.01" required>
                        </label>
                        <label>
                            Detalle de compra
                            <input type="text" name="detalle_precio_compra" class="form-control" placeholder="Ej: compra por caja" required>
                        </label>
                        <label>
                            Imagen
                            <input type="file" name="imagen_producto" class="form-control" accept="image/*">
                        </label>
                    </div>

                    <section class="product-modal-step">
                        <div class="product-step-number">3</div>
                        <div>
                            <strong>Datos opcionales</strong>
                            <span>Activa solo lo que corresponde al producto.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label class="product-toggle-field">
                            <span>Fecha de vencimiento</span>
                            <div class="product-toggle-row">
                                <input type="checkbox" id="habilitarVencimiento">
                                <span>Habilitar vencimiento</span>
                            </div>
                            <input type="date" name="fecha_vencimiento" id="vencimientoProducto" class="form-control" disabled>
                        </label>
                        <label class="product-toggle-field">
                            <span>Presentacion</span>
                            <div class="product-toggle-row">
                                <input type="checkbox" id="habilitarPresentacion">
                                <span>Habilitar presentacion</span>
                            </div>
                            <input type="text" name="presentacion" id="presentacionProducto" class="form-control" placeholder="Ej: paquete x 12" disabled>
                        </label>
                        <label class="product-toggle-field">
                            <span>Promocion</span>
                            <div class="product-toggle-row">
                                <input type="checkbox" name="promocion" id="habilitarPromocion" value="1">
                                <span>Habilitar promocion</span>
                            </div>
                            <input type="number" name="descuento_porcentaje" id="promocionDescuento" class="form-control mb-2" min="0" max="100" value="0" placeholder="Descuento %" disabled>
                            <input type="text" name="descuento_promocion" id="promocionRegalo" class="form-control" placeholder="Regalo o detalle" disabled>
                        </label>
                    </div>

                    <section class="product-modal-step">
                        <div class="product-step-number">4</div>
                        <div>
                            <strong>Formas de venta</strong>
                            <span>Define como se vendera el producto y como descuenta del stock.</span>
                        </div>
                    </section>

                    <div class="product-sales-box">
                        <div class="product-sales-title">
                            <strong>Presentaciones de venta</strong>
                            <button class="btn btn-success product-inline-btn" type="button" id="boton-agregar-forma-venta">
                                <i class="fas fa-plus"></i> Agregar forma
                            </button>
                        </div>
                        <div class="product-sales-grid product-sales-head">
                            <span>Forma de venta</span>
                            <span>Precio venta</span>
                            <span>Desc. stock</span>
                            <span>Accion</span>
                        </div>
                        <div id="grupodeinputs">
                            <div class="product-sales-grid">
                                <input type="text" class="form-control" name="nombre_forma_venta[]" placeholder="Ej: Unidad" required>
                                <input type="number" class="form-control" name="precio_forma_venta[]" placeholder="Ej: 10.50" min="0.01" value="0.01" step="0.01" required>
                                <input type="number" class="form-control" name="equivalencia[]" placeholder="Ej: 1" min="1" value="1" step="1" required>
                                <button class="btn btn-outline-danger product-remove-sale" type="button" onclick="quitarFormaVentaProducto(this)">
                                    <i class="fas fa-times"></i> Quitar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="product-modal-footer">
                <x-adminlte-button theme="danger" id="botonenviar-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2 product-modal-action" />
                <x-adminlte-button type="submit" id="botonenviarproducto" theme="success" icon="fas fa-save" label="Guardar producto" class="rounded-3 px-4 py-2 product-modal-action" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal
        id="editar-producto"
        size="xl"
        theme="dark"
        icon="fas fa-edit"
        title="Editar producto"
        data-backdrop="static"
    >
        <div class="modal-body px-4 product-modal-body">
            <form id="editar-producto-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editar_producto_id">
                <div class="product-modal-flow">
                    <section class="product-modal-step">
                        <div class="product-step-number">1</div>
                        <div>
                            <strong>Clasificacion</strong>
                            <span>Cambia proveedor, marca o linea si el producto fue clasificado en otro grupo.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label>
                            Proveedor
                            <select name="proveedor" id="editar_proveedor_id" class="form-control" required>
                                <option value="" disabled>Seleccione proveedor...</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Marca
                            <select name="marca_producto" id="editar_marca_id" class="form-control" required>
                                <option value="" disabled>Seleccione una marca...</option>
                            </select>
                        </label>
                        <label>
                            Linea
                            <select name="linea_producto" id="editar_linea_id" class="form-control" required>
                                <option value="" disabled>Seleccione una linea...</option>
                            </select>
                        </label>
                    </div>

                    <section class="product-modal-step">
                        <div class="product-step-number">2</div>
                        <div>
                            <strong>Datos generales</strong>
                            <span>Actualiza nombre, codigo, stock, precio e imagen.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label>
                            Codigo
                            <input type="text" name="codigo_producto" id="editar_codigo_producto" class="form-control" readonly required>
                        </label>
                        <label>
                            Nombre
                            <input type="text" name="nombre_producto" id="editar_nombre_producto" class="form-control" required>
                        </label>
                        <label>
                            Descripcion
                            <input type="text" name="descripcion_producto" id="editar_descripcion_producto" class="form-control" required>
                        </label>
                        <label>
                            Stock actual
                            <input type="number" name="cantidad" id="editar_cantidad" class="form-control" min="0" step="1" required>
                        </label>
                        <label>
                            Unidad de stock
                            <input type="text" name="detalle_cantidad" id="editar_detalle_cantidad" class="form-control" required>
                        </label>
                        <label>
                            Precio de compra
                            <input type="number" name="precio_compra" id="editar_precio_compra" class="form-control" min="0" step="0.01" required>
                        </label>
                        <label>
                            Detalle de compra
                            <input type="text" name="detalle_precio_compra" id="editar_detalle_precio_compra" class="form-control" required>
                        </label>
                        <label>
                            Fecha vencimiento
                            <input type="date" name="fecha_vencimiento" id="editar_fecha_vencimiento" class="form-control">
                        </label>
                        <label>
                            Presentacion
                            <input type="text" name="presentacion" id="editar_presentacion" class="form-control" placeholder="Ej: paquete x 12">
                        </label>
                        <label>
                            Imagen actual
                            <img id="editar_imagen_actual" src="" alt="Imagen actual del producto" class="product-edit-preview">
                            <input type="file" name="imagen_producto" class="form-control" accept="image/*">
                        </label>
                    </div>

                    <section class="product-modal-step">
                        <div class="product-step-number">3</div>
                        <div>
                            <strong>Promocion</strong>
                            <span>Activa o corrige el descuento y regalo del producto.</span>
                        </div>
                    </section>

                    <div class="product-modal-grid">
                        <label class="product-toggle-field">
                            <span>Promocion</span>
                            <div class="product-toggle-row">
                                <input type="checkbox" name="promocion" id="editar_promocion" value="1">
                                <span>Habilitar promocion</span>
                            </div>
                            <input type="number" name="descuento_porcentaje" id="editar_descuento_porcentaje" class="form-control mb-2" min="0" max="100" value="0" placeholder="Descuento %">
                            <input type="text" name="descuento_promocion" id="editar_descuento_promocion" class="form-control" placeholder="Regalo o detalle">
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <x-slot name="footerSlot">
            <div class="product-modal-footer">
                <x-adminlte-button theme="danger" id="editar-producto-cerrar" label="Cancelar" data-dismiss="modal" icon="fas fa-times" class="rounded-3 px-4 py-2 product-modal-action" />
                <x-adminlte-button type="submit" id="editar-producto-guardar" theme="success" icon="fas fa-save" label="Guardar cambios" class="rounded-3 px-4 py-2 product-modal-action" />
            </div>
        </x-slot>
    </x-adminlte-modal>

    <div class="inventory-page">
        <section class="inventory-summary" aria-label="Resumen de inventario">
            <article class="inventory-summary-card">
                <span>Productos registrados</span>
                <strong>{{ $resumenInventario['total'] ?? 0 }}</strong>
            </article>
            <article class="inventory-summary-card">
                <span>Disponibles para vender</span>
                <strong>{{ $resumenInventario['activos'] ?? 0 }}</strong>
            </article>
            <article class="inventory-summary-card inventory-warning-card">
                <span>Necesitan reposicion</span>
                <strong>{{ $resumenInventario['bajo_stock'] ?? 0 }}</strong>
            </article>
            <article class="inventory-summary-card">
                <span>Productos de baja</span>
                <strong>{{ $resumenInventario['de_baja'] ?? 0 }}</strong>
            </article>
        </section>

        <section class="inventory-help">
            <i class="fas fa-clipboard-check"></i>
            <div>
                <strong>Usa el buscador para encontrar productos rapido.</strong>
                <span>En celular cada producto se muestra como una ficha con stock, venta y acciones grandes.</span>
            </div>
        </section>

        <section class="inventory-filters" aria-label="Filtros de inventario">
            <div class="inventory-filter-title">
                <strong>Filtros profesionales</strong>
                <span>Combina proveedor, marca, linea, stock, estado y promocion segun la necesidad del inventario.</span>
            </div>
            <div class="inventory-filter-grid">
                <label>
                    Proveedor
                    <select id="filtro-proveedor-productos" class="form-control inventory-filter-control">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Marca
                    <select id="filtro-marca-productos" class="form-control inventory-filter-control">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" data-proveedor="{{ $marca->id_proveedor }}">{{ $marca->descripcion }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Linea
                    <select id="filtro-linea-productos" class="form-control inventory-filter-control">
                        <option value="">Todas las lineas</option>
                        @foreach($lineas as $linea)
                            <option value="{{ $linea->id }}" data-marca="{{ $linea->id_marca }}" data-proveedor="{{ $linea->marca->id_proveedor ?? '' }}">{{ $linea->descripcion_linea }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Stock
                    <select id="filtro-stock-productos" class="form-control inventory-filter-control">
                        <option value="">Todos</option>
                        <option value="sin_stock">Sin stock</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="disponible">Disponible</option>
                    </select>
                </label>
                <label>
                    Estado
                    <select id="filtro-estado-productos" class="form-control inventory-filter-control">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="baja">De baja</option>
                    </select>
                </label>
                <label>
                    Promocion
                    <select id="filtro-promocion-productos" class="form-control inventory-filter-control">
                        <option value="">Todos</option>
                        <option value="con">Con promocion</option>
                        <option value="sin">Sin promocion</option>
                    </select>
                </label>
                <button type="button" id="limpiar-filtros-productos" class="btn btn-outline-secondary inventory-main-btn">
                    <i class="fas fa-eraser"></i> Limpiar filtros
                </button>
            </div>
            <div id="resumen-filtros-productos" class="inventory-filter-summary" aria-live="polite">
                <span>Mostrando todos los productos.</span>
            </div>
        </section>
    </div>

    <div class="container my-4 inventory-legacy-intro">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark" style="font-size: 1.75rem; font-weight: 600;">
                <i class="fas fa-fw fa-boxes me-2"></i> Productos
            </h2>
            <a href="{{route('administrador.productos.create')}}" class="btn btn-success" style="border-radius: 8px;">
                <i class="fas fa-plus me-2"></i> Crear nuevo producto
            </a>
        </div>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 400;">
            Aquí puedes gestionar todos los productos de la distribuidora.
        </p>
    </div>


    <!--TABLA DE PRODUCTOS-->

    <div class="container pb-5 inventory-table-box">
        <table id="tabla-productos" class="table tabla-striped table-bordered table-hover" style="width:100%">
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
        </table>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/r-3.0.6/datatables.min.css" rel="stylesheet">
  

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

        :root {
            --inventory-bg: #eef3f1;
            --inventory-surface: #ffffff;
            --inventory-line: #d7e4df;
            --inventory-text: #17211d;
            --inventory-muted: #64748b;
            --inventory-green: #15803d;
            --inventory-green-soft: #e7f6ec;
            --inventory-red: #b91c1c;
            --inventory-red-soft: #fee2e2;
        }

        .content-wrapper {
            background: var(--inventory-bg);
        }

        .inventory-legacy-header,
        .inventory-legacy-intro {
            display: none;
        }

        .inventory-header,
        .inventory-help,
        .inventory-filters,
        .inventory-summary-card,
        .inventory-table-box {
            background: var(--inventory-surface);
            border: 1px solid var(--inventory-line);
            border-radius: 8px;
        }

        .inventory-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
        }

        .inventory-header span {
            color: var(--inventory-green);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .inventory-header h1 {
            margin: 0;
            color: var(--inventory-text);
            font-size: 1.65rem;
            font-weight: 900;
        }

        .inventory-header p,
        .inventory-help span,
        .inventory-summary-card span {
            margin: 4px 0 0;
            color: var(--inventory-muted);
            font-weight: 700;
        }

        .inventory-header-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(150px, 1fr));
            gap: 8px;
            align-content: start;
        }

        .inventory-main-btn,
        .inventory-action-btn,
        .inventory-mini-btn {
            min-height: 40px;
            border-radius: 8px;
            font-weight: 900;
            white-space: normal;
        }

        .inventory-page {
            display: grid;
            gap: 12px;
            margin-bottom: 12px;
        }

        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .inventory-summary-card {
            padding: 14px;
        }

        .inventory-summary-card strong {
            display: block;
            margin-top: 4px;
            color: var(--inventory-text);
            font-size: 1.75rem;
            font-weight: 900;
        }

        .inventory-warning-card {
            background: var(--inventory-red-soft);
            border-color: #fecaca;
        }

        .inventory-warning-card strong,
        .inventory-warning-card span {
            color: var(--inventory-red);
        }

        .inventory-help {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 10px;
            align-items: center;
            padding: 12px;
        }

        .inventory-help i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: var(--inventory-green-soft);
            color: var(--inventory-green);
            font-size: 1.2rem;
        }

        .inventory-help strong {
            display: block;
            color: var(--inventory-text);
            font-weight: 900;
        }

        .inventory-filters {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .inventory-filter-title strong,
        .inventory-filter-grid label {
            color: var(--inventory-text);
            font-weight: 900;
        }

        .inventory-filter-title span {
            display: block;
            color: var(--inventory-muted);
            font-weight: 700;
        }

        .inventory-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            align-items: end;
        }

        .inventory-filter-grid .form-control {
            min-height: 42px;
            border-radius: 8px;
        }

        .inventory-filter-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            min-height: 34px;
            align-items: center;
        }

        .inventory-filter-summary span {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 6px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-muted);
            font-weight: 800;
        }

        .inventory-filter-summary .active-filter {
            background: var(--inventory-green-soft);
            border-color: #b7e4c7;
            color: var(--inventory-green);
        }

        .product-modal-body {
            background: #f8fafc;
            max-height: 76vh;
            overflow-y: auto;
        }

        .product-modal-flow {
            display: grid;
            gap: 12px;
        }

        .product-modal-step,
        .product-modal-grid,
        .product-sales-box {
            background: var(--inventory-surface);
            border: 1px solid var(--inventory-line);
            border-radius: 8px;
            padding: 12px;
        }

        .product-modal-step {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 10px;
            align-items: center;
        }

        .product-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--inventory-green-soft);
            color: var(--inventory-green);
            font-weight: 900;
        }

        .product-modal-step strong,
        .product-modal-grid label,
        .product-sales-title strong,
        .product-sales-head span {
            color: var(--inventory-text);
            font-weight: 900;
        }

        .product-modal-step span {
            display: block;
            color: var(--inventory-muted);
            font-weight: 700;
        }

        .product-modal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .product-modal-grid label {
            display: grid;
            gap: 6px;
            margin: 0;
        }

        .product-modal-grid .form-control,
        .product-sales-grid .form-control {
            min-height: 42px;
            border-radius: 8px;
        }

        .product-code-row,
        .product-toggle-row,
        .product-sales-title {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .product-toggle-row {
            color: var(--inventory-muted);
            font-weight: 800;
        }

        .product-inline-btn,
        .product-remove-sale {
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
            white-space: normal;
        }

        .product-sales-box {
            display: grid;
            gap: 10px;
        }

        .product-sales-title {
            justify-content: space-between;
        }

        .product-sales-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 120px;
            gap: 8px;
            align-items: center;
        }

        #grupodeinputs {
            display: grid;
            gap: 8px;
        }

        .product-modal-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .product-modal-action {
            width: 100%;
            min-height: 42px;
            border-radius: 8px;
            font-weight: 900;
        }

        .product-edit-preview {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border: 1px solid var(--inventory-line);
            border-radius: 8px;
            background: #f8fafc;
        }

        .inventory-table-box {
            padding: 14px;
        }

        #tabla-productos {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        #tabla-productos thead th {
            border: 0;
            color: var(--inventory-muted);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #tabla-productos tbody td {
            border-top: 1px solid var(--inventory-line);
            border-bottom: 1px solid var(--inventory-line);
            vertical-align: middle;
            font-weight: 800;
        }

        #tabla-productos tbody td:first-child {
            border-left: 1px solid var(--inventory-line);
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        #tabla-productos tbody td:last-child {
            border-right: 1px solid var(--inventory-line);
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .inventory-stock-box {
            display: grid;
            justify-items: center;
            gap: 4px;
        }

        .inventory-stock {
            display: inline-flex;
            justify-content: center;
            min-width: 92px;
            border-radius: 8px;
            padding: 6px 10px;
            font-weight: 900;
        }

        .inventory-stock.is-ok {
            background: var(--inventory-green-soft);
            color: var(--inventory-green);
        }

        .inventory-stock.is-low {
            background: var(--inventory-red-soft);
            color: var(--inventory-red);
        }

        .inventory-stock-label {
            color: var(--inventory-muted);
            font-weight: 800;
        }

        .inventory-actions {
            display: grid;
            gap: 8px;
        }

        .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .dataTables_filter label {
            width: 100%;
            color: var(--inventory-muted);
            font-weight: 900;
        }

        .dataTables_filter input {
            width: 100% !important;
            margin: 6px 0 0 !important;
            min-height: 42px;
            border-radius: 8px;
        }

        @media (max-width: 767.98px) {
            .content-header,
            .content {
                padding-left: 8px;
                padding-right: 8px;
            }

            .inventory-header,
            .inventory-header-actions {
                grid-template-columns: 1fr;
            }

            .inventory-header {
                flex-direction: column;
            }

            .inventory-main-btn {
                width: 100%;
            }

            .inventory-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .inventory-filter-grid {
                grid-template-columns: 1fr;
            }

            #tabla-productos,
            #tabla-productos tbody,
            #tabla-productos tr,
            #tabla-productos td {
                display: block;
                width: 100%;
            }

            #tabla-productos thead {
                display: none;
            }

            #tabla-productos tbody tr {
                margin-bottom: 12px;
                padding: 12px;
                background: var(--inventory-surface);
                border: 1px solid var(--inventory-line);
                border-radius: 8px;
            }

            #tabla-productos tbody td {
                display: grid;
                grid-template-columns: 112px 1fr;
                gap: 8px;
                align-items: start;
                border: 0;
                padding: 8px 0;
            }

            #tabla-productos tbody td::before {
                content: attr(data-label);
                color: var(--inventory-muted);
                font-size: .75rem;
                font-weight: 900;
                text-transform: uppercase;
            }

            #tabla-productos tbody td:first-child,
            #tabla-productos tbody td:last-child {
                border: 0;
            }

            #tabla-productos tbody td:nth-child(2),
            #tabla-productos tbody td:nth-child(5),
            #tabla-productos tbody td:nth-child(6),
            #tabla-productos tbody td:nth-child(7),
            #tabla-productos tbody td:nth-child(8) {
                grid-template-columns: 1fr;
            }

            #tabla-productos tbody td:nth-child(2)::before,
            #tabla-productos tbody td:nth-child(5)::before,
            #tabla-productos tbody td:nth-child(6)::before,
            #tabla-productos tbody td:nth-child(7)::before,
            #tabla-productos tbody td:nth-child(8)::before {
                margin-bottom: 2px;
            }

            .inventory-actions {
                grid-template-columns: 1fr;
            }

            .inventory-action-btn,
            .inventory-mini-btn {
                width: 100%;
            }

            .product-modal-grid,
            .product-sales-grid,
            .product-modal-footer {
                grid-template-columns: 1fr;
            }

            .product-code-row,
            .product-sales-title {
                align-items: stretch;
                flex-direction: column;
            }

            .product-inline-btn,
            .product-remove-sale {
                width: 100%;
            }
        }

        @media (max-width: 420px) {
            .inventory-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.3.3/b-3.2.4/b-colvis-3.2.4/b-html5-3.2.4/b-print-3.2.4/cc-1.0.7/r-3.0.6/datatables.min.js"></script>
    
    <script>
        $(document).ready(function () {
            const tabla = $('#tabla-productos').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
                language: {
                    url: '/i18n/es-ES.json',
                    search: 'Buscar producto',
                    searchPlaceholder: 'Nombre o codigo',
                    lengthMenu: 'Ver _MENU_ productos',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ productos'
                },
                ajax: {
                    url: "{{ route('administrador.productos.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.proveedor_id = $('#filtro-proveedor-productos').val();
                        d.marca_id = $('#filtro-marca-productos').val();
                        d.linea_id = $('#filtro-linea-productos').val();
                        d.stock_estado = $('#filtro-stock-productos').val();
                        d.estado_producto = $('#filtro-estado-productos').val();
                        d.promocion = $('#filtro-promocion-productos').val();
                    },
                },
                columns: [
                    { data: 'codigo', width: '10%' },
                    { data: 'imagen', orderable: false, searchable: false, width: '10%' },
                    { data: 'nombre_producto' , width: '25%' },
                    { data: 'marca', orderable: false, searchable: false , width: '15%' },
                    { data: 'stock', orderable: false, searchable: false , width: '10%' },
                    { data: 'formas_venta', orderable: false, searchable: false , width: '15%' },
                    { data: 'promocion_vista', orderable: false, searchable: false , width: '10%' },
                    { data: 'acciones', orderable: false, searchable: false , width: '15%' },
                ],
                dom: `
                    <'row mb-2'<'col-12 d-flex justify-content-between align-items-center'Bf>>
                    <'row'<'col-12'tr>>
                    <'row'<'col-12 d-flex justify-content-between align-items-center'ip>>
                `,
                buttons: [
                    {
                        extend: 'pageLength',
                        className: 'btn btn-secondary',
                        text: '<i class="fas fa-list-ol"></i> Mostrar filas',
                        titleAttr: 'Mostrar filas'
                    },
                    {
                        extend: 'colvis',
                        className: 'btn btn-secondary',
                        text: '<i class="fas fa-columns"></i> Columnas',
                        titleAttr: 'Columnas'
                    },
                    /*html*/
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger',
                        text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                        titleAttr: 'Exportar a PDF',
                    },
                    /*imprimir*/
                    {
                        extend: 'print',
                        className: 'btn btn-info',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        titleAttr: 'Imprimir',
                    }
                ],
                createdRow: function(row) {
                    const labels = ['Codigo', 'Imagen', 'Producto', 'Marca', 'Stock', 'Venta', 'Promocion', 'Acciones'];
                    $('td', row).each(function(index) {
                        $(this).attr('data-label', labels[index] || '');
                    });
                },
            });

            const actualizarResumenFiltros = function() {
                const filtros = [];

                $('.inventory-filter-control').each(function() {
                    const value = $(this).val();
                    const label = $(this).closest('label').clone().children().remove().end().text().trim();
                    const text = $(this).find('option:selected').text();

                    if (value) {
                        filtros.push(`<span class="active-filter">${label}: ${text}</span>`);
                    }
                });

                $('#resumen-filtros-productos').html(
                    filtros.length ? filtros.join('') : '<span>Mostrando todos los productos.</span>'
                );
            };

            const actualizarFiltrosDependientes = function() {
                const proveedorId = $('#filtro-proveedor-productos').val();
                const marcaId = $('#filtro-marca-productos').val();

                $('#filtro-marca-productos option').each(function() {
                    const optionProveedor = $(this).data('proveedor');
                    const habilitado = !$(this).val() || !proveedorId || String(optionProveedor) === String(proveedorId);
                    $(this).prop('disabled', !habilitado);
                });

                if ($('#filtro-marca-productos option:selected').prop('disabled')) {
                    $('#filtro-marca-productos').val('');
                }

                const marcaActual = $('#filtro-marca-productos').val();
                $('#filtro-linea-productos option').each(function() {
                    const optionMarca = $(this).data('marca');
                    const optionProveedor = $(this).data('proveedor');
                    const habilitado = !$(this).val()
                        || (marcaActual && String(optionMarca) === String(marcaActual))
                        || (!marcaActual && (!proveedorId || String(optionProveedor) === String(proveedorId)));
                    $(this).prop('disabled', !habilitado);
                });

                if ($('#filtro-linea-productos option:selected').prop('disabled')) {
                    $('#filtro-linea-productos').val('');
                }
            };

            $('.inventory-filter-control').on('change', function() {
                actualizarFiltrosDependientes();
                actualizarResumenFiltros();
                tabla.ajax.reload();
            });

            $('#limpiar-filtros-productos').on('click', function() {
                $('.inventory-filter-control').val('');
                $('.inventory-filter-control option').prop('disabled', false);
                actualizarResumenFiltros();
                tabla.ajax.reload();
            });

            actualizarFiltrosDependientes();
            actualizarResumenFiltros();
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

            $('#editar_proveedor_id').select2({
                placeholder: 'Seleccione un proveedor',
                width: 'resolve',
                dropdownParent: $('#editar-producto'),
            });
            $('#editar_marca_id').select2({
                placeholder: 'Seleccione una marca',
                width: 'resolve',
                dropdownParent: $('#editar-producto'),
            });
            $('#editar_linea_id').select2({
                placeholder: 'Seleccione una linea',
                width: 'resolve',
                dropdownParent: $('#editar-producto'),
            });

            $('#editar_proveedor_id').on('select2:select', function() {
                cargarMarcasEditar($(this).val());
            });

            $('#editar_marca_id').on('select2:select', function() {
                cargarLineasEditar($(this).val());
            });

            $('#editar_promocion').on('change', function() {
                const activo = $(this).is(':checked');
                $('#editar_descuento_porcentaje, #editar_descuento_promocion').prop('disabled', !activo);
                if (!activo) {
                    $('#editar_descuento_porcentaje').val(0);
                    $('#editar_descuento_promocion').val('');
                }
            });

            $('#editar-producto-guardar').on('click', function() {
                $('#editar-producto-form').submit();
            });

            $('#editar-producto-form').on('submit', function(event) {
                event.preventDefault();
                const idProducto = $('#editar_producto_id').val();
                const formData = new FormData(this);
                const botonGuardar = $('#editar-producto-guardar');
                botonGuardar.prop('disabled', true);

                Swal.fire({
                    title: 'Guardando cambios...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('administrador.productos.update', ':id') }}".replace(':id', idProducto),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Producto actualizado',
                            text: 'Los cambios se guardaron correctamente.',
                            showConfirmButton: false,
                            timer: 1600,
                        }).then(() => {
                            $('#editar-producto-cerrar').click();
                            $('#editar-producto-form')[0].reset();
                            $('#tabla-productos').DataTable().ajax.reload(null, false);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo actualizar',
                            text: xhr.responseJSON?.message || 'Revisa los datos e intenta nuevamente.',
                        });
                    },
                    complete: function() {
                        botonGuardar.prop('disabled', false);
                    }
                });
            });

            $('#generar-codigo-producto').on('click', function() {
                Swal.fire({
                    title: 'Generando codigo...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('administrador.productos.autogenerar_codigo') }}",
                    type: 'GET',
                    success: function(data) {
                        Swal.close();
                        $('#codigoProducto').val(data.codigo);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo generar el codigo',
                            text: 'Intenta nuevamente.',
                        });
                    }
                });
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
                <div class="product-sales-grid">
                    <input type="text" class="form-control" name="nombre_forma_venta[]" placeholder="Ej: Caja" required>
                    <input type="number" class="form-control" name="precio_forma_venta[]" placeholder="Ej: 120.00" min="0.01" value="0.01" step="0.01" required>
                    <input type="number" class="form-control" name="equivalencia[]" placeholder="Ej: 12" min="1" value="1" step="1" required>
                    <button class="btn btn-outline-danger product-remove-sale" type="button" onclick="quitarFormaVentaProducto(this)">
                        <i class="fas fa-times"></i> Quitar
                    </button>
                </div>`;
            $("#grupodeinputs").append(nuevoInput);
        });

        function quitarFormaVentaProducto(e) {
            if ($('#grupodeinputs .product-sales-grid').length <= 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Debe existir una forma de venta',
                    text: 'Cada producto necesita al menos una forma de venta.',
                });
                return;
            }

            $(e).closest('.product-sales-grid').remove();
        }


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
                        $('#registro-producto')[0].reset();
                        $('#marca_id').empty().append('<option value="" disabled selected>Seleccione una marca...</option>').trigger('change');
                        $('#linea_id').empty().append('<option value="" disabled selected>Seleccione una linea...</option>').trigger('change');
                        $('#proveedor_id').val(null).trigger('change');
                        $('#vencimientoProducto, #presentacionProducto, #promocionDescuento, #promocionRegalo').prop('disabled', true);
                        $('#grupodeinputs').html(`
                            <div class="product-sales-grid">
                                <input type="text" class="form-control" name="nombre_forma_venta[]" placeholder="Ej: Unidad" required>
                                <input type="number" class="form-control" name="precio_forma_venta[]" placeholder="Ej: 10.50" min="0.01" value="0.01" step="0.01" required>
                                <input type="number" class="form-control" name="equivalencia[]" placeholder="Ej: 1" min="1" value="1" step="1" required>
                                <button class="btn btn-outline-danger product-remove-sale" type="button" onclick="quitarFormaVentaProducto(this)">
                                    <i class="fas fa-times"></i> Quitar
                                </button>
                            </div>
                        `);
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
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    url: "/i18n/es-ES.json"
                },
                ajax: {
                    url: "{{ route('administrador.formaventas.index', ':id') }}".replace(':id', idProducto),
                    type: "GET",
                },
                columns: [
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

        function editarNombreFormaVenta(e){
            let idFormaVenta = $(e).attr('id-forma-venta');
            let nombreFormaVenta = $(e).attr('nombre-forma-venta');
            Swal.fire({
                title: 'Editar Nombre de Forma de Venta',
                input: 'text',
                inputLabel: 'Nombre de la Forma de Venta',
                inputValue: nombreFormaVenta,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                preConfirm: (newNombre) => {
                    if (!newNombre) {
                        Swal.showValidationMessage('El nombre no puede estar vacío');
                        return false;
                    }
                    return newNombre;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Actualizando nombre...',
                        html: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: "{{ route('administrador.formaventas.editarNombreFormaVenta', ':id') }}".replace(':id', idFormaVenta),
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method:'PUT',
                            nuevo_nombre: result.value
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Nombre actualizado',
                                text: 'El nombre de la forma de venta se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000,
                                didClose: () => {
                                    $('#tabla-formas-venta-producto').DataTable().ajax.reload(null, false);
                                    $('#tabla-productos').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al actualizar el nombre',
                                text: xhr.responseJSON.message || 'Ocurrió un error al actualizar el nombre de la forma de venta.',
                            });
                        }
                    });
                }
            });
        }
        function cargarMarcasEditar(proveedorId, marcaSeleccionada = null) {
            $('#editar_marca_id').empty().append('<option value="" disabled selected>Cargando marcas...</option>');
            $('#editar_linea_id').empty().append('<option value="" disabled selected>Seleccione una linea...</option>');

            return $.ajax({
                url: "{{ route('administrador.marcas.show', ':id') }}".replace(':id', proveedorId),
                type: 'GET',
                success: function(data) {
                    $('#editar_marca_id').empty().append('<option value="" disabled selected>Seleccione una marca...</option>');
                    data.forEach(function(marca) {
                        const selected = String(marca.id) === String(marcaSeleccionada) ? 'selected' : '';
                        $('#editar_marca_id').append(`<option value="${marca.id}" ${selected}>${marca.descripcion}</option>`);
                    });
                    $('#editar_marca_id').trigger('change');
                }
            });
        }

        function cargarLineasEditar(marcaId, lineaSeleccionada = null) {
            $('#editar_linea_id').empty().append('<option value="" disabled selected>Cargando lineas...</option>');

            return $.ajax({
                url: "{{ route('administrador.lineas.show', ':id') }}".replace(':id', marcaId),
                type: 'GET',
                success: function(data) {
                    $('#editar_linea_id').empty().append('<option value="" disabled selected>Seleccione una linea...</option>');
                    data.forEach(function(linea) {
                        const selected = String(linea.id) === String(lineaSeleccionada) ? 'selected' : '';
                        $('#editar_linea_id').append(`<option value="${linea.id}" ${selected}>${linea.descripcion_linea}</option>`);
                    });
                    $('#editar_linea_id').trigger('change');
                }
            });
        }

        function abrirEditarProducto(e) {
            const idProducto = $(e).attr('id-producto');

            Swal.fire({
                title: 'Cargando producto...',
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
                success: async function(data) {
                    const producto = data.producto;

                    $('#editar-producto-form')[0].reset();
                    $('#editar-producto-form input[name="imagen_producto"]').val('');
                    $('#editar_producto_id').val(producto.id);
                    $('#editar_proveedor_id').val(producto.id_proveedor).trigger('change');
                    await cargarMarcasEditar(producto.id_proveedor, producto.id_marca);
                    await cargarLineasEditar(producto.id_marca, producto.id_linea);

                    $('#editar_codigo_producto').val(producto.codigo);
                    $('#editar_nombre_producto').val(producto.nombre_producto);
                    $('#editar_descripcion_producto').val(producto.descripcion_producto);
                    $('#editar_cantidad').val(producto.cantidad);
                    $('#editar_detalle_cantidad').val(producto.detalle_cantidad);
                    $('#editar_precio_compra').val(producto.precio_compra);
                    $('#editar_detalle_precio_compra').val(producto.detalle_precio_compra);
                    $('#editar_fecha_vencimiento').val(producto.fecha_vencimiento || '');
                    $('#editar_presentacion').val(producto.presentacion || '');
                    $('#editar_imagen_actual').attr('src', data.imagen_url);
                    $('#editar_promocion').prop('checked', !!producto.promocion);
                    $('#editar_descuento_porcentaje').val(producto.descripcion_descuento_porcentaje || 0);
                    $('#editar_descuento_promocion').val(producto.descripcion_regalo || '');
                    $('#editar_descuento_porcentaje, #editar_descuento_promocion').prop('disabled', !producto.promocion);

                    Swal.close();
                    $('#editar-producto').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo cargar el producto',
                        text: xhr.responseJSON?.message || 'Intenta nuevamente.',
                    });
                }
            });
        }
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
