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
use CURLFile;

class WebhookController{
    private $main_model;
    function __construct(){
        $this->main_model   = new MainModel();
    }
    public function send(){
        $datamonday = json_decode(file_get_contents('php://input'));

        $model = new MainModel();
        $clients = $model->getConsoleByMondayId($datamonday->payload->inputFields->userId);

        $apiKeyMonday   = '';
        if (!empty($clients)) {
            $apiKeyMonday   = AesClass::decrypt($clients['api_key_monday']);
            $responseMonday = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: text){text}subitems{id,name,column_values(types: email){text}}}}'));
            
            $responseMonday2 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: file){id}}}'));

            $responseMonday3 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: status){id}}}'));

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
        $signersCustom = [];
        $signersInProgress = [];
        foreach ($responseMonday->data->items[0]->subitems as $key => $signer ) {
            $signers[$key] = new TemplateRole([
                'email' => $signer->column_values[0]->text,
                'name' => $signer->name,
                'role_name' => 'Signer'.($key+1)
            ]);
            $signersCustom[]    = 'Signer'.($key+1).'__'.$signer->id;
            $signersInProgress[]    = $signer->id;
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
                ]),
                new TextCustomField([
                    'name' => 'signers',
                    'value' => implode('||',$signersCustom),
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
        Monday::setSignersInProgress($apiKeyMonday,$signersInProgress);
    }

    public function upload(){
        $docusign = json_decode(file_get_contents('php://input'));

        $datosMonday = array_column($docusign->data->envelopeSummary->customFields->textCustomFields,'value','name');
        $clients = $this->main_model->getConsoleByMondayId($datosMonday['userIdMonday']);
        if (sizeof($docusign->recipients) > 0 && sizeof($docusign->recipients->signers) > 0 ) {
            $signers_docusign   = array_column($docusign->recipients->signers,'status','roleName');
            $signers_monday = explode('||',$datosMonday['signers']);
            if (sizeof($signers_monday) > 0) {
                foreach ($signers_monday as $signer_monday) {
                    $data_signer    = explode('__',$signer_monday);
                    if (isset($signers_docusign[$data_signer[0]]) && $signers_docusign[$data_signer[0]] == 'completed') {
                        Monday::setSignerStatus(AesClass::decrypt($clients['api_key_monday']),$datosMonday['boardId'],$data_signer[1]);
                    }
                }
            }
        }
        if ($docusign->data->envelopeSummary->status == "completed") {

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
                    CURLOPT_URL => 'https://api.monday.com/v2/file',
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
                        value: "{\"label\":\"Signed\"}"
                    ) {
                        id
                    }
                }
            ';

            Monday::genericCurlJsonQuery(AesClass::decrypt($clients['api_key_monday']),$query);
        }
    }

    public function signaturesQuery(){        
        $datamonday = json_decode(file_get_contents('php://input'));
        
        $client     = $this->main_model->getConsoleByMondayId($datamonday->payload->inputFields->userId);

        $monday_response    = Monday::getTemplateId(AesClass::decrypt($client['api_key_monday']),$datamonday->payload->inputFields->itemId);
        if ($monday_response['success']) {
            $template_id    = $monday_response['data'];
            $template_info  = Docusign::getTemplateInfo(
                $client['server_docusign'],
                AesClass::decrypt($client['client_id_docusign']),
                AesClass::decrypt($client['user_id_docusign']),
                AesClass::decrypt($client['private_key']),
                $template_id                                
            );
            if ($template_info['success']) {
                Monday::setSignerFields(AesClass::decrypt($client['api_key_monday']),$datamonday->payload->inputFields->itemId,$template_info['data']);
            }
        }
    }
}