<div class="min-h-screen bg-slate-50 pb-8"> {{-- Unificamos el fondo --}}

    {{-- ═══ HERO ═══ --}}
    <div class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 px-4 pt-10 pb-16 sm:px-6">
        <div class="max-w-4xl mx-auto"> {{-- Aumentamos a 4xl para consistencia --}}
            <div class="flex items-start gap-4 mb-8">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center ring-1 ring-white/20">
                    @svg('lucide-life-buoy', 'w-6 h-6 text-white')
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Centro de ayuda — Alumno</h1>
                    <p class="text-blue-300 text-sm mt-1">Encuentra respuestas rápidas sobre el uso del Sistema</p>
                </div>
            </div>

            {{-- Búsqueda --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    @svg('lucide-search', 'w-4 h-4 text-blue-400')
                </div>
                <input wire:model.live.debounce.300ms="busqueda"
                       type="text"
                       placeholder="Busca tu pregunta aquí…"
                       class="w-full pl-11 pr-10 py-3.5 rounded-xl bg-white text-blue-900 text-sm placeholder-blue-300 border-0 shadow-lg focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-6">
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- ═══ SIDEBAR CATEGORÍAS ═══ --}}
            <aside class="lg:w-52 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2 sticky top-20">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider px-3 py-2">Secciones</p>
                    @foreach($categorias as $cat)
                        <button wire:click="setCategoriaActiva('{{ $cat['id'] }}')"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all text-left
                                       {{ $categoriaActiva === $cat['id'] ? 'bg-blue-700 text-white' : 'text-slate-500 hover:bg-slate-50 hover:text-blue-700' }}">
                            @svg($cat['icon'], 'w-3.5 h-3.5 flex-shrink-0')
                            {{ $cat['label'] }}
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- ═══ CONTENIDO PRINCIPAL (FAQ) ═══ --}}
            <div class="flex-1 min-w-0">
                {{-- Aquí va el @foreach de tus preguntas, igual que lo tienes en el tutor --}}
                <div class="space-y-2">
                    @foreach($preguntas as $faq)
                        <div x-data="{ abierto: false }" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <button @click="abierto = !abierto" class="w-full flex items-start gap-3 p-4 text-left hover:bg-slate-50">
                                <div class="flex-shrink-0 w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center mt-0.5">
                                    @svg($faq['icono'], 'w-4 h-4 text-blue-600')
                                </div>
                                <span class="flex-1 text-sm font-medium text-slate-800">{{ $faq['pregunta'] }}</span>
                                @svg('lucide-chevron-down', 'w-4 h-4 text-slate-400 transition-transform', ['::class' => "abierto ? 'rotate-180' : ''"])
                            </button>
                            <div x-show="abierto" x-collapse class="px-4 pb-4 pt-1 bg-blue-50/30 border-t border-slate-50">
                                <p class="text-sm text-slate-600 pl-11">{{ $faq['respuesta'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Banner de contacto al final --}}
                <div class="mt-8 bg-gradient-to-r from-blue-700 to-blue-600 rounded-2xl p-5 flex items-center gap-4 text-white">
                    @svg('lucide-message-circle', 'w-10 h-10 opacity-20')
                    <div class="flex-1">
                        <p class="font-medium text-sm">¿Aún tienes dudas?</p>
                        <p class="text-blue-100 text-xs">Contacta a tu tutor directamente</p>
                    </div>
                    <a href="{{ route('alumno.mensajes') }}" class="bg-white text-blue-700 px-4 py-2 rounded-xl text-xs font-bold">Escribir</a>
                </div>
            </div>

        </div>
    </div>
</div>