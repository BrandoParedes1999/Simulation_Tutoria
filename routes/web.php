<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tutor\MensajeController as TutorMensajeController;
use App\Http\Controllers\Tutor\AlertaController  as TutorAlertaController;
use App\Http\Controllers\Alumno\MensajeController as AlumnoMensajeController;

// ══════════════════════════════════════════════════════════════
//  RUTAS PÚBLICAS  (cualquier persona, sin sesión)
// ══════════════════════════════════════════════════════════════

/*
 |  /  →  Si ya tienes sesión: va al dashboard según tu rol.
 |        Si no: muestra la landing pública con datos de ejemplo.
 */
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

/*
 |  Registro de alumno con matrícula + código al correo institucional.
 |  Solo accesible para visitantes sin sesión (middleware 'guest').
 */
Route::get('/registro/alumno', \App\Livewire\Auth\RegistroAlumno::class)
    ->middleware('guest')
    ->name('registro.alumno');


// ══════════════════════════════════════════════════════════════
//  DASHBOARD INTELIGENTE  (redirige según rol)
// ══════════════════════════════════════════════════════════════

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->esAlumno()) return redirect()->route('alumno.dashboard');
    if ($user->esTutor())  return redirect()->route('tutor.dashboard');
    if ($user->esAdmin())  return redirect()->route('admin.dashboard');

    abort(403, 'Rol no reconocido.');
})->middleware('auth')->name('dashboard');


// ══════════════════════════════════════════════════════════════
//  ALUMNO
// ══════════════════════════════════════════════════════════════

Route::middleware(['auth', 'rol:alumno'])
    ->prefix('alumno')
    ->name('alumno.')
    ->group(function () {

        // Vistas Livewire
        Route::get('/dashboard',      \App\Livewire\Alumno\Dashboard::class)      ->name('dashboard');
        Route::get('/malla',          \App\Livewire\Alumno\MallaCurricular::class) ->name('malla');
        Route::get('/materias',       \App\Livewire\Alumno\Materias::class)        ->name('materias');
        Route::get('/calificaciones', \App\Livewire\Alumno\Calificaciones::class)  ->name('calificaciones');
        Route::get('/historial',      \App\Livewire\Alumno\Historial::class)       ->name('historial');
        Route::get('/mensajes',       \App\Livewire\Alumno\Mensajes::class)        ->name('mensajes');

        // Endpoints JSON usados por los componentes Livewire de mensajes
        Route::post('/mensajes/{mensaje}/responder', [AlumnoMensajeController::class, 'responder'])
            ->name('mensajes.responder');
        Route::post('/mensajes/{mensaje}/leer',      [AlumnoMensajeController::class, 'leer'])
            ->name('mensajes.leer');
    });


// ══════════════════════════════════════════════════════════════
//  TUTOR
// ══════════════════════════════════════════════════════════════

Route::middleware(['auth', 'rol:tutor'])
    ->prefix('tutor')
    ->name('tutor.')
    ->group(function () {

        // Vistas blade estáticas con lógica en @php
        Route::view('/dashboard', 'tutor.dashboard')->name('dashboard');
        Route::view('/alumnos',   'tutor.alumnos')  ->name('alumnos');
        Route::view('/alertas',   'tutor.alertas')  ->name('alertas');
        Route::view('/mensajes',  'tutor.mensajes')  ->name('mensajes');
        Route::view('/reportes',  'tutor.reportes')  ->name('reportes');

        // Detalle de alumno individual
        Route::get('/alumnos/{id}', fn(int $id) => view('tutor.detalle_alumno', compact('id')))
            ->name('alumno-detalle');

        // Endpoints JSON: mensajería
        Route::post('/mensajes/enviar',              [TutorMensajeController::class, 'enviar'])
            ->name('mensajes.enviar');
        Route::post('/mensajes/{mensaje}/responder', [TutorMensajeController::class, 'responder'])
            ->name('mensajes.responder');
        Route::post('/mensajes/{mensaje}/leer',      [TutorMensajeController::class, 'leer'])
            ->name('mensajes.leer');

        // Endpoints JSON: alertas
        Route::post('/alertas/{alerta}/atender', [TutorAlertaController::class, 'atender'])
            ->name('alertas.atender');
    });


// ══════════════════════════════════════════════════════════════
//  ADMIN
// ══════════════════════════════════════════════════════════════

Route::middleware(['auth', 'rol:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('/usuarios',  'admin.usuarios') ->name('usuarios');
    });


// ══════════════════════════════════════════════════════════════
//  PERFIL  (cualquier usuario autenticado)
// ══════════════════════════════════════════════════════════════

Route::middleware('auth')->group(function () {
    Route::get('/perfil',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/perfil',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ══════════════════════════════════════════════════════════════
//  AUTH  (login, logout, password reset, etc.)
// ══════════════════════════════════════════════════════════════

require __DIR__.'/auth.php';