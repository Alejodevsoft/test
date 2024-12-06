<?php

namespace App\Libs;

/**
 * Monday Class
 *
 * @category Lib
 * @package  App\Libs
 * @author   Fabián-V,Sebastián-R,Smith-T,Alejandro-M
 */
class Monday{

    /**
     * Validate user
     * 
     * Verifica la existencia del usuario con la api de Monday
     *
     * @param string $userId ID de usuario de Monday
     * @param string $apiKey Api Key de Monday
     * @return array $return
     */
    public static function validateUser($userId, $apiKey){
        $response   = self::curlMonday($apiKey,"{users(ids:[$userId]){name,email,is_admin}account{name,slug}}");
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
        $return['data']['name']         = $data->data->users[0]->name;
        $return['data']['email']        = $data->data->users[0]->email;
        $return['data']['is_admin']     = $data->data->users[0]->is_admin;
        $return['data']['account_id']   = $data->account_id;

        return $return;
    }

    /**
     * Get users
     * 
     * Obtiene todos los usuarios en la api de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @return array $return
     */
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

    /**
     * Get boards
     * 
     * Obtiene todas los boards en la api de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @return array $return
     */
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

    /**
     * Get envelops
     * 
     * Obtiene todas los evelops de un board en la api de Monday, cuando el board es compatible
     *
     * @param string $apiKey Api Key de Monday
     * @param string $boardId ID del board de Monday
     * @return array $return
     */
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

    /**
     * Get template ID
     * 
     * Obtiene el ID del template de Docusign almacenado en el envelop de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param string $itemId ID del item/envelop de Monday
     * @return array $return
     */
    public static function getTemplateId($apiKey,$itemId){
        $response   = self::curlMonday($apiKey,"query{items(ids:$itemId){column_values(types:text){text}}}");
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

    /**
     * Set template
     * 
     * Cambia ID del template de Docusign almacenado en el envelop de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param string $boardId ID del board de Monday
     * @param string $itemId ID del item/envelop de Monday
     * @param string $templateId ID del template de Docusign
     * @return array $return
     */
    public static function setTemplate($apiKey, $boardId, $itemId, $templateId){
        $response = self::curlMondayJsonQuery($apiKey, "mutation {change_multiple_column_values(item_id:$itemId, board_id:$boardId, column_values: \"{\\\"texto__1\\\":\\\"$templateId\\\"}\") {id}}");

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Api Key";

            return $return;
        }
    }

    /**
     * Set signer fields
     * 
     * Crea los subitems dentro del item/envelop de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param string $itemId ID del item/envelop de Monday
     * @param array $signers Lista de firmantes 
     * @return array $return
     */
    public static function setSignerFields($apiKey,$itemId,$signers){
        if (sizeof($signers) < 1) {
            $return['success'] = false;
            $return['error'] = "Error empty signers";
        }
        $success    = true; 
        $error      = ''; 
        foreach ($signers as $signer) {
            $response   = self::curlMonday($apiKey,'mutation { create_subitem(parent_item_id: '.$itemId.', item_name: \\"Insert name\\", column_values: \\"{\\\\\\"texto__1\\\\\\":\\\\\\"'.$signer['role'].'\\\\\\",\\\\\\"n_meros__1\\\\\\":\\\\\\"'.$signer['order'].'\\\\\\"}\\") { id } }');
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

    /**
     * Set signer status
     * 
     * Cambia el valor del estado del firmante en el subitem de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param string $signerItemId ID del subitem de Monday
     * @param array $status Nuevo estado del firmante
     * @return array $return
     */
    public static function setSignerStatus($apiKey,$signerItemId,$status){
        $boardData  = self::curlMonday($apiKey,'{items(ids: '.$signerItemId.') {board{id}}}');
        $boardData = json_decode($boardData);
        if (isset($boardData->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Board Api Key";

            return $return;
        }
        $response = self::curlMondayJsonQuery($apiKey, '
                mutation {
                    change_column_value(
                        item_id: '.$signerItemId.',
                        board_id: '.$boardData->data->items[0]->board->id.',
                        column_id: "status",
                        value: "{\"label\":\"'.$status.'\"}"
                    ) {
                        id
                    }
                }
            ');

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error signer status";

            return $return;
        }
    }

    /**
     * Set signers in progress
     * 
     * Cambia el valor del estado a "In progress" de todos los firmantes en item/envelop de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param array $signers Lista de firmantes 
     * @return array $return
     */
    public static function setSignersInProgress($apiKey,$signers){
        $boardData  = self::curlMonday($apiKey,'{items(ids: '.$signers[0].') {board{id}}}');
        $boardData = json_decode($boardData);
        if (isset($boardData->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Board Api Key";

            return $return;
        }
        $query  = 'mutation{';
        foreach ($signers as $key => $signer) {
            $query  .= 'item'.$key.': change_column_value(
                            item_id: '.$signer.',
                            board_id: '.$boardData->data->items[0]->board->id.',
                            column_id: "status",
                            value: "{\"label\":\"In progress\"}"
                        ) {
                            id
                        }';
        }
        $query  .= '}';
        $response = self::curlMondayJsonQuery($apiKey, $query);

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Signers in progress";

            return $return;
        }
    }

    /**
     * Set signers declined
     * 
     * Cambia el valor del estado a "Declined" de todos los firmantes en item/envelop de Monday
     *
     * @param string $apiKey Api Key de Monday
     * @param array $signers Lista de firmantes 
     * @return array $return
     */
    public static function setSignersDeclined($apiKey,$signers){
        $boardData  = self::curlMonday($apiKey,'{items(ids: '.$signers[0].') {board{id}}}');
        $boardData = json_decode($boardData);
        if (isset($boardData->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Board Api Key";

            return $return;
        }
        $query  = 'mutation{';
        foreach ($signers as $key => $signer) {
            $query  .= 'item'.$key.': change_column_value(
                            item_id: '.$signer.',
                            board_id: '.$boardData->data->items[0]->board->id.',
                            column_id: "status",
                            value: "{\"label\":\"Declined\"}"
                        ) {
                            id
                        }';
        }
        $query  .= '}';
        $response = self::curlMondayJsonQuery($apiKey, $query);

        $data = json_decode($response);
        if (isset($data->errors)) {
            $return['success'] = false;
            $return['error'] = "Error Signers in progress";

            return $return;
        }
    }

    /**
     * Validate purchase
     * 
     * Valida el estado de la suscripción de la implementación en monday
     *
     * @param string $apiKey Api Key de Monday
     * @return array $return
     */
    public static function validatePurchase($apiKey){
        $return['success'] = true;
        return $return;
    }

    public static function genericCurl($apiKey,$query){
        return self::curlMonday($apiKey,$query);
    }

    public static function genericCurlJsonQuery($apiKey,$query){
        return self::curlMondayJsonQuery($apiKey,$query);
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