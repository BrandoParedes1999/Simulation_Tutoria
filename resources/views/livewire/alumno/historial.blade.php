<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

    {{-- HEADER --}}
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            @svg('lucide-clock', 'w-5 h-5 text-blue-700')
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Historial Académico</h1>
            <p class="text-xs text-blue-400">Registro completo de todas tus materias cursadas</p>
        </div>
    </div>

    {{-- RESUMEN GLOBAL --}}
    <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-5 text-white shadow-lg shadow-blue-900/20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="relative">
            <p class="text-xs text-blue-200 uppercase tracking-wide mb-1">Promedio general</p>
            <div class="flex items-baseline gap-2">
                <p class="text-4xl font-bold">
                    {{ $resumen['promedio_general'] > 0 ? number_format($resumen['promedio_general'], 1) : '—' }}
                </p>
                @if($resumen['promedio_general'] > 0)
                    <span class="text-sm text-blue-200">/ 100</span>
                @endif
            </div>
            <div class="grid grid-cols-4 gap-2 mt-4 pt-4 border-t border-white/20">
                <div>
                    <p class="text-xs text-blue-200">Total</p>
                    <p class="text-lg font-bold">{{ $resumen['total_materias'] }}</p>
                </div>
                <div class="border-l border-white/20 pl-2">
                    <p class="text-xs text-blue-200">Aprobadas</p>
                    <p class="text-lg font-bold text-emerald-200">{{ $resumen['aprobadas'] }}</p>
                </div>
                <div class="border-l border-white/20 pl-2">
                    <p class="text-xs text-blue-200">Reprobadas</p>
                    <p class="text-lg font-bold {{ $resumen['reprobadas'] > 0 ? 'text-red-200' : 'text-white/50' }}">
                        {{ $resumen['reprobadas'] }}
                    </p>
                </div>
                <div class="border-l border-white/20 pl-2">
                    <p class="text-xs text-blue-200">Créditos</p>
                    <p class="text-lg font-bold">{{ $resumen['creditos_aprobados'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRO DE ESTATUS --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-3">
        <div class="flex gap-2 flex-wrap">
            @foreach(['todos' => 'Todos', 'aprobada' => 'Aprobadas', 'reprobada' => 'Reprobadas', 'dada_de_baja' => 'Bajas'] as $valor => $etiqueta)
                <button
                    wire:click="$set('filtroEstatus', '{{ $valor }}')"
                    class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                        {{ $filtroEstatus === $valor ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                    {{ $etiqueta }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- LISTADO POR PERIODO --}}
    @forelse($historial as $periodoClave => $inscripciones)
        @php
            $aprobadas   = $inscripciones->where('estatus', 'aprobada')->count();
            $total       = $inscripciones->count();
            $promedioP   = $inscripciones->whereIn('estatus', ['aprobada', 'reprobada'])->avg('promedio');
            $creditosSem = $inscripciones->where('estatus', 'aprobada')
                ->sum(fn($i) => $i->materiaMalla->creditos ?? 0);
        @endphp
        <div class="bg-white border border-blue-100 rounded-2xl shadow-sm overflow-hidden">
            {{-- Cabecera del periodo --}}
            <div class="bg-blue-50/60 border-b border-blue-100 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @svg('lucide-calendar', 'w-4 h-4 text-blue-500')
                    <div>
                        <p class="text-sm font-bold text-blue-900">{{ $periodoClave }}</p>
                        <p class="text-xs text-blue-400">
                            {{ $total }} {{ $total === 1 ? 'materia' : 'materias' }}
                            · {{ $creditosSem }} créditos aprobados
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($promedioP)
                        <span class="text-sm font-bold {{ $promedioP >= 70 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($promedioP, 1) }}
                        </span>
                    @endif
                    <span class="text-xs text-blue-500">{{ $aprobadas }}/{{ $total }} aprobadas</span>
                </div>
            </div>

            {{-- Materias del periodo --}}
            <div class="divide-y divide-blue-50">
                @foreach($inscripciones->sortBy('materiaMalla.semestre') as $insc)
                    @php
                        $estilo = match($insc->estatus) {
                            'aprobada'     => ['icon' => 'lucide-check-circle-2', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'label' => 'Aprobada'],
                            'reprobada'    => ['icon' => 'lucide-x-circle',       'color' => 'text-red-600',     'bg' => 'bg-red-50',     'label' => 'Reprobada'],
                            'dada_de_baja' => ['icon' => 'lucide-minus-circle',   'color' => 'text-gray-500',   'bg' => 'bg-gray-50',    'label' => 'Baja'],
                            default        => ['icon' => 'lucide-circle',          'color' => 'text-blue-400',   'bg' => 'bg-blue-50',    'label' => $insc->estatus],
                        };
                    @endphp
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="{{ $estilo['bg'] }} p-1.5 rounded-lg flex-shrink-0">
                            @svg($estilo['icon'], 'w-4 h-4 ' . $estilo['color'])
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-[10px] font-mono text-blue-500">{{ $insc->materiaMalla->clave ?? '—' }}</p>
                                <span class="text-[10px] text-blue-400">Sem {{ $insc->materiaMalla->semestre ?? '?' }}</span>
                                <span class="text-[10px] text-blue-400">· {{ $insc->materiaMalla->creditos ?? 0 }} cr.</span>
                            </div>
                            <p class="text-sm font-medium text-blue-900 truncate">
                                {{ $insc->materiaMalla->nombre ?? 'Materia eliminada' }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($insc->promedio !== null)
                                <p class="text-lg font-bold {{ $insc->promedio >= 70 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ number_format($insc->promedio, 1) }}
                                </p>
                            @else
                                <p class="text-sm text-blue-300">—</p>
                            @endif
                            <p class="text-[10px] {{ $estilo['color'] }} font-medium">{{ $estilo['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-blue-50/50 rounded-2xl p-8 text-center">
            @svg('lucide-inbox', 'w-12 h-12 text-blue-300 mx-auto mb-2')
            <p class="text-sm font-medium text-blue-700">Sin historial aún</p>
            <p class="text-xs text-blue-500 mt-1">Las materias cursadas aparecerán aquí al finalizar el periodo.</p>
        </div>
    @endforelse

</div>