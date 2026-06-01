<?php

// ── Redirección ──────────────────────────────────────────────────────
function redirect(string $url): never
{
    if (!headers_sent()) {
        header('Location: ' . APP_URL . $url);
    }
    exit;
}

// ── Flash messages ───────────────────────────────────────────────────
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function getFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

// ── Renderizado de vistas ────────────────────────────────────────────
function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $path = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
    if (!file_exists($path)) {
        die("Vista no encontrada: {$template}");
    }
    require $path;
}

// ── CSRF ─────────────────────────────────────────────────────────────
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('CSRF token inválido');
    }
}

// ── HTML escape ──────────────────────────────────────────────────────
function e(string|null $str): string
{
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// ── Old input ────────────────────────────────────────────────────────
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function saveOld(): void
{
    $_SESSION['old'] = $_POST;
}

// ── Errores de validación ────────────────────────────────────────────
function errors(): array
{
    $errs = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errs;
}

function hasError(string $field, array $errs): bool
{
    return isset($errs[$field]);
}

function errorMsg(string $field, array $errs): string
{
    return $errs[$field] ?? '';
}

// ── Fecha formateada ─────────────────────────────────────────────────
function fechaHumana(string|null $date): string
{
    if (!$date) return '—';
    $meses = ['', 'enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $ts = strtotime($date);
    return date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
}

function diaActual(): string
{
    $dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    $meses = ['', 'enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return ucfirst($dias[date('w')]) . ', ' . date('j') . ' de ' . $meses[(int)date('n')];
}

// ── URL actual ───────────────────────────────────────────────────────
function currentPath(): string
{
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

function isPath(string $path): bool
{
    return currentPath() === $path;
}

function isPathStarting(string $prefix): bool
{
    return str_starts_with(currentPath(), $prefix);
}

// ── Número formateado ────────────────────────────────────────────────
function num(float $n, int $decimals = 1): string
{
    return number_format($n, $decimals);
}

// ── Clasificación de promedio ────────────────────────────────────────
function clasificarPromedio(float $promedio): array
{
    if ($promedio >= 90) return ['texto' => 'Excelente',        'color' => 'text-emerald-600'];
    if ($promedio >= 70) return ['texto' => 'Buen rendimiento', 'color' => 'text-blue-600'];
    if ($promedio > 0)   return ['texto' => 'Requiere atención','color' => 'text-red-600'];
    return ['texto' => 'Sin calificaciones', 'color' => 'text-blue-400'];
}

// ── Icono Lucide inline (data-lucide) ─────────────────────────────────
function icon(string $name, string $class = 'w-5 h-5'): string
{
    return '<i data-lucide="' . e($name) . '" class="' . e($class) . '"></i>';
}

// ── JSON seguro para uso en HTML ──────────────────────────────────────
function toJson(mixed $data): string
{
    return htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
}

// ── Método HTTP override ──────────────────────────────────────────────
function method(string $m): string
{
    return '<input type="hidden" name="_method" value="' . strtoupper($m) . '">';
}

function requestMethod(): string
{
    $override = $_POST['_method'] ?? '';
    return strtoupper($override ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}
