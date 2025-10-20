<?php
namespace App\Models;

use Core\Database;
use PDO;

/**
 * BaseModel simple para PHP 7.4
 */
abstract class BaseModel
{
    /** @var PDO */
    protected $db;
    /** @var string */
    protected $table = '';
    /** @var string */
    protected $primaryKey = 'id';
    /** @var array */
    protected $fillable = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all($limit = 100, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :off,:lim";
        $st = $this->db->prepare($sql);
        $st->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        $st->bindValue(':lim', (int)$limit,  PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute([':id'=>$id]);
        return $st->fetch() ?: null;
    }

    public function insert(array $data) {
        $data = $this->onlyFillable($data);
        if (!$data) return false;
        $cols = array_keys($data);
        $place = array_map(function($c){ return ':'.$c; }, $cols);
        $sql = "INSERT INTO {$this->table} (".implode(',', $cols).") VALUES (".implode(',', $place).")";
        $st = $this->db->prepare($sql);
        $st->execute($this->prefixKeys($data));
        return (int)$this->db->lastInsertId();
    }

    public function updateById($id, array $data) {
        $data = $this->onlyFillable($data);
        if (!$data) return false;
        $sets = [];
        foreach ($data as $c => $_) $sets[] = "{$c} = :{$c}";
        $sql = "UPDATE {$this->table} SET ".implode(',', $sets)." WHERE {$this->primaryKey} = :_id";
        $data['_id'] = $id;
        $st = $this->db->prepare($sql);
        return $st->execute($this->prefixKeys($data));
    }

    public function deleteById($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $st = $this->db->prepare($sql);
        return $st->execute([':id'=>$id]);
    }

    protected function onlyFillable(array $data) {
        if (empty($this->fillable)) return $data;
        $out = [];
        foreach ($this->fillable as $k) {
            if (array_key_exists($k, $data)) $out[$k] = $data[$k];
        }
        return $out;
    }

    protected function prefixKeys(array $data) {
        $out = [];
        foreach ($data as $k=>$v) {
            $out[$k[0] === ':' ? $k : ':'.$k] = $v;
        }
        return $out;
    }
}
