<?php

return [
    '/'             => 'App\Controllers\MainController::index',
    'test'          => 'App\Controllers\MainController::test',
    'test2'         => 'App\Controllers\MainController::test2',
    'additional'    => 'App\Controllers\AdditionalController',
    'jwt-verify'    => 'App\Controllers\MainController::jwt',
    'send-to-sign'  => 'App\Controllers\MainController::send'
];
