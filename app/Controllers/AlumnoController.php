<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class AlumnoController
{
    private function alumno(): object
    {
        Auth::requireRole('alumno');
        $user = Auth::user();
        $alumno = Database::fetchOne(
            "SELECT a.*, c.nombre as carrera_nombre, c.clave as carrera_clave, c.total_creditos,
                    u.name, u.email,
                    t.id as tutor_id_real,
                    tu.name as tutor_nombre
             FROM alumnos a
             JOIN carreras c ON c.id = a.carrera_id
             JOIN users u ON u.id = a.usuario_id
             LEFT JOIN tutores t ON t.id = a.tutor_id
             LEFT JOIN users tu ON tu.id = t.usuario_id
             WHERE a.usuario_id = ?",
            [$user->id]
        );
        if (!$alumno) redirect('/login');
        return $alumno;
    }

    public function dashboard(): void
    {
        $alumno  = $this->alumno();
        $periodo = Database::fetchOne("SELECT * FROM periodos WHERE es_actual = 1 LIMIT 1");

        // KPIs del periodo actual
        $datosPeriodo = ['promedio_semestral' => 0, 'materias_en_curso' => 0, 'creditos_periodo' => 0];
        if ($periodo) {
            $insc = Database::fetchAll(
                "SELECT i.*, mm.creditos, mm.nombre as materia_nombre
                 FROM inscripciones i
                 JOIN materias_malla mm ON mm.id = i.materia_malla_id
                 WHERE i.alumno_id = ? AND i.periodo_id = ?",
                [$alumno->id, $periodo->id]
            );
            $enCurso     = array_filter($insc, fn($i) => $i->estatus === 'en_curso');
            $conPromedio  = array_filter($insc, fn($i) => $i->promedio > 0);
            $datosPeriodo = [
                'promedio_semestral' => count($conPromedio) > 0
                    ? array_sum(array_column($conPromedio, 'promedio')) / count($conPromedio) : 0,
                'materias_en_curso'  => count($enCurso),
                'creditos_periodo'   => array_sum(array_map(fn($i) => $i->creditos, $enCurso)),
            ];
        }

        // Alertas sin atender
        $alertas = Database::fetchAll(
            "SELECT * FROM alertas WHERE alumno_id = ? AND atendida = 0 ORDER BY FIELD(prioridad,'critica','media','baja') LIMIT 3",
            [$alumno->id]
        );
        $alertasTotal = Database::fetchOne(
            "SELECT COUNT(*) as total FROM alertas WHERE alumno_id = ? AND atendida = 0",
            [$alumno->id]
        )->total;

        // Estadísticas de créditos
        $creditosAprobados = (int)$alumno->creditos_aprobados;
        $totalCreditos     = (int)$alumno->total_creditos ?: 1;
        $porcentajeAvance  = min(100, round(($creditosAprobados / $totalCreditos) * 100));

        $estadisticas = [
            'creditos_aprobados' => $creditosAprobados,
            'porcentaje_avance'  => $porcentajeAvance,
        ];

        // Evolución de promedio por periodo
        $evolucion = Database::fetchAll(
            "SELECT p.clave as sem, AVG(i.promedio) as prom
             FROM inscripciones i
             JOIN periodos p ON p.id = i.periodo_id
             WHERE i.alumno_id = ? AND i.promedio > 0
             GROUP BY p.id, p.clave, p.fecha_inicio
             ORDER BY p.fecha_inicio",
            [$alumno->id]
        );

        // Elegibilidad (práctica/servicio social)
        $elegibilidad = $this->calcularElegibilidad($alumno);

        // Mensajes recientes no leídos
        $mensajesRecientes = Database::fetchAll(
            "SELECT m.*, u.name as remitente_nombre
             FROM mensajes m
             JOIN users u ON u.id = m.remitente_id
             WHERE m.destinatario_id = ? AND m.leido = 0
             ORDER BY m.created_at DESC LIMIT 3",
            [$alumno->usuario_id]
        );

        $semestresRestantes = max(0, 9 - (int)$alumno->semestre_actual);
        $clasificacionPromedio = clasificarPromedio((float)$datosPeriodo['promedio_semestral']);

        $anuncios = Database::fetchAll(
            "SELECT * FROM anuncios WHERE activo = 1 AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) ORDER BY created_at DESC LIMIT 3"
        );

        view('layouts.app', [
            'titulo'   => 'Inicio',
            'content'  => 'alumno.dashboard',
            'data'     => compact(
                'alumno','periodo','datosPeriodo','alertas','alertasTotal',
                'estadisticas','evolucion','elegibilidad','mensajesRecientes',
                'semestresRestantes','clasificacionPromedio','anuncios'
            ),
        ]);
    }

    public function malla(): void
    {
        $alumno   = $this->alumno();
        $materias = Database::fetchAll(
            "SELECT mm.*,
                    i.estatus as estatus_inscripcion, i.promedio as promedio_inscripcion,
                    i.calificacion_final
             FROM materias_malla mm
             LEFT JOIN inscripciones i ON i.materia_malla_id = mm.id AND i.alumno_id = ?
             WHERE mm.carrera_id = ?
             ORDER BY mm.semestre, mm.nombre",
            [$alumno->id, $alumno->carrera_id]
        );

        // Calcular estado de cada materia
        $aprobadas = array_column(
            array_filter($materias, fn($m) => $m->estatus_inscripcion === 'aprobada'),
            'id'
        );

        $materiasConEstado = array_map(function ($m) use ($aprobadas) {
            if ($m->estatus_inscripcion === 'aprobada') {
                $m->estado = 'aprobada';
            } elseif ($m->estatus_inscripcion === 'en_curso') {
                $m->estado = 'en_curso';
            } elseif ($m->estatus_inscripcion === 'reprobada') {
                $m->estado = 'reprobada';
            } else {
                // Verificar prerrequisitos
                $prereqs = Database::fetchAll(
                    "SELECT prerequisito_id FROM prerrequisitos WHERE materia_malla_id = ?",
                    [$m->id]
                );
                $prereqIds = array_column($prereqs, 'prerequisito_id');
                $bloqueada = !empty(array_diff($prereqIds, $aprobadas));
                $m->estado = $bloqueada ? 'bloqueada' : 'disponible';
            }
            return $m;
        }, $materias);

        // Agrupar por semestre
        $porSemestre = [];
        foreach ($materiasConEstado as $m) {
            $porSemestre[$m->semestre][] = $m;
        }
        ksort($porSemestre);

        // Estadísticas
        $totales = count($materiasConEstado);
        $aprobadas_count = count(array_filter($materiasConEstado, fn($m) => $m->estado === 'aprobada'));
        $en_curso_count  = count(array_filter($materiasConEstado, fn($m) => $m->estado === 'en_curso'));
        $reprobadas_count = count(array_filter($materiasConEstado, fn($m) => $m->estado === 'reprobada'));

        view('layouts.app', [
            'titulo'  => 'Malla Curricular',
            'content' => 'alumno.malla',
            'data'    => compact('alumno','porSemestre','totales','aprobadas_count','en_curso_count','reprobadas_count'),
        ]);
    }

    public function materias(): void
    {
        $alumno  = $this->alumno();
        $periodo = Database::fetchOne("SELECT * FROM periodos WHERE es_actual = 1 LIMIT 1");

        $inscripciones = [];
        $disponibles   = [];
        $carrito       = $_SESSION['carrito_' . $alumno->id] ?? [];
        $periodoAbierto = false;
        $diasRestantes  = 0;

        if ($periodo) {
            $periodoAbierto = !empty($periodo->fecha_limite_inscripcion) &&
                              strtotime($periodo->fecha_limite_inscripcion) >= strtotime('today');
            $diasRestantes  = max(0, (int)ceil((strtotime($periodo->fecha_limite_inscripcion ?? 'yesterday') - time()) / 86400));

            $inscripciones = Database::fetchAll(
                "SELECT i.*, mm.nombre as materia_nombre, mm.creditos, mm.semestre as sem,
                        mm.clave, mm.total_horas
                 FROM inscripciones i
                 JOIN materias_malla mm ON mm.id = i.materia_malla_id
                 WHERE i.alumno_id = ? AND i.periodo_id = ?
                 ORDER BY mm.semestre, mm.nombre",
                [$alumno->id, $periodo->id]
            );

            // Materias disponibles (no inscritas, prereqs cumplidos)
            if ($periodoAbierto) {
                $aprobadas = Database::fetchAll(
                    "SELECT materia_malla_id FROM inscripciones WHERE alumno_id = ? AND estatus = 'aprobada'",
                    [$alumno->id]
                );
                $aprobadas_ids = array_column($aprobadas, 'materia_malla_id');
                $inscritas_ids = array_column($inscripciones, 'materia_malla_id');
                $excluir       = array_merge($aprobadas_ids, $inscritas_ids, $carrito);

                $allMaterias = Database::fetchAll(
                    "SELECT mm.* FROM materias_malla mm WHERE mm.carrera_id = ? ORDER BY mm.semestre, mm.nombre",
                    [$alumno->carrera_id]
                );

                foreach ($allMaterias as $m) {
                    if (in_array($m->id, $excluir)) continue;
                    $prereqs = Database::fetchAll(
                        "SELECT prerequisito_id FROM prerrequisitos WHERE materia_malla_id = ?",
                        [$m->id]
                    );
                    $prereqIds = array_column($prereqs, 'prerequisito_id');
                    if (!empty(array_diff($prereqIds, $aprobadas_ids))) continue;
                    $disponibles[] = $m;
                }
            }
        }

        // Materias del carrito con detalle
        $carritoDetalle = [];
        foreach ($carrito as $mid) {
            $mat = Database::fetchOne("SELECT * FROM materias_malla WHERE id = ?", [$mid]);
            if ($mat) $carritoDetalle[] = $mat;
        }

        view('layouts.app', [
            'titulo'  => 'Materias',
            'content' => 'alumno.materias',
            'data'    => compact('alumno','periodo','periodoAbierto','diasRestantes',
                                 'inscripciones','disponibles','carritoDetalle','carrito'),
        ]);
    }

    public function inscribir(): void
    {
        verify_csrf();
        $alumno = $this->alumno();
        $action = $_POST['action'] ?? '';
        $mid    = (int)($_POST['materia_id'] ?? 0);

        if ($action === 'carrito_add') {
            $carrito = $_SESSION['carrito_' . $alumno->id] ?? [];
            if (!in_array($mid, $carrito) && count($carrito) < 10) {
                $carrito[] = $mid;
                $_SESSION['carrito_' . $alumno->id] = $carrito;
            }
            redirect('/alumno/materias');
        }

        if ($action === 'carrito_remove') {
            $carrito = $_SESSION['carrito_' . $alumno->id] ?? [];
            $_SESSION['carrito_' . $alumno->id] = array_values(array_filter($carrito, fn($id) => $id !== $mid));
            redirect('/alumno/materias');
        }

        if ($action === 'confirmar') {
            $carrito = $_SESSION['carrito_' . $alumno->id] ?? [];
            $periodo = Database::fetchOne("SELECT * FROM periodos WHERE es_actual = 1 LIMIT 1");
            if (!$periodo || empty($carrito)) {
                flash('error', 'No hay periodo activo o el carrito está vacío.');
                redirect('/alumno/materias');
            }

            Database::beginTransaction();
            try {
                foreach ($carrito as $mId) {
                    Database::execute(
                        "INSERT INTO inscripciones (alumno_id, materia_malla_id, periodo_id, estatus, created_at, updated_at)
                         VALUES (?, ?, ?, 'en_curso', NOW(), NOW())
                         ON DUPLICATE KEY UPDATE estatus = 'en_curso'",
                        [$alumno->id, $mId, $periodo->id]
                    );
                }
                Database::commit();
                unset($_SESSION['carrito_' . $alumno->id]);
                flash('success', 'Materias inscritas correctamente.');
            } catch (\Exception $e) {
                Database::rollback();
                flash('error', 'Error al inscribir materias.');
            }
            redirect('/alumno/materias');
        }

        redirect('/alumno/materias');
    }

    public function desinscribir(): void
    {
        verify_csrf();
        $alumno = $this->alumno();
        $id     = (int)($_POST['inscripcion_id'] ?? 0);
        Database::execute(
            "DELETE FROM inscripciones WHERE id = ? AND alumno_id = ? AND estatus = 'en_curso'",
            [$id, $alumno->id]
        );
        flash('info', 'Materia eliminada de tus inscripciones.');
        redirect('/alumno/materias');
    }

    public function calificaciones(): void
    {
        $alumno  = $this->alumno();
        $periodo = Database::fetchOne("SELECT * FROM periodos WHERE es_actual = 1 LIMIT 1");
        $materias = [];

        if ($periodo) {
            $materias = Database::fetchAll(
                "SELECT i.*, mm.nombre as materia_nombre, mm.creditos, mm.clave
                 FROM inscripciones i
                 JOIN materias_malla mm ON mm.id = i.materia_malla_id
                 WHERE i.alumno_id = ? AND i.periodo_id = ?
                 ORDER BY mm.semestre, mm.nombre",
                [$alumno->id, $periodo->id]
            );
        }

        $aprobadas   = count(array_filter($materias, fn($m) => $m->estatus === 'aprobada'));
        $reprobadas  = count(array_filter($materias, fn($m) => $m->estatus === 'reprobada'));
        $calificadas = count(array_filter($materias, fn($m) => $m->promedio > 0));
        $promedioArr = array_filter(array_column($materias, 'promedio'), fn($p) => $p > 0);
        $promedioPeriodo = count($promedioArr) > 0 ? array_sum($promedioArr) / count($promedioArr) : 0;

        $resumen = [
            'promedio_periodo' => round($promedioPeriodo, 1),
            'total_materias'   => count($materias),
            'calificadas'      => $calificadas,
            'aprobadas'        => $aprobadas,
            'reprobadas'       => $reprobadas,
        ];

        $flash = getFlash();
        view('layouts.app', [
            'titulo'  => 'Calificaciones',
            'content' => 'alumno.calificaciones',
            'data'    => compact('alumno','periodo','materias','resumen','flash'),
        ]);
    }

    public function guardarCalificaciones(): void
    {
        verify_csrf();
        $alumno = $this->alumno();

        $id = (int)($_POST['inscripcion_id'] ?? 0);
        $p1 = $_POST['parcial1'] !== '' ? (float)$_POST['parcial1'] : null;
        $p2 = $_POST['parcial2'] !== '' ? (float)$_POST['parcial2'] : null;
        $p3 = $_POST['parcial3'] !== '' ? (float)$_POST['parcial3'] : null;

        // Validar rango 0-100
        foreach ([$p1, $p2, $p3] as $p) {
            if ($p !== null && ($p < 0 || $p > 100)) {
                flash('error', 'Las calificaciones deben estar entre 0 y 100.');
                redirect('/alumno/calificaciones');
            }
        }

        $vals = array_filter([$p1, $p2, $p3], fn($v) => $v !== null);
        $promedio = count($vals) > 0 ? array_sum($vals) / count($vals) : null;
        $estatus  = count($vals) >= 3 ? ($promedio >= 70 ? 'aprobada' : 'reprobada') : 'en_curso';

        Database::execute(
            "UPDATE inscripciones SET parcial1=?, parcial2=?, parcial3=?, promedio=?, estatus=?, updated_at=NOW()
             WHERE id=? AND alumno_id=?",
            [$p1, $p2, $p3, $promedio, $estatus, $id, $alumno->id]
        );

        // Actualizar promedio general del alumno
        $promGeneral = Database::fetchOne(
            "SELECT AVG(promedio) as prom FROM inscripciones WHERE alumno_id=? AND estatus='aprobada' AND promedio IS NOT NULL",
            [$alumno->id]
        )->prom ?? 0;
        $creditosAprobados = Database::fetchOne(
            "SELECT COALESCE(SUM(mm.creditos),0) as tot FROM inscripciones i JOIN materias_malla mm ON mm.id=i.materia_malla_id WHERE i.alumno_id=? AND i.estatus='aprobada'",
            [$alumno->id]
        )->tot ?? 0;

        Database::execute(
            "UPDATE alumnos SET promedio_general=?, creditos_aprobados=?, updated_at=NOW() WHERE id=?",
            [round($promGeneral, 2), $creditosAprobados, $alumno->id]
        );

        flash('success', 'Calificaciones guardadas.');
        redirect('/alumno/calificaciones');
    }

    public function historial(): void
    {
        $alumno = $this->alumno();

        $historial = Database::fetchAll(
            "SELECT i.*, mm.nombre as materia_nombre, mm.creditos, mm.clave, mm.semestre as sem,
                    p.nombre as periodo_nombre, p.clave as periodo_clave
             FROM inscripciones i
             JOIN materias_malla mm ON mm.id = i.materia_malla_id
             JOIN periodos p ON p.id = i.periodo_id
             WHERE i.alumno_id = ? AND i.estatus != 'en_curso'
             ORDER BY p.fecha_inicio DESC, mm.semestre",
            [$alumno->id]
        );

        $aprobadas  = array_filter($historial, fn($h) => $h->estatus === 'aprobada');
        $reprobadas = array_filter($historial, fn($h) => $h->estatus === 'reprobada');
        $promArr    = array_filter(array_column($historial, 'promedio'), fn($p) => $p > 0);
        $promedioGeneral = count($promArr) > 0 ? array_sum($promArr) / count($promArr) : 0;

        view('layouts.app', [
            'titulo'  => 'Historial Académico',
            'content' => 'alumno.historial',
            'data'    => compact('alumno','historial','aprobadas','reprobadas','promedioGeneral'),
        ]);
    }

    public function mensajes(): void
    {
        $alumno = $this->alumno();
        $flash  = getFlash();

        $mensajes = Database::fetchAll(
            "SELECT m.*, u.name as remitente_nombre
             FROM mensajes m
             JOIN users u ON u.id = m.remitente_id
             WHERE m.destinatario_id = ?
             ORDER BY m.created_at DESC",
            [$alumno->usuario_id]
        );

        view('layouts.app', [
            'titulo'  => 'Mensajes',
            'content' => 'alumno.mensajes',
            'data'    => compact('alumno','mensajes','flash'),
        ]);
    }

    public function marcarLeido(string $id): void
    {
        verify_csrf();
        $alumno = $this->alumno();
        Database::execute(
            "UPDATE mensajes SET leido = 1, updated_at = NOW() WHERE id = ? AND destinatario_id = ?",
            [(int)$id, $alumno->usuario_id]
        );
        redirect('/alumno/mensajes');
    }

    public function responderMensaje(): void
    {
        verify_csrf();
        $alumno  = $this->alumno();
        $cuerpo  = trim($_POST['cuerpo'] ?? '');
        $tutorId = (int)($_POST['tutor_usuario_id'] ?? 0);

        if (!$cuerpo || !$tutorId) {
            flash('error', 'El mensaje no puede estar vacío.');
            redirect('/alumno/mensajes');
        }

        Database::execute(
            "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, cuerpo, leido, created_at, updated_at)
             VALUES (?, ?, 'Respuesta del alumno', ?, 0, NOW(), NOW())",
            [$alumno->usuario_id, $tutorId, $cuerpo]
        );

        flash('success', 'Mensaje enviado.');
        redirect('/alumno/mensajes');
    }

    private function calcularElegibilidad(object $alumno): array
    {
        $totalCreditos    = (int)$alumno->total_creditos ?: 250;
        $creditosAprobados = (int)$alumno->creditos_aprobados;
        $semestre         = (int)$alumno->semestre_actual;
        $pctAvance        = ($creditosAprobados / $totalCreditos) * 100;

        return [
            'practicas' => [
                'elegible'    => $pctAvance >= 60 && $semestre >= 6,
                'porcentaje'  => round($pctAvance, 1),
                'falta_pct'   => max(0, 60 - $pctAvance),
                'falta_sem'   => max(0, 6 - $semestre),
            ],
            'servicio' => [
                'elegible'    => $pctAvance >= 70 && $semestre >= 5,
                'porcentaje'  => round($pctAvance, 1),
                'falta_pct'   => max(0, 70 - $pctAvance),
                'falta_sem'   => max(0, 5 - $semestre),
            ],
        ];
    }
}
