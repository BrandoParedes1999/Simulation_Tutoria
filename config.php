<?php
// ── Configuración de la aplicación ─────────────────────────────────
define('APP_NAME', 'Sistema de Tutoría');
define('APP_ENV',  'development');   // cambiar a 'production' en servidor real

// ── Detectar base URL automáticamente (funciona en subdir o raíz) ───
// Ejemplo: si el proyecto está en C:/wamp64/www/Simulation_Tutoria/
// entonces APP_URL = '/Simulation_Tutoria'
(function () {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    // SCRIPT_NAME = /Simulation_Tutoria/index.php  →  base = /Simulation_Tutoria
    $base = rtrim(dirname($script), '/');
    define('APP_URL', $base === '.' ? '' : $base);
})();

// ── Base de datos ───────────────────────────────────────────────────
define('DB_HOST',    $_ENV['DB_HOST']     ?? '127.0.0.1');
define('DB_PORT',    $_ENV['DB_PORT']     ?? '3306');
define('DB_NAME',    $_ENV['DB_DATABASE'] ?? 'simulation_tutoria');
define('DB_USER',    $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASS',    $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// ── Sesión ──────────────────────────────────────────────────────────
define('SESSION_NAME',     'tutoria_session');
define('SESSION_LIFETIME', 7200);

// ── Paths absolutos del servidor ─────────────────────────────────────
define('BASE_PATH',  __DIR__);
define('VIEWS_PATH', __DIR__ . '/views');
define('APP_PATH',   __DIR__ . '/app');
