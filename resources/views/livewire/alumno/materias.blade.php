<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

    {{-- HEADER: Periodo y estado --}}
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                @svg('lucide-book-open', 'w-5 h-5 text-blue-700')
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Inscripción de Materias</h1>
                <p class="text-xs text-blue-400">Gestiona las materias de tu periodo actual</p>
            </div>
        </div>

        @if($periodo)
            <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="text-xs text-blue-400 uppercase tracking-wide">Periodo actual</p>
                        <p class="text-base font-bold text-blue-900">{{ $periodo->nombre }}</p>
                    </div>
                    @if($periodoAbierto)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full flex-shrink-0">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Abierto
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full flex-shrink-0">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Cerrado
                        </span>
                    @endif
                </div>

                @if($periodoAbierto)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
                        @svg('lucide-clock', 'w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5')
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-amber-900">
                                @if($diasRestantesInscripcion > 0)
                                    {{ $diasRestantesInscripcion }} {{ $diasRestantesInscripcion === 1 ? 'día restante' : 'días restantes' }} para inscribir
                                @else
                                    Último día para inscribir
                                @endif
                            </p>
                            <p class="text-xs text-amber-700">
                                Fecha límite: {{ $periodo->fecha_limite_inscripcion->isoFormat('D [de] MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                        @svg('lucide-alert-circle', 'w-4 h-4 text-red-600 flex-shrink-0 mt-0.5')
                        <p class="text-xs text-red-700">
                            El periodo de inscripción ha cerrado. Solo puedes consultar las materias ya inscritas.
                        </p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
                @svg('lucide-alert-triangle', 'w-5 h-5 text-red-600 flex-shrink-0 mt-0.5')
                <div>
                    <p class="font-semibold text-red-900 text-sm">Sin periodo activo</p>
                    <p class="text-xs text-red-700 mt-0.5">
                        No hay ningún periodo académico activo. Contacta a control escolar.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- TABS --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-blue-100">
            <button
                wire:click="cambiarTab('disponibles')"
                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition-colors relative
                    {{ $tab === 'disponibles' ? 'text-blue-700 bg-blue-50/50' : 'text-blue-400 hover:text-blue-600 hover:bg-blue-50/30' }}">
                @svg('lucide-plus-circle', 'w-4 h-4')
                <span class="hidden sm:inline">Disponibles</span>
                <span class="sm:hidden">Dispo.</span>
                <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full
                    {{ $tab === 'disponibles' ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-600' }}">
                    {{ $disponibles->count() }}
                </span>
                @if($tab === 'disponibles')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-700"></span>
                @endif
            </button>

            <button
                wire:click="cambiarTab('carrito')"
                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition-colors relative
                    {{ $tab === 'carrito' ? 'text-blue-700 bg-blue-50/50' : 'text-blue-400 hover:text-blue-600 hover:bg-blue-50/30' }}">
                @svg('lucide-shopping-cart', 'w-4 h-4')
                <span>Carrito</span>
                @if(count($carrito) > 0)
                    <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full
                        {{ $tab === 'carrito' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-700' }}">
                        {{ count($carrito) }}
                    </span>
                @endif
                @if($tab === 'carrito')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-700"></span>
                @endif
            </button>

            <button
                wire:click="cambiarTab('inscritas')"
                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition-colors relative
                    {{ $tab === 'inscritas' ? 'text-blue-700 bg-blue-50/50' : 'text-blue-400 hover:text-blue-600 hover:bg-blue-50/30' }}">
                @svg('lucide-check-circle-2', 'w-4 h-4')
                <span>Inscritas</span>
                <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full
                    {{ $tab === 'inscritas' ? 'bg-emerald-500 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $inscritas->count() }}
                </span>
                @if($tab === 'inscritas')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-700"></span>
                @endif
            </button>
        </div>

        <div class="p-4 sm:p-5">

            {{-- ─── TAB 1: DISPONIBLES ─── --}}
            @if($tab === 'disponibles')
                <div class="space-y-4">

                    {{-- Sugeridas --}}
                    @if($sugeridas->count() > 0)
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-2">
                                    @svg('lucide-sparkles', 'w-4 h-4 text-blue-600')
                                    <div>
                                        <p class="text-sm font-bold text-blue-900">Sugeridas para ti</p>
                                        <p class="text-[11px] text-blue-600">
                                            Semestre {{ $alumno->semestre_actual }}° · {{ $sugeridas->count() }} {{ $sugeridas->count() === 1 ? 'materia' : 'materias' }}
                                        </p>
                                    </div>
                                </div>
                                @if($periodoAbierto)
                                    <button
                                        wire:click="agregarSugeridas"
                                        class="text-xs font-semibold bg-blue-700 text-white px-3 py-1.5 rounded-lg hover:bg-blue-800 active:scale-95 transition-all flex items-center gap-1 flex-shrink-0">
                                        @svg('lucide-plus', 'w-3 h-3')
                                        Agregar todas
                                    </button>
                                @endif
                            </div>
                            <div class="space-y-1.5">
                                @foreach($sugeridas as $mat)
                                    <div class="flex items-center gap-2 text-xs text-blue-800">
                                        @svg('lucide-check', 'w-3 h-3 text-blue-600 flex-shrink-0')
                                        <span class="font-mono text-blue-600">{{ $mat['clave'] }}</span>
                                        <span class="truncate">{{ $mat['nombre'] }}</span>
                                        <span class="text-blue-500 ml-auto flex-shrink-0">{{ $mat['creditos'] }} cr.</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Filtros --}}
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    @svg('lucide-search', 'w-4 h-4 text-blue-300')
                                </div>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="busqueda"
                                    placeholder="Buscar por nombre o clave..."
                                    class="w-full pl-9 pr-3 py-2 text-sm border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none bg-blue-50/30 text-blue-900 placeholder-blue-300">
                            </div>
                            @if($busqueda || $filtroSemestre)
                                <button
                                    wire:click="limpiarFiltros"
                                    class="px-3 py-2 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-xl transition-colors flex items-center gap-1">
                                    @svg('lucide-x', 'w-3.5 h-3.5')
                                    Limpiar
                                </button>
                            @endif
                        </div>

                        @if(count($semestresDisponibles) > 1)
                            <div class="flex gap-1.5 overflow-x-auto scrollbar-hide pb-1">
                                <button
                                    wire:click="$set('filtroSemestre', null)"
                                    class="flex-shrink-0 px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                                        {{ !$filtroSemestre ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                                    Todos
                                </button>
                                @foreach($semestresDisponibles as $sem)
                                    <button
                                        wire:click="$set('filtroSemestre', {{ $sem }})"
                                        class="flex-shrink-0 px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                                            {{ $filtroSemestre === $sem ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                                        Sem {{ $sem }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Listado de materias --}}
                    @if($disponibles->count() > 0)
                        <div class="space-y-2">
                            @foreach($disponibles as $materia)
                                @php
                                    $enCarrito = in_array($materia['id'], $carrito);
                                    $esReprobada = $materia['estado'] === 'reprobada';
                                @endphp
                                <div class="bg-white border rounded-2xl p-3 transition-all
                                    {{ $enCarrito ? 'border-blue-400 bg-blue-50/30 shadow-sm' : 'border-blue-100 hover:border-blue-200 hover:shadow-sm' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm
                                            {{ $esReprobada ? 'bg-red-500' : 'bg-amber-500' }}">
                                            @if($esReprobada)
                                                @svg('lucide-rotate-ccw', 'w-4 h-4 text-white')
                                            @else
                                                @svg('lucide-unlock', 'w-4 h-4 text-white')
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="text-[10px] font-mono text-blue-500">{{ $materia['clave'] }}</p>
                                                        <span class="text-[10px] text-blue-400">Sem {{ $materia['semestre'] }}</span>
                                                        @if($esReprobada)
                                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-semibold rounded-full">
                                                                Recursar
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm font-semibold text-blue-900 leading-tight mt-0.5">
                                                        {{ $materia['nombre'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-blue-500 mt-1">
                                                <span class="flex items-center gap-1">
                                                    @svg('lucide-award', 'w-3 h-3')
                                                    {{ $materia['creditos'] }} cr.
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    @svg('lucide-clock', 'w-3 h-3')
                                                    {{ $materia['total_horas'] }}h
                                                </span>
                                            </div>
                                        </div>

                                        @if($periodoAbierto)
                                            @if($enCarrito)
                                                <button
                                                    wire:click="quitarDelCarrito({{ $materia['id'] }})"
                                                    class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl transition-colors flex-shrink-0"
                                                    title="Quitar del carrito">
                                                    @svg('lucide-check', 'w-4 h-4')
                                                </button>
                                            @else
                                                <button
                                                    wire:click="agregarAlCarrito({{ $materia['id'] }})"
                                                    class="p-2 bg-blue-700 hover:bg-blue-800 text-white rounded-xl transition-all active:scale-95 flex-shrink-0"
                                                    title="Agregar al carrito">
                                                    @svg('lucide-plus', 'w-4 h-4')
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-blue-50/50 rounded-2xl p-8 text-center">
                            @svg('lucide-search-x', 'w-12 h-12 text-blue-300 mx-auto mb-2')
                            <p class="text-sm font-medium text-blue-700">No hay materias disponibles</p>
                            <p class="text-xs text-blue-500 mt-1">
                                @if($busqueda || $filtroSemestre)
                                    Prueba ajustando los filtros
                                @else
                                    ¡Felicidades! Ya cursaste todas las materias que puedes inscribir por ahora.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ─── TAB 2: CARRITO ─── --}}
            @if($tab === 'carrito')
                <div class="space-y-4">
                    @if($materiasEnCarrito->count() > 0)

                        <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-4 text-white shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs text-blue-200 uppercase tracking-wide">Total del carrito</p>
                                <button
                                    wire:click="vaciarCarrito"
                                    wire:confirm="¿Seguro que quieres vaciar el carrito?"
                                    class="text-xs text-blue-200 hover:text-white flex items-center gap-1">
                                    @svg('lucide-trash-2', 'w-3 h-3')
                                    Vaciar
                                </button>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-3xl font-bold">{{ $materiasEnCarrito->count() }}</p>
                                    <p class="text-xs text-blue-200">{{ $materiasEnCarrito->count() === 1 ? 'materia' : 'materias' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold">{{ $creditosCarrito }}</p>
                                    <p class="text-xs text-blue-200">créditos</p>
                                </div>
                            </div>
                        </div>

                        @if(!empty($erroresCarrito))
                            <div class="bg-red-50 border border-red-200 rounded-2xl p-3 space-y-2">
                                <div class="flex items-center gap-2">
                                    @svg('lucide-alert-triangle', 'w-4 h-4 text-red-600')
                                    <p class="text-sm font-bold text-red-900">Hay problemas con tu carrito</p>
                                </div>
                                @if(isset($erroresCarrito['general']))
                                    <p class="text-xs text-red-700">{{ $erroresCarrito['general'] }}</p>
                                @else
                                    <div class="space-y-1">
                                        @foreach($erroresCarrito as $materiaId => $error)
                                            <p class="text-xs text-red-700">• {{ $error }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="space-y-2">
                            @foreach($materiasEnCarrito as $materia)
                                @php $tieneError = isset($erroresCarrito[$materia['id']]); @endphp
                                <div class="bg-white border rounded-2xl p-3
                                    {{ $tieneError ? 'border-red-200 bg-red-50/30' : 'border-blue-100' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                            @svg('lucide-shopping-cart', 'w-4 h-4 text-white')
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-[10px] font-mono text-blue-500">{{ $materia['clave'] }}</p>
                                                <span class="text-[10px] text-blue-400">Sem {{ $materia['semestre'] }}</span>
                                            </div>
                                            <p class="text-sm font-semibold text-blue-900 leading-tight mt-0.5">
                                                {{ $materia['nombre'] }}
                                            </p>
                                            <div class="flex items-center gap-3 text-xs text-blue-500 mt-1">
                                                <span>{{ $materia['creditos'] }} cr.</span>
                                                <span>{{ $materia['total_horas'] }}h</span>
                                            </div>
                                        </div>
                                        <button
                                            wire:click="quitarDelCarrito({{ $materia['id'] }})"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors flex-shrink-0">
                                            @svg('lucide-x', 'w-4 h-4')
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($periodoAbierto)
                            <button
                                wire:click="confirmarInscripcion"
                                wire:confirm="¿Confirmar la inscripción de {{ $materiasEnCarrito->count() }} materia(s)?"
                                @disabled(!empty($erroresCarrito))
                                class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3.5 rounded-xl font-semibold shadow-md shadow-emerald-600/25 transition-all flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="confirmarInscripcion" class="flex items-center gap-2">
                                    @svg('lucide-check', 'w-5 h-5')
                                    Confirmar inscripción
                                </span>
                                <span wire:loading wire:target="confirmarInscripcion" class="flex items-center gap-2">
                                    @svg('lucide-loader-2', 'w-5 h-5 animate-spin')
                                    Procesando...
                                </span>
                            </button>
                        @endif

                    @else
                        <div class="bg-blue-50/50 rounded-2xl p-8 text-center">
                            @svg('lucide-shopping-cart', 'w-12 h-12 text-blue-300 mx-auto mb-2')
                            <p class="text-sm font-medium text-blue-700">Tu carrito está vacío</p>
                            <p class="text-xs text-blue-500 mt-1 mb-4">Agrega materias desde la pestaña "Disponibles"</p>
                            <button
                                wire:click="cambiarTab('disponibles')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white text-sm font-medium rounded-xl hover:bg-blue-800 transition-colors">
                                @svg('lucide-arrow-left', 'w-4 h-4')
                                Ver disponibles
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ─── TAB 3: INSCRITAS ─── --}}
            @if($tab === 'inscritas')
                <div class="space-y-3">
                    @if($inscritas->count() > 0)
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3 flex items-start gap-2">
                            @svg('lucide-info', 'w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5')
                            <p class="text-xs text-emerald-700">
                                Estás inscrito en <span class="font-bold">{{ $inscritas->count() }}</span>
                                {{ $inscritas->count() === 1 ? 'materia' : 'materias' }} este periodo.
                                Total: <span class="font-bold">{{ $inscritas->sum('materia.creditos') }} créditos</span>.
                            </p>
                        </div>

                        <div class="space-y-2">
                            @foreach($inscritas as $item)
                                <div class="bg-white border border-emerald-100 rounded-2xl p-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                            @svg('lucide-play', 'w-4 h-4 text-white')
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-[10px] font-mono text-emerald-600">{{ $item['materia']['clave'] }}</p>
                                                <span class="text-[10px] text-emerald-500">Sem {{ $item['materia']['semestre'] }}</span>
                                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded-full">
                                                    En curso
                                                </span>
                                            </div>
                                            <p class="text-sm font-semibold text-emerald-900 leading-tight mt-0.5">
                                                {{ $item['materia']['nombre'] }}
                                            </p>
                                            <div class="flex items-center gap-3 text-xs text-emerald-700 mt-1">
                                                <span>{{ $item['materia']['creditos'] }} cr.</span>
                                                <span>{{ $item['materia']['total_horas'] }}h</span>
                                            </div>
                                        </div>
                                        @if($item['puede_darse_baja'])
                                            <button
                                                wire:click="darDeBaja({{ $item['id'] }})"
                                                wire:confirm="¿Dar de baja esta materia?"
                                                class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors flex-shrink-0"
                                                title="Dar de baja">
                                                @svg('lucide-x-circle', 'w-4 h-4')
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-blue-50/50 rounded-2xl p-8 text-center">
                            @svg('lucide-inbox', 'w-12 h-12 text-blue-300 mx-auto mb-2')
                            <p class="text-sm font-medium text-blue-700">Sin materias inscritas</p>
                            <p class="text-xs text-blue-500 mt-1">Aún no te has inscrito en ninguna materia este periodo</p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>