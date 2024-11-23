<?php

function template_init($view,$data=[]){
    if (empty($data['page_title'])) {
        $data['page_title'] = 'MDs';
    }else{
        $data['page_title'] = 'MDs - '.$data['page_title'];
    }
    if (sizeof($data) > 0) {
        foreach ($data as $var_name => $value) {
            $$var_name  = $value;
        }
    }
    include 'app/Views/admin/head.php';
    include 'app/Views/admin/'.$view.'.php';
    include 'app/Views/admin/foot.php';
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

function set_open_tab($url=''){
    $_SESSION['open_tab']  = $url;
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
        $route  = base_url();
    }else{
        $route  = base_url().$route;
    }
    ob_end_clean();
    header('Location: '.$route);
    exit;
}

function base_url(){
    return (($_SERVER['REQUEST_SCHEME']=='https')?'https://':'http://').$_SERVER['HTTP_HOST'].str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
}
?>