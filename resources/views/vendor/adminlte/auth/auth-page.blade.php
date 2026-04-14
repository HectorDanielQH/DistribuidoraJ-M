@extends('adminlte::master')

@php
    $authType = $authType ?? 'login';
    $dashboardUrl = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboardUrl = $dashboardUrl ? route($dashboardUrl) : '';
    } else {
        $dashboardUrl = $dashboardUrl ? url($dashboardUrl) : '';
    }

    $bodyClasses = "{$authType}-page";

    if (! empty(config('adminlte.layout_dark_mode', null))) {
        $bodyClasses .= ' dark-mode';
    }
@endphp

@section('adminlte_css')
    <style>
        body.login-page,
        body.register-page {
            background: #f3f8f6;
            min-height: 100vh;
        }

        .hj-auth-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, .95fr);
            min-height: 100vh;
            width: 100%;
        }

        .hj-auth-info {
            align-content: space-between;
            background: #ffffff;
            border-right: 1px solid #d7e3df;
            display: grid;
            gap: 36px;
            min-height: 100vh;
            padding: 38px;
        }

        .hj-auth-brand {
            align-items: center;
            color: #14211e;
            display: inline-flex;
            gap: 12px;
            text-decoration: none;
            width: max-content;
        }

        .hj-auth-brand:hover {
            color: #14211e;
            text-decoration: none;
        }

        .hj-auth-brand img {
            background: #ffffff;
            border: 1px solid #d7e3df;
            border-radius: 8px;
            height: 54px;
            object-fit: contain;
            padding: 7px;
            width: 54px;
        }

        .hj-auth-brand span {
            color: #54645f;
            display: block;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.15;
        }

        .hj-auth-brand strong {
            color: #14211e;
            display: block;
            font-size: 24px;
            font-weight: 900;
            line-height: 1.1;
        }

        .hj-auth-copy {
            max-width: 660px;
        }

        .hj-auth-copy span {
            color: #0f766e;
            display: block;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .hj-auth-copy h1 {
            color: #14211e;
            font-size: 46px;
            font-weight: 900;
            line-height: 1.06;
            margin: 0 0 16px;
        }

        .hj-auth-copy p {
            color: #54645f;
            font-size: 20px;
            line-height: 1.45;
            margin: 0;
        }

        .hj-auth-photo {
            border-radius: 8px;
            display: block;
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .hj-auth-panel {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 30px;
        }

        .hj-login-box {
            margin: 0;
            max-width: 440px;
            width: 100%;
        }

        .hj-login-logo {
            margin-bottom: 18px;
        }

        .hj-login-logo a {
            align-items: center;
            color: #14211e;
            display: inline-flex;
            font-size: 24px;
            font-weight: 900;
            gap: 10px;
            justify-content: center;
            text-decoration: none;
            width: 100%;
        }

        .hj-login-logo img {
            border-radius: 8px;
            height: 46px;
            object-fit: contain;
            width: 46px;
        }

        .hj-auth-card {
            border: 1px solid #d7e3df;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(20, 33, 30, .12);
            overflow: hidden;
        }

        .hj-auth-card-header {
            background: #ffffff;
            border-bottom: 1px solid #e4ece9;
            padding: 22px 24px 12px;
        }

        .hj-auth-card-header h3 {
            color: #14211e;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.2;
            margin: 0;
        }

        @media (max-width: 991.98px) {
            .hj-auth-shell {
                grid-template-columns: 1fr;
            }

            .hj-auth-info {
                border-right: 0;
                min-height: auto;
                padding: 24px 22px 0;
            }

            .hj-auth-copy h1 {
                font-size: 34px;
            }

            .hj-auth-copy p {
                font-size: 18px;
            }

            .hj-auth-photo {
                display: none;
            }

            .hj-auth-panel {
                min-height: auto;
                padding: 22px;
            }
        }

        @media (max-width: 575.98px) {
            .hj-auth-info {
                gap: 18px;
                padding: 16px 14px 0;
            }

            .hj-auth-copy h1 {
                font-size: 28px;
            }

            .hj-auth-copy p {
                font-size: 16px;
            }

            .hj-auth-panel {
                padding: 16px 14px 24px;
            }

            .hj-login-logo {
                display: none;
            }

            .hj-auth-card-header {
                padding: 20px 18px 10px;
            }
        }
    </style>
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ $bodyClasses }}@stop

@section('body')
    <div class="hj-auth-shell">
        <section class="hj-auth-info" aria-label="Distribuidora H&J">
            <a class="hj-auth-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo_color.webp') }}" alt="Logo H&J">
                <span>
                    Distribuidora
                    <strong>H&J</strong>
                </span>
            </a>

            <div class="hj-auth-copy">
                <span>Acceso seguro</span>
                <h1>Control de pedidos, rutas, stock y ventas.</h1>
                <p>Entra con tu usuario asignado y trabaja solo con las opciones que corresponden a tu rol.</p>
            </div>

            <img class="hj-auth-photo" src="{{ asset('images/background.webp') }}" alt="Productos de la distribuidora">
        </section>

        <section class="hj-auth-panel" aria-label="Inicio de sesion">
            <div class="{{ $authType }}-box hj-login-box">

                {{-- Logo --}}
                <div class="{{ $authType }}-logo hj-login-logo">
                    <a href="{{ $dashboardUrl }}">

                        {{-- Logo Image --}}
                        @if (config('adminlte.auth_logo.enabled', false))
                            <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                                 alt="{{ config('adminlte.auth_logo.img.alt') }}"
                                 @if (config('adminlte.auth_logo.img.class', null))
                                    class="{{ config('adminlte.auth_logo.img.class') }}"
                                 @endif
                                 @if (config('adminlte.auth_logo.img.width', null))
                                    width="{{ config('adminlte.auth_logo.img.width') }}"
                                 @endif
                                 @if (config('adminlte.auth_logo.img.height', null))
                                    height="{{ config('adminlte.auth_logo.img.height') }}"
                                 @endif>
                        @else
                            <img src="{{ asset(config('adminlte.logo_img')) }}"
                                 alt="{{ config('adminlte.logo_img_alt') }}" height="50">
                        @endif

                        {{-- Logo Label --}}
                        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}

                    </a>
                </div>

                {{-- Card Box --}}
                <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }} hj-auth-card">

                    {{-- Card Header --}}
                    @hasSection('auth_header')
                        <div class="card-header {{ config('adminlte.classes_auth_header', '') }} hj-auth-card-header">
                            <h3 class="card-title float-none text-center">
                                @yield('auth_header')
                            </h3>
                        </div>
                    @endif

                    {{-- Card Body --}}
                    <div class="card-body {{ $authType }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                        @yield('auth_body')
                    </div>

                    {{-- Card Footer --}}
                    @hasSection('auth_footer')
                        <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                            @yield('auth_footer')
                        </div>
                    @endif

                </div>

            </div>
        </section>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
