<?php
namespace App\Models;

class Empleado extends BaseModel
{
    protected $table = 'empleado';
    protected $primaryKey = 'id_empleado';
    protected $fillable = ['nombre','apellido','ci','puesto','sueldo','password_hash','rol'];

    public function listar() {
        $sql = "SELECT id_empleado,nombre,apellido,ci,puesto,sueldo,rol
                FROM {$this->table}
                ORDER BY rol DESC, nombre, apellido";
        return $this->db->query($sql)->fetchAll();
    }

    public function findByCI($ci) {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE ci=:ci LIMIT 1");
        $st->execute([':ci'=>$ci]);
        return $st->fetch() ?: null;
    }

    public function ciExists($ci) {
        $st = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE ci=:ci LIMIT 1");
        $st->execute([':ci'=>$ci]);
        return (bool)$st->fetchColumn();
    }

    public function setPassword($id, $plain) {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        return $this->updateById($id, ['password_hash'=>$hash]);
    }

    public function verifyLogin($ci, $plain) {
        $u = $this->findByCI($ci);
        if (!$u) return null;
        if (!empty($u['password_hash']) && password_verify($plain, $u['password_hash'])) {
            return $u;
        }
        return null;
    }

    public function countAdmins() {
        $st = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE rol='admin'");
        return (int)$st->fetchColumn();
    }

    public function isAdmin($id) {
        $st = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE {$this->primaryKey}=? AND rol='admin' LIMIT 1");
        $st->execute([$id]);
        return (bool)$st->fetch();
    }
}
