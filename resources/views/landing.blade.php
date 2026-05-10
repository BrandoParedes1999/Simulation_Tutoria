<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e3a8a">
    <title>Sistema de Tutoría Académica · UNACAR</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .hero-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 32px 32px;
        }
        .browser-frame { box-shadow: 0 32px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.08); }
        .demo-tab-active { background: #1d4ed8; color: #fff; }
        .demo-tab { color: #60a5fa; transition: all .2s; }
        .demo-tab:hover { background: rgba(255,255,255,0.08); color: #fff; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .float { animation: float 4s ease-in-out infinite; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp .6s ease-out forwards; }
        .fade-up-2 { animation: fadeUp .6s .15s ease-out both; }
        .fade-up-3 { animation: fadeUp .6s .30s ease-out both; }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(30,58,138,.12); }
        .badge-aprobada { background:#dcfce7;color:#15803d; }
        .badge-curso    { background:#dbeafe;color:#1d4ed8; }
        .badge-baja     { background:#fef9c3;color:#a16207; }
        .badge-block    { background:#f3f4f6;color:#4b5563; }
        .glow { box-shadow: 0 0 40px rgba(59,130,246,.3); }
    </style>
</head>
<body class="font-sans antialiased bg-white overflow-x-hidden"
      x-data="{
          tabDemo:    'dashboard',
          semDemo:    1,
          modalLogin: false,
          menuOpen:   false,
          sems: [1,2,3,4,5,6,7,8,9],
          malla: {
            1: [{c:'TCOE101',n:'Com. Oral y Escrita',cr:4,e:'aprobada',p:88},
                {c:'RL101',n:'Razonamiento Lógico',cr:4,e:'aprobada',p:90},
                {c:'QG101',n:'Química General',cr:6,e:'aprobada',p:82},
                {c:'LEE101',n:'La Empresa y su Entorno',cr:5,e:'aprobada',p:85},
                {c:'PROG101',n:'Programación I',cr:8,e:'aprobada',p:78},
                {c:'TMI101',n:'Tecnologías de la Info.',cr:4,e:'aprobada',p:91}],
            2: [{c:'AL201',n:'Álgebra Lineal',cr:6,e:'aprobada',p:80},
                {c:'PROG201',n:'Programación II',cr:8,e:'aprobada',p:75},
                {c:'BD201',n:'Bases de Datos I',cr:6,e:'aprobada',p:83},
                {c:'DS201',n:'Desarrollo Sustentable',cr:2,e:'aprobada',p:92}],
            3: [{c:'ING301',n:'Inglés I',cr:4,e:'aprobada',p:88},
                {c:'CD301',n:'Cálculo Diferencial',cr:7,e:'aprobada',p:72},
                {c:'MD301',n:'Matemáticas Discretas',cr:7,e:'aprobada',p:76},
                {c:'ED301',n:'Estructura de Datos',cr:6,e:'aprobada',p:80},
                {c:'BD301',n:'Bases de Datos II',cr:6,e:'aprobada',p:84}],
            4: [{c:'ING401',n:'Inglés II',cr:4,e:'aprobada',p:86},
                {c:'CI401',n:'Cálculo Integral',cr:7,e:'aprobada',p:70},
                {c:'PE401',n:'Probabilidad y Est.',cr:7,e:'aprobada',p:74},
                {c:'POO401',n:'Prog. Orientada a Obj.',cr:7,e:'aprobada',p:88},
                {c:'SO401',n:'Sistemas Operativos I',cr:6,e:'aprobada',p:79}],
            5: [{c:'ING501',n:'Inglés III',cr:4,e:'curso',p:null},
                {c:'TS501',n:'Teoría de Señales',cr:6,e:'aprobada',p:75},
                {c:'SEE501',n:'Sist. Eléctricos y Electr.',cr:6,e:'baja',p:null},
                {c:'PV501',n:'Programación Visual',cr:6,e:'aprobada',p:90},
                {c:'ADS501',n:'Análisis y Diseño II',cr:8,e:'curso',p:null}],
            6: [{c:'ING601',n:'Inglés IV',cr:4,e:'block',p:null},
                {c:'EMP601',n:'Emprendedores',cr:4,e:'block',p:null},
                {c:'IO601',n:'Inv. de Operaciones',cr:6,e:'block',p:null},
                {c:'AC601',n:'Arquitectura de Comp.',cr:6,e:'block',p:null},
                {c:'RC601',n:'Redes de Comp. I',cr:6,e:'block',p:null}],
            7: [{c:'PMN701',n:'Prog. Métodos Numéricos',cr:6,e:'block',p:null},
                {c:'SCS701',n:'Sistemas Cliente-Servidor I',cr:6,e:'block',p:null},
                {c:'DI701',n:'Diseño de Interfaces',cr:6,e:'block',p:null}],
            8: [{c:'SCS801',n:'Sistemas Cliente-Servidor II',cr:6,e:'block',p:null},
                {c:'LIU801',n:'Lab. Interfaces de Usuario',cr:6,e:'block',p:null}],
            9: [{c:'PA901',n:'Programación Avanzada',cr:6,e:'block',p:null},
                {c:'SS901',n:'Servicio Social',cr:10,e:'block',p:null}]
          },
          badgeClass(e) {
            return {aprobada:'badge-aprobada',curso:'badge-curso',baja:'badge-baja',block:'badge-block'}[e] ?? 'badge-block';
          },
          badgeLabel(e) {
            return {aprobada:'Aprobada',curso:'En curso',baja:'Baja',block:'Bloqueada'}[e] ?? e;
          }
      }">

    {{-- ── MODAL: Prompt para iniciar sesión ─────────────────────────── --}}
    <div x-show="modalLogin" x-cloak
         @keydown.escape.window="modalLogin = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-blue-950/60 backdrop-blur-sm"
         x-transition.opacity>
        <div @click.outside="modalLogin = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                @svg('lucide-lock', 'w-8 h-8 text-blue-700')
            </div>
            <h3 class="text-lg font-bold text-blue-900 mb-2">Accede a tus datos reales</h3>
            <p class="text-sm text-blue-500 mb-6 leading-relaxed">
                Lo que ves aquí son datos de ejemplo. Inicia sesión o regístrate con tu matrícula
                para ver <strong class="text-blue-700">tu información académica real</strong>.
            </p>
            <a href="{{ route('registro.alumno') }}"
               class="block w-full py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition mb-3">
                Registrarme con mi matrícula
            </a>
            <a href="{{ route('login') }}"
               class="block w-full py-3 bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 text-sm font-semibold rounded-xl transition">
                Ya tengo cuenta — Iniciar sesión
            </a>
            <button @click="modalLogin = false" class="mt-4 text-xs text-blue-400 hover:text-blue-600">
                Seguir explorando el demo
            </button>
        </div>
    </div>

    {{-- ── NAVBAR ─────────────────────────────────────────────────────── --}}
    <nav class="fixed top-0 left-0 right-0 z-40 bg-blue-900/95 backdrop-blur-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-blue-600/60 rounded-xl flex items-center justify-center border border-white/20">
                    @svg('lucide-graduation-cap', 'w-5 h-5 text-white')
                </div>
                <div>
                    <p class="font-bold text-white text-sm leading-none">Sistema de Tutoría</p>
                    <p class="text-blue-300 text-[10px]">UNACAR · ISC</p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm text-blue-200 hover:text-white hover:bg-white/10 rounded-xl transition font-medium">
                    Iniciar sesión
                </a>
                <a href="{{ route('registro.alumno') }}"
                   class="px-4 py-2 text-sm bg-white text-blue-900 hover:bg-blue-50 rounded-xl transition font-semibold">
                    Registrarse
                </a>
            </div>

            {{-- Mobile menu button --}}
            <button @click="menuOpen = !menuOpen" class="sm:hidden p-2 text-white hover:bg-white/10 rounded-xl">
                @svg('lucide-menu', 'w-5 h-5')
            </button>
        </div>
        {{-- Mobile menu --}}
        <div x-show="menuOpen" x-cloak class="sm:hidden border-t border-white/10 px-4 py-3 space-y-2 bg-blue-900">
            <a href="{{ route('login') }}" class="block px-4 py-2.5 text-sm text-blue-200 hover:bg-white/10 rounded-xl">
                Iniciar sesión
            </a>
            <a href="{{ route('registro.alumno') }}" class="block px-4 py-2.5 text-sm bg-white text-blue-900 rounded-xl font-semibold text-center">
                Registrarme con mi matrícula
            </a>
        </div>
    </nav>

    {{-- ── HERO ────────────────────────────────────────────────────────── --}}
    <section class="min-h-screen pt-16 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 relative overflow-hidden">
        <div class="hero-grid absolute inset-0 pointer-events-none"></div>
        {{-- Decoration blobs --}}
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-16 lg:py-24 flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

            {{-- Left: Copy --}}
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 border border-white/20 rounded-full text-blue-200 text-xs font-medium mb-6 fade-up">
                    @svg('lucide-star', 'w-3.5 h-3.5')
                    <span>Plataforma oficial de seguimiento académico</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 fade-up-2">
                    Tu trayectoria<br>
                    académica,<br>
                    <span class="text-blue-300">siempre contigo</span>
                </h1>

                <p class="text-blue-200 text-lg leading-relaxed mb-8 max-w-lg mx-auto lg:mx-0 fade-up-3">
                    Consulta tu malla curricular, calificaciones y alertas de tu tutor.
                    Seguimiento en tiempo real de tu rendimiento académico — todo en un solo lugar.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-12 fade-up-3">
                    <a href="{{ route('registro.alumno') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-blue-900 font-bold rounded-2xl hover:bg-blue-50 transition shadow-lg shadow-blue-900/30 text-sm">
                        @svg('lucide-user-plus', 'w-4 h-4')
                        Soy alumno — Registrarme
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/10 border border-white/30 text-white font-semibold rounded-2xl hover:bg-white/20 transition text-sm">
                        @svg('lucide-log-in', 'w-4 h-4')
                        Iniciar sesión
                    </a>
                </div>

                {{-- Mini stats --}}
                <div class="flex flex-wrap justify-center lg:justify-start gap-6 text-center fade-up-3">
                    <div>
                        <p class="text-2xl font-bold text-white">847</p>
                        <p class="text-xs text-blue-300">Alumnos activos</p>
                    </div>
                    <div class="w-px bg-white/20 self-stretch"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">94.7%</p>
                        <p class="text-xs text-blue-300">Tasa de aprobación</p>
                    </div>
                    <div class="w-px bg-white/20 self-stretch"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">23</p>
                        <p class="text-xs text-blue-300">Tutores activos</p>
                    </div>
                    <div class="w-px bg-white/20 self-stretch"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">1,204</p>
                        <p class="text-xs text-blue-300">Alertas atendidas</p>
                    </div>
                </div>
            </div>

            {{-- Right: App preview card --}}
            <div class="flex-1 w-full max-w-md lg:max-w-xl float">
                <div class="browser-frame rounded-2xl overflow-hidden">
                    {{-- Browser bar --}}
                    <div class="bg-gray-800 px-4 py-2.5 flex items-center gap-3">
                        <div class="flex gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        </div>
                        <div class="flex-1 bg-gray-700 rounded-md px-3 py-1 text-xs text-gray-400 font-mono truncate">
                            🔒 sistema-tutoria.unacar.edu.mx/alumno/dashboard
                        </div>
                    </div>
                    {{-- App navbar --}}
                    <div class="bg-blue-900 px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-blue-600 rounded-lg flex items-center justify-center">
                                @svg('lucide-graduation-cap', 'w-3.5 h-3.5 text-white')
                            </div>
                            <span class="text-white text-xs font-bold">Sistema de Tutoría</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-[9px] font-bold">AF</span>
                            </div>
                            <span class="text-blue-300 text-[10px]">Ana Fernanda</span>
                        </div>
                    </div>
                    {{-- Mini dashboard preview --}}
                    <div class="bg-blue-50 p-3 space-y-2">
                        <div class="grid grid-cols-4 gap-2">
                            <div class="bg-white rounded-xl p-2 text-center border border-blue-100">
                                <p class="text-base font-bold text-blue-700">83.6</p>
                                <p class="text-[9px] text-blue-400">Promedio</p>
                            </div>
                            <div class="bg-white rounded-xl p-2 text-center border border-blue-100">
                                <p class="text-base font-bold text-blue-700">5</p>
                                <p class="text-[9px] text-blue-400">Materias</p>
                            </div>
                            <div class="bg-white rounded-xl p-2 text-center border border-blue-100">
                                <p class="text-base font-bold text-blue-700">124</p>
                                <p class="text-[9px] text-blue-400">Créditos</p>
                            </div>
                            <div class="bg-white rounded-xl p-2 text-center border border-blue-100">
                                <p class="text-base font-bold text-blue-700">5°</p>
                                <p class="text-[9px] text-blue-400">Semestre</p>
                            </div>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-2 flex items-center gap-2">
                            @svg('lucide-alert-triangle', 'w-3.5 h-3.5 text-amber-500 flex-shrink-0')
                            <p class="text-[10px] text-amber-800">Alerta: Caída de calificación en Sist. Eléctricos</p>
                        </div>
                        <div class="bg-white rounded-xl p-2.5 border border-blue-100">
                            <p class="text-[9px] text-blue-400 mb-1.5 font-medium uppercase tracking-wide">Materias activas</p>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-blue-700">Inglés III</span>
                                    <span class="text-[10px] font-bold text-blue-700">86.5</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-blue-700">Programación Visual</span>
                                    <span class="text-[10px] font-bold text-emerald-600">90.0</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-blue-700">Análisis y Diseño II</span>
                                    <span class="text-[10px] font-bold text-blue-700">79.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-blue-300 text-xs mt-3">
                    ✨ Datos de ejemplo — <button @click="modalLogin = true" class="underline hover:text-white">ver mis datos reales →</button>
                </p>
            </div>
        </div>

        {{-- Wave --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0,60 C240,20 480,0 720,20 C960,40 1200,60 1440,40 L1440,60 Z" fill="#ffffff"/>
            </svg>
        </div>
    </section>

    {{-- ── DEMO INTERACTIVO ────────────────────────────────────────────── --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full text-amber-700 text-xs font-semibold mb-4">
                    @svg('lucide-eye', 'w-3.5 h-3.5')
                    <span>Datos de ejemplo — Ana Fernanda López Cruz · Matrícula 220148</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-blue-900 mb-4">Explora el sistema sin registrarte</h2>
                <p class="text-blue-500 max-w-xl mx-auto">
                    Navega por las secciones del sistema con datos ficticios. Cuando quieras ver
                    <strong class="text-blue-700">tu información real</strong>, solo regístrate con tu matrícula.
                </p>
            </div>

            {{-- Browser window con tabs --}}
            <div class="browser-frame rounded-2xl overflow-hidden max-w-5xl mx-auto">
                {{-- Browser chrome --}}
                <div class="bg-gray-900 px-5 py-3 flex items-center gap-4">
                    <div class="flex gap-1.5 flex-shrink-0">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                    </div>
                    <div class="flex-1 bg-gray-700 rounded-lg px-4 py-1.5 text-xs text-gray-300 font-mono">
                        🔒 sistema-tutoria.unacar.edu.mx/alumno/<span x-text="tabDemo"></span>
                    </div>
                    <button @click="modalLogin = true"
                            class="flex-shrink-0 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded-lg font-medium transition">
                        Mis datos reales →
                    </button>
                </div>

                {{-- App navbar (fake) --}}
                <div class="bg-blue-900 px-4 py-2 flex items-center justify-between gap-4 overflow-x-auto">
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="w-7 h-7 bg-blue-600/60 rounded-lg flex items-center justify-center">
                            @svg('lucide-graduation-cap', 'w-4 h-4 text-white')
                        </div>
                        <span class="text-white text-xs font-bold hidden sm:block">Sistema de Tutoría</span>
                    </div>
                    <div class="flex gap-0.5">
                        @foreach(['dashboard' => 'Inicio', 'malla' => 'Malla', 'calificaciones' => 'Calificaciones', 'mensajes' => 'Mensajes'] as $key => $label)
                        <button @click="tabDemo = '{{ $key }}'"
                                :class="tabDemo === '{{ $key }}' ? 'demo-tab-active' : 'demo-tab'"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                    <button @click="modalLogin = true" class="flex-shrink-0 flex items-center gap-1.5">
                        <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-[10px] font-bold">AF</span>
                        </div>
                    </button>
                </div>

                {{-- Content area --}}
                <div class="bg-blue-50 min-h-[500px] p-4 sm:p-5">

                    {{-- ─── TAB: DASHBOARD ──────────────────────────── --}}
                    <div x-show="tabDemo === 'dashboard'" class="space-y-4">

                        {{-- Saludo --}}
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold">A</span>
                            </div>
                            <div>
                                <p class="text-xs text-blue-400">Lunes, 7 de Abril · 2025</p>
                                <h3 class="text-base font-bold text-blue-900">¡Hola, Ana Fernanda! 👋</h3>
                            </div>
                        </div>

                        {{-- KPIs --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-white rounded-2xl p-3.5 border border-blue-100">
                                <div class="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center mb-2">
                                    @svg('lucide-star', 'w-3.5 h-3.5 text-blue-600')
                                </div>
                                <p class="text-2xl font-bold text-blue-700">83.6</p>
                                <p class="text-[11px] text-blue-400">Promedio semestral</p>
                                <p class="text-[10px] text-blue-600 mt-0.5">Buen rendimiento</p>
                            </div>
                            <div class="bg-white rounded-2xl p-3.5 border border-blue-100">
                                <div class="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center mb-2">
                                    @svg('lucide-book-open', 'w-3.5 h-3.5 text-blue-600')
                                </div>
                                <p class="text-2xl font-bold text-blue-700">3</p>
                                <p class="text-[11px] text-blue-400">Materias en curso</p>
                                <p class="text-[10px] text-blue-400 mt-0.5">26 créditos</p>
                            </div>
                            <div class="bg-white rounded-2xl p-3.5 border border-blue-100">
                                <div class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center mb-2">
                                    @svg('lucide-award', 'w-3.5 h-3.5 text-emerald-600')
                                </div>
                                <p class="text-2xl font-bold text-blue-700">124</p>
                                <p class="text-[11px] text-blue-400">Créditos aprobados</p>
                                <p class="text-[10px] text-emerald-500 mt-0.5">38.7% completado</p>
                            </div>
                            <div class="bg-white rounded-2xl p-3.5 border border-blue-100">
                                <div class="w-7 h-7 bg-indigo-50 rounded-lg flex items-center justify-center mb-2">
                                    @svg('lucide-trending-up', 'w-3.5 h-3.5 text-indigo-600')
                                </div>
                                <p class="text-2xl font-bold text-blue-700">5°</p>
                                <p class="text-[11px] text-blue-400">Semestre actual</p>
                                <p class="text-[10px] text-blue-400 mt-0.5">4 semestres restantes</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Alerta --}}
                            <div class="sm:col-span-2 space-y-2">
                                <p class="text-xs font-semibold text-blue-900 flex items-center gap-1.5">
                                    @svg('lucide-bell', 'w-3.5 h-3.5 text-red-500')
                                    Alertas activas
                                </p>
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-3">
                                    <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                        @svg('lucide-alert-triangle', 'w-3.5 h-3.5 text-white')
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-amber-900">Caída de 15 pts en Sist. Eléctricos</p>
                                        <p class="text-[11px] text-amber-700">P1: 80 → P2: 65 · Tu tutor ha sido notificado</p>
                                    </div>
                                </div>
                                <div class="bg-white border border-blue-100 rounded-xl p-3">
                                    <p class="text-[10px] text-blue-400 uppercase tracking-wide mb-2 font-medium">Tutor asignado</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            @svg('lucide-user-circle-2', 'w-4 h-4 text-blue-600')
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-blue-900">Dr. Carlos Ramírez Vidal</p>
                                            <p class="text-[10px] text-blue-400">Depto. de Sistemas Computacionales</p>
                                        </div>
                                        <button @click="modalLogin = true"
                                                class="ml-auto text-[10px] text-blue-600 hover:underline">
                                            Enviar mensaje →
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- Fechas --}}
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-blue-900">Fechas importantes</p>
                                <div class="bg-white border border-blue-100 rounded-xl p-3 space-y-2">
                                    <div class="flex items-center gap-2">
                                        @svg('lucide-calendar-x', 'w-3.5 h-3.5 text-amber-500 flex-shrink-0')
                                        <div>
                                            <p class="text-[10px] font-semibold text-blue-900">Límite bajas</p>
                                            <p class="text-[10px] text-amber-600">30 de Abril · en 23 días</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @svg('lucide-calendar-check', 'w-3.5 h-3.5 text-blue-500 flex-shrink-0')
                                        <div>
                                            <p class="text-[10px] font-semibold text-blue-900">Fin del periodo</p>
                                            <p class="text-[10px] text-blue-500">30 de Junio 2025</p>
                                        </div>
                                    </div>
                                    <div class="bg-blue-700 rounded-xl p-2 text-center cursor-pointer"
                                         @click="modalLogin = true">
                                        <p class="text-[10px] text-blue-200">Elegibilidad SS/PP</p>
                                        <p class="text-xs font-bold text-white">Ver requisitos →</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ─── TAB: MALLA CURRICULAR ───────────────────── --}}
                    <div x-show="tabDemo === 'malla'" class="space-y-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                                @svg('lucide-layout-grid', 'w-4 h-4 text-blue-700')
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-blue-900">Malla Curricular · ISC Plan 2010</h3>
                                <p class="text-xs text-blue-400">124 / 320 créditos aprobados · 38.7%</p>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="bg-white rounded-2xl border border-blue-100 p-3">
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="text-blue-600 font-medium">Avance de la carrera</span>
                                <span class="font-bold text-blue-900">38.7%</span>
                            </div>
                            <div class="w-full bg-blue-50 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-700 h-full rounded-full" style="width:38.7%"></div>
                            </div>
                        </div>

                        {{-- Leyenda --}}
                        <div class="flex flex-wrap gap-2">
                            <span class="badge-aprobada text-[10px] font-medium px-2 py-1 rounded-full">✓ Aprobada</span>
                            <span class="badge-curso text-[10px] font-medium px-2 py-1 rounded-full">▶ En curso</span>
                            <span class="badge-baja text-[10px] font-medium px-2 py-1 rounded-full">↓ Dada de baja</span>
                            <span class="badge-block text-[10px] font-medium px-2 py-1 rounded-full">🔒 Bloqueada</span>
                        </div>

                        {{-- Semester selector --}}
                        <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden">
                            <div class="flex overflow-x-auto scrollbar-hide gap-1 p-2">
                                <template x-for="s in sems" :key="s">
                                    <button @click="semDemo = s"
                                            :class="semDemo === s ? 'bg-blue-700 text-white' : 'text-blue-600 hover:bg-blue-50'"
                                            class="flex-shrink-0 flex flex-col items-center px-3 py-2 rounded-xl transition text-xs">
                                        <span class="text-[9px] opacity-70">Sem</span>
                                        <span class="font-bold" x-text="s"></span>
                                        <span class="text-[9px] opacity-70" x-text="(malla[s] || []).filter(m => m.e === 'aprobada').length + '/' + (malla[s] || []).length"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Subjects grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="mat in (malla[semDemo] || [])" :key="mat.c">
                                <button @click="mat.e !== 'aprobada' ? modalLogin = true : null"
                                        class="w-full text-left bg-white border rounded-xl p-3 hover:shadow-md transition-all"
                                        :class="{
                                            'border-emerald-200': mat.e === 'aprobada',
                                            'border-blue-200':    mat.e === 'curso',
                                            'border-amber-200':   mat.e === 'baja',
                                            'border-gray-200':    mat.e === 'block'
                                        }">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-mono mb-0.5" :class="{
                                                'text-emerald-600': mat.e === 'aprobada',
                                                'text-blue-500':    mat.e === 'curso',
                                                'text-amber-600':   mat.e === 'baja',
                                                'text-gray-400':    mat.e === 'block'
                                            }" x-text="mat.c"></p>
                                            <p class="text-xs font-semibold text-blue-900 leading-tight" x-text="mat.n"></p>
                                            <p class="text-[10px] text-blue-400 mt-0.5" x-text="mat.cr + ' créditos'"></p>
                                        </div>
                                        <div class="flex-shrink-0 flex flex-col items-end gap-1">
                                            <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full" :class="badgeClass(mat.e)" x-text="badgeLabel(mat.e)"></span>
                                            <span x-show="mat.p" class="text-xs font-bold" :class="{'text-emerald-600': mat.p >= 70, 'text-red-500': mat.p < 70}" x-text="mat.p"></span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- ─── TAB: CALIFICACIONES ─────────────────────── --}}
                    <div x-show="tabDemo === 'calificaciones'" class="space-y-4">
                        <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-4 text-white">
                            <p class="text-xs text-blue-200 uppercase tracking-wide mb-1">Promedio del periodo</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-4xl font-bold">83.6</p>
                                <span class="text-sm text-blue-200">/ 100</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/20">
                                <div><p class="text-xs text-blue-200">Calificadas</p><p class="text-lg font-bold">2/5</p></div>
                                <div class="border-l border-white/20 pl-2"><p class="text-xs text-blue-200">Aprobadas</p><p class="text-lg font-bold text-emerald-200">2</p></div>
                                <div class="border-l border-white/20 pl-2"><p class="text-xs text-blue-200">Reprobadas</p><p class="text-lg font-bold text-white/40">0</p></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @php
                            $materias = [
                                ['clave'=>'ING501','nombre'=>'Inglés III','p1'=>85,'p2'=>88,'p3'=>null,'prom'=>86.5,'est'=>'en_curso'],
                                ['clave'=>'TS501','nombre'=>'Teoría de Señales','p1'=>72,'p2'=>75,'p3'=>78,'prom'=>75.0,'est'=>'aprobada'],
                                ['clave'=>'SEE501','nombre'=>'Sist. Eléctricos y Electrónicos','p1'=>80,'p2'=>65,'p3'=>null,'prom'=>72.5,'est'=>'en_curso','alerta'=>true],
                                ['clave'=>'PV501','nombre'=>'Programación Visual','p1'=>90,'p2'=>92,'p3'=>88,'prom'=>90.0,'est'=>'aprobada'],
                                ['clave'=>'ADS501','nombre'=>'Análisis y Diseño II','p1'=>78,'p2'=>80,'p3'=>null,'prom'=>79.0,'est'=>'en_curso'],
                            ];
                            @endphp
                            @foreach($materias as $mat)
                            <div class="bg-white border rounded-2xl p-3 {{ isset($mat['alerta']) ? 'border-amber-200 bg-amber-50/30' : 'border-blue-100' }}">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-mono text-blue-400">{{ $mat['clave'] }}</p>
                                        <p class="text-xs font-semibold text-blue-900 leading-tight">{{ $mat['nombre'] }}</p>
                                    </div>
                                    @if($mat['est'] === 'aprobada')
                                        <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-semibold flex-shrink-0">Aprobada</span>
                                    @else
                                        <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-semibold flex-shrink-0">En curso</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 text-xs">
                                    <div class="text-center"><p class="text-blue-400 text-[9px]">P1</p><p class="font-bold text-blue-900">{{ $mat['p1'] }}</p></div>
                                    <div class="text-center">
                                        <p class="text-blue-400 text-[9px]">P2</p>
                                        <p class="font-bold {{ isset($mat['alerta']) ? 'text-amber-600' : 'text-blue-900' }}">{{ $mat['p2'] }}</p>
                                    </div>
                                    <div class="text-center"><p class="text-blue-400 text-[9px]">P3</p><p class="font-bold text-blue-300">{{ $mat['p3'] ?? '—' }}</p></div>
                                    <div class="ml-auto text-center">
                                        <p class="text-blue-400 text-[9px]">Promedio</p>
                                        <p class="font-extrabold text-sm {{ $mat['prom'] >= 90 ? 'text-emerald-600' : ($mat['prom'] >= 70 ? 'text-blue-700' : 'text-red-600') }}">{{ number_format($mat['prom'],1) }}</p>
                                    </div>
                                </div>
                                @if(isset($mat['alerta']))
                                <div class="mt-2 flex items-center gap-1.5 text-[10px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1">
                                    @svg('lucide-alert-triangle', 'w-3 h-3')
                                    Caída de 15 pts entre P1 y P2 — tu tutor fue notificado
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <button @click="modalLogin = true"
                                class="w-full py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2">
                            @svg('lucide-pen-line', 'w-4 h-4')
                            Capturar mis calificaciones reales
                        </button>
                    </div>

                    {{-- ─── TAB: MENSAJES ───────────────────────────── --}}
                    <div x-show="tabDemo === 'mensajes'" class="space-y-3">
                        <div class="bg-white rounded-2xl border border-blue-100 overflow-hidden">
                            <div class="p-3 border-b border-blue-50 flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-700 font-bold text-xs">CR</span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-blue-900">Dr. Carlos Ramírez Vidal</p>
                                    <p class="text-[10px] text-blue-400">Tutor académico · Sistemas Computacionales</p>
                                </div>
                                <span class="ml-auto text-[9px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">En línea</span>
                            </div>
                            <div class="p-4 space-y-3 min-h-48 bg-slate-50">
                                {{-- Tutor message --}}
                                <div class="flex justify-start">
                                    <div class="max-w-xs">
                                        <div class="bg-white border border-blue-100 rounded-2xl rounded-tl-sm px-4 py-3">
                                            <p class="text-xs text-slate-700 leading-relaxed">
                                                Hola Ana Fernanda, he notado una caída importante en Sistemas Eléctricos. Tu P1 fue de 80 y en P2 obtuviste 65. ¿Podemos agendar una asesoría para esta semana?
                                            </p>
                                        </div>
                                        <p class="text-[9px] text-slate-400 mt-1">Dr. Ramírez · Hace 2 días</p>
                                    </div>
                                </div>
                                {{-- Student reply --}}
                                <div class="flex justify-end">
                                    <div class="max-w-xs">
                                        <div class="bg-blue-600 rounded-2xl rounded-tr-sm px-4 py-3">
                                            <p class="text-xs text-white leading-relaxed">
                                                Buenos días Dr. Ramírez, sí me gustaría mucho. Estoy disponible el viernes a las 3pm.
                                            </p>
                                        </div>
                                        <p class="text-[9px] text-slate-400 text-right mt-1">Ayer</p>
                                    </div>
                                </div>
                                {{-- Tutor reply --}}
                                <div class="flex justify-start">
                                    <div class="max-w-xs">
                                        <div class="bg-white border border-blue-100 rounded-2xl rounded-tl-sm px-4 py-3">
                                            <p class="text-xs text-slate-700 leading-relaxed">
                                                Perfecto, agendado. Te comparto material de apoyo antes de la asesoría. ¡Ánimo! 💪
                                            </p>
                                        </div>
                                        <p class="text-[9px] text-slate-400 mt-1">Dr. Ramírez · Hoy</p>
                                    </div>
                                </div>
                            </div>
                            {{-- Input blocked --}}
                            <div class="p-3 border-t border-blue-50">
                                <button @click="modalLogin = true"
                                        class="w-full py-2.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-xs text-blue-500 flex items-center justify-center gap-2 transition">
                                    @svg('lucide-lock', 'w-3.5 h-3.5')
                                    Inicia sesión para enviar mensajes reales
                                </button>
                            </div>
                        </div>
                    </div>

                </div>{{-- /content area --}}
            </div>{{-- /browser frame --}}

            <p class="text-center text-blue-400 text-xs mt-4">
                ⚡ Todo lo que ves aquí es exactamente lo que verás con tus datos reales al registrarte
            </p>
        </div>
    </section>

    {{-- ── FEATURES ────────────────────────────────────────────────────── --}}
    <section class="py-20 bg-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-blue-900 mb-4">Todo lo que necesitas en un lugar</h2>
                <p class="text-blue-500 max-w-lg mx-auto">Diseñado específicamente para alumnos y tutores de la UNACAR.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                $features = [
                    ['icon'=>'lucide-layout-grid','color'=>'bg-blue-100 text-blue-700','title'=>'Malla Curricular Interactiva','desc'=>'Visualiza todas tus materias por semestre. Consulta prerrequisitos, créditos y el estado de cada asignatura en tiempo real.'],
                    ['icon'=>'lucide-award','color'=>'bg-emerald-100 text-emerald-700','title'=>'Captura de Calificaciones','desc'=>'Registra tus calificaciones parciales y consulta tu promedio actualizado. El sistema detecta automáticamente si apruebas o repruebas.'],
                    ['icon'=>'lucide-bell','color'=>'bg-amber-100 text-amber-700','title'=>'Alertas Automáticas','desc'=>'Tu tutor recibe notificaciones si detecta una caída en tus calificaciones o si tu promedio baja del mínimo requerido.'],
                    ['icon'=>'lucide-message-circle','color'=>'bg-violet-100 text-violet-700','title'=>'Mensajería Directa','desc'=>'Comunícate directamente con tu tutor. Recibe orientación, materiales de apoyo e invitaciones a asesorías desde la plataforma.'],
                    ['icon'=>'lucide-briefcase','color'=>'bg-rose-100 text-rose-700','title'=>'Elegibilidad SS y PP','desc'=>'Consulta en tiempo real si ya cumples los requisitos de créditos y semestre para iniciar tu Servicio Social o Prácticas Profesionales.'],
                    ['icon'=>'lucide-clock','color'=>'bg-cyan-100 text-cyan-700','title'=>'Historial Académico','desc'=>'Accede a tu registro completo de materias cursadas, calificaciones por periodo y evolución de tu promedio semestral.'],
                ];
                @endphp
                @foreach($features as $f)
                <div class="bg-white rounded-2xl border border-blue-100 p-6 card-hover">
                    <div class="w-11 h-11 {{ $f['color'] }} rounded-xl flex items-center justify-center mb-4">
                        @svg($f['icon'], 'w-5 h-5')
                    </div>
                    <h3 class="font-bold text-blue-900 mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm text-blue-500 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── ALUMNOS vs TUTORES ──────────────────────────────────────────── --}}
    <section class="py-20 bg-blue-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">Una plataforma, dos roles</h2>
                <p class="text-blue-300">Diseñada para cubrir las necesidades de alumnos y tutores.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Alumno --}}
                <div class="bg-white/10 border border-white/20 rounded-3xl p-8">
                    <div class="w-12 h-12 bg-blue-600/50 rounded-2xl flex items-center justify-center mb-5 border border-white/20">
                        @svg('lucide-user', 'w-6 h-6 text-white')
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Para Alumnos</h3>
                    <ul class="space-y-3">
                        @foreach(['Ver mi malla curricular completa','Capturar y consultar calificaciones parciales','Recibir alertas de mi tutor','Inscribir y dar de baja materias','Comunicarme directo con mi tutor','Ver mi elegibilidad para SS y Prácticas'] as $item)
                        <li class="flex items-center gap-3 text-blue-200 text-sm">
                            <div class="w-5 h-5 bg-emerald-500/30 rounded-full flex items-center justify-center flex-shrink-0">
                                @svg('lucide-check', 'w-3 h-3 text-emerald-400')
                            </div>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('registro.alumno') }}"
                       class="mt-6 w-full inline-flex items-center justify-center gap-2 py-3 bg-white text-blue-900 font-bold rounded-xl hover:bg-blue-50 transition text-sm">
                        @svg('lucide-user-plus', 'w-4 h-4')
                        Registrarme como alumno
                    </a>
                </div>
                {{-- Tutor --}}
                <div class="bg-white/10 border border-white/20 rounded-3xl p-8">
                    <div class="w-12 h-12 bg-blue-600/50 rounded-2xl flex items-center justify-center mb-5 border border-white/20">
                        @svg('lucide-users', 'w-6 h-6 text-white')
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Para Tutores</h3>
                    <ul class="space-y-3">
                        @foreach(['Monitorear el rendimiento de todos mis alumnos','Recibir alertas automáticas por calificaciones bajas','Enviar mensajes individuales o grupales','Configurar reglas de alerta personalizadas','Ver dashboard con estadísticas del grupo','Generar reportes académicos exportables'] as $item)
                        <li class="flex items-center gap-3 text-blue-200 text-sm">
                            <div class="w-5 h-5 bg-emerald-500/30 rounded-full flex items-center justify-center flex-shrink-0">
                                @svg('lucide-check', 'w-3 h-3 text-emerald-400')
                            </div>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('login') }}"
                       class="mt-6 w-full inline-flex items-center justify-center gap-2 py-3 bg-white/20 border border-white/30 text-white font-semibold rounded-xl hover:bg-white/30 transition text-sm">
                        @svg('lucide-log-in', 'w-4 h-4')
                        Acceso para tutores
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── CÓMO REGISTRARSE ────────────────────────────────────────────── --}}
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-blue-900 mb-4">Regístrate en 3 pasos</h2>
                <p class="text-blue-500">Solo necesitas tu matrícula y tu correo institucional.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                $pasos = [
                    ['num'=>'01','icon'=>'lucide-id-card','color'=>'bg-blue-700','title'=>'Ingresa tu matrícula','desc'=>'Escribe tu número de matrícula. El sistema verificará que seas alumno activo registrado en Control Escolar.'],
                    ['num'=>'02','icon'=>'lucide-mail-check','color'=>'bg-blue-600','title'=>'Verifica tu correo institucional','desc'=>'Recibirás un código de 6 dígitos en tu correo @unacar.edu.mx. Válido por 10 minutos.'],
                    ['num'=>'03','icon'=>'lucide-user-check','color'=>'bg-emerald-600','title'=>'Completa tu perfil','desc'=>'Define tu nombre y contraseña. Listo — accede al sistema con toda tu información académica real.'],
                ];
                @endphp
                @foreach($pasos as $paso)
                <div class="relative">
                    <div class="w-12 h-12 {{ $paso['color'] }} rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                        @svg($paso['icon'], 'w-6 h-6 text-white')
                    </div>
                    <span class="absolute top-0 left-12 ml-1 text-xs font-bold text-blue-300">{{ $paso['num'] }}</span>
                    <h3 class="font-bold text-blue-900 text-base mb-2">{{ $paso['title'] }}</h3>
                    <p class="text-sm text-blue-500 leading-relaxed">{{ $paso['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA FINAL ───────────────────────────────────────────────────── --}}
    <section class="py-20 bg-gradient-to-br from-blue-700 to-blue-900 relative overflow-hidden">
        <div class="absolute inset-0 hero-grid pointer-events-none"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                ¿Listo para ver tu situación académica real?
            </h2>
            <p class="text-blue-200 text-lg mb-8 max-w-xl mx-auto">
                Regístrate con tu matrícula y accede a toda tu información académica de forma segura y gratuita.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('registro.alumno') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-900 font-bold rounded-2xl hover:bg-blue-50 transition shadow-xl text-base">
                    @svg('lucide-user-plus', 'w-5 h-5')
                    Registrarme como alumno
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/15 border border-white/30 text-white font-semibold rounded-2xl hover:bg-white/25 transition text-base">
                    @svg('lucide-log-in', 'w-5 h-5')
                    Ya tengo cuenta
                </a>
            </div>
            <p class="text-blue-400 text-xs mt-6">
                Solo alumnos y tutores registrados en UNACAR · Sistema gratuito y oficial
            </p>
        </div>
    </section>

    {{-- ── FOOTER ──────────────────────────────────────────────────────── --}}
    <footer class="bg-blue-950 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-blue-800 rounded-lg flex items-center justify-center border border-blue-700">
                        @svg('lucide-graduation-cap', 'w-4 h-4 text-blue-300')
                    </div>
                    <div>
                        <p class="font-bold text-white text-sm">Sistema de Tutoría Académica</p>
                        <p class="text-blue-500 text-xs">Universidad Autónoma del Carmen</p>
                    </div>
                </div>
                <div class="flex gap-4 text-xs text-blue-500">
                    <a href="{{ route('login') }}" class="hover:text-white transition">Iniciar sesión</a>
                    <a href="{{ route('registro.alumno') }}" class="hover:text-white transition">Registrarse</a>
                </div>
                <p class="text-xs text-blue-600">© {{ date('Y') }} UNACAR · ISC Plan 2010</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>