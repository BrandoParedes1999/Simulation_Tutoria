<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="award" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mis Calificaciones</h1>
            <p class="text-xs text-blue-400">Captura tus calificaciones del periodo actual</p>
        </div>
    </div>

    <!-- KPI banner -->
    <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-5 text-white shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <p class="text-xs text-blue-200 uppercase tracking-wide mb-1">Promedio del periodo</p>
            <?php if ($resumen['promedio_periodo'] > 0): ?>
            <div class="flex items-baseline gap-2">
                <p class="text-4xl font-bold"><?= num($resumen['promedio_periodo']) ?></p>
                <span class="text-sm text-blue-200">/ 100</span>
            </div>
            <?php else: ?>
            <p class="text-4xl font-bold text-blue-300">N/A</p>
            <p class="text-xs text-blue-300 mt-1 italic">Sin calificaciones registradas este periodo</p>
            <?php endif; ?>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/20">
                <div>
                    <p class="text-xs text-blue-200">Calificadas</p>
                    <p class="text-lg font-bold"><?= $resumen['calificadas'] ?>/<?= $resumen['total_materias'] ?></p>
                </div>
                <div class="border-l border-white/20 pl-2">
                    <p class="text-xs text-blue-200">Aprobadas</p>
                    <p class="text-lg font-bold text-emerald-200"><?= $resumen['aprobadas'] ?></p>
                </div>
                <div class="border-l border-white/20 pl-2">
                    <p class="text-xs text-blue-200">Reprobadas</p>
                    <p class="text-lg font-bold <?= $resumen['reprobadas'] > 0 ? 'text-red-200' : 'text-white/50' ?>">
                        <?= $resumen['reprobadas'] ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($materias)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 p-8 text-center">
        <i data-lucide="book-x" class="w-12 h-12 text-blue-200 mx-auto mb-3"></i>
        <p class="text-blue-900 font-semibold">Sin materias inscritas</p>
        <p class="text-sm text-blue-400 mt-1">Inscríbete en materias para registrar calificaciones.</p>
        <a href="/alumno/materias" class="inline-block mt-4 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800">
            Ir a Materias
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($materias as $m): ?>
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden"
             x-data="{ abierta: false }">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="abierta = !abierta">
                <div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($m->materia_nombre) ?></p>
                    <p class="text-xs text-blue-400"><?= e($m->clave) ?> · <?= (int)$m->creditos ?> créditos</p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if ($m->promedio > 0): ?>
                    <div class="text-right">
                        <p class="text-sm font-bold <?= $m->promedio >= 70 ? 'text-emerald-600' : 'text-red-600' ?>">
                            <?= num($m->promedio) ?>
                        </p>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold
                            <?= $m->estatus === 'aprobada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                            <?= ucfirst($m->estatus) ?>
                        </span>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-blue-400 italic">Sin calificar</span>
                    <?php endif; ?>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-blue-400 transition-transform"
                       :class="abierta ? 'rotate-180' : ''"></i>
                </div>
            </div>

            <div x-show="abierta" x-cloak class="border-t border-blue-50 p-4" x-transition>
                <form method="POST" action="/alumno/calificaciones/guardar">
                    <?= csrf_field() ?>
                    <input type="hidden" name="inscripcion_id" value="<?= $m->id ?>">
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <?php foreach (['parcial1'=>'P1','parcial2'=>'P2','parcial3'=>'P3'] as $field=>$label): ?>
                        <div>
                            <label class="block text-xs text-blue-600 font-medium mb-1"><?= $label ?></label>
                            <input type="number" name="<?= $field ?>" min="0" max="100" step="0.1"
                                   value="<?= $m->$field !== null ? $m->$field : '' ?>"
                                   placeholder="—"
                                   class="w-full px-3 py-2 border border-blue-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-400 outline-none"
                                   oninput="calcProm(this.closest('form'))">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-blue-400">
                            Promedio: <strong class="prom-display text-blue-700">
                                <?= $m->promedio > 0 ? num($m->promedio) : '—' ?>
                            </strong>
                        </div>
                        <button type="submit"
                                class="px-4 py-1.5 bg-blue-700 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 transition">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function calcProm(form) {
    const vals = ['parcial1','parcial2','parcial3'].map(n => {
        const v = form.elements[n].value;
        return v !== '' ? parseFloat(v) : null;
    }).filter(v => v !== null);
    const display = form.querySelector('.prom-display');
    if (vals.length > 0) {
        display.textContent = (vals.reduce((a,b)=>a+b,0) / vals.length).toFixed(1);
    } else {
        display.textContent = '—';
    }
}
</script>
