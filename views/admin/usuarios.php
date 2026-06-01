<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
    <div>
        <h1 class="text-xl font-bold text-blue-900">Usuarios</h1>
        <p class="text-sm text-blue-400">Lista de todos los usuarios del sistema</p>
    </div>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">Rol</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700">ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    <?php foreach ($usuarios as $u): ?>
                    <tr class="hover:bg-blue-50/30">
                        <td class="px-4 py-3 font-medium text-blue-900"><?= e($u->name) ?></td>
                        <td class="px-4 py-3 text-blue-500"><?= e($u->email) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold
                                <?= $u->rol === 'admin' ? 'bg-purple-100 text-purple-700' :
                                   ($u->rol === 'tutor' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700') ?>">
                                <?= ucfirst($u->rol) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-blue-300 font-mono text-xs">
                            <?= e($u->matricula ?? $u->numero_empleado ?? $u->id) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
