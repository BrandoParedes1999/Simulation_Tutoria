<x-app-layout>
    @php
        $tutor   = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados->load('usuario', 'carrera');
        $periodos = \App\Models\Periodo::orderByDesc('fecha_inicio')->get();
        $periodoActual = $periodos->firstWhere('es_actual', '!=', 0);
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
         x-data="{
            paso: 1,
            tipo: '',
            alumnoId: '',
            periodoId: '{{ $periodoActual->id ?? '' }}',
            periodoDesdeId: '',
            periodoHastaId: '',
            formato: 'pdf',
            incluirLogo: true,
            incluirFirma: true,
            incluirEmail: false,
            incluirDistribucion: true,
            incluirGraficos: true,
            incluirAlertas: true,
            incluirDetalle: false,

            siguiente() { if (this.paso < 3) this.paso++; },
            anterior()  { if (this.paso > 1) this.paso--; },

            puedeAvanzar() {
                if (this.paso === 1) return this.tipo !== '';
                if (this.paso === 2) {
                    if (this.tipo === 'individual') return this.alumnoId !== '' && this.periodoId !== '';
                    return true;
                }
                return this.formato !== '';
            },

            generarUrl() {
                let url = '{{ route('tutor.reportes') }}?';
                url += 'tipo=' + this.tipo;
                url += '&formato=' + this.formato;
                url += '&incluir_logo=' + (this.incluirLogo ? 1 : 0);
                url += '&incluir_firma=' + (this.incluirFirma ? 1 : 0);
                if (this.tipo === 'individual') {
                    url += '&alumno_id=' + this.alumnoId;
                    url += '&periodo_id=' + this.periodoId;
                }
                if (this.tipo === 'grupal') {
                    url += '&periodo_id=' + this.periodoId;
                    url += '&incluir_distribucion=' + (this.incluirDistribucion ? 1 : 0);
                    url += '&incluir_graficos=' + (this.incluirGraficos ? 1 : 0);
                    url += '&incluir_alertas=' + (this.incluirAlertas ? 1 : 0);
                    url += '&incluir_detalle=' + (this.incluirDetalle ? 1 : 0);
                }
                if (this.tipo === 'comparativo') {
                    url += '&periodo_desde=' + this.periodoDesdeId;
                    url += '&periodo_hasta=' + this.periodoHastaId;
                }
                return url;
            }
         }">

        {{-- Encabezado --}}
        <div class="mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Generación de Reportes</h1>
            <p class="text-sm text-blue-400 mt-0.5">Paso <span x-text="paso"></span> de 3</p>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center mb-8">
            <template x-for="n in [1,2,3]" :key="n">
                <div class="flex items-center flex-1 last:flex-none">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                         :class="paso >= n
                             ? 'bg-blue-600 text-white'
                             : 'bg-blue-100 text-blue-400'"
                         x-text="n">
                    </div>
                    <template x-if="n < 3">
                        <div class="flex-1 h-0.5 mx-2 transition-all"
                             :class="paso > n ? 'bg-blue-600' : 'bg-blue-100'">
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Card contenedor --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6">

            {{-- ===== PASO 1: Tipo de reporte ===== --}}
            <div x-show="paso === 1">
                <h2 class="font-bold text-blue-900 mb-5">Selecciona el Tipo de Reporte</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <button @click="tipo = 'individual'"
                            :class="tipo === 'individual'
                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                : 'border-blue-100 hover:border-blue-300'"
                            class="border-2 rounded-2xl p-5 text-left transition-all">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center
                                    justify-center mb-3">
                            @svg('lucide-bar-chart-2', 'w-5 h-5 text-blue-600')
                        </div>
                        <p class="font-bold text-blue-900">Individual</p>
                        <p class="text-xs text-blue-400 mt-1">Detalle completo de un alumno específico</p>
                    </button>

                    <button @click="tipo = 'grupal'"
                            :class="tipo === 'grupal'
                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                : 'border-blue-100 hover:border-blue-300'"
                            class="border-2 rounded-2xl p-5 text-left transition-all">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center
                                    justify-center mb-3">
                            @svg('lucide-trending-up', 'w-5 h-5 text-blue-600')
                        </div>
                        <p class="font-bold text-blue-900">Grupal</p>
                        <p class="text-xs text-blue-400 mt-1">Estadísticas del grupo asignado</p>
                    </button>

                    <button @click="tipo = 'comparativo'"
                            :class="tipo === 'comparativo'
                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                : 'border-blue-100 hover:border-blue-300'"
                            class="border-2 rounded-2xl p-5 text-left transition-all">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center
                                    justify-center mb-3">
                            @svg('lucide-git-compare', 'w-5 h-5 text-blue-600')
                        </div>
                        <p class="font-bold text-blue-900">Comparativo</p>
                        <p class="text-xs text-blue-400 mt-1">Análisis entre semestres/grupos</p>
                    </button>

                </div>
            </div>

            {{-- ===== PASO 2: Parámetros ===== --}}
            <div x-show="paso === 2">
                <h2 class="font-bold text-blue-900 mb-5">Configura los Parámetros</h2>

                {{-- Individual --}}
                <div x-show="tipo === 'individual'" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-blue-600 block mb-1.5">Alumno</label>
                        <select x-model="alumnoId"
                                class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                       text-blue-700 bg-white focus:outline-none focus:ring-2
                                       focus:ring-blue-200">
                            <option value="">Seleccionar alumno...</option>
                            @foreach($alumnos as $a)
                                <option value="{{ $a->id }}">
                                    {{ $a->usuario->name }} — {{ $a->matricula }}
                                    (Prom. {{ number_format((float)$a->promedio_general, 1) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-blue-600 block mb-1.5">Periodo</label>
                        <select x-model="periodoId"
                                class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                       text-blue-700 bg-white focus:outline-none focus:ring-2
                                       focus:ring-blue-200">
                            @foreach($periodos as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->clave }} {{ $p->es_actual ? '(Actual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Grupal --}}
                <div x-show="tipo === 'grupal'" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-blue-600 block mb-1.5">Periodo</label>
                        <select x-model="periodoId"
                                class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                       text-blue-700 bg-white focus:outline-none focus:ring-2
                                       focus:ring-blue-200">
                            @foreach($periodos as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->clave }} {{ $p->es_actual ? '(Actual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-blue-600 mb-2">Incluir:</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="incluirDistribucion"
                                       class="w-4 h-4 rounded accent-blue-600">
                                <span class="text-sm text-slate-700">Distribución de calificaciones</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="incluirGraficos"
                                       class="w-4 h-4 rounded accent-blue-600">
                                <span class="text-sm text-slate-700">Gráficos de tendencia</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="incluirAlertas"
                                       class="w-4 h-4 rounded accent-blue-600">
                                <span class="text-sm text-slate-700">Lista de alertas activas</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="incluirDetalle"
                                       class="w-4 h-4 rounded accent-blue-600">
                                <span class="text-sm text-slate-700">Detalle por alumno</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Comparativo --}}
                <div x-show="tipo === 'comparativo'" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-blue-600 block mb-1.5">Comparar</label>
                        <select class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                       text-blue-700 bg-white focus:outline-none focus:ring-2
                                       focus:ring-blue-200">
                            <option>Semestres</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-blue-600 block mb-1.5">Desde</label>
                            <select x-model="periodoDesdeId"
                                    class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                           text-blue-700 bg-white focus:outline-none focus:ring-2
                                           focus:ring-blue-200">
                                <option value="">Seleccionar...</option>
                                @foreach($periodos as $p)
                                    <option value="{{ $p->id }}">{{ $p->clave }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-blue-600 block mb-1.5">Hasta</label>
                            <select x-model="periodoHastaId"
                                    class="w-full px-4 py-2.5 border border-blue-200 rounded-xl text-sm
                                           text-blue-700 bg-white focus:outline-none focus:ring-2
                                           focus:ring-blue-200">
                                <option value="">Seleccionar...</option>
                                @foreach($periodos as $p)
                                    <option value="{{ $p->id }}">{{ $p->clave }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== PASO 3: Formato ===== --}}
            <div x-show="paso === 3">
                <h2 class="font-bold text-blue-900 mb-5">Formato de Exportación</h2>

                <div class="space-y-3 mb-6">

                    <label @click="formato = 'pdf'"
                           :class="formato === 'pdf'
                               ? 'border-blue-400 bg-blue-50'
                               : 'border-slate-200 hover:border-blue-200'"
                           class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                             :class="formato === 'pdf' ? 'border-blue-600' : 'border-slate-300'">
                            <div x-show="formato === 'pdf'"
                                 class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">
                                PDF — Reporte imprimible con gráficos
                            </p>
                            <p class="text-xs text-blue-400">Ideal para presentaciones y archivo</p>
                        </div>
                    </label>

                    <label @click="formato = 'excel'"
                           :class="formato === 'excel'
                               ? 'border-blue-400 bg-blue-50'
                               : 'border-slate-200 hover:border-blue-200'"
                           class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                             :class="formato === 'excel' ? 'border-blue-600' : 'border-slate-300'">
                            <div x-show="formato === 'excel'"
                                 class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">
                                Excel — Datos tabulares para análisis
                            </p>
                            <p class="text-xs text-blue-400">Permite manipulación de datos</p>
                        </div>
                    </label>

                    <label @click="formato = 'word'"
                           :class="formato === 'word'
                               ? 'border-blue-400 bg-blue-50'
                               : 'border-slate-200 hover:border-blue-200'"
                           class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                             :class="formato === 'word' ? 'border-blue-600' : 'border-slate-300'">
                            <div x-show="formato === 'word'"
                                 class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">
                                Word — Documento editable
                            </p>
                            <p class="text-xs text-blue-400">Ideal para personalizar el reporte</p>
                        </div>
                    </label>

                </div>

                {{-- Opciones adicionales --}}
                <div class="border-t border-blue-50 pt-4">
                    <p class="text-xs font-semibold text-slate-600 mb-2">Opciones adicionales:</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="incluirLogo"
                                   class="w-4 h-4 rounded accent-blue-600">
                            <span class="text-sm text-slate-700">Incluir logo institucional</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="incluirFirma"
                                   class="w-4 h-4 rounded accent-blue-600">
                            <span class="text-sm text-slate-700">Firma digital del tutor</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="incluirEmail"
                                   class="w-4 h-4 rounded accent-blue-600">
                            <span class="text-sm text-slate-700">Enviar copia por email</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Botones de navegación --}}
            <div class="flex items-center gap-3 mt-8 pt-5 border-t border-blue-50">
                <button x-show="paso > 1"
                        @click="anterior()"
                        class="px-5 py-2.5 border border-blue-200 rounded-xl text-blue-700
                               text-sm font-medium hover:bg-blue-50 transition">
                    Anterior
                </button>

                <button x-show="paso < 3"
                        @click="puedeAvanzar() && siguiente()"
                        :disabled="!puedeAvanzar()"
                        :class="puedeAvanzar()
                            ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer'
                            : 'bg-blue-200 cursor-not-allowed'"
                        class="px-5 py-2.5 text-white rounded-xl text-sm font-medium transition">
                    Siguiente
                </button>

                <a x-show="paso === 3"
                   :href="generarUrl()"
                   :class="!puedeAvanzar()
                       ? 'pointer-events-none opacity-50'
                       : 'hover:bg-blue-700'"
                   class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm
                          font-medium transition flex items-center gap-2">
                    @svg('lucide-download', 'w-4 h-4')
                    Generar Reporte
                </a>
            </div>

        </div>
    </div>
</x-app-layout>