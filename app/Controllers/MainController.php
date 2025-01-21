<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Docusign;
use App\Libs\Monday;
use App\Models\MainModel;
use DocuSign\eSign\Client\ApiClient;
use Throwable;

class MainController{
    private $main_model;
    function __construct(){
        $this->main_model   = new MainModel();
    }

    public function index(){
        if (is_logged()) {
            $user_data  = get_user_data();
            $console_data   = $this->main_model->getConsoleByMondayId($user_data['monday_id']);
            if (boolval($console_data['docusign_verify'])) {
                redirect('admin');
            }else{
                redirect('active');
            }
        }else{
            view('login');
        }
    }

    public function active(){
        if (is_logged()) {
            $user_data  = get_user_data();
            $console_data   = $this->main_model->getConsoleByMondayId($user_data['monday_id']);
            if (boolval($console_data['docusign_verify'])) {
                redirect('admin');
            }else{
                view('form_docusign',get_user_data());
            }
        }else{
            view('form_monday');
        }
    }

    public function activeMonday(){
        if (empty($_POST['user_id']) || empty($_POST['api_key'])) {
            $this->loadErrorMain('Data not reported');
        }
        $this->verifiyMondayUser($_POST);
        redirect('active');
    }

    public function validateLogin(){
        $request    = $_POST;
        if (empty($request['user_id']) || empty($request['password'])) {
            $this->loadErrorMain('Data not reported');
        }
        $user   = $this->main_model->getUserByMondayId($request['user_id']);
        if ($user == null) {
            $this->loadErrorMain('User not found');
            redirect();
        }
        if (!boolval($user['active'])) {
            $this->loadErrorMain('Unauthorized user');
            redirect();
        }
        if (password_verify($request['password'],$user['password'])) {
            $client = $this->main_model->getConsoleById($user['console_id']);
            $user_data  = [
                'client_name' => $client['client_name'],
                'user_name' => $user['name'],
                'monday_id' => $request['user_id']
            ];
            set_login(true,$user_data);
        }else{
            $this->loadErrorMain('Incorrect password');
        }
        redirect();

    }

    private function verifiyMondayUser($request){
        $validate_user  = Monday::validateUser($request['user_id'],$request['api_key']);
        if (!$validate_user['success']) {
            $this->loadErrorMain($validate_user['error']);
        }
        if (!$validate_user['data']['is_admin']) {
            $this->loadErrorMain('Unauthorized user');
        }
        $client = $this->main_model->getConsoleByMondayKey($request['api_key']);
        if ($client == null) {
            $new_user['api_key']            = $request['api_key'];
            $new_user['client_name']        = $validate_user['data']['company_name'];
            $new_user['client_account_id']  = $validate_user['data']['account_id'];
            $resutl_insert  = $this->main_model->createConsoleUnpaid($new_user);
            if ($resutl_insert != null) {
                $users_monday = Monday::getUsers($request['api_key']);
                if ($users_monday['success']) {
                    $options = [
                        'cost' => 11
                    ];
    
                    $admin_users = array_filter($users_monday['data'], function ($user) use($options){
                        $user->password = password_hash($user->id,PASSWORD_BCRYPT,$options); 
                        return $user->is_admin === true;
                    });
                    $admin_users = array_values($admin_users);
                    if (sizeof($admin_users) > 0) {
                        $this->main_model->createMultipleUsers($resutl_insert,$admin_users);
                    }
                }
            }
        }else{
            $user   = $this->main_model->getUserByMondayId($request['user_id']);
            if ($user == null) {
                $this->loadErrorMain('Unauthorized user');
            }
        }
        $validate_purchase  = Monday::validatePurchase($request['api_key']);
        if (!$validate_purchase['success']) {
            $this->loadErrorMain('Not purchase');
        }
        $user_data  = [
            'client_name' => $validate_user['data']['company_name'],
            'user_name' => $validate_user['data']['name'],
            'monday_id' => $request['user_id']
        ];
        set_login(true,$user_data);
    }

    public function saveDocusign(){
        if (!is_logged()) {
            $this->loadErrorMain('Not logged');
        }
        $request    = $_POST;
        if (
            empty($request['client_id']) ||
            empty($request['user_id']) ||
            empty($request['private_key'])
        ) {
            $this->loadErrorMain('Data not reported');
        }
        $user   = $this->main_model->getUserByMondayId(get_user_data()['monday_id']);
        if ($user == null) {
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
        $client['client_id_docusign']   = AesClass::encrypt($client_id_docusign);
        $client['user_id_docusign']     = AesClass::encrypt($user_id_docusign);
        $client['private_key']          = AesClass::encrypt($private_key);
        $this->main_model->updateConsole($user['console_id'],$client);
        $docusign   = Docusign::verifyConsent($client_id_docusign,$user_id_docusign,$private_key);
        if (!$docusign['success']) {
            if ($docusign['redirect']) {
                $_SESSION['redirect_url']   = $docusign['redirect_url'];
            }
        }
        redirect('jwt-verify?id='.$user['console_id']);
    }

    private function loadErrorMain($text_error){
        set_error($text_error);
        redirect();
    }
    public function jwt(){
        if (isset($_GET['id'])) {
            $console = $this->main_model->getConsoleById($_GET['id']);
            if (!empty($console)) {
                if (is_docusign_new()) {
                    $is_doc_new = true;
                    $new_docusign   = docusign_new();
                    $clientId   = AesClass::decrypt($new_docusign['client_id_docusign']);
                    $userId     = AesClass::decrypt($new_docusign['user_id_docusign']);
                    $privateKey = AesClass::decrypt($new_docusign['private_key']);
                }else{
                    $is_doc_new = false;
                    $clientId   = AesClass::decrypt($console['client_id_docusign']);
                    $userId     = AesClass::decrypt($console['user_id_docusign']);
                    $privateKey = AesClass::decrypt($console['private_key']);
                }
                if ($console['server_docusign'] == 0) {
                    $oauthBasePath = 'account-d.docusign.com';
                } else {
                    $oauthBasePath = 'account.docusign.com';
                }
                
                $apiClient = new ApiClient();
                $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
                
                $jwt_scope = 'signature';
                
                try {
                    $response = $apiClient->requestJWTUserToken(
                        $clientId,
                        $userId,
                        $privateKey,
                        $jwt_scope
                    );
                    $this->main_model->verifyConsole($_GET['id']);
                    if ($is_doc_new) {
                        $this->main_model->updateConsole($console['id'],$new_docusign);
                    }
                    view('jwt_correct');
                } catch (Throwable $th) {
                    if (strpos($th->getMessage(), "consent_required") !== false) {
                        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type=code&'
                        . http_build_query(
                            [
                                'scope' => "impersonation+" . 'signature',
                                'client_id' => $clientId,
                                'redirect_uri' => 'https://monday.com'
                            ]
                        );
                        view('jwt',['url'=>$authorizationURL]);
                    }else{
                        if ($is_doc_new) {
                            $this->main_model->verifyConsole($_GET['id']);
                        }
                        view('jwt_error');
                    }
                }
            }else{
                redirect();
            }
        }else{
            redirect();
        }
    }

    public function logout(){
        session_destroy();
        redirect();
    } 
}