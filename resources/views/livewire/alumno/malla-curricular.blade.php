<div
    x-data="{
        semestreSeleccionado: @js($semestreInicial),
        materiaSeleccionada: null,
        malla: @js($malla),

        get semestres() { return Object.keys(this.malla).map(Number).sort((a,b) => a-b); },
        get materiasDelSemestre() { return this.malla[this.semestreSeleccionado] || []; },
        get nivelSemestre() {
            const m = this.materiasDelSemestre[0];
            return m ? m.nivel : 'basico';
        },
        get creditosSemestre() {
            return this.materiasDelSemestre.reduce((s, m) => s + m.creditos, 0);
        },

        labelNivel(nivel) {
            return { basico: 'Básico', profesionalizante: 'Profesionalizante', terminal: 'Terminal' }[nivel] || 'N/A';
        },
        colorHeader(nivel) {
            return {
                basico: 'from-amber-400 to-amber-600',
                profesionalizante: 'from-emerald-500 to-emerald-700',
                terminal: 'from-blue-500 to-blue-700'
            }[nivel] || 'from-gray-400 to-gray-600';
        },
        estilo(estado) {
            return {
                aprobada:   { bg:'bg-emerald-50', border:'border-emerald-200', icon_bg:'bg-emerald-500', text:'text-emerald-900', badge_bg:'bg-emerald-100', badge_text:'text-emerald-700', label:'Aprobada'   },
                en_curso:   { bg:'bg-blue-50',    border:'border-blue-200',    icon_bg:'bg-blue-500',    text:'text-blue-900',    badge_bg:'bg-blue-100',    badge_text:'text-blue-700',    label:'En curso'    },
                disponible: { bg:'bg-amber-50',   border:'border-amber-200',   icon_bg:'bg-amber-500',   text:'text-amber-900',   badge_bg:'bg-amber-100',   badge_text:'text-amber-700',   label:'Disponible'  },
                reprobada:  { bg:'bg-red-50',     border:'border-red-200',     icon_bg:'bg-red-500',     text:'text-red-900',     badge_bg:'bg-red-100',     badge_text:'text-red-700',     label:'Reprobada'   },
                bloqueada:  { bg:'bg-gray-50',    border:'border-gray-200',    icon_bg:'bg-gray-400',    text:'text-gray-700',    badge_bg:'bg-gray-200',    badge_text:'text-gray-600',    label:'Bloqueada'   }
            }[estado];
        },
        aprobadasDe(sem) {
            return (this.malla[sem] || []).filter(m => m.estado === 'aprobada').length;
        }
    }"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
