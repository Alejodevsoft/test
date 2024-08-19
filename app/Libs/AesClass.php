<?php

namespace App\Libs;

class AesClass {
    
    public static function encrypt($data){
        $method = "AES-256-CBC";
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        
        $secret = $_ENV['SECRET'];
        $encrypted = openssl_encrypt($data, $method, $secret, OPENSSL_RAW_DATA);
        $encrypted_base64 = base64_encode($encrypted);
        return $encrypted_base64;
    }
    public static function decrypt($data){
        $method = "AES-256-CBC";
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        
        $secret = $_ENV['SECRET'];
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, $method, $secret, OPENSSL_RAW_DATA);
        return $decrypted;
    }
}

?>