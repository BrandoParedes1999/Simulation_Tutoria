<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte Grupal — {{ $periodo->clave }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="no-print bg-blue-900 text-white px-6 py-3 flex items-center justify-between sticky top-0 z-50">
        <a href="{{ route('tutor.reportes') }}" class="text-sm text-blue-200 hover:text-white">← Volver a reportes</a>
        <button onclick="window.print()" class="px-4 py-2 bg-white text-blue-900 text-sm font-semibold rounded-xl hover:bg-blue-50">
            🖨 Imprimir / Guardar PDF
        </button>
    </div>

    <div class="max-w-5xl mx-auto p-6 sm:p-8 bg-white my-6 rounded-2xl shadow-sm">

        {{-- Encabezado --}}
        <div class="flex items-start justify-between pb-5 mb-5 border-b-2 border-blue-700">
            <div>
                <h1 class="text-2xl font-bold text-blue-900">Reporte Grupal de Seguimiento</h1>
                <p class="text-sm text-blue-500 mt-1">Universidad Autónoma del Carmen · Sistema de Tutoría</p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p class="font-semibold text-slate-700">{{ now()->locale('es')->isoFormat('D [de] MMMM YYYY') }}</p>
                <p class="mt-1">Tutor: <strong>{{ $tutor->usuario->name ?? '—' }}</strong></p>
            </div>
        </div>

        {{-- Resumen del periodo --}}
        @php
            $promedios = $alumnos->filter(fn($a) => (float)$a->promedio_general > 0);
            $promedioGrupal = $promedios->isNotEmpty() ? round($promedios->avg('promedio_general'), 1) : 0;
        @endphp
        <div class="bg-blue-50 rounded-2xl p-5 mb-6 border border-blue-100">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-blue-400">Periodo</p>
                    <p class="font-bold text-blue-900 text-lg mt-0.5">{{ $periodo->clave }}</p>
                    <p class="text-xs text-blue-500">{{ $periodo->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Total de Alumnos</p>
                    <p class="font-bold text-blue-900 text-2xl mt-0.5">{{ $alumnos->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Promedio Grupal</p>
                    <p class="font-bold text-lg mt-0.5 {{ $promedioGrupal >= 70 ? 'text-blue-700' : 'text-red-600' }}">
                        {{ $promedioGrupal > 0 ? number_format($promedioGrupal, 1) . ' pts' : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-blue-400">Alertas Activas</p>
                    <p class="font-bold text-2xl mt-0.5 {{ $alertasPorAlumno->sum() > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ $alertasPorAlumno->sum() }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Distribución de calificaciones --}}
        @if($opciones['distribucion'])
            @php
                $dist = [
                    '90–100 Excelente' => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 90)->count(),
                    '80–89 Bueno'      => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 80 && (float)$a->promedio_general < 90)->count(),
                    '70–79 Regular'    => $alumnos->filter(fn($a) => (float)$a->promedio_general >= 70 && (float)$a->promedio_general < 80)->count(),
                    '<70 En riesgo'    => $alumnos->filter(fn($a) => (float)$a->promedio_general > 0 && (float)$a->promedio_general < 70)->count(),
                    'Sin datos'        => $alumnos->filter(fn($a) => (float)$a->promedio_general == 0)->count(),
                ];
            @endphp
            <h2 class="text-base font-bold text-blue-900 mb-3">Distribución de Calificaciones</h2>
            <div class="grid grid-cols-5 gap-3 mb-6">
                @foreach($dist as $rango => $cantidad)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                        <p class="text-2xl font-bold text-slate-700">{{ $cantidad }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $rango }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Lista de alumnos --}}
        <h2 class="text-base font-bold text-blue-900 mb-3">Lista de Alumnos</h2>
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-blue-700 text-white">
                        <th class="text-left px-3 py-2.5 font-semibold rounded-tl-lg">Alumno</th>
                        <th class="text-left px-3 py-2.5 font-semibold">Matrícula</th>
                        <th class="text-center px-3 py-2.5 font-semibold">Semestre</th>
                        <th class="text-center px-3 py-2.5 font-semibold">Promedio Gral.</th>
                        <th class="text-center px-3 py-2.5 font-semibold">Créditos</th>
                        @if($opciones['alertas'])
                            <th class="text-center px-3 py-2.5 font-semibold rounded-tr-lg">Alertas</th>
                        @else
                            <th class="rounded-tr-lg"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumnos->sortBy('usuario.name') as $a)
                        @php $prom = (float) $a->promedio_general; @endphp
                        <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-100">
                            <td class="px-3 py-2 font-medium text-slate-700">{{ $a->usuario->name ?? '—' }}</td>
                            <td class="px-3 py-2 font-mono text-blue-600">{{ $a->matricula }}</td>
                            <td class="px-3 py-2 text-center text-slate-600">{{ $a->semestre_actual }}°</td>
                            <td class="px-3 py-2 text-center font-bold
                                {{ $prom >= 90 ? 'text-emerald-600' : ($prom >= 70 ? 'text-blue-700' : ($prom > 0 ? 'text-red-600' : 'text-slate-300')) }}">
                                {{ $prom > 0 ? number_format($prom, 1) : '—' }}
                            </td>
                            <td class="px-3 py-2 text-center text-slate-600">{{ $a->creditos_aprobados ?? 0 }}</td>
                            @if($opciones['alertas'])
                                <td class="px-3 py-2 text-center">
                                    @php $alertasCount = $alertasPorAlumno[$a->id] ?? 0; @endphp
                                    @if($alertasCount > 0)
                                        <span class="bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded-full">{{ $alertasCount }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Detalle por alumno --}}
        @if($opciones['detalle'] && $inscripciones->isNotEmpty())
            <h2 class="text-base font-bold text-blue-900 mb-3">Detalle de Inscripciones del Periodo</h2>
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="text-left px-3 py-2 font-semibold text-slate-600">Alumno</th>
                            <th class="text-left px-3 py-2 font-semibold text-slate-600">Materia</th>
                            <th class="text-center px-3 py-2 font-semibold text-slate-600">P1</th>
                            <th class="text-center px-3 py-2 font-semibold text-slate-600">P2</th>
                            <th class="text-center px-3 py-2 font-semibold text-slate-600">P3</th>
                            <th class="text-center px-3 py-2 font-semibold text-slate-600">Prom.</th>
                            <th class="text-center px-3 py-2 font-semibold text-slate-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscripciones->sortBy('alumno.usuario.name') as $i)
                            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-100">
                                <td class="px-3 py-2 text-slate-700">{{ $i->alumno->usuario->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $i->materiaMalla->nombre ?? '—' }}</td>
                                <td class="px-3 py-2 text-center {{ $i->parcial1 !== null && $i->parcial1 < 70 ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                    {{ $i->parcial1 !== null ? number_format($i->parcial1, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center {{ $i->parcial2 !== null && $i->parcial2 < 70 ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                    {{ $i->parcial2 !== null ? number_format($i->parcial2, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center {{ $i->parcial3 !== null && $i->parcial3 < 70 ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                    {{ $i->parcial3 !== null ? number_format($i->parcial3, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center font-bold {{ ($i->promedio ?? 0) >= 70 ? 'text-blue-700' : 'text-red-600' }}">
                                    {{ $i->promedio !== null ? number_format($i->promedio, 1) : '—' }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded-full font-medium
                                        {{ $i->estatus === 'aprobada' ? 'bg-emerald-100 text-emerald-700' :
                                          ($i->estatus === 'reprobada' ? 'bg-red-100 text-red-700' :
                                                                         'bg-blue-100 text-blue-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $i->estatus)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Footer --}}
        <div class="flex items-center justify-between text-xs text-slate-400 mt-4 pt-4 border-t border-slate-100">
            <span>Sistema de Tutoría Académica — UNACAR</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</body>
</html>