<?php

namespace App\Config;

use App\Filters\Auth;

class Middleware{
    protected $filters  = [
        'auth' => Auth::class
    ];
}