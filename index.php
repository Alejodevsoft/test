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
        echo "No se encontró el archivo: " . $file . "<br>";
    }
});
//Obtiene la ruta del navegador, limpiadno el index y la y el dominio
function getRoute() {
    $base_dir = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/');
    $request_uri = $_SERVER['REQUEST_URI'];
    $relative_uri = parse_url($request_uri, PHP_URL_PATH);

    if ($base_dir !== '') {
        $relative_uri = preg_replace('#^' . preg_quote($base_dir, '#') . '/?#', '', $relative_uri);
    }

    $uri = trim($relative_uri, '/');

    $return['uri']      = $uri === '' ? '/' : $uri;
    $return['method']   = $_SERVER['REQUEST_METHOD'];
    return $return;
}

$routesClass    = new Routes();

$routes = $routesClass->getRoutes();
// $routes = require 'routes.php';

$route = getRoute();

// if (array_key_exists($route, $routes)) {
if (isset($routes[$route['method']][$route['uri']])) {
    list($controllerClass, $method) = explode('::', $routes[$route['method']][$route['uri']]['action']);

    $controller = new $controllerClass();
    
    if (method_exists($controller, $method)) {
        try {
            $controller->$method();
        } catch (\Throwable $th) {
            http_response_code(500);
            write_error_log($th);
        }
    } else {
        http_response_code(405);
        echo "Ruta {$route['uri']} no encontrada.";
        $environment!='production' ? write_error_route_log("El método $method no se encontro en el controlador $controllerClass."):'';
    }
} else {
    http_response_code(404);
    echo "404 - Página no encontrada";
}
