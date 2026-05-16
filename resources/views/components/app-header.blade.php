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

            {{-- Logo con wire:navigate --}}
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 flex-shrink-0">
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

            {{-- Navegación desktop con wire:navigate --}}
            <nav class="hidden lg:flex gap-1 flex-1 justify-center max-w-2xl">
                @foreach ($links as $link)
                    <a href="{{ route($link['ruta']) }}"
                       wire:navigate
                       class="px-3 py-1.5 rounded-md text-sm transition-all duration-200 flex items-center gap-1.5 {{ request()->routeIs($link['ruta']) ? 'bg-blue-700 text-white font-medium shadow-sm' : 'text-blue-200 hover:text-white hover:bg-blue-800' }}">
                        @svg($link['icon'], 'w-4 h-4')
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Usuario (desktop) --}}
            <div class="hidden lg:flex items-center gap-3">

                {{-- Campana de notificaciones --}}
                @php
                    $notifs     = auth()->user()->unreadNotifications()->latest()->limit(5)->get();
                    $notifCount = $notifs->count();
                @endphp
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                            class="relative p-2 rounded-lg text-blue-200 hover:text-white hover:bg-blue-800 transition-colors"
                            :aria-expanded="open">
                        @svg('lucide-bell', 'w-5 h-5')
                        @if($notifCount > 0)
                            <span class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center leading-none">
                                {{ $notifCount > 9 ? '9+' : $notifCount }}
                            </span>
                        @endif
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-blue-100 overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-blue-50">
                            <p class="text-sm font-bold text-blue-900">Notificaciones</p>
                            @if($notifCount > 0)
                                <form method="POST" action="{{ route('notificaciones.leer') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-500 hover:text-blue-700">
                                        Marcar todas como leídas
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-blue-50">
                            @forelse($notifs as $notif)
                                @php
                                    $data      = $notif->data;
                                    $isMensaje = ($data['tipo'] ?? '') === 'mensaje_recibido';
                                @endphp
                                {{-- Enlace real: marca como leída y redirige al mensaje --}}
                                <a href="{{ route('notificaciones.abrir', $notif->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group">
                                    <div class="w-8 h-8 {{ $isMensaje ? 'bg-indigo-100' : 'bg-blue-100' }} rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        @if($isMensaje)
                                            @svg('lucide-mail', 'w-4 h-4 text-indigo-600')
                                        @else
                                            @svg('lucide-user-check', 'w-4 h-4 text-blue-600')
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-blue-900 group-hover:text-blue-700">
                                            {{ $data['titulo'] ?? 'Notificación' }}
                                        </p>
                                        <p class="text-xs text-blue-600 mt-0.5 line-clamp-2">{{ $data['mensaje'] ?? '' }}</p>
                                        <p class="text-[10px] text-blue-300 mt-1">{{ $notif->created_at->locale('es')->diffForHumans() }}</p>
                                    </div>
                                    @svg('lucide-chevron-right', 'w-3.5 h-3.5 text-blue-300 flex-shrink-0 self-center opacity-0 group-hover:opacity-100 transition-opacity')
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    @svg('lucide-bell-off', 'w-8 h-8 text-blue-200 mx-auto mb-2')
                                    <p class="text-xs text-blue-400">Sin notificaciones nuevas</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

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

                {{-- ❌ El logout NO lleva wire:navigate --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="p-2 rounded-lg text-blue-200 hover:text-white hover:bg-blue-800 transition-colors"
                            title="Cerrar sesión">
                        @svg('lucide-log-out', 'w-4 h-4')
                    </button>
                </form>
            </div>

            {{-- Botón usuario móvil --}}
            <button @click="menuAbierto = !menuAbierto"
                    class="lg:hidden p-2 rounded-lg text-white hover:bg-blue-800 transition-colors"
                    aria-label="Menú">
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

        {{-- Menú desplegable móvil --}}
        <div x-show="menuAbierto"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.away="menuAbierto = false"
             class="lg:hidden pb-4 border-t border-blue-800 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-blue-900">

            <div class="py-3">
                <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                <p class="text-xs text-blue-300 mt-0.5">{{ $rolLabel }}</p>
            </div>

            <div class="space-y-1">
                {{-- Perfil SÍ lleva wire:navigate --}}
                <a href="{{ route('profile.edit') }}"
                   wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-blue-200 hover:text-white hover:bg-blue-800 transition-colors">
                    @svg('lucide-user-circle', 'w-4 h-4')
                    Mi perfil
                </a>

                {{-- ❌ El logout NO lleva wire:navigate --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-blue-200 hover:text-white hover:bg-blue-800 transition-colors text-left">
                        @svg('lucide-log-out', 'w-4 h-4')
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>