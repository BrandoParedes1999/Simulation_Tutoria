<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-center gap-3">
        <a href="/tutor/alumnos" class="p-2 rounded-xl hover:bg-blue-50 text-blue-400 hover:text-blue-700 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900"><?= e($alumno->name) ?></h1>
            <p class="text-xs text-blue-400"><?= e($alumno->matricula) ?> · <?= e($alumno->carrera_nombre) ?></p>
        </div>
    </div>

    <!-- Info básica -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([
            ['label'=>'Semestre',  'val'=>$alumno->semestre_actual],
            ['label'=>'Promedio',  'val'=>num((float)$alumno->promedio_general)],
            ['label'=>'Créditos',  'val'=>(int)$alumno->creditos_aprobados],
            ['label'=>'Estatus',   'val'=>ucfirst($alumno->estatus)],
        ] as $kpi): ?>
        <div class="bg-white rounded-2xl border border-blue-100 p-3 text-center shadow-sm">
            <p class="text-xl font-bold text-blue-700"><?= $kpi['val'] ?></p>
            <p class="text-xs text-blue-500"><?= $kpi['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Alertas -->
    <?php if (!empty($alertas)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
        <h2 class="font-semibold text-blue-900 text-sm mb-3 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i> Alertas
        </h2>
        <div class="space-y-2">
            <?php foreach ($alertas as $al): ?>
            <div class="flex items-start justify-between gap-3 p-3 rounded-xl
                <?= $al->atendida ? 'bg-gray-50 border border-gray-100' :
                   ($al->prioridad === 'critica' ? 'bg-red-50 border border-red-200' :
                   ($al->prioridad === 'media' ? 'bg-amber-50 border border-amber-200' : 'bg-blue-50 border border-blue-200')) ?>">
                <div>
                    <p class="text-xs font-semibold text-blue-900"><?= e($al->titulo) ?></p>
                    <p class="text-[10px] text-blue-500 mt-0.5"><?= e($al->mensaje) ?></p>
                </div>
                <?php if ($al->atendida): ?>
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-semibold whitespace-nowrap">Atendida</span>
                <?php else: ?>
                <form method="POST" action="/tutor/alertas/<?= $al->id ?>/atender">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs bg-blue-700 text-white px-2.5 py-1 rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
                        Atender
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historial de inscripciones -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-blue-50">
            <h2 class="text-sm font-semibold text-blue-900">Historial académico</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-100">
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-blue-700">Materia</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">Periodo</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">P1</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">P2</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">P3</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">Prom.</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-700">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    <?php foreach ($inscripciones as $i): ?>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="px-4 py-2.5 text-blue-900 font-medium text-xs"><?= e($i->materia_nombre) ?></td>
                        <td class="px-4 py-2.5 text-center text-blue-400 text-xs"><?= e($i->periodo_clave) ?></td>
                        <td class="px-4 py-2.5 text-center text-xs"><?= $i->parcial1 ?? '—' ?></td>
                        <td class="px-4 py-2.5 text-center text-xs"><?= $i->parcial2 ?? '—' ?></td>
                        <td class="px-4 py-2.5 text-center text-xs"><?= $i->parcial3 ?? '—' ?></td>
                        <td class="px-4 py-2.5 text-center font-bold text-xs <?= ($i->promedio ?? 0) >= 70 ? 'text-emerald-600' : (($i->promedio ?? 0) > 0 ? 'text-red-600' : 'text-blue-400') ?>">
                            <?= $i->promedio > 0 ? num($i->promedio) : '—' ?>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold
                                <?= $i->estatus === 'aprobada' ? 'bg-emerald-100 text-emerald-700' :
                                   ($i->estatus === 'reprobada' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                                <?= ucfirst($i->estatus) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inscripciones)): ?>
                    <tr><td colspan="7" class="px-4 py-6 text-center text-blue-300 text-sm">Sin inscripciones</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Desasignar -->
    <div class="flex justify-end">
        <form method="POST" action="/tutor/alumnos/<?= $alumno->id ?>/desasignar" onsubmit="return confirm('¿Desasignar a este alumno?')">
            <?= csrf_field() ?>
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-xl hover:bg-red-100 transition border border-red-200">
                <i data-lucide="user-minus" class="w-4 h-4"></i> Desasignar alumno
            </button>
        </form>
    </div>
</div>
