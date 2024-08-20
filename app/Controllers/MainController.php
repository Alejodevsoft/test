<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Docusign;
use App\Libs\Monday;
use App\Models\MainModel;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\TextCustomField;
use Throwable;

class MainController{
    public function index(){
        if ($_SERVER['REQUEST_METHOD']==='GET') {
            view('form_monday');
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['user_id']) || empty($_POST['api_key'])) {
                $this->loadErrorMain('Data not reported');
            }
            $user_data  = $this->verifiyMondayUser($_POST);
            view('form_docusign',$user_data);
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
        $_SESSION['user_id_monday'] = $request['user_id'];
        return [
            'user_name' => $validate_user['data']['name'],
            'monday_id' => $request['user_id']
        ];
    }

    public function saveDocusign(){
        if ($_SERVER['REQUEST_METHOD']!=='POST') {
            $this->loadErrorMain('Method not valid');
        }
        $request    = $_POST;
        if (
            empty($request['monday_id']) ||
            empty($request['client_id']) ||
            empty($request['user_id']) ||
            empty($request['private_key'])
        ) {
            $this->loadErrorMain('Data not reported');
        }
        $model  = new MainModel();
        $client = $model->getClientByMondayId($request['monday_id']);
        if ($client == null) {
            $this->loadErrorMain('Client not found');
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
            }
        }
        header('Location: ./jwt-verify?id='.$client['user_id_monday']);
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
                    include 'app/Views/jwt_correct.php';
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
                        $data_red['url'] = $authorizationURL;
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
    public function send(){
        $datamonday = json_decode(file_get_contents('php://input'));

        $model = new MainModel();
        $clients = $model->getClientByMondayId($datamonday->payload->inputFields->userId);

        if (!empty($clients)) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.monday.com/v2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('query' => "{items(ids: [".$datamonday->payload->inputFields->itemId."]){column_values(types: text){text}subitems{name,column_values(types: email){text}}}}"),
                CURLOPT_HTTPHEADER => array(
                  "Authorization: ".AesClass::decrypt($clients['api_key_monday'])
                ),
            ));

            $responseMonday = json_decode(curl_exec($curl));

            curl_close($curl);

            $clientId = AesClass::decrypt($clients['client_id_docusign']);
            $userId = AesClass::decrypt($clients['user_id_docusign']);
            if ($clients['server_docusign'] == 0) {
                $oauthBasePath = 'account-d.docusign.com';
            } else {
                $oauthBasePath = 'account.docusign.com';
            }
            $apiClient = new ApiClient();
            $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
            $privateKey = AesClass::decrypt($clients['private_key']);

            $jwt_scope = 'signature';

            $response = $apiClient->requestJWTUserToken(
                $clientId,
                $userId,
                $privateKey,
                $jwt_scope
            );

            $accessToken = $response[0]->getAccessToken();
            $accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

            
            if ($clients['server_docusign'] == 0) {
                $basePath = 'https://demo.docusign.net/restapi';
            } else if($clients['server_docusign'] > 0 && $clients['server_docusign'] < 5){
                $number = $clients['server_docusign'];
                $basePath = "https://na$number.docusign.net/restapi";
            } elseif($clients['server_docusign'] == 5){
                $basePath = "https://ca.docusign.net/restapi";
            } elseif($clients['server_docusign'] == 6){
                $basePath = "https://au.docusign.net/restapi";
            } elseif($clients['server_docusign'] == 7){
                $basePath = "https://eu.docusign.net/restapi";
            }


        }
       
        $configuration = new Configuration();
        $configuration->setHost($basePath);
        $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);
    
        $apiClient = new ApiClient($configuration);
        $envelopeApi = new EnvelopesApi($apiClient);
        $signers = [];
        foreach ($responseMonday->data->items[0]->subitems as $key => $signer ) {
            $signers[$key] = new TemplateRole([
                'email' => $signer->column_values[0]->text,
                'name' => $signer->name,
                'role_name' => 'Signer'.($key+1)
            ]);
        }
        $customFields = new CustomFields([
            'text_custom_fields' => [
                new TextCustomField([
                    'name' => 'pulseId',
                    'value' => $datamonday->payload->inputFields->itemId,
                    'show' => 'false'
                ]),
                new TextCustomField([
                    'name' => 'userIdMonday',
                    'value' => $datamonday->payload->inputFields->userId,
                    'show' => 'false'
                ])
            ]
        ]);
    
        $envelopeDefinition = new EnvelopeDefinition([
            'template_id' => $responseMonday->data->items[0]->column_values[0]->text,
            'template_roles' => $signers,
            'status' => 'sent',
            'custom_fields' => $customFields
        ]);
        $results = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
    }
}