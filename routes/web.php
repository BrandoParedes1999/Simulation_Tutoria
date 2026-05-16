<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tutor\MensajeController  as TutorMensajeController;
use App\Http\Controllers\Tutor\AlertaController   as TutorAlertaController;
use App\Http\Controllers\Tutor\ReporteController  as TutorReporteController;
use App\Http\Controllers\Alumno\MensajeController as AlumnoMensajeController;
use App\Http\Controllers\Alumno\NotificacionController;
use App\Livewire\Tutor\GestionAlumnos as TutorGestionAlumnos;

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return view('landing');
})->name('landing');

Route::get('/registro/alumno', \App\Livewire\Auth\RegistroAlumno::class)
    ->middleware('guest')
    ->name('registro.alumno');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->esAlumno()) return redirect()->route('alumno.dashboard');
    if ($user->esTutor())  return redirect()->route('tutor.dashboard');
    if ($user->esAdmin())  return redirect()->route('admin.dashboard');
    abort(403, 'Rol no reconocido.');
})->middleware('auth')->name('dashboard');

// ── ALUMNO ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'rol:alumno'])->prefix('alumno')->name('alumno.')->group(function () {
    Route::get('/dashboard',      \App\Livewire\Alumno\Dashboard::class)      ->name('dashboard');
    Route::get('/malla',          \App\Livewire\Alumno\MallaCurricular::class) ->name('malla');
    Route::get('/materias',       \App\Livewire\Alumno\Materias::class)        ->name('materias');
    Route::get('/calificaciones', \App\Livewire\Alumno\Calificaciones::class)  ->name('calificaciones');
    Route::get('/historial',      \App\Livewire\Alumno\Historial::class)       ->name('historial');
    Route::get('/mensajes',       \App\Livewire\Alumno\Mensajes::class)        ->name('mensajes');

    Route::post('/mensajes/{mensaje}/responder', [AlumnoMensajeController::class, 'responder'])->name('mensajes.responder');
    Route::post('/mensajes/{mensaje}/leer',      [AlumnoMensajeController::class, 'leer'])     ->name('mensajes.leer');
});

// ── TUTOR ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'rol:tutor'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::view('/dashboard', 'tutor.dashboard')->name('dashboard');
    Route::get('/alumnos', TutorGestionAlumnos::class)->name('alumnos');
    Route::view('/alertas',   'tutor.alertas')  ->name('alertas');
    Route::view('/mensajes',  'tutor.mensajes')  ->name('mensajes');

    Route::get('/reportes', [TutorReporteController::class, 'index'])->name('reportes');

    Route::get('/alumnos/{id}', fn(int $id) => view('tutor.detalle_alumno', compact('id')))
        ->name('alumno-detalle');

    Route::post('/mensajes/enviar',              [TutorMensajeController::class, 'enviar'])   ->name('mensajes.enviar');
    Route::post('/mensajes/{mensaje}/responder', [TutorMensajeController::class, 'responder'])->name('mensajes.responder');
    Route::post('/mensajes/{mensaje}/leer',      [TutorMensajeController::class, 'leer'])     ->name('mensajes.leer');

    Route::post('/alertas/{alerta}/atender',     [TutorAlertaController::class, 'atender'])         ->name('alertas.atender');
    // PDF #6 — Guardar configuración de reglas con umbrales editables
    Route::post('/alertas/reglas/guardar',       [TutorAlertaController::class, 'guardarReglas'])    ->name('alertas.guardar-reglas');
});

// ── ADMIN ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('/usuarios',  'admin.usuarios') ->name('usuarios');
});

// ── NOTIFICACIONES (cualquier usuario autenticado) ───────────────────────────
Route::middleware('auth')->post('/notificaciones/leer', [NotificacionController::class, 'marcarLeidas'])
    ->name('notificaciones.leer');

// ── PERFIL ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/perfil',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/perfil',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';