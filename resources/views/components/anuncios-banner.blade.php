@php
    $anuncios = \App\Models\Anuncio::activos()->limit(10)->get();
@endphp

@if($anuncios->isNotEmpty())
<div x-data="{
        cerrado: false,
        idx: 0,
        total: {{ $anuncios->count() }},
        auto: null,
        init() {
            this.auto = setInterval(() => {
                this.idx = (this.idx + 1) % this.total;
            }, 5000);
        },
        destroy() { clearInterval(this.auto); },
        prev() { this.idx = (this.idx - 1 + this.total) % this.total; clearInterval(this.auto); },
        next() { this.idx = (this.idx + 1) % this.total; clearInterval(this.auto); }
    }"
    x-show="!cerrado"
    x-cloak
    class="bg-white border-b border-blue-100 shadow-sm">

    {{-- ── Strip superior: ticker de destacados ─────────────────────── --}}
    @php $urgentes = $anuncios->where('categoria', 'urgente')->where('destacado', true); @endphp
    @if($urgentes->isNotEmpty())
    <div class="bg-red-600 px-4 py-1.5 flex items-center gap-3 overflow-hidden">
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
            <span class="text-white text-[10px] font-bold uppercase tracking-widest">Urgente</span>
        </div>
        <div class="flex-1 overflow-hidden">
            <div class="whitespace-nowrap overflow-hidden">
                @foreach($urgentes as $u)
                    <span class="text-white text-xs mr-12">{{ $u->titulo }} — {{ Str::limit($u->contenido, 80) }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tablero principal ─────────────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto">

        {{-- Header del tablero --}}
        <div class="flex items-center justify-between px-4 sm:px-6 py-2 border-b border-blue-50">
            <div class="flex items-center gap-2">
                @svg('lucide-newspaper', 'w-3.5 h-3.5 text-blue-600')
                <span class="text-xs font-bold text-blue-900 uppercase tracking-wide">Tablero de Avisos · UNACAR</span>
            </div>
            <div class="flex items-center gap-3">
                {{-- Dots navegación --}}
                <div class="hidden sm:flex gap-1">
                    @foreach($anuncios as $i => $a)
                    <button @click="idx = {{ $i }}"
                            :class="idx === {{ $i }} ? 'bg-blue-700 w-4' : 'bg-blue-200 w-2'"
                            class="h-2 rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
                {{-- Cerrar --}}
                <button @click="cerrado = true"
                        class="text-blue-300 hover:text-blue-600 transition p-1"
                        title="Cerrar tablero">
                    @svg('lucide-x', 'w-3.5 h-3.5')
                </button>
            </div>
        </div>

        {{-- Tarjetas de anuncios --}}
        <div class="relative overflow-hidden">

            {{-- Flecha izquierda --}}
            <button @click="prev()"
                    class="absolute left-1 top-1/2 -translate-y-1/2 z-10
                           w-7 h-7 bg-white border border-blue-200 rounded-full
                           flex items-center justify-center shadow-sm
                           hover:bg-blue-50 transition hidden sm:flex">
                @svg('lucide-chevron-left', 'w-4 h-4 text-blue-600')
            </button>

            {{-- Cards visibles --}}
            <div class="flex gap-3 px-4 sm:px-10 py-3 overflow-x-auto scrollbar-hide sm:overflow-visible">

                @foreach($anuncios as $i => $anuncio)
                @php $c = $anuncio->colorCategoria(); @endphp
                <div
                    x-show="window.innerWidth < 640 || Math.abs(idx - {{ $i }}) <= 1"
                    :class="idx === {{ $i }} ? 'ring-2 ring-blue-500 shadow-md' : 'opacity-60'"
                    class="flex-shrink-0 w-72 sm:w-auto sm:flex-1 sm:max-w-xs
                           bg-white border {{ $c['border'] }} rounded-2xl p-4
                           transition-all duration-300 cursor-pointer hover:opacity-100"
                    @click="idx = {{ $i }}">

                    {{-- Badge + fecha --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 {{ $c['bg'] }} {{ $c['text'] }} text-[10px] font-bold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                            {{ $c['label'] }}
                        </span>
                        <span class="text-[10px] text-blue-300">
                            {{ $anuncio->created_at->locale('es')->isoFormat('D MMM') }}
                        </span>
                    </div>

                    {{-- Título --}}
                    <p class="text-sm font-bold text-blue-900 leading-tight mb-1.5">
                        {{ $anuncio->titulo }}
                    </p>

                    {{-- Contenido --}}
                    <p class="text-xs text-blue-500 leading-relaxed line-clamp-2">
                        {{ $anuncio->contenido }}
                    </p>

                    {{-- Footer: ver más --}}
                    <div class="mt-2 flex items-center justify-between">
                        @if($anuncio->fecha_expiracion)
                        <span class="text-[10px] text-blue-300 flex items-center gap-1">
                            @svg('lucide-clock', 'w-3 h-3')
                            Vence {{ $anuncio->fecha_expiracion->locale('es')->isoFormat('D MMM') }}
                        </span>
                        @else
                        <span></span>
                        @endif
                        <button x-data
                                @click.stop="$dispatch('open-aviso', { titulo: '{{ addslashes($anuncio->titulo) }}', contenido: '{{ addslashes($anuncio->contenido) }}', label: '{{ $c['label'] }}' })"
                                class="text-[10px] {{ $c['text'] }} font-semibold hover:underline">
                            Ver más →
                        </button>
                    </div>
                </div>
                @endforeach

            </div>

            {{-- Flecha derecha --}}
            <button @click="next()"
                    class="absolute right-1 top-1/2 -translate-y-1/2 z-10
                           w-7 h-7 bg-white border border-blue-200 rounded-full
                           flex items-center justify-center shadow-sm
                           hover:bg-blue-50 transition hidden sm:flex">
                @svg('lucide-chevron-right', 'w-4 h-4 text-blue-600')
            </button>
        </div>

    </div>

    {{-- ── Modal detalle de aviso ────────────────────────────────────── --}}
    <div x-data="{ abierto: false, titulo: '', contenido: '', label: '' }"
         @open-aviso.window="abierto = true; titulo = $event.detail.titulo; contenido = $event.detail.contenido; label = $event.detail.label"
         @keydown.escape.window="abierto = false"
         x-show="abierto"
         x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-blue-950/50 backdrop-blur-sm"
         x-transition.opacity>
        <div @click.outside="abierto = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wide" x-text="label"></span>
                    <h3 class="text-base font-bold text-blue-900 leading-tight mt-1" x-text="titulo"></h3>
                </div>
                <button @click="abierto = false" class="ml-3 p-1.5 hover:bg-blue-50 rounded-xl text-blue-400">
                    @svg('lucide-x', 'w-4 h-4')
                </button>
            </div>
            <p class="text-sm text-blue-700 leading-relaxed" x-text="contenido"></p>
            <button @click="abierto = false"
                    class="mt-5 w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition">
                Entendido
            </button>
        </div>
    </div>

</div>
@endif