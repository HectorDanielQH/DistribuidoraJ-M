@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
    <div class="home-title">
        <img src="{{ asset('images/logo_color.webp') }}" alt="Distribuidora H&J">
        <div>
            <p>Distribuidora H&J</p>
            <h1>Inicio</h1>
        </div>
    </div>
@stop

@section('content')
    <div class="home-shell">
        <section class="home-hero">
            <div class="home-hero-text">
                <span class="home-eyebrow">Panel principal</span>
                <h2>Que necesitas hacer ahora?</h2>
                <p>
                    Entra directo a las tareas mas usadas. Los botones son grandes para trabajar comodo desde celular.
                </p>
            </div>
            <img src="{{ asset('images/background.webp') }}" alt="Productos de la distribuidora" class="home-hero-image">
        </section>

        @can('administrador.permisos')
            <section class="home-section" aria-labelledby="admin-title">
                <div class="home-section-heading">
                    <div>
                        <span>Administrador</span>
                        <h3 id="admin-title">Trabajo del dia</h3>
                    </div>
                </div>

                <div class="home-metrics">
                    <a href="{{ route('administrador.productos.index') }}" class="home-metric">
                        <strong>{{ $stats['productos_bajo_stock'] ?? 0 }}</strong>
                        <span>Productos con poco stock</span>
                    </a>
                    <a href="{{ route('administrador.pedidos.administrador.visualizacion') }}" class="home-metric">
                        <strong>{{ $stats['pedidos_pendientes'] ?? 0 }}</strong>
                        <span>Pedidos por despachar</span>
                    </a>
                    <a href="{{ route('pedidos.administrador.visualizacionDespachados') }}" class="home-metric">
                        <strong>{{ $stats['pedidos_despachados'] ?? 0 }}</strong>
                        <span>Pedidos por contabilizar</span>
                    </a>
                </div>

                <div class="home-actions">
                    <a class="home-action home-action-primary" href="{{ route('administrador.pedidos.administrador.visualizacion') }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>
                            <strong>Ver pedidos pendientes</strong>
                            <small>Revisar clientes, productos y cantidades.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('pedidos.administrador.visualizacionParaDespachado') }}">
                        <i class="fas fa-truck-loading"></i>
                        <span>
                            <strong>Preparar despacho</strong>
                            <small>Ver el resumen de productos a cargar.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('pedidos.administrador.visualizacionDespachados') }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>
                            <strong>Contabilizar pedidos</strong>
                            <small>Pasar pedidos despachados a ventas.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('administrador.asignacionclientes.index') }}">
                        <i class="fas fa-route"></i>
                        <span>
                            <strong>Asignar rutas</strong>
                            <small>Dar clientes a cada vendedor.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('administrador.productos.index') }}">
                        <i class="fas fa-boxes"></i>
                        <span>
                            <strong>Productos y stock</strong>
                            <small>Actualizar precios, stock y promociones.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('administrador.ventas.administrador.ventasPorPedido') }}">
                        <i class="fas fa-receipt"></i>
                        <span>
                            <strong>Ventas por fecha</strong>
                            <small>Revisar arqueos y mover fechas.</small>
                        </span>
                    </a>
                </div>
            </section>
        @endcan

        @can('vendedor.permisos')
            <section class="home-section" aria-labelledby="seller-title">
                <div class="home-section-heading">
                    <div>
                        <span>Vendedor</span>
                        <h3 id="seller-title">Atencion en ruta</h3>
                    </div>
                </div>

                <div class="home-metrics">
                    <a href="{{ route('asignacionvendedor.index') }}" class="home-metric">
                        <strong>{{ $stats['asignaciones_pendientes'] ?? 0 }}</strong>
                        <span>Clientes por atender</span>
                    </a>
                    <a href="{{ route('preventistas.ventas.vendedor.misVentas') }}" class="home-metric">
                        <strong>{{ number_format($stats['mis_ventas_hoy'] ?? 0, 2, '.', ',') }}</strong>
                        <span>Bs. vendidos hoy</span>
                    </a>
                </div>

                <div class="home-actions">
                    <a class="home-action home-action-primary" href="{{ route('asignacionvendedor.index') }}">
                        <i class="fas fa-tasks"></i>
                        <span>
                            <strong>Mis asignaciones</strong>
                            <small>Atender clientes y registrar pedidos.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('preventistas.productos.vendedor.obtenerProductos') }}">
                        <i class="fas fa-tags"></i>
                        <span>
                            <strong>Ver catalogo</strong>
                            <small>Consultar precios, stock y promociones.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('preventistas.ventas.vendedor.misVentas') }}">
                        <i class="fas fa-cash-register"></i>
                        <span>
                            <strong>Mis ventas</strong>
                            <small>Revisar ventas contabilizadas.</small>
                        </span>
                    </a>
                </div>
            </section>
        @endcan

        @can('contador.permisos')
            <section class="home-section" aria-labelledby="accounting-title">
                <div class="home-section-heading">
                    <div>
                        <span>Contabilidad</span>
                        <h3 id="accounting-title">Control de ventas</h3>
                    </div>
                </div>

                <div class="home-metrics">
                    <a href="{{ route('contabilidad.ventas.porDia') }}" class="home-metric">
                        <strong>{{ number_format($stats['ventas_hoy'] ?? 0, 2, '.', ',') }}</strong>
                        <span>Bs. contabilizados hoy</span>
                    </a>
                </div>

                <div class="home-actions">
                    <a class="home-action home-action-primary" href="{{ route('contabilidad.ventas.porDia') }}">
                        <i class="fas fa-calendar-day"></i>
                        <span>
                            <strong>Ventas por dia</strong>
                            <small>Ver totales por fecha y vendedor.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('contabilidad.ventas.porPreventista') }}">
                        <i class="fas fa-user-check"></i>
                        <span>
                            <strong>Ventas por preventista</strong>
                            <small>Comparar ventas entre vendedores.</small>
                        </span>
                    </a>
                    <a class="home-action" href="{{ route('contabilidad.ventas.comparacionGanancial') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>
                            <strong>Comparacion ganancial</strong>
                            <small>Revisar margen y resultados.</small>
                        </span>
                    </a>
                </div>
            </section>
        @endcan

        <section class="home-help">
            <i class="fas fa-mobile-alt"></i>
            <p>Desde celular usa los botones grandes. Si dudas, empieza por la primera opcion de tu seccion.</p>
        </section>
    </div>
