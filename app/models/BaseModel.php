<?php
namespace App\Models;

class BaseModel {
    protected \PDO $db;
    public function __construct(\PDO $pdo) { $this->db = $pdo; }
}
