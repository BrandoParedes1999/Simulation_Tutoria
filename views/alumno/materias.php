<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
     x-data="{ tab: 'disponibles' }">

    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="book-open" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Inscripción de Materias</h1>
            <p class="text-xs text-blue-400">Gestiona las materias de tu periodo actual</p>
        </div>
    </div>

    <?php if ($periodo): ?>
    <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <p class="text-xs text-blue-400 uppercase tracking-wide">Periodo actual</p>
                <p class="text-base font-bold text-blue-900"><?= e($periodo->nombre) ?></p>
            </div>
            <?php if ($periodoAbierto): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Abierto
            </span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Cerrado
            </span>
            <?php endif; ?>
        </div>
        <?php if ($periodoAbierto && $diasRestantes > 0): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs text-amber-700"><strong><?= $diasRestantes ?> días restantes</strong> para inscribir materias</p>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        <p class="text-sm font-semibold text-red-900">Sin periodo activo</p>
        <p class="text-xs text-red-700 mt-1">No hay ningún periodo académico activo. Contacta a control escolar.</p>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-blue-100">
            <button @click="tab='disponibles'"
                    :class="tab==='disponibles' ? 'text-blue-700 bg-blue-50/50 border-b-2 border-blue-700' : 'text-blue-400 hover:text-blue-600'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Disponibles
                <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-600">
                    <?= count($disponibles) ?>
                </span>
            </button>
            <button @click="tab='carrito'"
                    :class="tab==='carrito' ? 'text-blue-700 bg-blue-50/50 border-b-2 border-blue-700' : 'text-blue-400 hover:text-blue-600'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                Carrito
                <?php if (count($carrito) > 0): ?>
                <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">
                    <?= count($carrito) ?>
                </span>
                <?php endif; ?>
            </button>
            <button @click="tab='inscritas'"
                    :class="tab==='inscritas' ? 'text-blue-700 bg-blue-50/50 border-b-2 border-blue-700' : 'text-blue-400 hover:text-blue-600'"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium transition">
                <i data-lucide="check-square" class="w-4 h-4"></i>
                Inscritas
                <span class="inline-flex items-center justify-center px-1.5 min-w-[20px] h-5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-600">
                    <?= count($inscripciones) ?>
                </span>
            </button>
        </div>

        <!-- Tab: Disponibles -->
        <div x-show="tab==='disponibles'" class="p-4 space-y-2">
            <?php if (!$periodoAbierto): ?>
            <p class="text-sm text-blue-400 text-center py-6">El periodo de inscripción está cerrado.</p>
            <?php elseif (empty($disponibles)): ?>
            <p class="text-sm text-blue-400 text-center py-6">No hay materias disponibles para inscribir.</p>
            <?php else: ?>
            <?php foreach ($disponibles as $m): ?>
            <div class="flex items-center justify-between gap-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
                <div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($m->nombre) ?></p>
                    <p class="text-xs text-blue-400"><?= e($m->clave) ?> · Sem. <?= (int)$m->semestre ?> · <?= (int)$m->creditos ?> créd.</p>
                </div>
                <form method="POST" action="/alumno/materias/inscribir">
                    <?= csrf_field() ?>
                    <input type="hidden" name="materia_id" value="<?= $m->id ?>">
                    <input type="hidden" name="action" value="carrito_add">
                    <button type="submit" class="px-3 py-1.5 bg-blue-700 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 transition">
                        + Carrito
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tab: Carrito -->
        <div x-show="tab==='carrito'" x-cloak class="p-4 space-y-2">
            <?php if (empty($carritoDetalle)): ?>
            <p class="text-sm text-blue-400 text-center py-6">El carrito está vacío.</p>
            <?php else: ?>
            <?php foreach ($carritoDetalle as $m): ?>
            <div class="flex items-center justify-between gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                <div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($m->nombre) ?></p>
                    <p class="text-xs text-blue-400"><?= e($m->clave) ?> · <?= (int)$m->creditos ?> créd.</p>
                </div>
                <form method="POST" action="/alumno/materias/inscribir">
                    <?= csrf_field() ?>
                    <input type="hidden" name="materia_id" value="<?= $m->id ?>">
                    <input type="hidden" name="action" value="carrito_remove">
                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200 transition">
                        Quitar
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
            <form method="POST" action="/alumno/materias/inscribir" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="confirmar">
                <button type="submit"
                        class="w-full bg-blue-700 text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Confirmar inscripción (<?= count($carritoDetalle) ?> materias)
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Tab: Inscritas -->
        <div x-show="tab==='inscritas'" x-cloak class="p-4 space-y-2">
            <?php if (empty($inscripciones)): ?>
            <p class="text-sm text-blue-400 text-center py-6">No tienes materias inscritas este periodo.</p>
            <?php else: ?>
            <?php foreach ($inscripciones as $i): ?>
            <div class="flex items-center justify-between gap-3 p-3 bg-white rounded-xl border border-blue-100">
                <div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($i->materia_nombre) ?></p>
                    <p class="text-xs text-blue-400"><?= e($i->clave) ?> · <?= (int)$i->creditos ?> créd.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        <?= $i->estatus === 'aprobada' ? 'bg-emerald-100 text-emerald-700' :
                           ($i->estatus === 'reprobada' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                        <?= ucfirst($i->estatus) ?>
                    </span>
                    <?php if ($i->estatus === 'en_curso' && $periodoAbierto): ?>
                    <form method="POST" action="/alumno/materias/desinscribir" onsubmit="return confirm('¿Eliminar esta materia?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="inscripcion_id" value="<?= $i->id ?>">
                        <button type="submit" class="text-red-400 hover:text-red-600 transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
