@php
    $user = auth()->user();
    $rol = $user->rol;

    // Contador de alertas/mensajes no leídos (solo alumno)
    $badgeAlertas = 0;
    $badgeMensajes = 0;

    if ($rol === 'alumno' && $user->alumno) {
        $badgeAlertas = \App\Models\Alerta::where('alumno_id', $user->alumno->id)
            ->where('atendida', false)
            ->count();
        $badgeMensajes = \App\Models\Mensaje::where('destinatario_id', $user->id)
            ->whereNull('leido_en')
            ->count();
    } elseif ($rol === 'tutor' && $user->tutor) {
        $alumnosIds = $user->tutor->alumnosAsignados()->pluck('id');
        $badgeAlertas = \App\Models\Alerta::whereIn('alumno_id', $alumnosIds)
            ->where('atendida', false)
            ->count();
        $badgeMensajes = \App\Models\Mensaje::where('destinatario_id', $user->id)
            ->whereNull('leido_en')
            ->count();
    }

    $linksAlumno = [
        ['ruta' => 'alumno.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home', 'badge' => 0],
        ['ruta' => 'alumno.malla', 'label' => 'Malla', 'icon' => 'lucide-layout-grid', 'badge' => 0],
        ['ruta' => 'alumno.materias', 'label' => 'Materias', 'icon' => 'lucide-book-open', 'badge' => 0],
        ['ruta' => 'alumno.calificaciones', 'label' => 'Calif.', 'icon' => 'lucide-award', 'badge' => 0],
        ['ruta' => 'alumno.mensajes', 'label' => 'Buzón', 'icon' => 'lucide-mail', 'badge' => $badgeMensajes],
    ];

    $linksTutor = [
        ['ruta' => 'tutor.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home', 'badge' => 0],
        ['ruta' => 'tutor.alumnos', 'label' => 'Alumnos', 'icon' => 'lucide-users', 'badge' => 0],
        ['ruta' => 'tutor.alertas', 'label' => 'Alertas', 'icon' => 'lucide-alert-triangle', 'badge' => $badgeAlertas],
        ['ruta' => 'tutor.mensajes', 'label' => 'Buzón', 'icon' => 'lucide-mail', 'badge' => $badgeMensajes],
        ['ruta' => 'tutor.reportes', 'label' => 'Reportes', 'icon' => 'lucide-file-text', 'badge' => 0],
    ];

    $links = match($rol) {
        'alumno' => $linksAlumno,
        'tutor' => $linksTutor,
        default => [],
    };
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-blue-100 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]"
     x-data="{ ayudaMovilAbierta: false }">
    <div class="grid grid-cols-6 max-w-lg mx-auto">
        @foreach($links as $link)
            @php $activo = request()->routeIs($link['ruta']); @endphp
            <a href="{{ route($link['ruta']) }}"
               wire:navigate
               class="flex flex-col items-center justify-center gap-1 py-2.5 transition-colors relative {{ $activo ? 'text-blue-700' : 'text-blue-400 hover:text-blue-600' }}"
               :aria-label="'Ir a {{ $link['label'] }}'">
                @if($activo)
                    <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-700 rounded-b-full"></span>
                @endif
                <div class="relative">
                    @svg($link['icon'], 'w-5 h-5')
                    @if(($link['badge'] ?? 0) > 0)
                        <span class="absolute -top-1.5 -right-2 min-w-[16px] h-[16px] px-1 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                            {{ $link['badge'] > 9 ? '9+' : $link['badge'] }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-medium">{{ $link['label'] }}</span>
            </a>
        @endforeach

        {{-- Botón de Ayuda (Tema 2 — acceso desde nav móvil) --}}
        <button @click="ayudaMovilAbierta = true"
                class="flex flex-col items-center justify-center gap-1 py-2.5 transition-colors text-blue-400 hover:text-blue-600"
                aria-label="Abrir guía de ayuda">
            @svg('lucide-circle-help', 'w-5 h-5')
            <span class="text-[10px] font-medium">Ayuda</span>
        </button>
    </div>

    {{-- Mini-modal de ayuda rápida para móvil (redirige al modal del header) --}}
    <div x-show="ayudaMovilAbierta"
         x-cloak
         @keydown.escape.window="ayudaMovilAbierta = false"
         class="fixed inset-0 z-50 flex items-end justify-center bg-blue-950/60 backdrop-blur-sm"
         @click.self="ayudaMovilAbierta = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white w-full rounded-t-3xl shadow-2xl max-h-[80vh] flex flex-col overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">

            <div class="flex justify-center pt-3 pb-1 flex-shrink-0">
                <div class="w-12 h-1 bg-blue-200 rounded-full"></div>
            </div>

            <div class="flex items-center justify-between px-5 py-3 border-b border-blue-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    @svg('lucide-circle-help', 'w-5 h-5 text-blue-700')
                    <p class="text-base font-bold text-blue-900">Ayuda del sistema</p>
                </div>
                <button @click="ayudaMovilAbierta = false"
                        class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center"
                        aria-label="Cerrar ayuda">
                    @svg('lucide-x', 'w-4 h-4 text-blue-600')
                </button>
            </div>

            <div class="overflow-y-auto flex-1 p-4 space-y-3 pb-safe">
                @php
                    $rolMovil = auth()->user()->rol;
                    $seccionesMovil = $rolMovil === 'tutor'
                        ? [
                            ['icon' => 'lucide-home',           'titulo' => 'Inicio',           'desc' => 'Resumen de alumnos y alertas del grupo.'],
                            ['icon' => 'lucide-users',          'titulo' => 'Alumnos',           'desc' => 'Lista con promedio e indicador de riesgo (▲◆●). "Ver perfil" muestra el historial completo.'],
                            ['icon' => 'lucide-alert-triangle', 'titulo' => 'Alertas',           'desc' => 'Gestiona alertas críticas, medias y bajas. Configura umbrales en la sección inferior.'],
                            ['icon' => 'lucide-mail',           'titulo' => 'Mensajes',          'desc' => 'Comunícate con tus alumnos asignados.'],
                            ['icon' => 'lucide-file-text',      'titulo' => 'Reportes',          'desc' => 'Genera reportes individuales, grupales y comparativos.'],
                          ]
                        : [
                            ['icon' => 'lucide-home',        'titulo' => 'Inicio',           'desc' => 'Tu resumen académico: promedio, materias en curso y alertas.'],
                            ['icon' => 'lucide-layout-grid', 'titulo' => 'Malla',            'desc' => 'Visualiza tu avance por semestre. ✓ Aprobada, ▶ En curso, ○ Disponible, ✗ Reprobada, 🔒 Bloqueada.'],
                            ['icon' => 'lucide-book-open',   'titulo' => 'Materias',         'desc' => 'Inscríbete en materias. Las "Sugeridas" corresponden a tu semestre y prerrequisitos.'],
                            ['icon' => 'lucide-award',       'titulo' => 'Calificaciones',   'desc' => 'Captura tus parciales. Necesitas materias inscritas. El promedio se calcula automáticamente.'],
                            ['icon' => 'lucide-mail',        'titulo' => 'Mensajes',         'desc' => 'Comunícate con tu tutor asignado.'],
                          ];
                @endphp

                @foreach($seccionesMovil as $sec)
                    <div class="flex items-start gap-3 p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            @svg($sec['icon'], 'w-4 h-4 text-blue-700')
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">{{ $sec['titulo'] }}</p>
                            <p class="text-xs text-blue-600 mt-0.5 leading-relaxed">{{ $sec['desc'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
                    @svg('lucide-lightbulb', 'w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5')
                    <p class="text-xs text-amber-800">
                        @if($rolMovil === 'tutor')
                            Revisa las alertas críticas diariamente. Configura los umbrales según tu grupo.
                        @else
                            Inscribe tus materias al inicio del semestre. Captura calificaciones tan pronto las recibas.
                        @endif
                    </p>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-blue-50 bg-blue-50/50 flex-shrink-0">
                <button @click="ayudaMovilAbierta = false"
                        class="w-full py-2.5 bg-blue-700 text-white text-sm font-semibold rounded-xl">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</nav>