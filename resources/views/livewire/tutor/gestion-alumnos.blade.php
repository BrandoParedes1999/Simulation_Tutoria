<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Alumnos</h1>
            <p class="text-sm text-blue-400 mt-0.5">Grupo de tutoría asignado</p>
        </div>
        <button
            wire:click="$toggle('mostrarAsignar')"
            class="flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            @if($mostrarAsignar)
                @svg('lucide-x', 'w-4 h-4')
                Cerrar
            @else
                @svg('lucide-user-plus', 'w-4 h-4')
                Agregar alumno
            @endif
        </button>
    </div>

    {{-- Panel: buscar y agregar alumnos sin tutor --}}
    @if($mostrarAsignar)
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 space-y-3">
            <div class="flex items-center gap-2">
                @svg('lucide-user-plus', 'w-5 h-5 text-blue-700')
                <h2 class="text-sm font-bold text-blue-900">Asignar alumno a tu grupo</h2>
            </div>
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    @svg('lucide-search', 'w-4 h-4 text-blue-300')
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="busquedaSinAsignar"
                    placeholder="Buscar por nombre o matrícula..."
                    class="w-full pl-9 pr-4 py-2.5 border border-blue-200 rounded-xl text-sm text-blue-900 bg-white
                           focus:outline-none focus:ring-2 focus:ring-blue-300 placeholder-blue-300">
            </div>

            @if($busquedaSinAsignar !== '')
                <div wire:loading wire:target="busquedaSinAsignar" class="text-xs text-blue-500 flex items-center gap-1">
                    @svg('lucide-loader-2', 'w-3 h-3 animate-spin')
                    Buscando...
                </div>
                <div wire:loading.remove wire:target="busquedaSinAsignar">
                    @if($sinAsignar->isEmpty())
                        <p class="text-sm text-blue-500 text-center py-4">No se encontraron alumnos sin tutor con ese criterio.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($sinAsignar as $a)
                                <div class="bg-white border border-blue-100 rounded-xl p-3 flex items-center gap-3">
                                    <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-blue-700 font-bold text-xs">{{ strtoupper(substr($a->usuario->name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-blue-900">{{ $a->usuario->name ?? 'Sin nombre' }}</p>
                                        <p class="text-xs text-blue-500">{{ $a->matricula }} · {{ $a->carrera->nombre ?? $a->carrera->clave ?? '—' }} · Sem {{ $a->semestre_actual }}</p>
                                    </div>
                                    <button
                                        wire:click="asignar({{ $a->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="asignar({{ $a->id }})"
                                        class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="asignar({{ $a->id }})">@svg('lucide-plus', 'w-3 h-3') Asignar</span>
                                        <span wire:loading wire:target="asignar({{ $a->id }})">@svg('lucide-loader-2', 'w-3 h-3 animate-spin') ...</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-xs text-blue-400 text-center">Escribe al menos un carácter para buscar alumnos sin tutor.</p>
            @endif
        </div>
    @endif

    {{-- Buscador de alumnos asignados --}}
    <div class="relative">
        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            @svg('lucide-search', 'w-4 h-4 text-blue-300')
        </div>
        <input
            type="text"
            wire:model.live.debounce.300ms="busqueda"
            placeholder="Filtrar alumnos asignados..."
            class="w-full pl-9 pr-4 py-3 border border-blue-100 rounded-2xl text-sm text-blue-900 bg-white
                   focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-blue-200">
    </div>

    {{-- Tabla de alumnos asignados --}}
    <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
        <table class="w-full">
            <thead>
                <tr class="bg-blue-50/60 border-b border-blue-100">
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Alumno</th>
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Matrícula</th>
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Semestre</th>
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Promedio</th>
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Alertas</th>
                    <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody wire:loading.class="opacity-50">
                @forelse($asignados as $a)
                    @php
                        $alertas = $alertasPorAlumno[$a->id] ?? 0;
                        $promedio = (float) $a->promedio_general;
                    @endphp
                    <tr class="border-b border-blue-50 hover:bg-blue-50/40 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($a->usuario->name ?? '?', 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-blue-900">{{ $a->usuario->name ?? '—' }}</p>
                                    <p class="text-xs text-blue-400">{{ $a->carrera->nombre ?? $a->carrera->clave ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-blue-600 font-medium">{{ $a->matricula }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-600">{{ $a->semestre_actual }}°</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-bold
                                @if($promedio >= 90) text-emerald-600
                                @elseif($promedio >= 80) text-blue-600
                                @elseif($promedio >= 70) text-amber-500
                                @elseif($promedio > 0) text-red-500
                                @else text-slate-300 @endif">
                                {{ $promedio > 0 ? number_format($promedio, 1) . ' pts' : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($alertas > 0)
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-red-500 text-white text-xs font-bold rounded-full">{{ $alertas }}</span>
                            @else
                                <span class="text-slate-300 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('tutor.alumno-detalle', $a->id) }}"
                                   class="px-3 py-1.5 border border-blue-200 rounded-lg text-blue-600 text-xs font-medium hover:bg-blue-50 transition">
                                    Ver perfil
                                </a>
                                <button
                                    wire:click="desasignar({{ $a->id }})"
                                    wire:confirm="¿Desasignar a {{ $a->usuario?->name }} de tu grupo?"
                                    class="px-3 py-1.5 border border-red-200 rounded-lg text-red-500 text-xs font-medium hover:bg-red-50 transition">
                                    Quitar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-sm text-slate-400">
                            @if($busqueda)
                                No se encontraron alumnos con ese criterio.
                            @else
                                No tienes alumnos asignados aún. Usa "Agregar alumno" para asignar.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-blue-50">
            <span class="text-xs text-slate-400">
                {{ $asignados->count() }} {{ $asignados->count() === 1 ? 'alumno asignado' : 'alumnos asignados' }}
            </span>
        </div>
    </div>

</div>
