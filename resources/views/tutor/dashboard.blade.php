<x-app-layout>
    @php
        $tutor = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Dashboard del Tutor</h1>
                <p class="text-sm text-blue-400 mt-0.5">
                    Semestre 2026-1 · {{ $tutor->departamento ?? 'Sin departamento asignado' }}
                </p>
            </div>
            <div class="flex gap-2">
                <div class="flex gap-2">
                <a href="{{ route('tutor.mensajes') }}"
                class="flex items-center gap-1.5 px-4 py-2 border border-blue-200 rounded-xl text-blue-700 text-sm font-medium hover:bg-blue-50 transition">
                @svg('lucide-message-circle', 'w-4 h-4')
               Mensajes
                </a>
              </div>
           <button onclick="descargarReporteGrafico(this)" class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 rounded-xl text-white text-sm font-medium hover:bg-blue-700 transition">
             @svg('lucide-file-text', 'w-4 h-4')
             Exportar reporte
           </button>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="p-2 rounded-lg w-fit bg-blue-50">
                    @svg('lucide-users', 'w-4 h-4 text-blue-600')
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $alumnos->count() }}</p>
                <p class="text-xs text-blue-400">Alumnos asignados</p>
            </div>
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="p-2 rounded-lg w-fit bg-amber-50">
                    @svg('lucide-settings', 'w-4 h-4 text-amber-600')
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">
                    {{ $tutor->reglasAlerta->where('activa', true)->count() }}
                </p>
                <p class="text-xs text-blue-400">Reglas activas</p>
            </div>
        </div>

        {{-- Gráficas --}}
        <div id="graficas-export" class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Alumnos --}}
        @if($alumnos->count() > 0)
            <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-blue-100">
                    <h3 class="font-semibold text-blue-900">Alumnos a cargo</h3>
                </div>
                <div class="divide-y divide-blue-50">
                    @foreach($alumnos as $alumno)
                        <div class="p-4 flex items-center gap-3 hover:bg-blue-50/50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-700 font-bold text-sm">{{ substr($alumno->usuario->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-blue-900 truncate">{{ $alumno->usuario->name }}</p>
                                <p class="text-xs text-blue-400">
                                    {{ $alumno->matricula }} · Sem {{ $alumno->semestre_actual }} · {{ $alumno->carrera->clave }}
                                </p>
                            </div>
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full">
                                {{ ucfirst($alumno->estatus) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center">
                @svg('lucide-user-plus', 'w-10 h-10 text-blue-300 mx-auto mb-2')
                <p class="text-sm text-blue-700 font-medium">Sin alumnos asignados</p>
                <p class="text-xs text-blue-500 mt-1">Los alumnos aparecerán aquí cuando se te asignen.</p>
            </div>
        @endif

            {{-- Estado del grupo (dona) --}}
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
                                Excelentes (≥9)
                            </span>
                            <strong>{{ $excelentes }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                Regulares (7-8.9)
                            </span>
                            <strong>{{ $regulares }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                                En riesgo (&lt;7)
                            </span>
                            <strong>{{ $enRiesgo }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
///
        {{-- Materias reprobación + Alertas urgentes + Acciones rápidas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Materias con mayor índice de reprobación (ocupa 2 columnas) --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm md:col-span-2">
                <p class="text-sm font-bold text-blue-900">Materias con mayor índice de reprobación</p>
                <p class="text-xs text-blue-400 mt-0.5 mb-4">% de alumnos reprobados este semestre</p>

                @if($materiasReprobacion->isEmpty())
                    <div class="flex flex-col items-center justify-center py-6">
                        @svg('lucide-check-circle-2', 'w-8 h-8 text-emerald-200 mb-2')
                        <p class="text-xs text-slate-400">Sin materias con reprobación este periodo</p>
                    </div>
                @else
                    {{-- Eje X --}}
                    <div class="space-y-3">
                        @foreach($materiasReprobacion as $m)
                            @php
                                $color = $m['pct'] >= 50 ? '#ef4444' : ($m['pct'] >= 30 ? '#f59e0b' : '#3b82f6');
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500 text-right"
                                      style="min-width:130px">{{ $m['nombre'] }}</span>
                                <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         style="width:{{ $m['pct'] }}%;background:{{ $color }}">
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-slate-500"
                                      style="min-width:35px">{{ $m['pct'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                    {{-- Eje X etiquetas --}}
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
                        <a href="#" class="text-xs text-blue-500 font-medium hover:underline">
                            Ver todas &rsaquo;
                        </a>
                    </div>

                    @forelse($alertasUrgentes as $alerta)
                        @php
                            $bgColor  = match($alerta->prioridad) {
                                'critica' => 'bg-red-50 border-red-100',
                                'alta'    => 'bg-red-50 border-red-100',
                                'media'   => 'bg-amber-50 border-amber-100',
                                default   => 'bg-blue-50 border-blue-100',
                            };
                            $iconColor = match($alerta->prioridad) {
                                'critica','alta' => 'text-red-400',
                                'media'          => 'text-amber-400',
                                default          => 'text-blue-400',
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
                        <a href="#"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-message-square', 'w-4 h-4 text-blue-400')
                                Mensaje grupal
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                        <a href="#"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-file-text', 'w-4 h-4 text-blue-400')
                                Generar reporte
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                        <a href="#"
                           class="flex items-center justify-between py-2.5 hover:bg-blue-50/50 transition-colors rounded-lg px-2">
                            <span class="flex items-center gap-2.5 text-sm text-blue-700">
                                @svg('lucide-calendar', 'w-4 h-4 text-teal-400')
                                Agendar asesoría
                            </span>
                            <span class="text-slate-300 text-xs">›</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
 {{-- Alumnos que requieren atención --}}
        <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-blue-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-blue-900">Alumnos que requieren atención</h3>
                    <p class="text-xs text-blue-400 mt-0.5">Ordenados por promedio ascendente</p>
                </div>
                <a href="{{ route('tutor.alumnos') }}"
               class="text-xs text-blue-500 font-medium hover:underline">
              Ver lista completa &rsaquo;
             </a>
            </div>

            @forelse($alumnosAtencion as $alumno)
                @php
                    $alertasAlumno = \App\Models\Alerta::where('alumno_id', $alumno->id)
                        ->where('atendida', false)->count();
                @endphp
                <div class="p-4 flex items-center gap-3 border-b border-blue-50 last:border-0
                            hover:bg-blue-50/50 transition-colors">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-700 font-bold text-sm">
                            {{ strtoupper(substr($alumno->usuario->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-blue-900 truncate">{{ $alumno->usuario->name }}</p>
                        <p class="text-xs text-blue-400">
                            {{ $alumno->carrera->nombre ?? $alumno->carrera->clave ?? '' }}
                            · Sem. {{ $alumno->semestre_actual }}
                        </p>
                    </div>
                    <div class="text-center px-3">
                        <p class="text-xs text-slate-400 mb-0.5">Promedio</p>
                        <p class="text-sm font-bold text-red-500">
                            {{ number_format((float)$alumno->promedio_general, 1) }}
                        </p>
                    </div>
                    <div class="text-center px-3">
                        <p class="text-xs text-slate-400 mb-0.5">Alertas</p>
                        <p class="text-sm font-bold text-red-500">{{ $alertasAlumno }}</p>
                    </div>
                    <div class="text-center px-3 hidden sm:block">
                        <p class="text-xs text-slate-400 mb-0.5">Créditos</p>
                        <p class="text-sm font-bold text-slate-600">
                            {{ $alumno->creditos_aprobados ?? 0 }}
                        </p>
                    </div>
                 <a :href="`{{ url('/tutor/alumnos') }}/${a.id}`"
                 class="px-3 py-1.5 border border-blue-200 rounded-lg
                 text-blue-600 text-xs font-medium
                 hover:bg-blue-50 transition">
                Ver Detalle
                </a>
                </div>
            @empty
                <div class="p-6 text-center">
                    @svg('lucide-check-circle-2', 'w-8 h-8 text-emerald-300 mx-auto mb-2')
                    <p class="text-sm text-slate-400">Todos los alumnos tienen promedio mayor a 7.0</p>
                </div>
            @endforelse
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