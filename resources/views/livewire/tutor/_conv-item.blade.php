<div wire:click="seleccionar({{ $conv->id }})"
     class="p-3 cursor-pointer transition-colors
            {{ $conversacionActivaId === $conv->id ? 'bg-blue-50 border-l-2 border-l-blue-500' : 'hover:bg-blue-50/50' }}">
    <div class="flex items-start gap-2.5">
        {{-- Avatar --}}
        <div class="relative flex-shrink-0">
            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-700 font-bold text-xs">
                    {{ strtoupper(substr($conv->otro_nombre, 0, 1)) }}
                </span>
            </div>
            @if($conv->sin_leer)
                <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1">
                <p class="text-xs {{ $conv->sin_leer ? 'font-bold text-blue-900' : 'font-semibold text-slate-700' }} truncate">
                    {{ $conv->otro_nombre }}
                </p>
                <span class="text-[10px] text-slate-400 flex-shrink-0">
                    {{ $conv->ultima_actividad->locale('es')->diffForHumans(short: true) }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $conv->asunto }}</p>
            <p class="text-[10px] text-slate-400 truncate mt-0.5">
                @if(!$conv->ultimo_es_tutor)
                    <span class="text-blue-500 font-medium">Alumno: </span>
                @else
                    <span class="text-slate-400">Tú: </span>
                @endif
                {{ $conv->ultimo_contenido }}
            </p>
            <div class="flex items-center gap-1.5 mt-1">
                @if($conv->prioridad === 'urgente')
                    <span class="text-[10px] px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full font-medium">Urgente</span>
                @endif
                @if($conv->sin_leer)
                    <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded-full font-medium">Nueva respuesta</span>
                @endif
                @if($conv->respuestas->count() > 0)
                    <span class="text-[10px] text-slate-400">{{ $conv->respuestas->count() }} {{ $conv->respuestas->count() === 1 ? 'respuesta' : 'respuestas' }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
