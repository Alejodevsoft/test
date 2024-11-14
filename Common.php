<?php

function template_init($view,$data=[]){
    if (sizeof($data) > 0) {
        foreach ($data as $var_name => $value) {
            $$var_name  = $value;
        }
    }
    include 'app/Views/head.php';
    include 'app/Views/'.$view.'.php';
    include 'app/Views/foot.php';
}

function view($view,$data=[]){
    if (sizeof($data) > 0) {
        foreach ($data as $var_name => $value) {
            $$var_name  = $value;
        }
    }
    include 'app/Views/'.$view.'.php';
}

function set_error($error_message=''){
    $_SESSION['error']  = $error_message;
}

function is_error_message(){
    return isset($_SESSION['error']);
}

function error_message(){
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
    return $error;
}

function set_login($valid = false,$user_data=[]){
    $_SESSION['logged']     = $valid;
    $_SESSION['user_data']  = $user_data;
}

function is_logged(){
    return isset($_SESSION['logged'])&&$_SESSION['logged'];
}

function get_user_data(){
    return $_SESSION['user_data'];
}

function redirect($route = null){
    if (empty($route)) {
        $route  = './';
    }else{
        $route  = './'.$route;
    }
    ob_end_clean();
    header('Location: '.$route);
    exit;
}
?>