<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Livewire\Alumno\MallaCurricular;

// ── Ruta raíz: redirige según autenticación ──
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// ── Dashboard inteligente: redirige según el rol ──
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->esAlumno()) return redirect()->route('alumno.dashboard');
    if ($user->esTutor()) return redirect()->route('tutor.dashboard');
    if ($user->esAdmin()) return redirect()->route('admin.dashboard');
    
    abort(403, 'Rol no reconocido');
})->middleware('auth')->name('dashboard');

// ═══ ALUMNO ═══
Route::middleware(['auth', 'rol:alumno'])
    ->prefix('alumno')
    ->name('alumno.')
    ->group(function () {
        Route::view('/dashboard', 'alumno.dashboard')->name('dashboard');
        Route::get('/malla', \App\Livewire\Alumno\MallaCurricular::class)->name('malla');
        Route::get('/materias', \App\Livewire\Alumno\Materias::class)->name('materias');
        Route::view('/calificaciones', 'alumno.calificaciones')->name('calificaciones');
        Route::view('/historial', 'alumno.historial')->name('historial');
        Route::view('/mensajes', 'alumno.mensajes')->name('mensajes');
    });

// ═══ TUTOR ═══
Route::middleware(['auth', 'rol:tutor'])
    ->prefix('tutor')
    ->name('tutor.')
    ->group(function () {
        Route::view('/dashboard', 'tutor.dashboard')->name('dashboard');
        Route::view('/alumnos', 'tutor.alumnos')->name('alumnos');
        Route::get('/alumnos/{id}', fn($id) => view('tutor.alumno-detalle', ['id' => $id]))->name('alumno-detalle');
        Route::view('/alertas', 'tutor.alertas')->name('alertas');
        Route::view('/mensajes', 'tutor.mensajes')->name('mensajes');
        Route::view('/reportes', 'tutor.reportes')->name('reportes');
    });

// ═══ ADMIN ═══
Route::middleware(['auth', 'rol:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('/usuarios', 'admin.usuarios')->name('usuarios');
    });

// ── Perfil (común) ──
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';