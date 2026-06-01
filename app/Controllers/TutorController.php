<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class TutorController
{
    private function tutor(): object
    {
        Auth::requireRole('tutor');
        $user  = Auth::user();
        $tutor = Database::fetchOne(
            "SELECT t.*, u.name, u.email FROM tutores t JOIN users u ON u.id = t.usuario_id WHERE t.usuario_id = ?",
            [$user->id]
        );
        if (!$tutor) redirect('/login');
        return $tutor;
    }

    public function dashboard(): void
    {
        $tutor   = $this->tutor();
        $alumnos = Database::fetchAll(
            "SELECT a.*, u.name, c.nombre as carrera_nombre
             FROM alumnos a JOIN users u ON u.id = a.usuario_id JOIN carreras c ON c.id = a.carrera_id
             WHERE a.tutor_id = ?",
            [$tutor->id]
        );
        $ids = array_column($alumnos, 'id') ?: [0];
        $ph  = implode(',', array_fill(0, count($ids), '?'));

        $alertasTotal = Database::fetchOne(
            "SELECT COUNT(*) as total FROM alertas WHERE alumno_id IN ($ph) AND atendida = 0",
            $ids
        )->total ?? 0;

        $promedios = array_filter(array_column($alumnos, 'promedio_general'), fn($p) => $p > 0);
        $promedioGrupal = count($promedios) > 0 ? round(array_sum($promedios) / count($promedios), 1) : 0;

        $alumnosCriticos = array_filter($alumnos, fn($a) => (float)$a->promedio_general > 0 && (float)$a->promedio_general < 70);

        $dist = [
            '90-100' => count(array_filter($alumnos, fn($a) => (float)$a->promedio_general >= 90)),
            '80-89'  => count(array_filter($alumnos, fn($a) => (float)$a->promedio_general >= 80 && (float)$a->promedio_general < 90)),
            '70-79'  => count(array_filter($alumnos, fn($a) => (float)$a->promedio_general >= 70 && (float)$a->promedio_general < 80)),
            '<70'    => count($alumnosCriticos),
        ];

        $anuncios = Database::fetchAll(
            "SELECT * FROM anuncios WHERE activo = 1 AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) ORDER BY created_at DESC LIMIT 3"
        );

        view('layouts.app', [
            'titulo'  => 'Inicio',
            'content' => 'tutor.dashboard',
            'data'    => compact('tutor','alumnos','alertasTotal','promedioGrupal','dist','alumnosCriticos','anuncios'),
        ]);
    }

    public function alumnos(): void
    {
        $tutor   = $this->tutor();
        $busq    = trim($_GET['q'] ?? '');
        $semFilt = (int)($_GET['sem'] ?? 0);
        $alertFilt = $_GET['alerta'] ?? '';

        $asignados = Database::fetchAll(
            "SELECT a.*, u.name, u.email, c.nombre as carrera_nombre, c.clave as carrera_clave
             FROM alumnos a JOIN users u ON u.id = a.usuario_id JOIN carreras c ON c.id = a.carrera_id
             WHERE a.tutor_id = ?
             ORDER BY u.name",
            [$tutor->id]
        );

        $ids = array_column($asignados, 'id') ?: [0];
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $alertasPorAlumno = [];
        $rows = Database::fetchAll(
            "SELECT alumno_id, COUNT(*) as total FROM alertas WHERE alumno_id IN ($ph) AND atendida=0 GROUP BY alumno_id",
            $ids
        );
        foreach ($rows as $r) $alertasPorAlumno[$r->alumno_id] = $r->total;

        $sinAsignar = Database::fetchAll(
            "SELECT a.*, u.name, u.email, c.nombre as carrera_nombre
             FROM alumnos a JOIN users u ON u.id = a.usuario_id JOIN carreras c ON c.id = a.carrera_id
             WHERE a.tutor_id IS NULL AND a.usuario_id IS NOT NULL
             ORDER BY u.name LIMIT 30"
        );

        $flash = getFlash();
        view('layouts.app', [
            'titulo'  => 'Alumnos',
            'content' => 'tutor.alumnos',
            'data'    => compact('tutor','asignados','sinAsignar','alertasPorAlumno','flash'),
        ]);
    }

    public function detalleAlumno(string $id): void
    {
        $tutor  = $this->tutor();
        $alumno = Database::fetchOne(
            "SELECT a.*, u.name, u.email, c.nombre as carrera_nombre
             FROM alumnos a JOIN users u ON u.id = a.usuario_id JOIN carreras c ON c.id = a.carrera_id
             WHERE a.id = ? AND a.tutor_id = ?",
            [(int)$id, $tutor->id]
        );
        if (!$alumno) redirect('/tutor/alumnos');

        $periodo = Database::fetchOne("SELECT * FROM periodos WHERE es_actual = 1 LIMIT 1");
        $inscripciones = Database::fetchAll(
            "SELECT i.*, mm.nombre as materia_nombre, mm.creditos, p.clave as periodo_clave
             FROM inscripciones i
             JOIN materias_malla mm ON mm.id = i.materia_malla_id
             JOIN periodos p ON p.id = i.periodo_id
             WHERE i.alumno_id = ?
             ORDER BY p.fecha_inicio DESC, mm.nombre",
            [$alumno->id]
        );
        $alertas = Database::fetchAll(
            "SELECT * FROM alertas WHERE alumno_id = ? ORDER BY atendida, FIELD(prioridad,'critica','media','baja')",
            [$alumno->id]
        );

        view('layouts.app', [
            'titulo'  => 'Detalle Alumno',
            'content' => 'tutor.detalle_alumno',
            'data'    => compact('tutor','alumno','periodo','inscripciones','alertas'),
        ]);
    }

    public function asignar(string $id): void
    {
        verify_csrf();
        $tutor  = $this->tutor();
        $alumno = Database::fetchOne("SELECT * FROM alumnos WHERE id = ?", [(int)$id]);
        if (!$alumno || $alumno->tutor_id) {
            flash('error', 'No se puede asignar este alumno.');
            redirect('/tutor/alumnos');
        }
        Database::execute("UPDATE alumnos SET tutor_id=?, updated_at=NOW() WHERE id=?", [$tutor->id, $alumno->id]);
        flash('success', 'Alumno asignado correctamente.');
        redirect('/tutor/alumnos');
    }

    public function desasignar(string $id): void
    {
        verify_csrf();
        $tutor = $this->tutor();
        Database::execute(
            "UPDATE alumnos SET tutor_id=NULL, updated_at=NOW() WHERE id=? AND tutor_id=?",
            [(int)$id, $tutor->id]
        );
        flash('info', 'Alumno desasignado.');
        redirect('/tutor/alumnos');
    }

    public function alertas(): void
    {
        $tutor   = $this->tutor();
        $alumnos = Database::fetchAll("SELECT id FROM alumnos WHERE tutor_id = ?", [$tutor->id]);
        $ids     = array_column($alumnos, 'id') ?: [0];
        $ph      = implode(',', array_fill(0, count($ids), '?'));

        $todasAlertas = Database::fetchAll(
            "SELECT al.*, a.id as alumno_id, u.name as alumno_nombre
             FROM alertas al
             JOIN alumnos a ON a.id = al.alumno_id
             JOIN users u ON u.id = a.usuario_id
             WHERE al.alumno_id IN ($ph)
             ORDER BY al.atendida, FIELD(al.prioridad,'critica','media','baja'), al.created_at DESC",
            $ids
        );

        $criticas = count(array_filter($todasAlertas, fn($a) => !$a->atendida && $a->prioridad === 'critica'));
        $medias   = count(array_filter($todasAlertas, fn($a) => !$a->atendida && $a->prioridad === 'media'));
        $bajas    = count(array_filter($todasAlertas, fn($a) => !$a->atendida && $a->prioridad === 'baja'));

        $reglas = Database::fetchAll("SELECT * FROM reglas_alerta WHERE tutor_id = ?", [$tutor->id]);
        $flash  = getFlash();

        view('layouts.app', [
            'titulo'  => 'Alertas',
            'content' => 'tutor.alertas',
            'data'    => compact('tutor','todasAlertas','criticas','medias','bajas','reglas','flash'),
        ]);
    }

    public function atenderAlerta(string $id): void
    {
        verify_csrf();
        $tutor = $this->tutor();
        $nota  = trim($_POST['nota'] ?? '');
        Database::execute(
            "UPDATE alertas SET atendida=1, atendida_por=?, atendida_en=NOW(), nota_atencion=?, updated_at=NOW() WHERE id=?",
            [$tutor->usuario_id, $nota, (int)$id]
        );
        flash('success', 'Alerta marcada como atendida.');
        redirect('/tutor/alertas');
    }

    public function guardarReglas(): void
    {
        verify_csrf();
        $tutor = $this->tutor();

        // Recibir JSON o form data
        $input = json_decode(file_get_contents('php://input'), true);
        $reglas = $input['reglas'] ?? $_POST['reglas'] ?? [];

        foreach ($reglas as $regla) {
            Database::execute(
                "UPDATE reglas_alerta SET activa=?, umbral=?, updated_at=NOW() WHERE id=? AND tutor_id=?",
                [(int)($regla['activa'] ?? 0), (float)($regla['umbral'] ?? 0), (int)$regla['id'], $tutor->id]
            );
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        flash('success', 'Reglas guardadas correctamente.');
        redirect('/tutor/alertas');
    }

    public function mensajes(): void
    {
        $tutor    = $this->tutor();
        $alumnoId = (int)($_GET['alumno'] ?? 0);
        $alumnos  = Database::fetchAll(
            "SELECT a.*, u.name, u.id as usuario_id
             FROM alumnos a JOIN users u ON u.id = a.usuario_id WHERE a.tutor_id = ?",
            [$tutor->id]
        );

        $conversacion = [];
        $alumnoSel    = null;
        if ($alumnoId) {
            $alumnoSel = array_values(array_filter($alumnos, fn($a) => $a->id === $alumnoId))[0] ?? null;
            if ($alumnoSel) {
                $conversacion = Database::fetchAll(
                    "SELECT m.*, u.name as remitente_nombre
                     FROM mensajes m JOIN users u ON u.id = m.remitente_id
                     WHERE (m.remitente_id = ? AND m.destinatario_id = ?)
                        OR (m.remitente_id = ? AND m.destinatario_id = ?)
                     ORDER BY m.created_at ASC",
                    [$tutor->usuario_id, $alumnoSel->usuario_id,
                     $alumnoSel->usuario_id, $tutor->usuario_id]
                );
                // Marcar como leídos
                Database::execute(
                    "UPDATE mensajes SET leido=1, updated_at=NOW()
                     WHERE remitente_id=? AND destinatario_id=? AND leido=0",
                    [$alumnoSel->usuario_id, $tutor->usuario_id]
                );
            }
        }

        $flash = getFlash();
        view('layouts.app', [
            'titulo'  => 'Mensajes',
            'content' => 'tutor.mensajes',
            'data'    => compact('tutor','alumnos','alumnoSel','conversacion','flash'),
        ]);
    }

    public function enviarMensaje(): void
    {
        verify_csrf();
        $tutor      = $this->tutor();
        $destinoId  = (int)($_POST['destinatario_id'] ?? 0);
        $asunto     = trim($_POST['asunto'] ?? 'Mensaje del tutor');
        $cuerpo     = trim($_POST['cuerpo'] ?? '');

        if (!$cuerpo || !$destinoId) {
            flash('error', 'El mensaje no puede estar vacío.');
            redirect('/tutor/mensajes');
        }

        Database::execute(
            "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, cuerpo, leido, created_at, updated_at)
             VALUES (?, ?, ?, ?, 0, NOW(), NOW())",
            [$tutor->usuario_id, $destinoId, $asunto, $cuerpo]
        );

        flash('success', 'Mensaje enviado.');
        redirect('/tutor/mensajes?alumno=' . (int)($_POST['alumno_id'] ?? 0));
    }

    public function responderMensaje(string $id): void
    {
        verify_csrf();
        $tutor  = $this->tutor();
        $cuerpo = trim($_POST['cuerpo'] ?? '');
        $destId = (int)($_POST['destinatario_id'] ?? 0);

        if ($cuerpo && $destId) {
            Database::execute(
                "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, cuerpo, leido, created_at, updated_at)
                 VALUES (?, ?, 'Respuesta del tutor', ?, 0, NOW(), NOW())",
                [$tutor->usuario_id, $destId, $cuerpo]
            );
        }

        redirect('/tutor/mensajes?alumno=' . (int)($_POST['alumno_id'] ?? 0));
    }

    public function reportes(): void
    {
        $tutor   = $this->tutor();
        $tipo    = $_GET['tipo'] ?? '';
        $alumnos = Database::fetchAll(
            "SELECT a.*, u.name, c.nombre as carrera_nombre
             FROM alumnos a JOIN users u ON u.id = a.usuario_id JOIN carreras c ON c.id = a.carrera_id
             WHERE a.tutor_id = ?",
            [$tutor->id]
        );

        view('layouts.app', [
            'titulo'  => 'Reportes',
            'content' => 'tutor.reportes',
            'data'    => compact('tutor','alumnos','tipo'),
        ]);
    }
}
