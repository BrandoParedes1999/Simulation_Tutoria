<?php
$linksAlumno = [
    ['path' => '/alumno/dashboard',      'label' => 'Inicio',    'icon' => 'home'],
    ['path' => '/alumno/malla',          'label' => 'Malla',     'icon' => 'layout-grid'],
    ['path' => '/alumno/materias',       'label' => 'Materias',  'icon' => 'book-open'],
    ['path' => '/alumno/calificaciones', 'label' => 'Calif.',    'icon' => 'award'],
    ['path' => '/alumno/historial',      'label' => 'Historial', 'icon' => 'clock'],
    ['path' => '/alumno/mensajes',       'label' => 'Mensajes',  'icon' => 'mail'],
];

$linksTutor = [
    ['path' => '/tutor/dashboard', 'label' => 'Inicio',   'icon' => 'home'],
    ['path' => '/tutor/alumnos',   'label' => 'Alumnos',  'icon' => 'users'],
    ['path' => '/tutor/alertas',   'label' => 'Alertas',  'icon' => 'alert-triangle'],
    ['path' => '/tutor/mensajes',  'label' => 'Mensajes', 'icon' => 'mail'],
    ['path' => '/tutor/reportes',  'label' => 'Reportes', 'icon' => 'file-text'],
];

$linksAdmin = [
    ['path' => '/admin/dashboard', 'label' => 'Inicio',   'icon' => 'home'],
    ['path' => '/admin/usuarios',  'label' => 'Usuarios', 'icon' => 'users'],
];

$links = match($rol) {
    'alumno' => $linksAlumno,
    'tutor'  => $linksTutor,
    'admin'  => $linksAdmin,
    default  => [],
};

$cp = currentPath();
?>
<header class="sticky top-0 z-40 bg-blue-900 shadow-lg shadow-blue-900/20"
        x-data="{ menuAbierto: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="h-16 flex items-center justify-between">

            <!-- Logo -->
            <a href="/dashboard" class="flex items-center gap-2.5 flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center shadow-md">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-bold text-white text-sm sm:text-base tracking-wide hidden sm:block">Sistema de Tutoría</span>
                <span class="font-bold text-white text-sm tracking-wide sm:hidden">Tutoría</span>
            </a>

            <!-- Nav desktop -->
            <nav class="hidden lg:flex gap-1 flex-1 justify-center max-w-2xl">
                <?php foreach ($links as $link): ?>
                <a href="<?= e($link['path']) ?>"
                   class="px-3 py-1.5 rounded-md text-sm transition-all flex items-center gap-1.5
                          <?= $cp === $link['path'] ? 'bg-blue-700 text-white font-medium' : 'text-blue-200 hover:text-white hover:bg-blue-800' ?>">
                    <i data-lucide="<?= e($link['icon']) ?>" class="w-4 h-4"></i>
                    <?= e($link['label']) ?>
                </a>
                <?php endforeach; ?>
            </nav>

            <!-- Acciones derecha -->
            <div class="flex items-center gap-2" x-data="{ menuUsuario: false }">
                <div class="relative">
                    <button @click="menuUsuario = !menuUsuario"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-800 hover:bg-blue-700 transition">
                        <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                            <?= strtoupper(substr($user->name ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="text-sm text-white hidden sm:block max-w-[120px] truncate">
                            <?= e(explode(' ', $user->name ?? '')[0]) ?>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-blue-300"></i>
                    </button>

                    <div x-show="menuUsuario" @click.outside="menuUsuario = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-blue-100 py-1 z-50">
                        <a href="/perfil" class="flex items-center gap-2 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                            <i data-lucide="user" class="w-4 h-4"></i> Mi Perfil
                        </a>
                        <hr class="my-1 border-blue-100">
                        <form method="POST" action="/logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
