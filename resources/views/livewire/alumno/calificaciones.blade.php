<div
    x-data="{
        expandir(id) {
            if ($wire.abierta === id) {
                $wire.cerrar();
            } else {
                $wire.abrir(id);
            }
        },
        calcPromedio(p1, p2, p3) {
            const vals = [p1, p2, p3].filter(v => v !== null && v !== '' && !isNaN(v)).map(Number);
            if (vals.length === 0) return null;
            return (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(2);
        },
        estadoPreview(p1, p2, p3) {
            const vals = [p1, p2, p3].filter(v => v !== null && v !== '' && !isNaN(v));
            if (vals.length < 3) return 'en_curso';
            const promedio = vals.map(Number).reduce((a, b) => a + b, 0) / 3;
            return promedio >= 70 ? 'aprobada' : 'reprobada';
        },
        validoNum(v) {
            if (v === '' || v === null) return true;
            const n = Number(v);
            return !isNaN(n) && n >= 0 && n <= 100;
        }
    }"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
>

    {{-- HEADER --}}
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                @svg('lucide-award', 'w-5 h-5 text-blue-700')
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mis Calificaciones</h1>
                <p class="text-xs text-blue-400">Captura tus calificaciones del periodo actual</p>
            </div>
        </div>

        {{-- KPIs del periodo --}}
        <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-5 text-white shadow-lg shadow-blue-900/20 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <p class="text-xs text-blue-200 uppercase tracking-wide mb-1">Promedio del periodo</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-4xl font-bold">
                        {{ $resumen['promedio_periodo'] > 0 ? number_format($resumen['promedio_periodo'], 1) : '—' }}
                    </p>
                    @if($resumen['promedio_periodo'] > 0)
                        <span class="text-sm text-blue-200">/ 100</span>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-white/20">
                    <div>
                        <p class="text-xs text-blue-200">Calificadas</p>
                        <p class="text-lg font-bold">{{ $resumen['calificadas'] }}/{{ $resumen['total_materias'] }}</p>
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
                </div>
            </div>
        </div>
    </div>

    {{-- LISTADO DE MATERIAS --}}
    @if($materias->count() > 0)
        <div class="space-y-3">
            @foreach($materias as $materia)
                @php
                    $estilo = match($materia['estatus']) {
                        'aprobada' => [
                            'border' => 'border-emerald-200',
                            'icon_bg' => 'bg-emerald-500',
                            'icon' => 'lucide-check',
                            'badge_bg' => 'bg-emerald-100',
                            'badge_text' => 'text-emerald-700',
                            'label' => 'Aprobada',
                        ],
                        'reprobada' => [
                            'border' => 'border-red-200',
                            'icon_bg' => 'bg-red-500',
                            'icon' => 'lucide-x',
                            'badge_bg' => 'bg-red-100',
                            'badge_text' => 'text-red-700',
                            'label' => 'Reprobada',
                        ],
                        default => [
                            'border' => 'border-blue-100',
                            'icon_bg' => $materia['parciales_capturados'] > 0 ? 'bg-blue-500' : 'bg-gray-400',
                            'icon' => $materia['parciales_capturados'] > 0 ? 'lucide-pen-line' : 'lucide-clock',
                            'badge_bg' => $materia['parciales_capturados'] > 0 ? 'bg-blue-100' : 'bg-gray-100',
                            'badge_text' => $materia['parciales_capturados'] > 0 ? 'text-blue-700' : 'text-gray-600',
                            'label' => $materia['parciales_capturados'] > 0
                                ? "Capturando ({$materia['parciales_capturados']}/3)"
                                : 'Pendiente',
                        ],
                    };
                @endphp

                <div class="bg-white border rounded-2xl {{ $estilo['border'] }} overflow-hidden transition-all">
                    {{-- Cabecera --}}
                    <button
                        @click="expandir({{ $materia['id'] }})"
                        class="w-full p-3 text-left hover:bg-blue-50/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 {{ $estilo['icon_bg'] }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                @svg($estilo['icon'], 'w-4 h-4 text-white')
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-[10px] font-mono text-blue-500">{{ $materia['clave'] }}</p>
                                            <span class="text-[10px] text-blue-400">Sem {{ $materia['semestre'] }}</span>
                                            <span class="text-[10px] text-blue-400">{{ $materia['creditos'] }} cr.</span>
                                        </div>
                                        <p class="text-sm font-semibold text-blue-900 leading-tight mt-0.5">
                                            {{ $materia['nombre'] }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-0.5 {{ $estilo['badge_bg'] }} {{ $estilo['badge_text'] }} text-[10px] font-semibold rounded-full flex-shrink-0 whitespace-nowrap">
                                        {{ $estilo['label'] }}
                                    </span>
                                </div>

                                <div x-show="$wire.abierta !== {{ $materia['id'] }}" class="flex items-center gap-4 mt-2 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-blue-400">P1:</span>
                                        <span class="font-semibold {{ $materia['parcial1'] !== null ? 'text-blue-900' : 'text-blue-300' }}">
                                            {{ $materia['parcial1'] !== null ? number_format($materia['parcial1'], 1) : '—' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-blue-400">P2:</span>
                                        <span class="font-semibold {{ $materia['parcial2'] !== null ? 'text-blue-900' : 'text-blue-300' }}">
                                            {{ $materia['parcial2'] !== null ? number_format($materia['parcial2'], 1) : '—' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-blue-400">P3:</span>
                                        <span class="font-semibold {{ $materia['parcial3'] !== null ? 'text-blue-900' : 'text-blue-300' }}">
                                            {{ $materia['parcial3'] !== null ? number_format($materia['parcial3'], 1) : '—' }}
                                        </span>
                                    </div>
                                    @if($materia['promedio'])
                                        <div class="ml-auto flex items-center gap-1 font-bold {{ $materia['promedio'] >= 70 ? 'text-emerald-600' : 'text-red-600' }}">
                                            @svg('lucide-star', 'w-3 h-3')
                                            {{ number_format($materia['promedio'], 1) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex-shrink-0 self-center">
                                <div x-show="$wire.abierta !== {{ $materia['id'] }}">
                                    @svg('lucide-chevron-down', 'w-4 h-4 text-blue-400')
                                </div>
                                <div x-show="$wire.abierta === {{ $materia['id'] }}" x-cloak>
                                    @svg('lucide-chevron-up', 'w-4 h-4 text-blue-600')
                                </div>
                            </div>
                        </div>
                    </button>

                    {{-- Formulario expandido --}}
                    <div
                        x-show="$wire.abierta === {{ $materia['id'] }}"
                        x-cloak
                        x-collapse
                        x-data="{
                            p1: @js($materia['parcial1']),
                            p2: @js($materia['parcial2']),
                            p3: @js($materia['parcial3']),
                        }">
                        <div class="border-t border-blue-100 p-4 bg-blue-50/30 space-y-4">

                            <div class="grid grid-cols-3 gap-3">
                                @foreach([1, 2, 3] as $num)
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-700 mb-1.5">
                                            Parcial {{ $num }}
                                        </label>
                                        <input
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="100"
                                            inputmode="decimal"
                                            x-model.number.lazy="p{{ $num }}"
                                            @input="$wire.edits[{{ $materia['id'] }}] = { parcial1: p1, parcial2: p2, parcial3: p3 }"
                                            :class="!validoNum(p{{ $num }}) ? 'border-red-400 bg-red-50' : 'border-blue-200 bg-white focus:border-blue-400'"
                                            class="w-full px-3 py-2.5 text-center text-lg font-bold rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-blue-900"
                                            placeholder="—">
                                    </div>
                                @endforeach
                            </div>

                            <template x-if="!validoNum(p1) || !validoNum(p2) || !validoNum(p3)">
                                <div class="bg-red-50 border border-red-200 rounded-xl p-2.5 flex items-start gap-2">
                                    @svg('lucide-alert-circle', 'w-4 h-4 text-red-600 flex-shrink-0 mt-0.5')
                                    <p class="text-xs text-red-700">Las calificaciones deben estar entre 0 y 100.</p>
                                </div>
                            </template>

                            <div class="bg-white rounded-xl p-3 border border-blue-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] text-blue-400 uppercase tracking-wide">Promedio actual</p>
                                        <p class="text-2xl font-bold" :class="{
                                            'text-emerald-600': estadoPreview(p1, p2, p3) === 'aprobada',
                                            'text-red-600': estadoPreview(p1, p2, p3) === 'reprobada',
                                            'text-blue-700': estadoPreview(p1, p2, p3) === 'en_curso'
                                        }">
                                            <span x-text="calcPromedio(p1, p2, p3) ?? '—'"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <template x-if="estadoPreview(p1, p2, p3) === 'aprobada'">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                                                @svg('lucide-check-circle-2', 'w-4 h-4')
                                                Aprobada
                                            </div>
                                        </template>
                                        <template x-if="estadoPreview(p1, p2, p3) === 'reprobada'">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                                                @svg('lucide-x-circle', 'w-4 h-4')
                                                Reprobada
                                            </div>
                                        </template>
                                        <template x-if="estadoPreview(p1, p2, p3) === 'en_curso'">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                                @svg('lucide-clock', 'w-4 h-4')
                                                Parciales pendientes
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="estadoPreview(p1, p2, p3) === 'reprobada'">
                                    <p class="text-xs text-red-600 mt-2">
                                        ⚠️ Con este promedio reprobarás la materia. Se notificará a tu tutor.
                                    </p>
                                </template>
                                <template x-if="estadoPreview(p1, p2, p3) === 'aprobada' && calcPromedio(p1, p2, p3) >= 90">
                                    <p class="text-xs text-emerald-600 mt-2">
                                        🎉 ¡Excelente rendimiento!
                                    </p>
                                </template>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    @click="$wire.cerrar()"
                                    class="flex-1 px-4 py-2.5 bg-white border border-blue-200 text-blue-700 text-sm font-semibold rounded-xl hover:bg-blue-50 transition-colors">
                                    Cancelar
                                </button>
                                <button
                                    wire:click="guardar({{ $materia['id'] }})"
                                    :disabled="!validoNum(p1) || !validoNum(p2) || !validoNum(p3)"
                                    class="flex-1 px-4 py-2.5 bg-blue-700 hover:bg-blue-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="guardar" class="flex items-center gap-2">
                                        @svg('lucide-save', 'w-4 h-4')
                                        Guardar
                                    </span>
                                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                                        @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-blue-50/50 rounded-2xl p-8 text-center">
            @svg('lucide-inbox', 'w-12 h-12 text-blue-300 mx-auto mb-2')
            <p class="text-sm font-medium text-blue-700">Sin materias inscritas</p>
            <p class="text-xs text-blue-500 mt-1 mb-4">Inscribe materias para poder capturar calificaciones</p>
            <a href="{{ route('alumno.materias') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white text-sm font-medium rounded-xl hover:bg-blue-800 transition-colors">
                @svg('lucide-arrow-right', 'w-4 h-4')
                Ir a Materias
            </a>
        </div>
    @endif

</div>