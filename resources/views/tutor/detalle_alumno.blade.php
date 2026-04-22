<x-app-layout>
    @php
        $tutor  = auth()->user()->tutor;
        $alumno = \App\Models\Alumno::with(['usuario', 'carrera'])
            ->where('tutor_id', $tutor->id)
            ->findOrFail($id);

        $alertas = \App\Models\Alerta::where('alumno_id', $alumno->id)
            ->where('atendida', false)
            ->orderByRaw("FIELD(prioridad, 'critica', 'alta', 'media', 'baja')")
            ->get();

        $periodoActual = \App\Models\Periodo::where('es_actual', '!=', 0)
            ->orderByDesc('fecha_inicio')
            ->first();

        $materiasActuales = collect();
        if ($periodoActual) {
            $materiasActuales = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
                ->where('periodo_id', $periodoActual->id)
                ->with('materiaMalla')
                ->get();
        }

        $materiasReprobadas = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
            ->where('estatus', 'reprobada')
            ->count();

        // Evolución por periodo
        $periodos  = \App\Models\Periodo::orderBy('fecha_inicio')->get();
        $evolucion = $periodos->map(function($periodo) use ($alumno) {
            $prom = \App\Models\Inscripcion::where('alumno_id', $alumno->id)
                ->where('periodo_id', $periodo->id)
                ->whereNotNull('promedio')
                ->where('promedio', '>', 0)
                ->avg('promedio');
            return [
                'sem'      => $periodo->clave,
                'prom'     => round((float)$prom, 2),
                'sin_datos'=> is_null($prom),
            ];
        })->values()->toArray();

        $promConDatos = array_values(array_filter(
            array_column($evolucion, 'prom'), fn($p) => $p > 0
        ));

        if (count($promConDatos) >= 2) {
            $minP = max(0, floor(min($promConDatos)) - 1);
            $maxP = min(10, ceil(max($promConDatos)) + 1);
        } elseif (count($promConDatos) === 1) {
            $minP = max(0, $promConDatos[0] - 2);
            $maxP = min(10, $promConDatos[0] + 2);
        } else {
            $minP = 0; $maxP = 10;
        }
        if ($minP === $maxP) { $minP = max(0, $maxP - 1); }

        $svgH = 120; $svgW = 500; $pad = 30;
        $pts  = '';
        foreach ($evolucion as $i => $d) {
            if ($d['sin_datos'] || $d['prom'] <= 0) continue;
            $divisor = count($evolucion) > 1 ? count($evolucion) - 1 : 1;
            $x = $pad + ($i / $divisor) * ($svgW - $pad * 2);
            $y = $svgH - $pad - (($d['prom'] - $minP) / ($maxP - $minP)) * ($svgH - $pad * 2);
            $y = max($pad, min($svgH - $pad, $y));
            $pts .= "{$x},{$y} ";
        }
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
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center
                                justify-center flex-shrink-0">
                        <span class="text-blue-700 font-bold text-2xl">
                            {{ strtoupper(substr($alumno->usuario->name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-blue-900">
                            {{ $alumno->usuario->name }}
                        </h1>
                        <p class="text-sm text-blue-400 mt-0.5">
                            Matrícula: {{ $alumno->matricula }} |
                            Semestre: {{ $alumno->semestre_actual }}to |
                            Promedio Global:
                            <strong class="{{ (float)$alumno->promedio_general < 7
                                ? 'text-red-500' : 'text-blue-700' }}">
                                {{ number_format((float)$alumno->promedio_general, 1) }}
                            </strong>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($alertas->count() > 0)
                        <span class="px-3 py-2 bg-red-50 border border-red-100
                                     text-red-600 text-sm font-semibold rounded-xl">
                            {{ $alertas->count() }} Alertas Activas
                        </span>
                    @endif
                     <a href="{{ route('tutor.mensajes') }}"
                    class="flex items-center gap-1.5 px-4 py-2 border border-blue-200
                   rounded-xl text-blue-700 text-sm font-medium
                   hover:bg-blue-50 transition">
                   @svg('lucide-mail', 'w-4 h-4')
                  Enviar Mensaje
                  </a>
                   <a href="{{ route('tutor.mensajes') }}?accion=asesoria"
                 class="flex items-center gap-1.5 px-4 py-2 bg-blue-600
                rounded-xl text-white text-sm font-medium
                 hover:bg-blue-700 transition">
              @svg('lucide-calendar', 'w-4 h-4')
             Programar Asesoría
             </a>
                </div>
            </div>
        </div>

        {{-- Grid 2 columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Columna izquierda --}}
            <div class="md:col-span-2 space-y-5">

                {{-- Métricas clave --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4">Métricas Clave</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Promedio Actual</p>
                            <p class="text-2xl font-bold
                               {{ (float)$alumno->promedio_general < 7 ? 'text-red-500' :
                                  ((float)$alumno->promedio_general < 8 ? 'text-amber-500'
                                  : 'text-blue-700') }}">
                                {{ number_format((float)$alumno->promedio_general, 1) }}
                            </p>
                            @if((float)$alumno->promedio_general < 7)
                                <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600
                                             rounded-full font-medium">Bajo</span>
                            @elseif((float)$alumno->promedio_general < 8)
                                <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-600
                                             rounded-full font-medium">Regular</span>
                            @else
                                <span class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-600
                                             rounded-full font-medium">Bueno</span>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Créditos Cursados</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $alumno->creditos_aprobados ?? 0 }}/240
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Materias Reprobadas</p>
                            <p class="text-2xl font-bold
                               {{ $materiasReprobadas > 0 ? 'text-red-500' : 'text-blue-900' }}">
                                {{ $materiasReprobadas }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-400 mb-1">Alertas Activas</p>
                            <p class="text-2xl font-bold
                               {{ $alertas->count() > 0 ? 'text-red-500' : 'text-blue-900' }}">
                                {{ $alertas->count() }}
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Materias actuales --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-4">Materias Actuales</h3>

                    @if($materiasActuales->isEmpty())
                        <p class="text-sm text-slate-400 text-center py-4">
                            Sin inscripciones en el periodo actual
                        </p>
                    @else
                        <div class="rounded-xl overflow-hidden border border-blue-100">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-blue-50/60">
                                        <th class="text-left text-xs font-semibold text-blue-600
                                                   px-4 py-2.5">Materia</th>
                                        <th class="text-left text-xs font-semibold text-blue-600
                                                   px-4 py-2.5">Calif.</th>
                                        <th class="text-left text-xs font-semibold text-blue-600
                                                   px-4 py-2.5">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-50">
                                    @foreach($materiasActuales as $insc)
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="px-4 py-3 text-sm text-blue-700">
                                                {{ $insc->materiaMalla->nombre ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-bold
                                                {{ (float)($insc->calificacion_final ?? 0) >= 9
                                                    ? 'text-green-600' :
                                                   ((float)($insc->calificacion_final ?? 0) >= 7
                                                    ? 'text-blue-700' : 'text-red-500') }}">
                                                {{ $insc->calificacion_final
                                                    ? number_format((float)$insc->calificacion_final, 1)
                                                    : '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($insc->estatus === 'aprobada')
                                                    <span class="text-green-500">✓</span>
                                                @elseif($insc->estatus === 'reprobada')
                                                    <span class="text-red-500">✗</span>
                                                @else
                                                    <span class="text-xs text-slate-400">En curso</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Evolución de rendimiento --}}
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
                                    $guiasD[] = round($minP + $pasoD * $g, 1);
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
                                    $divisor = count($evolucion) > 1
                                        ? count($evolucion) - 1 : 1;
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
                                    <circle cx="{{ $x }}" cy="{{ $y }}"
                                            r="5" fill="#1d4ed8"/>
                                @else
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4"
                                            fill="white" stroke="#cbd5e1"
                                            stroke-width="1.5"/>
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
                        <div class="flex items-start gap-2 p-3 bg-red-50 border
                                    border-red-100 rounded-xl mb-2">
                            @svg('lucide-alert-circle', 'w-4 h-4 text-red-400 flex-shrink-0 mt-0.5')
                            <p class="text-xs text-red-700 font-medium">
                                {{ $alerta->titulo }}
                            </p>
                        </div>
                    @empty
                        <div class="flex items-center gap-2 p-3 bg-emerald-50
                                    border border-emerald-100 rounded-xl">
                            @svg('lucide-check-circle-2', 'w-4 h-4 text-emerald-500')
                            <p class="text-xs text-emerald-700">Sin alertas activas</p>
                        </div>
                    @endforelse
                </div>

                {{-- Última interacción --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-3">Última Interacción</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-blue-400">Mensaje enviado:</p>
                            <p class="text-sm font-medium text-blue-900">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-400">Asesoría:</p>
                            <p class="text-sm font-medium text-blue-900">—</p>
                        </div>
                    </div>
                </div>

                {{-- Info general --}}
                <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-3">Información General</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Carrera</span>
                            <span class="text-blue-900 font-medium text-right"
                                  style="max-width:140px">
                                {{ $alumno->carrera->nombre ?? $alumno->carrera->clave ?? '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Semestre</span>
                            <span class="text-blue-900 font-medium">
                                {{ $alumno->semestre_actual }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Ingreso</span>
                            <span class="text-blue-900 font-medium">
                                {{ \Carbon\Carbon::parse($alumno->fecha_ingreso)->format('M Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-400">Estatus</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $alumno->estatus === 'activo'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($alumno->estatus) }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>