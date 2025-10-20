<?php
namespace App\Models;

class DetalleVenta extends BaseModel
{
    protected $table = 'detalle_venta';
    protected $primaryKey = 'id_detalle';
    protected $fillable = ['id_factura','id_plato','cantidad','precio','subtotal'];

    public function agregarItem($idFactura, $idPlato, $cantidad, $precioUnit) {
        $subtotal = $cantidad * $precioUnit;
        return $this->insert([
            'id_factura' => $idFactura,
            'id_plato'   => $idPlato,
            'cantidad'   => (int)$cantidad,
            'precio'     => (float)$precioUnit,
            'subtotal'   => (float)$subtotal,
        ]);
    }

    public function listarPorFactura($idFactura) {
        $st = $this->db->prepare("SELECT d.*, p.nombre AS plato
                                  FROM detalle_venta d
                                  LEFT JOIN plato_bebidas p ON p.id_plato = d.id_plato
                                  WHERE d.id_factura = :id
                                  ORDER BY d.id_detalle ASC");
        $st->execute([':id'=>$idFactura]);
        return $st->fetchAll();
    }

    public function totalPorFactura($idFactura) {
        $st = $this->db->prepare("SELECT SUM(subtotal) FROM detalle_venta WHERE id_factura=:id");
        $st->execute([':id'=>$idFactura]);
        return (float)$st->fetchColumn();
    }
}
