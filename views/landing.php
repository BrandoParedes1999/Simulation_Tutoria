<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tutoría Académica · UNACAR</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        .hero-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 32px 32px;
        }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .float { animation: float 4s ease-in-out infinite; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up   { animation: fadeUp .6s ease-out forwards; }
        .fade-up-2 { animation: fadeUp .6s .15s ease-out both; }
        .fade-up-3 { animation: fadeUp .6s .30s ease-out both; }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(30,58,138,.12); }
        .glow { box-shadow: 0 0 40px rgba(59,130,246,.3); }
    </style>
</head>
<body class="bg-[#f0f7ff]">

<!-- NAV -->
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-blue-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-blue-700 rounded-lg flex items-center justify-center shadow-md">
                <i data-lucide="graduation-cap" class="w-5 h-5 text-white"></i>
            </div>
            <span class="font-bold text-blue-900 text-sm sm:text-base">Sistema de Tutoría</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="#caracteristicas" class="hidden sm:block text-sm text-blue-600 hover:text-blue-800 font-medium">Características</a>
            <a href="/login" class="px-4 py-2 border border-blue-200 text-blue-700 rounded-xl text-sm font-semibold hover:bg-blue-50 transition">
                Iniciar sesión
            </a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 text-white overflow-hidden hero-grid">
    <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/20 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-blue-950/30 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-400/30 rounded-full px-3 py-1 text-xs font-semibold text-blue-200 mb-6 fade-up">
                    <i data-lucide="zap" class="w-3 h-3"></i> Plataforma 2025
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold leading-tight mb-4 fade-up-2">
                    Seguimiento<br>Académico<br>
                    <span class="text-blue-300">Inteligente</span>
                </h1>
                <p class="text-blue-200 text-lg leading-relaxed mb-8 fade-up-3">
                    Plataforma completa para tutores y alumnos universitarios. Seguimiento de calificaciones, alertas académicas y comunicación directa.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 fade-up-3">
                    <a href="/login"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-blue-900 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg shadow-blue-900/20 text-sm">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Iniciar sesión
                    </a>
                    <a href="/registro"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600/30 border border-blue-400/30 text-white rounded-xl font-semibold hover:bg-blue-600/40 transition text-sm">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Soy alumno nuevo
                    </a>
                </div>
            </div>

            <!-- Preview card -->
            <div class="float">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20 glow">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        <span class="text-xs text-blue-300 ml-2">Dashboard del alumno</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <?php foreach ([
                            ['label'=>'Promedio','val'=>'85.3','color'=>'text-emerald-300'],
                            ['label'=>'Materias','val'=>'6','color'=>'text-blue-200'],
                            ['label'=>'Créditos','val'=>'120','color'=>'text-blue-200'],
                            ['label'=>'Semestre','val'=>'6°','color'=>'text-blue-200'],
                        ] as $kpi): ?>
                        <div class="bg-white/10 rounded-xl p-2.5 text-center">
                            <p class="text-xl font-bold <?= $kpi['color'] ?>"><?= $kpi['val'] ?></p>
                            <p class="text-[10px] text-blue-300"><?= $kpi['label'] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bg-emerald-500/20 border border-emerald-400/30 rounded-lg p-2 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-300"></i>
                        <p class="text-xs text-emerald-200">Sin alertas activas · Buen rendimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CARACTERÍSTICAS -->
<section id="caracteristicas" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-blue-900 mb-3">Todo lo que necesitas</h2>
            <p class="text-blue-500 max-w-xl mx-auto">Herramientas diseñadas para alumnos y tutores universitarios.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ([
                ['icon'=>'layout-grid',   'title'=>'Malla Curricular',         'desc'=>'Visualiza tu avance por semestre con estados claros: aprobada, en curso, bloqueada.'],
                ['icon'=>'award',         'title'=>'Calificaciones',            'desc'=>'Registra tus parciales y calcula tu promedio automáticamente.'],
                ['icon'=>'alert-triangle','title'=>'Sistema de Alertas',        'desc'=>'Los tutores reciben alertas automáticas cuando un alumno está en riesgo.'],
                ['icon'=>'message-circle','title'=>'Mensajería',                'desc'=>'Comunicación directa entre alumnos y tutores dentro de la plataforma.'],
                ['icon'=>'file-text',     'title'=>'Reportes Académicos',       'desc'=>'Genera reportes individuales, grupales y comparativos del desempeño.'],
                ['icon'=>'clock',         'title'=>'Historial Completo',        'desc'=>'Consulta tu historial académico completo con todos los periodos cursados.'],
            ] as $feat): ?>
            <div class="bg-white rounded-2xl border border-blue-100 p-5 card-hover shadow-sm">
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="<?= $feat['icon'] ?>" class="w-5 h-5 text-blue-700"></i>
                </div>
                <h3 class="font-bold text-blue-900 mb-2"><?= $feat['title'] ?></h3>
                <p class="text-sm text-blue-500 leading-relaxed"><?= $feat['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-br from-blue-800 to-blue-900 text-white">
    <div class="max-w-2xl mx-auto text-center px-4">
        <i data-lucide="graduation-cap" class="w-12 h-12 text-blue-300 mx-auto mb-4"></i>
        <h2 class="text-3xl font-bold mb-3">¿Listo para empezar?</h2>
        <p class="text-blue-200 mb-8">Inicia sesión con tu matrícula o número de empleado.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/login" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-blue-900 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg">
                <i data-lucide="log-in" class="w-4 h-4"></i> Iniciar sesión
            </a>
            <a href="/registro" class="inline-flex items-center gap-2 px-8 py-3 border border-white/30 text-white rounded-xl font-semibold hover:bg-white/10 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Registrarme
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-blue-950 text-blue-400 py-6 text-center text-xs">
    <p>© <?= date('Y') ?> Sistema de Tutoría Académica · UNACAR</p>
</footer>

<script>lucide.createIcons();</script>
</body>
</html>
