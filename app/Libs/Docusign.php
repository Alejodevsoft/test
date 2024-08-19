<?php
namespace App\Libs;
class Docusign{
    public static function verifyConset(){
        // try {
        //     $response = $apiClient->requestJWTUserToken(
        //         $clientId,
        //         $userId,
        //         $privateKey,
        //         $jwt_scope
        //     );
        // } catch (Throwable $th) {
        //     if (strpos($th->getMessage(), "consent_required") !== false) {
        //         $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type=code&'
        //         . http_build_query(
        //             [
        //                 'scope' => "impersonation+" . $jwt_scope,
        //                 'client_id' => $clientId,
        //                 'redirect_uri' => 'https://monday.com'
        //             ]
        //         );
        //         header('Location: ' . $authorizationURL);
        //     }
        // }
    }
}
?>