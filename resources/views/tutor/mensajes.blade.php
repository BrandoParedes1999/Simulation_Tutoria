<x-app-layout>
    @php
        $tutor   = auth()->user()->tutor;
        $alumnos = $tutor->alumnosAsignados->load('usuario', 'carrera');
        $userId  = auth()->id();

        // Alertas por alumno para mostrar badges
        $alertasPorAlumno = \App\Models\Alerta::whereIn('alumno_id', $alumnos->pluck('id'))
            ->where('atendida', false)
            ->selectRaw('alumno_id, count(*) as total')
            ->groupBy('alumno_id')
            ->pluck('total', 'alumno_id');

        // Alumnos críticos (para el banner de atención inmediata)
        $alumnosCriticos = $alumnos->filter(
            fn($a) => (float)$a->promedio_general > 0 && (float)$a->promedio_general < 7.5
        )->take(5);

        // Conversaciones del tutor (mensajes raíz, sin padre)
        $conversaciones = \App\Models\Mensaje::where(function($q) use ($userId) {
                $q->where('remitente_id', $userId)
                  ->orWhere('destinatario_id', $userId);
            })
            ->whereNull('mensaje_padre_id')
            ->with(['remitente', 'destinatario', 'respuestas'])
            ->orderByDesc('created_at')
            ->get();

        // Mensajes urgentes no leídos
        $urgentes = $conversaciones->filter(
            fn($m) => $m->prioridad === 'urgente' && !$m->leido_en && $m->destinatario_id === $userId
        )->count();

        // Recibidos no leídos
        $noLeidos = $conversaciones->filter(
            fn($m) => $m->destinatario_id === $userId && !$m->leido_en
        )->count();

        // Plantillas predefinidas
        $plantillas = [
            ['clave' => 'bajo_rendimiento',    'label' => 'Bajo rendimiento',    'icono' => 'lucide-trending-down'],
            ['clave' => 'plan_recuperacion',   'label' => 'Plan de recuperación', 'icono' => 'lucide-book-open'],
            ['clave' => 'caida_calificacion',  'label' => 'Caída de calificación','icono' => 'lucide-alert-triangle'],
            ['clave' => 'invitacion_asesoria', 'label' => 'Invitación a asesoría','icono' => 'lucide-calendar'],
            ['clave' => 'recordatorio_grupal', 'label' => 'Recordatorio grupal',  'icono' => 'lucide-users'],
            ['clave' => 'felicitacion',        'label' => 'Felicitación',          'icono' => 'lucide-star'],
        ];

        $textoPlantillas = [
            'bajo_rendimiento'    => 'Estimado alumno, he notado que tu promedio actual está por debajo del mínimo requerido. Es importante que nos reunamos para diseñar un plan de mejora.',
            'plan_recuperacion'   => 'Te invito a revisar juntos las materias en las que presentas dificultades y elaborar un plan de recuperación académica.',
            'caida_calificacion'  => 'He detectado una caída en tus calificaciones recientes. Me gustaría hablar contigo para identificar las causas y buscar soluciones.',
            'invitacion_asesoria' => 'Te invito a una sesión de asesoría para revisar tu situación académica. Por favor confirma tu disponibilidad.',
            'recordatorio_grupal' => 'Estimado grupo, les recuerdo que estoy disponible para atender cualquier consulta académica. No duden en contactarme.',
            'felicitacion'        => '¡Felicitaciones por tu excelente desempeño académico! Sigue así, es un orgullo tenerte como alumno.',
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
         x-data="{
         
            modalAbierto: false,
            tipoDestinatario: 'alumno',
            mostrarPlantillas: false,
            plantillaSeleccionada: '',
            destinatarioId: null,
            prioridad: 'normal',
            asunto: '',
            contenido: '',
            conversacionActiva: null,
            pestana: 'recibidos',
            busqueda: '',
            textoRespuesta: '',
            csrfToken: '{{ csrf_token() }}',
            urlEnviar: '{{ url('tutor/mensajes/enviar') }}',
            urlResponder: '{{ url('tutor/mensajes') }}',
            urlLeer: '{{ url('tutor/mensajes') }}',

            textoPlantillas: {{ json_encode($textoPlantillas) }},

            alumnos: {{ $alumnos->map(fn($a) => [
                'usuario_id' => $a->usuario_id,
                'nombre'     => $a->usuario->name,
                'carrera'    => $a->carrera->nombre ?? $a->carrera->clave ?? '',
                'promedio'   => number_format((float)$a->promedio_general, 1),
                'alertas'    => $alertasPorAlumno[$a->id] ?? 0,
            ])->values()->toJson() }},

            conversaciones: {{ $conversaciones->map(fn($m) => [
                'id'          => $m->id,
                'remitente'   => $m->remitente->name ?? '—',
                'destinatario'=> $m->destinatario->name ?? '—',
                'remitente_id'=> $m->remitente_id,
                'asunto'      => $m->asunto,
                'contenido'   => $m->contenido,
                'prioridad'   => $m->prioridad,
                'leido'       => !is_null($m->leido_en),
                'fecha'       => $m->created_at->format('d M'),
                'respuestas'  => $m->respuestas->map(fn($r) => [
                    'id'          => $r->id,
                    'remitente'   => $r->remitente->name ?? '—',
                    'remitente_id'=> $r->remitente_id,
                    'contenido'   => $r->contenido,
                    'fecha'       => $r->created_at->format('d M, g:i a'),
                ])->values()->toArray(),
            ])->values()->toJson() }},

            userId: {{ $userId }},

            get conversacionesFiltradas() {
                return this.conversaciones.filter(m => {
                    const q = this.busqueda.toLowerCase();
                    const matchQ = !q || m.asunto.toLowerCase().includes(q) ||
                        m.remitente.toLowerCase().includes(q);
                    if (this.pestana === 'recibidos')
                        return matchQ && m.remitente_id !== this.userId;
                    if (this.pestana === 'enviados')
                        return matchQ && m.remitente_id === this.userId;
                    if (this.pestana === 'urgentes')
                        return matchQ && m.prioridad === 'urgente';
                    return matchQ;
                });
            },

            abrirConversacion(conv) {
                this.conversacionActiva = conv;
                if (!conv.leido && conv.remitente_id !== this.userId) {
                    fetch(this.urlLeer + '/' + conv.id + '/leer', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken }
                    });
                    conv.leido = true;
                }
            },

            usarPlantilla(clave, label) {
                this.plantillaSeleccionada = clave;
                this.contenido = this.textoPlantillas[clave] || '';
                if (!this.asunto) this.asunto = label;
            },

            enviarMensaje() {
                if (!this.asunto || !this.contenido) return;
                const body = {
                    tipo:      this.tipoDestinatario,
                    asunto:    this.asunto,
                    contenido: this.contenido,
                    prioridad: this.prioridad,
                    plantilla: this.plantillaSeleccionada || null,
                };
                if (this.tipoDestinatario === 'alumno') {
                    body.destinatario_id = this.destinatarioId;
                }
                fetch(this.urlEnviar, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        this.modalAbierto    = false;
                        this.asunto          = '';
                        this.contenido       = '';
                        this.prioridad       = 'normal';
                        this.destinatarioId  = null;
                        this.plantillaSeleccionada = '';
                        window.location.reload();
                    }
                })
                .catch(() => alert('Error al enviar el mensaje'));
            },

            responder() {
                if (!this.textoRespuesta || !this.conversacionActiva) return;
                fetch(this.urlResponder + '/' + this.conversacionActiva.id + '/responder', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ contenido: this.textoRespuesta })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        this.conversacionActiva.respuestas.push({
                            id:           Date.now(),
                            remitente:    '{{ auth()->user()->name }}',
                            remitente_id: this.userId,
                            contenido:    this.textoRespuesta,
                            fecha:        'Ahora',
                        });
                        this.textoRespuesta = '';
                    }
                })
                .catch(() => alert('Error al enviar la respuesta'));
            }
         }">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mensajes y Notificaciones</h1>
                <p class="text-sm text-blue-400 mt-0.5">Comunícate con tus alumnos y envía alertas de seguimiento</p>
            </div>
            <button @click="modalAbierto = true"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm
                           font-medium rounded-xl hover:bg-blue-700 transition">
                @svg('lucide-plus', 'w-4 h-4')
                Nueva notificación
            </button>
        </div>

        {{-- Banner alumnos críticos --}}
        @if($alumnosCriticos->count() > 0)
            <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    @svg('lucide-alert-circle', 'w-4 h-4 text-red-500')
                    <p class="text-sm font-semibold text-red-700">
                        Alumnos que requieren atención inmediata
                    </p>
                    <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">
                        {{ $alumnosCriticos->count() }} alumnos
                    </span>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach($alumnosCriticos as $ac)
                        <div class="flex items-center gap-2 bg-white border border-red-100
                                    rounded-xl px-3 py-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center
                                        justify-center flex-shrink-0">
                                <span class="text-blue-700 font-bold text-xs">
                                    {{ strtoupper(substr($ac->usuario->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-blue-900">
                                    {{ explode(' ', $ac->usuario->name)[0] }}
                                    {{ explode(' ', $ac->usuario->name)[1] ?? '' }}
                                </p>
                                <p class="text-xs text-red-500">
                                    Prom. {{ number_format((float)$ac->promedio_general, 1) }}
                                </p>
                            </div>
                            <div class="flex gap-1 ml-1">
                                <button @click="
                                    modalAbierto = true;
                                    tipoDestinatario = 'alumno';
                                    destinatarioId = {{ $ac->usuario_id }};
                                    usarPlantilla('bajo_rendimiento', 'Bajo rendimiento')
                                "
                                class="p-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition"
                                title="Bajo rendimiento">
                                    @svg('lucide-trending-down', 'w-3.5 h-3.5 text-red-400')
                                </button>
                                <button @click="
                                    modalAbierto = true;
                                    tipoDestinatario = 'alumno';
                                    destinatarioId = {{ $ac->usuario_id }};
                                    usarPlantilla('plan_recuperacion', 'Plan de recuperación')
                                "
                                class="p-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                                title="Plan de recuperación">
                                    @svg('lucide-book-open', 'w-3.5 h-3.5 text-blue-400')
                                </button>
                                <button @click="
                                    modalAbierto = true;
                                    tipoDestinatario = 'alumno';
                                    destinatarioId = {{ $ac->usuario_id }};
                                    usarPlantilla('invitacion_asesoria', 'Invitación a asesoría')
                                "
                                class="p-1.5 bg-teal-50 rounded-lg hover:bg-teal-100 transition"
                                title="Agendar asesoría">
                                    @svg('lucide-calendar', 'w-3.5 h-3.5 text-teal-400')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Panel principal --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" style="height:520px">

            {{-- Lista de conversaciones --}}
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">

                {{-- Buscador --}}
                <div class="p-3 border-b border-blue-50">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-2.5 flex items-center pointer-events-none">
                            @svg('lucide-search', 'w-3.5 h-3.5 text-blue-300')
                        </div>
                        <input x-model="busqueda"
                               type="text"
                               placeholder="Buscar conversación..."
                               class="w-full pl-8 pr-3 py-2 border border-blue-100 rounded-xl
                                      text-xs text-blue-900 bg-white focus:outline-none
                                      focus:ring-2 focus:ring-blue-200 placeholder-blue-200">
                    </div>
                </div>

                {{-- Pestañas --}}
                <div class="flex border-b border-blue-50 px-3 pt-2 gap-1">
                    <button @click="pestana = 'recibidos'"
                            :class="pestana === 'recibidos'
                                ? 'bg-blue-600 text-white'
                                : 'text-blue-500 hover:bg-blue-50'"
                            class="px-3 py-1 text-xs font-medium rounded-lg transition flex items-center gap-1">
                        Recibidos
                        @if($noLeidos > 0)
                            <span class="bg-white text-blue-600 text-xs font-bold
                                         px-1.5 rounded-full">{{ $noLeidos }}</span>
                        @endif
                    </button>
                    <button @click="pestana = 'enviados'"
                            :class="pestana === 'enviados'
                                ? 'bg-blue-600 text-white'
                                : 'text-blue-500 hover:bg-blue-50'"
                            class="px-3 py-1 text-xs font-medium rounded-lg transition">
                        Enviados
                    </button>
                    <button @click="pestana = 'urgentes'"
                            :class="pestana === 'urgentes'
                                ? 'bg-blue-600 text-white'
                                : 'text-blue-500 hover:bg-blue-50'"
                            class="px-3 py-1 text-xs font-medium rounded-lg transition flex items-center gap-1">
                        Urgentes
                        @if($urgentes > 0)
                            <span class="bg-red-500 text-white text-xs font-bold
                                         px-1.5 rounded-full">{{ $urgentes }}</span>
                        @endif
                    </button>
                </div>

                {{-- Lista --}}
                <div class="flex-1 overflow-y-auto divide-y divide-blue-50">
                    <template x-for="conv in conversacionesFiltradas" :key="conv.id">
                        <div @click="abrirConversacion(conv)"
                             :class="conversacionActiva && conversacionActiva.id === conv.id
                                 ? 'bg-blue-50 border-l-2 border-blue-500'
                                 : 'hover:bg-blue-50/50'"
                             class="p-3 cursor-pointer transition-colors">
                            <div class="flex items-start gap-2.5">
                                <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center
                                            justify-center flex-shrink-0">
                                    <span class="text-blue-700 font-bold text-xs"
                                          x-text="(conv.remitente_id !== userId
                                              ? conv.remitente
                                              : conv.destinatario).charAt(0).toUpperCase()">
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold text-blue-900 truncate"
                                           x-text="conv.remitente_id !== userId
                                               ? conv.remitente
                                               : conv.destinatario"></p>
                                        <span class="text-xs text-slate-400 flex-shrink-0 ml-1"
                                              x-text="conv.fecha"></span>
                                    </div>
                                    <p class="text-xs text-slate-600 truncate mt-0.5"
                                       x-text="conv.asunto"></p>
                                    <div class="flex items-center gap-1 mt-1">
                                        <template x-if="conv.prioridad === 'urgente'">
                                            <span class="text-xs px-1.5 py-0.5 bg-red-100
                                                         text-red-600 rounded-full font-medium">
                                                Urgente
                                            </span>
                                        </template>
                                        <span class="text-xs text-slate-400"
                                              x-text="conv.respuestas.length + ' mensajes'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="conversacionesFiltradas.length === 0"
                         class="flex flex-col items-center justify-center py-10">
                        @svg('lucide-inbox', 'w-8 h-8 text-blue-100 mb-2')
                        <p class="text-xs text-slate-400">Sin conversaciones</p>
                    </div>
                </div>
            </div>

            {{-- Panel conversación activa --}}
            <div class="md:col-span-2 bg-white rounded-2xl border border-blue-100 shadow-sm
                        flex flex-col overflow-hidden">

                {{-- Sin conversación seleccionada --}}
                <template x-if="!conversacionActiva">
                    <div class="flex flex-col items-center justify-center h-full">
                        @svg('lucide-message-circle', 'w-12 h-12 text-blue-100 mb-3')
                        <p class="text-sm text-slate-400">Selecciona una conversación</p>
                        <p class="text-xs text-slate-300 mt-1">o crea una nueva notificación</p>
                    </div>
                </template>

                {{-- Conversación activa --}}
                <template x-if="conversacionActiva">
                    <div class="flex flex-col h-full">

                        {{-- Header conversación --}}
                        <div class="p-4 border-b border-blue-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center
                                            justify-center flex-shrink-0">
                                    <span class="text-blue-700 font-bold text-sm"
                                          x-text="(conversacionActiva.remitente_id !== userId
                                              ? conversacionActiva.remitente
                                              : conversacionActiva.destinatario)
                                              .charAt(0).toUpperCase()">
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-blue-900"
                                       x-text="conversacionActiva.remitente_id !== userId
                                           ? conversacionActiva.remitente
                                           : conversacionActiva.destinatario">
                                    </p>
                                    <p class="text-xs text-blue-400"
                                       x-text="conversacionActiva.asunto"></p>
                                </div>
                            </div>
                            <button @click="
                                modalAbierto = true;
                                tipoDestinatario = 'alumno';
                            "
                            class="flex items-center gap-1.5 px-3 py-1.5 border border-blue-200
                                   rounded-xl text-blue-600 text-xs font-medium hover:bg-blue-50 transition">
                                @svg('lucide-plus', 'w-3.5 h-3.5')
                                Nuevo mensaje
                            </button>
                        </div>

                        {{-- Mensajes del hilo --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">

                            {{-- Mensaje principal --}}
                            <template x-if="conversacionActiva.remitente_id === userId">
                                <div class="flex justify-end">
                                    <div class="max-w-xs lg:max-w-md">
                                        <div class="flex items-center justify-end gap-2 mb-1">
                                            <template x-if="conversacionActiva.prioridad === 'urgente'">
                                                <span class="text-xs px-2 py-0.5 bg-red-100
                                                             text-red-600 rounded-full font-medium">
                                                    Urgente
                                                </span>
                                            </template>
                                        </div>
                                        <div class="bg-blue-600 text-white rounded-2xl
                                                    rounded-tr-sm px-4 py-3">
                                            <p class="text-xs font-semibold mb-1"
                                               x-text="conversacionActiva.asunto"></p>
                                            <p class="text-sm leading-relaxed"
                                               x-text="conversacionActiva.contenido"></p>
                                        </div>
                                        <p class="text-xs text-slate-400 text-right mt-1">
                                            <span x-text="conversacionActiva.fecha"></span>
                                            <span class="ml-1 text-blue-400">✓✓</span>
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="conversacionActiva.remitente_id !== userId">
                                <div class="flex justify-start">
                                    <div class="max-w-xs lg:max-w-md">
                                        <div class="bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-3">
                                            <p class="text-xs font-semibold text-blue-700 mb-1"
                                               x-text="conversacionActiva.asunto"></p>
                                            <p class="text-sm text-slate-700 leading-relaxed"
                                               x-text="conversacionActiva.contenido"></p>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1"
                                           x-text="conversacionActiva.fecha"></p>
                                    </div>
                                </div>
                            </template>

                            {{-- Respuestas --}}
                            <template x-for="resp in conversacionActiva.respuestas" :key="resp.id">
                                <div :class="resp.remitente_id === userId ? 'flex justify-end' : 'flex justify-start'">
                                    <div class="max-w-xs lg:max-w-md">
                                        <div :class="resp.remitente_id === userId
                                            ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-3'
                                            : 'bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-3'">
                                            <p :class="resp.remitente_id === userId
                                                ? 'text-sm leading-relaxed'
                                                : 'text-sm text-slate-700 leading-relaxed'"
                                               x-text="resp.contenido"></p>
                                        </div>
                                        <p :class="resp.remitente_id === userId
                                            ? 'text-xs text-slate-400 text-right mt-1'
                                            : 'text-xs text-slate-400 mt-1'"
                                           x-text="resp.fecha"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Caja de respuesta --}}
                        <div class="p-3 border-t border-blue-50">
                            <div class="flex gap-2">
                                <input x-model="textoRespuesta"
                                       @keydown.enter.prevent="responder()"
                                       type="text"
                                       placeholder="Escribe tu respuesta..."
                                       class="flex-1 px-4 py-2.5 border border-blue-100 rounded-xl
                                              text-sm text-blue-900 focus:outline-none
                                              focus:ring-2 focus:ring-blue-200 placeholder-blue-200">
                                <button @click="responder()"
                                        class="px-4 py-2.5 bg-blue-600 text-white rounded-xl
                                               text-sm font-medium hover:bg-blue-700 transition
                                               flex items-center gap-1.5">
                                    @svg('lucide-send', 'w-4 h-4')
                                </button>
                            </div>
                        </div>

                    </div>
                </template>

            </div>
        </div>

        {{-- ===== MODAL NUEVA NOTIFICACIÓN ===== --}}
        <div x-show="modalAbierto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             style="display:none">

            <div @click.outside="modalAbierto = false"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4
                        max-h-screen overflow-y-auto">

                {{-- Header modal --}}
                <div class="flex items-start justify-between p-5 border-b border-blue-50">
                    <div>
                        <h3 class="font-bold text-blue-900">Nueva Notificación</h3>
                        <p class="text-xs text-blue-400 mt-0.5">
                            Envía un mensaje de seguimiento a uno o varios alumnos
                        </p>
                    </div>
                    <button @click="modalAbierto = false"
                            class="text-slate-400 hover:text-slate-600 transition">
                        @svg('lucide-x', 'w-5 h-5')
                    </button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Plantillas --}}
                    <div>
                        <button @click="mostrarPlantillas = !mostrarPlantillas"
                                class="flex items-center gap-1.5 text-sm text-blue-600
                                       font-medium hover:underline">
                            @svg('lucide-sparkles', 'w-4 h-4')
                            <span x-text="mostrarPlantillas ? 'Ocultar plantillas ▲' : 'Mostrar plantillas ▼'">
                                Mostrar plantillas ▼
                            </span>
                        </button>

                        <div x-show="mostrarPlantillas" class="grid grid-cols-2 gap-2 mt-3">
                            @foreach($plantillas as $p)
                                <button @click="usarPlantilla('{{ $p['clave'] }}', '{{ $p['label'] }}')"
                                        :class="plantillaSeleccionada === '{{ $p['clave'] }}'
                                            ? 'border-blue-400 bg-blue-50'
                                            : 'border-slate-200 hover:border-blue-300'"
                                        class="flex items-center gap-2 px-3 py-2 border
                                               rounded-xl text-xs text-slate-700 transition text-left">
                                    @svg($p['icono'], 'w-3.5 h-3.5 text-blue-400 flex-shrink-0')
                                    {{ $p['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Destinatario --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Destinatario</p>
                        <div class="flex gap-2 mb-3">
                            <button @click="tipoDestinatario = 'alumno'"
                                    :class="tipoDestinatario === 'alumno'
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'border-slate-200 text-slate-600 hover:border-blue-300'"
                                    class="flex items-center gap-1.5 px-4 py-2 border
                                           rounded-xl text-xs font-medium transition">
                                @svg('lucide-user', 'w-3.5 h-3.5')
                                Alumno individual
                            </button>
                            <button @click="tipoDestinatario = 'grupo'"
                                    :class="tipoDestinatario === 'grupo'
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'border-slate-200 text-slate-600 hover:border-blue-300'"
                                    class="flex items-center gap-1.5 px-4 py-2 border
                                           rounded-xl text-xs font-medium transition">
                                @svg('lucide-users', 'w-3.5 h-3.5')
                                Grupo completo
                            </button>
                        </div>

                        {{-- Lista alumnos --}}
                        <template x-if="tipoDestinatario === 'alumno'">
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="a in alumnos" :key="a.usuario_id">
                                    <div @click="destinatarioId = a.usuario_id"
                                         :class="destinatarioId === a.usuario_id
                                             ? 'border-blue-400 bg-blue-50'
                                             : 'border-slate-100 hover:border-blue-200'"
                                         class="flex items-center gap-3 p-3 border rounded-xl
                                                cursor-pointer transition">
                                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center
                                                    justify-center flex-shrink-0">
                                            <span class="text-blue-700 font-bold text-xs"
                                                  x-text="a.nombre.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-blue-900"
                                               x-text="a.nombre"></p>
                                            <p class="text-xs text-blue-400"
                                               x-text="a.carrera + ' · Prom. ' + a.promedio"></p>
                                        </div>
                                        <template x-if="a.alertas > 0">
                                            <span class="px-2 py-0.5 bg-red-100 text-red-600
                                                         text-xs font-bold rounded-full"
                                                  x-text="a.alertas + (a.alertas === 1 ? ' alerta' : ' alertas')">
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="tipoDestinatario === 'grupo'">
                            <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <p class="text-xs text-blue-600 font-medium">
                                    El mensaje se enviará a todos los
                                    <strong>{{ $alumnos->count() }} alumnos</strong> de tu grupo.
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- Prioridad --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Prioridad</p>
                        <div class="flex gap-2">
                            @foreach(['urgente' => ['color' => 'red', 'label' => 'Urgente'], 'normal' => ['color' => 'blue', 'label' => 'Normal'], 'informativo' => ['color' => 'slate', 'label' => 'Informativo']] as $val => $cfg)
                                <button @click="prioridad = '{{ $val }}'"
                                        :class="prioridad === '{{ $val }}'
                                            ? 'border-{{ $cfg['color'] }}-400 bg-{{ $cfg['color'] }}-50'
                                            : 'border-slate-200 hover:border-{{ $cfg['color'] }}-200'"
                                        class="flex items-center gap-1.5 px-3 py-2 border
                                               rounded-xl text-xs font-medium transition">
                                    <span class="w-2 h-2 rounded-full bg-{{ $cfg['color'] }}-400"></span>
                                    {{ $cfg['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Asunto --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-1.5">Asunto</p>
                        <input x-model="asunto"
                               type="text"
                               placeholder="Escribe el asunto del mensaje..."
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm
                                      text-blue-900 focus:outline-none focus:ring-2
                                      focus:ring-blue-200 placeholder-slate-300">
                    </div>

                    {{-- Mensaje --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-1.5">Mensaje</p>
                        <textarea x-model="contenido"
                                  rows="4"
                                  placeholder="Escribe tu mensaje aquí..."
                                  class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm
                                         text-blue-900 focus:outline-none focus:ring-2
                                         focus:ring-blue-200 placeholder-slate-300 resize-none">
                        </textarea>
                    </div>

                </div>

                {{-- Footer modal --}}
                <div class="px-5 py-4 border-t border-blue-50 flex items-center justify-between">
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        @svg('lucide-paperclip', 'w-3.5 h-3.5')
                        Se guardará en el historial de conversaciones
                    </p>
                    <div class="flex gap-2">
                        <button @click="modalAbierto = false"
                                class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600
                                       text-sm font-medium hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button @click="enviarMensaje()"
                                :disabled="!asunto || !contenido || (tipoDestinatario === 'alumno' && !destinatarioId)"
                                :class="(!asunto || !contenido || (tipoDestinatario === 'alumno' && !destinatarioId))
                                    ? 'opacity-50 cursor-not-allowed'
                                    : 'hover:bg-blue-700'"
                                class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white
                                       rounded-xl text-sm font-medium transition">
                            @svg('lucide-send', 'w-4 h-4')
                            Enviar mensaje
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>