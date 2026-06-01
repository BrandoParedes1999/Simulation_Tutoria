<?php

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\AlumnoController;
use App\Controllers\TutorController;
use App\Controllers\AdminController;
use App\Controllers\ProfileController;

$router = new Router();

// ── Públicas ─────────────────────────────────────────────────────────
$router->get('/',              [AuthController::class, 'landing']);
$router->get('/login',         [AuthController::class, 'loginForm']);
$router->post('/login',        [AuthController::class, 'login']);
$router->post('/logout',       [AuthController::class, 'logout']);
$router->get('/registro',      [AuthController::class, 'registroForm']);
$router->post('/registro',     [AuthController::class, 'registro']);

// ── Dashboard (redirige según rol) ───────────────────────────────────
$router->get('/dashboard',     [AuthController::class, 'dashboard']);

// ── Alumno ────────────────────────────────────────────────────────────
$router->group('/alumno', function (Router $r) {
    $r->get('/dashboard',      [AlumnoController::class, 'dashboard']);
    $r->get('/malla',          [AlumnoController::class, 'malla']);
    $r->get('/materias',       [AlumnoController::class, 'materias']);
    $r->post('/materias/inscribir',  [AlumnoController::class, 'inscribir']);
    $r->post('/materias/desinscribir', [AlumnoController::class, 'desinscribir']);
    $r->get('/calificaciones', [AlumnoController::class, 'calificaciones']);
    $r->post('/calificaciones/guardar', [AlumnoController::class, 'guardarCalificaciones']);
    $r->get('/historial',      [AlumnoController::class, 'historial']);
    $r->get('/mensajes',       [AlumnoController::class, 'mensajes']);
    $r->post('/mensajes/responder', [AlumnoController::class, 'responderMensaje']);
    $r->post('/mensajes/{id}/leer', [AlumnoController::class, 'marcarLeido']);
});

// ── Tutor ─────────────────────────────────────────────────────────────
$router->group('/tutor', function (Router $r) {
    $r->get('/dashboard',      [TutorController::class, 'dashboard']);
    $r->get('/alumnos',        [TutorController::class, 'alumnos']);
    $r->get('/alumnos/{id}',   [TutorController::class, 'detalleAlumno']);
    $r->post('/alumnos/{id}/asignar',   [TutorController::class, 'asignar']);
    $r->post('/alumnos/{id}/desasignar',[TutorController::class, 'desasignar']);
    $r->get('/alertas',        [TutorController::class, 'alertas']);
    $r->post('/alertas/{id}/atender', [TutorController::class, 'atenderAlerta']);
    $r->post('/alertas/reglas/guardar', [TutorController::class, 'guardarReglas']);
    $r->get('/mensajes',       [TutorController::class, 'mensajes']);
    $r->post('/mensajes/enviar', [TutorController::class, 'enviarMensaje']);
    $r->post('/mensajes/{id}/responder', [TutorController::class, 'responderMensaje']);
    $r->get('/reportes',       [TutorController::class, 'reportes']);
});

// ── Admin ─────────────────────────────────────────────────────────────
$router->group('/admin', function (Router $r) {
    $r->get('/dashboard',      [AdminController::class, 'dashboard']);
    $r->get('/usuarios',       [AdminController::class, 'usuarios']);
});

// ── Perfil ────────────────────────────────────────────────────────────
$router->get('/perfil',        [ProfileController::class, 'edit']);
$router->post('/perfil',       [ProfileController::class, 'update']);

return $router;
