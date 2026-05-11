<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte Comparativo</title>
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

    <div class="max-w-4xl mx-auto p-6 sm:p-8 bg-white my-6 rounded-2xl shadow-sm">

        {{-- Encabezado --}}
        <div class="flex items-start justify-between pb-5 mb-5 border-b-2 border-blue-700">
            <div>
                <h1 class="text-2xl font-bold text-blue-900">Reporte Comparativo de Periodos</h1>
                <p class="text-sm text-blue-500 mt-1">Universidad Autónoma del Carmen · Sistema de Tutoría</p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p class="font-semibold text-slate-700">{{ now()->locale('es')->isoFormat('D [de] MMMM YYYY') }}</p>
                <p class="mt-1">Tutor: <strong>{{ $tutor->usuario->name ?? '—' }}</strong></p>
            </div>
        </div>

        {{-- Cards de periodos --}}
        <div class="grid grid-cols-{{ $datos->count() }} gap-4 mb-6">
            @foreach($datos as $d)
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                    <p class="text-xs text-blue-400 uppercase tracking-wide mb-1">{{ $d['periodo']->clave }}</p>
                    <p class="text-sm text-blue-600 mb-4">{{ $d['periodo']->nombre }}</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-blue-400">Promedio grupal</p>
                            <p class="text-2xl font-bold {{ $d['promedio'] >= 70 ? 'text-blue-700' : 'text-red-600' }}">
                                {{ $d['promedio'] > 0 ? number_format($d['promedio'], 1) . ' pts' : '—' }}
                            </p>
                        </div>
                        <div class="flex gap-4 pt-3 border-t border-blue-200">
                            <div>
                                <p class="text-xs text-emerald-600 font-medium">Aprobadas</p>
                                <p class="text-lg font-bold text-emerald-700">{{ $d['aprobadas'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-red-500 font-medium">Reprobadas</p>
                                <p class="text-lg font-bold text-red-600">{{ $d['reprobadas'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-500 font-medium">En curso</p>
                                <p class="text-lg font-bold text-blue-600">{{ $d['en_curso'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabla comparativa --}}
        @php
            $datosArr  = $datos->values();
            $showDiff  = $datosArr->count() === 2;
            $metricas  = [
                'promedio'   => ['label' => 'Promedio grupal',       'float' => true],
                'aprobadas'  => ['label' => 'Materias aprobadas',    'float' => false],
                'reprobadas' => ['label' => 'Materias reprobadas',   'float' => false],
                'en_curso'   => ['label' => 'Materias en curso',     'float' => false],
            ];
        @endphp
        <h2 class="text-base font-bold text-blue-900 mb-3">Tabla Comparativa</h2>
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-blue-700 text-white">
                        <th class="text-left px-4 py-3 font-semibold rounded-tl-lg">Métrica</th>
                        @foreach($datosArr as $d)
                            <th class="text-center px-4 py-3 font-semibold">{{ $d['periodo']->clave }}</th>
                        @endforeach
                        @if($showDiff)
                            <th class="text-center px-4 py-3 font-semibold rounded-tr-lg">Variación</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($metricas as $key => $meta)
                        <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-100">
                            <td class="px-4 py-2.5 font-medium text-slate-700">{{ $meta['label'] }}</td>
                            @foreach($datosArr as $d)
                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">
                                    {{ $meta['float'] ? number_format($d[$key], 1) . ' pts' : $d[$key] }}
                                </td>
                            @endforeach
                            @if($showDiff)
                                @php $diff = $datosArr[1][$key] - $datosArr[0][$key]; @endphp
                                <td class="px-4 py-2.5 text-center font-bold
                                    {{ $diff > 0 ? 'text-emerald-600' : ($diff < 0 ? 'text-red-600' : 'text-slate-400') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ $meta['float'] ? number_format($diff, 1) : $diff }}
                                    @if($diff !== 0){{ $diff > 0 ? ' ↑' : ' ↓' }}@endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Lista de alumnos del grupo --}}
        <h2 class="text-base font-bold text-blue-900 mb-3">Alumnos del Grupo ({{ $alumnos->count() }})</h2>
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="text-left px-3 py-2 font-semibold text-slate-600">Alumno</th>
                        <th class="text-left px-3 py-2 font-semibold text-slate-600">Matrícula</th>
                        <th class="text-center px-3 py-2 font-semibold text-slate-600">Semestre</th>
                        <th class="text-center px-3 py-2 font-semibold text-slate-600">Promedio Actual</th>
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
                                {{ $prom >= 70 ? 'text-blue-700' : ($prom > 0 ? 'text-red-600' : 'text-slate-300') }}">
                                {{ $prom > 0 ? number_format($prom, 1) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between text-xs text-slate-400 mt-4 pt-4 border-t border-slate-100">
            <span>Sistema de Tutoría Académica — UNACAR</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</body>
</html>