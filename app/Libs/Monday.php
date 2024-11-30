<?php

namespace App\Libs;

class Monday{
    public static function validateUser($userId, $apiKey){
        $response   = self::curlMonday($apiKey,"{users(ids:[$userId]){name}account{name,slug}}");
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
        if (!empty($data->data->account->name)) {
            $return['data']['company_name'] = $data->data->account->name;
        }else{
            $return['data']['company_name'] = $data->data->account->slug;
        }
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

    public static function getBoards($apiKey) {
        $allBoards = [];
        $hasMore = true;
        $page = 1;
        $limit = 5000;
    
        while ($hasMore) {
            $response = self::curlMonday($apiKey,"{boards(limit:$limit,page:$page){id,name,type}}");
    
            $data = json_decode($response);
    
            if (isset($data->errors)) {
                $return['success'] = false;
                $return['error'] = "Error Api Key";
    
                return $return;
            }
    
            if (empty($data->data->boards)) {
                $return['success'] = false;
                $return['error'] = "Error not boards";
    
                return $return;
            }
    
            $allBoards = array_merge($allBoards, $data->data->boards);
    
            $hasMore = count($data->data->boards) === $limit;
            $page++;
        }
    
        $return['success'] = true;
        $return['data'] = $allBoards;
    
        return $return;
    }

    public static function getEnvelops($apiKey, $boardId) {
        $response   = self::curlMonday($apiKey,"{boards(ids:$boardId){items_page{items{name,id,column_values(ids:\\\"texto__1\\\"){text,column{title}}}}}}");
        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
        $data = $data->data->boards[0]->items_page->items;
        
        if (!empty($data[0]->column_values[0]->column) && $data[0]->column_values[0]->column->title == "ID Template") {
            $return['success'] = true;
            $return['data'] = $data;
        }else {
            $return['success'] = false;
            $return['error'] = "This board isn't compatible with MDS";
        }

        return $return;
    }

    public static function getTemplateId($apiKey,$item_id){
        $response   = self::curlMonday($apiKey,"query{items(ids:$item_id){column_values(types:text){text}}}");
        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
        if (empty($data->data->items)) {
            $return['success'] = false;
            $return['error'] = "Error not data";

            return $return;
        }
        $return['success'] = true;
        $return['data'] = $data->data->items[0]->column_values[0]->text;

        return $return;
    }

    public static function setTemplate($apiKey, $boardId, $contractId, $templateId){
        $response = self::curlMondayJsonQuery($apiKey, "mutation {change_multiple_column_values(item_id:$contractId, board_id:$boardId, column_values: \"{\\\"texto__1\\\":\\\"$templateId\\\"}\") {id}}");

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
    }

    public static function setSignerFields($apiKey,$item_id,$signers){
        if (sizeof($signers) < 1) {
            $return['success'] = false;
            $return['error'] = "Error empty signers";
        }
        $success    = true; 
        $error      = ''; 
        foreach ($signers as $signer) {
            $response   = self::curlMonday($apiKey,'mutation { create_subitem(parent_item_id: '.$item_id.', item_name: \\"Insert name\\", column_values: \\"{\\\\\\"texto__1\\\\\\":\\\\\\"'.$signer['role'].'\\\\\\",\\\\\\"n_meros__1\\\\\\":\\\\\\"'.$signer['order'].'\\\\\\"}\\") { id } }');
            $data = json_decode($response);
            if (isset($data->errors)) {
                $success = false;
                $error .= "Error Api Key";
            }
            if (empty($data->data->items)) {
                $success = false;
                $error .= "Error not data";
            }
            $success = true;
            $data = [''];
        }
        return [
            'success'   => $success,
            'data'      => $data,
            'error'     => $error
        ];
    }

    public static function setSignerStatus($apiKey,$boardId,$signer_item_id){
        $response = self::curlMondayJsonQuery($apiKey, '
                mutation {
                    change_column_value(
                        item_id: '.$signer_item_id.',
                        board_id: '.$boardId.',
                        column_id: "status",
                        value: "{\"label\":\"Signed\"}"
                    ) {
                        id
                    }
                }
            ');

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
    }

    public static function validatePurchase(){
        $return['success'] = true;
        return $return;
    }

    public static function genericCurl($apiKey,$query){
        return self::curlMonday($apiKey,$query);
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

    private static function curlMondayJsonQuery($apiKey,$query){
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
            CURLOPT_POSTFIELDS => json_encode(
                [
                    'query' => $query
                ]
            ),
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