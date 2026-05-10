<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
        <meta name="theme-color" content="#1e3a8a">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Tutoría') }}</title>

        {{-- Fuentes --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Assets de Vite (Tailwind + JS con Alpine incluido vía Livewire) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Livewire styles: necesario para que componentes como RegistroAlumno funcionen --}}
        @livewireStyles
    </head>
    <body class="font-sans antialiased">

        {{ $slot }}

        {{-- Livewire scripts: también inicializa Alpine.js --}}
        @livewireScripts
    </body>
</html>