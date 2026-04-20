<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

    {{-- ══════════════════════════════════════════════ --}}
    {{-- HEADER con título y progreso general          --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                @svg('lucide-layout-grid', 'w-5 h-5 text-blue-700')
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mi Malla Curricular</h1>
                <p class="text-xs text-blue-400">Ingeniería en Sistemas Computacionales · Plan 2010</p>
            </div>
        </div>

        {{-- Barra de progreso general --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-blue-900">Avance del programa</p>
                <span class="text-sm font-bold text-blue-700">
                    {{ $this->estadisticas['porcentaje_avance'] }}%
                </span>
            </div>
            <div class="w-full bg-blue-50 rounded-full h-2.5 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 h-full rounded-full transition-all duration-500"
                     style="width: {{ $this->estadisticas['porcentaje_avance'] }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-blue-50">
                <div class="text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $this->estadisticas['creditos_aprobados'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">Aprobados</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-600">{{ $this->estadisticas['creditos_en_curso'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">En curso</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-300">{{ $this->estadisticas['creditos_restantes'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">Restantes</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- LEYENDA DE ESTADOS                              --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-blue-100 p-3 shadow-sm">
        <p class="text-xs font-medium text-blue-400 uppercase tracking-wide mb-2">Estados</p>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                Aprobada
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                En curso
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                Disponible
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                Reprobada
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                Bloqueada
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- SELECTOR DE SEMESTRE (scroll horizontal)       --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <div class="flex gap-1 p-2 min-w-max">
                @foreach($this->malla as $semestre => $materias)
                    @php
                        $esActivo = $semestreSeleccionado === $semestre;
                        $aprobadas = collect($materias)->where('estado', 'aprobada')->count();
                        $total = count($materias);
                        $nivel = $materias[0]['nivel'] ?? 'basico';
                        $colorNivel = match($nivel) {
                            'basico' => 'amber',
                            'profesionalizante' => 'emerald',
                            'terminal' => 'blue',
                            default => 'gray'
                        };
                    @endphp
                    <button
                        wire:click="seleccionarSemestre({{ $semestre }})"
                        class="flex-shrink-0 flex flex-col items-center justify-center px-4 py-2.5 rounded-xl transition-all
                            {{ $esActivo
                                ? 'bg-blue-700 text-white shadow-md'
                                : 'text-blue-600 hover:bg-blue-50' }}">
                        <span class="text-[10px] font-medium uppercase tracking-wider {{ $esActivo ? 'text-blue-200' : 'text-blue-400' }}">
                            Semestre
                        </span>
                        <span class="text-lg font-bold leading-none mt-0.5">{{ $semestre }}</span>
                        <span class="text-[10px] mt-1 {{ $esActivo ? 'text-blue-200' : 'text-blue-400' }}">
                            {{ $aprobadas }}/{{ $total }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- MATERIAS DEL SEMESTRE SELECCIONADO              --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if($semestreSeleccionado && isset($this->malla[$semestreSeleccionado]))
        @php
            $materiasDelSemestre = $this->malla[$semestreSeleccionado];
            $nivelSemestre = $materiasDelSemestre[0]['nivel'] ?? 'basico';
            $labelNivel = match($nivelSemestre) {
                'basico' => 'Básico',
                'profesionalizante' => 'Profesionalizante',
                'terminal' => 'Terminal',
                default => 'N/A'
            };
            $colorHeader = match($nivelSemestre) {
                'basico' => 'from-amber-400 to-amber-600',
                'profesionalizante' => 'from-emerald-500 to-emerald-700',
                'terminal' => 'from-blue-500 to-blue-700',
                default => 'from-gray-400 to-gray-600'
            };
        @endphp

        <div class="space-y-3">
            {{-- Header del semestre --}}
            <div class="bg-gradient-to-r {{ $colorHeader }} rounded-2xl p-4 text-white shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-white/80 uppercase tracking-wide">Nivel {{ $labelNivel }}</p>
                        <h2 class="text-xl font-bold">Semestre {{ $semestreSeleccionado }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold">{{ collect($materiasDelSemestre)->sum('creditos') }}</p>
                        <p class="text-xs text-white/80 uppercase tracking-wide">Créditos</p>
                    </div>
                </div>
            </div>

            {{-- Lista de materias --}}
            <div class="space-y-2">
                @foreach($materiasDelSemestre as $materia)
                    @php
                        $estiloMateria = match($materia['estado']) {
                            'aprobada' => [
                                'border' => 'border-emerald-200',
                                'bg' => 'bg-emerald-50',
                                'icon_bg' => 'bg-emerald-500',
                                'icon' => 'lucide-check',
                                'text' => 'text-emerald-900',
                                'badge_bg' => 'bg-emerald-100',
                                'badge_text' => 'text-emerald-700',
                                'label' => 'Aprobada',
                            ],
                            'en_curso' => [
                                'border' => 'border-blue-200',
                                'bg' => 'bg-blue-50',
                                'icon_bg' => 'bg-blue-500',
                                'icon' => 'lucide-play',
                                'text' => 'text-blue-900',
                                'badge_bg' => 'bg-blue-100',
                                'badge_text' => 'text-blue-700',
                                'label' => 'En curso',
                            ],
                            'disponible' => [
                                'border' => 'border-amber-200',
                                'bg' => 'bg-amber-50',
                                'icon_bg' => 'bg-amber-500',
                                'icon' => 'lucide-unlock',
                                'text' => 'text-amber-900',
                                'badge_bg' => 'bg-amber-100',
                                'badge_text' => 'text-amber-700',
                                'label' => 'Disponible',
                            ],
                            'reprobada' => [
                                'border' => 'border-red-200',
                                'bg' => 'bg-red-50',
                                'icon_bg' => 'bg-red-500',
                                'icon' => 'lucide-x',
                                'text' => 'text-red-900',
                                'badge_bg' => 'bg-red-100',
                                'badge_text' => 'text-red-700',
                                'label' => 'Reprobada',
                            ],
                            default => [
                                'border' => 'border-gray-200',
                                'bg' => 'bg-gray-50',
                                'icon_bg' => 'bg-gray-400',
                                'icon' => 'lucide-lock',
                                'text' => 'text-gray-700',
                                'badge_bg' => 'bg-gray-200',
                                'badge_text' => 'text-gray-600',
                                'label' => 'Bloqueada',
                            ],
                        };
                    @endphp

                    <button
                        wire:click='verDetalleMateria({{ json_encode($materia) }})'
                        class="w-full text-left {{ $estiloMateria['bg'] }} border {{ $estiloMateria['border'] }} rounded-2xl p-3 hover:shadow-md transition-all active:scale-[0.99]">
                        <div class="flex items-start gap-3">
                            {{-- Icono de estado --}}
                            <div class="w-9 h-9 {{ $estiloMateria['icon_bg'] }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                @svg($estiloMateria['icon'], 'w-4 h-4 text-white')
                            </div>

                            {{-- Info de la materia --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-mono {{ $estiloMateria['badge_text'] }} mb-0.5">
                                            {{ $materia['clave'] }}
                                        </p>
                                        <p class="text-sm font-semibold {{ $estiloMateria['text'] }} leading-tight">
                                            {{ $materia['nombre'] }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-0.5 {{ $estiloMateria['badge_bg'] }} {{ $estiloMateria['badge_text'] }} text-[10px] font-medium rounded-full flex-shrink-0">
                                        {{ $estiloMateria['label'] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 mt-2 text-xs {{ $estiloMateria['text'] }}/70">
                                    <span class="flex items-center gap-1">
                                        @svg('lucide-award', 'w-3 h-3')
                                        {{ $materia['creditos'] }} cr.
                                    </span>
                                    <span class="flex items-center gap-1">
                                        @svg('lucide-clock', 'w-3 h-3')
                                        {{ $materia['total_horas'] }}h
                                    </span>
                                    @if($materia['calificacion'])
                                        <span class="flex items-center gap-1 font-bold">
                                            @svg('lucide-star', 'w-3 h-3')
                                            {{ number_format($materia['calificacion'], 1) }}
                                        </span>
                                    @endif
                                    @if(count($materia['prerrequisitos']) > 0)
                                        <span class="flex items-center gap-1">
                                            @svg('lucide-link', 'w-3 h-3')
                                            {{ count($materia['prerrequisitos']) }} prereq.
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- MODAL DE DETALLE DE MATERIA                     --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if($materiaSeleccionada)
        <div
            wire:click="cerrarDetalle"
            class="fixed inset-0 z-50 bg-blue-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data
            x-init="document.body.style.overflow = 'hidden'"
            x-on:click.outside="$wire.cerrarDetalle()">

            <div
                wire:click.stop
                class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl max-h-[85vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100">

                {{-- Handle para deslizar (solo móvil) --}}
                <div class="sm:hidden flex justify-center pt-3 pb-1">
                    <div class="w-12 h-1 bg-blue-200 rounded-full"></div>
                </div>

                {{-- Header del modal --}}
                <div class="px-5 pt-4 pb-3 sticky top-0 bg-white border-b border-blue-100 z-10">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-mono text-blue-400">{{ $materiaSeleccionada['clave'] }}</p>
                            <h3 class="text-base font-bold text-blue-900 leading-tight mt-0.5">
                                {{ $materiaSeleccionada['nombre'] }}
                            </h3>
                        </div>
                        <button
                            wire:click="cerrarDetalle"
                            class="w-8 h-8 bg-blue-50 hover:bg-blue-100 rounded-full flex items-center justify-center transition-colors flex-shrink-0">
                            @svg('lucide-x', 'w-4 h-4 text-blue-600')
                        </button>
                    </div>
                </div>

                {{-- Contenido --}}
                <div class="p-5 space-y-4">
                    {{-- KPIs --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            <p class="text-lg font-bold text-blue-900">{{ $materiaSeleccionada['creditos'] }}</p>
                            <p class="text-[10px] text-blue-500 uppercase">Créditos</p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            <p class="text-lg font-bold text-blue-900">{{ $materiaSeleccionada['total_horas'] }}</p>
                            <p class="text-[10px] text-blue-500 uppercase">Horas</p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            @if($materiaSeleccionada['calificacion'])
                                <p class="text-lg font-bold text-blue-900">{{ number_format($materiaSeleccionada['calificacion'], 1) }}</p>
                                <p class="text-[10px] text-blue-500 uppercase">Promedio</p>
                            @else
                                <p class="text-lg font-bold text-blue-300">—</p>
                                <p class="text-[10px] text-blue-500 uppercase">Sin calif.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Información general --}}
                    <div class="bg-blue-50/50 rounded-xl p-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-blue-500">Tipo</span>
                            <span class="font-medium text-blue-900 capitalize">{{ $materiaSeleccionada['tipo'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-blue-500">Nivel</span>
                            <span class="font-medium text-blue-900 capitalize">{{ str_replace('_', ' ', $materiaSeleccionada['nivel']) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-blue-500">Área</span>
                            <span class="font-medium text-blue-900 text-right capitalize">
                                {{ str_replace('_', ' ', $materiaSeleccionada['area']) }}
                            </span>
                        </div>
                    </div>

                    {{-- Prerrequisitos --}}
                    @if(count($materiaSeleccionada['prerrequisitos']) > 0)
                        <div>
                            <p class="text-xs font-medium text-blue-400 uppercase tracking-wide mb-2">
                                Prerrequisitos
                            </p>
                            <div class="space-y-2">
                                @foreach($materiaSeleccionada['prerrequisitos'] as $prereq)
                                    <div class="flex items-center gap-2 p-2.5 rounded-xl border {{ $prereq['cumplido'] ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ $prereq['cumplido'] ? 'bg-emerald-500' : 'bg-gray-400' }}">
                                            @if($prereq['cumplido'])
                                                @svg('lucide-check', 'w-3.5 h-3.5 text-white')
                                            @else
                                                @svg('lucide-x', 'w-3.5 h-3.5 text-white')
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-mono {{ $prereq['cumplido'] ? 'text-emerald-600' : 'text-gray-500' }}">
                                                {{ $prereq['clave'] }}
                                            </p>
                                            <p class="text-sm font-medium {{ $prereq['cumplido'] ? 'text-emerald-900' : 'text-gray-700' }} leading-tight">
                                                {{ $prereq['nombre'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-start gap-2">
                            @svg('lucide-info', 'w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5')
                            <p class="text-xs text-emerald-700">
                                Esta materia no requiere prerrequisitos.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>