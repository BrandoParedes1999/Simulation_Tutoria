<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso · <?= APP_NAME ?></title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>[x-cloak]{display:none!important} body{font-family:'Figtree',sans-serif}</style>
</head>
<body class="font-sans antialiased bg-[#f0f7ff]">
<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Panel izquierdo -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700
                lg:w-1/2 lg:min-h-screen px-6 py-8 sm:px-10 sm:py-10 lg:p-12">
        <div class="absolute top-0 right-0 w-64 h-64 lg:w-96 lg:h-96 bg-blue-600/30 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 lg:w-80 lg:h-80 bg-blue-950/40 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col justify-between h-full text-white gap-6 lg:gap-8">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center border border-white/20">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-base sm:text-lg leading-tight">Sistema de Tutoría</p>
                    <p class="text-blue-200 text-xs">Gestión Académica Universitaria</p>
                </div>
            </div>
            <div class="space-y-4 lg:space-y-8 py-4 lg:py-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold leading-tight lg:mb-4">
                        <span class="hidden lg:inline">Seguimiento<br>Académico<br></span>
                        <span class="lg:hidden">Seguimiento Académico </span>
                        <span class="text-blue-300">Inteligente</span>
                    </h1>
                    <p class="hidden lg:block text-blue-200 text-lg leading-relaxed">
                        Plataforma completa para el seguimiento del rendimiento estudiantil.
                    </p>
                </div>
                <div class="hidden lg:block space-y-3">
                    <?php foreach ([
                        ['icon'=>'book-open',    'text'=>'Seguimiento de materias y calificaciones'],
                        ['icon'=>'trending-up',  'text'=>'Dashboards analíticos de rendimiento'],
                        ['icon'=>'users',        'text'=>'Comunicación directa alumno-tutor'],
                    ] as $f): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/15">
                            <i data-lucide="<?= $f['icon'] ?>" class="w-4 h-4 text-blue-200"></i>
                        </div>
                        <p class="text-blue-100 text-sm"><?= e($f['text']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="hidden lg:flex items-center gap-2">
                <div class="h-px flex-1 bg-blue-700"></div>
                <p class="text-blue-400 text-xs">© <?= date('Y') ?> Sistema de Tutoría</p>
                <div class="h-px flex-1 bg-blue-700"></div>
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6 sm:py-12 lg:p-8 -mt-6 lg:mt-0 relative z-20">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl shadow-blue-900/10 border border-blue-100 p-6 sm:p-8">
                <div class="text-center mb-6 sm:mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-blue-900 mb-1">Acceso al Sistema</h2>
                    <p class="text-blue-400 text-sm">Ingresa tus credenciales</p>
                </div>

                <?php foreach ($flash as $f): ?>
                <div class="mb-4 p-3 <?= $f['tipo'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' ?> border rounded-xl text-sm">
                    <?= e($f['mensaje']) ?>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($errs)): ?>
                <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5"></i>
                    <p class="text-sm text-red-700"><?= e(array_values($errs)[0]) ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="/login" class="space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-sm font-medium text-blue-900 mb-1.5">
                            Matrícula, N° empleado o email
                        </label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <i data-lucide="user" class="w-5 h-5 text-blue-300"></i>
                            </div>
                            <input type="text" name="identificador" value="<?= old('identificador') ?>"
                                   placeholder="Ej: 190039"
                                   class="w-full pl-11 pr-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition bg-blue-50/50 text-blue-900 placeholder-blue-300 text-base"
                                   required autofocus>
                        </div>
                    </div>

                    <div x-data="{ mostrar: false }">
                        <label class="block text-sm font-medium text-blue-900 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <i data-lucide="lock" class="w-5 h-5 text-blue-300"></i>
                            </div>
                            <input :type="mostrar ? 'text' : 'password'" name="password" placeholder="••••••••"
                                   class="w-full pl-11 pr-12 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition bg-blue-50/50 text-blue-900 placeholder-blue-300 text-base"
                                   required>
                            <button type="button" @click="mostrar = !mostrar"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 hover:text-blue-600 p-1" tabindex="-1">
                                <i x-show="!mostrar" data-lucide="eye" class="w-5 h-5"></i>
                                <i x-show="mostrar" data-lucide="eye-off" class="w-5 h-5" x-cloak></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-700 text-white py-3.5 rounded-xl font-semibold hover:bg-blue-800 active:bg-blue-900 transition-colors shadow-md shadow-blue-700/25 text-base flex items-center justify-center gap-2">
                        <span>Iniciar Sesión</span>
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
            <div class="mt-6 text-center">
                <p class="text-xs text-blue-400">
                    ¿Alumno nuevo? <a href="/registro" class="text-blue-600 font-medium hover:underline">Crear cuenta</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script>if(typeof lucide!=='undefined')lucide.createIcons();</script>
</body>
</html>
