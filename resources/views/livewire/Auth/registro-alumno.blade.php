<div class="min-h-screen bg-[#f0f7ff] flex flex-col lg:flex-row">

    {{-- ── Panel izquierdo — desktop ─────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden
                bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700
                flex-col justify-between p-12">
        <div class="absolute top-0 right-0 w-80 h-80 bg-blue-600/30 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-950/40 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

        <div class="relative z-10">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center border border-white/20">
                    @svg('lucide-graduation-cap', 'w-6 h-6 text-white')
                </div>
                <div>
                    <p class="font-bold text-white text-base">Sistema de Tutoría</p>
                    <p class="text-blue-200 text-xs">UNACAR · ISC</p>
                </div>
            </a>
        </div>

        <div class="relative z-10 space-y-6">
            <h2 class="text-4xl font-extrabold text-white leading-tight">
                Bienvenido al<br><span class="text-blue-300">Sistema de Tutoría</span>
            </h2>
            <p class="text-blue-200 leading-relaxed">
                Registra tu cuenta con tu matrícula para acceder a tu malla curricular,
                calificaciones y seguimiento académico personalizado.
            </p>
            <div class="space-y-3">
                @foreach(['Verificación segura con tu correo @mail.unacar.mx', 'Registro completado en menos de 2 minutos', 'Solo alumnos activos de UNACAR'] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/15 flex-shrink-0">
                        @svg('lucide-check', 'w-4 h-4 text-emerald-300')
                    </div>
                    <p class="text-blue-100 text-sm">{{ $item }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10 flex items-center gap-2">
            <div class="h-px flex-1 bg-blue-700"></div>
            <p class="text-blue-400 text-xs">© {{ date('Y') }} Sistema de Tutoría · UNACAR</p>
            <div class="h-px flex-1 bg-blue-700"></div>
        </div>
    </div>

    {{-- ── Panel derecho — formulario ─────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">

            {{-- Logo móvil --}}
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-blue-700 rounded-xl flex items-center justify-center">
                        @svg('lucide-graduation-cap', 'w-5 h-5 text-white')
                    </div>
                    <span class="font-bold text-blue-900">Sistema de Tutoría</span>
                </a>
            </div>

            {{-- ── Stepper ─────────────────────────────────────────────── --}}
            <div class="flex items-center mb-8">
                @foreach([1 => 'Matrícula', 2 => 'Verificación', 3 => 'Perfil'] as $n => $label)
                    <div class="flex items-center {{ $n < 3 ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all
                                {{ $paso > $n ? 'bg-emerald-500 text-white' : ($paso === $n ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-400') }}">
                                @if($paso > $n)
                                    @svg('lucide-check', 'w-4 h-4')
                                @else
                                    {{ $n }}
                                @endif
                            </div>
                            <p class="text-[10px] mt-1 font-medium {{ $paso >= $n ? 'text-blue-700' : 'text-blue-300' }}">
                                {{ $label }}
                            </p>
                        </div>
                        @if($n < 3)
                        <div class="flex-1 h-0.5 mx-2 mb-4 {{ $paso > $n ? 'bg-emerald-400' : 'bg-blue-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ── Card ────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-xl shadow-blue-900/10 border border-blue-100 p-7">

                {{-- ══ PASO 1: Matrícula ══════════════════════════════════ --}}
                @if($paso === 1)
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        @svg('lucide-id-card', 'w-7 h-7 text-blue-700')
                    </div>
                    <h2 class="text-xl font-bold text-blue-900">Ingresa tu matrícula</h2>
                    <p class="text-blue-400 text-sm mt-1">
                        Te enviaremos un código de verificación a tu correo
                        <strong class="text-blue-600">@mail.unacar.mx</strong>
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-1.5">
                            Número de matrícula
                        </label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                @svg('lucide-hash', 'w-5 h-5 text-blue-300')
                            </div>
                            <input
                                wire:model="matricula"
                                type="text"
                                placeholder="Ej: 190039"
                                maxlength="15"
                                autocomplete="off"
                                wire:keydown.enter="enviarCodigo"
                                autofocus
                                class="w-full pl-11 pr-4 py-3.5 border border-blue-200 rounded-xl
                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-400
                                       outline-none bg-blue-50/50 text-blue-900 placeholder-blue-300
                                       font-mono text-xl uppercase tracking-widest text-center
                                       @error('matricula') border-red-300 bg-red-50/50 @enderror">
                        </div>
                        @error('matricula')
                        <div class="mt-2 flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-xl">
                            @svg('lucide-alert-circle', 'w-4 h-4 text-red-500 flex-shrink-0 mt-0.5')
                            <p class="text-xs text-red-700 leading-relaxed">{{ $message }}</p>
                        </div>
                        @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-start gap-2">
                        @svg('lucide-shield-check', 'w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5')
                        <p class="text-xs text-blue-600 leading-relaxed">
                            El código se enviará a <strong class="font-mono">{tu_matricula}@mail.unacar.mx</strong>.
                            Si tu correo institucional es diferente, acude a Control Escolar.
                        </p>
                    </div>

                    <button
                        wire:click="enviarCodigo"
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 bg-blue-700 hover:bg-blue-800 active:bg-blue-900
                               disabled:opacity-60 disabled:cursor-not-allowed
                               text-white font-semibold rounded-xl transition-all
                               flex items-center justify-center gap-2 text-sm">
                        <span wire:loading.remove wire:target="enviarCodigo" class="flex items-center gap-2">
                            @svg('lucide-send', 'w-4 h-4')
                            Enviar código de verificación
                        </span>
                        <span wire:loading wire:target="enviarCodigo" class="flex items-center gap-2">
                            @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                            Enviando código...
                        </span>
                    </button>
                </div>
                @endif

                {{-- ══ PASO 2: Código ═════════════════════════════════════ --}}
                @if($paso === 2)
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        @svg('lucide-mail-check', 'w-7 h-7 text-emerald-600')
                    </div>
                    <h2 class="text-xl font-bold text-blue-900">Revisa tu correo</h2>
                    <p class="text-blue-400 text-sm mt-1">
                        Enviamos el código a
                    </p>
                    <p class="text-blue-700 font-semibold text-sm font-mono mt-0.5">{{ $correoOculto }}</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-2 text-center">
                            Ingresa el código de 6 dígitos
                        </label>
                        <input
                            wire:model="codigo"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="— — — — — —"
                            wire:keydown.enter="verificarCodigo"
                            autofocus
                            class="w-full px-4 py-5 border border-blue-200 rounded-xl
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-400
                                   outline-none bg-blue-50/50 text-center
                                   text-4xl font-black font-mono tracking-[0.6em] text-blue-900
                                   placeholder-blue-200
                                   @error('codigo') border-red-300 bg-red-50/50 @enderror">
                        @error('codigo')
                        <div class="mt-2 flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-xl">
                            @svg('lucide-alert-circle', 'w-4 h-4 text-red-500 flex-shrink-0 mt-0.5')
                            <p class="text-xs text-red-700">{{ $message }}</p>
                        </div>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl p-3">
                        @svg('lucide-clock', 'w-4 h-4 text-blue-400 flex-shrink-0')
                        <p class="text-xs text-blue-600">
                            El código es válido por <strong>10 minutos</strong>.
                            Revisa también la carpeta de spam.
                        </p>
                    </div>

                    <button
                        wire:click="verificarCodigo"
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 bg-blue-700 hover:bg-blue-800
                               disabled:opacity-60 disabled:cursor-not-allowed
                               text-white font-semibold rounded-xl transition-all
                               flex items-center justify-center gap-2 text-sm">
                        <span wire:loading.remove wire:target="verificarCodigo" class="flex items-center gap-2">
                            @svg('lucide-check-circle-2', 'w-4 h-4')
                            Verificar código
                        </span>
                        <span wire:loading wire:target="verificarCodigo" class="flex items-center gap-2">
                            @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                            Verificando...
                        </span>
                    </button>

                    <button
                        wire:click="reenviarCodigo"
                        class="w-full py-2.5 text-sm text-blue-400 hover:text-blue-700
                               hover:bg-blue-50 rounded-xl transition">
                        ¿No llegó el correo? Intentar de nuevo
                    </button>
                </div>
                @endif

                {{-- ══ PASO 3: Completar perfil ═══════════════════════════ --}}
                @if($paso === 3)
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        @svg('lucide-user-check', 'w-7 h-7 text-emerald-600')
                    </div>
                    <h2 class="text-xl font-bold text-blue-900">¡Identidad verificada!</h2>
                    <p class="text-blue-400 text-sm mt-1">
                        Matrícula <strong class="text-blue-700 font-mono">{{ $matricula }}</strong> confirmada.
                        Completa tu perfil para ingresar.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-1.5">Nombre completo</label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                @svg('lucide-user', 'w-5 h-5 text-blue-300')
                            </div>
                            <input wire:model="nombre" type="text"
                                   placeholder="Ej: Ana Fernanda López Cruz"
                                   autofocus
                                   class="w-full pl-11 pr-4 py-3.5 border border-blue-200 rounded-xl
                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-400
                                          outline-none bg-blue-50/50 text-blue-900 placeholder-blue-300
                                          @error('nombre') border-red-300 bg-red-50/50 @enderror">
                        </div>
                        @error('nombre')<p class="text-xs text-red-600 mt-1.5 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="{ ver: false }">
                        <label class="block text-sm font-semibold text-blue-900 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                @svg('lucide-lock', 'w-5 h-5 text-blue-300')
                            </div>
                            <input wire:model="password" :type="ver ? 'text' : 'password'"
                                   placeholder="Mínimo 8 caracteres"
                                   class="w-full pl-11 pr-12 py-3.5 border border-blue-200 rounded-xl
                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-400
                                          outline-none bg-blue-50/50 text-blue-900 placeholder-blue-300
                                          @error('password') border-red-300 bg-red-50/50 @enderror">
                            <button type="button" @click="ver = !ver" tabindex="-1"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 p-1 text-blue-400 hover:text-blue-600 transition">
                                <span x-show="!ver">@svg('lucide-eye', 'w-4 h-4')</span>
                                <span x-show="ver" x-cloak>@svg('lucide-eye-off', 'w-4 h-4')</span>
                            </button>
                        </div>
                        @error('password')<p class="text-xs text-red-600 mt-1.5 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-1.5">Confirmar contraseña</label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                @svg('lucide-lock', 'w-5 h-5 text-blue-300')
                            </div>
                            <input wire:model="passwordConf" type="password"
                                   placeholder="Repite tu contraseña"
                                   class="w-full pl-11 pr-4 py-3.5 border border-blue-200 rounded-xl
                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-400
                                          outline-none bg-blue-50/50 text-blue-900 placeholder-blue-300
                                          @error('passwordConf') border-red-300 bg-red-50/50 @enderror">
                        </div>
                        @error('passwordConf')<p class="text-xs text-red-600 mt-1.5 ml-1">{{ $message }}</p>@enderror
                    </div>

                    <button
                        wire:click="completar"
                        wire:loading.attr="disabled"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700
                               disabled:opacity-60 disabled:cursor-not-allowed
                               text-white font-bold rounded-xl transition-all mt-2
                               flex items-center justify-center gap-2 text-sm">
                        <span wire:loading.remove wire:target="completar" class="flex items-center gap-2">
                            @svg('lucide-rocket', 'w-4 h-4')
                            Crear mi cuenta y entrar al sistema
                        </span>
                        <span wire:loading wire:target="completar" class="flex items-center gap-2">
                            @svg('lucide-loader-2', 'w-4 h-4 animate-spin')
                            Creando cuenta...
                        </span>
                    </button>
                </div>
                @endif

            </div>{{-- /card --}}

            <div class="mt-5 text-center space-y-2">
                <p class="text-sm text-blue-500">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:underline">
                        Iniciar sesión
                    </a>
                </p>
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-1 text-xs text-blue-400 hover:text-blue-600 transition">
                    @svg('lucide-arrow-left', 'w-3 h-3')
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>

    {{-- Toast handler --}}
    <div x-data="{
            toasts:[],
            add(tipo,mensaje){
                const id=Date.now();
                this.toasts.push({id,tipo,mensaje});
                setTimeout(()=>this.toasts=this.toasts.filter(t=>t.id!==id),4000);
            }
         }"
         @toast.window="add($event.detail.tipo,$event.detail.mensaje)"
         class="fixed top-4 left-4 right-4 z-50 space-y-2 lg:left-auto lg:right-4 lg:max-w-sm pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="-translate-y-4 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :class="{
                     'bg-emerald-600 border-emerald-700': toast.tipo==='success',
                     'bg-red-600 border-red-700':         toast.tipo==='error',
                     'bg-blue-600 border-blue-700':        toast.tipo==='info',
                 }"
                 class="text-white px-4 py-3 rounded-xl shadow-lg border flex items-center gap-2 pointer-events-auto">
                <span x-text="toast.mensaje" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>

</div>