<?php
// Cargar variables de entorno desde .env si existe
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

require_once __DIR__ . '/config.php';

// Mostrar errores en desarrollo, ocultarlos en producción
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/Router.php';

// Autoloader para namespace App\
spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/app/';
    $rel  = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file = $base . $rel . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Iniciar sesión
\App\Auth::start();
