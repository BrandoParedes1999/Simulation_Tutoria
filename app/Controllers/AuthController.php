<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class AuthController
{
    public function landing(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $anuncios = Database::fetchAll(
            "SELECT * FROM anuncios WHERE activo = 1 AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) ORDER BY created_at DESC LIMIT 3"
        );
        view('landing', ['anuncios' => $anuncios]);
    }

    public function loginForm(): void
    {
        if (Auth::check()) redirect('/dashboard');
        $errs  = errors();
        $flash = getFlash();
        view('auth.login', ['errs' => $errs, 'flash' => $flash]);
    }

    public function login(): void
    {
        verify_csrf();
        $id  = trim($_POST['identificador'] ?? '');
        $pwd = $_POST['password'] ?? '';

        if (!$id || !$pwd) {
            saveOld();
            $_SESSION['errors']['identificador'] = 'Completa todos los campos.';
            redirect('/login');
        }

        if (!Auth::attempt($id, $pwd)) {
            saveOld();
            $_SESSION['errors']['identificador'] = 'Credenciales incorrectas.';
            redirect('/login');
        }

        redirect('/dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        redirect('/login');
    }

    public function registroForm(): void
    {
        if (Auth::check()) redirect('/dashboard');
        $errs    = errors();
        $carreras = Database::fetchAll("SELECT id, nombre, clave FROM carreras ORDER BY nombre");
        view('auth.registro', ['errs' => $errs, 'carreras' => $carreras]);
    }

    public function registro(): void
    {
        verify_csrf();

        $matricula  = trim($_POST['matricula'] ?? '');
        $correo     = trim($_POST['correo_institucional'] ?? '');
        $name       = trim($_POST['name'] ?? '');
        $password   = $_POST['password'] ?? '';
        $password2  = $_POST['password_confirmation'] ?? '';
        $carrera_id = (int)($_POST['carrera_id'] ?? 0);

        $errs = [];
        if (!$matricula) $errs['matricula'] = 'La matrícula es requerida.';
        if (!$correo || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errs['correo_institucional'] = 'Correo inválido.';
        if (!$name) $errs['name'] = 'El nombre es requerido.';
        if (strlen($password) < 8) $errs['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($password !== $password2) $errs['password_confirmation'] = 'Las contraseñas no coinciden.';

        if (!$carrera_id) {
            $errs['carrera_id'] = 'Selecciona una carrera.';
        }

        // Verificar si la matrícula ya tiene un usuario registrado
        $alumno = Database::fetchOne(
            "SELECT a.id, a.usuario_id FROM alumnos a WHERE a.matricula = ?",
            [$matricula]
        );

        if (!$alumno) {
            $errs['matricula'] = 'Matrícula no encontrada. Contacta a tu tutor o administrador.';
        } elseif ($alumno->usuario_id) {
            $errs['matricula'] = 'Esta matrícula ya tiene una cuenta registrada.';
        }

        if ($errs) {
            saveOld();
            $_SESSION['errors'] = $errs;
            redirect('/registro');
        }

        // Crear usuario
        Database::beginTransaction();
        try {
            $userId = Database::insert(
                "INSERT INTO users (name, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, 'alumno', NOW(), NOW())",
                [$name, $correo, password_hash($password, PASSWORD_DEFAULT)]
            );

            Database::execute(
                "UPDATE alumnos SET usuario_id = ?, correo_institucional = ? WHERE id = ?",
                [$userId, $correo, $alumno->id]
            );

            Database::commit();
            flash('success', 'Cuenta creada exitosamente. Ya puedes iniciar sesión.');
            redirect('/login');
        } catch (\Exception $e) {
            Database::rollback();
            flash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            redirect('/registro');
        }
    }

    public function dashboard(): void
    {
        Auth::requireAuth();
        $rol = Auth::rol();
        match ($rol) {
            'alumno' => redirect('/alumno/dashboard'),
            'tutor'  => redirect('/tutor/dashboard'),
            'admin'  => redirect('/admin/dashboard'),
            default  => redirect('/login'),
        };
    }
}
