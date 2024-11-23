<?php

return [
    '/'                     => 'App\Controllers\MainController::index',
    'test'                  => 'App\Controllers\MainController::test',
    'test2'                 => 'App\Controllers\MainController::test2',
    'additional'            => 'App\Controllers\AdditionalController',
    'jwt-verify'            => 'App\Controllers\MainController::jwt',
    'save-docusign'         => 'App\Controllers\MainController::saveDocusign',
    'send-to-sign'          => 'App\Controllers\MainController::send',
    'upload-monday'         => 'App\Controllers\MainController::upload',
    'logout'                => 'App\Controllers\MainController::logout',
    'admin'                 => 'App\Controllers\AdminController::admin',
    'admin/templates'       => 'App\Controllers\AdminController::templates',
    'admin/list-templates'  => 'App\Controllers\AdminController::getDocusignTemplates',
    'admin/contracts'       => 'App\Controllers\AdminController::contracts',
    'admin/docusign'        => 'App\Controllers\AdminController::docusign',
    'admin/update-docusign' => 'App\Controllers\AdminController::updateDocusign',
    'admin/set-template'    => 'App\Controllers\AdminController::setTemplate'
];