>
    {{-- ═══ HEADER ═══ --}}
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                @svg('lucide-layout-grid', 'w-5 h-5 text-blue-700')
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mi Malla Curricular</h1>
                <p class="text-xs text-blue-400">Ingeniería en Sistemas Computacionales · Plan 2010</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-blue-900">Avance del programa</p>
                <span class="text-sm font-bold text-blue-700">{{ $estadisticas['porcentaje_avance'] }}%</span>
            </div>
            <div class="w-full bg-blue-50 rounded-full h-2.5 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 h-full rounded-full transition-all duration-500"
                     style="width: {{ $estadisticas['porcentaje_avance'] }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-blue-50">
                <div class="text-center">
                    <p class="text-lg font-bold text-emerald-600">{{ $estadisticas['creditos_aprobados'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">Aprobados</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-600">{{ $estadisticas['creditos_en_curso'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">En curso</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-300">{{ $estadisticas['creditos_restantes'] }}</p>
                    <p class="text-[10px] text-blue-400 uppercase tracking-wide">Restantes</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ LEYENDA ═══ --}}
    <div class="bg-white rounded-2xl border border-blue-100 p-3 shadow-sm">
        <p class="text-xs font-medium text-blue-400 uppercase tracking-wide mb-2">Estados</p>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span>Aprobada</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full"><span class="w-2 h-2 bg-blue-500 rounded-full"></span>En curso</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full"><span class="w-2 h-2 bg-amber-500 rounded-full"></span>Disponible</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-full"><span class="w-2 h-2 bg-red-500 rounded-full"></span>Reprobada</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full"><span class="w-2 h-2 bg-gray-400 rounded-full"></span>Bloqueada</span>
        </div>
    </div>

    {{-- ═══ SELECTOR DE SEMESTRE (Alpine, instantáneo) ═══ --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto scrollbar-hide">
            <div class="flex gap-1 p-2 min-w-max">
                <template x-for="sem in semestres" :key="sem">
                    <button
                        @click="semestreSeleccionado = sem; materiaSeleccionada = null"
                        :class="semestreSeleccionado === sem ? 'bg-blue-700 text-white shadow-md' : 'text-blue-600 hover:bg-blue-50'"
                        class="flex-shrink-0 flex flex-col items-center justify-center px-4 py-2.5 rounded-xl transition-all"
                    >
                        <span class="text-[10px] font-medium uppercase tracking-wider"
                              :class="semestreSeleccionado === sem ? 'text-blue-200' : 'text-blue-400'">Semestre</span>
                        <span class="text-lg font-bold leading-none mt-0.5" x-text="sem"></span>
                        <span class="text-[10px] mt-1"
                              :class="semestreSeleccionado === sem ? 'text-blue-200' : 'text-blue-400'"
                              x-text="aprobadasDe(sem) + '/' + (malla[sem]?.length || 0)"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ═══ MATERIAS DEL SEMESTRE ═══ --}}
    <div class="space-y-3">
        <div class="rounded-2xl p-4 text-white shadow-md bg-gradient-to-r"
             :class="colorHeader(nivelSemestre)">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-white/80 uppercase tracking-wide" x-text="'Nivel ' + labelNivel(nivelSemestre)"></p>
                    <h2 class="text-xl font-bold" x-text="'Semestre ' + semestreSeleccionado"></h2>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold" x-text="creditosSemestre"></p>
                    <p class="text-xs text-white/80 uppercase tracking-wide">Créditos</p>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <template x-for="materia in materiasDelSemestre" :key="materia.id">
                <button
                    @click="materiaSeleccionada = materia"
                    :class="estilo(materia.estado).bg + ' ' + estilo(materia.estado).border"
                    class="w-full text-left border rounded-2xl p-3 hover:shadow-md transition-all active:scale-[0.99]"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                             :class="estilo(materia.estado).icon_bg">
                            <template x-if="materia.estado === 'aprobada'">@svg('lucide-check', 'w-4 h-4 text-white')</template>
                            <template x-if="materia.estado === 'en_curso'">@svg('lucide-play', 'w-4 h-4 text-white')</template>
                            <template x-if="materia.estado === 'disponible'">@svg('lucide-unlock', 'w-4 h-4 text-white')</template>
                            <template x-if="materia.estado === 'reprobada'">@svg('lucide-x', 'w-4 h-4 text-white')</template>
                            <template x-if="materia.estado === 'bloqueada'">@svg('lucide-lock', 'w-4 h-4 text-white')</template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-mono mb-0.5"
                                       :class="estilo(materia.estado).badge_text" x-text="materia.clave"></p>
                                    <p class="text-sm font-semibold leading-tight"
                                       :class="estilo(materia.estado).text" x-text="materia.nombre"></p>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full flex-shrink-0"
                                      :class="estilo(materia.estado).badge_bg + ' ' + estilo(materia.estado).badge_text"
                                      x-text="estilo(materia.estado).label"></span>
                            </div>

                            <div class="flex items-center gap-3 mt-2 text-xs"
                                 :class="estilo(materia.estado).text + '/70'">
                                <span class="flex items-center gap-1">
                                    @svg('lucide-award', 'w-3 h-3')
                                    <span x-text="materia.creditos + ' cr.'"></span>
                                </span>
                                <span class="flex items-center gap-1">
                                    @svg('lucide-clock', 'w-3 h-3')
                                    <span x-text="materia.total_horas + 'h'"></span>
                                </span>
                                <template x-if="materia.calificacion">
                                    <span class="flex items-center gap-1 font-bold">
                                        @svg('lucide-star', 'w-3 h-3')
                                        <span x-text="Number(materia.calificacion).toFixed(1)"></span>
                                    </span>
                                </template>
                                <template x-if="materia.prerrequisitos.length > 0">
                                    <span class="flex items-center gap-1">
                                        @svg('lucide-link', 'w-3 h-3')
                                        <span x-text="materia.prerrequisitos.length + ' prereq.'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </button>
            </template>
        </div>
    </div>

    {{-- ═══ MODAL DE DETALLE (Alpine, instantáneo) ═══ --}}
    <div
        x-show="materiaSeleccionada"
        x-cloak
        @keydown.escape.window="materiaSeleccionada = null"
        class="fixed inset-0 z-50 bg-blue-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
        @click.self="materiaSeleccionada = null"
        x-transition.opacity
    >
        <div
            x-show="materiaSeleccionada"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-full opacity-0"
            class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl max-h-[85vh] overflow-y-auto"
        >
            <template x-if="materiaSeleccionada">
                <div>
                    <div class="sm:hidden flex justify-center pt-3 pb-1">
                        <div class="w-12 h-1 bg-blue-200 rounded-full"></div>
                    </div>

                    <div class="px-5 pt-4 pb-3 sticky top-0 bg-white border-b border-blue-100 z-10">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-mono text-blue-400" x-text="materiaSeleccionada.clave"></p>
                                <h3 class="text-base font-bold text-blue-900 leading-tight mt-0.5" x-text="materiaSeleccionada.nombre"></h3>
                            </div>
                            <button
                                @click="materiaSeleccionada = null"
                                class="w-8 h-8 bg-blue-50 hover:bg-blue-100 rounded-full flex items-center justify-center transition-colors flex-shrink-0"
                            >
                                @svg('lucide-x', 'w-4 h-4 text-blue-600')
                            </button>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-lg font-bold text-blue-900" x-text="materiaSeleccionada.creditos"></p>
                                <p class="text-[10px] text-blue-500 uppercase">Créditos</p>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-lg font-bold text-blue-900" x-text="materiaSeleccionada.total_horas"></p>
                                <p class="text-[10px] text-blue-500 uppercase">Horas</p>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <template x-if="materiaSeleccionada.calificacion">
                                    <div>
                                        <p class="text-lg font-bold text-blue-900" x-text="Number(materiaSeleccionada.calificacion).toFixed(1)"></p>
                                        <p class="text-[10px] text-blue-500 uppercase">Promedio</p>
                                    </div>
                                </template>
                                <template x-if="!materiaSeleccionada.calificacion">
                                    <div>
                                        <p class="text-lg font-bold text-blue-300">—</p>
                                        <p class="text-[10px] text-blue-500 uppercase">Sin calif.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="bg-blue-50/50 rounded-xl p-3 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-blue-500">Tipo</span>
                                <span class="font-medium text-blue-900 capitalize" x-text="materiaSeleccionada.tipo"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-blue-500">Nivel</span>
                                <span class="font-medium text-blue-900 capitalize" x-text="materiaSeleccionada.nivel.replace(/_/g, ' ')"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-blue-500">Área</span>
                                <span class="font-medium text-blue-900 text-right capitalize" x-text="materiaSeleccionada.area.replace(/_/g, ' ')"></span>
                            </div>
                        </div>

                        <template x-if="materiaSeleccionada.prerrequisitos.length > 0">
                            <div>
                                <p class="text-xs font-medium text-blue-400 uppercase tracking-wide mb-2">Prerrequisitos</p>
                                <div class="space-y-2">
                                    <template x-for="prereq in materiaSeleccionada.prerrequisitos" :key="prereq.clave">
                                        <div class="flex items-center gap-2 p-2.5 rounded-xl border"
                                             :class="prereq.cumplido ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200'">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                                 :class="prereq.cumplido ? 'bg-emerald-500' : 'bg-gray-400'">
                                                <template x-if="prereq.cumplido">@svg('lucide-check', 'w-3.5 h-3.5 text-white')</template>
                                                <template x-if="!prereq.cumplido">@svg('lucide-x', 'w-3.5 h-3.5 text-white')</template>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-mono"
                                                   :class="prereq.cumplido ? 'text-emerald-600' : 'text-gray-500'"
                                                   x-text="prereq.clave"></p>
                                                <p class="text-sm font-medium leading-tight"
                                                   :class="prereq.cumplido ? 'text-emerald-900' : 'text-gray-700'"
                                                   x-text="prereq.nombre"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="materiaSeleccionada.prerrequisitos.length === 0">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-start gap-2">
                                @svg('lucide-info', 'w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5')
                                <p class="text-xs text-emerald-700">Esta materia no requiere prerrequisitos.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>