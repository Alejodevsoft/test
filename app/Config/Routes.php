<?php

namespace App\Config;

class Routes extends Middleware{
    private const NAMESPACE = 'App\Controllers\\';
    private $routes         = [];
    private $groupPrefix    = '';
    private $groupFilter    = null;

    public function __construct() {
        $this->get('/', 'MainController::index');
        $this->post('/', 'MainController::validateLogin');
        $this->get('jwt-verify', 'MainController::jwt');
        $this->post('save-docusign', 'MainController::saveDocusign');
        $this->get('logout', 'MainController::logout');
        $this->post('send-to-sign', 'WebhookController::send');
        $this->post('signatures-query', 'WebhookController::signaturesQuery');
        $this->post('upload-monday', 'WebhookController::upload');

        $this->group('admin', function () {
            $this->get('/', 'AdminController::admin');
            $this->get('templates', 'AdminController::templates');
            $this->get('list-templates', 'AdminController::getDocusignTemplates');
            $this->get('envelops', 'AdminController::envelops');
            $this->get('docusign', 'AdminController::docusign');
            $this->post('update-docusign', 'AdminController::updateDocusign');
            $this->post('set-template', 'AdminController::setTemplate');
            $this->post('set-user-active', 'AdminController::setUserActive');
        }, 'auth');
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function get($route, $action) {
        $this->create_route($route, $action, 'GET');
    }

    public function post($route, $action) {
        $this->create_route($route, $action, 'POST');
    }

    public function put($route, $action) {
        $this->create_route($route, $action, 'PUT');
    }

    public function delete($route, $action) {
        $this->create_route($route, $action, 'DELETE');
    }

    private function create_route($route, $action, $method) {
        if ($this->groupPrefix) {
            $fullRoute = trim($this->groupPrefix . '/' . ltrim($route, '/'), '/');
        } else {
            $fullRoute = $route === '/' ? $route : trim($route, '/');
        }

        $this->routes[$method][$fullRoute] = [
            'action' => self::NAMESPACE . $action,
            'filter' => $this->groupFilter
        ];
    }

    public function group($prefix, callable $callback, $filter = null) {
        $previousPrefix = $this->groupPrefix;
        $previousFilter = $this->groupFilter;

        $this->groupPrefix = trim($previousPrefix . '/' . trim($prefix, '/'), '/');
        $this->groupFilter = $filter;

        $callback();

        $this->groupPrefix = $previousPrefix;
        $this->groupFilter = $previousFilter;
    }

    function getRoute() {
        $base_dir = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/');
        $request_uri = $_SERVER['REQUEST_URI'];
        $relative_uri = parse_url($request_uri, PHP_URL_PATH);

        if ($base_dir !== '') {
            $relative_uri = preg_replace('#^' . preg_quote($base_dir, '#') . '/?#', '', $relative_uri);
        }

        $uri = trim($relative_uri, '/');

        $return['uri'] = $uri === '' ? '/' : $uri;
        $return['method'] = $_SERVER['REQUEST_METHOD'];
        return $return;
    }

    public function toRoute() {
        $route = $this->getRoute();
        $environment = $_ENV['ENVIRONMENT'] ?? 'development';
        if (isset($this->routes[$route['method']][$route['uri']])) {
            list($controllerClass, $method) = explode('::', $this->routes[$route['method']][$route['uri']]['action']);
            $toRoute = $this->routes[$route['method']][$route['uri']];

            if (!empty($toRoute['filter'])) {
                if (isset($this->filters[$toRoute['filter']])) {
                    $filterClass = new $this->filters[$toRoute['filter']]();
                    $filterClass->main();
                }
            }
            if (method_exists($controllerClass, $method)) {
                try {
                    $controller = new $controllerClass();
                    $controller->$method();
                } catch (\Throwable $th) {
                    http_response_code(500);
                    write_error_log($th);
                }
            } else {
                http_response_code(405);
                echo "Ruta {$route['uri']} no encontrada.";
                $environment != 'production' ? write_error_route_log("El método $method no se encontró en el controlador $controllerClass.") : '';
            }
        } else {
            http_response_code(404);
            echo "404 - Página no encontrada";
        }
    }
}
