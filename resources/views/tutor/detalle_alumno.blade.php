<x-app-layout>
    @php
        $tutor  = auth()->user()->tutor;
        $alumno = \App\Models\Alumno::with(['usuario', 'carrera'])
            ->where('tutor_id', $tutor->id)
            ->findOrFail($id);

        $alertas = \App\Models\Alerta::where('alumno_id', $alumno->id)
            ->where('atendida', false)
            ->orderByRaw("FIELD(prioridad, 'critica', 'media', 'baja')")
            ->get();

        // FIX #10: es_actual = true (no '!= 0')
        $periodoActual = \App\Models\Periodo::where('es_actual', true)->first();

        $materiasActuales = collect();
        if ($periodoActual) {
            $materiasActuales = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
                ->where('periodo_id', $periodoActual->id)
                ->with('materiaMalla:id,nombre,clave,creditos')
                ->get();
        }

        $materiasReprobadas = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
            ->where('estatus', 'reprobada')
            ->count();

        // Evolución por periodo (promedio en escala 0-100)
        $periodos  = \App\Models\Periodo::orderBy('fecha_inicio')->get();
        $evolucion = $periodos->map(function ($periodo) use ($alumno) {
            $prom = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
                ->where('periodo_id', $periodo->id)
                ->whereNotNull('promedio')
                ->where('promedio', '>', 0)
                ->avg('promedio');
            return [
                'sem'       => $periodo->clave,
                'prom'      => round((float) $prom, 2),
                'sin_datos' => is_null($prom),
            ];
        })->values()->toArray();

        $promConDatos = array_values(array_filter(
            array_column($evolucion, 'prom'), fn($p) => $p > 0
        ));

        // FIX #12/#14: Escala correcta 0-100 para SVG
        $svgH = 120; $svgW = 500; $pad = 30;
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

        // FIX #13: usar total_creditos de la carrera, no hardcodear 240
        $totalCreditos = $alumno->carrera->total_creditos ?? 320;
        $promedio = (float) $alumno->promedio_general;

        // FIX #11: umbrales en escala 0-100
        $clasificacion = match(true) {
            $promedio >= 90 => ['clase' => 'text-emerald-600', 'label' => 'Excelente'],
            $promedio >= 80 => ['clase' => 'text-blue-600',    'label' => 'Bueno'],
            $promedio >= 70 => ['clase' => 'text-amber-500',   'label' => 'Regular'],
            $promedio >  0  => ['clase' => 'text-red-500',     'label' => 'Bajo'],
            default         => ['clase' => 'text-slate-400',   'label' => 'Sin datos'],
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Volver --}}
        <a href="{{ route('tutor.alumnos') }}"
           class="flex items-center gap-1.5 text-sm text-blue-600 font-medium hover:underline w-fit">
            @svg('lucide-arrow-left', 'w-4 h-4')
            Volver a Lista de Alumnos
        </a>

        {{-- Perfil --}}
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-700 font-bold text-2xl">
                            {{ strtoupper(substr($alumno->usuario->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-blue-900">{{ $alumno->usuario->name }}</h1>
                        <p class="text-sm text-blue-400 mt-0.5">
                            Matrícula: {{ $alumno->matricula }} ·
                            Semestre: {{ $alumno->semestre_actual }}° ·
                            Promedio:
                            <strong class="{{ $clasificacion['clase'] }}">
                                {{ $promedio > 0 ? number_format($promedio, 1) . ' pts' : '—' }}
                            </strong>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($alertas->count() > 0)
                        <span class="px-3 py-2 bg-red-50 border border-red-100 text-red-600 text-sm font-semibold rounded-xl">
                            {{ $alertas->count() }} {{ $alertas->count() === 1 ? 'Alerta Activa' : 'Alertas Activas' }}
                        </span>
                    @endif
                    <a href="{{ route('tutor.mensajes') }}"
                       class="flex items-center gap-1.5 px-4 py-2 border border-blue-200 rounded-xl
                              text-blue-700 text-sm font-medium hover:bg-blue-50 transition">
                        @svg('lucide-mail', 'w-4 h-4')
                        Enviar Mensaje
                    </a>
                </div>
            </div>
        </div>

        {{-- Grid 2 columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Columna izquierda --}}
            <div class="md:col-span-2 space-y-5">

                {{-- FIX #11/#13: Métricas en escala 0-100 + créditos de carrera --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4">Métricas Clave</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Promedio Actual</p>
                            <p class="text-2xl font-bold {{ $clasificacion['clase'] }}">
                                {{ $promedio > 0 ? number_format($promedio, 1) : '—' }}
                            </p>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium mt-1 inline-block
                                {{ $promedio >= 90 ? 'bg-emerald-100 text-emerald-600' :
                                  ($promedio >= 70 ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600') }}">
                                {{ $clasificacion['label'] }}
                            </span>
                        </div>

                        {{-- FIX #13: Créditos totales de la carrera, no hardcoded 240 --}}
                        <div>
                            <p class="text-xs text-blue-400 mb-1">Créditos Cursados</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $alumno->creditos_aprobados ?? 0 }}/{{ $totalCreditos }}
                            </p>
                            <p class="text-xs text-blue-400 mt-1">
                                {{ $totalCreditos > 0
                                    ? round((($alumno->creditos_aprobados ?? 0) / $totalCreditos) * 100, 1)
                                    : 0 }}% completado
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Materias Reprobadas</p>
                            <p class="text-2xl font-bold {{ $materiasReprobadas > 0 ? 'text-red-500' : 'text-blue-900' }}">
                                {{ $materiasReprobadas }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Alertas Activas</p>
                            <p class="text-2xl font-bold {{ $alertas->count() > 0 ? 'text-red-500' : 'text-blue-900' }}">
                                {{ $alertas->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Materias actuales --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4">
                        Materias Actuales
                        @if($periodoActual)
                            <span class="text-xs font-normal text-blue-400 ml-2">{{ $periodoActual->nombre }}</span>
                        @endif
                    </h3>

                    @if($materiasActuales->isEmpty())
                        <p class="text-sm text-slate-400 text-center py-4">Sin inscripciones en el periodo actual</p>
                    @else
                        <div class="rounded-xl overflow-hidden border border-blue-100">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-blue-50/60">
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">Materia</th>
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">P1</th>
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">P2</th>
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">P3</th>
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">Prom.</th>
                                        <th class="text-left text-xs font-semibold text-blue-600 px-4 py-2.5">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-50">
                                    @foreach($materiasActuales as $insc)
                                        {{-- FIX #11: colores con umbral 70 (escala 0-100) --}}
                                        @php
                                            $prom = (float) ($insc->promedio ?? 0);
                                            $colorProm = $prom >= 90 ? 'text-emerald-600' : ($prom >= 70 ? 'text-blue-700' : 'text-red-500');
                                        @endphp
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="px-4 py-3">
                                                <p class="text-xs font-mono text-blue-500">{{ $insc->materiaMalla->clave ?? '—' }}</p>
                                                <p class="text-sm text-blue-700">{{ $insc->materiaMalla->nombre ?? '—' }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">
                                                {{ $insc->parcial1 !== null ? number_format($insc->parcial1, 1) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">
                                                {{ $insc->parcial2 !== null ? number_format($insc->parcial2, 1) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-600">
                                                {{ $insc->parcial3 !== null ? number_format($insc->parcial3, 1) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-bold {{ $colorProm }}">
                                                {{ $prom > 0 ? number_format($prom, 1) : '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($insc->estatus === 'aprobada')
                                                    <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-semibold">Aprobada</span>
                                                @elseif($insc->estatus === 'reprobada')
                                                    <span class="text-[10px] px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-semibold">Reprobada</span>
                                                @else
                                                    <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-semibold">En curso</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- FIX #12/#14: Evolución SVG con escala correcta 0-100 --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4">Evolución de Rendimiento</h3>

                    @if(count($promConDatos) === 0)
                        <div class="flex flex-col items-center justify-center py-8">
                            @svg('lucide-bar-chart-2', 'w-10 h-10 text-blue-100 mb-2')
                            <p class="text-sm text-slate-400">Sin historial de calificaciones</p>
                        </div>
                    @else
                        <svg viewBox="0 0 500 150" style="width:100%;height:180px">
                            @php
                                $guiasD = [];
                                $pasoD  = ($maxP - $minP) / 4;
                                for ($g = 0; $g <= 4; $g++) {
                                    $guiasD[] = round($minP + $pasoD * $g, 0);
                                }
                            @endphp
                            @foreach($guiasD as $guia)
                                @php
                                    $yg = $svgH - $pad
                                        - (($guia - $minP) / ($maxP - $minP))
                                        * ($svgH - $pad * 2);
                                    $yg = max($pad, min($svgH - $pad, $yg));
                                @endphp
                                <line x1="{{ $pad }}" y1="{{ $yg }}"
                                      x2="{{ $svgW - $pad }}" y2="{{ $yg }}"
                                      stroke="#e2e8f0" stroke-width="1"
                                      stroke-dasharray="4,4"/>
                                <text x="{{ $pad - 5 }}" y="{{ $yg + 3 }}"
                                      text-anchor="end" font-size="10"
                                      fill="#94a3b8">{{ $guia }}</text>
                            @endforeach

                            @if(strlen(trim($pts)) > 0)
                                <polyline points="{{ trim($pts) }}"
                                          fill="none" stroke="#1d4ed8" stroke-width="2.5"
                                          stroke-linejoin="round" stroke-linecap="round"/>
                            @endif

                            @foreach($evolucion as $i => $d)
                                @php
                                    $divisor = count($evolucion) > 1 ? count($evolucion) - 1 : 1;
                                    $x = $pad + ($i / $divisor) * ($svgW - $pad * 2);
                                    if (!$d['sin_datos'] && $d['prom'] > 0) {
                                        $y = $svgH - $pad
                                            - (($d['prom'] - $minP) / ($maxP - $minP))
                                            * ($svgH - $pad * 2);
                                        $y = max($pad, min($svgH - $pad, $y));
                                    } else {
                                        $y = $svgH - $pad;
                                    }
                                @endphp
                                @if(!$d['sin_datos'] && $d['prom'] > 0)
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#1d4ed8"/>
                                    <text x="{{ $x }}" y="{{ $y - 8 }}"
                                          text-anchor="middle" font-size="9"
                                          fill="#1d4ed8">{{ $d['prom'] }}</text>
                                @else
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4"
                                            fill="white" stroke="#cbd5e1" stroke-width="1.5"/>
                                @endif
                                <text x="{{ $x }}" y="{{ $svgH + 10 }}"
                                      text-anchor="middle" font-size="9"
                                      fill="#94a3b8">{{ $d['sem'] }}</text>
                            @endforeach
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Columna derecha --}}
            <div class="space-y-5">

                {{-- Alertas activas --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-3">Alertas Activas</h3>
                    @forelse($alertas as $alerta)
                        @php
                            $bg = match($alerta->prioridad) {
                                'critica' => 'bg-red-50 border-red-100',
                                'media'   => 'bg-amber-50 border-amber-100',
                                default   => 'bg-blue-50 border-blue-100',
                            };
                            $ic = match($alerta->prioridad) {
                                'critica' => 'text-red-400',
                                'media'   => 'text-amber-400',
                                default   => 'text-blue-400',
                            };
                        @endphp
                        <div class="flex items-start gap-2 p-3 border rounded-xl mb-2 {{ $bg }}">
                            @svg('lucide-alert-circle', 'w-4 h-4 flex-shrink-0 mt-0.5 ' . $ic)
                            <div>
                                <p class="text-xs font-semibold text-blue-900">{{ $alerta->titulo }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $alerta->mensaje }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    <span class="capitalize font-medium">{{ $alerta->prioridad }}</span>
                                    · {{ $alerta->created_at->locale('es')->isoFormat('D MMM YYYY') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                            @svg('lucide-check-circle-2', 'w-4 h-4 text-emerald-500')
                            <p class="text-xs text-emerald-700">Sin alertas activas</p>
                        </div>
                    @endforelse
                </div>

                {{-- Información general --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-3">Información General</h3>
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Carrera</span>
                            <span class="text-blue-900 font-medium text-right max-w-[140px] truncate"
                                  title="{{ $alumno->carrera->nombre ?? '' }}">
                                {{ $alumno->carrera->nombre ?? $alumno->carrera->clave ?? '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Plan</span>
                            <span class="text-blue-900 font-medium">{{ $alumno->carrera->plan ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Semestre</span>
                            <span class="text-blue-900 font-medium">{{ $alumno->semestre_actual }}°</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Ingreso</span>
                            <span class="text-blue-900 font-medium">
                                {{ $alumno->fecha_ingreso
                                    ? \Carbon\Carbon::parse($alumno->fecha_ingreso)->locale('es')->isoFormat('MMM YYYY')
                                    : '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Estatus</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $alumno->estatus === 'activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst(str_replace('_', ' ', $alumno->estatus)) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-blue-50">
                            <span class="text-blue-400">Avance</span>
                            <span class="text-blue-900 font-medium">
                                {{ $alumno->creditos_aprobados ?? 0 }}/{{ $totalCreditos }} créditos
                                ({{ $totalCreditos > 0 ? round((($alumno->creditos_aprobados ?? 0) / $totalCreditos) * 100, 1) : 0 }}%)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>