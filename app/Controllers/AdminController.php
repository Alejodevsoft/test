<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Docusign;
use App\Libs\Monday;
use App\Models\MainModel;

class AdminController{
    private $main_model;
    function __construct(){
        $this->main_model   = new MainModel();
        if (!is_logged()) {
            redirect();
        }else{
            $user_data  = get_user_data();
            $console_data   = $this->main_model->getConsoleByMondayId($user_data['monday_id']);
            if (!boolval($console_data['docusign_verify'])) {
                redirect();
            }
        }
    }
    
    public function admin(){
        $data['select_aside'] = 10;
        $data['page_title'] = 'Admin';
        return template_init('index',$data);
    }

    public function templates(){
        $data['select_aside'] = 20;
        $data['page_title'] = 'Templates Config';

        $console     = $this->main_model->getConsoleByMondayId(get_user_data()['monday_id']);

        $api_key     = AesClass::decrypt($console['api_key_monday']);
        $data_boards = Monday::getBoards($api_key);

        $data['boards'] = $data_boards;
        
        return template_init('templates',$data);
    }

    public function contracts(){
        $board_id    = $_GET['board_id'];
        $console     = $this->main_model->getConsoleByMondayId(get_user_data()['monday_id']);

        $api_key     = AesClass::decrypt($console['api_key_monday']);
        $data_contracts = Monday::getContracts($api_key, $board_id);

        if ($data_contracts['success']) {
            return $this->returnRest($data_contracts['success'],"ok",$data_contracts['data']);
        }else{
            return $this->returnRest($data_contracts['success'], $data_contracts['error']);
        }
    }

    public function docusign(){
        $data['select_aside'] = 30;
        $data['page_title'] = 'Docusign Config';
        return template_init('docusign',$data);
    }

    public function updateDocusign(){
        $request    = $_POST;
        if (!is_logged()) {
            redirect();
        }
        if ($_SERVER['REQUEST_METHOD']!=='POST') {
            redirect();
        }
        $request    = $_POST;
        if (
            empty($request['client_id']) ||
            empty($request['user_id']) ||
            empty($request['private_key'])
        ) {
            $this->loadErrorMain('Data not reported');
        }
        $console    = $this->main_model->getConsoleByMondayId(get_user_data()['monday_id']);
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
        $console    = $model->getConsoleByMondayId(get_user_data()['monday_id']);
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