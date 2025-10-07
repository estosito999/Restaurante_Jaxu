<?php
namespace Core;

class Database {
    protected $pdo;

    public function __construct() {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=restaurant', 'usuario', 'contraseña');
    }

    public function getPdo() {
        return $this->pdo;
    }
}