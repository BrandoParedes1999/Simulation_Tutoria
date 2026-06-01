<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="layout-grid" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Malla Curricular</h1>
            <p class="text-xs text-blue-400"><?= e($alumno->carrera_nombre) ?></p>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([
            ['label'=>'Total materias', 'val'=>$totales,         'color'=>'bg-blue-50',    'text'=>'text-blue-700'],
            ['label'=>'Aprobadas',      'val'=>$aprobadas_count, 'color'=>'bg-emerald-50', 'text'=>'text-emerald-700'],
            ['label'=>'En curso',       'val'=>$en_curso_count,  'color'=>'bg-amber-50',   'text'=>'text-amber-700'],
            ['label'=>'Reprobadas',     'val'=>$reprobadas_count,'color'=>'bg-red-50',     'text'=>'text-red-700'],
        ] as $s): ?>
        <div class="<?= $s['color'] ?> rounded-2xl border border-<?= explode('-',$s['color'])[1] ?>-100 p-3 text-center">
            <p class="text-2xl font-bold <?= $s['text'] ?>"><?= $s['val'] ?></p>
            <p class="text-xs <?= $s['text'] ?> opacity-80"><?= $s['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Leyenda -->
    <div class="flex flex-wrap gap-3 text-xs">
        <?php foreach ([
            ['color'=>'bg-emerald-100 border-emerald-300 text-emerald-800', 'label'=>'✓ Aprobada'],
            ['color'=>'bg-blue-100 border-blue-300 text-blue-800',           'label'=>'▶ En curso'],
            ['color'=>'bg-white border-blue-200 text-blue-700',              'label'=>'○ Disponible'],
            ['color'=>'bg-red-100 border-red-300 text-red-800',              'label'=>'✗ Reprobada'],
            ['color'=>'bg-gray-100 border-gray-200 text-gray-500',           'label'=>'🔒 Bloqueada'],
        ] as $l): ?>
        <span class="px-2.5 py-1 rounded-full border <?= $l['color'] ?> font-medium"><?= $l['label'] ?></span>
        <?php endforeach; ?>
    </div>

    <!-- Semestres -->
    <?php foreach ($porSemestre as $sem => $materias): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="bg-blue-700 px-4 py-2.5">
            <h2 class="font-semibold text-white text-sm">Semestre <?= (int)$sem ?></h2>
        </div>
        <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            <?php foreach ($materias as $m):
                $css = match($m->estado) {
                    'aprobada'   => 'bg-emerald-50 border-emerald-200',
                    'en_curso'   => 'bg-blue-50 border-blue-200',
                    'reprobada'  => 'bg-red-50 border-red-200',
                    'disponible' => 'bg-white border-blue-200',
                    default      => 'bg-gray-50 border-gray-200',
                };
                $badge = match($m->estado) {
                    'aprobada'   => '<span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-full">✓</span>',
                    'en_curso'   => '<span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded-full">▶</span>',
                    'reprobada'  => '<span class="text-[10px] font-bold text-red-700 bg-red-100 px-1.5 py-0.5 rounded-full">✗</span>',
                    'disponible' => '<span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded-full">○</span>',
                    default      => '<span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">🔒</span>',
                };
            ?>
            <div class="<?= $css ?> border rounded-xl p-3 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-blue-900 leading-tight"><?= e($m->nombre) ?></p>
                    <p class="text-[10px] text-blue-400 mt-0.5"><?= e($m->clave) ?> · <?= (int)$m->creditos ?> créd.</p>
                    <?php if ($m->estado === 'aprobada' && $m->promedio_inscripcion > 0): ?>
                    <p class="text-[10px] text-emerald-600 font-medium mt-0.5">Promedio: <?= num($m->promedio_inscripcion) ?></p>
                    <?php endif; ?>
                </div>
                <?= $badge ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
