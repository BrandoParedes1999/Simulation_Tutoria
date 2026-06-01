<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro · <?= APP_NAME ?></title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>body{font-family:'Figtree',sans-serif}</style>
</head>
<body class="font-sans antialiased bg-[#f0f7ff] min-h-screen flex items-center justify-center py-10 px-4">
<div class="w-full max-w-lg">
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
            <i data-lucide="graduation-cap" class="w-7 h-7 text-white"></i>
        </div>
        <h1 class="text-2xl font-bold text-blue-900">Crear cuenta de alumno</h1>
        <p class="text-blue-400 text-sm mt-1">Ingresa tu matrícula para continuar</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-blue-100 p-6 sm:p-8">
        <?php if (!empty($errs)): ?>
        <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 space-y-1">
            <?php foreach ($errs as $e): ?><p>• <?= e($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/registro" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Matrícula *</label>
                <input type="text" name="matricula" value="<?= old('matricula') ?>"
                       placeholder="Ej: 190039"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
                <?php if (isset($errs['matricula'])): ?>
                <p class="text-xs text-red-600 mt-1"><?= e($errs['matricula']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Nombre completo *</label>
                <input type="text" name="name" value="<?= old('name') ?>"
                       placeholder="Tu nombre completo"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Correo institucional *</label>
                <input type="email" name="correo_institucional" value="<?= old('correo_institucional') ?>"
                       placeholder="correo@unacar.mx"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Carrera *</label>
                <select name="carrera_id"
                        class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                        required>
                    <option value="">Selecciona tu carrera</option>
                    <?php foreach ($carreras as $c): ?>
                    <option value="<?= $c->id ?>" <?= old('carrera_id') == $c->id ? 'selected' : '' ?>>
                        <?= e($c->nombre) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Contraseña *</label>
                <input type="password" name="password" placeholder="Mínimo 8 caracteres"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required minlength="8">
            </div>

            <div>
                <label class="block text-sm font-medium text-blue-900 mb-1">Confirmar contraseña *</label>
                <input type="password" name="password_confirmation" placeholder="Repite la contraseña"
                       class="w-full px-4 py-2.5 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50/40 text-blue-900"
                       required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-700 text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition mt-2 shadow-md">
                Crear cuenta
            </button>
        </form>
    </div>

    <p class="text-center text-sm text-blue-400 mt-6">
        ¿Ya tienes cuenta? <a href="/login" class="text-blue-600 font-medium hover:underline">Iniciar sesión</a>
    </p>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
