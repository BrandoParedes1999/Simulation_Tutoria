<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4">

    <!-- Saludo -->
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs text-blue-400"><?= diaActual() ?></p>
            <h1 class="text-xl sm:text-2xl font-bold text-blue-900">
                Hola, <?= e(explode(' ', $alumno->name)[0]) ?> 👋
            </h1>
            <p class="text-xs text-blue-500 mt-0.5 truncate">
                Semestre <?= e($alumno->semestre_actual) ?> · <?= e($alumno->carrera_nombre) ?>
                <?php if ($periodo): ?> · <?= e($periodo->clave) ?><?php endif; ?>
            </p>
        </div>
        <div class="hidden md:flex gap-2 flex-shrink-0">
            <a href="/alumno/mensajes"
               class="relative flex items-center gap-1.5 px-3 py-2 bg-white border border-blue-200 rounded-xl text-blue-700 text-sm font-medium hover:bg-blue-50 transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Mensajes
                <?php if ($alertasTotal > 0): ?>
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    <?= $alertasTotal > 9 ? '9+' : $alertasTotal ?>
                </span>
                <?php endif; ?>
            </a>
            <a href="/alumno/malla"
               class="flex items-center gap-1.5 px-3 py-2 bg-blue-700 rounded-xl text-white text-sm font-medium hover:bg-blue-800 transition shadow-md">
                <i data-lucide="layout-grid" class="w-4 h-4"></i> Malla Curricular
            </a>
        </div>
    </div>

    <!-- Anuncios -->
    <?php if (!empty($anuncios)): ?>
    <div class="space-y-2">
        <?php foreach (array_slice($anuncios, 0, 1) as $an): ?>
        <div class="bg-blue-700 text-white rounded-2xl p-4 flex items-start gap-3">
            <i data-lucide="megaphone" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold text-sm"><?= e($an->titulo) ?></p>
                <p class="text-blue-200 text-xs mt-0.5"><?= e($an->contenido) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-blue-50">
                <i data-lucide="star" class="w-3.5 h-3.5 text-blue-600"></i>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2">
                <?= $datosPeriodo['promedio_semestral'] > 0 ? num($datosPeriodo['promedio_semestral']) : '—' ?>
            </p>
            <p class="text-xs font-medium text-blue-900">Promedio semestral</p>
            <p class="text-[10px] <?= $clasificacionPromedio['color'] ?> mt-1"><?= e($clasificacionPromedio['texto']) ?></p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-blue-50">
                <i data-lucide="book-open" class="w-3.5 h-3.5 text-blue-600"></i>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2"><?= (int)$datosPeriodo['materias_en_curso'] ?></p>
            <p class="text-xs font-medium text-blue-900">Materias en curso</p>
            <?php if ($datosPeriodo['materias_en_curso'] === 0): ?>
            <a href="/alumno/materias" class="inline-flex items-center gap-1 mt-1 text-[10px] text-blue-600 font-semibold hover:underline">
                Inscribir materias →
            </a>
            <?php else: ?>
            <p class="text-[10px] text-blue-400 mt-1"><?= (int)$datosPeriodo['creditos_periodo'] ?> créditos este semestre</p>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-emerald-50">
                <i data-lucide="award" class="w-3.5 h-3.5 text-emerald-600"></i>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2"><?= (int)$estadisticas['creditos_aprobados'] ?></p>
            <p class="text-xs font-medium text-blue-900">Créditos aprobados</p>
            <p class="text-[10px] text-blue-400 mt-1"><?= (int)$estadisticas['porcentaje_avance'] ?>% de la carrera completado</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
            <div class="p-1.5 rounded-lg w-fit bg-indigo-50">
                <i data-lucide="trending-up" class="w-3.5 h-3.5 text-indigo-600"></i>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-2"><?= (int)$alumno->semestre_actual ?></p>
            <p class="text-xs font-medium text-blue-900">Semestre actual</p>
            <p class="text-[10px] text-blue-400 mt-1">
                <?= $semestresRestantes === 0 ? 'Último semestre' : $semestresRestantes . ' semestres restantes' ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Alertas -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-blue-900 text-sm flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                    Alertas académicas
                </h2>
                <?php if ($alertasTotal > 3): ?>
                <span class="text-xs text-blue-400"><?= $alertasTotal ?> alertas activas</span>
                <?php endif; ?>
            </div>
            <?php if (empty($alertas)): ?>
            <div class="text-center py-6 text-blue-300">
                <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-emerald-400"></i>
                <p class="text-sm text-emerald-600 font-medium">Sin alertas activas</p>
            </div>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($alertas as $al): ?>
                <div class="flex items-start gap-3 p-3 rounded-xl
                    <?= $al->prioridad === 'critica' ? 'bg-red-50 border border-red-200' :
                       ($al->prioridad === 'media'   ? 'bg-amber-50 border border-amber-200' : 'bg-blue-50 border border-blue-200') ?>">
                    <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                        <?= $al->prioridad === 'critica' ? 'bg-red-500' :
                           ($al->prioridad === 'media'   ? 'bg-amber-500' : 'bg-blue-500') ?>"></div>
                    <div>
                        <p class="text-xs font-semibold <?= $al->prioridad === 'critica' ? 'text-red-800' :
                           ($al->prioridad === 'media' ? 'text-amber-800' : 'text-blue-800') ?>">
                            <?= e($al->titulo) ?>
                        </p>
                        <p class="text-[10px] <?= $al->prioridad === 'critica' ? 'text-red-600' :
                           ($al->prioridad === 'media' ? 'text-amber-600' : 'text-blue-600') ?> mt-0.5">
                            <?= e($al->mensaje) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tutor y elegibilidad -->
        <div class="space-y-3">
            <!-- Tutor -->
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
                <h2 class="font-semibold text-blue-900 text-sm flex items-center gap-2 mb-3">
                    <i data-lucide="user-check" class="w-4 h-4 text-blue-600"></i> Mi Tutor
                </h2>
                <?php if ($alumno->tutor_nombre): ?>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-700 flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr($alumno->tutor_nombre, 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= e($alumno->tutor_nombre) ?></p>
                        <a href="/alumno/mensajes" class="text-xs text-blue-600 hover:underline">Enviar mensaje</a>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-sm text-blue-400">Sin tutor asignado</p>
                <?php endif; ?>
            </div>

            <!-- Elegibilidad -->
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
                <h2 class="font-semibold text-blue-900 text-sm flex items-center gap-2 mb-3">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-600"></i> Elegibilidad
                </h2>
                <div class="space-y-2">
                    <?php
                    $items = [
                        ['label' => 'Prácticas Profesionales', 'data' => $elegibilidad['practicas']],
                        ['label' => 'Servicio Social',         'data' => $elegibilidad['servicio']],
                    ];
                    foreach ($items as $item):
                        $d = $item['data'];
                    ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-blue-700"><?= e($item['label']) ?></span>
                        <?php if ($d['elegible']): ?>
                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Elegible
                        </span>
                        <?php else: ?>
                        <span class="text-amber-600 font-semibold"><?= num($d['porcentaje']) ?>%</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes recientes -->
    <?php if (!empty($mensajesRecientes)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-blue-900 text-sm flex items-center gap-2">
                <i data-lucide="mail" class="w-4 h-4 text-blue-600"></i> Mensajes sin leer
            </h2>
            <a href="/alumno/mensajes" class="text-xs text-blue-600 hover:underline">Ver todos</a>
        </div>
        <div class="space-y-2">
            <?php foreach ($mensajesRecientes as $m): ?>
            <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-xl">
                <div class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($m->remitente_nombre, 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-blue-900"><?= e($m->remitente_nombre) ?></p>
                    <p class="text-xs text-blue-600 truncate"><?= e($m->asunto) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
