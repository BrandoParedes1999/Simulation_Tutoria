<x-app-layout>
    @php
        $tutor   = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados;
        $ids     = $alumnos->pluck('id');

        // Todas las alertas de los alumnos del tutor
        $todasAlertas = \App\Models\Alerta::whereIn('alumno_id', $ids)
            ->with('alumno.usuario')
            ->orderByRaw("FIELD(prioridad, 'critica', 'alta', 'media', 'baja')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Conteos por prioridad (no atendidas)
        $criticas = $todasAlertas->where('atendida', false)
            ->whereIn('prioridad', ['critica', 'alta'])->count();
        $medias   = $todasAlertas->where('atendida', false)
            ->where('prioridad', 'media')->count();
        $bajas    = $todasAlertas->where('atendida', false)
            ->where('prioridad', 'baja')->count();

        // Reglas del tutor
        $reglas = $tutor->reglasAlerta;

        // URL base para detalle alumno
        $urlDetalle = url('tutor/alumnos');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- Encabezado --}}
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Centro de Alertas</h1>
            <p class="text-sm text-blue-400 mt-0.5">Monitoreo de alumnos en riesgo</p>
        </div>

        {{-- Filtros Alpine --}}
        <div x-data="{
            prioridad: '',
            estado: '',
            periodo: '',
            alertas: {{ $todasAlertas->map(fn($a) => [
                'id'        => $a->id,
                'nombre'    => $a->alumno->usuario->name ?? 'Alumno',
                'alumno_id' => $a->alumno_id,
                'titulo'    => $a->titulo,
                'mensaje'   => $a->mensaje ?? '',
                'prioridad' => $a->prioridad,
                'categoria' => $a->categoria ?? '',
                'atendida'  => (bool)$a->atendida,
                'fecha'     => $a->created_at->format('d/m/Y'),
                'fecha_ts'  => $a->created_at->timestamp,
            ])->values()->toJson() }},
            get filtradas() {
                const ahora   = Math.floor(Date.now() / 1000);
                const semana  = 7  * 86400;
                const mes     = 30 * 86400;
                return this.alertas.filter(a => {
                    const matchP = !this.prioridad || a.prioridad === this.prioridad;
                    const matchE = !this.estado   ||
                        (this.estado === 'pendiente' && !a.atendida) ||
                        (this.estado === 'atendida'  &&  a.atendida);
                    const diff   = ahora - a.fecha_ts;
                    const matchT = !this.periodo  ||
                        (this.periodo === 'semana' && diff <= semana) ||
                        (this.periodo === 'mes'    && diff <= mes);
                    return matchP && matchE && matchT;
                });
            },
            get criticas()  { return this.filtradas.filter(a => ['critica','alta'].includes(a.prioridad) && !a.atendida); },
            get medias()    { return this.filtradas.filter(a => a.prioridad === 'media' && !a.atendida); },
            get bajas()     { return this.filtradas.filter(a => a.prioridad === 'baja'  && !a.atendida); },
            get atendidas() { return this.filtradas.filter(a => a.atendida); },
            urlBase: '{{ $urlDetalle }}'
        }">

            {{-- Selectores de filtro --}}
            <div class="flex flex-wrap gap-3">
                <select x-model="prioridad"
                        class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todas las prioridades</option>
                    <option value="critica">Crítica</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>

                <select x-model="estado"
                        class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="atendida">Atendidas</option>
                </select>

                <select x-model="periodo"
                        class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Última semana</option>
                    <option value="semana">Última semana</option>
                    <option value="mes">Último mes</option>
                </select>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                    <p class="text-3xl font-bold text-red-500" x-text="criticas.length">{{ $criticas }}</p>
                    <p class="text-xs text-blue-400 mt-1">Críticas</p>
                    <p class="text-xs text-red-400 mt-0.5">Promedio &lt;7</p>
                </div>
                <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                    <p class="text-3xl font-bold text-amber-500" x-text="medias.length">{{ $medias }}</p>
                    <p class="text-xs text-blue-400 mt-1">Medias</p>
                    <p class="text-xs text-amber-400 mt-0.5">Promedio 7-8</p>
                </div>
                <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                    <p class="text-3xl font-bold text-blue-500" x-text="bajas.length">{{ $bajas }}</p>
                    <p class="text-xs text-blue-400 mt-1">Bajas</p>
                    <p class="text-xs text-blue-400 mt-0.5">Asistencia</p>
                </div>
            </div>

            {{-- CRÍTICAS --}}
            <template x-if="criticas.length > 0">
                <div class="space-y-3">
                    <h2 class="font-bold text-blue-900 text-sm">
                        CRÍTICAS <span class="text-red-400 font-normal">(Requieren atención inmediata)</span>
                    </h2>
                    <template x-for="a in criticas" :key="a.id">
                        <div class="bg-white rounded-2xl border-l-4 border-red-400 border border-red-100
                                    p-4 shadow-sm flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                @svg('lucide-alert-circle', 'w-5 h-5 text-red-400 flex-shrink-0 mt-0.5')
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-blue-900"
                                       x-text="a.nombre + ' - ' + a.titulo"></p>
                                    <p class="text-xs text-blue-400 mt-0.5" x-text="a.mensaje"></p>
                                    <p class="text-xs text-slate-400 mt-1"
                                       x-text="'Generada: ' + a.fecha"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a :href="urlBase + '/' + a.alumno_id"
                                   class="text-xs text-blue-600 font-medium hover:underline">
                                    Ver Detalle
                                </a>
                                <span class="text-slate-200">|</span>
                                <button class="text-xs text-blue-600 font-medium hover:underline">
                                    Enviar Mensaje
                                </button>
                                <button @click="a.atendida = true"
                                        class="px-3 py-1.5 border border-blue-200 rounded-lg
                                               text-blue-600 text-xs font-medium
                                               hover:bg-blue-50 transition">
                                    Marcar atendida
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- MEDIAS --}}
            <template x-if="medias.length > 0">
                <div class="space-y-3">
                    <h2 class="font-bold text-blue-900 text-sm">
                        MEDIAS <span class="text-amber-400 font-normal">(Monitorear)</span>
                    </h2>
                    <template x-for="a in medias" :key="a.id">
                        <div class="bg-white rounded-2xl border-l-4 border-amber-400 border border-amber-100
                                    p-4 shadow-sm flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                @svg('lucide-alert-triangle', 'w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5')
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-blue-900"
                                       x-text="a.nombre + ' - ' + a.titulo"></p>
                                    <p class="text-xs text-blue-400 mt-0.5" x-text="a.mensaje"></p>
                                    <p class="text-xs text-slate-400 mt-1"
                                       x-text="'Generada: ' + a.fecha"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a :href="urlBase + '/' + a.alumno_id"
                                   class="text-xs text-blue-600 font-medium hover:underline">
                                    Ver Detalle
                                </a>
                                <button @click="a.atendida = true"
                                        class="px-3 py-1.5 border border-blue-200 rounded-lg
                                               text-blue-600 text-xs font-medium
                                               hover:bg-blue-50 transition">
                                    Marcar atendida
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- BAJAS --}}
            <template x-if="bajas.length > 0">
                <div class="space-y-3">
                    <h2 class="font-bold text-blue-900 text-sm">
                        BAJAS <span class="text-blue-400 font-normal">(Seguimiento)</span>
                    </h2>
                    <template x-for="a in bajas" :key="a.id">
                        <div class="bg-white rounded-2xl border-l-4 border-blue-300 border border-blue-100
                                    p-4 shadow-sm flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                @svg('lucide-info', 'w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5')
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-blue-900"
                                       x-text="a.nombre + ' - ' + a.titulo"></p>
                                    <p class="text-xs text-blue-400 mt-0.5" x-text="a.mensaje"></p>
                                    <p class="text-xs text-slate-400 mt-1"
                                       x-text="'Generada: ' + a.fecha"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a :href="urlBase + '/' + a.alumno_id"
                                   class="text-xs text-blue-600 font-medium hover:underline">
                                    Ver Detalle
                                </a>
                                <button @click="a.atendida = true"
                                        class="px-3 py-1.5 border border-blue-200 rounded-lg
                                               text-blue-600 text-xs font-medium
                                               hover:bg-blue-50 transition">
                                    Marcar atendida
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Sin alertas pendientes --}}
            <template x-if="criticas.length === 0 && medias.length === 0 && bajas.length === 0">
                <div class="bg-white rounded-2xl border border-blue-100 p-8 text-center shadow-sm">
                    @svg('lucide-check-circle-2', 'w-10 h-10 text-emerald-300 mx-auto mb-2')
                    <p class="text-sm font-medium text-slate-500">Sin alertas pendientes</p>
                    <p class="text-xs text-slate-400 mt-1">Todos los alumnos están siendo monitoreados</p>
                </div>
            </template>

            {{-- TABLA DE ALERTAS ATENDIDAS --}}
            <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-blue-100">
                    <h3 class="font-bold text-blue-900">Historial de Alertas</h3>
                    <p class="text-xs text-blue-400 mt-0.5">Registro completo — atendidas y pendientes</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-blue-50/60 border-b border-blue-100">
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Alumno
                                </th>
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Alerta
                                </th>
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Prioridad
                                </th>
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Fecha
                                </th>
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Estado
                                </th>
                                <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="a in filtradas" :key="'hist-' + a.id">
                                <tr class="border-b border-blue-50 hover:bg-blue-50/30 transition-colors">

                                    {{-- Alumno --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center
                                                        justify-center flex-shrink-0">
                                                <span class="text-blue-700 font-bold text-xs"
                                                      x-text="a.nombre.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <span class="text-sm text-blue-900 font-medium"
                                                  x-text="a.nombre"></span>
                                        </div>
                                    </td>

                                    {{-- Título --}}
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-slate-700" x-text="a.titulo"></p>
                                        <p class="text-xs text-slate-400" x-text="a.categoria"></p>
                                    </td>

                                    {{-- Prioridad --}}
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-red-100 text-red-600':    ['critica','alta'].includes(a.prioridad),
                                                  'bg-amber-100 text-amber-600': a.prioridad === 'media',
                                                  'bg-blue-100 text-blue-600':   a.prioridad === 'baja',
                                              }"
                                              x-text="a.prioridad.charAt(0).toUpperCase() + a.prioridad.slice(1)">
                                        </span>
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-slate-500" x-text="a.fecha"></span>
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-4 py-3">
                                        <template x-if="a.atendida">
                                            <span class="flex items-center gap-1 text-xs
                                                         text-emerald-600 font-medium">
                                                <span class="inline-block w-1.5 h-1.5 rounded-full
                                                             bg-emerald-500"></span>
                                                Atendida
                                            </span>
                                        </template>
                                        <template x-if="!a.atendida">
                                            <span class="flex items-center gap-1 text-xs
                                                         text-red-500 font-medium">
                                                <span class="inline-block w-1.5 h-1.5 rounded-full
                                                             bg-red-500"></span>
                                                Pendiente
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Acción --}}
                                    <td class="px-4 py-3">
                                        <a :href="urlBase + '/' + a.alumno_id"
                                           class="text-xs text-blue-600 font-medium hover:underline">
                                            Ver detalle
                                        </a>
                                    </td>

                                </tr>
                            </template>

                            <tr x-show="filtradas.length === 0">
                                <td colspan="6" class="text-center py-8 text-sm text-slate-400">
                                    Sin alertas con los filtros seleccionados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-blue-50">
                    <span class="text-xs text-slate-400">
                        Total: <span x-text="filtradas.length"></span> alertas
                    </span>
                </div>
            </div>

            {{-- CONFIGURAR REGLAS --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                <h3 class="font-bold text-blue-900 mb-4">Configurar Reglas de Alerta</h3>
                <div class="space-y-3">
                    @forelse($reglas as $regla)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox"
                                   {{ $regla->activa ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-blue-600">
                            <span class="text-sm text-slate-700">
                                {{ $regla->descripcion }}
                                @if($regla->umbral)
                                    <span class="text-blue-500 font-medium">
                                        {{ $regla->umbral }}
                                    </span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-400">Sin reglas configuradas</p>
                    @endforelse
                </div>
                <button class="mt-4 px-5 py-2 bg-blue-600 text-white text-sm font-medium
                               rounded-xl hover:bg-blue-700 transition">
                    Guardar Configuración
                </button>
            </div>

        </div>
    </div>
</x-app-layout>