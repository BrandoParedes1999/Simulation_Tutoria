<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Centro de Alertas</h1>
            <p class="text-sm text-blue-400 mt-0.5">Monitoreo de alumnos en riesgo</p>
        </div>
    </div>

    <!-- Contadores -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-red-600"><?= (int)$criticas ?></p>
            <p class="text-xs text-red-700 font-medium">Críticas</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-amber-600"><?= (int)$medias ?></p>
            <p class="text-xs text-amber-700 font-medium">Medias</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-blue-600"><?= (int)$bajas ?></p>
            <p class="text-xs text-blue-700 font-medium">Bajas</p>
        </div>
    </div>

    <!-- Alertas -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-blue-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-blue-900">Alertas activas</h2>
        </div>
        <div class="divide-y divide-blue-50">
            <?php $pendientes = array_filter($todasAlertas, fn($a) => !$a->atendida);
            if (empty($pendientes)): ?>
            <div class="p-8 text-center">
                <i data-lucide="check-circle" class="w-12 h-12 text-emerald-400 mx-auto mb-3"></i>
                <p class="text-emerald-600 font-semibold">Sin alertas pendientes</p>
            </div>
            <?php else:
            foreach ($pendientes as $al):
                $css = $al->prioridad === 'critica' ? 'border-l-4 border-red-500' :
                      ($al->prioridad === 'media'   ? 'border-l-4 border-amber-500' : 'border-l-4 border-blue-400');
            ?>
            <div class="<?= $css ?> px-4 py-3 flex items-start justify-between gap-3"
                 x-data="{ atendiendo: false }">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold <?= $al->prioridad === 'critica' ? 'text-red-700' :
                                    ($al->prioridad === 'media' ? 'text-amber-700' : 'text-blue-700') ?> uppercase tracking-wide">
                            <?= $al->prioridad ?>
                        </span>
                        <span class="text-xs text-blue-400">· <?= e($al->alumno_nombre) ?></span>
                    </div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($al->titulo) ?></p>
                    <p class="text-xs text-blue-500 mt-0.5"><?= e($al->mensaje) ?></p>
                </div>
                <div>
                    <button @click="atendiendo = !atendiendo"
                            class="px-3 py-1.5 bg-blue-700 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 transition whitespace-nowrap">
                        Atender
                    </button>
                    <div x-show="atendiendo" x-cloak x-transition class="mt-2">
                        <form method="POST" action="/tutor/alertas/<?= $al->id ?>/atender" class="space-y-2">
                            <?= csrf_field() ?>
                            <textarea name="nota" rows="2" placeholder="Nota (opcional)…"
                                      class="w-full text-xs px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none resize-none"></textarea>
                            <button type="submit" class="w-full px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                                Confirmar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Alertas atendidas -->
    <?php $atendidas = array_filter($todasAlertas, fn($a) => $a->atendida);
    if (!empty($atendidas)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden"
         x-data="{ mostrar: false }">
        <button @click="mostrar = !mostrar"
                class="w-full px-4 py-3 flex items-center justify-between text-sm text-blue-500 hover:bg-blue-50 transition">
            <span>Alertas atendidas (<?= count($atendidas) ?>)</span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="mostrar ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="mostrar" x-cloak class="divide-y divide-blue-50 border-t border-blue-50">
            <?php foreach ($atendidas as $al): ?>
            <div class="px-4 py-3 opacity-60">
                <p class="text-xs font-semibold text-blue-900"><?= e($al->titulo) ?></p>
                <p class="text-[10px] text-blue-400"><?= e($al->alumno_nombre) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Configurar reglas -->
    <?php if (!empty($reglas)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
        <h2 class="font-semibold text-blue-900 text-sm mb-4 flex items-center gap-2">
            <i data-lucide="settings" class="w-4 h-4 text-blue-600"></i> Reglas de alerta
        </h2>
        <form method="POST" action="/tutor/alertas/reglas/guardar" class="space-y-3">
            <?= csrf_field() ?>
            <?php foreach ($reglas as $i => $r): ?>
            <div class="flex items-center gap-3">
                <input type="hidden" name="reglas[<?= $i ?>][id]" value="<?= $r->id ?>">
                <input type="checkbox" name="reglas[<?= $i ?>][activa]" value="1"
                       <?= $r->activa ? 'checked' : '' ?>
                       class="w-4 h-4 text-blue-600 rounded border-blue-300">
                <div class="flex-1">
                    <p class="text-xs font-medium text-blue-900"><?= e($r->descripcion) ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-blue-400">Umbral:</span>
                    <input type="number" name="reglas[<?= $i ?>][umbral]" value="<?= $r->umbral ?>"
                           step="1" min="0" max="100"
                           class="w-16 px-2 py-1 border border-blue-200 rounded-lg text-xs text-center focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
            </div>
            <?php endforeach; ?>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 transition">
                    Guardar reglas
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
