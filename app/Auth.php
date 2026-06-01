<?php
namespace App;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function attempt(string $identificador, string $password): bool
    {
        // Buscar por matrícula, número empleado o email
        $user = Database::fetchOne(
            "SELECT u.*, a.id as alumno_id, a.matricula, t.id as tutor_id, t.numero_empleado
             FROM users u
             LEFT JOIN alumnos a ON a.usuario_id = u.id
             LEFT JOIN tutores t ON t.usuario_id = u.id
             WHERE u.email = :id
                OR a.matricula = :id2
                OR t.numero_empleado = :id3",
            [':id' => $identificador, ':id2' => $identificador, ':id3' => $identificador]
        );

        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function login(object $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user->id;
        $_SESSION['user_rol']  = $user->rol;
        $_SESSION['user_name'] = $user->name;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function rol(): ?string
    {
        return $_SESSION['user_rol'] ?? null;
    }

    public static function user(): ?object
    {
        if (!self::check()) return null;
        static $cached = null;
        if ($cached !== null) return $cached;

        $cached = Database::fetchOne(
            "SELECT u.*, a.id as alumno_id, a.matricula, a.semestre_actual, a.carrera_id,
                    a.tutor_id as alumno_tutor_id, a.estatus, a.creditos_aprobados, a.promedio_general,
                    a.correo_institucional,
                    t.id as tutor_id, t.numero_empleado
             FROM users u
             LEFT JOIN alumnos a ON a.usuario_id = u.id AND u.rol = 'alumno'
             LEFT JOIN tutores t ON t.usuario_id = u.id AND u.rol = 'tutor'
             WHERE u.id = ?",
            [self::id()]
        );
        return $cached;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            flash('error', 'Debes iniciar sesión para acceder.');
            redirect('/login');
        }
    }

    public static function requireRole(string $rol): void
    {
        self::requireAuth();
        if (self::rol() !== $rol) {
            redirect('/dashboard');
        }
    }
}
