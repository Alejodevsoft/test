<?php

namespace App\Controllers;

use App\Libs\AesClass;
use App\Libs\Monday;
use App\Models\MainModel;

class MainController{
    public function index(){
        if ($_SERVER['REQUEST_METHOD']==='GET') {
            include 'app/Views/form_monday.php';
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['user_id']) || empty($_POST['api_key'])) {
                $this->loadErrorMain('Data not reported');
            }
            $user_name  = $this->verifiyMondayUser($_POST);
            include 'app/Views/form_docusign.php';
        }
    }

    private function verifiyMondayUser($request){
        $validate_user      = Monday::validateUser($request['user_id'],$request['api_key']);
        if (!$validate_user['success']) {
            $this->loadErrorMain($validate_user['error']);
        }
        $model  = new MainModel();
        $client = $model->getClientByMondayId($request['user_id']);
        if ($client == null) {
            $model->createClientUnpaid($request['user_id'],$request['api_key']);
        }
        $validate_purchase  = Monday::validatePurchase();
        if (!$validate_purchase['success']) {
            $this->loadErrorMain('Not purchase');
        }
        return $validate_user['data']['name'];
    }

    public function test(){
        $aes_class  = new Monday();
        $aes_class->validateUser("35497500","eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM3NDc5NjAwMCwiYWFpIjoxMSwidWlkIjozNTQ5NzUwMCwiaWFkIjoiMjAyNC0wNi0yMFQxNzoxNDoyNS4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTAyMDk1OTMsInJnbiI6InVzZTEifQ.Kap9bbJUy0P7BqvYZsgXk8cywgocFYYfyY2yVZZzLao");
    }

    public function test2(){
        $model = new MainModel();
        $clients = $model->getAllClients();

        foreach ($clients as $client) {
            echo "ID: " . $client['id'] . " - User ID Monday: " . $client['user_id_monday'] . " - API Key Monday: " . $client['api_key_monday'] . "<br>";
        }

        // Datos del cliente
        $user_id_monday = '35497500';
        $api_key_monday = 'sjkhfdsjfbsjdlkgbfsldkfns';

        // $newClientId = $model->createClient($user_id_monday, $api_key_monday);
        // echo "<br>Nuevo cliente creado con ID: " . $newClientId;
    }

    private function loadErrorMain($text_error){
        session_start();
        $_SESSION['error']  = $text_error;
        header('Location: ./');
        exit;
    }
}