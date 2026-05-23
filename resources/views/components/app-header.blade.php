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
        'tutor'  => $linksTutor,
        default  => [],
    };

    $rolLabel = match($rol) {
        'alumno' => 'Alumno',
        'tutor'  => 'Tutor',
        'admin'  => 'Admin',
        default  => 'Usuario',
    };

    // Contenido de ayuda según rol (Tema 2 — 2.1 Diseño centrado en el usuario)
    $ayudaSeccionesAlumno = [
        ['icon' => 'lucide-home',        'titulo' => 'Inicio / Dashboard',     'desc' => 'Consulta tu resumen académico: promedio semestral, materias en curso, créditos aprobados y alertas activas. Si aún no tienes materias inscritas, inscríbete desde aquí.'],
        ['icon' => 'lucide-layout-grid', 'titulo' => 'Malla Curricular',        'desc' => 'Visualiza el avance en tu plan de estudios por semestre. Los estados son: ✓ Aprobada, ▶ En curso, ○ Disponible, ✗ Reprobada, 🔒 Bloqueada. Toca cualquier materia para ver detalles y prerrequisitos.'],
        ['icon' => 'lucide-book-open',   'titulo' => 'Inscripción de Materias', 'desc' => 'Inscríbete en materias del periodo actual. Las "Sugeridas para ti" corresponden a tu semestre y prerrequisitos cumplidos. Usa el carrito para seleccionar y confirma al final.'],
        ['icon' => 'lucide-award',       'titulo' => 'Mis Calificaciones',      'desc' => 'Captura tus calificaciones de cada parcial (P1, P2, P3). El promedio se calcula automáticamente. Necesitas materias inscritas para usar esta sección.'],
        ['icon' => 'lucide-clock',       'titulo' => 'Historial Académico',     'desc' => 'Registro completo de todas tus materias cursadas. Revisa tu promedio general y el indicador de permanencia académica (máx. 3 reprobadas). Si tienes materias reprobadas, encontrarás orientación de qué hacer a continuación.'],
        ['icon' => 'lucide-mail',        'titulo' => 'Mensajes',                'desc' => 'Comunícate directamente con tu tutor. Revisa el buzón frecuentemente para no perder avisos importantes.'],
    ];

    $ayudaSeccionesTutor = [
        ['icon' => 'lucide-home',           'titulo' => 'Inicio / Dashboard', 'desc' => 'Vista general de tus alumnos: resumen de alertas críticas, medias y bajas, estadísticas de promedio grupal y acceso rápido a las funciones principales.'],
        ['icon' => 'lucide-users',          'titulo' => 'Alumnos',            'desc' => 'Lista de alumnos con promedio e indicador de riesgo: ▲ Excelente (≥85), ◆ Regular (70–84), ● En riesgo (<70). Filtra por semestre o alertas.'],
        ['icon' => 'lucide-alert-triangle', 'titulo' => 'Centro de Alertas',  'desc' => 'Gestiona alertas ordenadas por prioridad. Marca alertas como atendidas. En la sección inferior configura los umbrales de cada regla; recibirás confirmación de guardado.'],
        ['icon' => 'lucide-mail',           'titulo' => 'Mensajes',           'desc' => 'Buzón de comunicación con tus alumnos asignados. Selecciona un alumno de la lista izquierda para ver la conversación.'],
        ['icon' => 'lucide-file-text',      'titulo' => 'Reportes',           'desc' => 'Genera reportes individuales, grupales y comparativos del desempeño académico para seguimiento tutorial.'],
    ];

    $ayudaSecciones = $rol === 'tutor' ? $ayudaSeccionesTutor : $ayudaSeccionesAlumno;
    $ayudaTitulo    = $rol === 'tutor' ? 'Guía del Portal Tutor' : 'Guía del Portal Alumno';
@endphp

