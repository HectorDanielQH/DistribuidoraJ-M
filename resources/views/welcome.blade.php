<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>DISTRIBUIDORA H&J</title>

        <link rel="shortcut icon" href="{{ asset('images/logo_color.webp') }}" type="image/x-icon">

        <style>
            :root {
                color-scheme: light;
                --ink: #14211e;
                --muted: #54645f;
                --line: #d7e3df;
                --surface: #ffffff;
                --page: #f3f8f6;
                --brand: #0f766e;
                --brand-dark: #0b5c56;
                --warning: #f7d154;
            }

            * {
                box-sizing: border-box;
            }

            body {
                background: var(--page);
                color: var(--ink);
                font-family: Arial, Helvetica, sans-serif;
                margin: 0;
                min-height: 100vh;
            }

            .welcome-page {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .topbar {
                align-items: center;
                display: flex;
                justify-content: space-between;
                gap: 16px;
                margin: 0 auto;
                max-width: 1160px;
                padding: 18px 22px;
                width: 100%;
            }

            .brand {
                align-items: center;
                color: var(--ink);
                display: inline-flex;
                gap: 10px;
                min-width: 0;
                text-decoration: none;
            }

            .brand img {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: 8px;
                height: 48px;
                object-fit: contain;
                padding: 6px;
                width: 48px;
            }

            .brand span {
                display: block;
                font-size: 13px;
                font-weight: 700;
                line-height: 1.2;
            }

            .brand strong {
                display: block;
                font-size: 18px;
                font-weight: 850;
                line-height: 1.2;
            }

            .topbar-action {
                background: var(--brand);
                border-radius: 8px;
                color: #ffffff;
                display: inline-flex;
                font-size: 15px;
                font-weight: 800;
                justify-content: center;
                min-height: 46px;
                min-width: 132px;
                padding: 13px 18px;
                text-decoration: none;
            }

            .topbar-action:hover {
                background: var(--brand-dark);
            }

            .hero {
                align-items: center;
                display: grid;
                flex: 1;
                gap: 28px;
                grid-template-columns: minmax(0, 1fr) minmax(280px, 430px);
                margin: 0 auto;
                max-width: 1160px;
                padding: 20px 22px 56px;
                width: 100%;
            }

            .hero-copy {
                min-width: 0;
            }

            .eyebrow {
                color: var(--brand);
                display: block;
                font-size: 14px;
                font-weight: 850;
                letter-spacing: 0;
                margin-bottom: 12px;
                text-transform: uppercase;
            }

            h1 {
                color: var(--ink);
                font-size: 48px;
                font-weight: 900;
                line-height: 1.05;
                margin: 0 0 16px;
                max-width: 760px;
            }

            .lead {
                color: var(--muted);
                font-size: 20px;
                line-height: 1.45;
                margin: 0 0 26px;
                max-width: 720px;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 26px;
            }

            .button-primary,
            .button-secondary {
                align-items: center;
                border-radius: 8px;
                display: inline-flex;
                font-size: 17px;
                font-weight: 850;
                justify-content: center;
                min-height: 56px;
                padding: 16px 22px;
                text-decoration: none;
            }

            .button-primary {
                background: var(--brand);
                color: #ffffff;
                min-width: 190px;
            }

            .button-primary:hover {
                background: var(--brand-dark);
            }

            .button-secondary {
                background: #ffffff;
                border: 1px solid var(--line);
                color: var(--ink);
                min-width: 190px;
            }

            .quick-guide {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .guide-item {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: 8px;
                min-height: 116px;
                padding: 16px;
            }

            .guide-item strong {
                color: var(--ink);
                display: block;
                font-size: 17px;
                line-height: 1.2;
                margin-bottom: 8px;
            }

            .guide-item span {
                color: var(--muted);
                display: block;
                font-size: 15px;
                line-height: 1.35;
            }

            .visual {
                background: #ffffff;
                border: 1px solid var(--line);
                border-radius: 8px;
                overflow: hidden;
            }

            .visual img {
                display: block;
                height: 280px;
                object-fit: cover;
                width: 100%;
            }

            .visual-panel {
                padding: 18px;
            }

            .visual-panel img {
                height: 64px;
                margin-bottom: 12px;
                object-fit: contain;
                width: 64px;
            }

            .visual-panel strong {
                display: block;
                font-size: 22px;
                font-weight: 900;
                line-height: 1.15;
                margin-bottom: 8px;
            }

            .visual-panel span {
                color: var(--muted);
                display: block;
                font-size: 16px;
                line-height: 1.35;
            }

            .help-strip {
                background: #fff8db;
                border-top: 1px solid #ecd982;
                color: #403700;
                font-size: 15px;
                font-weight: 800;
                line-height: 1.35;
                padding: 14px 22px;
                text-align: center;
            }

            @media (max-width: 920px) {
                .hero {
                    grid-template-columns: 1fr;
                    padding-top: 10px;
                }

                h1 {
                    font-size: 38px;
                }

                .visual {
                    order: -1;
                }

                .visual img {
                    height: 190px;
                }
            }

            @media (max-width: 640px) {
                .topbar {
                    align-items: stretch;
                    flex-direction: column;
                    padding: 14px;
                }

                .topbar-action {
                    width: 100%;
                }

                .hero {
                    gap: 18px;
                    padding: 8px 14px 32px;
                }

                h1 {
                    font-size: 31px;
                }

                .lead {
                    font-size: 18px;
                }

                .actions,
                .quick-guide {
                    grid-template-columns: 1fr;
                }

                .actions {
                    display: grid;
                }

                .button-primary,
                .button-secondary {
                    width: 100%;
                }

                .visual img {
                    height: 150px;
                }
            }
        </style>
    </head>
    <body>
        <div class="welcome-page">
            <header class="topbar">
                <a class="brand" href="{{ url('/') }}" aria-label="Distribuidora H&J">
                    <img src="{{ asset('images/logo_color.webp') }}" alt="Logo H&J">
                    <span>
                        Sistema de trabajo
                        <strong>Distribuidora H&J</strong>
                    </span>
                </a>

                @if (Route::has('login'))
                    @auth
                        <a class="topbar-action" href="{{ url('/home') }}">Ir al inicio</a>
                    @else
                        <a class="topbar-action" href="{{ route('login') }}">Iniciar sesion</a>
                    @endauth
                @endif
            </header>

            <main class="hero">
                <section class="hero-copy" aria-labelledby="welcome-title">
                    <span class="eyebrow">Control simple para el negocio</span>
                    <h1 id="welcome-title">Pedidos, rutas, stock y ventas en un solo lugar.</h1>
                    <p class="lead">
                        Entra con tu usuario y trabaja paso a paso. El sistema separa lo que necesita cada persona:
                        vendedor, administrador o contabilidad.
                    </p>

                    <div class="actions">
                        @auth
                            <a class="button-primary" href="{{ url('/home') }}">Entrar al sistema</a>
                        @else
                            <a class="button-primary" href="{{ route('login') }}">Iniciar sesion</a>
                        @endauth
                        <a class="button-secondary" href="#guia">Ver para que sirve</a>
                    </div>

                    <div class="quick-guide" id="guia">
                        <div class="guide-item">
                            <strong>Vendedores</strong>
                            <span>Ven sus clientes, registran pedidos y revisan sus ventas.</span>
                        </div>
                        <div class="guide-item">
                            <strong>Administracion</strong>
                            <span>Controla productos, rutas, clientes, pedidos y despachos.</span>
                        </div>
                        <div class="guide-item">
                            <strong>Contabilidad</strong>
                            <span>Revisa ventas por dia, por preventista y resultados.</span>
                        </div>
                    </div>
                </section>

                <aside class="visual" aria-label="Distribuidora H&J">
                    <img src="{{ asset('images/background.webp') }}" alt="Productos de distribuidora">
                    <div class="visual-panel">
                        <img src="{{ asset('images/logo_color.webp') }}" alt="Logo H&J">
                        <strong>Hecho para usar rapido desde celular.</strong>
                        <span>Botones grandes, palabras claras y acceso directo al trabajo diario.</span>
                    </div>
                </aside>
            </main>

            <div class="help-strip">
                Si no tienes usuario o contrasena, pide acceso al administrador.
            </div>
        </div>
    </body>
</html>
