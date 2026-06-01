<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="clock" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Historial Académico</h1>
            <p class="text-xs text-blue-400">Registro completo de materias cursadas</p>
        </div>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-blue-100 p-4 text-center">
            <p class="text-2xl font-bold text-blue-700"><?= count($historial) ?></p>
            <p class="text-xs text-blue-500">Total cursadas</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700"><?= count($aprobadas) ?></p>
            <p class="text-xs text-emerald-600">Aprobadas</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-700"><?= count($reprobadas) ?></p>
            <p class="text-xs text-red-600">Reprobadas</p>
        </div>
    </div>

    <?php if ($promedioGeneral > 0): ?>
    <div class="bg-gradient-to-r from-blue-700 to-blue-800 rounded-2xl p-4 text-white flex items-center justify-between">
        <div>
            <p class="text-xs text-blue-200">Promedio general histórico</p>
            <p class="text-3xl font-bold"><?= num($promedioGeneral) ?></p>
        </div>
        <i data-lucide="trending-up" class="w-10 h-10 text-blue-400"></i>
    </div>
    <?php endif; ?>

    <?php if (empty($historial)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 p-8 text-center">
        <i data-lucide="book" class="w-12 h-12 text-blue-200 mx-auto mb-3"></i>
        <p class="text-blue-400 text-sm">No tienes historial académico registrado aún.</p>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700">Materia</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">Clave</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">Periodo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">Promedio</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    <?php foreach ($historial as $h): ?>
                    <tr class="hover:bg-blue-50/50 transition">
                        <td class="px-4 py-3 text-blue-900 font-medium"><?= e($h->materia_nombre) ?></td>
                        <td class="px-4 py-3 text-center text-blue-400 font-mono text-xs"><?= e($h->clave) ?></td>
                        <td class="px-4 py-3 text-center text-blue-400"><?= e($h->periodo_clave) ?></td>
                        <td class="px-4 py-3 text-center font-bold <?= $h->promedio >= 70 ? 'text-emerald-600' : 'text-red-600' ?>">
                            <?= $h->promedio > 0 ? num($h->promedio) : '—' ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                <?= $h->estatus === 'aprobada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                                <?= ucfirst($h->estatus) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
