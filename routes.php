<?php

$namespace  = 'App\Controllers\\';
$routes     = [];

get('/','MainController::index');
post('/','MainController::validateLogin');
get('jwt-verify','MainController::jwt');
post('save-docusign','MainController::saveDocusign');
get('logout','MainController::logout');
post('send-to-sign','WebhookController::send');
post('signatures-query','WebhookController::signaturesQuery');
post('upload-monday','WebhookController::upload');
get('admin','AdminController::admin');
get('admin/templates','AdminController::templates');
get('admin/list-templates','AdminController::getDocusignTemplates');
get('admin/envelops','AdminController::envelops');
get('admin/docusign','AdminController::docusign');
post('admin/update-docusign','AdminController::updateDocusign');
post('admin/set-template','AdminController::setTemplate');
post('admin/set-user-active' ,'AdminController::setUserActive');

function get($route, $action) {
    create_route($route,$action,'GET');
}

function post($route, $action) {
    create_route($route,$action,'POST');
}

function put($route, $action) {
    create_route($route,$action,'PUT');
}

function delete($route, $action) {
    create_route($route,$action,'DELETE');
}

function create_route($route, $action,$method){
    global $routes, $namespace;
    $routes[$method][$route]  = [
        'action'    => $namespace.$action
    ];
}

return $routes;