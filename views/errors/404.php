<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 · <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-[#f0f7ff] min-h-screen flex items-center justify-center">
    <div class="text-center px-4">
        <i data-lucide="file-question" class="w-16 h-16 text-blue-200 mx-auto mb-4"></i>
        <h1 class="text-4xl font-bold text-blue-900 mb-2">404</h1>
        <p class="text-blue-500 mb-6">Página no encontrada</p>
        <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-700 text-white rounded-xl font-semibold hover:bg-blue-800 transition">
            <i data-lucide="home" class="w-4 h-4"></i> Ir al inicio
        </a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
