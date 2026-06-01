<?php
// ── Configuración de la aplicación ─────────────────────────────────
define('APP_NAME', 'Sistema de Tutoría');
define('APP_URL',  '');
define('APP_ENV',  'production');

// ── Base de datos ───────────────────────────────────────────────────
define('DB_HOST',     $_ENV['DB_HOST']     ?? '127.0.0.1');
define('DB_PORT',     $_ENV['DB_PORT']     ?? '3306');
define('DB_NAME',     $_ENV['DB_DATABASE'] ?? 'simulation_tutoria');
define('DB_USER',     $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASS',     $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET',  'utf8mb4');

// ── Sesión ──────────────────────────────────────────────────────────
define('SESSION_NAME',     'tutoria_session');
define('SESSION_LIFETIME', 7200);   // 2 horas en segundos

// ── Rutas base ──────────────────────────────────────────────────────
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', __DIR__ . '/views');
define('APP_PATH',   __DIR__ . '/app');
