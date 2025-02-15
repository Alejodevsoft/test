<?php

namespace App\Filters;

use App\Models\MainModel;

class Auth{
    public function main(){
        $main_model = new MainModel();
        if (!is_logged()) {
            redirect();
        }else{
            $user   = $main_model->getUserByMondayId(get_user_data()['monday_id']);
            if ($user == null || $user['active'] == 0) {
                $this->logout();
            }
        }
    }

    private function logout(){
        logout();
        redirect();
    } 
}