@stop

@section('css')
    <style>
        .content-wrapper {
            background: #f4f7f6;
        }

        .home-title {
            align-items: center;
            display: flex;
            gap: 12px;
        }

        .home-title img {
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            height: 48px;
            object-fit: contain;
            padding: 6px;
            width: 48px;
        }

        .home-title p {
            color: #5b6764;
            font-size: 14px;
            line-height: 1.2;
            margin: 0;
        }

        .home-title h1 {
            color: #17211f;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.15;
            margin: 0;
        }

        .home-shell {
            margin: 0 auto;
            max-width: 1180px;
            padding: 0 0 28px;
        }

        .home-hero {
            align-items: center;
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            display: grid;
            gap: 20px;
            grid-template-columns: minmax(0, 1fr) 210px;
            margin-bottom: 18px;
            overflow: hidden;
            padding: 24px;
        }

        .home-eyebrow,
        .home-section-heading span {
            color: #0f766e;
            display: block;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .home-hero h2 {
            color: #17211f;
            font-size: 28px;
            font-weight: 850;
            line-height: 1.15;
            margin: 6px 0 10px;
        }

        .home-hero p {
            color: #4b5c58;
            font-size: 16px;
            line-height: 1.45;
            margin: 0;
            max-width: 650px;
        }

        .home-hero-image {
            border-radius: 8px;
            height: 150px;
            object-fit: cover;
            width: 100%;
        }

        .home-section {
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 18px;
        }

        .home-section-heading {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .home-section-heading h3 {
            color: #17211f;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.2;
            margin: 2px 0 0;
        }

        .home-metrics {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 14px;
        }

        .home-metric {
            background: #eef7f4;
            border: 1px solid #cfe5df;
            border-radius: 8px;
            color: #173f38;
            display: block;
            min-height: 92px;
            padding: 16px;
            text-decoration: none;
        }

        .home-metric:hover {
            color: #0f4f47;
            text-decoration: none;
        }

        .home-metric strong {
            display: block;
            font-size: 30px;
            font-weight: 850;
            line-height: 1;
            margin-bottom: 8px;
        }

        .home-metric span {
            display: block;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.25;
        }

        .home-actions {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .home-action {
            align-items: center;
            background: #ffffff;
            border: 1px solid #d8e1df;
            border-radius: 8px;
            color: #1f2f2c;
            display: flex;
            gap: 12px;
            min-height: 96px;
            padding: 16px;
            text-decoration: none;
            transition: border-color .15s ease, transform .15s ease;
        }

        .home-action:hover {
            border-color: #0f766e;
            color: #17211f;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .home-action i {
            align-items: center;
            background: #e9f6f3;
            border-radius: 8px;
            color: #0f766e;
            display: inline-flex;
            flex: 0 0 46px;
            font-size: 20px;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        .home-action-primary {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }

        .home-action-primary:hover {
            color: #ffffff;
        }

        .home-action-primary i {
            background: #ffffff;
            color: #0f766e;
        }

        .home-action strong,
        .home-action small {
            display: block;
            line-height: 1.25;
        }

        .home-action strong {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .home-action small {
            color: inherit;
            font-size: 13px;
            opacity: .82;
        }

        .home-help {
            align-items: center;
            background: #fff8db;
            border: 1px solid #ead77c;
            border-radius: 8px;
            color: #403700;
            display: flex;
            gap: 12px;
            padding: 14px 16px;
        }

        .home-help i {
            color: #0f766e;
            font-size: 22px;
        }

        .home-help p {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.35;
            margin: 0;
        }

        @media (max-width: 991.98px) {
            .home-shell {
                padding-bottom: 18px;
            }

            .home-hero {
                grid-template-columns: 1fr;
                padding: 18px;
            }

            .home-hero-image {
                height: 118px;
            }

            .home-metrics,
            .home-actions {
                grid-template-columns: 1fr;
            }

            .home-action,
            .home-metric {
                min-height: 86px;
            }
        }

        @media (max-width: 575.98px) {
            .content-header {
                padding: 12px 12px 4px;
            }

            .content {
                padding: 0 12px 18px;
            }

            .home-title img {
                height: 42px;
                width: 42px;
            }

            .home-title h1 {
                font-size: 22px;
            }

            .home-hero h2 {
                font-size: 24px;
            }

            .home-section {
                padding: 14px;
            }

            .home-action {
                align-items: flex-start;
                padding: 14px;
            }
        }
    </style>
@stop

@section('js')
@stop
