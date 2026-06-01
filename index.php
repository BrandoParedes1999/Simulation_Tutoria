<?php
// Punto de entrada de la aplicación PHP puro
require_once __DIR__ . '/bootstrap.php';

$router = require_once __DIR__ . '/routes.php';
$router->dispatch();
