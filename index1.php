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

$clientId = 'e239487b-5548-4744-aebb-b47e0c0cf1d6';
$userId = '5d0b70fc-aef4-42b2-b756-0c704c907909';
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
                'redirect_uri' => 'http://localhost:80/callback.php'
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

    // Crear el documento
    $document = new Document([
        'document_base64' => base64_encode(file_get_contents('pdf.pdf')),
        'name' => 'Documento a Firmar', // Nombre del documento
        'file_extension' => 'pdf', // Extensión del archivo
        'document_id' => '1'

    ]);

    // Crear el firmante
    $signer = new Signer([
        'email' => $recipientEmail,
        'name' => $recipientName,
        'recipient_id' => '1',
        'routing_order' => '1'
    ]);

    $signHere = new SignHere([
        'document_id' => '1',
        'page_number' => '1',
        'recipient_id' => '1',
        'tab_label' => 'Firma',
        'x_position' => '100',
        'y_position' => '150'
    ]);

    $tabs = new Tabs(['sign_here_tabs' => [$signHere]]);
    $signer->setTabs($tabs);

    // Crear los destinatarios
    $recipients = new Recipients(['signers' => [$signer]]);

    // Crear el sobre
    $envelopeDefinition = new EnvelopeDefinition([
        'email_subject' => 'Por favor, firme este documento',
        'documents' => [$document],
        'recipients' => $recipients,
        'status' => 'sent' // Para enviar el sobre inmediatamente
    ]);

    // Enviar el sobre
    $envelopeSummary = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);

    return $envelopeSummary;
}

// Ejemplo de uso
try {
    $recipientName = 'rocha';
    $recipientEmail = 'jeissonstobar@gmail.com';
    $envelopeSummary = sendDocumentToSign($recipientName, $recipientEmail, $accessToken, $accountId, $basePath);
    print_r($envelopeSummary);

} catch (Exception $e) {
    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
}
?>