<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Fuel Station HOS') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background-image: url('{{ asset('assets/img/gallery/fuelstationimage.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0" style="background-color: rgba(0, 0, 0, 0.4);">
            <div>
                <a href="/">
                    <img src="{{ asset('assets/img/logo/site-logo.jpg') }}" alt="Site Logo" class="h-20 w-auto mx-auto" />
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800" style="background-color: rgba(255, 255, 255, 0.95);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
