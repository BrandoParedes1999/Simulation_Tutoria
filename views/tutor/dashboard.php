<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs text-blue-400"><?= diaActual() ?></p>
            <h1 class="text-xl sm:text-2xl font-bold text-blue-900">
                Hola, <?= e(explode(' ', $tutor->name)[0]) ?> 👋
            </h1>
            <p class="text-xs text-blue-500 mt-0.5">Portal del Tutor · <?= count($alumnos) ?> alumnos asignados</p>
        </div>
        <a href="/tutor/alumnos" class="flex items-center gap-1.5 px-3 py-2 bg-blue-700 text-white text-sm font-medium rounded-xl hover:bg-blue-800 transition shadow-md">
            <i data-lucide="users" class="w-4 h-4"></i> Mis alumnos
        </a>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-blue-50 mb-2">
                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
            </div>
            <p class="text-3xl font-bold text-blue-700"><?= count($alumnos) ?></p>
            <p class="text-xs font-medium text-blue-900">Alumnos asignados</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-amber-50 mb-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
            </div>
            <p class="text-3xl font-bold text-amber-600"><?= (int)$alertasTotal ?></p>
            <p class="text-xs font-medium text-blue-900">Alertas sin atender</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-emerald-50 mb-2">
                <i data-lucide="star" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <p class="text-3xl font-bold text-blue-700"><?= num($promedioGrupal) ?></p>
            <p class="text-xs font-medium text-blue-900">Promedio grupal</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-red-50 mb-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
            </div>
            <p class="text-3xl font-bold text-red-600"><?= count($alumnosCriticos) ?></p>
            <p class="text-xs font-medium text-blue-900">Alumnos en riesgo</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Distribución de promedios -->
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
            <h2 class="font-semibold text-blue-900 text-sm mb-4">Distribución de promedios</h2>
            <div class="space-y-3">
                <?php
                $total = max(count($alumnos), 1);
                $bars = [
                    ['range' => '90-100', 'count' => $dist['90-100'], 'color' => 'bg-emerald-500', 'label' => 'Excelente'],
                    ['range' => '80-89',  'count' => $dist['80-89'],  'color' => 'bg-blue-500',    'label' => 'Bueno'],
                    ['range' => '70-79',  'count' => $dist['70-79'],  'color' => 'bg-amber-500',   'label' => 'Regular'],
                    ['range' => '<70',    'count' => $dist['<70'],    'color' => 'bg-red-500',     'label' => 'En riesgo'],
                ];
                foreach ($bars as $b):
                    $pct = round(($b['count'] / $total) * 100);
                ?>
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-blue-700 font-medium"><?= $b['range'] ?> — <?= $b['label'] ?></span>
                        <span class="text-blue-400"><?= $b['count'] ?> alumnos</span>
                    </div>
                    <div class="h-2 bg-blue-50 rounded-full overflow-hidden">
                        <div class="h-2 <?= $b['color'] ?> rounded-full" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Alumnos en riesgo -->
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
            <h2 class="font-semibold text-blue-900 text-sm mb-3 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                Alumnos en riesgo
            </h2>
            <?php if (empty($alumnosCriticos)): ?>
            <div class="text-center py-6">
                <i data-lucide="check-circle" class="w-10 h-10 text-emerald-400 mx-auto mb-2"></i>
                <p class="text-sm text-emerald-600">Sin alumnos en riesgo</p>
            </div>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach (array_slice($alumnosCriticos, 0, 5) as $a): ?>
                <a href="/tutor/alumnos/<?= $a->id ?>"
                   class="flex items-center justify-between gap-3 p-2.5 bg-red-50 rounded-xl border border-red-100 hover:bg-red-100 transition">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-red-200 rounded-full flex items-center justify-center text-red-700 text-xs font-bold">
                            <?= strtoupper(substr($a->name, 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-blue-900"><?= e($a->name) ?></p>
                            <p class="text-[10px] text-blue-400">Sem. <?= (int)$a->semestre_actual ?></p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-red-600"><?= num((float)$a->promedio_general) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([
            ['href'=>'/tutor/alertas',  'icon'=>'bell',       'label'=>'Centro de Alertas', 'color'=>'amber'],
            ['href'=>'/tutor/mensajes', 'icon'=>'mail',       'label'=>'Mensajes',           'color'=>'blue'],
            ['href'=>'/tutor/reportes', 'icon'=>'file-text',  'label'=>'Reportes',           'color'=>'indigo'],
            ['href'=>'/tutor/alumnos',  'icon'=>'user-plus',  'label'=>'Gestionar Alumnos',  'color'=>'emerald'],
        ] as $acc): ?>
        <a href="<?= $acc['href'] ?>"
           class="bg-white rounded-2xl border border-<?= $acc['color'] ?>-100 p-4 flex flex-col items-center gap-2 hover:bg-<?= $acc['color'] ?>-50 transition text-center">
            <div class="w-10 h-10 bg-<?= $acc['color'] ?>-100 rounded-xl flex items-center justify-center">
                <i data-lucide="<?= $acc['icon'] ?>" class="w-5 h-5 text-<?= $acc['color'] ?>-600"></i>
            </div>
            <span class="text-xs font-semibold text-blue-900"><?= $acc['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
