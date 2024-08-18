<?php

namespace App\Libs;

class AesClass {
    private $method = "AES-256-CBC";

    public function encrypt($data){
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        
        $secret = $_ENV['SECRET'];
        $encrypted = openssl_encrypt($data, $this->method, $secret, OPENSSL_RAW_DATA);
        $encrypted_base64 = base64_encode($encrypted);
        return $encrypted_base64;
    }
    public function decrypt($data){
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        
        $secret = $_ENV['SECRET'];
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, $this->method, $secret, OPENSSL_RAW_DATA);
        return $decrypted;
    }
}

?>