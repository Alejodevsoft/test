<?php

namespace App\Models;

use App\Config\Database;
use App\Libs\AesClass;
use PDO;

class MainModel{
    private $db;

    public function __construct(){
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAllClients(){
        $sql = "SELECT * FROM console";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getClientById($id){
        $sql = "SELECT * FROM console WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $return = $stmt->fetch();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    public function getConsoleByMondayKey($api_key){
        $api_key_monday = AesClass::encrypt($api_key);
        $sql = "SELECT * FROM console WHERE api_key_monday = :api_key_monday";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->execute();
        $return = $stmt->fetch();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    public function getUserByMondayId($monday_id){
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "SELECT * FROM $table WHERE monday_id = :monday_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':monday_id', $monday_id, PDO::PARAM_INT);
        $stmt->execute();
        $return = $stmt->fetch();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    public function createConsole($api_key_monday){
        $sql = "INSERT INTO console (api_key_monday) VALUES (:api_key_monday)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function createConsoleUnpaid($api_key_monday_normal){
        $api_key_monday = AesClass::encrypt($api_key_monday_normal);
        $zero   = 0;
        $sql = "INSERT INTO console (api_key_monday,paid,docusign_verify) VALUES (:api_key_monday, :paid, :docusign_verify)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->bindParam(':paid', $zero, PDO::PARAM_INT );
        $stmt->bindParam(':docusign_verify', $zero, PDO::PARAM_INT );
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateConsole($id, $data){
        $sql = "UPDATE console SET 
                user_id_monday = :user_id_monday, 
                api_key_monday = :api_key_monday, 
                server_docusign = :server_docusign, 
                client_id_docusign = :client_id_docusign, 
                user_id_docusign = :user_id_docusign, 
                private_key = :private_key, 
                paid = :paid, 
                docusign_verify = :docusign_verify 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id_monday', $data['user_id_monday'], PDO::PARAM_STR);
        $stmt->bindParam(':api_key_monday', $data['api_key_monday'], PDO::PARAM_STR);
        $stmt->bindParam(':server_docusign', $data['server_docusign'], PDO::PARAM_INT);
        $stmt->bindParam(':client_id_docusign', $data['client_id_docusign'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id_docusign', $data['user_id_docusign'], PDO::PARAM_STR);
        $stmt->bindParam(':private_key', $data['private_key'], PDO::PARAM_STR);
        $stmt->bindParam(':paid', $data['paid'], PDO::PARAM_INT);
        $stmt->bindParam(':docusign_verify', $data['docusign_verify'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function deleteConsole($id){
        $sql = "DELETE FROM console WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function createMultipleUsers($console_id,$users) {
        // Agrupar usuarios por el dígito inicial de su ID
        $groupedUsers = [];
    
        foreach ($users as $user) {
            $tableSuffix = substr($user->id, 0, 1);
            $tableName = "user" . $tableSuffix;
            $groupedUsers[$tableName][] = $user;
        }
    
        // Insertar usuarios en sus respectivas tablas
        foreach ($groupedUsers as $table => $usersInTable) {
            $sql = "INSERT INTO $table (console_id, monday_id, name, email) VALUES ";
            $values = [];
            $params = [];
    
            foreach ($usersInTable as $index => $user) {
                $values[] = "(:console_id{$index}, :monday_id{$index}, :name{$index}, :email{$index})";
                $params[":console_id{$index}"] = $console_id;
                $params[":monday_id{$index}"] = $user->id;
                $params[":name{$index}"] = $user->name;
                $params[":email{$index}"] = $user->email;
            }
    
            $sql .= implode(", ", $values);
            $stmt = $this->db->prepare($sql);
    
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
    
            // Ejecutar la inserción en la tabla correspondiente
            $stmt->execute();
        }
    
        return true;
    }
}
