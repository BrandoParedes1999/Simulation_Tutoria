<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 space-y-5">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i data-lucide="mail" class="w-5 h-5 text-blue-700"></i>
        </div>
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-blue-900">Mensajes</h1>
            <p class="text-xs text-blue-400">Comunicación con tu tutor</p>
        </div>
    </div>

    <?php if ($alumno->tutor_nombre): ?>
    <!-- Formulario de respuesta / nuevo mensaje -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
        <p class="text-sm font-semibold text-blue-900 mb-3">Enviar mensaje a <?= e($alumno->tutor_nombre) ?></p>
        <form method="POST" action="/alumno/mensajes/responder" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="tutor_usuario_id" value="<?= (int)$alumno->alumno_tutor_id ?>">
            <textarea name="cuerpo" rows="3" placeholder="Escribe tu mensaje…"
                      class="w-full px-4 py-3 border border-blue-200 rounded-xl text-sm text-blue-900 focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                      required></textarea>
            <div class="flex justify-end">
                <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 transition">
                    <i data-lucide="send" class="w-4 h-4"></i> Enviar
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-700">
        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
        No tienes tutor asignado. Contacta a tu coordinador académico.
    </div>
    <?php endif; ?>

    <!-- Lista de mensajes -->
    <?php if (empty($mensajes)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 p-8 text-center">
        <i data-lucide="inbox" class="w-12 h-12 text-blue-200 mx-auto mb-3"></i>
        <p class="text-blue-400 text-sm">No tienes mensajes.</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($mensajes as $m): ?>
        <div class="bg-white rounded-2xl border <?= !$m->leido ? 'border-blue-400 shadow-md' : 'border-blue-100' ?> p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-700 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        <?= strtoupper(substr($m->remitente_nombre, 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= e($m->remitente_nombre) ?></p>
                        <p class="text-xs text-blue-400"><?= fechaHumana($m->created_at) ?></p>
                    </div>
                </div>
                <?php if (!$m->leido): ?>
                <span class="text-[10px] bg-blue-700 text-white px-2 py-0.5 rounded-full font-semibold">Nuevo</span>
                <?php endif; ?>
            </div>
            <div class="mt-3 pl-12">
                <p class="text-xs text-blue-600 font-medium mb-1"><?= e($m->asunto) ?></p>
                <p class="text-sm text-blue-800 leading-relaxed"><?= nl2br(e($m->cuerpo)) ?></p>
                <?php if (!$m->leido): ?>
                <form method="POST" action="/alumno/mensajes/<?= $m->id ?>/leer" class="mt-2">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs text-blue-500 hover:text-blue-700 underline">
                        Marcar como leído
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
