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

    /**
     * Send
     * 
     * Se envía el sobre a los signers configurados de monday
     *
     * @return null
     */
    public function send(){
        //Lee el dato de entrada de monday
        $datamonday = json_decode(file_get_contents('php://input'));

        //Consulta el cliente de monday
        $model = new MainModel();
        $clients = $model->getConsoleByMondayId($datamonday->payload->inputFields->userId);

        $apiKeyMonday   = '';
        if ($clients != null && $clients['active'] == 1) {
            $apiKeyMonday   = AesClass::decrypt($clients['api_key_monday']);

            //Recupera los signers(email, nombre y rol) a enviar los sobres, el template de DocuSign y el tipo de envío(idividual o grupal)
            $responseMonday = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values{id,text,type}subitems{id,name,column_values{type,text}}}}'));

            //Recupera la columnda para el cambio de estado
            $responseMonday3 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: status){id}}}'));

            //Recupera la info de conexión a Docusign
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

            //Recupera el token de docusign para las peticiones
            $response = $apiClient->requestJWTUserToken(
                $clientId,
                $userId,
                $privateKey,
                $jwt_scope
            );

            $accessToken = $response[0]->getAccessToken();
            $accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

            //Se configura la conexón a docusign
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
       
            $configuration = new Configuration();
            $configuration->setHost($basePath);
            $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);
        
            $apiClient = new ApiClient($configuration);
            $envelopeApi = new EnvelopesApi($apiClient);
            $signers = [];
            $signersCustom = [];
            $signersInProgress = [];

            //Setea los datos del template signtype recuperados del item de monday
            $template_id    = '';
            $sign_type      = '';
            foreach ($responseMonday->data->items[0]->column_values as $key => $column) {
                if ($column->type === 'text') {
                    $template_id    = $column->text;
                }
                if ($column->id === 'estado__1') {
                    $sign_type  = $column->text;
                }
            }

            //Se recorren los signers a enviar el sobre
            foreach ($responseMonday->data->items[0]->subitems as $key => $signer ) {
                $email      = '';
                $role_name  = '';
                foreach ($signer->column_values as $column) {
                    if ($column->type == 'email') {
                        $email  = $column->text;
                    }
                    if ($column->type == 'text') {
                        $role_name  = $column->text;
                    }
                }
                $signers[$key] = new TemplateRole([
                    'email' => $email,
                    'name' => $signer->name,
                    'role_name' => $role_name
                ]);
                $signersCustom[]    = $role_name.'__'.$signer->id;
                $signersInProgress[]    = $signer->id;
            }
            //Se agregan los campos custom para el envío a DocuSign
            $array_custom_fields    = [
                new TextCustomField([
                    'name' => 'signType',
                    'value' => $sign_type,
                    'show' => 'false'
                ]),
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
                    'name' => 'columnIdStatus',
                    'value' => $responseMonday3->data->items[0]->column_values[0]->id,
                    'show' => 'false'
                ]),
                new TextCustomField([
                    'name' => 'signers',
                    'value' => implode('||',$signersCustom),
                    'show' => 'false'
                ])
            ];

            //Configura el envelop a enviar a DocuSign, diferente en caso de ser envío grupal o onvidual
            if ($sign_type == 'Joint') {
                //En caso se ser grupal, se configura el campo de file al item, y se envía el arreglo de los signers
                $responseMonday2 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: file){id}}}'));
                $array_custom_fields[]  = new TextCustomField([
                    'name' => 'columnId',
                    'value' => $responseMonday2->data->items[0]->column_values[0]->id,
                    'show' => 'false'
                ]);
                $array_custom_fields[]  = new TextCustomField([
                    'name' => 'signers',
                    'value' => implode('||',$signersCustom),
                    'show' => 'false'
                ]);
                $customFields = new CustomFields([
                    'text_custom_fields' => $array_custom_fields
                ]);
                $envelopeDefinition = new EnvelopeDefinition([
                    'template_id' => $template_id,
                    'template_roles' => $signers,
                    'status' => 'sent',
                    'custom_fields' => $customFields
                ]);
                $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
            }else{
                //En caso se ser individual, se configura el campo de file al subitem de cada usuario, y se envía el signer individual. 
                //Y adicional se envía un envelop por cada usuario
                $responseMonday2 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){subitems{column_values(types:file){id}}}}'));
                foreach ($signers as $key => $signer) {
                    $array_custom_fields[]  = new TextCustomField([
                        'name' => 'columnId',
                        'value' => $responseMonday2->data->items[0]->subitems[0]->column_values[0]->id,
                        'show' => 'false'
                    ]);
                    $array_custom_fields[]  = new TextCustomField([
                        'name' => 'signers',
                        'value' => $signersCustom[$key],
                        'show' => 'false'
                    ]);
                    $customFields = new CustomFields([
                        'text_custom_fields' => $array_custom_fields
                    ]);
                    $envelopeDefinition = new EnvelopeDefinition([
                        'template_id' => $template_id,
                        'template_roles' => [$signer],
                        'status' => 'sent',
                        'custom_fields' => $customFields
                    ]);
                    $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
                }
            }
            //Se cambia elestado de los signers a "In progress"
            Monday::setSignersInProgress($apiKeyMonday,$signersInProgress);
        }elseif ($clients != null){
            $responseMonday3 = json_decode(Monday::genericCurl($apiKeyMonday,'{items(ids: ['.$datamonday->payload->inputFields->itemId.']){column_values(types: status){id}}}'));
            $query = '
                mutation {
                    change_column_value(
                        item_id: ' . $datamonday->payload->inputFields->itemId . ',
                        board_id: ' . $datamonday->payload->inputFields->boardId . ',
                        column_id: "' . $responseMonday3->data->items[0]->column_values[0]->id . '",
                        value: "{\"label\":\"Unsent\"}"
                    ) {
                        id
                    }
                }
            ';
            Monday::genericCurlJsonQuery(AesClass::decrypt($clients['api_key_monday']),$query);
        }
    }

    /**
     * Upload
     * 
     * Se suben los archivos de la firma de DocuSign en el tablero de monday
     *
     * @return null
     */
    public function upload(){
        //Se lee toda la data de entrada de docusign
        $docusign = json_decode(file_get_contents('php://input'));

        
        //Se lee toda la data de monday de la entrada de docusign
        $datosMonday = array_column($docusign->data->envelopeSummary->customFields->textCustomFields,'value','name');
        $clients    = $this->main_model->getConsoleByMondayId($datosMonday['userIdMonday']);
        $signer     = null;
        if ($docusign->data->envelopeSummary->recipients != null && sizeof($docusign->data->envelopeSummary->recipients->signers) > 0 ) {
            //Se recuperan los signers de la plantilla de DocuSign, y se organizan en un array assoc
            $signers_docusign   = array_column($docusign->data->envelopeSummary->recipients->signers,'status','roleName');
            //Se recuperan los signers de monday
            $signers_monday     = explode('||',$datosMonday['signers']);
            if (sizeof($signers_monday) > 0) {
                foreach ($signers_monday as $signer_monday) {
                    //Se lee la data del signer de monday
                    $data_signer    = explode('__',$signer_monday);
                    //Se guarda la data del signer de monday para usarlo en caso de firma individual
                    $signer         = $data_signer;
                    if (isset($signers_docusign[$data_signer[0]])) {
                        //Verifica el resultado de la firma del signer y setea el subitem en monday
                        if ($signers_docusign[$data_signer[0]] == 'completed') {
                            Monday::setSignerStatus(AesClass::decrypt($clients['api_key_monday']),$data_signer[1],'Completed');
                        }elseif ($signers_docusign[$data_signer[0]] == 'declined') {
                            Monday::setSignerStatus(AesClass::decrypt($clients['api_key_monday']),$data_signer[1],'Declined');
                        }
                    }
                }
            }
        }
        //Proceso cuando la firma se completó
        if ($docusign->data->envelopeSummary->status == "completed") {

            //Se leen los documentos
            foreach ($docusign->data->envelopeSummary->envelopeDocuments as $key =>$document) {
                //Se lee el base64 de los files
                $base64File     = $document->PDFBytes;
                $decodedFile    = base64_decode($base64File);
                if ($key == 0) {
                    $tempFilePath = 'Document-'.$document->documentIdGuid.'.pdf';
                }else{
                    $tempFilePath = 'Certificate-'.$document->documentIdGuid.'.pdf';
                }
                //Se escribe el archivo temporal que se enviará a monday
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

                if ($datosMonday['signType'] == 'Joint') {
                    //Envía el archivo firmado de manera grupal al item de monday
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
                }else{
                    //Envía el archivo firmado de manera individual al subitem de monday
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.monday.com/v2/file',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => array('query' => 'mutation ($file: File!) {add_file_to_column (item_id:'.$signer[1].', column_id: "'.$datosMonday['columnId'].'", file: $file) {id }}','variables[file]'=> new CURLFile($tempFilePath)),
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: '.AesClass::decrypt($clients['api_key_monday'])
                        ),
                    ));
                }

                $response = curl_exec($curl);

                curl_close($curl);

                unlink($tempFilePath);
            }
            $curl = curl_init();

            //Setea el Signed del item en caso de que sea firma grupal
            if ($datosMonday['signType'] == 'Joint') {
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
        }elseif ($docusign->data->envelopeSummary->status == "declined") {
            //Setea el Signed del item en caso de que sea firma grupal
            if ($datosMonday['signType'] == 'Joint') {
                $query = '
                    mutation {
                        change_column_value(
                            item_id: ' . $datosMonday['pulseId'] . ',
                            board_id: ' . $datosMonday['boardId'] . ',
                            column_id: "' . $datosMonday['columnIdStatus'] . '",
                            value: "{\"label\":\"Declined\"}"
                        ) {
                            id
                        }
                    }
                ';
    
                Monday::genericCurlJsonQuery(AesClass::decrypt($clients['api_key_monday']),$query);
            }
        }
    }

    /**
     * SignaturesQuery
     * 
     * Se setean los subitems de monday cuando se cambia el estado del item a setn
     *
     * @return null
     */
    public function signaturesQuery(){
        $datamonday = json_decode(file_get_contents('php://input'));
        
        $client     = $this->main_model->getConsoleByMondayId($datamonday->payload->inputFields->userId);

        $monday_response    = Monday::getTemplateId(AesClass::decrypt($client['api_key_monday']),$datamonday->payload->inputFields->itemId);
        if ($monday_response['success']) {
            $template_id    = $monday_response['data']['template_id'];
            $template_info  = Docusign::getTemplateInfo(
                $client['server_docusign'],
                AesClass::decrypt($client['client_id_docusign']),
                AesClass::decrypt($client['user_id_docusign']),
                AesClass::decrypt($client['private_key']),
                $template_id
            );
            if ($template_info['success']) {
                Monday::clearSigners(AesClass::decrypt($client['api_key_monday']),$monday_response['data']['subitems']);
                Monday::setSignerFields(AesClass::decrypt($client['api_key_monday']),$datamonday->payload->inputFields->itemId,$template_info['data']);
            }
        }
    }
}