<?php

define('PREF_SESSION_NAME', 'mds_');

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
    $_SESSION[PREF_SESSION_NAME.'error']  = $error_message;
}

function is_error_message(){
    return isset($_SESSION[PREF_SESSION_NAME.'error']);
}

function error_message(){
    $error = $_SESSION[PREF_SESSION_NAME.'error'];
    unset($_SESSION[PREF_SESSION_NAME.'error']);
    return $error;
}

function set_reverify($rev){
    $_SESSION[PREF_SESSION_NAME.'reverify']  = $rev;
}

function is_reverify(){
    return isset($_SESSION[PREF_SESSION_NAME.'reverify']);
}

function reverify(){
    $reverify   = $_SESSION[PREF_SESSION_NAME.'reverify'];
    unset($_SESSION[PREF_SESSION_NAME.'reverify']);
    return $reverify;
}

function set_docusign_new($data=''){
    $_SESSION[PREF_SESSION_NAME.'docusign_new']  = $data;
}

function is_docusign_new(){
    return isset($_SESSION[PREF_SESSION_NAME.'docusign_new']);
}

function docusign_new(){
    $docusign_new  = $_SESSION[PREF_SESSION_NAME.'docusign_new'];
    unset($_SESSION[PREF_SESSION_NAME.'docusign_new']);
    return $docusign_new;
}

function set_login($valid = false,$user_data=[]){
    $_SESSION[PREF_SESSION_NAME.'logged']     = $valid;
    $_SESSION[PREF_SESSION_NAME.'user_data']  = $user_data;
}

function is_logged(){
    return isset($_SESSION[PREF_SESSION_NAME.'logged'])&&$_SESSION[PREF_SESSION_NAME.'logged'];
}

function get_user_data(){
    return $_SESSION[PREF_SESSION_NAME.'user_data'];
}

function logout(){
    $_SESSION[PREF_SESSION_NAME.'logged'] = false;
    unset($_SESSION[PREF_SESSION_NAME.'user_data']);
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

function write_error_log($th){
    $log_id     = randomString(20);
    $timer      = getmicrotime().' |'.$log_id.'| ';

    $message    = 'EXCEPTION - '.$timer.'Method: '.$_SERVER['REQUEST_METHOD'].'. Origin: '.$_SERVER['REMOTE_ADDR'].'. Message: '.$th->getMessage()."\n";
    $message    .= $timer.'File: '.$th->getFile().'('.$th->getLine().")\n";

    $traceArray = explode("\n", $th->getTraceAsString());
    $formattedTrace = "";
    foreach ($traceArray as $line) {
        $formattedTrace .= $timer . $line . "\n";
    }
    $logContent = $message . $formattedTrace;
    file_put_contents('logs/log-' . date('Y-m-d') . '.log', $logContent, FILE_APPEND);
}

function write_error_route_log($message){
    $log_id     = randomString(20);
    $timer      = getmicrotime().' |'.$log_id.'| ';
    $logContent = 'ROUTE - '.$timer.'Method: '.$_SERVER['REQUEST_METHOD'].'. Origin: '.$_SERVER['REMOTE_ADDR'].'. Message: '.$message."\n";
    file_put_contents('logs/log-' . date('Y-m-d') . '.log', $logContent, FILE_APPEND);
}

function write_warning_log($message){
    $log_id     = randomString(20);
    $timer      = getmicrotime().' |'.$log_id.'| ';
    $logContent = 'WARNING - '.$timer.'Method: '.$_SERVER['REQUEST_METHOD'].'. Origin: '.$_SERVER['REMOTE_ADDR'].'. Message: '.$message."\n";
    file_put_contents('logs/log-' . date('Y-m-d') . '.log', $logContent, FILE_APPEND);
}

function write_info_log($method_class,$message){
    $log_id     = randomString(20);
    $timer      = getmicrotime().' |'.$log_id.'| ';
    $logContent = 'INFO - '.$timer.'Method: '.$_SERVER['REQUEST_METHOD'].'. Origin: '.$_SERVER['REMOTE_ADDR']."\n";
    $logContent .= $timer.'From: '.$method_class.'. Message: '.$message."\n";
    file_put_contents('logs/log-' . date('Y-m-d') . '.log', $logContent, FILE_APPEND);
}

function getmicrotime(){
    $microtime  = microtime(true);
    return date("Y-m-d H:i:s", $microtime) . sprintf(".%03d", ($microtime - floor($microtime)) * 10000);
}

function randomString($size){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $size; $i++) {
        $randstring .= $characters[rand(0, strlen($characters)-1)];
    }
    return $randstring;
}
?>