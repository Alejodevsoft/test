<?php

namespace App\Models;

use App\Config\Database;
use App\Libs\AesClass;
use PDO;

/**
 * Database Class
 *
 * @category Model
 * @package  App\Models
 * @author   Fabián-V,Sebastián-R,Smith-T,Alejandro-M
 */
class MainModel{
    /**
     * Db connection
     *
     * @var db instancia de la conexión a la base de datos
     */
    private $db;

    public function __construct(){
        $database = Database::getInstance();
        $this->db = $database->connect();
    }

    /**
     * Get console by id
     * 
     * Obtiene la console usando el id como filtro
     *
     * @param String $id identificador de la console
     * @return array|null console
     */
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

    /**
     * Get console by monday key
     * 
     * Obtiene la console usando el api key de monday como filtro
     *
     * @param String $api_key Api key de la console
     * @return array|null console
     */
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

    /**
     * Get console by monday id
     * 
     * Obtiene la console usando el user_id en la tabla de users como filtro
     *
     * @param String $monday_id ID del user de Monday
     * @return array|null console
     */
    public function getConsoleByMondayId($monday_id){
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "SELECT c.id,c.api_key_monday,c.client_id_docusign,c.user_id_docusign,c.server_docusign,c.private_key,c.docusign_verify,active FROM $table
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

    /**
     * Get user by monday id
     * 
     * Obtiene el user usando el user_id como filtro
     *
     * @param String $monday_id ID del user de Monday
     * @return array|null user
     */
    public function getUserByMondayId($monday_id){
        if (!is_numeric(substr($monday_id,0,1))){
            return null;
        }
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

    /**
     * Get users by console id
     * 
     * Obtiene los user usando el console_id como filtro
     *
     * @param String $console_id ID de la console
     * @return array|null [users]
     */
    public function getUsersByConsoleId($console_id){
        $tables = range(1,9);
        $unionQueries = [];
        $params = [];

        foreach ($tables as $i) {
            $paramName = ":console_id_$i";
            $unionQueries[] = "
                SELECT *
                FROM user{$i}
                WHERE console_id = $paramName
            ";
            $params[$paramName] = $console_id;
        }
        $sql = implode(" UNION ALL ", $unionQueries)." ORDER BY monday_id ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $paramName => $value) {
            $stmt->bindValue($paramName, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
        $return = $stmt->fetchAll();
        if ($return===false) {
            return null;
        }
        return $return;
    }

    /**
     * Create console unpaid
     * 
     * Crea la console por primera vez
     *
     * @param array $data de la info del la console
     * @return int console ID
     */
    public function createConsoleUnpaid($data){
        $api_key_monday = AesClass::encrypt($data['api_key']);
        $zero   = 0;
        $sql = "INSERT INTO console (client_account_id,client_name,api_key_monday,paid,docusign_verify) VALUES (:client_account_id,:client_name,:api_key_monday, :paid, :docusign_verify)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_account_id', $data['client_account_id'], PDO::PARAM_STR);
        $stmt->bindParam(':client_name', $data['client_name'], PDO::PARAM_STR);
        $stmt->bindParam(':api_key_monday', $api_key_monday, PDO::PARAM_STR);
        $stmt->bindParam(':paid', $zero, PDO::PARAM_INT );
        $stmt->bindParam(':docusign_verify', $zero, PDO::PARAM_INT );
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update console
     * 
     * Actualiza la console
     *
     * @param string $id ID de la console
     * @param array $data de la info del la console
     * @return int rows afected
     */
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

    /**
     * Verify console
     * 
     * Activa la bandera de verificación de la console
     *
     * @param string $id ID de la console
     * @return int rows afected
     */
    public function verifyConsole($id){
        $sql = "UPDATE console SET
                docusign_verify = 1 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Unverify console
     * 
     * Desactiva la bandera de verificación de la console
     *
     * @param string $id ID de la console
     * @return int rows afected
     */
    public function unVerifyConsole($id){
        $sql = "UPDATE console SET
                docusign_verify = 0 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Delete console
     * 
     * Borra la console
     *
     * @param string $id ID de la console
     * @return int rows afected
     */
    public function deleteConsole($id){
        $sql = "DELETE FROM console WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Create multiple users
     * 
     * Crea los users a partir de la lista
     *
     * @param string $console_id ID de la console
     * @param array $users Lista users 
     * @return bool finalizado
     */
    public function createMultipleUsers($console_id,$users) {
        // Agrupar users por el dígito inicial de su ID
        $groupedUsers = [];
    
        foreach ($users as $user) {
            $tableSuffix = substr($user->id, 0, 1);
            $tableName = "user" . $tableSuffix;
            $groupedUsers[$tableName][] = $user;
        }
    
        // Insertar users en sus respectivas tablas
        foreach ($groupedUsers as $table => $usersInTable) {
            $sql = "INSERT INTO $table (console_id, monday_id, name, email, password) VALUES ";
            $values = [];
            $params = [];
    
            foreach ($usersInTable as $index => $user) {
                $values[] = "(:console_id{$index}, :monday_id{$index}, :name{$index}, :email{$index}, :password{$index})";
                $params[":console_id{$index}"] = $console_id;
                $params[":monday_id{$index}"] = $user->id;
                $params[":name{$index}"] = $user->name;
                $params[":email{$index}"] = $user->email;
                $params[":password{$index}"] = $user->password;
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

    /**
     * Create user
     * 
     * Crea un user un user
     *
     * @param string $console_id ID de la console
     * @param array $user data del user de Monday
     * @return int rows afected
     */
    public function createUser($console_id,$user){
        $table  = 'user'.substr($user['monday_id'],0,1);
        $sql = "INSERT INTO $table (console_id,monday_id,name,email) VALUES (:console_id,:monday_id,:name,:email)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':console_id', $console_id, PDO::PARAM_INT);
        $stmt->bindParam(':monday_id', $user['monday_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $user['name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Set active user
     * 
     * Setea el valor de activación de un user
     *
     * @param boolean $active Estado nuevo para el ususario
     * @param string $console_id ID de la console
     * @param String $monday_id ID del user de Monday
     * @return int rows afected
     */
    public function setActiveUser($active=false,$console_id,$monday_id){
        $active = $active?1:0;
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "UPDATE $table set active = :active WHERE console_id = :console_id AND monday_id = :monday_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':active', $active, PDO::PARAM_INT);
        $stmt->bindParam(':console_id', $console_id, PDO::PARAM_INT);
        $stmt->bindParam(':monday_id', $monday_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Delete user
     * 
     * Borra un user
     *
     * @param string $console_id ID de la console
     * @param String $monday_id ID del user de Monday
     * @return int rows afected
     */
    public function deleteUser($console_id,$monday_id){
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "DELETE FROM $table WHERE console_id = :console_id AND monday_id = :monday_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':console_id', $console_id, PDO::PARAM_INT);
        $stmt->bindParam(':monday_id', $monday_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Set active user
     * 
     * Setea el valor de activación de un user
     *
     * @param String $monday_id ID del user de Monday
     * @param string $console_id ID de la console
     * @param String $password Nueva contraseña del user
     * @return int rows afected
     */
    public function updatePassword($monday_id,$console_id,$password){
        $table  = 'user'.substr($monday_id,0,1);
        $sql = "UPDATE $table set password = :password WHERE console_id = :console_id AND monday_id = :monday_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':console_id', $console_id, PDO::PARAM_INT);
        $stmt->bindParam(':monday_id', $monday_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
