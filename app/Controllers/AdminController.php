<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Docusign;
use App\Libs\Monday;
use App\Models\MainModel;

class AdminController{
    private $main_model;
    private $console_data;
    private $user_data;
    function __construct(){
        $this->main_model   = new MainModel();
        $this->user_data    = get_user_data();
        $this->console_data = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);
        if (!boolval($this->console_data['docusign_verify'])) {
            redirect();
        }
    }
    
    public function admin(){
        $data['select_aside'] = 10;
        $data['page_title'] = 'Admin';

        $users_admin    = $this->main_model->getUsersByConsoleId($this->console_data['id']);
        if ($users_admin != null & sizeof($users_admin) > 0) {
            $users_admin    = array_column($users_admin,'active','monday_id');
        }

        $users  = [];
        $users_monday   = Monday::getUsers(AesClass::decrypt($this->console_data['api_key_monday']));
        if ($users_monday != null & sizeof($users_monday['data']) > 0) {
            foreach ($users_monday['data'] as $monday_user) {
                if ($monday_user->is_admin) {
                    $item   = [
                        'id'    => $monday_user->id,
                        'name'  => $monday_user->name,
                        'email' => $monday_user->email
                    ];
                    if (isset($users_admin[$monday_user->id]) && $users_admin[$monday_user->id] == 1) {
                        $item['active'] = true;
                    }else{
                        $item['active'] = false;
                    }
                    $users[] = $item;
                }
            }
        }

        $data['users']      = $users;
        $data['docusign']   = Docusign::getAccountData(
            $this->console_data['server_docusign'],
            AesClass::decrypt($this->console_data['client_id_docusign']),
            AesClass::decrypt($this->console_data['user_id_docusign']),
            AesClass::decrypt($this->console_data['private_key'])
        );

        return template_init('index',$data);
    }

    public function templates(){
        $data['select_aside'] = 20;
        $data['page_title'] = 'Templates Config';

        $console     = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);

        $api_key     = AesClass::decrypt($console['api_key_monday']);
        $data_boards = Monday::getBoards($api_key);

        $data['boards'] = $data_boards;
        
        return template_init('templates',$data);
    }

    public function envelops(){
        $board_id    = $_GET['board_id'];
        $console     = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);

        $api_key        = AesClass::decrypt($console['api_key_monday']);
        $data_envelops  = Monday::getEnvelops($api_key, $board_id);

        if ($data_envelops['success']) {
            return $this->returnRest($data_envelops['success'],"ok",$data_envelops['data']);
        }else{
            return $this->returnRest($data_envelops['success'], $data_envelops['error']);
        }
    }

    public function docusign(){
        $data['select_aside'] = 30;
        $data['page_title'] = 'Docusign Config';
        return template_init('docusign',$data);
    }

    public function updateDocusign(){
        $request    = $_POST;
        if (
            empty($request['client_id']) ||
            empty($request['user_id']) ||
            empty($request['private_key'])
        ) {
            $this->loadErrorMain('Data not reported');
        }
        $console    = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);
        if ($console == null) {
            $this->loadErrorMain('User not found');
        }
        if (empty($request['server_type'])) {
            $client['server_docusign']  = '0';
        }else{
            $client['server_docusign']  = $request['server_type'];
        }
        $client_id_docusign = $request['client_id'];
        $user_id_docusign   = $request['user_id'];
        $private_key        = $request['private_key'];
        if (
            AesClass::decrypt($console['client_id_docusign']) != $client_id_docusign ||
            AesClass::decrypt($console['user_id_docusign']) != $user_id_docusign ||
            AesClass::decrypt($console['private_key']) != $private_key
        ) {
            $data_docusign    = [
                'client_id_docusign'    => AesClass::encrypt($client_id_docusign),
                'user_id_docusign'      => AesClass::encrypt($user_id_docusign),
                'private_key'           => AesClass::encrypt($private_key)
            ];
            set_docusign_new($data_docusign);
        }
        $this->main_model->unVerifyConsole($console['id']);
        redirect('jwt-verify?id='.$console['id']);
    }

    public function getDocusignTemplates(){
        $model = new MainModel();
        $console    = $model->getConsoleByMondayId($this->user_data['monday_id']);
        $templates_response = Docusign::getTemplates(
            $console['server_docusign'],
            AesClass::decrypt($console['client_id_docusign']),
            AesClass::decrypt($console['user_id_docusign']),
            AesClass::decrypt($console['private_key'])
        );
        if ($templates_response['success']) {
            return $this->returnRest($templates_response['success'],'ok',$templates_response['data']);
        }
        return $this->returnRest($templates_response['success'],$templates_response['error']);
    }

    public function setTemplate() {
        $request    = $_POST;
        if (
            empty($request['board_id']) ||
            empty($request['contract_id']) ||
            empty($request['template_id']) 
        ) {
            $this->loadErrorMain('Data not reported');
        }

        $console    = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);
        $api_key    = AesClass::decrypt($console['api_key_monday']);

        echo Monday::setTemplate($api_key,$request['board_id'],$request['contract_id'],$request['template_id']);
    }

    public function setUserActive(){
        $request    = $_POST;
        if (
            empty($request['monday_id']) ||
            empty($request['active'])
        ) {
            return $this->returnRest(false,'Data not reported');
        }
        if ($request['monday_id'] === $this->user_data['monday_id']) {
            return $this->returnRest(false,'User cannot change their own status');
        }
        $console    = $this->main_model->getConsoleByMondayId($this->user_data['monday_id']);
        if ($request['active'] === 'false') {
            $this->main_model->setActiveUser(false,$console['id'],$request['monday_id']);
            return $this->returnRest(true,'User unactived',$request);
        }else{
            $api_key        = AesClass::decrypt($console['api_key_monday']);
            $monday_user    = Monday::validateUser($request['monday_id'],$api_key);
            if (!$monday_user['success']) {
                return $this->returnRest(false,'An error has occurred on monday');
            }
            if (!$monday_user['data']['is_admin']) {
                return $this->returnRest(false,'The user cannot be activated because he is not an administrator');
            }
            $user   = $this->main_model->getUserByMondayId($request['monday_id']);
            if ($user != null) {
                $this->main_model->setActiveUser(true,$console['id'],$request['monday_id']);
                return $this->returnRest(true,'User actived',$request);
            }else{
                $user   = [
                    'monday_id' => $request['monday_id'],
                    'name'      => $monday_user['data']['name'],
                    'email'     => $monday_user['data']['email']
                ];
                if ($this->main_model->createUser($console['id'],$user) > 0) {
                    return $this->returnRest(true,'User activated');
                }
                return $this->returnRest(false,'An error has ocurred');
            }
        }
    }

    public function changePassword(){
        $data['select_aside'] = 40;
        $data['page_title'] = 'Change password';
        return template_init('change-password',$data);
    }

    public function updatePassword(){
        $request    = $_POST;
        if (
            empty($request['old_password']) ||
            empty($request['new_password']) ||
            empty($request['new_password2'])
        ) {
            return $this->returnRest(false,'Data not reported');
        }
        $user   = $this->main_model->getUserByMondayId($this->user_data['monday_id']);
        if (password_verify($request['old_password'],$user['password'])){
            if ($request['new_password'] == $request['new_password2']) {
                $options = [
                    'cost' => 11
                ]; 
                if ($this->main_model->updatePassword(
                    $user['monday_id'],
                    $user['console_id'],
                    password_hash($request['new_password2'],PASSWORD_BCRYPT,$options)
                ) > 0){
                    return $this->returnRest(true,'Pasword updated');
                }
                return $this->returnRest(false,'An error has ocurred');
            }
            return $this->returnRest(false,'Passwords do not match');
        }
        return $this->returnRest(false,'Password incorrect');
    }

    private function loadErrorMain($text_error){
        set_error($text_error);
        redirect();
    }

    private function returnRest($success, $message, $data_entry = []){
        header('Content-Type: application/json; charset=utf-8');

        $data['success'] = $success;
        $data['message'] = $message;
        $data['data'] = $data_entry;

        echo json_encode($data);
    }
}