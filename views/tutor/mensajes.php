<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex gap-4 h-[calc(100vh-10rem)]">
        <!-- Lista de alumnos -->
        <div class="w-64 flex-shrink-0 bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-3 border-b border-blue-50">
                <h2 class="text-sm font-semibold text-blue-900">Alumnos</h2>
            </div>
            <div class="overflow-y-auto flex-1">
                <?php foreach ($alumnos as $a): ?>
                <a href="/tutor/mensajes?alumno=<?= $a->id ?>"
                   class="flex items-center gap-3 px-3 py-3 hover:bg-blue-50 transition border-b border-blue-50
                          <?= ($alumnoSel && $alumnoSel->id === $a->id) ? 'bg-blue-100' : '' ?>">
                    <div class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= strtoupper(substr($a->name, 0, 1)) ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-blue-900 truncate"><?= e($a->name) ?></p>
                        <p class="text-[10px] text-blue-400"><?= e($a->matricula) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (empty($alumnos)): ?>
                <p class="text-xs text-blue-300 text-center py-6">Sin alumnos asignados</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Conversación -->
        <div class="flex-1 bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col overflow-hidden">
            <?php if (!$alumnoSel): ?>
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center text-blue-300">
                    <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3"></i>
                    <p class="text-sm">Selecciona un alumno para ver la conversación</p>
                </div>
            </div>
            <?php else: ?>
            <!-- Header -->
            <div class="px-4 py-3 border-b border-blue-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-700 rounded-full flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($alumnoSel->name, 0, 1)) ?>
                </div>
                <div>
                    <p class="text-sm font-semibold text-blue-900"><?= e($alumnoSel->name) ?></p>
                    <p class="text-xs text-blue-400"><?= e($alumnoSel->matricula) ?></p>
                </div>
            </div>

            <!-- Mensajes -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chat">
                <?php foreach ($conversacion as $m):
                    $esPropio = $m->remitente_id === $tutor->usuario_id;
                ?>
                <div class="flex <?= $esPropio ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-xs lg:max-w-md px-4 py-2.5 rounded-2xl text-sm
                        <?= $esPropio ? 'bg-blue-700 text-white rounded-br-sm' : 'bg-blue-50 border border-blue-100 text-blue-900 rounded-bl-sm' ?>">
                        <p class="leading-relaxed"><?= nl2br(e($m->cuerpo)) ?></p>
                        <p class="text-[10px] <?= $esPropio ? 'text-blue-200' : 'text-blue-400' ?> mt-1 text-right">
                            <?= fechaHumana($m->created_at) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($conversacion)): ?>
                <p class="text-center text-blue-300 text-sm py-8">Sin mensajes aún. ¡Inicia la conversación!</p>
                <?php endif; ?>
            </div>

            <!-- Formulario -->
            <div class="p-3 border-t border-blue-100">
                <form method="POST" action="/tutor/mensajes/enviar" class="flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="destinatario_id" value="<?= (int)$alumnoSel->usuario_id ?>">
                    <input type="hidden" name="alumno_id" value="<?= (int)$alumnoSel->id ?>">
                    <textarea name="cuerpo" rows="2" placeholder="Escribe un mensaje…"
                              class="flex-1 px-3 py-2 border border-blue-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none"
                              required></textarea>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-700 text-white rounded-xl hover:bg-blue-800 transition flex items-center">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
const chat = document.getElementById('chat');
if (chat) chat.scrollTop = chat.scrollHeight;
</script>
