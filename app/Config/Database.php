<?php

namespace App\Config;

use PDO;
use PDOException;

class Database{
    private $host;
    private $db;
    private $port;
    private $user;
    private $pass;
    private $charset;
    private $pdo;
    private $error;

    public function __construct(){
        $this->host = $_ENV['DB_HOST'];
        $this->db   = $_ENV['DB_NAME'];
        $this->port = $_ENV['DB_PORT'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];
        $this->charset = 'utf8mb4';

        // Incluir el puerto en el DSN
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new PDOException($this->error, (int)$e->getCode());
        }
    }

    public function connect(){
        return $this->pdo;
    }

    public function disconnect(){
        $this->pdo = null;
    }

}
