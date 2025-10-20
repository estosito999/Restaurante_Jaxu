<?php
namespace App\Models;

class Cliente extends BaseModel
{
    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    protected $fillable = ['nombre','ci','telefono','direccion']; // ajusta a tu esquema real

    public function findByCI($ci) {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE ci=:ci LIMIT 1");
        $st->execute([':ci'=>$ci]);
        return $st->fetch() ?: null;
    }
}
