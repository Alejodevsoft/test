<?php
namespace App\Libs;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\BillingApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Configuration;
use Exception;
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
            $data_return['success']     = true;
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

    public static function getTemplates($server_docusign,$clientId,$userId,$privateKey){
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient = new ApiClient();
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        $jwt_scope = 'signature';

        $response = $apiClient->requestJWTUserToken(
            $clientId,
            $userId,
            $privateKey,
            $jwt_scope
        );
        
        $accessToken = $response[0]->getAccessToken();
        $accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

        if ($server_docusign == 0) {
            $basePath = 'https://demo.docusign.net/restapi';
        } else if($server_docusign > 0 && $server_docusign < 5){
            $number = $server_docusign;
            $basePath = "https://na$number.docusign.net/restapi";
        } elseif($server_docusign == 5){
            $basePath = "https://ca.docusign.net/restapi";
        } elseif($server_docusign == 6){
            $basePath = "https://au.docusign.net/restapi";
        } elseif($server_docusign == 7){
            $basePath = "https://eu.docusign.net/restapi";
        }

        $configuration = new Configuration();
        $configuration->setHost($basePath);
        $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

        $apiClient = new ApiClient($configuration);
        
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        

        $templatesApi = new TemplatesApi($apiClient);

        try {
            $templatesList = $templatesApi->listTemplates($accountId);
            $templatesData = [];
            foreach ($templatesList->getEnvelopeTemplates() as $template) {
                $templatesData[] = [
                    'template_id' => $template->getTemplateId(),
                    'template_name' => $template->getName()
                ];
            }
            
            $data_return['success'] = true;
            $data_return['data']    = $templatesData;

        } catch (ApiException $e) {
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        } catch (Throwable $e){
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        }
        return $data_return;
    }

    public static function getAccountData($server_docusign, $clientId, $userId, $privateKey) {
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient = new ApiClient();
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        $jwt_scope = 'signature';

        $response = $apiClient->requestJWTUserToken(
            $clientId,
            $userId,
            $privateKey,
            $jwt_scope
        );
        
        $accessToken = $response[0]->getAccessToken();
        $accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

        if ($server_docusign == 0) {
            $basePath = 'https://demo.docusign.net/restapi';
        } else if($server_docusign > 0 && $server_docusign < 5){
            $number = $server_docusign;
            $basePath = "https://na$number.docusign.net/restapi";
        } elseif($server_docusign == 5){
            $basePath = "https://ca.docusign.net/restapi";
        } elseif($server_docusign == 6){
            $basePath = "https://au.docusign.net/restapi";
        } elseif($server_docusign == 7){
            $basePath = "https://eu.docusign.net/restapi";
        }

        $configuration = new Configuration();
        $configuration->setHost($basePath);
        $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

        $apiClient = new ApiClient($configuration);
        
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        

        $acocuntsApi = new AccountsApi($apiClient);

        try {
            $accountData    = $acocuntsApi->getAccountInformation($accountId);
            $data_return    = [
                'name'                                  => $accountData['account_name'],
                'plan_name'                             => $accountData['plan_name'],
                'billing_period_end_date'               => $accountData['billing_period_end_date'],
                'billing_period_envelopes_allowed'      => $accountData['billing_period_envelopes_allowed'],
                'billing_period_envelopes_sent'         => $accountData['billing_period_envelopes_sent'],
                'billing_period_envelopes_available'    => (is_numeric($accountData['billing_period_envelopes_allowed']) && is_numeric($accountData['billing_period_envelopes_sent']))?$accountData['billing_period_envelopes_allowed']-$accountData['billing_period_envelopes_sent']:'0'
            ];
            $data_return['success'] = true;
            $data_return['data']    = $data_return;

        } catch (ApiException $e) {
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        } catch (Throwable $e){
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        }
        return $data_return;
    }    

    public static function getTemplateInfo($server_docusign,$clientId,$userId,$privateKey,$templateId){
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient = new ApiClient();
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        $jwt_scope = 'signature';

        $response = $apiClient->requestJWTUserToken(
            $clientId,
            $userId,
            $privateKey,
            $jwt_scope
        );
        
        $accessToken = $response[0]->getAccessToken();
        $accountId = $apiClient->getUserInfo($response[0]->getAccessToken())[0]["accounts"][0]["account_id"];

        if ($server_docusign == 0) {
            $basePath = 'https://demo.docusign.net/restapi';
        } else if($server_docusign > 0 && $server_docusign < 5){
            $number = $server_docusign;
            $basePath = "https://na$number.docusign.net/restapi";
        } elseif($server_docusign == 5){
            $basePath = "https://ca.docusign.net/restapi";
        } elseif($server_docusign == 6){
            $basePath = "https://au.docusign.net/restapi";
        } elseif($server_docusign == 7){
            $basePath = "https://eu.docusign.net/restapi";
        }

        $configuration = new Configuration();
        $configuration->setHost($basePath);
        $configuration->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

        $apiClient = new ApiClient($configuration);
        
        if ($server_docusign == 0) {
            $oauthBasePath = 'account-d.docusign.com';
        } else {
            $oauthBasePath = 'account.docusign.com';
        }
        $apiClient->getOAuth()->setOAuthBasePath($oauthBasePath);
        

        $templatesApi = new TemplatesApi($apiClient);

        try {
            $templateInfo = $templatesApi->listRecipients($accountId,$templateId);

            $signers    = [];
            foreach ($templateInfo['signers'] as $signer) {
                $signers[]  = [
                    'role'  => $signer['role_name'],
                    'order' => $signer['routing_order']
                ];
            }

            $data_return['success'] = true;
            $data_return['data']    = $signers;

        } catch (ApiException $e) {
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        } catch (Throwable $e){
            $data_return['success'] = false;
            $data_return['error']   = $e->getMessage();
        }
        return $data_return;
    }
}
?>