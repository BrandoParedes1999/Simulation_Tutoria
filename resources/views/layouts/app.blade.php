<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Tutoría') }}</title>

    {{-- Fuente --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Chart.js para gráficas --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="font-sans antialiased bg-[#f0f7ff]">
    <div class="min-h-screen">
        <x-app-header />

        @isset($header)
            <header class="bg-white shadow-sm border-b border-blue-100 lg:block hidden">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="pb-20 lg:pb-0">
            {{ $slot }}
        </main>

        <x-mobile-nav />
    </div>

    @livewireScripts

    {{-- Sistema de toasts --}}
    <div x-data="{
            toasts: [],
            add(tipo, mensaje) {
                const id = Date.now();
                this.toasts.push({ id, tipo, mensaje });
                setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 3500);
            }
        }"
        @toast.window="add($event.detail.tipo, $event.detail.mensaje)"
        class="fixed top-4 left-4 right-4 z-50 space-y-2 lg:left-auto lg:right-4 lg:max-w-sm">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-[-20px] opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'bg-emerald-600 border-emerald-700': toast.tipo === 'success',
                    'bg-red-600 border-red-700': toast.tipo === 'error',
                    'bg-blue-600 border-blue-700': toast.tipo === 'info',
                }"
                class="text-white px-4 py-3 rounded-xl shadow-lg border flex items-center gap-2">
                <span x-text="toast.mensaje" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>
</body>
</html>