<header class="sticky top-0 z-40 bg-blue-900 shadow-lg shadow-blue-900/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6" x-data="{ menuAbierto: false, ayudaAbierta: false }">
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

                {{-- Botón de Ayuda (Tema 2 — 2.1 Diseño centrado en el usuario) --}}
                <button @click="ayudaAbierta = true"
                        class="p-2 rounded-lg text-blue-200 hover:text-white hover:bg-blue-800 transition-colors"
                        title="Abrir guía de ayuda del sistema"
                        aria-label="Ayuda del sistema">
                    @svg('lucide-circle-help', 'w-5 h-5')
                </button>

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

        {{-- ═══ MODAL DE AYUDA (Tema 2 — 2.1/2.2/2.3/2.4) ═══
             Guía completa de uso del sistema para alumno y tutor.
             Cumple heurística 10 de Nielsen: ayuda y documentación contextual.
        --}}
        <div x-show="ayudaAbierta"
             x-cloak
             @keydown.escape.window="ayudaAbierta = false"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-blue-950/60 backdrop-blur-sm p-0 sm:p-4"
             @click.self="ayudaAbierta = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="bg-white w-full sm:max-w-2xl rounded-t-3xl sm:rounded-3xl shadow-2xl max-h-[90vh] flex flex-col overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full sm:translate-y-4 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-full sm:translate-y-4 opacity-0">

                {{-- Handle móvil --}}
                <div class="sm:hidden flex justify-center pt-3 pb-1 flex-shrink-0">
                    <div class="w-12 h-1 bg-blue-200 rounded-full"></div>
                </div>

                {{-- Header del modal --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-blue-100 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                            @svg('lucide-circle-help', 'w-5 h-5 text-blue-700')
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-blue-900">{{ $ayudaTitulo }}</h2>
                            <p class="text-xs text-blue-400">Guía de uso del sistema de tutoría</p>
                        </div>
                    </div>
                    <button @click="ayudaAbierta = false"
                            class="w-8 h-8 bg-blue-50 hover:bg-blue-100 rounded-full flex items-center justify-center transition-colors"
                            aria-label="Cerrar ayuda">
                        @svg('lucide-x', 'w-4 h-4 text-blue-600')
                    </button>
                </div>

                {{-- Contenido scrollable --}}
                <div class="overflow-y-auto flex-1 p-5 space-y-4">

                    {{-- Bienvenida --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                        <p class="text-sm font-semibold text-blue-900">
                            @if($rol === 'tutor') Bienvenido, tutor @else Bienvenido, alumno @endif
                        </p>
                        <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                            @if($rol === 'tutor')
                                Este portal te permite monitorear el desempeño académico de tus alumnos,
                                gestionar alertas de riesgo y mantener comunicación directa con ellos.
                                Usa la navegación superior para acceder a cada sección.
                            @else
                                Este portal te permite gestionar tu trayectoria académica: inscripción de materias,
                                captura de calificaciones, seguimiento de tu malla curricular y comunicación con tu tutor.
                                Navega usando la barra superior (computadora) o la barra inferior (móvil).
                            @endif
                        </p>
                    </div>

                    {{-- Secciones --}}
                    <div>
                        <p class="text-xs font-semibold text-blue-400 uppercase tracking-wide mb-3">Secciones del sistema</p>
                        <div class="space-y-3">
                            @foreach($ayudaSecciones as $sec)
                                <div class="bg-white border border-blue-100 rounded-2xl p-4 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            @svg($sec['icon'], 'w-4 h-4 text-blue-700')
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-blue-900">{{ $sec['titulo'] }}</p>
                                            <p class="text-xs text-blue-600 mt-1 leading-relaxed">{{ $sec['desc'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Leyenda de estados (solo alumno) --}}
                    @if($rol === 'alumno')
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">Estados de materias en Malla</p>
                            <div class="grid grid-cols-2 gap-1.5 text-xs">
                                <span class="flex items-center gap-2 text-emerald-700"><span class="font-bold">✓</span> Aprobada</span>
                                <span class="flex items-center gap-2 text-blue-700"><span class="font-bold">▶</span> En curso</span>
                                <span class="flex items-center gap-2 text-amber-700"><span class="font-bold">○</span> Disponible para inscribir</span>
                                <span class="flex items-center gap-2 text-red-700"><span class="font-bold">✗</span> Reprobada</span>
                                <span class="flex items-center gap-2 text-gray-500"><span class="font-bold">🔒</span> Bloqueada (prerrequisitos pendientes)</span>
                            </div>
                        </div>
                    @endif

                    {{-- Leyenda de riesgo (solo tutor) --}}
                    @if($rol === 'tutor')
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">Niveles de riesgo en lista de alumnos</p>
                            <div class="space-y-1.5 text-xs">
                                <span class="flex items-center gap-2 text-emerald-700"><span class="font-bold text-base leading-none">▲</span> Excelente — Promedio ≥ 85</span>
                                <span class="flex items-center gap-2 text-amber-600"><span class="font-bold text-base leading-none">◆</span> Regular — Promedio 70–84</span>
                                <span class="flex items-center gap-2 text-red-600"><span class="font-bold text-base leading-none">●</span> En riesgo — Promedio &lt; 70</span>
                            </div>
                            <p class="text-xs text-blue-500 mt-2">Los íconos combinan forma y color para ser accesibles a personas con daltonismo (WCAG 2.1).</p>
                        </div>
                    @endif

                    {{-- Consejos de usabilidad --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                        <div class="flex items-start gap-2">
                            @svg('lucide-lightbulb', 'w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5')
                            <div>
                                <p class="text-xs font-semibold text-amber-900">Consejos de uso</p>
                                <ul class="mt-1 space-y-1 text-xs text-amber-800 list-disc list-inside">
                                    @if($rol === 'alumno')
                                        <li>Revisa el dashboard al inicio de cada semana para estar al tanto de tus alertas.</li>
                                        <li>Captura tus calificaciones parciales tan pronto las recibas para mantener tu seguimiento actualizado.</li>
                                        <li>Si tienes materias reprobadas, comunícate con tu tutor antes de inscribir el siguiente semestre.</li>
                                        <li>Verifica los prerrequisitos en la Malla Curricular antes de planear tus inscripciones.</li>
                                    @else
                                        <li>Revisa las alertas críticas diariamente para atender a alumnos en riesgo a tiempo.</li>
                                        <li>Configura los umbrales de alerta según los criterios de tu grupo académico.</li>
                                        <li>Usa los reportes comparativos para identificar patrones de bajo rendimiento grupal.</li>
                                        <li>Mantén comunicación activa con alumnos que tengan materias reprobadas.</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Contacto de soporte --}}
                    <div class="bg-white border border-blue-100 rounded-2xl p-4 flex items-start gap-3">
                        @svg('lucide-mail', 'w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5')
                        <p class="text-xs text-blue-600">
                            ¿Necesitas más ayuda? Contacta a
                            <span class="font-semibold text-blue-800">soporte académico</span>
                            o comunícate con
                            @if($rol === 'alumno') tu tutor asignado @else la coordinación @endif
                            a través del módulo de Mensajes.
                        </p>
                    </div>
                </div>

                {{-- Footer del modal --}}
                <div class="px-5 py-3 border-t border-blue-50 bg-blue-50/50 flex-shrink-0">
                    <button @click="ayudaAbierta = false"
                            class="w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition-colors">
                        Entendido
                    </button>
                </div>
            </div>
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

                {{-- Ayuda (Tema 2) --}}
                <button @click="menuAbierto = false; ayudaAbierta = true"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-blue-200 hover:text-white hover:bg-blue-800 transition-colors text-left">
                    @svg('lucide-circle-help', 'w-4 h-4')
                    Ayuda del sistema
                </button>

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