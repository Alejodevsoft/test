<?php

namespace App\Libs;

/**
 * AesClass Class
 *
 * @category Lib
 * @package  App\Libs
 * @author   Fabián-V,Sebastián-R,Smith-T,Alejandro-M
 */
class AesClass {
    
    /**
     * Encrypt
     * 
     * Encripta el dato de etrada
     *
     * @param string $data
     * @return string $encrypted_base64 Encrypted data
     */
    public static function encrypt($data){
        $method = "AES-256-CBC";
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        
        $secret = $_ENV['SECRET'];
        $encrypted = openssl_encrypt($data, $method, $secret, OPENSSL_RAW_DATA);
        $encrypted_base64 = base64_encode($encrypted);
        return $encrypted_base64;
    }
    
    /**
     * Encrypt
     * 
     * Desencripta el dato de etrada
     *
     * @param string $data
     * @return string $decrypted Decrypted data
     */
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