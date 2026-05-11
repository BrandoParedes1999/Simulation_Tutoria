<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte Individual — {{ $alumno->usuario->name ?? 'Alumno' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-slate-800">

    {{-- Barra de acciones --}}
    <div class="no-print bg-blue-900 text-white px-6 py-3 flex items-center justify-between sticky top-0 z-50 shadow-lg">
        <a href="{{ route('tutor.reportes') }}"
           class="flex items-center gap-1.5 text-sm text-blue-200 hover:text-white transition">
            ← Volver a reportes
        </a>
        <button onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 bg-white text-blue-900 text-sm font-semibold rounded-xl hover:bg-blue-50 transition shadow-sm">
            🖨 Imprimir / Guardar PDF
        </button>
    </div>

    <div class="max-w-4xl mx-auto p-6 sm:p-8 bg-white my-6 rounded-2xl shadow-sm">

        {{-- Encabezado --}}
        <div class="flex items-start justify-between pb-5 mb-5 border-b-2 border-blue-700">
            <div>
                <h1 class="text-2xl font-bold text-blue-900">Reporte de Seguimiento Individual</h1>
                <p class="text-sm text-blue-500 mt-1">Universidad Autónoma del Carmen · Sistema de Tutoría Académica</p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p class="font-semibold text-slate-700">{{ now()->locale('es')->isoFormat('D [de] MMMM YYYY') }}</p>
                <p class="mt-1">Tutor: <strong>{{ $tutor->usuario->name ?? '—' }}</strong></p>
                @if($tutor->departamento)
                    <p>{{ $tutor->departamento }}</p>
                @endif
            </div>
        </div>

        {{-- Datos del alumno --}}
        <div class="bg-blue-50 rounded-2xl p-5 mb-6 border border-blue-100">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-blue-400">Nombre completo</p>
                    <p class="font-bold text-blue-900 mt-0.5">{{ $alumno->usuario->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Matrícula</p>
                    <p class="font-bold text-blue-900 font-mono mt-0.5">{{ $alumno->matricula }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Carrera</p>
                    <p class="font-semibold text-blue-800 mt-0.5 text-sm">{{ $alumno->carrera->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Semestre</p>
                    <p class="font-bold text-blue-900 mt-0.5">{{ $alumno->semestre_actual }}° semestre</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-blue-200">
                <div>
                    <p class="text-xs text-blue-400">Periodo evaluado</p>
                    <p class="font-semibold text-blue-800 mt-0.5">{{ $periodo->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Promedio general</p>
                    <p class="font-bold mt-0.5 text-lg
                        {{ (float)$alumno->promedio_general >= 90 ? 'text-emerald-600' :
                          ((float)$alumno->promedio_general >= 70 ? 'text-blue-700' : 'text-red-600') }}">
                        {{ (float)$alumno->promedio_general > 0
                            ? number_format((float)$alumno->promedio_general, 1) . ' pts'
                            : 'Sin datos' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Créditos aprobados</p>
                    <p class="font-bold text-blue-900 text-lg mt-0.5">{{ $alumno->creditos_aprobados ?? 0 }}</p>
                </div>
            </div>
        </div>

        {{-- Materias del periodo --}}
        <h2 class="text-base font-bold text-blue-900 mb-3">Materias del Periodo — {{ $periodo->clave }}</h2>
        @if($inscripciones->isEmpty())
            <div class="bg-slate-50 rounded-xl p-6 text-center text-slate-400 mb-6">
                Sin inscripciones registradas en este periodo.
            </div>
        @else
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-blue-700 text-white">
                            <th class="text-left px-3 py-2.5 font-semibold rounded-tl-lg">Clave</th>
                            <th class="text-left px-3 py-2.5 font-semibold">Materia</th>
                            <th class="text-center px-3 py-2.5 font-semibold">Cr.</th>
                            <th class="text-center px-3 py-2.5 font-semibold">P1</th>
                            <th class="text-center px-3 py-2.5 font-semibold">P2</th>
                            <th class="text-center px-3 py-2.5 font-semibold">P3</th>
                            <th class="text-center px-3 py-2.5 font-semibold">Prom.</th>
                            <th class="text-center px-3 py-2.5 font-semibold rounded-tr-lg">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscripciones as $i)
                            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-100">
                                <td class="px-3 py-2 font-mono text-blue-600">{{ $i->materiaMalla->clave ?? '—' }}</td>
                                <td class="px-3 py-2 font-medium text-slate-700">{{ $i->materiaMalla->nombre ?? '—' }}</td>
                                <td class="px-3 py-2 text-center text-slate-500">{{ $i->materiaMalla->creditos ?? 0 }}</td>
                                <td class="px-3 py-2 text-center font-semibold
                                    {{ $i->parcial1 !== null && $i->parcial1 < 70 ? 'text-red-600' : 'text-slate-700' }}">
                                    {{ $i->parcial1 !== null ? number_format($i->parcial1, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold
                                    {{ $i->parcial2 !== null && $i->parcial2 < 70 ? 'text-red-600' : 'text-slate-700' }}">
                                    {{ $i->parcial2 !== null ? number_format($i->parcial2, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold
                                    {{ $i->parcial3 !== null && $i->parcial3 < 70 ? 'text-red-600' : 'text-slate-700' }}">
                                    {{ $i->parcial3 !== null ? number_format($i->parcial3, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center font-bold text-sm
                                    {{ ($i->promedio ?? 0) >= 90 ? 'text-emerald-600' :
                                      (($i->promedio ?? 0) >= 70 ? 'text-blue-700' : 'text-red-600') }}">
                                    {{ $i->promedio !== null ? number_format($i->promedio, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($i->estatus === 'aprobada')
                                        <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Aprobada</span>
                                    @elseif($i->estatus === 'reprobada')
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Reprobada</span>
                                    @else
                                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">En curso</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Fila de totales --}}
                        @php $promediosPeriodo = $inscripciones->filter(fn($i) => $i->promedio !== null); @endphp
                        <tr class="bg-blue-50 border-t-2 border-blue-200 font-semibold">
                            <td colspan="2" class="px-3 py-2 text-blue-900 text-xs">Totales del periodo</td>
                            <td class="px-3 py-2 text-center text-blue-700">
                                {{ $inscripciones->sum(fn($i) => $i->materiaMalla->creditos ?? 0) }}
                            </td>
                            <td colspan="3"></td>
                            <td class="px-3 py-2 text-center font-bold text-blue-900">
                                {{ $promediosPeriodo->isNotEmpty() ? number_format($promediosPeriodo->avg('promedio'), 1) : '—' }}
                            </td>
                            <td class="px-3 py-2 text-center text-xs text-blue-600">
                                {{ $inscripciones->where('estatus','aprobada')->count() }} apr. /
                                {{ $inscripciones->where('estatus','reprobada')->count() }} rep.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Historial de evolución --}}
        @if($evolucion->count() > 0)
            <h2 class="text-base font-bold text-blue-900 mb-3">Evolución Histórica del Promedio</h2>
            <table class="w-full text-xs border-collapse mb-6">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="text-left px-3 py-2 font-semibold text-slate-600">Periodo</th>
                        <th class="text-center px-3 py-2 font-semibold text-slate-600">Promedio</th>
                        <th class="text-center px-3 py-2 font-semibold text-slate-600">Clasificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evolucion as $e)
                        <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-700">{{ $e->clave }}</td>
                            <td class="px-3 py-2 text-center font-bold
                                {{ $e->promedio >= 90 ? 'text-emerald-600' :
                                  ($e->promedio >= 70 ? 'text-blue-700' : 'text-red-600') }}">
                                {{ number_format($e->promedio, 1) }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($e->promedio >= 90)
                                    <span class="text-emerald-600 font-medium">Excelente</span>
                                @elseif($e->promedio >= 80)
                                    <span class="text-blue-600 font-medium">Bueno</span>
                                @elseif($e->promedio >= 70)
                                    <span class="text-amber-600 font-medium">Regular</span>
                                @else
                                    <span class="text-red-600 font-medium">Bajo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Alertas activas --}}
        @if($alertas->count() > 0)
            <h2 class="text-base font-bold text-blue-900 mb-3">Alertas Activas ({{ $alertas->count() }})</h2>
            <div class="space-y-2 mb-6">
                @foreach($alertas as $alerta)
                    <div class="border rounded-xl p-3 flex items-start gap-3
                        {{ $alerta->prioridad === 'critica' ? 'bg-red-50 border-red-200' :
                          ($alerta->prioridad === 'media'   ? 'bg-amber-50 border-amber-200' :
                                                              'bg-blue-50 border-blue-200') }}">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-800">{{ $alerta->titulo }}</p>
                                <span class="text-xs font-medium capitalize px-2 py-0.5 rounded-full
                                    {{ $alerta->prioridad === 'critica' ? 'bg-red-100 text-red-700' :
                                      ($alerta->prioridad === 'media'   ? 'bg-amber-100 text-amber-700' :
                                                                          'bg-blue-100 text-blue-700') }}">
                                    {{ $alerta->prioridad }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $alerta->mensaje }}</p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $alerta->created_at->locale('es')->isoFormat('D [de] MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Área de firmas --}}
        <div class="border-t border-slate-200 pt-6 mt-6">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-center">
                    <div class="h-12 border-b border-slate-300 mb-2"></div>
                    <p class="text-xs text-slate-500">Firma del Tutor</p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $tutor->usuario->name ?? '—' }}</p>
                    @if($tutor->grado_academico)
                        <p class="text-xs text-slate-400">{{ $tutor->grado_academico }}</p>
                    @endif
                </div>
                <div class="text-center">
                    <div class="h-12 border-b border-slate-300 mb-2"></div>
                    <p class="text-xs text-slate-500">Firma del Alumno</p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $alumno->usuario->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $alumno->matricula }}</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between text-xs text-slate-400 mt-6 pt-4 border-t border-slate-100">
            <span>Sistema de Tutoría Académica — Universidad Autónoma del Carmen</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</body>
</html>