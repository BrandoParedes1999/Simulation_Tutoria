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

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-blue-100 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
    <div class="grid grid-cols-5 max-w-lg mx-auto">
        @foreach($links as $link)
            @php $activo = request()->routeIs($link['ruta']); @endphp
            <a href="{{ route($link['ruta']) }}"
               wire:navigate
               class="flex flex-col items-center justify-center gap-1 py-2.5 transition-colors relative {{ $activo ? 'text-blue-700' : 'text-blue-400 hover:text-blue-600' }}">
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
    </div>
</nav>