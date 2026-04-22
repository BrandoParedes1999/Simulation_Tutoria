<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

    {{-- HEADER --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            @svg('lucide-mail', 'w-5 h-5 text-blue-700')
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Buzón de Mensajes</h1>
            <p class="text-xs text-blue-400">Comunicación con tu tutor</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" style="min-height:520px">

        {{-- ─── LISTA DE CONVERSACIONES ─── --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">

            {{-- Pestañas --}}
            <div class="flex border-b border-blue-50 p-2 gap-1">
                <button
                    wire:click="$set('pestana', 'recibidos')"
                    class="flex-1 flex items-center justify-center gap-1 px-2 py-1.5 text-xs font-medium rounded-xl transition-colors
                        {{ $pestana === 'recibidos' ? 'bg-blue-700 text-white' : 'text-blue-500 hover:bg-blue-50' }}">
                    Recibidos
                    @if($noLeidos > 0)
                        <span class="{{ $pestana === 'recibidos' ? 'bg-white text-blue-700' : 'bg-blue-600 text-white' }} text-[10px] font-bold px-1.5 rounded-full">
                            {{ $noLeidos }}
                        </span>
                    @endif
                </button>
                <button
                    wire:click="$set('pestana', 'enviados')"
                    class="flex-1 px-2 py-1.5 text-xs font-medium rounded-xl transition-colors
                        {{ $pestana === 'enviados' ? 'bg-blue-700 text-white' : 'text-blue-500 hover:bg-blue-50' }}">
                    Enviados
                </button>
                <button
                    wire:click="$set('pestana', 'urgentes')"
                    class="flex-1 px-2 py-1.5 text-xs font-medium rounded-xl transition-colors
                        {{ $pestana === 'urgentes' ? 'bg-red-600 text-white' : 'text-blue-500 hover:bg-blue-50' }}">
                    Urgentes
                </button>
            </div>

            {{-- Lista --}}
            <div class="flex-1 overflow-y-auto divide-y divide-blue-50">
                @forelse($conversaciones as $conv)
                    @php
                        $esMio     = $conv->remitente_id === $userId;
                        $contraparte = $esMio ? ($conv->destinatario->name ?? '—') : ($conv->remitente->name ?? '—');
                        $noLeido   = !$esMio && !$conv->leido_en;
                    @endphp
                    <button
                        wire:click="seleccionar({{ $conv->id }})"
                        class="w-full text-left p-3 transition-colors hover:bg-blue-50/50
                            {{ $conversacionActivaId === $conv->id ? 'bg-blue-50 border-l-2 border-blue-600' : '' }}">
                        <div class="flex items-start gap-2.5">
                            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-700 font-bold text-xs">{{ strtoupper(substr($contraparte, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-blue-900 truncate {{ $noLeido ? 'font-bold' : '' }}">
                                        {{ $contraparte }}
                                    </p>
                                    <span class="text-[10px] text-blue-400 flex-shrink-0 ml-1">
                                        {{ $conv->created_at->locale('es')->isoFormat('D MMM') }}
                                    </span>
                                </div>
                                <p class="text-xs text-blue-700 truncate mt-0.5 {{ $noLeido ? 'font-semibold' : '' }}">
                                    {{ $conv->asunto }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($conv->prioridad === 'urgente')
                                        <span class="text-[10px] px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full font-medium">Urgente</span>
                                    @endif
                                    @if($noLeido)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    @endif
                                    <span class="text-[10px] text-blue-400">{{ $conv->respuestas->count() }} respuestas</span>
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        @svg('lucide-inbox', 'w-8 h-8 text-blue-200 mb-2')
                        <p class="text-xs text-blue-400">Sin mensajes aquí</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ─── PANEL CONVERSACIÓN ACTIVA ─── --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">

            @if(!$conversacionActiva)
                <div class="flex flex-col items-center justify-center h-full py-20">
                    @svg('lucide-message-circle', 'w-12 h-12 text-blue-100 mb-3')
                    <p class="text-sm text-blue-400">Selecciona una conversación</p>
                </div>
            @else
                {{-- Header de la conversación --}}
                @php
                    $esMia       = $conversacionActiva->remitente_id === $userId;
                    $contraNombre = $esMia
                        ? ($conversacionActiva->destinatario->name ?? '—')
                        : ($conversacionActiva->remitente->name ?? '—');
                @endphp
                <div class="p-4 border-b border-blue-50 flex items-center gap-3 sticky top-0 bg-white z-10">
                    <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($contraNombre, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-blue-900">{{ $contraNombre }}</p>
                        <p class="text-xs text-blue-400 truncate">{{ $conversacionActiva->asunto }}</p>
                    </div>
                    @if($conversacionActiva->prioridad === 'urgente')
                        <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">Urgente</span>
                    @endif
                </div>

                {{-- Mensajes del hilo --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">

                    {{-- Mensaje raíz --}}
                    @php $esMioRaiz = $conversacionActiva->remitente_id === $userId; @endphp
                    <div class="{{ $esMioRaiz ? 'flex justify-end' : 'flex justify-start' }}">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="{{ $esMioRaiz ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm' : 'bg-slate-100 text-slate-800 rounded-2xl rounded-tl-sm' }} px-4 py-3">
                                <p class="text-xs {{ $esMioRaiz ? 'text-blue-200' : 'text-blue-500' }} font-semibold mb-1">
                                    {{ $conversacionActiva->asunto }}
                                </p>
                                <p class="text-sm leading-relaxed">{{ $conversacionActiva->contenido }}</p>
                            </div>
                            <p class="text-xs text-slate-400 {{ $esMioRaiz ? 'text-right' : '' }} mt-1">
                                {{ $conversacionActiva->created_at->locale('es')->isoFormat('D MMM, H:mm') }}
                            </p>
                        </div>
                    </div>

                    {{-- Respuestas --}}
                    @foreach($conversacionActiva->respuestas as $resp)
                        @php $esMioResp = $resp->remitente_id === $userId; @endphp
                        <div class="{{ $esMioResp ? 'flex justify-end' : 'flex justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="{{ $esMioResp ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm' : 'bg-slate-100 text-slate-800 rounded-2xl rounded-tl-sm' }} px-4 py-3">
                                    <p class="text-sm leading-relaxed">{{ $resp->contenido }}</p>
                                </div>
                                <p class="text-xs text-slate-400 {{ $esMioResp ? 'text-right' : '' }} mt-1">
                                    {{ $resp->created_at->locale('es')->isoFormat('D MMM, H:mm') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Caja de respuesta --}}
                <div class="p-3 border-t border-blue-50">
                    <div class="flex gap-2">
                        <input
                            type="text"
                            wire:model="textoRespuesta"
                            wire:keydown.enter="responder"
                            placeholder="Escribe tu respuesta..."
                            class="flex-1 px-4 py-2.5 border border-blue-200 rounded-xl text-sm text-blue-900
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none
                                   bg-blue-50/30 placeholder-blue-300">
                        <button
                            wire:click="responder"
                            class="px-4 py-2.5 bg-blue-700 hover:bg-blue-800 text-white rounded-xl transition-colors flex items-center gap-2">
                            <span wire:loading.remove wire:target="responder">@svg('lucide-send', 'w-4 h-4')</span>
                            <span wire:loading wire:target="responder">@svg('lucide-loader-2', 'w-4 h-4 animate-spin')</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>