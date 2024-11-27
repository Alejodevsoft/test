<?php

return [
    '/'                     => 'App\Controllers\MainController::index',
    'jwt-verify'            => 'App\Controllers\MainController::jwt',
    'save-docusign'         => 'App\Controllers\MainController::saveDocusign',
    'logout'                => 'App\Controllers\MainController::logout',
    'send-to-sign'          => 'App\Controllers\WebhookController::send',
    'upload-monday'         => 'App\Controllers\WebhookController::upload',
    'admin'                 => 'App\Controllers\AdminController::admin',
    'admin/templates'       => 'App\Controllers\AdminController::templates',
    'admin/list-templates'  => 'App\Controllers\AdminController::getDocusignTemplates',
    'admin/contracts'       => 'App\Controllers\AdminController::contracts',
    'admin/docusign'        => 'App\Controllers\AdminController::docusign',
    'admin/update-docusign' => 'App\Controllers\AdminController::updateDocusign',
    'admin/set-template'    => 'App\Controllers\AdminController::setTemplate'
];
