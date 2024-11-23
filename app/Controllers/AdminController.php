<?php

namespace App\Controllers;

use App\Models\MainModel;

class AdminController{
    private $main_model;
    function __construct(){
        $this->main_model   = new MainModel();
        if (!is_logged()) {
            redirect();
        }
    }
    
    public function admin(){
        $data['page_title'] = 'Admin';
        return template_init('admin',$data);
    }
}