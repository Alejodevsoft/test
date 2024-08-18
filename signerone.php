<?php

require_once 'vendor/autoload.php';

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\SignHere;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\TemplateRole;

$clientId = 'ab10da02-2550-48d1-8b06-6cf52787d18f';
$userId = 'b7f39eec-c463-4526-b500-a5aa2c44a8f7';
$oauthBasePath = 'account-d.docusign.com';
$privateKeyPath = 'private.key';

$expiresIn = 3600;

$apiClient = new ApiClient();
$apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
$privateKey = file_get_contents($privateKeyPath, true);

$jwt_scope = 'signature';

try {
    $response = $apiClient->requestJWTUserToken(
        $clientId,
        $userId,
        $privateKey,
        $jwt_scope
    );
} catch (Throwable $th) {
    if (strpos($th->getMessage(), "consent_required") !== false) {
        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type=code&'
        . http_build_query(
            [
                'scope' => "impersonation+" . $jwt_scope,
                'client_id' => $clientId,
                'redirect_uri' => 'https://monday.com'
            ]
        );
        header('Location: ' . $authorizationURL);
    }
}

$accessToken = $response[0]->getAccessToken();
$accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

$basePath = 'https://demo.docusign.net/restapi';

function sendDocumentToSign($recipientName, $recipientEmail, $accessToken, $accountId, $basePath){
    $configuration = new Configuration();
    $configuration->setHost($basePath);
    $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

    $apiClient = new ApiClient($configuration);
    $envelopeApi = new EnvelopesApi($apiClient);

    // Configurar el firmante 

    // $signer = new TemplateRole([
    //     'email' => 'dm9378323@gmail.com',
    //     'name' => 'Jhoan Sebastian Rocha Martinez',
    //     'role_name' => 'Signer' // Asegúrate de que coincida con el rol en la plantilla


    // ]);

    // Configurar vasrios firmantes

    $signers = [
        new TemplateRole([
            'email' => 'dm9378323@gmail.com',
            'name' => 'Jhoan Sebastian Rocha Martinez',
            'role_name' => 'Signer' // Asegúrate de que coincida con el rol en la plantilla
        ]),
        new TemplateRole([
            'email' => 'jeissonstobar@gmail.com',
            'name' => 'Jeisson Smith Tobar Aguirre',
            'role_name' => 'Signer2' // Asegúrate de que coincida con el rol en la plantilla
        ]),
    ];

    // Crear el objeto de sobre
    $envelopeDefinition = new EnvelopeDefinition([
        'template_id' => 'd98e4966-4035-46bd-84f6-4d873a3dd0f5',
        'template_roles' => $signers,
        'status' => 'sent' // Enviar inmediatamente
    ]);

    // Enviar la solicitud de sobre
    $results = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);

    return $results;
}

// Ejemplo de uso
try {
    $recipientName = 'Firma de documento';
    $recipientEmail = 'dm9378323@gmail.com';
    $results = sendDocumentToSign($recipientName, $recipientEmail, $accessToken, $accountId, $basePath);
    print_r($results);

} catch (Exception $e) {
    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
}
?>