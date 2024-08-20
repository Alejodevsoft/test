<?php
namespace App\Libs;

use DocuSign\eSign\Client\ApiClient;
use Throwable;

class Docusign{
    public static function verifyConset($clientId,$userId,$privateKey){
        $apiClient = new ApiClient();
        $apiClient->getOAuth()->setOAuthBasePath('account-d.docusign.com');
        $data_return    = [];
        try {
            $response = $apiClient->requestJWTUserToken(
                $clientId,
                $userId,
                $privateKey,
                'signature'
            );
            $data_return['success']     = false;
            $data_return['response']    = $response;
        } catch (Throwable $th) {
            $data_return['success']     = false;
            $data_return['redirect']    = false;
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type=code&'
                . http_build_query(
                    [
                        'scope' => "impersonation+" . 'signature',
                        'client_id' => $clientId,
                        'redirect_uri' => 'https://monday.com'
                    ]
                );
                $data_return['redirect']        = true;
                $data_return['redirect_url']    = $authorizationURL;
            }
        }
        return $data_return;
    }
}
?>