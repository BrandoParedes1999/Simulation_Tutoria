<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class AdminController
{
    public function dashboard(): void
    {
        Auth::requireRole('admin');
        $totalAlumnos = Database::fetchOne("SELECT COUNT(*) as t FROM alumnos")->t;
        $totalTutores = Database::fetchOne("SELECT COUNT(*) as t FROM tutores")->t;
        $totalAlertas = Database::fetchOne("SELECT COUNT(*) as t FROM alertas WHERE atendida=0")->t;

        view('layouts.app', [
            'titulo'  => 'Admin',
            'content' => 'admin.dashboard',
            'data'    => compact('totalAlumnos','totalTutores','totalAlertas'),
        ]);
    }

    public function usuarios(): void
    {
        Auth::requireRole('admin');
        $usuarios = Database::fetchAll(
            "SELECT u.*, a.matricula, t.numero_empleado FROM users u
             LEFT JOIN alumnos a ON a.usuario_id = u.id
             LEFT JOIN tutores t ON t.usuario_id = u.id
             ORDER BY u.rol, u.name"
        );

        view('layouts.app', [
            'titulo'  => 'Usuarios',
            'content' => 'admin.usuarios',
            'data'    => compact('usuarios'),
        ]);
    }
}
