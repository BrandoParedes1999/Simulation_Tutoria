@php
    $user = auth()->user();
    $rol = $user->rol;

    $linksAlumno = [
        ['ruta' => 'alumno.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home'],
        ['ruta' => 'alumno.malla', 'label' => 'Malla', 'icon' => 'lucide-layout-grid'],
        ['ruta' => 'alumno.materias', 'label' => 'Materias', 'icon' => 'lucide-book-open'],
        ['ruta' => 'alumno.calificaciones', 'label' => 'Calif.', 'icon' => 'lucide-award'],
        ['ruta' => 'alumno.historial', 'label' => 'Historial', 'icon' => 'lucide-clock'],
        ['ruta' => 'alumno.mensajes', 'label' => 'Mensajes', 'icon' => 'lucide-mail'],
    ];

    $linksTutor = [
        ['ruta' => 'tutor.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home'],
        ['ruta' => 'tutor.alumnos', 'label' => 'Alumnos', 'icon' => 'lucide-users'],
        ['ruta' => 'tutor.alertas', 'label' => 'Alertas', 'icon' => 'lucide-alert-triangle'],
        ['ruta' => 'tutor.mensajes', 'label' => 'Mensajes', 'icon' => 'lucide-mail'],
        ['ruta' => 'tutor.reportes', 'label' => 'Reportes', 'icon' => 'lucide-file-text'],
    ];

    $links = match($rol) {
        'alumno' => $linksAlumno,
        'tutor' => $linksTutor,
        default => [],
    };

    $rolLabel = match($rol) {
        'alumno' => 'Alumno',
        'tutor' => 'Tutor',
        'admin' => 'Admin',
        default => 'Usuario',
    };
@endphp

<header class="sticky top-0 z-40 bg-blue-900 shadow-lg shadow-blue-900/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6" x-data="{ menuAbierto: false }">
        <div class="h-16 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center shadow-md">
                    @svg('lucide-graduation-cap', 'w-5 h-5 text-white')
                </div>
                <span class="font-bold text-white text-sm sm:text-base tracking-wide hidden sm:block">
                    Sistema de Tutoría
                </span>
                <span class="font-bold text-white text-sm tracking-wide sm:hidden">
                    Tutoría
                </span>
            </a>

            {{-- Navegación desktop --}}
            <nav class="hidden lg:flex gap-1 flex-1 justify-center max-w-2xl">
                @foreach ($links as $link)
                    
                        href="{{ route($link['ruta']) }}"
                        class="px-3 py-1.5 rounded-md text-sm transition-all duration-200 flex items-center gap-1.5
                            {{ request()->routeIs($link['ruta'])
                                ? 'bg-blue-700 text-white font-medium shadow-sm'
                                : 'text-blue-200 hover:text-white hover:bg-blue-800' }}"
                    >
                        @svg($link['icon'], 'w-4 h-4')
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Usuario (desktop) --}}
            <div class="hidden lg:flex items-center gap-3">
                <div class="flex items-center gap-2.5 pl-3 border-l border-blue-700">
                    @if($user->foto)
                        <img src="{{ $user->foto }}" class="w-8 h-8 rounded-full ring-2 ring-blue-500 object-cover" alt="{{ $user->name }}">
                    @else
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-blue-400">
                            <span class="text-white text-xs font-bold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-white leading-none">{{ $user->name }}</p>
                        <p class="text-xs text-blue-300 mt-0.5">{{ $rolLabel }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="p-2 rounded-lg text-blue-200 hover:text-white hover:bg-blue-800 transition-colors"
                        title="Cerrar sesión"
                    >
                        @svg('lucide-log-out', 'w-4 h-4')
                    </button>
                </form>
            </div>

            {{-- Botón usuario móvil --}}
            <button
                @click="menuAbierto = !menuAbierto"
                class="lg:hidden p-2 rounded-lg text-white hover:bg-blue-800 transition-colors"
                aria-label="Menú"
            >
                <div class="flex items-center gap-2">
                    @if($user->foto)
                        <img src="{{ $user->foto }}" class="w-7 h-7 rounded-full ring-2 ring-blue-500 object-cover" alt="{{ $user->name }}">
                    @else
                        <div class="w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-blue-400">
                            <span class="text-white text-xs font-bold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    @svg('lucide-chevron-down', 'w-4 h-4 transition-transform')
                </div>
            </button>
        </div>

        {{-- Menú desplegable móvil (usuario) --}}
        <div
            x-show="menuAbierto"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.away="menuAbierto = false"
            class="lg:hidden pb-4 border-t border-blue-800 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-blue-900"
        >
            <div class="py-3">
                <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                <p class="text-xs text-blue-300 mt-0.5">{{ $rolLabel }}</p>
            </div>

            <div class="space-y-1">
                
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-blue-200 hover:text-white hover:bg-blue-800 transition-colors"
                >
                    @svg('lucide-user-circle', 'w-4 h-4')
                    Mi perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-blue-200 hover:text-white hover:bg-blue-800 transition-colors text-left"
                    >
                        @svg('lucide-log-out', 'w-4 h-4')
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>