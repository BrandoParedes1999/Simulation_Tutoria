<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="file-text" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Reportes</h1>
            <p class="text-xs text-blue-400">Genera reportes de desempeño académico</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php foreach ([
            ['tipo'=>'individual',  'icon'=>'user',       'label'=>'Individual',  'desc'=>'Reporte detallado de un alumno específico'],
            ['tipo'=>'grupal',      'icon'=>'users',      'label'=>'Grupal',      'desc'=>'Resumen del grupo completo asignado'],
            ['tipo'=>'comparativo', 'icon'=>'bar-chart-2','label'=>'Comparativo', 'desc'=>'Comparación entre alumnos del grupo'],
        ] as $r): ?>
        <a href="?tipo=<?= $r['tipo'] ?>"
           class="bg-white rounded-2xl border <?= $tipo === $r['tipo'] ? 'border-blue-500 shadow-md' : 'border-blue-100' ?> p-5 flex flex-col items-center gap-3 hover:border-blue-400 transition text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i data-lucide="<?= $r['icon'] ?>" class="w-6 h-6 text-blue-700"></i>
            </div>
            <div>
                <p class="font-semibold text-blue-900"><?= $r['label'] ?></p>
                <p class="text-xs text-blue-400 mt-1"><?= $r['desc'] ?></p>
            </div>
            <?php if ($tipo === $r['tipo']): ?>
            <span class="text-xs bg-blue-700 text-white px-2.5 py-0.5 rounded-full font-semibold">Seleccionado</span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($tipo === 'individual'): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
        <h2 class="font-semibold text-blue-900 mb-4">Reporte Individual</h2>
        <?php foreach ($alumnos as $a): ?>
        <div class="flex items-center justify-between py-2 border-b border-blue-50 last:border-0">
            <div>
                <p class="text-sm font-semibold text-blue-900"><?= e($a->name) ?></p>
                <p class="text-xs text-blue-400"><?= e($a->matricula) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold <?= (float)$a->promedio_general >= 70 ? 'text-emerald-600' : 'text-red-600' ?>">
                    <?= (float)$a->promedio_general > 0 ? num((float)$a->promedio_general) : '—' ?>
                </span>
                <a href="/tutor/alumnos/<?= $a->id ?>" class="text-xs text-blue-600 hover:underline">Ver</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($alumnos)): ?>
        <p class="text-sm text-blue-400 text-center py-4">Sin alumnos asignados.</p>
        <?php endif; ?>
    </div>

    <?php elseif ($tipo === 'grupal'): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 space-y-4">
        <h2 class="font-semibold text-blue-900">Reporte Grupal</h2>
        <?php
        $promedios = array_filter(array_column($alumnos, 'promedio_general'), fn($p) => $p > 0);
        $promGrupal = count($promedios) > 0 ? array_sum($promedios) / count($promedios) : 0;
        $aprobados  = count(array_filter($alumnos, fn($a) => (float)$a->promedio_general >= 70));
        ?>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-blue-700"><?= count($alumnos) ?></p>
                <p class="text-xs text-blue-500">Total alumnos</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-emerald-700"><?= num($promGrupal) ?></p>
                <p class="text-xs text-emerald-600">Promedio grupal</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-blue-700"><?= $aprobados ?></p>
                <p class="text-xs text-blue-500">Con promedio aprobatorio</p>
            </div>
        </div>
    </div>

    <?php elseif ($tipo === 'comparativo'): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-blue-50">
            <h2 class="text-sm font-semibold text-blue-900">Comparativo de promedios</h2>
        </div>
        <div class="p-4 space-y-2">
            <?php
            usort($alumnos, fn($a, $b) => (float)$b->promedio_general <=> (float)$a->promedio_general);
            $maxProm = max(array_map(fn($a) => (float)$a->promedio_general, $alumnos) ?: [100]);
            foreach ($alumnos as $a):
                $prom = (float)$a->promedio_general;
                $pct  = $maxProm > 0 ? ($prom / $maxProm) * 100 : 0;
            ?>
            <div>
                <div class="flex justify-between text-xs mb-0.5">
                    <span class="text-blue-900 font-medium"><?= e(explode(' ', $a->name)[0]) . ' ' . e(explode(' ', $a->name)[1] ?? '') ?></span>
                    <span class="font-bold <?= $prom >= 70 ? 'text-emerald-600' : ($prom > 0 ? 'text-red-600' : 'text-blue-300') ?>">
                        <?= $prom > 0 ? num($prom) : '—' ?>
                    </span>
                </div>
                <div class="h-2 bg-blue-50 rounded-full">
                    <div class="h-2 rounded-full <?= $prom >= 90 ? 'bg-emerald-500' : ($prom >= 70 ? 'bg-blue-500' : ($prom > 0 ? 'bg-red-400' : 'bg-gray-200')) ?>"
                         style="width:<?= round($pct) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center text-blue-500 text-sm">
        Selecciona un tipo de reporte para ver el contenido.
    </div>
    <?php endif; ?>
</div>
