<?php

namespace App\Libs;

class Monday{
    public static function validateUser($userId, $apiKey){
        $response   = self::curlMonday($apiKey,"{users(ids:[$userId]){name}}");
        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
        if (empty($data->data->users)) {
            $return['success'] = false;
            $return['error'] = "Error User ID";

            return $return;
        }
        $return['success'] = true;
        $return['data']['name'] = $data->data->users[0]->name;

        return $return;
    }
    public static function getUsers($apiKey){
        $response   = self::curlMonday($apiKey,"query{users{name email id is_admin}}");
        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
        if (empty($data->data->users)) {
            $return['success'] = false;
            $return['error'] = "Error not users";

            return $return;
        }
        $return['success'] = true;
        $return['data'] = $data->data->users;

        return $return;
    }

    public static function validatePurchase(){
        $return['success'] = true;
        return $return;
    }

    private static function curlMonday($apiKey,$query){
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
            CURLOPT_POSTFIELDS => '{
                "query":"'.$query.'"
            }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $apiKey",
                "Content-Type: application/json",
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
?>
query{users{name email id is_admin}}