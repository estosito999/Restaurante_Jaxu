<?php
namespace App\Models;

class DetalleVenta extends BaseModel {

    // Crear detalle de venta
    public function create(array $data): int {
        $st = $this->db->prepare("INSERT INTO detalle_venta(id_factura, id_plato, cantidad, precio_unitario, subtotal)
                                  VALUES(?,?,?,?,?)");
        $st->execute([
            $data['id_factura'],
            $data['id_plato'],
            $data['cantidad'],
            $data['precio_unitario'],
            $data['subtotal']
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Obtener detalles de venta por ID
    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM detalle_venta WHERE id_detalle = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // Listar todos los detalles de una factura
    public function getByFactura(int $idFactura): array {
        $st = $this->db->prepare("SELECT dv.*, p.nombre AS plato_nombre
                                  FROM detalle_venta dv
                                  LEFT JOIN plato_bebidas p ON p.id_plato = dv.id_plato
                                  WHERE dv.id_factura = ?");
        $st->execute([$idFactura]);
        return $st->fetchAll();
    }
}
