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
        $data['select_aside'] = 10;
        $data['page_title'] = 'Admin';
        return template_init('index',$data);
    }

    public function docusign(){
        $data['select_aside'] = 20;
        $data['page_title'] = 'Docusign Config';
        return template_init('docusign',$data);
    }
}