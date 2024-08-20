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
        $sql = "SELECT * FROM client";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getClientById($id){
        $sql = "SELECT * FROM client WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $return = $stmt->fetch();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    public function getClientByMondayId($monday_id){
        $sql = "SELECT * FROM client WHERE user_id_monday = :user_id_monday";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id_monday', $monday_id, PDO::PARAM_INT);
        $stmt->execute();
        $return = $stmt->fetch();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    public function createClient($user_id_monday,$api_key_monday){
        $sql = "INSERT INTO client (user_id_monday, api_key_monday) VALUES (:user_id_monday, :api_key_monday)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id_monday', $user_id_monday, PDO::PARAM_STR);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function createClientUnpaid($user_id_monday,$api_key_monday_normal){
        $api_key_monday = AesClass::encrypt($api_key_monday_normal);
        $zero   = 0;
        $sql = "INSERT INTO client (user_id_monday, api_key_monday,paid,docusign_verify) VALUES (:user_id_monday, :api_key_monday, :paid, :docusign_verify)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id_monday', $user_id_monday, PDO::PARAM_STR);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_INT);
        $stmt->bindParam(':paid', $zero, PDO::PARAM_INT );
        $stmt->bindParam(':docusign_verify', $zero, PDO::PARAM_INT );
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateClient($id, $data){
        $sql = "UPDATE client SET 
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

    public function deleteClient($id){
        $sql = "DELETE FROM client WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
