<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
     x-data="{ tab: 'todo' }">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mensajes</h1>
            <p class="text-sm text-blue-400 mt-0.5">Conversaciones con tus alumnos</p>
        </div>
        <button wire:click="$set('modalAbierto', true)"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm
                       font-medium rounded-xl hover:bg-blue-700 transition">
            @svg('lucide-plus', 'w-4 h-4')
            Nuevo mensaje
        </button>
    </div>

    {{-- Banner alumnos críticos --}}
    @if($alumnosCriticos->count() > 0)
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                @svg('lucide-alert-circle', 'w-4 h-4 text-red-500')
                <p class="text-sm font-semibold text-red-700">Alumnos que requieren atención inmediata</p>
                <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">
                    {{ $alumnosCriticos->count() }} con promedio &lt; 75 pts
                </span>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($alumnosCriticos as $ac)
                    @php $acNombre = $ac->usuario?->name ?? $ac->matricula; @endphp
                    <div class="flex items-center gap-2 bg-white border border-red-100 rounded-xl px-3 py-2">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-700 font-bold text-xs">{{ strtoupper(substr($acNombre, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-blue-900">
                                {{ explode(' ', $acNombre)[0] }} {{ explode(' ', $acNombre)[1] ?? '' }}
                            </p>
                            <p class="text-xs text-red-500">Prom. {{ number_format((float)$ac->promedio_general, 1) }} pts</p>
                        </div>
                        @if($ac->usuario_id)
                            <div class="flex gap-1 ml-1">
                                <button wire:click="abrirModalParaAlumno({{ $ac->usuario_id }}, 'bajo_rendimiento')"
                                        class="p-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition" title="Bajo rendimiento">
                                    @svg('lucide-trending-down', 'w-3.5 h-3.5 text-red-400')
                                </button>
                                <button wire:click="abrirModalParaAlumno({{ $ac->usuario_id }}, 'invitacion_asesoria')"
                                        class="p-1.5 bg-teal-50 rounded-lg hover:bg-teal-100 transition" title="Agendar asesoría">
                                    @svg('lucide-calendar', 'w-3.5 h-3.5 text-teal-400')
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Panel principal --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" style="min-height:520px">

        {{-- ── Columna izquierda: lista de conversaciones ── --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">

            {{-- Buscador (decorativo por ahora, datos ya filtrados server-side) --}}
            <div class="p-3 border-b border-blue-50">
                <div class="relative">
                    <div class="absolute inset-y-0 left-2.5 flex items-center pointer-events-none">
                        @svg('lucide-search', 'w-3.5 h-3.5 text-blue-300')
                    </div>
                    <input type="text" placeholder="Buscar..." disabled
                           class="w-full pl-8 pr-3 py-2 border border-blue-100 rounded-xl
                                  text-xs text-blue-900 bg-white placeholder-blue-200 opacity-60 cursor-not-allowed">
                </div>
            </div>

            {{-- Pestañas (Alpine, sin round-trip) --}}
            <div class="flex border-b border-blue-50 px-2 pt-2 gap-1">
                <button @click="tab = 'todo'"
                        :class="tab === 'todo' ? 'bg-blue-600 text-white' : 'text-blue-500 hover:bg-blue-50'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition flex items-center gap-1">
                    Todo
                    @if($todo->count() > 0)
                        <span class="text-[10px] px-1.5 rounded-full"
                              :class="tab === 'todo' ? 'bg-blue-400 text-white' : 'bg-blue-100 text-blue-500'">
                            {{ $todo->count() }}
                        </span>
                    @endif
                </button>
                <button @click="tab = 'porResponder'"
                        :class="tab === 'porResponder' ? 'bg-blue-600 text-white' : 'text-blue-500 hover:bg-blue-50'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition flex items-center gap-1">
                    Por responder
                    @if($porResponder->count() > 0)
                        <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 rounded-full">
                            {{ $porResponder->count() }}
                        </span>
                    @endif
                </button>
                <button @click="tab = 'urgentes'"
                        :class="tab === 'urgentes' ? 'bg-blue-600 text-white' : 'text-blue-500 hover:bg-blue-50'"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition flex items-center gap-1">
                    Urgentes
                    @if($urgentes->count() > 0)
                        <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 rounded-full">
                            {{ $urgentes->count() }}
                        </span>
                    @endif
                </button>
            </div>

            {{-- Listas (Alpine muestra/oculta, Livewire las renderizó) --}}
            <div class="flex-1 overflow-y-auto">

                {{-- Todo --}}
                <div x-show="tab === 'todo'" class="divide-y divide-blue-50">
                    @forelse($todo as $conv)
                        @include('livewire.tutor._conv-item', ['conv' => $conv])
                    @empty
                        @include('livewire.tutor._conv-empty')
                    @endforelse
                </div>

                {{-- Por responder --}}
                <div x-show="tab === 'porResponder'" class="divide-y divide-blue-50" style="display:none">
                    @forelse($porResponder as $conv)
                        @include('livewire.tutor._conv-item', ['conv' => $conv])
                    @empty
                        <div class="flex flex-col items-center justify-center py-12">
                            @svg('lucide-check-circle', 'w-8 h-8 text-green-200 mb-2')
                            <p class="text-xs text-slate-400">Sin conversaciones pendientes</p>
                        </div>
                    @endforelse
                </div>

                {{-- Urgentes --}}
                <div x-show="tab === 'urgentes'" class="divide-y divide-blue-50" style="display:none">
                    @forelse($urgentes as $conv)
                        @include('livewire.tutor._conv-item', ['conv' => $conv])
                    @empty
                        <div class="flex flex-col items-center justify-center py-12">
                            @svg('lucide-inbox', 'w-8 h-8 text-blue-100 mb-2')
                            <p class="text-xs text-slate-400">Sin mensajes urgentes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Columna derecha: conversación activa ── --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">

            @if(!$conversacionActiva)
                <div class="flex flex-col items-center justify-center h-full py-20">
                    @svg('lucide-message-circle', 'w-12 h-12 text-blue-100 mb-3')
                    <p class="text-sm text-slate-400">Selecciona una conversación</p>
                    <p class="text-xs text-slate-300 mt-1">o envía un nuevo mensaje a un alumno</p>
                </div>
            @else
                {{-- Cabecera --}}
                <div class="p-4 border-b border-blue-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-700 font-bold text-sm">
                                {{ strtoupper(substr($conversacionActiva->otro_nombre, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-900">{{ $conversacionActiva->otro_nombre }}</p>
                            <p class="text-xs text-blue-400">{{ $conversacionActiva->asunto }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($conversacionActiva->prioridad === 'urgente')
                            <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">Urgente</span>
                        @endif
                        <button wire:click="$set('modalAbierto', true)"
                                class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200
                                       rounded-xl text-blue-600 text-xs font-medium hover:bg-blue-50 transition">
                            @svg('lucide-plus', 'w-3.5 h-3.5')
                            Nuevo
                        </button>
                    </div>
                </div>

                {{-- Hilo de mensajes --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">

                    {{-- Mensaje raíz --}}
                    @if($conversacionActiva->remitente_id === $userId)
                        <div class="flex justify-end">
                            <div class="max-w-sm lg:max-w-md">
                                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3">
                                    <p class="text-[10px] font-semibold text-blue-200 mb-1 uppercase tracking-wide">
                                        {{ $conversacionActiva->asunto }}
                                    </p>
                                    <p class="text-sm leading-relaxed">{{ $conversacionActiva->contenido }}</p>
                                </div>
                                <p class="text-[10px] text-slate-400 text-right mt-1">
                                    Tú · {{ $conversacionActiva->created_at->locale('es')->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start">
                            <div class="max-w-sm lg:max-w-md">
                                <div class="bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-3">
                                    <p class="text-[10px] font-semibold text-blue-600 mb-1 uppercase tracking-wide">
                                        {{ $conversacionActiva->asunto }}
                                    </p>
                                    <p class="text-sm text-slate-700 leading-relaxed">{{ $conversacionActiva->contenido }}</p>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    {{ $conversacionActiva->remitente?->name ?? '—' }}
                                    · {{ $conversacionActiva->created_at->locale('es')->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Respuestas --}}
                    @foreach($conversacionActiva->respuestas as $resp)
                        @if($resp->remitente_id === $userId)
                            <div class="flex justify-end">
                                <div class="max-w-sm lg:max-w-md">
                                    <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3">
                                        <p class="text-sm leading-relaxed">{{ $resp->contenido }}</p>
                                    </div>
                                    <p class="text-[10px] text-slate-400 text-right mt-1">
                                        Tú · {{ $resp->created_at->locale('es')->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start gap-2">
                                <div class="w-7 h-7 bg-slate-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-slate-600 font-bold text-[10px]">
                                        {{ strtoupper(substr($resp->remitente?->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="max-w-sm lg:max-w-md">
                                    <div class="bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-3">
                                        <p class="text-sm text-slate-700 leading-relaxed">{{ $resp->contenido }}</p>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        {{ $resp->remitente?->name ?? '—' }}
                                        · {{ $resp->created_at->locale('es')->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Caja de respuesta --}}
                <div class="p-3 border-t border-blue-50">
                    <div class="flex gap-2">
                        <input wire:model="textoRespuesta"
                               wire:keydown.enter="responder"
                               type="text"
                               placeholder="Escribe tu respuesta..."
                               class="flex-1 px-4 py-2.5 border border-blue-100 rounded-xl text-sm
                                      text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-200
                                      placeholder-blue-200">
                        <button wire:click="responder"
                                wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium
                                       hover:bg-blue-700 transition flex items-center gap-1.5
                                       disabled:opacity-50">
                            <span wire:loading.remove wire:target="responder">
                                @svg('lucide-send', 'w-4 h-4')
                            </span>
                            <span wire:loading wire:target="responder">
                                @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── MODAL NUEVO MENSAJE ── --}}
    @if($modalAbierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             x-data x-init="$el.querySelector('[data-modal]').focus()">
            <div data-modal tabindex="-1"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto outline-none">

                <div class="flex items-start justify-between p-5 border-b border-blue-50">
                    <div>
                        <h3 class="font-bold text-blue-900">Nuevo mensaje</h3>
                        <p class="text-xs text-blue-400 mt-0.5">Envía un mensaje a uno o varios alumnos</p>
                    </div>
                    <button wire:click="$set('modalAbierto', false)"
                            class="text-slate-400 hover:text-slate-600">
                        @svg('lucide-x', 'w-5 h-5')
                    </button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Plantillas rápidas --}}
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-1.5 text-sm text-blue-600 font-medium hover:underline">
                            @svg('lucide-sparkles', 'w-4 h-4')
                            <span x-text="open ? 'Ocultar plantillas ▲' : 'Usar plantilla ▼'">Usar plantilla ▼</span>
                        </button>
                        <div x-show="open" x-cloak class="grid grid-cols-2 gap-2 mt-3">
                            @foreach([
                                ['bajo_rendimiento',    'lucide-trending-down',    'Bajo rendimiento'],
                                ['plan_recuperacion',   'lucide-book-open',        'Plan de recuperación'],
                                ['caida_calificacion',  'lucide-alert-triangle',   'Caída de calificación'],
                                ['invitacion_asesoria', 'lucide-calendar',         'Invitación a asesoría'],
                                ['recordatorio_grupal', 'lucide-users',            'Recordatorio grupal'],
                                ['felicitacion',        'lucide-star',             'Felicitación'],
                            ] as [$clave, $icono, $label])
                                <button wire:click="usarPlantilla('{{ $clave }}')"
                                        class="flex items-center gap-2 px-3 py-2 border rounded-xl text-xs text-slate-700 transition text-left
                                               {{ $plantillaSeleccionada === $clave ? 'border-blue-400 bg-blue-50' : 'border-slate-200 hover:border-blue-300' }}">
                                    @svg($icono, 'w-3.5 h-3.5 text-blue-400 flex-shrink-0')
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Destinatario --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Destinatario</p>
                        <div class="flex gap-2 mb-3">
                            <button wire:click="$set('tipoDestinatario', 'alumno')"
                                    class="flex items-center gap-1.5 px-4 py-2 border rounded-xl text-xs font-medium transition
                                           {{ $tipoDestinatario === 'alumno' ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:border-blue-300' }}">
                                @svg('lucide-user', 'w-3.5 h-3.5')
                                Alumno individual
                            </button>
                            <button wire:click="$set('tipoDestinatario', 'grupo')"
                                    class="flex items-center gap-1.5 px-4 py-2 border rounded-xl text-xs font-medium transition
                                           {{ $tipoDestinatario === 'grupo' ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:border-blue-300' }}">
                                @svg('lucide-users', 'w-3.5 h-3.5')
                                Grupo completo
                            </button>
                        </div>

                        @if($tipoDestinatario === 'alumno')
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                @forelse($alumnos->filter(fn($a) => $a->usuario_id) as $al)
                                    <button wire:click="$set('destinatarioId', {{ $al->usuario_id }})"
                                            class="w-full flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition text-left
                                                   {{ $destinatarioId === $al->usuario_id ? 'border-blue-400 bg-blue-50' : 'border-slate-100 hover:border-blue-200' }}">
                                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-700 font-bold text-xs">
                                                {{ strtoupper(substr($al->usuario?->name ?? 'A', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-blue-900 truncate">
                                                {{ $al->usuario?->name ?? 'Sin nombre' }}
                                            </p>
                                            <p class="text-xs text-blue-400">
                                                {{ $al->carrera?->clave ?? '—' }}
                                                @if($alertasPorAlumno[$al->id] ?? 0)
                                                    · <span class="text-red-500">{{ $alertasPorAlumno[$al->id] }} alertas</span>
                                                @endif
                                            </p>
                                        </div>
                                        @if($destinatarioId === $al->usuario_id)
                                            @svg('lucide-check-circle', 'w-4 h-4 text-blue-500 flex-shrink-0')
                                        @endif
                                    </button>
                                @empty
                                    <p class="text-xs text-slate-400 text-center py-4">Sin alumnos con cuenta activa.</p>
                                @endforelse
                            </div>
                        @else
                            <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <p class="text-xs text-blue-600 font-medium">
                                    El mensaje se enviará a los
                                    <strong>{{ $alumnos->filter(fn($a) => $a->usuario_id)->count() }} alumnos</strong>
                                    de tu grupo con cuenta activa.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Prioridad --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Prioridad</p>
                        <div class="flex gap-2">
                            @foreach(['urgente' => 'Urgente', 'normal' => 'Normal', 'informativo' => 'Informativo'] as $val => $lbl)
                                <button wire:click="$set('prioridad', '{{ $val }}')"
                                        class="px-3 py-2 border rounded-xl text-xs transition
                                               {{ $prioridad === $val ? 'border-blue-400 bg-blue-50 font-semibold text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Asunto --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-1.5">Asunto</p>
                        <input wire:model="asunto" type="text" placeholder="Escribe el asunto..."
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm
                                      text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-200 placeholder-slate-300">
                        @error('asunto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Contenido --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-1.5">Mensaje</p>
                        <textarea wire:model="contenido" rows="4" placeholder="Escribe tu mensaje aquí..."
                                  class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm
                                         text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-200
                                         placeholder-slate-300 resize-none"></textarea>
                        @error('contenido') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-blue-50 flex items-center justify-between">
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        @svg('lucide-paperclip', 'w-3.5 h-3.5')
                        Se guardará en el historial
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="$set('modalAbierto', false)"
                                class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600
                                       text-sm font-medium hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button wire:click="enviar"
                                wire:loading.attr="disabled"
                                @disabled(!$asunto || !$contenido || ($tipoDestinatario === 'alumno' && !$destinatarioId))
                                class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white
                                       rounded-xl text-sm font-medium hover:bg-blue-700 transition
                                       disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="enviar">
                                @svg('lucide-send', 'w-4 h-4')
                                Enviar
                            </span>
                            <span wire:loading wire:target="enviar">
                                @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                                Enviando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
