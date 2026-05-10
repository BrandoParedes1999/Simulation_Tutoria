{{--
    Componente de anuncios para el dashboard.
    Se incluye SOLO en alumno/dashboard y tutor/dashboard.
    
    Uso: @include('components.anuncios-dashboard')
--}}
@php
    use App\Models\Anuncio;
    try {
        $anuncios = Anuncio::activos()->limit(7)->get();
    } catch (\Throwable $e) {
        $anuncios = collect();
    }
@endphp

@if($anuncios->isNotEmpty())
@php
    $principal  = $anuncios->first();
    $secundarios = $anuncios->skip(1)->take(6);
@endphp

<div x-data="{ modalAbierto: false, anuncioActivo: null }" class="mb-6">

    {{-- ── TÍTULO DE SECCIÓN ───────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            @svg('lucide-newspaper', 'w-4 h-4 text-blue-600')
            <h2 class="text-sm font-bold text-blue-900">Tablero de Avisos · UNACAR</h2>
            @if($anuncios->where('categoria','urgente')->isNotEmpty())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    Aviso urgente
                </span>
            @endif
        </div>
        <span class="text-[10px] text-blue-400">{{ $anuncios->count() }} avisos activos</span>
    </div>

    {{-- ── GRID PRINCIPAL ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

        {{-- CARD PRINCIPAL (grande) --}}
        @php $c = $principal->colorCategoria(); @endphp
        <div class="lg:col-span-2 relative rounded-2xl overflow-hidden cursor-pointer group shadow-md
                    min-h-[220px] lg:min-h-[260px]"
             @click="anuncioActivo = {{ json_encode(['titulo'=>$principal->titulo,'contenido'=>$principal->contenido,'label'=>$c['label'],'imagen'=>$principal->imagen_url]) }}; modalAbierto = true">

            {{-- Imagen de fondo --}}
            @if($principal->imagen_url)
            <img src="{{ $principal->imagen_url }}"
                 alt="{{ $principal->titulo }}"
                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 onerror="this.style.display='none'">
            @endif

            {{-- Gradiente overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-blue-950/90 via-blue-900/50 to-blue-800/20"></div>

            {{-- Contenido --}}
            <div class="relative h-full flex flex-col justify-between p-5 min-h-[220px] lg:min-h-[260px]">
                <div class="flex items-start justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $c['bg'] }} {{ $c['text'] }} text-[10px] font-bold rounded-full backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                        {{ $c['label'] }}
                    </span>
                    @if($principal->destacado)
                    <span class="px-2 py-1 bg-amber-400/90 text-amber-900 text-[10px] font-bold rounded-full backdrop-blur-sm">
                        ⭐ Destacado
                    </span>
                    @endif
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white leading-tight mb-2 drop-shadow-md">
                        {{ $principal->titulo }}
                    </h3>
                    <p class="text-sm text-blue-100 leading-relaxed line-clamp-2 drop-shadow">
                        {{ $principal->contenido }}
                    </p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-[11px] text-blue-300">
                            {{ $principal->created_at->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
                            @if($principal->fecha_expiracion)
                                · Vence {{ $principal->fecha_expiracion->locale('es')->isoFormat('D MMM') }}
                            @endif
                        </span>
                        <span class="flex items-center gap-1 text-xs text-blue-200 group-hover:text-white transition-colors font-medium">
                            Leer más
                            @svg('lucide-arrow-right', 'w-3.5 h-3.5')
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA SECUNDARIA --}}
        <div class="flex flex-col gap-3">
            @foreach($secundarios->take(3) as $anuncio)
            @php $cs = $anuncio->colorCategoria(); @endphp
            <div class="relative rounded-2xl overflow-hidden cursor-pointer group shadow-sm flex-1
                        min-h-[78px]"
                 @click="anuncioActivo = {{ json_encode(['titulo'=>$anuncio->titulo,'contenido'=>$anuncio->contenido,'label'=>$cs['label'],'imagen'=>$anuncio->imagen_url]) }}; modalAbierto = true">

                {{-- Imagen --}}
                @if($anuncio->imagen_url)
                <img src="{{ $anuncio->imagen_url }}"
                     alt="{{ $anuncio->titulo }}"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                     onerror="this.style.display='none'">
                @endif

                {{-- Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-r from-blue-950/85 via-blue-900/60 to-transparent"></div>

                {{-- Contenido --}}
                <div class="relative flex items-center gap-3 p-3.5">
                    <div class="w-8 h-8 {{ $cs['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0 shadow">
                        @if($anuncio->categoria === 'urgente')   @svg('lucide-alert-circle', 'w-4 h-4 ' . $cs['text'])
                        @elseif($anuncio->categoria === 'academico') @svg('lucide-book-open', 'w-4 h-4 ' . $cs['text'])
                        @elseif($anuncio->categoria === 'evento') @svg('lucide-calendar', 'w-4 h-4 ' . $cs['text'])
                        @elseif($anuncio->categoria === 'beca')   @svg('lucide-award', 'w-4 h-4 ' . $cs['text'])
                        @else @svg('lucide-info', 'w-4 h-4 ' . $cs['text'])
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white leading-tight line-clamp-1 drop-shadow">
                            {{ $anuncio->titulo }}
                        </p>
                        <p class="text-[10px] text-blue-200 mt-0.5 drop-shadow">
                            {{ $anuncio->created_at->locale('es')->isoFormat('D MMM') }}
                        </p>
                    </div>
                    @svg('lucide-chevron-right', 'w-4 h-4 text-blue-300 flex-shrink-0 group-hover:text-white transition-colors')
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- FILA INFERIOR (resto de anuncios) --}}
    @if($secundarios->count() > 3)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
        @foreach($secundarios->skip(3) as $anuncio)
        @php $cr = $anuncio->colorCategoria(); @endphp
        <div class="bg-white border {{ $cr['border'] }} rounded-2xl p-3.5 cursor-pointer hover:shadow-md
                    transition-all duration-200 group card-hover"
             @click="anuncioActivo = {{ json_encode(['titulo'=>$anuncio->titulo,'contenido'=>$anuncio->contenido,'label'=>$cr['label'],'imagen'=>$anuncio->imagen_url]) }}; modalAbierto = true">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full {{ $cr['dot'] }}"></span>
                <span class="text-[10px] {{ $cr['text'] }} font-bold uppercase tracking-wide">{{ $cr['label'] }}</span>
                <span class="ml-auto text-[10px] text-blue-300">{{ $anuncio->created_at->locale('es')->isoFormat('D MMM') }}</span>
            </div>
            <p class="text-xs font-semibold text-blue-900 leading-tight line-clamp-2 group-hover:text-blue-700 transition-colors">
                {{ $anuncio->titulo }}
            </p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── MODAL DETALLE ───────────────────────────────────────────── --}}
    <div x-show="modalAbierto && anuncioActivo"
         x-cloak
         @keydown.escape.window="modalAbierto = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-blue-950/60 backdrop-blur-sm"
         x-transition.opacity>
        <div @click.outside="modalAbierto = false"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            {{-- Imagen del modal --}}
            <div class="relative h-40 bg-gradient-to-br from-blue-700 to-blue-900 overflow-hidden"
                 x-show="anuncioActivo">
                <template x-if="anuncioActivo && anuncioActivo.imagen">
                    <img :src="anuncioActivo.imagen"
                         class="absolute inset-0 w-full h-full object-cover opacity-70"
                         onerror="this.style.display='none'">
                </template>
                <div class="absolute inset-0 bg-gradient-to-t from-blue-950/70 to-transparent"></div>
                <button @click="modalAbierto = false"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/20 hover:bg-white/30 backdrop-blur-sm
                               rounded-full flex items-center justify-center text-white transition">
                    @svg('lucide-x', 'w-4 h-4')
                </button>
                <div class="absolute bottom-3 left-4">
                    <span class="text-xs font-bold text-white/80 uppercase tracking-wide"
                          x-text="anuncioActivo?.label"></span>
                </div>
            </div>

            {{-- Contenido del modal --}}
            <div class="p-6">
                <h3 class="text-base font-bold text-blue-900 leading-tight mb-3"
                    x-text="anuncioActivo?.titulo"></h3>
                <p class="text-sm text-blue-700 leading-relaxed"
                   x-text="anuncioActivo?.contenido"></p>
                <button @click="modalAbierto = false"
                        class="mt-5 w-full py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
@endif