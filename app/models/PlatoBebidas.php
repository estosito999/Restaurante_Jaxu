<?php
namespace App\Models;

class PlatoBebidas extends BaseModel
{
    protected $table = 'plato_bebidas'; // ajusta si tu tabla se llama distinto
    protected $primaryKey = 'id_plato';
    protected $fillable = ['nombre','precio','stock','tipo']; // tipo: 'plato'|'bebida'

    public function buscarPorNombre($q, $limit = 20) {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE nombre LIKE :q ORDER BY nombre LIMIT :lim");
        $st->bindValue(':q', '%'.$q.'%');
        $st->bindValue(':lim', (int)$limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function disminuirStock($id, $cantidad) {
        $st = $this->db->prepare("UPDATE {$this->table} SET stock = stock - :c WHERE {$this->primaryKey} = :id AND stock >= :c");
        return $st->execute([':c'=>(int)$cantidad, ':id'=>$id]);
    }

    public function aumentarStock($id, $cantidad) {
        $st = $this->db->prepare("UPDATE {$this->table} SET stock = stock + :c WHERE {$this->primaryKey} = :id");
        return $st->execute([':c'=>(int)$cantidad, ':id'=>$id]);
    }
}
