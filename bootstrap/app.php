<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registra el alias 'rol' para el middleware VerificarRol.
        // Sin esto, todas las rutas con ->middleware('rol:alumno') etc.
        // lanzan "Target class [rol] does not exist".
        $middleware->alias([
            'rol' => \App\Http\Middleware\VerificarRol::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();