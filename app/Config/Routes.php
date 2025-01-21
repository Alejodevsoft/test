<?php

namespace App\Config;

class Routes{
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
        $this->get('admin','AdminController::admin');
        $this->get('admin/templates','AdminController::templates');
        $this->get('admin/list-templates','AdminController::getDocusignTemplates');
        $this->get('admin/envelops','AdminController::envelops');
        $this->get('admin/docusign','AdminController::docusign');
        $this->post('admin/update-docusign','AdminController::updateDocusign');
        $this->post('admin/set-template','AdminController::setTemplate');
        $this->post('admin/set-user-active' ,'AdminController::setUserActive');
    }

    public function getRoutes(){
        return $this->routes;
    }    

    public function get($route, $action) {
        $this->create_route($route,$action,'GET');
    }

    public function post($route, $action) {
        $this->create_route($route,$action,'POST');
    }

    public function put($route, $action) {
        $this->create_route($route,$action,'PUT');
    }

    public function delete($route, $action) {
        $this->create_route($route,$action,'DELETE');
    }

    private function create_route($route, $action,$method){
        $this->routes[$method][$route]  = [
            'action'    => self::NAMESPACE.$action
        ];
    }
}