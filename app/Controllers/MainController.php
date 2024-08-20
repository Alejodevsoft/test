<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Docusign;
use App\Libs\Monday;
use App\Models\MainModel;
use DocuSign\eSign\Client\ApiClient;
use Throwable;

class MainController{
    public function index(){
        if ($_SERVER['REQUEST_METHOD']==='GET') {
            view('form_monday');
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['user_id']) || empty($_POST['api_key'])) {
                $this->loadErrorMain('Data not reported');
            }
            $user_name  = $this->verifiyMondayUser($_POST);
            view('form_docusign',['user_name'=>$user_name]);
        }
    }

    private function verifiyMondayUser($request){
        $validate_user      = Monday::validateUser($request['user_id'],$request['api_key']);
        if (!$validate_user['success']) {
            $this->loadErrorMain($validate_user['error']);
        }
        $model  = new MainModel();
        $client = $model->getClientByMondayId($request['user_id']);
        if ($client == null) {
            $model->createClientUnpaid($request['user_id'],$request['api_key']);
        }
        $validate_purchase  = Monday::validatePurchase();
        if (!$validate_purchase['success']) {
            $this->loadErrorMain('Not purchase');
        }
        session_start();
        $_SESSION['user_id_monday'] = $request['user_id'];
        return $validate_user['data']['name'];
    }

    public function saveDocusign(){
        if ($_SERVER['REQUEST_METHOD']!=='POST') {
            $this->loadErrorMain('Method not valid');
        }
        $model  = new MainModel();
        session_start();
        $client = $model->getClientByMondayId($_SESSION['user_id_monday']);
        session_destroy();
        if ($client == null) {
            $this->loadErrorMain('Client not found');
        }
        $request    = $_POST;
        if (
            empty($request['client_id']) ||
            empty($request['user_id']) ||
            empty($request['private_key'])
        ) {
            $this->loadErrorMain('Data not reported');
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
        $model->updateClient($client['id'],$client);
        $docusign   = Docusign::verifyConset($client_id_docusign,$user_id_docusign,$private_key);
        if (!$docusign['success']) {
            if ($docusign['redirect']) {
                session_start();
                $_SESSION['redirect_url']   = $docusign['redirect_url'];
                header('Location: ./jwt-verify?id='.$client['user_id_monday']);
                exit;
            }
        }
    }

    public function test(){
        $aes_class  = new Monday();
        $aes_class->validateUser("35497500","eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM3NDc5NjAwMCwiYWFpIjoxMSwidWlkIjozNTQ5NzUwMCwiaWFkIjoiMjAyNC0wNi0yMFQxNzoxNDoyNS4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTAyMDk1OTMsInJnbiI6InVzZTEifQ.Kap9bbJUy0P7BqvYZsgXk8cywgocFYYfyY2yVZZzLao");
    }

    public function test2(){
        $model = new MainModel();
        $clients = $model->getAllClients();

        foreach ($clients as $client) {
            echo "ID: " . $client['id'] . " - User ID Monday: " . $client['user_id_monday'] . " - API Key Monday: " . $client['api_key_monday'] . "<br>";
        }

        // Datos del cliente
        $user_id_monday = '35497500';
        $api_key_monday = 'sjkhfdsjfbsjdlkgbfsldkfns';

        // $newClientId = $model->createClient($user_id_monday, $api_key_monday);
        // echo "<br>Nuevo cliente creado con ID: " . $newClientId;
    }

    private function loadErrorMain($text_error){
        session_start();
        $_SESSION['error']  = $text_error;
        header('Location: ./');
        exit;
    }
    public function jwt(){
        if (isset($_GET['id'])) {
            $model = new MainModel();
            $clients = $model->getClientByMondayId($_GET['id']);
            if (!empty($clients)) {

                $clientId = AesClass::decrypt($clients['client_id_docusign']);
                $userId = AesClass::decrypt($clients['user_id_docusign']);
                if ($clients['server_docusign'] == 0) {
                    $oauthBasePath = 'account-d.docusign.com';
                } else {
                    $oauthBasePath = 'account.docusign.com';
                }
                
                $expiresIn = 3600;

                $apiClient = new ApiClient();
                $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
                $privateKey = AesClass::decrypt($clients['private_key']);

                $jwt_scope = 'signature';

                try {
                    $response = $apiClient->requestJWTUserToken(
                        $clientId,
                        $userId,
                        $privateKey,
                        $jwt_scope
                    );
                    session_start();
                    if (!empty($_SESSION['redirect_url'])) {
                        $data_red['open_docu']  = true;
                        $data_red['url']        = $_SESSION['redirect_url'];
                    }else{
                        $data_red['open_docu']  = false;
                    }
                    session_destroy();
                    include 'app/Views/jwt_correct.php';
                } catch (Throwable $th) {
                    if (strpos($th->getMessage(), "consent_required") !== false) {
                        include 'app/Views/jwt.php';
                    }
                }
            }else{
                header('Location: ./');
            }
        }else{
            header('Location: ./');
        }
    }
}