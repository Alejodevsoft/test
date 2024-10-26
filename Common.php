<?php
function view($view,$data=[]){
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
?>