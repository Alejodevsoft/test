<?php

use App\Config\Routes;

session_set_cookie_params([
    'samesite'  => 'None',
    'secure'    => true,
]);
ob_start();
session_start();
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Common.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$environment = $_ENV['ENVIRONMENT'] ?? 'development';

if ($environment === 'development') {
    // Mostrar todos los errores en entorno de desarrollo
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} elseif ($environment === 'production') {
    // Ocultar errores en entorno de producción
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    // Configuración por defecto
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        write_warning_log("No se encontró el archivo: " . $file . "<br>");
    }
});

$routesClass = new Routes();
$routesClass->toRoute();
