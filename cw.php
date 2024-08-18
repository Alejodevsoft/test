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
use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Model\ConnectCustomConfiguration;
use DocuSign\eSign\Model\ConnectEventData;

use DocuSign\eSign\Model\ConnectSettings;

$clientId = 'e239487b-5548-4744-aebb-b47e0c0cf1d6';
$userId = '5d0b70fc-aef4-42b2-b756-0c704c907909';
$oauthBasePath = 'account-d.docusign.com';
$privateKeyPath = 'private.key';
$expiresIn = 3600;

$apiClient = new ApiClient();
$apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
$privateKey = file_get_contents($privateKeyPath, true);

$scope = 'signature';
$jwt_scope = $scope;

try {
    $response = $apiClient->requestJWTUserToken(
        $clientId,
        $userId,
        $privateKey,
        $jwt_scope
    );
} catch (Throwable $th) {
    if (strpos($th->getMessage(), "consent_required") !== false) {
        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type= code&'
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


$config = new Configuration();
$config->setHost('https://demo.docusign.net/restapi');
$config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

$apiClient = new ApiClient($config);

$accountsApi = new AccountsApi($apiClient);

$connectConfig = new ConnectCustomConfiguration();
$connectConfig->setName("My Webhook Configuration");
$connectConfig->setUrlToPublishTo('https://your-webhook-url.com/webhook'); // URL del webhook
$connectConfig->setAllowEnvelopePublish(true);



$connectSettings = new ConnectSettings();
$connectSettings->setEnvelopeEvents([
    'Sent', 'Delivered', 'Completed', 'Declined', 'Voided'
]);
$connectSettings->setRecipientEvents([
    'Sent', 'Delivered', 'Completed', 'Declined', 'AuthenticationFailed', 'AutoResponded'
]);
$connectConfig->setSettings($connectSettings);

$accountsApi->createCustomConfiguration($accountId, $connectConfig);

