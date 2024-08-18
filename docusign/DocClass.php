<?php

class DocClass {

    public function validateRequest(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../');
        }
        echo json_encode($_POST);
        if (empty($_POST['user_id']) || empty($_POST['user_id'])) {
            session_start();
            $_SESSION['error']  = 'Data not reported';
            header('Location: ../');
        }
        
    }
}
?>