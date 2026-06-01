<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Diagnóstico del Sistema</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f7ff; }
        h1 { color: #1e3a8a; }
        .ok   { color: #15803d; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .warn { color: #d97706; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; max-width: 700px; }
        td, th { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #1e3a8a; color: white; }
        tr:nth-child(even) { background: #eff6ff; }
    </style>
</head>
<body>
<h1>🔍 Diagnóstico — Sistema de Tutoría</h1>

<table>
<tr><th>Verificación</th><th>Resultado</th><th>Valor</th></tr>

<?php
function row(string $label, bool $ok, string $value = ''): void {
    $cls = $ok ? 'ok' : 'fail';
    $ico = $ok ? '✓' : '✗';
    echo "<tr><td>{$label}</td><td class='{$cls}'>{$ico} " . ($ok ? 'OK' : 'ERROR') . "</td><td>" . htmlspecialchars($value) . "</td></tr>\n";
}

// PHP version
$phpOk = version_compare(PHP_VERSION, '8.1', '>=');
row('PHP >= 8.1', $phpOk, PHP_VERSION);

// PDO MySQL
$pdoOk = extension_loaded('pdo_mysql');
row('Extensión pdo_mysql', $pdoOk, $pdoOk ? 'Disponible' : 'FALTA — habilitar en php.ini');

// mod_rewrite (sólo indicativo)
$modRw = isset($_SERVER['HTTP_MOD_REWRITE']) || function_exists('apache_get_modules')
    ? in_array('mod_rewrite', apache_get_modules() ?? [])
    : null;
if ($modRw === null) {
    echo "<tr><td>mod_rewrite</td><td class='warn'>? Verificar</td><td>No detectable por PHP. Revisar en WAMP → Apache → Modules → rewrite_module</td></tr>\n";
} else {
    row('mod_rewrite', $modRw, $modRw ? 'Activo' : 'INACTIVO — habilitar en WAMP');
}

// Archivos del proyecto
foreach ([
    'index.php', 'bootstrap.php', 'config.php', 'routes.php',
    'app/Database.php', 'app/Auth.php', 'app/helpers.php', 'app/Router.php',
    'app/Controllers/AuthController.php',
] as $f) {
    row("Archivo: {$f}", file_exists(__DIR__ . '/' . $f), '');
}

// Conexión a BD
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    row('Conexión MySQL', true, 'BD: ' . DB_NAME . ' en ' . DB_HOST);
    $pdo = null;
} catch (Exception $e) {
    row('Conexión MySQL', false, $e->getMessage());
}

// APP_URL detectado
row('APP_URL detectado', true, APP_URL === '' ? '/ (raíz)' : APP_URL);
?>

</table>

<br>
<p><b>URL del proyecto:</b> <?= htmlspecialchars('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . APP_URL . '/') ?></p>
<p><b>SCRIPT_NAME:</b> <?= htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '') ?></p>
<p>Si todo aparece en verde, el sistema debería funcionar. Elimina este archivo en producción.</p>
<br>
<a href="<?= htmlspecialchars(APP_URL . '/') ?>" style="display:inline-block;padding:10px 20px;background:#1d4ed8;color:white;border-radius:8px;text-decoration:none">
    → Ir al sistema
</a>
</body>
</html>
