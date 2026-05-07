<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Ayuda extends Component
{
    public string $busqueda = '';
    public string $categoriaActiva = 'todos';
    public bool $modalAbierto = false;
    public ?array $preguntaActiva = null;

    public function abrirPregunta(int $index): void
    {
        $preguntas = $this->preguntasFiltradas();
        $this->preguntaActiva = $preguntas[$index] ?? null;
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->preguntaActiva = null;
    }

    public function setCategoriaActiva(string $categoria): void
    {
        $this->categoriaActiva = $categoria;
        $this->busqueda = '';
    }

    public function preguntasFiltradas(): array
    {
        $rol = Auth::user()->rol;
        $todas = $rol === 'alumno' ? $this->faqAlumno() : $this->faqTutor();

        $filtradas = $this->categoriaActiva === 'todos'
            ? $todas
            : array_filter($todas, fn($p) => $p['categoria'] === $this->categoriaActiva);

        if (trim($this->busqueda) !== '') {
            $q = mb_strtolower(trim($this->busqueda));
            $filtradas = array_filter($filtradas, function ($p) use ($q) {
                return str_contains(mb_strtolower($p['pregunta']), $q)
                    || str_contains(mb_strtolower($p['respuesta']), $q);
            });
        }

        return array_values($filtradas);
    }

    public function categorias(): array
    {
        $rol = Auth::user()->rol;
        if ($rol === 'alumno') {
            return [
                ['id' => 'todos',          'label' => 'Todas',           'icon' => 'lucide-layout-grid'],
                ['id' => 'cuenta',         'label' => 'Mi cuenta',       'icon' => 'lucide-user-circle'],
                ['id' => 'materias',       'label' => 'Materias',        'icon' => 'lucide-book-open'],
                ['id' => 'calificaciones', 'label' => 'Calificaciones',  'icon' => 'lucide-award'],
                ['id' => 'malla',          'label' => 'Malla curricular','icon' => 'lucide-layout-grid'],
                ['id' => 'mensajes',       'label' => 'Mensajes',        'icon' => 'lucide-mail'],
            ];
        }

        return [
            ['id' => 'todos',     'label' => 'Todas',          'icon' => 'lucide-layout-grid'],
            ['id' => 'alumnos',   'label' => 'Mis alumnos',    'icon' => 'lucide-users'],
            ['id' => 'alertas',   'label' => 'Alertas',        'icon' => 'lucide-alert-triangle'],
            ['id' => 'mensajes',  'label' => 'Mensajes',       'icon' => 'lucide-mail'],
            ['id' => 'reportes',  'label' => 'Reportes',       'icon' => 'lucide-file-text'],
            ['id' => 'cuenta',    'label' => 'Mi cuenta',      'icon' => 'lucide-user-circle'],
        ];
    }

    // ── FAQ ALUMNO ──────────────────────────────────────────────
    private function faqAlumno(): array
    {
        return [
            // CUENTA
            [
                'categoria' => 'cuenta',
                'pregunta'  => '¿Cómo cambio mi foto de perfil?',
                'respuesta' => 'Ve a tu perfil haciendo clic en tu nombre en la barra superior y selecciona "Mi perfil". Ahí encontrarás la opción para subir o cambiar tu foto. Formatos aceptados: JPG, PNG. Tamaño máximo: 2 MB.',
                'icono'     => 'lucide-user-circle',
            ],
            [
                'categoria' => 'cuenta',
                'pregunta'  => '¿Cómo actualizo mi contraseña?',
                'respuesta' => 'En la sección "Mi perfil", desplázate hasta "Seguridad de la cuenta". Ingresa tu contraseña actual y después la nueva. Tu nueva contraseña debe tener mínimo 8 caracteres.',
                'icono'     => 'lucide-lock',
            ],
            [
                'categoria' => 'cuenta',
                'pregunta'  => '¿Qué hago si olvidé mi contraseña?',
                'respuesta' => 'En la pantalla de inicio de sesión, haz clic en "¿Olvidaste tu contraseña?". Ingresa tu correo institucional y recibirás un enlace para restablecerla. Revisa también tu carpeta de spam.',
                'icono'     => 'lucide-key',
            ],
            // MATERIAS
            [
                'categoria' => 'materias',
                'pregunta'  => '¿Cómo me inscribo a materias?',
                'respuesta' => 'Ve a la sección "Materias" en el menú principal. Durante el periodo de inscripción activo, verás el botón "Inscribirse" junto a cada materia disponible. Consulta las materias sugeridas para ti basadas en tu avance curricular.',
                'icono'     => 'lucide-book-open',
            ],
            [
                'categoria' => 'materias',
                'pregunta'  => '¿Por qué no puedo inscribirme a algunas materias?',
                'respuesta' => 'Existen tres razones principales: (1) El periodo de inscripción está cerrado. (2) La materia requiere un prerequisito que aún no has aprobado — puedes verlo en tu malla curricular. (3) No tienes créditos suficientes para cursarla en este semestre.',
                'icono'     => 'lucide-lock',
            ],
            [
                'categoria' => 'materias',
                'pregunta'  => '¿Cuándo abre el periodo de inscripción?',
                'respuesta' => 'Las fechas de inscripción se publican en la sección "Inicio" bajo "Fechas importantes". También recibirás una notificación de tu tutor cuando el periodo esté próximo a abrir.',
                'icono'     => 'lucide-calendar',
            ],
            // CALIFICACIONES
            [
                'categoria' => 'calificaciones',
                'pregunta'  => '¿Por qué mis calificaciones muestran "0"?',
                'respuesta' => 'Si ves "0" en calificaciones puede significar: (1) Eres alumno de nuevo ingreso y aún no hay calificaciones registradas. (2) El docente aún no ha capturado las calificaciones del periodo. Si el periodo ya terminó y sigues viendo "0", contacta a tu tutor.',
                'icono'     => 'lucide-help-circle',
            ],
            [
                'categoria' => 'calificaciones',
                'pregunta'  => '¿Cómo se calcula mi promedio semestral?',
                'respuesta' => 'Tu promedio semestral es el promedio aritmético de las calificaciones finales de todas las materias del periodo actual. La escala es de 0 a 10. Una calificación de 7.0 o superior es aprobatoria.',
                'icono'     => 'lucide-calculator',
            ],
            [
                'categoria' => 'calificaciones',
                'pregunta'  => '¿Puedo ver mis calificaciones parciales?',
                'respuesta' => 'En la sección "Calificaciones" puedes ver las calificaciones que tu docente haya registrado hasta el momento. Las calificaciones finales se publican al término del periodo evaluativo.',
                'icono'     => 'lucide-bar-chart',
            ],
            // MALLA
            [
                'categoria' => 'malla',
                'pregunta'  => '¿Qué significan los colores en mi malla curricular?',
                'respuesta' => 'Los colores indican el estado de cada materia: Verde = Aprobada (ya la cursaste y pasaste). Azul = En curso (la estás cursando este semestre). Naranja = Disponible (puedes inscribirla). Rojo = Reprobada (la cursaste pero no aprobaste). Gris = Bloqueada (tienes prerequisitos pendientes).',
                'icono'     => 'lucide-palette',
            ],
            [
                'categoria' => 'malla',
                'pregunta'  => '¿Cómo sé cuántos créditos me faltan para terminar la carrera?',
                'respuesta' => 'En la parte superior de tu malla curricular verás la barra de avance del programa con el porcentaje completado. Debajo encontrarás el total de créditos aprobados vs. los créditos totales de tu plan de estudios (320 créditos para Ingeniería en Sistemas Computacionales).',
                'icono'     => 'lucide-trending-up',
            ],
            [
                'categoria' => 'malla',
                'pregunta'  => '¿Qué necesito para hacer mi Servicio Social?',
                'respuesta' => 'Para ser elegible al Servicio Social debes haber completado el 70% de tus créditos curriculares (aproximadamente 213 créditos) y estar cursando a partir del 6° semestre. Puedes ver tu avance actual en la sección "Inicio" bajo "Elegibilidad para Servicio Social".',
                'icono'     => 'lucide-briefcase',
            ],
            // MENSAJES
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Cómo envío un mensaje a mi tutor?',
                'respuesta' => 'Ve a la sección "Buzón" en el menú. Si aún no tienes conversaciones, el sistema te mostrará la opción de contactar a tu tutor asignado. También puedes ir a "Inicio" y hacer clic en el botón "Mensajes" para redactar uno nuevo.',
                'icono'     => 'lucide-send',
            ],
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Cada cuánto debo revisar mis mensajes?',
                'respuesta' => 'Te recomendamos revisar tu buzón al menos una vez a la semana. Cuando tengas mensajes no leídos, aparecerá un badge rojo con el número en el ícono de "Buzón" en el menú de navegación.',
                'icono'     => 'lucide-bell',
            ],
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Qué significa que un mensaje sea "Urgente"?',
                'respuesta' => 'Los mensajes marcados como "Urgente" por tu tutor requieren atención inmediata, generalmente relacionados con tu desempeño académico, fechas límite importantes o situaciones que requieren una acción de tu parte. Respóndelos lo antes posible.',
                'icono'     => 'lucide-alert-circle',
            ],
        ];
    }

    // ── FAQ TUTOR ───────────────────────────────────────────────
    private function faqTutor(): array
    {
        return [
            // ALUMNOS
            [
                'categoria' => 'alumnos',
                'pregunta'  => '¿Cómo veo el detalle de un alumno?',
                'respuesta' => 'En la sección "Alumnos", haz clic en el botón "Ver" al final de la fila del alumno que deseas revisar. Verás su historial académico completo, calificaciones por materia, alertas activas y opciones para enviarle un mensaje o agendar una asesoría.',
                'icono'     => 'lucide-user',
            ],
            [
                'categoria' => 'alumnos',
                'pregunta'  => '¿Cómo filtro alumnos en riesgo académico?',
                'respuesta' => 'En la sección "Alumnos" usa el filtro "Todas las alertas" en la barra superior y selecciona "Con alertas activas". También puedes ordenar la tabla por promedio de forma ascendente para ver primero a los alumnos con menor rendimiento.',
                'icono'     => 'lucide-filter',
            ],
            [
                'categoria' => 'alumnos',
                'pregunta'  => '¿Puedo exportar la lista de mis alumnos?',
                'respuesta' => 'Sí. En la sección "Alumnos", al final de la tabla encontrarás el botón "Exportar Lista". Generará un archivo con la información académica básica de todos tus alumnos asignados. Para reportes más detallados, ve a la sección "Reportes".',
                'icono'     => 'lucide-download',
            ],
            [
                'categoria' => 'alumnos',
                'pregunta'  => '¿Cómo sé cuántos alumnos tengo asignados este semestre?',
                'respuesta' => 'En tu "Dashboard" (página de inicio), la primera tarjeta KPI muestra el número total de alumnos asignados con la variación respecto al semestre anterior. También puedes ir a "Alumnos" para ver la lista completa.',
                'icono'     => 'lucide-users',
            ],
            // ALERTAS
            [
                'categoria' => 'alertas',
                'pregunta'  => '¿Cómo se generan las alertas automáticas?',
                'respuesta' => 'El sistema genera alertas automáticamente según las reglas configuradas en el "Centro de Alertas". Las reglas predeterminadas son: promedio < 7.0 en cualquier materia, promedio semestral < 8.0, más de 3 faltas en un mes, y caída > 1.0 punto entre parciales.',
                'icono'     => 'lucide-settings',
            ],
            [
                'categoria' => 'alertas',
                'pregunta'  => '¿Qué significa "Marcar como atendida" en una alerta?',
                'respuesta' => 'Al marcar una alerta como "Atendida" indicas que ya tomaste acción sobre esa situación (contactaste al alumno, agendaste asesoría, etc.). La alerta se archivará y dejará de aparecer en la lista de alertas activas. Esta acción queda registrada con tu nombre y la fecha.',
                'icono'     => 'lucide-check-circle',
            ],
            [
                'categoria' => 'alertas',
                'pregunta'  => '¿Puedo configurar mis propias reglas de alerta?',
                'respuesta' => 'Sí. Al final de la sección "Alertas" encontrarás el panel "Configurar Reglas de Alerta". Puedes activar o desactivar cada regla según tus necesidades. Los cambios se guardan al presionar "Guardar Configuración". Nota: actualmente los umbrales son fijos; personalización de valores estará disponible próximamente.',
                'icono'     => 'lucide-sliders',
            ],
            [
                'categoria' => 'alertas',
                'pregunta'  => '¿Cómo distingo una alerta crítica de una media?',
                'respuesta' => 'Las alertas críticas (ícono rojo ⊙) requieren atención inmediata: promedio por debajo del mínimo aprobatorio o múltiples materias reprobadas. Las alertas medias (ícono amarillo ⚠) son situaciones a monitorear: caída de calificación o inicio de patrón de riesgo. Las bajas son informativas.',
                'icono'     => 'lucide-alert-triangle',
            ],
            // MENSAJES
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Cómo envío un mensaje a un alumno específico?',
                'respuesta' => 'Tienes tres opciones: (1) Desde "Mensajes" → botón "+ Nueva notificación" → selecciona el alumno. (2) Desde "Alertas" → botón "Enviar Mensaje" en la alerta específica. (3) Desde el perfil del alumno → botón "Enviar mensaje". Puedes marcar la prioridad: Urgente, Normal o Informativo.',
                'icono'     => 'lucide-send',
            ],
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Cómo envío un mensaje a todos mis alumnos a la vez?',
                'respuesta' => 'En "Mensajes" → "+ Nueva notificación", cambia la pestaña de "Alumno individual" a "Grupo completo". Redacta tu mensaje y todos tus alumnos asignados lo recibirán simultáneamente. También puedes usar "Mensaje grupal" desde las acciones rápidas del Dashboard.',
                'icono'     => 'lucide-users',
            ],
            [
                'categoria' => 'mensajes',
                'pregunta'  => '¿Qué diferencia hay entre prioridad Urgente, Normal e Informativo?',
                'respuesta' => 'Urgente (rojo): el alumno verá el mensaje destacado y recibirá una notificación prioritaria. Normal (azul): mensaje estándar, aparece en su buzón regular. Informativo (gris): comunicados de bajo impacto, como recordatorios generales. Usa "Urgente" solo cuando requieras respuesta inmediata del alumno.',
                'icono'     => 'lucide-flag',
            ],
            // REPORTES
            [
                'categoria' => 'reportes',
                'pregunta'  => '¿Qué tipos de reporte puedo generar?',
                'respuesta' => 'El módulo de reportes ofrece tres tipos: (1) Individual: detalle académico completo de un alumno específico (calificaciones, historial, alertas). (2) Grupal: estadísticas del grupo asignado (distribución de calificaciones, materias con mayor reprobación). (3) Comparativo: análisis entre semestres o grupos.',
                'icono'     => 'lucide-file-text',
            ],
            [
                'categoria' => 'reportes',
                'pregunta'  => '¿En qué formato se exportan los reportes?',
                'respuesta' => 'Los reportes se exportan en formato PDF, optimizados para impresión y presentación. El nombre del archivo incluye automáticamente el tipo de reporte, el nombre del grupo y la fecha de generación. Puedes descargarlo o compartirlo directamente desde el visor.',
                'icono'     => 'lucide-download',
            ],
            [
                'categoria' => 'reportes',
                'pregunta'  => '¿Puedo exportar el reporte directamente desde el Dashboard?',
                'respuesta' => 'Sí. En el Dashboard hay un botón "Exportar reporte" en la esquina superior derecha que genera directamente un reporte grupal del semestre actual. Para reportes individuales o comparativos, ve a la sección "Reportes" y sigue el asistente de 3 pasos.',
                'icono'     => 'lucide-share',
            ],
            // CUENTA
            [
                'categoria' => 'cuenta',
                'pregunta'  => '¿Cómo actualizo mi información de perfil?',
                'respuesta' => 'Haz clic en tu nombre en la barra de navegación superior y selecciona "Mi perfil". Desde ahí puedes actualizar tu nombre, foto y contraseña. Nota: tu correo institucional y datos académicos son gestionados por el departamento de Control Escolar.',
                'icono'     => 'lucide-user-circle',
            ],
        ];
    }

public function render()
{
    $rol = auth()->user()->rol;

    if ($rol === 'alumno') {
        // Usamos la constante app_path() para llegar a la carpeta física
        return view()->file(app_path('Livewire/Alumno/ayuda.blade.php'), [
            'preguntas'  => $this->preguntasFiltradas(),
            'categorias' => $this->categorias(),
            'rol'        => $rol,
        ])->layout('layouts.app');
    }

    return view()->file(app_path('Livewire/tutor/ayuda.blade.php'), [
        'preguntas'  => $this->preguntasFiltradas(),
        'categorias' => $this->categorias(),
        'rol'        => $rol,
    ])->layout('layouts.app');
}
}