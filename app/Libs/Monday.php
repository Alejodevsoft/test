<?php

namespace App\Libs;

class Monday{
    public static function validateUser($userId, $apiKey){
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
          CURLOPT_POSTFIELDS => array('query' => "{users(ids:[$userId]){name}}"),
          CURLOPT_HTTPHEADER => array(
            "Authorization: $apiKey"
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
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
    public static function validatePurchase(){
        $return['success'] = true;
        return $return;
    }
}