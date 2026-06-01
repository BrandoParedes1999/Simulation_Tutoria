<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class ProfileController
{
    public function edit(): void
    {
        Auth::requireAuth();
        $user  = Auth::user();
        $flash = getFlash();
        $errs  = errors();
        view('layouts.app', [
            'titulo'  => 'Mi Perfil',
            'content' => 'profile.edit',
            'data'    => compact('user','flash','errs'),
        ]);
    }

    public function update(): void
    {
        verify_csrf();
        Auth::requireAuth();

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tel   = trim($_POST['telefono'] ?? '');
        $pwd   = $_POST['password'] ?? '';
        $pwd2  = $_POST['password_confirmation'] ?? '';

        $errs = [];
        if (!$name)  $errs['name']  = 'El nombre es requerido.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errs['email'] = 'Email inválido.';
        if ($pwd && strlen($pwd) < 8)
            $errs['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($pwd && $pwd !== $pwd2)
            $errs['password_confirmation'] = 'Las contraseñas no coinciden.';

        if ($errs) {
            saveOld();
            $_SESSION['errors'] = $errs;
            redirect('/perfil');
        }

        $userId = Auth::id();
        if ($pwd) {
            Database::execute(
                "UPDATE users SET name=?, email=?, telefono=?, password=?, updated_at=NOW() WHERE id=?",
                [$name, $email, $tel, password_hash($pwd, PASSWORD_DEFAULT), $userId]
            );
        } else {
            Database::execute(
                "UPDATE users SET name=?, email=?, telefono=?, updated_at=NOW() WHERE id=?",
                [$name, $email, $tel, $userId]
            );
        }

        $_SESSION['user_name'] = $name;
        flash('success', 'Perfil actualizado correctamente.');
        redirect('/perfil');
    }
}
