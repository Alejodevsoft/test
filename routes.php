<?php

return [
    '/'                     => 'App\Controllers\MainController::index',
    'jwt-verify'            => 'App\Controllers\MainController::jwt',
    'save-docusign'         => 'App\Controllers\MainController::saveDocusign',
    'logout'                => 'App\Controllers\MainController::logout',
    'send-to-sign'          => 'App\Controllers\WebhookController::send',
    'signatures-query'      => 'App\Controllers\WebhookController::signaturesQuery',
    'upload-monday'         => 'App\Controllers\WebhookController::upload',
    'admin'                 => 'App\Controllers\AdminController::admin',
    'admin/templates'       => 'App\Controllers\AdminController::templates',
    'admin/list-templates'  => 'App\Controllers\AdminController::getDocusignTemplates',
    'admin/envelops'        => 'App\Controllers\AdminController::envelops',
    'admin/docusign'        => 'App\Controllers\AdminController::docusign',
    'admin/update-docusign' => 'App\Controllers\AdminController::updateDocusign',
    'admin/set-template'    => 'App\Controllers\AdminController::setTemplate',
    'admin/set-user-active' => 'App\Controllers\AdminController::setUserActive'
];
