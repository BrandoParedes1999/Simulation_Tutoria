<?php
$linksAlumno = [
    ['path' => '/alumno/dashboard',      'label' => 'Inicio',    'icon' => 'home'],
    ['path' => '/alumno/malla',          'label' => 'Malla',     'icon' => 'layout-grid'],
    ['path' => '/alumno/materias',       'label' => 'Materias',  'icon' => 'book-open'],
    ['path' => '/alumno/calificaciones', 'label' => 'Califi.',   'icon' => 'award'],
    ['path' => '/alumno/mensajes',       'label' => 'Mensajes',  'icon' => 'mail'],
];

$linksTutor = [
    ['path' => '/tutor/dashboard', 'label' => 'Inicio',   'icon' => 'home'],
    ['path' => '/tutor/alumnos',   'label' => 'Alumnos',  'icon' => 'users'],
    ['path' => '/tutor/alertas',   'label' => 'Alertas',  'icon' => 'alert-triangle'],
    ['path' => '/tutor/mensajes',  'label' => 'Mensajes', 'icon' => 'mail'],
    ['path' => '/tutor/reportes',  'label' => 'Reportes', 'icon' => 'file-text'],
];

$links = match($rol) {
    'alumno' => $linksAlumno,
    'tutor'  => $linksTutor,
    default  => [],
};

if (empty($links)) return;
$cp = currentPath();
?>
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-blue-100 shadow-lg lg:hidden">
    <div class="flex items-center justify-around px-2 h-16">
        <?php foreach ($links as $link): ?>
        <a href="<?= e($link['path']) ?>"
           class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition
                  <?= $cp === $link['path'] ? 'text-blue-700' : 'text-blue-400 hover:text-blue-600' ?>">
            <i data-lucide="<?= e($link['icon']) ?>" class="w-5 h-5"></i>
            <span class="text-[10px] font-medium"><?= e($link['label']) ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</nav>
