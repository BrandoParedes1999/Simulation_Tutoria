<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div>
        <h1 class="text-xl font-bold text-blue-900">Panel de Administración</h1>
        <p class="text-sm text-blue-400 mt-0.5">Gestión del sistema de tutoría</p>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-5 text-center shadow-sm">
            <i data-lucide="users" class="w-8 h-8 text-blue-600 mx-auto mb-2"></i>
            <p class="text-3xl font-bold text-blue-700"><?= (int)$totalAlumnos ?></p>
            <p class="text-sm text-blue-500">Alumnos</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-5 text-center shadow-sm">
            <i data-lucide="user-check" class="w-8 h-8 text-emerald-600 mx-auto mb-2"></i>
            <p class="text-3xl font-bold text-blue-700"><?= (int)$totalTutores ?></p>
            <p class="text-sm text-blue-500">Tutores</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-5 text-center shadow-sm">
            <i data-lucide="alert-triangle" class="w-8 h-8 text-amber-500 mx-auto mb-2"></i>
            <p class="text-3xl font-bold text-amber-600"><?= (int)$totalAlertas ?></p>
            <p class="text-sm text-blue-500">Alertas activas</p>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="/admin/usuarios" class="flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
            <i data-lucide="users" class="w-4 h-4"></i> Gestionar usuarios
        </a>
    </div>
</div>
