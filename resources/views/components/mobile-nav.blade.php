@php
    $user = auth()->user();
    $rol = $user->rol;

    $linksAlumno = [
        ['ruta' => 'alumno.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home'],
        ['ruta' => 'alumno.malla', 'label' => 'Malla', 'icon' => 'lucide-layout-grid'],
        ['ruta' => 'alumno.materias', 'label' => 'Materias', 'icon' => 'lucide-book-open'],
        ['ruta' => 'alumno.calificaciones', 'label' => 'Calif.', 'icon' => 'lucide-award'],
        ['ruta' => 'alumno.mensajes', 'label' => 'Buzón', 'icon' => 'lucide-mail'],
    ];

    $linksTutor = [
        ['ruta' => 'tutor.dashboard', 'label' => 'Inicio', 'icon' => 'lucide-home'],
        ['ruta' => 'tutor.alumnos', 'label' => 'Alumnos', 'icon' => 'lucide-users'],
        ['ruta' => 'tutor.alertas', 'label' => 'Alertas', 'icon' => 'lucide-alert-triangle'],
        ['ruta' => 'tutor.mensajes', 'label' => 'Buzón', 'icon' => 'lucide-mail'],
        ['ruta' => 'tutor.reportes', 'label' => 'Reportes', 'icon' => 'lucide-file-text'],
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
            
                href="{{ route($link['ruta']) }}"
                class="flex flex-col items-center justify-center gap-1 py-2.5 transition-colors relative
                    {{ $activo ? 'text-blue-700' : 'text-blue-400 hover:text-blue-600' }}"
            >
                @if($activo)
                    <span class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-700 rounded-b-full"></span>
                @endif
                @svg($link['icon'], 'w-5 h-5')
                <span class="text-[10px] font-medium">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>