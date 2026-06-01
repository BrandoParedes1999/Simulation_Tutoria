<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#1e3a8a">
    <title><?= e($titulo ?? 'Panel') ?> · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        [x-cloak]{display:none!important}
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="font-sans antialiased bg-[#f0f7ff]">

<?php
// Extraer datos del array $data
if (isset($data) && is_array($data)) extract($data, EXTR_SKIP);
$flash = getFlash();
$user  = \App\Auth::user();
$rol   = \App\Auth::rol();
?>

<div class="min-h-screen" x-data="{
    toasts: [],
    add(tipo, mensaje) {
        const id = Date.now();
        this.toasts.push({id, tipo, mensaje});
        setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 3500);
    }
}" id="app">

    <?php require VIEWS_PATH . '/components/header.php'; ?>

    <main class="pb-20 lg:pb-0">
        <?php require VIEWS_PATH . '/' . str_replace('.', '/', $content) . '.php'; ?>
    </main>

    <?php require VIEWS_PATH . '/components/mobile-nav.php'; ?>

    <!-- Toast container -->
    <div class="fixed top-4 left-4 right-4 z-50 space-y-2 lg:left-auto lg:right-4 lg:max-w-sm pointer-events-none">
        <?php foreach ($flash as $f): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             x-transition class="<?= $f['tipo'] === 'success' ? 'bg-emerald-600 border-emerald-700' : ($f['tipo'] === 'error' ? 'bg-red-600 border-red-700' : 'bg-blue-600 border-blue-700') ?> text-white px-4 py-3 rounded-xl shadow-lg border flex items-center gap-2 pointer-events-auto">
            <span class="text-sm font-medium"><?= e($f['mensaje']) ?></span>
        </div>
        <?php endforeach; ?>
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition
                 :class="{'bg-emerald-600 border-emerald-700': toast.tipo==='success', 'bg-red-600 border-red-700': toast.tipo==='error', 'bg-blue-600 border-blue-700': toast.tipo==='info'}"
                 class="text-white px-4 py-3 rounded-xl shadow-lg border flex items-center gap-2 pointer-events-auto">
                <span x-text="toast.mensaje" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>
</div>

<script>
// Inicializar iconos Lucide
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>
