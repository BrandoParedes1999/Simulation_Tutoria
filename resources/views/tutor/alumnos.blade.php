<x-app-layout>
    @php
        $tutor = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados->load('usuario', 'carrera');

        $alertasPorAlumno = \App\Models\Alerta::whereIn('alumno_id', $alumnos->pluck('id'))
            ->where('atendida', false)
            ->selectRaw('alumno_id, count(*) as total')
            ->groupBy('alumno_id')
            ->pluck('total', 'alumno_id');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Alumnos</h1>
            <p class="text-sm text-blue-400 mt-0.5">Lista de alumnos asignados</p>
        </div>

        <div x-data="{
            busqueda: '',
            semestre: '',
            alerta: '',
            alumnos: {{ $alumnos->map(fn($a) => [
                'id'        => $a->id,
                'nombre'    => $a->usuario->name,
                'carrera'   => $a->carrera->nombre ?? $a->carrera->clave ?? '',
                'matricula' => $a->matricula,
                'semestre'  => $a->semestre_actual,
                'promedio'  => (float)$a->promedio_general,
                'alertas'   => $alertasPorAlumno[$a->id] ?? 0,
                'estatus'   => $a->estatus,
            ])->values()->toJson() }},

            get filtrados() {
                return this.alumnos.filter(a => {
                    const q = this.busqueda.toLowerCase();
                    const matchBusqueda = !q ||
                        a.nombre.toLowerCase().includes(q) ||
                        a.matricula.toLowerCase().includes(q);
                    const matchSemestre = !this.semestre || a.semestre == this.semestre;
                    const matchAlerta   = !this.alerta ||
                        (this.alerta === 'con' && a.alertas > 0) ||
                        (this.alerta === 'sin' && a.alertas === 0);
                    return matchBusqueda && matchSemestre && matchAlerta;
                });
            },

            exportarCSV() {
                if (this.filtrados.length === 0) {
                    alert('No hay datos para exportar');
                    return;
                }
                let csvContent = '\uFEFF';
                csvContent += 'Nombre,Matrícula,Semestre,Promedio,Alertas\n';
                this.filtrados.forEach(a => {
                    let nombreLimpio = a.nombre.replace(/,/g, '');
                    csvContent += nombreLimpio + ',' + a.matricula + ',' + a.semestre + ',' + a.promedio + ',' + a.alertas + '\n';
                });
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url  = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'lista_alumnos.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }">

            {{-- Controles --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <select x-model="semestre"
                        class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todos los semestres</option>
                    @foreach($alumnos->pluck('semestre_actual')->unique()->sort() as $sem)
                        <option value="{{ $sem }}">Semestre {{ $sem }}</option>
                    @endforeach
                </select>

                <select x-model="alerta"
                        class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700
                               bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Todas las alertas</option>
                    <option value="con">Con alertas</option>
                    <option value="sin">Sin alertas</option>
                </select>
            </div>

            {{-- Buscador --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    @svg('lucide-search', 'w-4 h-4 text-blue-300')
                </div>
                <input x-model="busqueda"
                       type="text"
                       placeholder="Buscar por nombre o matrícula..."
                       class="w-full pl-9 pr-4 py-3 border border-blue-100 rounded-2xl text-sm
                              text-blue-900 bg-white focus:outline-none focus:ring-2
                              focus:ring-blue-200 placeholder-blue-200">
            </div>

            {{-- Leyenda de riesgo: PDF #7 - WCAG 1.4.1 --}}
            <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5 font-medium">Nivel de riesgo:</span>
                <span class="flex items-center gap-1">
                    <span class="text-emerald-600 font-bold">▲</span>
                    Excelente (≥85)
                </span>
                <span class="flex items-center gap-1">
                    <span class="text-amber-500 font-bold">◆</span>
                    Regular (70–84)
                </span>
                <span class="flex items-center gap-1">
                    <span class="text-red-500 font-bold">●</span>
                    En riesgo (&lt;70)
                </span>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="bg-blue-50/60 border-b border-blue-100">
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Alumno</th>
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Matrícula</th>
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Semestre</th>
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Promedio</th>
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Alertas</th>
                            <th class="text-left text-xs font-semibold text-blue-600 px-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="a in filtrados" :key="a.id">
                            <tr class="border-b border-blue-50 hover:bg-blue-50/40 transition-colors">

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center
                                                    justify-center flex-shrink-0">
                                            <span class="text-blue-700 font-bold text-sm"
                                                  x-text="a.nombre.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-900" x-text="a.nombre"></p>
                                            <p class="text-xs text-blue-400" x-text="a.carrera"></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="text-sm text-blue-600 font-medium" x-text="a.matricula"></span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="text-sm text-slate-600" x-text="a.semestre + '°'"></span>
                                </td>

                                {{--
                                    PDF #7 — WCAG 2.1 criterios 1.4.1 y 2.4.6:
                                    Íconos por FORMA + COLOR para identificar nivel de riesgo
                                    sin depender únicamente del color.
                                --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        {{-- Ícono por forma (accesibilidad) --}}
                                        <template x-if="a.promedio >= 85">
                                            <span class="text-emerald-600 font-bold text-sm leading-none"
                                                  :title="'Rendimiento excelente: ' + a.promedio.toFixed(1)"
                                                  :aria-label="'Rendimiento excelente: ' + a.promedio.toFixed(1) + ' puntos'">
                                                ▲
                                            </span>
                                        </template>
                                        <template x-if="a.promedio >= 70 && a.promedio < 85">
                                            <span class="text-amber-500 font-bold text-sm leading-none"
                                                  :title="'Rendimiento regular: ' + a.promedio.toFixed(1)"
                                                  :aria-label="'Rendimiento regular: ' + a.promedio.toFixed(1) + ' puntos'">
                                                ◆
                                            </span>
                                        </template>
                                        <template x-if="a.promedio > 0 && a.promedio < 70">
                                            <span class="text-red-500 font-bold text-sm leading-none"
                                                  :title="'En riesgo académico: ' + a.promedio.toFixed(1)"
                                                  :aria-label="'En riesgo académico: ' + a.promedio.toFixed(1) + ' puntos'">
                                                ●
                                            </span>
                                        </template>
                                        <span class="text-sm font-bold"
                                              :class="{
                                                  'text-emerald-600': a.promedio >= 90,
                                                  'text-blue-600':    a.promedio >= 80 && a.promedio < 90,
                                                  'text-amber-500':   a.promedio >= 70 && a.promedio < 80,
                                                  'text-red-500':     a.promedio > 0 && a.promedio < 70,
                                                  'text-slate-300':   a.promedio === 0
                                              }"
                                              x-text="a.promedio > 0 ? a.promedio.toFixed(1) + ' pts' : '—'"></span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <template x-if="a.alertas > 0">
                                        <span class="inline-flex items-center justify-center
                                                     w-6 h-6 bg-red-500 text-white text-xs
                                                     font-bold rounded-full"
                                              x-text="a.alertas"></span>
                                    </template>
                                    <template x-if="a.alertas === 0">
                                        <span class="text-slate-300 text-sm">—</span>
                                    </template>
                                </td>

                                {{--
                                    PDF #7 — WCAG 2.4.6: "Ver" sin contexto es inapropiado
                                    para lectores de pantalla. Ahora usa aria-label descriptivo.
                                --}}
                                <td class="px-4 py-3">
                                    <a :href="`/tutor/alumnos/${a.id}`"
                                       :aria-label="'Ver perfil de ' + a.nombre"
                                       class="px-3 py-1.5 border border-blue-200 rounded-lg
                                              text-blue-600 text-xs font-medium
                                              hover:bg-blue-50 transition">
                                        Ver perfil
                                    </a>
                                </td>

                            </tr>
                        </template>

                        <tr x-show="filtrados.length === 0">
                            <td colspan="6" class="text-center py-8 text-sm text-slate-400">
                                No se encontraron alumnos
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-blue-50 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Mostrando <span x-text="filtrados.length"></span>
                        de {{ $alumnos->count() }} alumnos
                    </span>
                    <button @click="exportarCSV()"
                            class="text-xs text-blue-600 font-medium hover:underline">
                        Exportar Lista
                    </button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>