<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>DISTRIBUIDORA H&J</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="relative text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col bg-gray-50 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center opacity-5 text-[5rem] lg:text-[8rem] font-bold z-0 select-none pointer-events-none">
            DISTRIBUIDORA H&J
        </div>

        <header class="relative z-10 w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
            <nav class="flex items-center justify-end gap-4">
                @if (Route::has('login'))
                    <nav class="flex items-center justify-end gap-4">
                        @auth
                            <a
                                href="{{ url('/home') }}"
                                class="inline-block px-5 py-1.5 text-gray-600 hover:text-blue-600 rounded-md text-sm font-medium"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-block px-5 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-md text-sm font-semibold transition shadow"
                            >
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-block px-5 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md text-sm font-medium transition"
                                >
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </nav>
        </header>

        <div class="relative z-10 flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row shadow-lg rounded-lg bg-white overflow-hidden">
                <div class="text-sm flex-1 p-6 lg:p-12">
                    <div class="mb-8">
                        <h1 class="text-2xl lg:text-3xl font-semibold text-gray-800 mb-3 leading-tight">
                            Sistema de Control H&J
                        </h1>
                        <p class="text-base text-gray-500 leading-relaxed">
                            Administra, gestiona y controla todos los aspectos de la empresa con nuestra plataforma integral.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mt-1"></div>
                            <div>
                                <h3 class="font-medium text-gray-800 mb-1">Acceso Seguro</h3>
                                <p class="text-gray-500 text-sm">Inicia sesión con tu cuenta asignada por el administrador.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mt-1"></div>
                            <div>
                                <h3 class="font-medium text-gray-800 mb-1">Control Completo</h3>
                                <p class="text-gray-500 text-sm">Accede a todas las funcionalidades del sistema según tu rol asignado.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:flex flex-col items-center justify-center w-[380px] bg-yellow-700 text-white p-10">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mb-4">
                        <img src="{{ asset('images/logo_white.webp') }}" alt="Logo H&J" class="w-16 h-16">
                    </div>
                    <h2 class="text-2xl font-semibold mb-1">H&J</h2>
                    <p class="text-white/90 text-sm text-center">Sistema de Control Empresarial</p>
                </div>
            </main>
        </div>
    </body>

</html>
