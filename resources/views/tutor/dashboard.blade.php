<x-app-layout>
    @php
        $tutor = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Saludo --}}
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">
                Dashboard del Tutor
            </h1>
            <p class="text-sm text-blue-400 mt-0.5">
                {{ $tutor->departamento ?? 'Sin departamento asignado' }}
            </p>
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

        {{-- Test de sistema --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start gap-3">
            @svg('lucide-check-circle-2', 'w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5')
            <div>
                <p class="font-semibold text-emerald-900 text-sm">¡Sistema funcionando!</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    N° empleado: {{ $tutor->numero_empleado }} · Base de datos conectada.
                </p>
            </div>
        </div>

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

    </div>
</x-app-layout>