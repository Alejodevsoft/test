<?php

namespace App\Config;

class Routes extends Middleware{
    private const NAMESPACE = 'App\Controllers\\';
    private $routes   = [];

    public function __construct(){
        $this->get('/','MainController::index');
        $this->post('/','MainController::validateLogin');
        $this->get('jwt-verify','MainController::jwt');
        $this->post('save-docusign','MainController::saveDocusign');
        $this->get('logout','MainController::logout');
        $this->post('send-to-sign','WebhookController::send');
        $this->post('signatures-query','WebhookController::signaturesQuery');
        $this->post('upload-monday','WebhookController::upload');
        $this->get('admin','AdminController::admin','auth','auth');
        $this->get('admin/templates','AdminController::templates','auth');
        $this->get('admin/list-templates','AdminController::getDocusignTemplates','auth');
        $this->get('admin/envelops','AdminController::envelops','auth');
        $this->get('admin/docusign','AdminController::docusign','auth');
        $this->post('admin/update-docusign','AdminController::updateDocusign','auth');
        $this->post('admin/set-template','AdminController::setTemplate','auth');
        $this->post('admin/set-user-active' ,'AdminController::setUserActive','auth');
    }

    public function getRoutes(){
        return $this->routes;
    }    

    public function get($route, $action,$filter = null) {
        $this->create_route($route,$action,'GET',$filter);
    }

    public function post($route, $action,$filter = null) {
        $this->create_route($route,$action,'POST',$filter);
    }

    public function put($route, $action,$filter = null) {
        $this->create_route($route,$action,'PUT',$filter);
    }

    public function delete($route, $action,$filter = null) {
        $this->create_route($route,$action,'DELETE',$filter);
    }

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

    public function toRoute(){
        $route  = $this->getRoute();
        $environment = $_ENV['ENVIRONMENT'] ?? 'development';
        if (isset($this->routes[$route['method']][$route['uri']])) {
            list($controllerClass, $method) = explode('::', $this->routes[$route['method']][$route['uri']]['action']);
            $toRoute    = $this->routes[$route['method']][$route['uri']];
            
            if (!empty($toRoute['filter'])) {
                if (isset($this->filters[$toRoute['filter']])){
                    $filterClass    = new $this->filters[$toRoute['filter']]();
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
                $environment!='production' ? write_error_route_log("El método $method no se encontro en el controlador $controllerClass."):'';
            }
        } else {
            http_response_code(404);
            echo "404 - Página no encontrada";
        }
    }

    private function create_route($route, $action,$method,$filter){
        $this->routes[$method][$route]  = [
            'action'    => self::NAMESPACE.$action,
            'filter'    => $filter
        ];
    }
}