<x-guest-layout>
    <div class="min-h-screen bg-[#f0f7ff]">
        <div class="min-h-screen flex flex-col lg:flex-row">

            {{-- ═══════════════════════════════════════════════════════ --}}
            {{-- PANEL IZQUIERDO (desktop) / TOP HEADER (móvil)         --}}
            {{-- ═══════════════════════════════════════════════════════ --}}
            <div class="
                relative overflow-hidden
                bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700
                lg:w-1/2 lg:min-h-screen
                px-6 py-8 sm:px-10 sm:py-10
                lg:p-12
            ">
                {{-- Decoraciones --}}
                <div class="absolute top-0 right-0 w-64 h-64 lg:w-96 lg:h-96 bg-blue-600/30 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 lg:w-80 lg:h-80 bg-blue-950/40 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-between h-full text-white gap-6 lg:gap-8">

                    {{-- Logo y título --}}
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center border border-white/20 flex-shrink-0">
                            @svg('lucide-graduation-cap', 'w-6 h-6 text-white')
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-base sm:text-lg leading-tight">Sistema de Tutoría</p>
                            <p class="text-blue-200 text-xs">Gestión Académica Universitaria</p>
                        </div>
                    </div>

                    {{-- Hero (solo se ve completo en desktop; resumido en móvil) --}}
                    <div class="space-y-4 lg:space-y-8 py-4 lg:py-0">
                        <div>
                            <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold leading-tight lg:mb-4">
                                <span class="hidden lg:inline">
                                    Seguimiento<br>Académico<br>
                                </span>
                                <span class="lg:hidden">Seguimiento Académico </span>
                                <span class="text-blue-300">Inteligente</span>
                            </h1>
                            <p class="hidden lg:block text-blue-200 text-lg leading-relaxed">
                                Plataforma completa para el seguimiento del rendimiento estudiantil con análisis en tiempo real.
                            </p>
                            <p class="lg:hidden text-blue-200 text-sm leading-relaxed mt-2">
                                Tu progreso académico en un solo lugar.
                            </p>
                        </div>

                        {{-- Features: ocultas en móvil para ahorrar espacio --}}
                        <div class="hidden lg:block space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/15 flex-shrink-0">
                                    @svg('lucide-book-open', 'w-4 h-4 text-blue-200')
                                </div>
                                <p class="text-blue-100 text-sm">Seguimiento de materias y calificaciones</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/15 flex-shrink-0">
                                    @svg('lucide-trending-up', 'w-4 h-4 text-blue-200')
                                </div>
                                <p class="text-blue-100 text-sm">Dashboards analíticos de rendimiento</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/15 flex-shrink-0">
                                    @svg('lucide-users', 'w-4 h-4 text-blue-200')
                                </div>
                                <p class="text-blue-100 text-sm">Comunicación directa alumno-tutor</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer (solo desktop) --}}
                    <div class="hidden lg:flex items-center gap-2">
                        <div class="h-px flex-1 bg-blue-700"></div>
                        <p class="text-blue-400 text-xs">© {{ date('Y') }} Sistema de Tutoría</p>
                        <div class="h-px flex-1 bg-blue-700"></div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════ --}}
            {{-- PANEL DERECHO (formulario)                             --}}
            {{-- ═══════════════════════════════════════════════════════ --}}
            <div class="
                flex-1 flex items-center justify-center
                px-4 py-8 sm:px-6 sm:py-12
                lg:w-1/2 lg:p-8
                -mt-6 lg:mt-0
                relative z-20
            ">
                <div class="w-full max-w-md">

                    {{-- Card del formulario --}}
                    <div class="bg-white rounded-2xl shadow-xl shadow-blue-900/10 border border-blue-100 p-6 sm:p-8">

                        {{-- Header del form --}}
                        <div class="text-center mb-6 sm:mb-8">
                            <h2 class="text-xl sm:text-2xl font-bold text-blue-900 mb-1">
                                Acceso al Sistema
                            </h2>
                            <p class="text-blue-400 text-sm">
                                Ingresa tus credenciales
                            </p>
                        </div>

                        {{-- Mensajes de error --}}
                        @if ($errors->any())
                            <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2">
                                @svg('lucide-alert-circle', 'w-4 h-4 text-red-600 flex-shrink-0 mt-0.5')
                                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                            </div>
                        @endif

                        {{-- Mensaje de sesión --}}
                        @if (session('status'))
                            <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <p class="text-sm text-emerald-700">{{ session('status') }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            {{-- Campo identificador --}}
                            <div>
                                <label for="identificador" class="block text-sm font-medium text-blue-900 mb-1.5">
                                    Matrícula, N° empleado o email
                                </label>
                                <div class="relative">
                                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                        @svg('lucide-user', 'w-5 h-5 text-blue-300')
                                    </div>
                                    <input
                                        id="identificador"
                                        type="text"
                                        name="identificador"
                                        value="{{ old('identificador') }}"
                                        placeholder="Ej: 190039"
                                        class="w-full pl-11 pr-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition bg-blue-50/50 text-blue-900 placeholder-blue-300 text-base"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        inputmode="text"
                                    >
                                </div>
                            </div>

                            {{-- Campo contraseña --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-sm font-medium text-blue-900">
                                        Contraseña
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                            ¿Olvidaste?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative" x-data="{ mostrar: false }">
                                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                        @svg('lucide-lock', 'w-5 h-5 text-blue-300')
                                    </div>
                                    <input
                                        id="password"
                                        :type="mostrar ? 'text' : 'password'"
                                        name="password"
                                        placeholder="••••••••"
                                        class="w-full pl-11 pr-12 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition bg-blue-50/50 text-blue-900 placeholder-blue-300 text-base"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <button
                                        type="button"
                                        @click="mostrar = !mostrar"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 hover:text-blue-600 transition-colors p-1"
                                        tabindex="-1"
                                    >
                                        <span x-show="!mostrar">@svg('lucide-eye', 'w-5 h-5')</span>
                                        <span x-show="mostrar" x-cloak>@svg('lucide-eye-off', 'w-5 h-5')</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Recordar sesión --}}
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    name="remember"
                                    class="w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500 cursor-pointer"
                                >
                                <label for="remember" class="text-sm text-blue-700 cursor-pointer select-none">
                                    Mantener sesión iniciada
                                </label>
                            </div>

                            {{-- Botón de submit --}}
                            <button
                                type="submit"
                                class="w-full bg-blue-700 text-white py-3.5 rounded-xl font-semibold hover:bg-blue-800 active:bg-blue-900 transition-colors shadow-md shadow-blue-700/25 text-base flex items-center justify-center gap-2"
                            >
                                <span>Iniciar Sesión</span>
                                @svg('lucide-log-in', 'w-5 h-5')
                            </button>
                        </form>
                    </div>

                    {{-- Ayuda al pie --}}
                    <div class="mt-6 text-center">
                        <p class="text-xs text-blue-400">
                            ¿Problemas para acceder?
                            <a href="#" class="text-blue-600 font-medium hover:underline">Contacta a soporte</a>
                        </p>
                    </div>

                    {{-- Footer móvil --}}
                    <div class="lg:hidden mt-8 text-center">
                        <p class="text-xs text-blue-300">© {{ date('Y') }} Sistema de Tutoría</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estilos extra para Alpine x-cloak --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-guest-layout>