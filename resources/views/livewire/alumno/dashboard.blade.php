<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4">

    {{-- ════════════════════════════════════════ --}}
    {{-- SALUDO + BOTONES                         --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-xs text-blue-400">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
            </p>
            <h1 class="text-xl sm:text-2xl font-bold text-blue-900 flex items-center gap-2">
                Hola, {{ explode(' ', $alumno->usuario->name)[0] }} 👋
            </h1>
            <p class="text-xs text-blue-500 mt-0.5">
                Semestre {{ $alumno->semestre_actual }} · {{ $alumno->carrera->nombre }} · {{ $periodo?->clave ?? 'Sin periodo' }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('alumno.mensajes') }}" wire:navigate
               class="relative flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3 py-2 bg-white border border-blue-200 rounded-xl text-blue-700 text-sm font-medium hover:bg-blue-50 transition">
                @svg('lucide-message-circle', 'w-4 h-4')
                <span>Mensajes</span>
                @if($alertasTotal > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                        {{ $alertasTotal }}
                    </span>
                @endif
            </a>
            <a href="{{ route('alumno.malla') }}" wire:navigate
               class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-700 rounded-xl text-white text-sm font-medium hover:bg-blue-800 transition shadow-md shadow-blue-700/20">
                @svg('lucide-layout-grid', 'w-4 h-4')
                <span class="hidden xs:inline sm:inline">Malla Curricular</span>
                <span class="xs:hidden sm:hidden">Malla</span>
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- KPIs (4 tarjetas)                        --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Promedio semestral --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-blue-50">
                @svg('lucide-star', 'w-3.5 h-3.5 text-blue-600')
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2">
                {{ $datosPeriodo['promedio_semestral'] > 0 ? number_format($datosPeriodo['promedio_semestral'], 1) : '—' }}
            </p>
            <p class="text-xs font-medium text-blue-900">Promedio semestral</p>
            <p class="text-[10px] {{ $clasificacionPromedio['color'] }} mt-1">{{ $clasificacionPromedio['texto'] }}</p>
        </div>

        {{-- Materias en curso --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-blue-50">
                @svg('lucide-book-open', 'w-3.5 h-3.5 text-blue-600')
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2">{{ $datosPeriodo['materias_en_curso'] }}</p>
            <p class="text-xs font-medium text-blue-900">Materias en curso</p>
            <p class="text-[10px] text-blue-400 mt-1">{{ $datosPeriodo['creditos_periodo'] }} créditos este semestre</p>
        </div>

        {{-- Créditos aprobados --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-emerald-50">
                @svg('lucide-award', 'w-3.5 h-3.5 text-emerald-600')
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2">{{ $estadisticas['creditos_aprobados'] }}</p>
            <p class="text-xs font-medium text-blue-900">Créditos aprobados</p>
            <p class="text-[10px] text-blue-400 mt-1">{{ $estadisticas['porcentaje_avance'] }}% de la carrera completado</p>
        </div>

        {{-- Semestre actual --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-indigo-50">
                @svg('lucide-trending-up', 'w-3.5 h-3.5 text-indigo-600')
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2">{{ $alumno->semestre_actual }}</p>
            <p class="text-xs font-medium text-blue-900">Semestre actual</p>
            <p class="text-[10px] text-blue-400 mt-1">
                {{ $semestresRestantes === 0 ? 'Último semestre' : ($semestresRestantes . ' semestres restantes') }}
            </p>
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- ELEGIBILIDAD SS/PP                       --}}
    {{-- ════════════════════════════════════════ --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-sm font-bold text-blue-900">Elegibilidad para Servicio y Prácticas</h2>
            <span class="text-[10px] text-blue-400 bg-blue-50 px-2 py-0.5 rounded-full">Calculado automáticamente</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            {{-- Servicio Social --}}
            @php $ss = $elegibilidad['servicio_social']; @endphp
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $ss['elegible'] ? 'bg-emerald-500' : 'bg-gray-100' }}">
                            @svg('lucide-heart-handshake', 'w-4 h-4 ' . ($ss['elegible'] ? 'text-white' : 'text-gray-500'))
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-900">{{ $ss['nombre'] }}</p>
                            <p class="text-[11px] text-blue-400">Requisito para titulación · Mínimo {{ $ss['horas'] }} horas.</p>
                        </div>
                    </div>
                    @if($ss['elegible'])
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full flex-shrink-0">
                            @svg('lucide-check-circle-2', 'w-3 h-3')
                            Disponible
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-50 text-gray-500 text-[10px] font-medium rounded-full flex-shrink-0">
                            @svg('lucide-lock', 'w-3 h-3')
                            No disponible
                        </span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-blue-600">
                            Créditos: <span class="font-semibold text-blue-900">{{ $ss['creditos_aprobados'] }}</span>
                            de <span class="font-semibold text-blue-900">{{ $ss['creditos_requeridos'] }}</span>
                            requeridos ({{ $ss['porcentaje_requerido'] }}%)
                        </span>
                        <span class="font-bold text-blue-700">{{ $ss['progreso_creditos'] }}%</span>
                    </div>
                    <div class="w-full bg-blue-50 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $ss['cumple_creditos'] ? 'bg-emerald-500' : 'bg-blue-500' }}"
                             style="width: {{ $ss['progreso_creditos'] }}%"></div>
                    </div>
                </div>

                @if($ss['cumple_semestre'])
                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-2 flex items-center gap-2">
                        @svg('lucide-check-circle-2', 'w-3.5 h-3.5 text-emerald-600 flex-shrink-0')
                        <p class="text-[11px] text-emerald-700">
                            Semestre mínimo alcanzado ({{ $ss['semestre_requerido'] }}°)
                        </p>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 flex items-center gap-2">
                        @svg('lucide-clock', 'w-3.5 h-3.5 text-gray-500 flex-shrink-0')
                        <p class="text-[11px] text-gray-600">
                            Se requiere el {{ $ss['semestre_requerido'] }}° semestre — te {{ $ss['semestres_faltantes'] === 1 ? 'falta' : 'faltan' }} {{ $ss['semestres_faltantes'] }} {{ $ss['semestres_faltantes'] === 1 ? 'semestre' : 'semestres' }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Prácticas Profesionales --}}
            @php $pp = $elegibilidad['practicas_profesionales']; @endphp
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $pp['elegible'] ? 'bg-emerald-500' : 'bg-gray-100' }}">
                            @svg('lucide-briefcase', 'w-4 h-4 ' . ($pp['elegible'] ? 'text-white' : 'text-gray-500'))
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-900">{{ $pp['nombre'] }}</p>
                            <p class="text-[11px] text-blue-400">Vinculación con empresas · Mínimo {{ $pp['horas'] }} horas.</p>
                        </div>
                    </div>
                    @if($pp['elegible'])
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full flex-shrink-0">
                            @svg('lucide-check-circle-2', 'w-3 h-3')
                            Disponible
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-50 text-gray-500 text-[10px] font-medium rounded-full flex-shrink-0">
                            @svg('lucide-lock', 'w-3 h-3')
                            No disponible
                        </span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-blue-600">
                            Créditos: <span class="font-semibold text-blue-900">{{ $pp['creditos_aprobados'] }}</span>
                            de <span class="font-semibold text-blue-900">{{ $pp['creditos_requeridos'] }}</span>
                            requeridos ({{ $pp['porcentaje_requerido'] }}%)
                        </span>
                        <span class="font-bold text-blue-700">{{ $pp['progreso_creditos'] }}%</span>
                    </div>
                    <div class="w-full bg-blue-50 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $pp['cumple_creditos'] ? 'bg-emerald-500' : 'bg-blue-500' }}"
                             style="width: {{ $pp['progreso_creditos'] }}%"></div>
                    </div>
                </div>

                @if($pp['cumple_semestre'])
                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-2 flex items-center gap-2">
                        @svg('lucide-check-circle-2', 'w-3.5 h-3.5 text-emerald-600 flex-shrink-0')
                        <p class="text-[11px] text-emerald-700">
                            Semestre mínimo alcanzado ({{ $pp['semestre_requerido'] }}°)
                        </p>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 flex items-center gap-2">
                        @svg('lucide-clock', 'w-3.5 h-3.5 text-gray-500 flex-shrink-0')
                        <p class="text-[11px] text-gray-600">
                            Se requiere el {{ $pp['semestre_requerido'] }}° semestre — te {{ $pp['semestres_faltantes'] === 1 ? 'falta' : 'faltan' }} {{ $pp['semestres_faltantes'] }} {{ $pp['semestres_faltantes'] === 1 ? 'semestre' : 'semestres' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- RADAR + LISTA + EVOLUCIÓN                --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        {{-- Radar de desempeño actual --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="mb-2">
                <p class="text-sm font-bold text-blue-900">Desempeño actual por materia</p>
                <p class="text-[11px] text-blue-400">Semestre {{ $periodo?->clave ?? '—' }}</p>
            </div>

            @if(count($datosPeriodo['materias_radar']) >= 3)
                <div class="relative mx-auto" style="height: 220px; max-width: 320px;"
                     x-data="radarChart()"
                     x-init="init(@js($datosPeriodo['materias_radar']))">
                    <canvas x-ref="chart"></canvas>
                </div>

                <div class="mt-3 space-y-1.5 pt-3 border-t border-blue-50">
                    @foreach($datosPeriodo['materias_radar'] as $m)
                        @php
                            $color = $m['promedio'] >= 90 ? 'emerald' : ($m['promedio'] >= 70 ? 'blue' : 'red');
                        @endphp
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                            <span class="text-blue-700 flex-1 truncate">{{ $m['nombre'] }}</span>
                            <div class="w-16 bg-{{ $color }}-50 rounded-full h-1 overflow-hidden">
                                <div class="bg-{{ $color }}-500 h-full" style="width: {{ min(100, $m['promedio']) }}%"></div>
                            </div>
                            <span class="font-bold text-{{ $color }}-600 w-8 text-right">{{ number_format($m['promedio'], 1) }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif(count($datosPeriodo['materias_radar']) > 0)
                {{-- Menos de 3 materias: solo mostrar lista, el radar se ve raro --}}
                <div class="space-y-2">
                    @foreach($datosPeriodo['materias_radar'] as $m)
                        @php
                            $color = $m['promedio'] >= 90 ? 'emerald' : ($m['promedio'] >= 70 ? 'blue' : 'red');
                        @endphp
                        <div class="bg-{{ $color }}-50 border border-{{ $color }}-100 rounded-xl p-3 flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-mono text-{{ $color }}-600">{{ $m['clave'] }}</p>
                                <p class="text-sm font-semibold text-{{ $color }}-900 truncate">{{ $m['nombre'] }}</p>
                            </div>
                            <div class="text-2xl font-bold text-{{ $color }}-600 ml-2">
                                {{ number_format($m['promedio'], 1) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    @svg('lucide-bar-chart-3', 'w-10 h-10 text-blue-200 mx-auto mb-2')
                    <p class="text-xs text-blue-500">Aún no has capturado calificaciones</p>
                    <a href="{{ route('alumno.calificaciones') }}" wire:navigate
                       class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 bg-blue-700 text-white text-xs font-medium rounded-lg hover:bg-blue-800">
                        @svg('lucide-pen-line', 'w-3 h-3')
                        Capturar ahora
                    </a>
                </div>
            @endif
        </div>

        {{-- Evolución del promedio --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="mb-2">
                <p class="text-sm font-bold text-blue-900">Evolución de promedio semestral</p>
                <p class="text-[11px] text-blue-400">Historial académico completo</p>
            </div>

            @if(count($evolucion) > 0)
                <div class="relative" style="height: 220px;"
                     x-data="lineChart()"
                     x-init="init(@js($evolucion))">
                    <canvas x-ref="chart"></canvas>
                </div>

                <div class="mt-3 pt-3 border-t border-blue-50">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-blue-600 font-medium">Avance total de créditos</span>
                        <span class="font-bold text-blue-900">
                            {{ $estadisticas['creditos_aprobados'] + $estadisticas['creditos_en_curso'] }}
                            / {{ $estadisticas['total_creditos'] }}
                        </span>
                    </div>
                    <div class="w-full bg-blue-50 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-emerald-500 h-full"
                             style="width: {{ $estadisticas['total_creditos'] > 0 ? ($estadisticas['creditos_aprobados'] / $estadisticas['total_creditos']) * 100 : 0 }}%"></div>
                        <div class="bg-blue-500 h-full"
                             style="width: {{ $estadisticas['total_creditos'] > 0 ? ($estadisticas['creditos_en_curso'] / $estadisticas['total_creditos']) * 100 : 0 }}%"></div>
                    </div>
                    <div class="flex items-center gap-4 mt-2 text-[10px]">
                        <span class="flex items-center gap-1 text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Aprobados ({{ $estadisticas['creditos_aprobados'] }})
                        </span>
                        <span class="flex items-center gap-1 text-blue-700">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            En curso ({{ $estadisticas['creditos_en_curso'] }})
                        </span>
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    @svg('lucide-line-chart', 'w-10 h-10 text-blue-200 mx-auto mb-2')
                    <p class="text-xs text-blue-500">Sin historial de periodos previos</p>
                    <p class="text-[10px] text-blue-400 mt-1">Se mostrará al finalizar tu primer periodo</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- ALERTAS ACTIVAS                          --}}
    {{-- ════════════════════════════════════════ --}}
    @if($alertas->count() > 0)
        <div>
            <div class="flex items-center gap-2 mb-3">
                @svg('lucide-bell', 'w-4 h-4 text-red-600')
                <h2 class="text-sm font-bold text-blue-900">Alertas activas</h2>
                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full">
                    {{ $alertasTotal }}
                </span>
            </div>
            <div class="space-y-2">
                @foreach($alertas as $alerta)
                    @php
                        $estilo = match($alerta->prioridad) {
                            'critica' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => 'bg-red-500', 'text' => 'text-red-900'],
                            'media'   => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'bg-amber-500', 'text' => 'text-amber-900'],
                            default   => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'bg-blue-500', 'text' => 'text-blue-900'],
                        };
                    @endphp
                    <div class="border rounded-xl p-3 {{ $estilo['bg'] }} {{ $estilo['border'] }} flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg {{ $estilo['icon'] }} flex items-center justify-center flex-shrink-0">
                            @svg('lucide-alert-triangle', 'w-3.5 h-3.5 text-white')
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold {{ $estilo['text'] }}">{{ $alerta->titulo }}</p>
                            <p class="text-xs {{ $estilo['text'] }}/80 mt-0.5">{{ $alerta->mensaje }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════ --}}
    {{-- FECHAS + MENSAJES + ACCESOS              --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        {{-- Fechas del periodo --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <p class="text-sm font-bold text-blue-900 mb-3">Fechas importantes</p>
            @if($periodo)
                <div class="space-y-2">
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-center gap-2">
                        @svg('lucide-calendar-x', 'w-4 h-4 text-amber-600 flex-shrink-0')
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-amber-900">Fecha límite de baja</p>
                            <p class="text-[11px] text-amber-700">
                                {{ $periodo->fecha_limite_baja?->locale('es')->isoFormat('D MMM YYYY') ?? 'No definida' }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2">
                        @svg('lucide-calendar-check', 'w-4 h-4 text-blue-600 flex-shrink-0')
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-blue-900">Fin del periodo</p>
                            <p class="text-[11px] text-blue-700">
                                {{ $periodo->fecha_fin->locale('es')->isoFormat('D MMM YYYY') }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex items-center gap-2">
                        @svg('lucide-clock', 'w-4 h-4 text-emerald-600 flex-shrink-0')
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-emerald-900">Días en curso</p>
                            <p class="text-[11px] text-emerald-700">
                                {{ (int) $periodo->fecha_inicio->diffInDays(now()) }} días transcurridos
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-blue-400">Sin periodo activo</p>
            @endif
        </div>

        {{-- Mensajes recientes --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-bold text-blue-900">Mensajes recientes</p>
                <a href="{{ route('alumno.mensajes') }}" wire:navigate class="text-[11px] text-blue-600 hover:underline font-medium">
                    Ver todos ›
                </a>
            </div>
            @if(count($mensajesRecientes) > 0)
                <div class="space-y-2">
                    @foreach($mensajesRecientes as $m)
                        <div class="border border-blue-50 rounded-xl p-3 hover:bg-blue-50/30 transition-colors flex items-start gap-2">
                            @if(!$m['leido'])
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></span>
                            @else
                                <span class="w-1.5 h-1.5 flex-shrink-0"></span>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-blue-900 truncate">{{ $m['asunto'] }}</p>
                                <p class="text-[11px] text-blue-400 truncate">{{ $m['remitente'] }} · {{ $m['fecha'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-4 text-center">
                    @svg('lucide-inbox', 'w-8 h-8 text-blue-200 mx-auto mb-1')
                    <p class="text-[11px] text-blue-400">Sin mensajes</p>
                </div>
            @endif
        </div>

        {{-- Accesos rápidos --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <p class="text-sm font-bold text-blue-900 mb-3">Acceso rápido</p>
            <div class="divide-y divide-blue-50">
                <a href="{{ route('alumno.malla') }}" wire:navigate
                   class="flex items-center gap-3 py-2.5 hover:bg-blue-50/30 -mx-1 px-1 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @svg('lucide-layout-grid', 'w-4 h-4 text-blue-600')
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-900">Malla curricular</p>
                        <p class="text-[10px] text-blue-400">{{ $estadisticas['porcentaje_avance'] }}% completado</p>
                    </div>
                    <span class="text-blue-300">›</span>
                </a>
                <a href="{{ route('alumno.materias') }}" wire:navigate
                   class="flex items-center gap-3 py-2.5 hover:bg-blue-50/30 -mx-1 px-1 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @svg('lucide-book-open', 'w-4 h-4 text-amber-600')
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-900">Mis materias</p>
                        <p class="text-[10px] text-blue-400">Gestionar materias inscritas</p>
                    </div>
                    <span class="text-blue-300">›</span>
                </a>
                <a href="{{ route('alumno.calificaciones') }}" wire:navigate
                   class="flex items-center gap-3 py-2.5 hover:bg-blue-50/30 -mx-1 px-1 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @svg('lucide-award', 'w-4 h-4 text-emerald-600')
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-900">Ver calificaciones</p>
                        <p class="text-[10px] text-blue-400">Todos los parciales</p>
                    </div>
                    <span class="text-blue-300">›</span>
                </a>
                <a href="{{ route('alumno.historial') }}" wire:navigate
                   class="flex items-center gap-3 py-2.5 hover:bg-blue-50/30 -mx-1 px-1 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        @svg('lucide-clock', 'w-4 h-4 text-indigo-600')
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-900">Historial académico</p>
                        <p class="text-[10px] text-blue-400">Todos los semestres</p>
                    </div>
                    <span class="text-blue-300">›</span>
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════ --}}
{{-- CHART.JS + SCRIPTS                       --}}
{{-- ════════════════════════════════════════ --}}
@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush
@endonce

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function lineChart() {
        return {
            init(datos) {
                if (!datos || datos.length === 0) return;
                this.$nextTick(() => {
                    const ctx = this.$refs.chart.getContext('2d');
                    const labels = datos.map(d => d.clave);
                    const valores = datos.map(d => d.promedio);

                    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
                    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: valores,
                                borderColor: '#1d4ed8',
                                backgroundColor: gradient,
                                borderWidth: 2,
                                tension: 0.35,
                                fill: true,
                                pointBackgroundColor: '#1d4ed8',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e3a8a',
                                    titleColor: '#fff',
                                    bodyColor: '#dbeafe',
                                    padding: 10,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: { label: (c) => `Promedio: ${c.parsed.y.toFixed(2)}` }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    min: Math.max(0, Math.floor(Math.min(...valores) - 5)),
                                    max: Math.min(100, Math.ceil(Math.max(...valores) + 5)),
                                    grid: { color: '#dbeafe' },
                                    ticks: { color: '#93c5fd', font: { size: 10 } }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#93c5fd', font: { size: 10 } }
                                }
                            },
                            animation: { duration: 800, easing: 'easeOutQuart' }
                        }
                    });
                });
            }
        }
    }

    function radarChart() {
        return {
            init(materias) {
                if (!materias || materias.length < 3) return;
                this.$nextTick(() => {
                    const ctx = this.$refs.chart.getContext('2d');
                    const labels = materias.map(m => {
                        const nombre = m.nombre;
                        return nombre.length > 14 ? nombre.substring(0, 12) + '…' : nombre;
                    });
                    const valores = materias.map(m => m.promedio);

                    new Chart(ctx, {
                        type: 'radar',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: valores,
                                borderColor: '#1d4ed8',
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderWidth: 2,
                                pointBackgroundColor: '#1d4ed8',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e3a8a',
                                    titleColor: '#fff',
                                    bodyColor: '#dbeafe',
                                    padding: 10,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: { label: (c) => `${c.label}: ${c.parsed.r.toFixed(1)}` }
                                }
                            },
                            scales: {
                                r: {
                                    beginAtZero: true,
                                    min: 0,
                                    max: 100,
                                    ticks: { display: false, stepSize: 25 },
                                    grid: { color: '#dbeafe' },
                                    angleLines: { color: '#dbeafe' },
                                    pointLabels: {
                                        color: '#1e3a8a',
                                        font: { size: 10, weight: '500' }
                                    }
                                }
                            },
                            animation: { duration: 800 }
                        }
                    });
                });
            }
        }
    }
</script>