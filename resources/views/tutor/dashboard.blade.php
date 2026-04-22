<x-app-layout>
    @php
        $tutor   = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados;
        $ids     = $alumnos->pluck('id');

        // Promedio grupal (escala 0-100)
        $promediosLista = $alumnos
            ->filter(fn($a) => (float)$a->promedio_general > 0)
            ->map(fn($a) => (float)$a->promedio_general);

        $promedioGrupal = $promediosLista->count() > 0
            ? number_format($promediosLista->avg(), 1)
            : '0.0';

        // FIX #7: Mostrar alertas NO atendidas reales, no reglas configuradas
        $alertasSinAtender = \App\Models\Alerta::whereIn('alumno_id', $ids)
            ->where('atendida', false)->count();

        // Reglas configuradas (dato separado)
        $reglasActivas = $tutor->reglasAlerta->where('activa', true)->count();

        // FIX #2: Escala 0-100 → umbral 70 (no 7)
        $alumnosCriticos = $alumnos->filter(
            fn($a) => (float)$a->promedio_general > 0 && (float)$a->promedio_general < 70
        );

        // FIX #3: Distribución en escala 0-100
        $dist = [
            '90-100' => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 90)->count(),
            '80-89'  => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 80 && (float)$a->promedio_general < 90)->count(),
            '70-79'  => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 70 && (float)$a->promedio_general < 80)->count(),
            '<70'    => $alumnosCriticos->count(),
        ];
        $colores = ['90-100' => '#22c55e', '80-89' => '#3b82f6', '70-79' => '#f59e0b', '<70' => '#ef4444'];

        // Dona (misma lógica, umbrales corregidos)
        $excelentes = $dist['90-100'];
        $regulares  = $dist['80-89'] + $dist['70-79'];
        $enRiesgo   = $dist['<70'];
        $totalDona  = max($alumnos->count(), 1);
        $r = 45; $cx = 70; $cy = 70;
        $circ = 2 * M_PI * $r;
        $segs = [
            ['val' => $excelentes, 'color' => '#22c55e'],
            ['val' => $regulares,  'color' => '#3b82f6'],
            ['val' => $enRiesgo,   'color' => '#ef4444'],
        ];
        $off = 0; $arcos = [];
        foreach ($segs as $s) {
            $len = ($s['val'] / $totalDona) * $circ;
            $arcos[] = ['len' => $len, 'offset' => $circ - $off, 'color' => $s['color']];
            $off += $len;
        }

        // Evolución real desde inscripciones por periodo
        $periodos  = \App\Models\Periodo::orderBy('fecha_inicio')->get();
        $evolucion = $periodos->map(function ($periodo) use ($ids) {
            $promPeriodo = \App\Models\Inscripcion::whereIn('alumno_id', $ids)
                ->where('periodo_id', $periodo->id)
                ->whereNotNull('promedio')
                ->where('promedio', '>', 0)
                ->avg('promedio');
            return [
                'sem'       => $periodo->clave,
                'prom'      => round((float) $promPeriodo, 2),
                'sin_datos' => is_null($promPeriodo),
            ];
        })->values()->toArray();

        $promConDatos = array_values(array_filter(
            array_column($evolucion, 'prom'), fn($p) => $p > 0
        ));

        // FIX #4: Escala 0-100 para SVG — min/max ajustados
        $svgH = 80; $svgW = 240; $pad = 20;
        if (count($promConDatos) >= 2) {
            $margen = 5;
            $minP = max(0,   floor(min($promConDatos)) - $margen);
            $maxP = min(100, ceil(max($promConDatos))  + $margen);
        } elseif (count($promConDatos) === 1) {
            $minP = max(0,   $promConDatos[0] - 10);
            $maxP = min(100, $promConDatos[0] + 10);
        } else {
            $minP = 0; $maxP = 100;
        }
        if ($minP === $maxP) { $minP = max(0, $maxP - 10); }

        $pts = '';
        foreach ($evolucion as $i => $d) {
            if ($d['sin_datos'] || $d['prom'] <= 0) continue;
            $divisor = count($evolucion) > 1 ? count($evolucion) - 1 : 1;
            $x = $pad + ($i / $divisor) * ($svgW - $pad * 2);
            $y = $svgH - $pad - (($d['prom'] - $minP) / ($maxP - $minP)) * ($svgH - $pad * 2);
            $y = max($pad, min($svgH - $pad, $y));
            $pts .= "{$x},{$y} ";
        }

        // FIX #1: es_actual = true (no '!= 0')
        // FIX #5: calificacion_final < 70 (escala 0-100)
        $periodoActual = \App\Models\Periodo::where('es_actual', true)->first();
        $materiasReprobacion = collect();
        if ($periodoActual) {
            $materiasReprobacion = \App\Models\Inscripcion::whereIn('alumno_id', $ids)
                ->where('periodo_id', $periodoActual->id)
                ->whereNotNull('calificacion_final')
                ->where('calificacion_final', '>', 0)
                ->with('materiaMalla:id,nombre')
                ->get()
                ->groupBy('materia_malla_id')
                ->map(function ($grupo) {
                    $total      = $grupo->count();
                    // FIX #5: reprobado = calificacion_final < 70
                    $reprobados = $grupo->filter(fn($i) => (float) $i->calificacion_final < 70)->count();
                    $pct        = $total > 0 ? round(($reprobados / $total) * 100) : 0;
                    return [
                        'nombre'     => $grupo->first()->materiaMalla->nombre ?? 'Sin nombre',
                        'reprobados' => $reprobados,
                        'total'      => $total,
                        'pct'        => $pct,
                    ];
                })
                ->filter(fn($m) => $m['reprobados'] > 0)
                ->sortByDesc('pct')
                ->take(5)
                ->values();
        }

        // FIX #6: alumnosAtencion con umbral correcto < 70
        $alumnosAtencion = $alumnos
            ->filter(fn($a) => (float) $a->promedio_general > 0 && (float) $a->promedio_general < 70)
            ->sortBy('promedio_general')
            ->take(5);

        // Alertas urgentes
        $alertasUrgentes = \App\Models\Alerta::whereIn('alumno_id', $ids)
            ->where('atendida', false)
            ->orderByRaw("FIELD(prioridad, 'critica', 'media', 'baja')")
            ->with('alumno.usuario:id,name')
            ->take(3)
            ->get();

        // Alertas por alumno para sección de atención
        $alertasPorAlumno = \App\Models\Alerta::whereIn('alumno_id', $ids)
            ->where('atendida', false)
            ->selectRaw('alumno_id, count(*) as total')
            ->groupBy('alumno_id')
            ->pluck('total', 'alumno_id');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Dashboard del Tutor</h1>
                <p class="text-sm text-blue-400 mt-0.5">
                    {{ $periodoActual?->nombre ?? 'Sin periodo activo' }} · {{ $tutor->departamento ?? 'Sin departamento' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tutor.mensajes') }}"
                   class="flex items-center gap-1.5 px-4 py-2 border border-blue-200 rounded-xl text-blue-700 text-sm font-medium hover:bg-blue-50 transition">
                    @svg('lucide-message-circle', 'w-4 h-4')
                    Mensajes
                </a>
                <button onclick="descargarReporteGrafico(this)" class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 rounded-xl text-white text-sm font-medium hover:bg-blue-700 transition">
             @svg('lucide-file-text', 'w-4 h-4')
             Exportar reporte
           </button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="p-2 rounded-lg w-fit bg-blue-50">
                        @svg('lucide-users', 'w-4 h-4 text-blue-600')
                    </div>
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $alumnos->count() }}</p>
                <p class="text-xs text-blue-400 mt-0.5">Alumnos asignados</p>
                <p class="text-xs text-emerald-500 mt-2">Grupo asignado</p>
            </div>

            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="p-2 rounded-lg w-fit bg-blue-50">
                        @svg('lucide-trending-up', 'w-4 h-4 text-blue-600')
                    </div>
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $promedioGrupal }}</p>
                <p class="text-xs text-blue-400 mt-0.5">Promedio grupal</p>
                {{-- FIX #8: color basado en escala 0-100 --}}
                <p class="text-xs mt-2
                    {{ (float)$promedioGrupal >= 90 ? 'text-emerald-500' : ((float)$promedioGrupal >= 70 ? 'text-blue-500' : 'text-red-500') }}">
                    {{ (float)$promedioGrupal >= 90 ? 'Excelente' : ((float)$promedioGrupal >= 70 ? 'Satisfactorio' : 'Requiere atención') }}
                </p>
            </div>

            {{-- FIX #7: Mostrar alertas REALES sin atender --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="p-2 rounded-lg w-fit bg-amber-50">
                        @svg('lucide-alert-circle', 'w-4 h-4 text-amber-500')
                    </div>
                    @if($alertasSinAtender > 0)
                        <span class="text-xs text-red-500 font-semibold">↑</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $alertasSinAtender }}</p>
                <p class="text-xs text-blue-400 mt-0.5">Alertas sin atender</p>
                <p class="text-xs text-amber-400 mt-2">{{ $reglasActivas }} reglas activas</p>
            </div>

            {{-- FIX #2: umbral < 70 --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <div class="p-2 rounded-lg w-fit bg-red-50">
                        @svg('lucide-book-open', 'w-4 h-4 text-red-500')
                    </div>
                    @if($alumnosCriticos->count() > 0)
                        <span class="text-xs text-red-400 font-semibold">↘</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $alumnosCriticos->count() }}</p>
                <p class="text-xs text-blue-400 mt-0.5">Alumnos en riesgo</p>
                <p class="text-xs text-red-400 mt-2">Promedio menor a 70 pts</p>
            </div>
        </div>

       
      {{-- Gráficas --}}
        <div id="graficas-export" class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- FIX #3: Distribución con rangos 0-100 --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <p class="text-sm font-bold text-blue-900">Distribución de calificaciones</p>
                <p class="text-xs text-blue-400 mt-0.5 mb-4">Alumnos por rango — semestre actual</p>
                <div class="flex gap-2">
                    <div class="flex flex-col justify-between text-right" style="height:110px">
                        <span class="text-xs text-slate-400">{{ max(1, $alumnos->count()) }}</span>
                        <span class="text-xs text-slate-400">{{ max(1, intval($alumnos->count() * 0.75)) }}</span>
                        <span class="text-xs text-slate-400">{{ max(1, intval($alumnos->count() * 0.5)) }}</span>
                        <span class="text-xs text-slate-400">{{ max(1, intval($alumnos->count() * 0.25)) }}</span>
                        <span class="text-xs text-slate-400">0</span>
                    </div>
                    <div class="flex items-end gap-3 flex-1" style="height:110px">
                        @foreach($dist as $rango => $cantidad)
                            <div class="flex flex-col items-center flex-1 gap-1 h-full justify-end">
                                <div class="w-full rounded-t-md"
                                     style="background:{{ $colores[$rango] }};
                                            height:{{ max(4, $alumnos->count() > 0 ? ($cantidad / $alumnos->count()) * 100 : 0) }}px">
                                </div>
                                <span class="text-slate-400" style="font-size:9px">{{ $rango }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3">
                    @foreach($dist as $rango => $cantidad)
                        <span class="flex items-center gap-1 text-xs text-slate-500">
                            <span class="inline-block w-2 h-2 rounded-full"
                                  style="background:{{ $colores[$rango] }}"></span>
                            {{ $rango }} pts ({{ $cantidad }})
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- FIX #4: Evolución con escala correcta 0-100 --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <p class="text-sm font-bold text-blue-900">Evolución del promedio grupal</p>
                <p class="text-xs text-blue-400 mt-0.5 mb-2">Últimos {{ count($evolucion) }} periodos</p>

                @if(count($promConDatos) === 0)
                    <div class="flex flex-col items-center justify-center" style="height:120px">
                        @svg('lucide-bar-chart-2', 'w-8 h-8 text-blue-100 mb-2')
                        <p class="text-xs text-slate-400 text-center">Sin calificaciones registradas aún</p>
                    </div>
                @else
                    <svg viewBox="0 0 240 100" style="width:100%;height:120px">
                        @php
                            $paso  = ($maxP - $minP) / 4;
                            $guias = [];
                            for ($g = 0; $g <= 4; $g++) {
                                $guias[] = round($minP + $paso * $g, 0);
                            }
                        @endphp
                        @foreach($guias as $guia)
                            @php
                                $yg = $svgH - $pad - (($guia - $minP) / ($maxP - $minP)) * ($svgH - $pad * 2);
                                $yg = max($pad, min($svgH - $pad, $yg));
                            @endphp
                            <line x1="{{ $pad }}" y1="{{ $yg }}"
                                  x2="{{ $svgW - $pad }}" y2="{{ $yg }}"
                                  stroke="#e2e8f0" stroke-width="0.5"/>
                            <text x="{{ $pad - 2 }}" y="{{ $yg + 3 }}"
                                  text-anchor="end" font-size="8" fill="#94a3b8">{{ $guia }}</text>
                        @endforeach

                        @if(strlen(trim($pts)) > 0)
                            <polyline points="{{ trim($pts) }}"
                                      fill="none" stroke="#3b82f6" stroke-width="2"
                                      stroke-linejoin="round" stroke-linecap="round"/>
                        @endif

                        @foreach($evolucion as $i => $d)
                            @php
                                $divisor = count($evolucion) > 1 ? count($evolucion) - 1 : 1;
                                $x = $pad + ($i / $divisor) * ($svgW - $pad * 2);
                                if (!$d['sin_datos'] && $d['prom'] > 0) {
                                    $y = $svgH - $pad - (($d['prom'] - $minP) / ($maxP - $minP)) * ($svgH - $pad * 2);
                                    $y = max($pad, min($svgH - $pad, $y));
                                } else {
                                    $y = $svgH - $pad;
                                }
                            @endphp
                            @if(!$d['sin_datos'] && $d['prom'] > 0)
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="3" fill="#3b82f6"/>
                            @else
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="3"
                                        fill="white" stroke="#cbd5e1" stroke-width="1.5"/>
                            @endif
                            <text x="{{ $x }}" y="97"
                                  text-anchor="middle" font-size="8" fill="#94a3b8">{{ $d['sem'] }}</text>
                        @endforeach
                    </svg>
                @endif

                <p class="text-xs text-blue-500 mt-1 font-medium">
                    — Promedio grupal · Actual:
                    <strong class="text-blue-700">{{ $promedioGrupal }}</strong> pts
                </p>
            </div>

            {{-- Estado del grupo (dona) - umbrales corregidos en variables --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <p class="text-sm font-bold text-blue-900">Estado del grupo</p>
                <p class="text-xs text-blue-400 mt-0.5 mb-3">{{ $alumnos->count() }} alumnos en total</p>
                <div class="flex items-center gap-3">
                    <svg viewBox="0 0 140 140" style="width:100px;height:100px;flex-shrink:0">
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                fill="none" stroke="#f1f5f9" stroke-width="20"/>
                        @foreach($arcos as $arc)
                            @if($arc['len'] > 0)
                                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                                        fill="none"
                                        stroke="{{ $arc['color'] }}"
                                        stroke-width="20"
                                        stroke-dasharray="{{ $arc['len'] }} {{ $circ - $arc['len'] }}"
                                        stroke-dashoffset="{{ $arc['offset'] }}"
                                        transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                            @endif
                        @endforeach
                        <text x="{{ $cx }}" y="{{ $cy - 4 }}"
                              text-anchor="middle" font-size="10" fill="#94a3b8">Regulares</text>
                        <text x="{{ $cx }}" y="{{ $cy + 10 }}"
                              text-anchor="middle" font-size="14" font-weight="bold" fill="#1e3a5f">
                            {{ $regulares }}
                        </text>
                    </svg>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                Excelentes (≥90)
                            </span>
                            <strong>{{ $excelentes }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                Regulares (70-89)
                            </span>
                            <strong>{{ $regulares }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                                En riesgo (&lt;70)
                            </span>
                            <strong>{{ $enRiesgo }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Materias reprobación + Alertas + Acciones --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- FIX #5: Materias con reprobación (< 70) --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm md:col-span-2">
                <p class="text-sm font-bold text-blue-900">Materias con mayor índice de reprobación</p>
                <p class="text-xs text-blue-400 mt-0.5 mb-4">% de alumnos con promedio &lt; 70 pts este semestre</p>

                @if($materiasReprobacion->isEmpty())
                    <div class="flex flex-col items-center justify-center py-6">
                        @svg('lucide-check-circle-2', 'w-8 h-8 text-emerald-200 mb-2')
                        <p class="text-xs text-slate-400">Sin materias con reprobación este periodo</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($materiasReprobacion as $m)
                            @php
                                $color = $m['pct'] >= 50 ? '#ef4444' : ($m['pct'] >= 30 ? '#f59e0b' : '#3b82f6');
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500 text-right truncate"
                                      style="min-width:130px">{{ $m['nombre'] }}</span>
                                <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         style="width:{{ $m['pct'] }}%;background:{{ $color }}">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-slate-600"
                                      style="min-width:55px">
                                    {{ $m['pct'] }}% ({{ $m['reprobados'] }}/{{ $m['total'] }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between mt-2 px-36">
                        <span class="text-xs text-slate-400">0%</span>
                        <span class="text-xs text-slate-400">25%</span>
                        <span class="text-xs text-slate-400">50%</span>
                        <span class="text-xs text-slate-400">75%</span>
                        <span class="text-xs text-slate-400">100%</span>
                    </div>
                @endif
            </div>

            {{-- Alertas urgentes + Acciones rápidas --}}
            <div class="space-y-4">

                {{-- Alertas urgentes --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-bold text-blue-900">Alertas urgentes</p>
                        <a href="{{ route('tutor.alertas') }}" class="text-xs text-blue-500 font-medium hover:underline">
                            Ver todas ›
                        </a>
                    </div>
                    @forelse($alertasUrgentes as $alerta)
                        @php
                            $bgColor = match($alerta->prioridad) {
                                'critica' => 'bg-red-50 border-red-100',
                                'media'   => 'bg-amber-50 border-amber-100',
                                default   => 'bg-blue-50 border-blue-100',
                            };
                            $iconColor = match($alerta->prioridad) {
                                'critica' => 'text-red-400',
                                'media'   => 'text-amber-400',
                                default   => 'text-blue-400',
                            };
                        @endphp
                        <div class="border rounded-xl p-3 mb-2 {{ $bgColor }}">
                            <div class="flex items-center gap-1.5">
                                @svg('lucide-alert-circle', 'w-3.5 h-3.5 flex-shrink-0 ' . $iconColor)
                                <p class="text-xs font-semibold text-blue-900">
                                    {{ $alerta->alumno->usuario->name ?? 'Alumno' }}
                                </p>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 ml-5">{{ $alerta->titulo }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Sin alertas urgentes</p>
                    @endforelse
                </div>

                {{-- Acciones rápidas --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                    <p class="text-sm font-bold text-blue-900 mb-3">Acciones rápidas</p>
                    <div class="divide-y divide-blue-50">
                        <a href="{{ route('tutor.mensajes') }}"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-message-square', 'w-4 h-4 text-blue-400')
                                Mensaje grupal
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                        <a href="{{ route('tutor.reportes') }}"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-file-text', 'w-4 h-4 text-blue-400')
                                Generar reporte
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                        <a href="{{ route('tutor.alertas') }}"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-bell', 'w-4 h-4 text-amber-400')
                                Ver alertas
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- FIX #6/#8: Alumnos que requieren atención — umbral < 70, sin N+1 --}}
        <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-blue-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-blue-900">Alumnos que requieren atención</h3>
                    <p class="text-xs text-blue-400 mt-0.5">Promedio menor a 70 pts — ordenados ascendente</p>
                </div>
                <a href="{{ route('tutor.alumnos') }}" class="text-xs text-blue-500 font-medium hover:underline">
                    Ver lista completa ›
                </a>
            </div>

            @forelse($alumnosAtencion as $alumno)
                @php
                    // Sin N+1: usa el array ya calculado
                    $alertasAlumno = $alertasPorAlumno[$alumno->id] ?? 0;
                    $promedio      = (float) $alumno->promedio_general;
                    $colorProm     = $promedio >= 70 ? 'text-amber-500' : 'text-red-500';
                @endphp
                <div class="p-4 flex items-center gap-3 border-b border-blue-50 last:border-0
                            hover:bg-blue-50/50 transition-colors">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-700 font-bold text-sm">
                            {{ strtoupper(substr($alumno->usuario->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-blue-900 truncate">{{ $alumno->usuario->name ?? '—' }}</p>
                        <p class="text-xs text-blue-400">
                            {{ $alumno->carrera->nombre ?? $alumno->carrera->clave ?? '' }}
                            · Sem. {{ $alumno->semestre_actual }}
                        </p>
                    </div>
                    <div class="text-center px-3">
                        <p class="text-xs text-slate-400 mb-0.5">Promedio</p>
                        <p class="text-sm font-bold {{ $colorProm }}">
                            {{ number_format($promedio, 1) }}
                        </p>
                    </div>
                    <div class="text-center px-3">
                        <p class="text-xs text-slate-400 mb-0.5">Alertas</p>
                        <p class="text-sm font-bold {{ $alertasAlumno > 0 ? 'text-red-500' : 'text-slate-400' }}">
                            {{ $alertasAlumno }}
                        </p>
                    </div>
                    <div class="text-center px-3 hidden sm:block">
                        <p class="text-xs text-slate-400 mb-0.5">Créditos</p>
                        <p class="text-sm font-bold text-slate-600">{{ $alumno->creditos_aprobados ?? 0 }}</p>
                    </div>
                    <a href="{{ route('tutor.alumno-detalle', $alumno->id) }}"
                       class="px-3 py-1.5 border border-blue-300 rounded-lg text-blue-600
                              text-xs font-medium hover:bg-blue-50 transition whitespace-nowrap">
                        Ver detalle
                    </a>
                </div>
            @empty
                <div class="p-6 text-center">
                    @svg('lucide-check-circle-2', 'w-8 h-8 text-emerald-300 mx-auto mb-2')
                    <p class="text-sm text-slate-400">Todos los alumnos tienen promedio ≥ 70 pts</p>
                </div>
            @endforelse
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
      <script>

   function descargarReporteGrafico() {

    const elemento = document.getElementById('graficas-export');

    const fecha = new Date();
    const fechaFormateada = fecha.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const logo = new Image();
    logo.src = '/IMAGES/escudo_unacar.png';

    logo.onload = function () {

        html2canvas(elemento, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true
        }).then(canvas => {

            const imgData = canvas.toDataURL('image/png');

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('portrait', 'mm', 'a4');

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            // =========================
            // 🟦 HEADER (FONDO AZUL)
            // =========================
            pdf.setFillColor(37, 99, 235);
            pdf.rect(0, 0, pageWidth, 22, 'F');

            // =========================
            // 🏫 LOGO (ENCIMA DEL AZUL)
            // =========================
            pdf.addImage(logo, 'PNG', 10, 3, 18, 18);

            // =========================
            // 📝 TÍTULOS
            // =========================
            pdf.setTextColor(255, 255, 255);
            pdf.setFontSize(14);
            pdf.text('Sistema de Tutorías Académicas', pageWidth / 2, 10, { align: 'center' });

            pdf.setFontSize(10);
            pdf.text('Reporte de desempeño del grupo', pageWidth / 2, 17, { align: 'center' });

            // =========================
            // 📅 FECHA
            // =========================
            pdf.setTextColor(60, 60, 60);
            pdf.setFontSize(10);
            pdf.text('Fecha de generación: ' + fechaFormateada, 15, 32);

            pdf.setDrawColor(220, 220, 220);
            pdf.line(15, 35, pageWidth - 15, 35);

            // =========================
            // 📊 GRÁFICAS (IMAGEN DEL DASH)
            // =========================
            const imgWidth = pageWidth - 20;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let y = 40;

            // si la gráfica es muy grande, evita que se salga
            if (imgHeight > pageHeight - 50) {
                pdf.addImage(imgData, 'PNG', 10, y, imgWidth, pageHeight - 60);
            } else {
                pdf.addImage(imgData, 'PNG', 10, y, imgWidth, imgHeight);
            }

            // =========================
            // 📄 FOOTER
            // =========================
            pdf.setFontSize(8);
            pdf.setTextColor(120, 120, 120);
            pdf.text(
                'Generado automáticamente por el sistema académico',
                pageWidth / 2,
                pageHeight - 10,
                { align: 'center' }
            );

            pdf.save('reporte-graficas.pdf');
        });
    };

    logo.onerror = function () {
        alert('No se pudo cargar el logo');
    };
}
</script>
</x-app-layout>