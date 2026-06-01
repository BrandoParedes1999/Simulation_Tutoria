<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5"
     x-data="{
        busqueda: '',
        semestre: '',
        alerta: '',
        alumnos: <?= toJson(array_map(fn($a) => [
            'id'        => $a->id,
            'nombre'    => $a->name,
            'carrera'   => $a->carrera_nombre ?? '',
            'matricula' => $a->matricula,
            'semestre'  => $a->semestre_actual,
            'promedio'  => (float)$a->promedio_general,
            'alertas'   => $alertasPorAlumno[$a->id] ?? 0,
            'estatus'   => $a->estatus,
        ], $asignados)) ?>,
        get filtrados() {
            return this.alumnos.filter(a => {
                const q = this.busqueda.toLowerCase();
                return (!q || a.nombre.toLowerCase().includes(q) || a.matricula.toLowerCase().includes(q))
                    && (!this.semestre || a.semestre == this.semestre)
                    && (!this.alerta || (this.alerta==='con' && a.alertas>0) || (this.alerta==='sin' && a.alertas===0));
            });
        }
     }">

    <div>
        <h1 class="text-lg sm:text-xl font-bold text-blue-900">Alumnos</h1>
        <p class="text-sm text-blue-400 mt-0.5">Lista de alumnos asignados</p>
    </div>

    <!-- Filtros -->
    <div class="flex flex-col sm:flex-row gap-3">
        <input x-model="busqueda" type="text" placeholder="Buscar por nombre o matrícula…"
               class="flex-1 px-4 py-2 border border-blue-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-300 outline-none">
        <select x-model="semestre" class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700 bg-white focus:outline-none">
            <option value="">Todos los semestres</option>
            <?php
            $sems = array_unique(array_column($asignados, 'semestre_actual'));
            sort($sems);
            foreach ($sems as $s): ?>
            <option value="<?= $s ?>">Semestre <?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <select x-model="alerta" class="px-3 py-2 border border-blue-200 rounded-xl text-sm text-blue-700 bg-white focus:outline-none">
            <option value="">Todas las alertas</option>
            <option value="con">Con alertas</option>
            <option value="sin">Sin alertas</option>
        </select>
    </div>

    <!-- Lista de asignados -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-blue-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-blue-900">Alumnos asignados</h2>
            <span class="text-xs text-blue-400" x-text="filtrados.length + ' de <?= count($asignados) ?>'"></span>
        </div>
        <div class="divide-y divide-blue-50">
            <template x-for="a in filtrados" :key="a.id">
                <div class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-blue-50/50 transition">
                    <a :href="'/tutor/alumnos/' + a.id" class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                             x-text="a.nombre.charAt(0).toUpperCase()"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-blue-900 truncate" x-text="a.nombre"></p>
                            <p class="text-xs text-blue-400" x-text="a.matricula + ' · Sem. ' + a.semestre"></p>
                        </div>
                    </a>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-sm font-bold"
                              :class="a.promedio >= 90 ? 'text-emerald-600' : (a.promedio >= 70 ? 'text-blue-700' : (a.promedio > 0 ? 'text-red-600' : 'text-blue-300'))"
                              x-text="a.promedio > 0 ? a.promedio.toFixed(1) : '—'"></span>
                        <span x-show="a.alertas > 0"
                              class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full">
                            <span x-text="a.alertas"></span>⚠
                        </span>
                    </div>
                </div>
            </template>
            <div x-show="filtrados.length === 0" class="px-4 py-8 text-center text-blue-400 text-sm">
                No se encontraron alumnos.
            </div>
        </div>
    </div>

    <!-- Asignar nuevos alumnos -->
    <?php if (!empty($sinAsignar)): ?>
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden" x-data="{ mostrar: false }">
        <button @click="mostrar = !mostrar"
                class="w-full px-4 py-3 flex items-center justify-between text-sm font-semibold text-blue-700 hover:bg-blue-50 transition">
            <span class="flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Asignar nuevos alumnos
                <span class="text-xs text-blue-400 font-normal">(<?= count($sinAsignar) ?> disponibles)</span>
            </span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="mostrar ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="mostrar" x-cloak x-transition>
            <div class="divide-y divide-blue-50 border-t border-blue-50">
                <?php foreach ($sinAsignar as $a): ?>
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-blue-900"><?= e($a->name) ?></p>
                        <p class="text-xs text-blue-400"><?= e($a->matricula) ?> · <?= e($a->carrera_nombre) ?></p>
                    </div>
                    <form method="POST" action="/tutor/alumnos/<?= $a->id ?>/asignar">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-3 py-1.5 bg-blue-700 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 transition">
                            Asignar
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
