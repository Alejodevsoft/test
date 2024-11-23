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

    public function getConsoleById($id){
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

    public function getConsoleByMondayId($monday_id){
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "SELECT c.id,c.api_key_monday,c.client_id_docusign,c.user_id_docusign,c.server_docusign,c.private_key,c.docusign_verify FROM $table
                JOIN console AS c ON $table.console_id = c.id
                WHERE monday_id = :monday_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':monday_id', $monday_id, PDO::PARAM_STR);
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

    public function createConsoleUnpaid($data){
        $api_key_monday = AesClass::encrypt($data['api_key']);
        $zero   = 0;
        $sql = "INSERT INTO console (client_name,api_key_monday,paid,docusign_verify) VALUES (:client_name,:api_key_monday, :paid, :docusign_verify)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_name', $data['client_name'], PDO::PARAM_STR);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->bindParam(':paid', $zero, PDO::PARAM_INT );
        $stmt->bindParam(':docusign_verify', $zero, PDO::PARAM_INT );
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateConsole($id, $data){
        $sql = "UPDATE console SET 
                server_docusign = :server_docusign,
                client_id_docusign = :client_id_docusign,
                user_id_docusign = :user_id_docusign,
                private_key = :private_key
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':server_docusign', $data['server_docusign'], PDO::PARAM_INT);
        $stmt->bindParam(':client_id_docusign', $data['client_id_docusign'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id_docusign', $data['user_id_docusign'], PDO::PARAM_STR);
        $stmt->bindParam(':private_key', $data['private_key'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function verifyConsole($id){
        $sql = "UPDATE console SET
                docusign_verify = 1 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function unVerifyConsole($id){
        $sql = "UPDATE console SET
                docusign_verify = 0 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
