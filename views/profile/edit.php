<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div>
        <h1 class="text-xl font-bold text-blue-900">Mi Perfil</h1>
        <p class="text-sm text-blue-400">Actualiza tu información personal</p>
    </div>

    <?php foreach (getFlash() as $f): ?>
    <div class="p-3 <?= $f['tipo'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' ?> border rounded-xl text-sm">
        <?= e($f['mensaje']) ?>
    </div>
    <?php endforeach; ?>

    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6">
        <form method="POST" action="/perfil" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Nombre completo</label>
                <input type="text" name="name" value="<?= old('name', $user->name) ?>"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
                <?php if (isset($errs['name'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errs['name']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Email</label>
                <input type="email" name="email" value="<?= old('email', $user->email) ?>"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
                <?php if (isset($errs['email'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errs['email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Teléfono</label>
                <input type="tel" name="telefono" value="<?= old('telefono', $user->telefono ?? '') ?>"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900">
            </div>

            <hr class="border-blue-100">
            <p class="text-xs text-blue-400">Deja en blanco para conservar la contraseña actual.</p>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Nueva contraseña</label>
                <input type="password" name="password" minlength="8"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40">
                <?php if (isset($errs['password'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errs['password']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-700 text-white rounded-xl font-semibold hover:bg-blue-800 transition shadow-md">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
