<div class="min-h-screen bg-slate-50 pb-8">

    {{-- ═══ HERO ═══ --}}
    <div class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 px-4 pt-10 pb-16 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-start gap-4 mb-8">
                <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center ring-1 ring-white/20">
                    @svg('lucide-life-buoy', 'w-6 h-6 text-white')
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Centro de ayuda — Tutor</h1>
                    <p class="text-blue-300 text-sm mt-1">Guía de uso del Sistema de Tutoría Académica</p>
                </div>
            </div>

            {{-- Búsqueda --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    @svg('lucide-search', 'w-4 h-4 text-blue-400')
                </div>
                <input wire:model.live.debounce.300ms="busqueda"
                       type="text"
                       placeholder="Busca entre las preguntas frecuentes…"
                       class="w-full pl-11 pr-10 py-3.5 rounded-xl bg-white text-blue-900 text-sm placeholder-blue-300 border-0 shadow-lg focus:ring-2 focus:ring-blue-300 outline-none">
                @if($busqueda)
                    <button wire:click="$set('busqueda', '')"
                            class="absolute inset-y-0 right-4 flex items-center text-blue-300 hover:text-blue-500">
                        @svg('lucide-x', 'w-4 h-4')
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-6">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- ═══ SIDEBAR CATEGORÍAS (desktop) ═══ --}}
            <aside class="lg:w-52 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2 sticky top-20">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider px-3 py-2">Secciones</p>
                    @foreach($categorias as $cat)
                        <button wire:click="setCategoriaActiva('{{ $cat['id'] }}')"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all text-left
                                       {{ $categoriaActiva === $cat['id']
                                            ? 'bg-blue-700 text-white'
                                            : 'text-slate-500 hover:bg-slate-50 hover:text-blue-700' }}">
                            @svg($cat['icon'], 'w-3.5 h-3.5 flex-shrink-0')
                            {{ $cat['label'] }}
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- ═══ CONTENIDO PRINCIPAL ═══ --}}
            <div class="flex-1 min-w-0">

                {{-- Categorías móvil --}}
                @if(!$busqueda)
                <div class="lg:hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-2.5 mb-4 overflow-x-auto">
                    <div class="flex gap-2 min-w-max">
                        @foreach($categorias as $cat)
                            <button wire:click="setCategoriaActiva('{{ $cat['id'] }}')"
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap
                                           {{ $categoriaActiva === $cat['id']
                                                ? 'bg-blue-700 text-white'
                                                : 'text-slate-500 hover:bg-slate-100 hover:text-blue-700' }}">
                                @svg($cat['icon'], 'w-3.5 h-3.5')
                                {{ $cat['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Encabezado de sección --}}
                @if(!$busqueda)
                    @php
                        $catActual = collect($categorias)->firstWhere('id', $categoriaActiva);
                    @endphp
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                            @svg($catActual['icon'] ?? 'lucide-layout-grid', 'w-3.5 h-3.5 text-blue-700')
                        </div>
                        <h2 class="text-sm font-semibold text-slate-700">{{ $catActual['label'] ?? 'Todas' }}</h2>
                        <span class="ml-auto text-xs text-slate-400">{{ count($preguntas) }} preguntas</span>
                    </div>
                @else
                    <p class="text-xs text-slate-400 mb-4">
                        {{ count($preguntas) }} {{ count($preguntas) === 1 ? 'resultado' : 'resultados' }} para "{{ $busqueda }}"
                    </p>
                @endif

                {{-- Lista FAQ --}}
                @if(count($preguntas) === 0)
                    <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            @svg('lucide-search-x', 'w-6 h-6 text-slate-400')
                        </div>
                        <p class="text-slate-700 font-medium">Sin resultados</p>
                        <p class="text-slate-400 text-sm mt-1">Intenta con otras palabras clave</p>
                        @if($busqueda)
                            <button wire:click="$set('busqueda', '')" class="mt-4 text-sm text-blue-600 underline">Limpiar búsqueda</button>
                        @endif
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($preguntas as $i => $faq)
                            <div x-data="{ abierto: false }"
                                 class="bg-white rounded-xl border shadow-sm overflow-hidden transition-all"
                                 :class="abierto ? 'border-blue-200' : 'border-slate-200'">

                                <button @click="abierto = !abierto"
                                        class="w-full flex items-start gap-3 p-4 text-left transition-colors hover:bg-slate-50/80">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center mt-0.5 transition-colors"
                                         :class="abierto ? 'bg-blue-100' : 'bg-slate-100'">
                                        @svg($faq['icono'], 'w-4 h-4 text-blue-600')
                                    </div>
                                    <span class="flex-1 text-sm font-medium text-slate-800 leading-snug">{{ $faq['pregunta'] }}</span>
                                    <div class="flex-shrink-0 mt-0.5 transition-transform duration-200 text-slate-400" :class="abierto ? 'rotate-180 text-blue-500' : ''">
                                        @svg('lucide-chevron-down', 'w-4 h-4')
                                    </div>
                                </button>

                                <div x-show="abierto"
                                     x-collapse
                                     class="border-t border-slate-100">
                                    <div class="px-4 pb-4 pt-3 bg-blue-50/30">
                                        <p class="text-sm text-slate-600 leading-relaxed pl-11">{{ $faq['respuesta'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ═══ SOPORTE ADICIONAL ═══ --}}
                <div class="mt-8 grid sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            @svg('lucide-mail', 'w-4 h-4 text-blue-700')
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">¿Tienes otra duda?</p>
                            <p class="text-xs text-slate-500 mt-0.5 mb-3">Contacta a soporte técnico del sistema</p>
                            <a href="mailto:soporte@tutorias.edu.mx"
                               class="text-xs font-medium text-blue-700 hover:text-blue-900 underline">
                                soporte@tutorias.edu.mx
                            </a>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-start gap-3">
                        <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            @svg('lucide-message-square', 'w-4 h-4 text-green-700')
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Mensajes con alumnos</p>
                            <p class="text-xs text-slate-500 mt-0.5 mb-3">Comunícate directamente desde el buzón</p>
                            <a href="{{ route('tutor.mensajes') }}" wire:navigate
                               class="text-xs font-medium text-blue-700 hover:text-blue-900 underline">
                                Ir al buzón →
                            </a>
                        </div>
                    </div>
                </div>

                <p class="text-center text-xs text-slate-400 mt-8">
                    Sistema de Tutoría Académica · Semestre 2026-1
                </p>
            </div>
        </div>
    </div>
</div>