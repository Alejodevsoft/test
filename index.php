<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Common.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$environment = $_ENV['ENVIRONMENT'] ?? 'production';

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
        echo "No se encontró el archivo: " . $file . "<br>";
    }
});
function getRoute() {
    $base_dir =  str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
    $uri = trim(parse_url(str_replace($base_dir,'',$_SERVER['REQUEST_URI']), PHP_URL_PATH), '/');
    return $uri === '' ? '/' : $uri;
}

$routes = require 'routes.php';

$route = getRoute();

if (array_key_exists($route, $routes)) {
    list($controllerClass, $method) = explode('::', $routes[$route]);

    $controller = new $controllerClass();
    
    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        echo "Método $method no encontrado en el controlador $controllerClass.";
    }
} else {
    http_response_code(404);
    echo "404 - Página no encontrada";
}
