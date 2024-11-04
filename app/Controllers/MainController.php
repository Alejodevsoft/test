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
use CURLFile;

class MainController{
    private $main_model;
    function __construct(){
        $this->main_model   = new MainModel();
    }

    public function index(){
        if ($_SERVER['REQUEST_METHOD']==='GET') {
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
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['user_id']) || empty($_POST['api_key'])) {
                $this->loadErrorMain('Data not reported');
            }
            $this->verifiyMondayUser($_POST);
            redirect();
        }
    }

    private function verifiyMondayUser($request){
        $validate_user  = Monday::validateUser($request['user_id'],$request['api_key']);
        if (!$validate_user['success']) {
            $this->loadErrorMain($validate_user['error']);
        }
        $client = $this->main_model->getConsoleByMondayKey($request['api_key']);
        if ($client == null) {
            $resutl_insert  = $this->main_model->createConsoleUnpaid($request['api_key']);
            if ($resutl_insert != null) {
                $users_monday = Monday::getUsers($request['api_key']);
                if ($users_monday['success']) {
                    $admin_users = array_filter($users_monday['data'], function ($user) {
                        return $user->is_admin === true;
                    });
                    $admin_users = array_values($admin_users);
                    if (sizeof($admin_users) > 0) {
                        $this->main_model->createMultipleUsers($resutl_insert,$admin_users);
                    }
                }
            }
        }
        $validate_purchase  = Monday::validatePurchase();
        if (!$validate_purchase['success']) {
            $this->loadErrorMain('Not purchase');
        }
        $user_data  = [
            'user_name' => $validate_user['data']['name'],
            'monday_id' => $request['user_id']
        ];
        set_login(true,$user_data);
    }

    public function saveDocusign(){
        if (!is_logged()) {
            $this->loadErrorMain('Not logged');
        }
        if ($_SERVER['REQUEST_METHOD']!=='POST') {
            $this->loadErrorMain('Method not valid');
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
        $docusign   = Docusign::verifyConset($client_id_docusign,$user_id_docusign,$private_key);
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
            $clients = $this->main_model->getConsoleById($_GET['id']);
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
                    $this->main_model->verifyConsole($_GET['id']);
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
                redirect();
            }
        }else{
            redirect();
        }
    }

    public function send(){
        $datamonday = json_decode(file_get_contents('php://input'));

        $model = new MainModel();
        $clients = $model->getConsoleByMondayId($datamonday->payload->inputFields->userId);

        if (!empty($clients)) {
            $responseMonday = json_decode(Monday::genericCurl(AesClass::decrypt($clients['api_key_monday']),'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: text){text}subitems{name,column_values(types: email){text}}}}'));
            
            $responseMonday2 = json_decode(Monday::genericCurl(AesClass::decrypt($clients['api_key_monday']),'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: file){id}}}'));

            $responseMonday3 = json_decode(Monday::genericCurl(AesClass::decrypt($clients['api_key_monday']),'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: status){id}}}'));

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
                    'name' => 'boardId',
                    'value' => $datamonday->payload->inputFields->boardId,
                    'show' => 'false'
                ]),
                new TextCustomField([
                    'name' => 'userIdMonday',
                    'value' => $datamonday->payload->inputFields->userId,
                    'show' => 'false'
                ]),
                new TextCustomField([
                    'name' => 'columnId',
                    'value' => $responseMonday2->data->items[0]->column_values[0]->id,
                    'show' => 'false'
                ]),
                new TextCustomField([
                    'name' => 'columnIdStatus',
                    'value' => $responseMonday3->data->items[0]->column_values[0]->id,
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

    public function upload(){
        $docusign = json_decode(file_get_contents('php://input'));
        if ($docusign->data->envelopeSummary->status == "completed") {

            $datosMonday = array_column($docusign->data->envelopeSummary->customFields->textCustomFields,'value','name');

            $clients = $this->main_model->getConsoleByMondayId($datosMonday['userIdMonday']);

            foreach ($docusign->data->envelopeSummary->envelopeDocuments as $key =>$document) {
                $base64File = $document->PDFBytes;
                $decodedFile = base64_decode($base64File);
                if ($key == 0) {
                    $tempFilePath = 'Document-'.$document->documentIdGuid.'.pdf';
                }else{
                    $tempFilePath = 'Certificate-'.$document->documentIdGuid.'.pdf';
                }
                file_put_contents($tempFilePath, $decodedFile);
                // Configura el formato de la marca de tiempo con milisegundos
                $microtime = microtime(true);
                $timestamp = date("Y-m-d H:i:s", $microtime) . sprintf(".%03d", ($microtime - floor($microtime)) * 1000);

                // Define el nombre del archivo donde guardarás las marcas de tiempo
                $filename = "timestamp.txt";

                // Abre el archivo para añadir datos (lo crea si no existe)
                $file = fopen($filename, "a");

                // Escribe la marca de tiempo en el archivo, seguido de una nueva línea
                fwrite($file, $tempFilePath.' - '.$timestamp . PHP_EOL);

                // Cierra el archivo
                fclose($file);

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
                CURLOPT_POSTFIELDS => array('query' => 'mutation ($file: File!) {add_file_to_column (item_id:'.$datosMonday['pulseId'].', column_id: "'.$datosMonday['columnId'].'", file: $file) {id }}','variables[file]'=> new CURLFile($tempFilePath)),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: '.AesClass::decrypt($clients['api_key_monday'])
                ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                unlink($tempFilePath);
            }
            $curl = curl_init();

            $query = '
                mutation {
                    change_column_value(
                        item_id: ' . $datosMonday['pulseId'] . ',
                        board_id: ' . $datosMonday['boardId'] . ',
                        column_id: "' . $datosMonday['columnIdStatus'] . '",
                        value: "{\"label\":\"Firmado\"}"
                    ) {
                        id
                    }
                }
            ';

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.monday.com/v2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(array('query' => $query)),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . AesClass::decrypt($clients['api_key_monday']),
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            if(curl_errno($curl)) {
                echo 'Error:' . curl_error($curl);
            }

            curl_close($curl);
        }
    }

    public function logout(){
        session_destroy();
        redirect();
    } 
}