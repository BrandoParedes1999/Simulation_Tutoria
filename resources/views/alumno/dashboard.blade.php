<x-app-layout>
    @php
        $alumno = auth()->user()->alumno;
        $periodo = \App\Models\Periodo::where('es_actual', true)->first();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Saludo --}}
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
                <span class="text-white font-bold text-lg">{{ substr($alumno->usuario->name, 0, 1) }}</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-blue-400">
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                </p>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900 truncate">
                    Hola, {{ explode(' ', $alumno->usuario->name)[0] }} 👋
                </h1>
            </div>
        </div>

        {{-- Card info académica --}}
        <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-5 text-white shadow-lg shadow-blue-900/20 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <p class="text-xs text-blue-200 uppercase tracking-wide mb-1">Matrícula</p>
                <p class="text-2xl font-bold">{{ $alumno->matricula }}</p>
                <p class="text-sm text-blue-200 mt-3">{{ $alumno->carrera->nombre }}</p>
                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-white/20">
                    <div>
                        <p class="text-xs text-blue-200">Semestre</p>
                        <p class="text-lg font-bold">{{ $alumno->semestre_actual }}°</p>
                    </div>
                    <div class="h-8 w-px bg-white/20"></div>
                    <div>
                        <p class="text-xs text-blue-200">Periodo</p>
                        <p class="text-lg font-bold">{{ $periodo?->clave ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs rápidos --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="p-2 rounded-lg w-fit bg-emerald-50">
                    @svg('lucide-award', 'w-4 h-4 text-emerald-600')
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">{{ $alumno->creditos_aprobados }}</p>
                <p class="text-xs text-blue-400">Créditos aprobados</p>
            </div>
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="p-2 rounded-lg w-fit bg-blue-50">
                    @svg('lucide-trending-up', 'w-4 h-4 text-blue-600')
                </div>
                <p class="text-2xl font-bold text-blue-900 mt-3">
                    {{ $alumno->promedio_general ? number_format($alumno->promedio_general, 1) : '—' }}
                </p>
                <p class="text-xs text-blue-400">Promedio general</p>
            </div>
        </div>

        {{-- Test de sistema --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-start gap-3">
            @svg('lucide-check-circle-2', 'w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5')
            <div>
                <p class="font-semibold text-emerald-900 text-sm">¡Sistema funcionando!</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    La base de datos responde correctamente y tu cuenta está configurada.
                </p>
            </div>
        </div>

        {{-- Info del tutor --}}
        @if($alumno->tutor)
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <p class="text-xs text-blue-400 uppercase tracking-wide mb-2">Tu tutor</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        @svg('lucide-user-circle-2', 'w-5 h-5 text-blue-600')
                    </div>
                    <div>
                        <p class="font-semibold text-blue-900">{{ $alumno->tutor->usuario->name }}</p>
                        <p class="text-xs text-blue-400">{{ $alumno->tutor->departamento }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Próximamente --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
            <p class="text-sm text-blue-700 font-medium">🚧 Próximamente</p>
            <p class="text-xs text-blue-500 mt-1">
                Malla curricular interactiva, gráficas de rendimiento, mensajes del tutor y más.
            </p>
        </div>

    </div>
</x-app-layout>