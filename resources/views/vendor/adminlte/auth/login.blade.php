@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

@section('css')
    <style>
        body.login-page {
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

        .login-card-body {
            color: #54645f;
            padding: 24px;
        }

        .hj-login-intro {
            color: #54645f;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
            margin: 0 0 18px;
            text-align: center;
        }

        .hj-field-label {
            color: #14211e;
            display: block;
            font-size: 14px;
            font-weight: 850;
            margin-bottom: 7px;
        }

        .hj-login-form .input-group {
            margin-bottom: 16px;
        }

        .hj-login-form .form-control {
            border-color: #cfdcd8;
            border-radius: 8px 0 0 8px;
            font-size: 16px;
            height: 52px;
        }

        .hj-login-form .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .16);
        }

        .hj-login-form .input-group-text {
            background: #eef7f4;
            border-color: #cfdcd8;
            border-radius: 0 8px 8px 0;
            color: #0f766e;
            justify-content: center;
            min-width: 52px;
        }

        .hj-submit {
            background: #0f766e;
            border: 1px solid #0f766e;
            border-radius: 8px;
            color: #ffffff;
            font-size: 17px;
            font-weight: 900;
            min-height: 54px;
            width: 100%;
        }

        .hj-submit:hover,
        .hj-submit:focus {
            background: #0b5c56;
            border-color: #0b5c56;
            color: #ffffff;
        }

        .hj-login-help {
            background: #fff8db;
            border: 1px solid #ecd982;
            border-radius: 8px;
            color: #403700;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.35;
            margin: 18px 0 0;
            padding: 12px;
            text-align: center;
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

            .login-card-body {
                padding: 20px 18px;
            }
        }
    </style>
@stop

@section('auth_header', 'Bienvenido')

@section('auth_body')
    <p class="hj-login-intro">Ingresa tu usuario y contrasena para continuar.</p>

    <form action="{{ $loginUrl }}" method="post" class="hj-login-form">
        @csrf

        <label class="hj-field-label" for="username">Usuario</label>
        <div class="input-group mb-3">
            <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror"
                value="{{ old('username') }}" placeholder="Escribe tu usuario" autocomplete="username" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('username')
                <span class="invalid-feedback" role="alert">
                    <strong>
                        @if($message == 'validation.required')
                            {{ __('Campo requerido') }}
                        @elseif($message == 'auth.failed')
                            {{ __('Usuario incorrecto') }}
                        @else
                            {{ $message }}
                        @endif
                    </strong>
                </span>
            @enderror
        </div>

        <label class="hj-field-label" for="password">Contrasena</label>
        <div class="input-group mb-3">
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Escribe tu contrasena" autocomplete="current-password">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>
                        @if($message == 'validation.required')
                            {{ __('Campo requerido') }}
                        @elseif($message == 'auth.failed')
                            {{ __('Contrasena incorrecta') }}
                        @else
                            {{ $message }}
                        @endif
                    </strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn hj-submit">
            <span class="fas fa-sign-in-alt"></span>
            Entrar al sistema
        </button>

        <p class="hj-login-help">Si olvidaste tus datos, pide ayuda al administrador.</p>
    </form>
@stop

@section('auth_footer')
@stop
