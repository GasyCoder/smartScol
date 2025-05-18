<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="pageroot" class="{{ dark_mode() ? 'dark' : '' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="BEZARA Florent">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Logiciel de faculté de médecine de l'Université de Mahajanga - SmartScol">
        <link rel="icon" type="image/png" href="{{ asset('images/favicon/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}" />
        <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}" />
        <title>@isset($title) {{ $title }} | @endisset{{ config('app.desc') }}</title>

        @vite(['resources/dashwin/css/app.css'])

    </head>
    <body class="bg-gray-50 dark:bg-gray-1000 font-body text-sm leading-relaxed text-slate-600 dark:text-slate-300 font-normal min-w-[320px]" dir="{{ gcs('direction', 'ltr') }}">
        <div class="nk-app-root">
            <div class="nk-main">
                <div class="flex flex-col min-h-screen nk-wrap">
                    @yield('content')
                </div>
            </div>
        </div><!-- root -->
        @stack('modals')
        @include('layouts.partials.off-canvas')
        <!-- JavaScript -->
        @vite(['resources/dashwin/js/scripts.js'])
        @stack('scripts')
    </body>
</html>
