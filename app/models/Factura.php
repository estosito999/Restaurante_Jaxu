<?php
namespace App\Models;

class Factura extends BaseModel {

    /** Crea factura + detalles + registro_venta (pendiente de cierre) */
    public function createFactura(int $idCliente, int $idEmpleado, string $fechaHora, array $items, float $ivaRate = 0.13): int {
        // $items: [ ['id_plato'=>int, 'cantidad'=>int, 'precio_unitario'=>float], ... ]
        $this->db->beginTransaction();
        try {
            // Calcular subtotal (suma de cantidad * precio_unitario)
            $subtotal = 0.0;
            foreach ($items as $it) {
                $subtotal += ((float)$it['precio_unitario']) * ((int)$it['cantidad']);
            }

            // Calcular IVA y monto total
            $iva = round($subtotal * $ivaRate, 2);
            $montoTotal = round($subtotal + $iva, 2);

            // Insertar factura solo con el monto total
            $st = $this->db->prepare("
                INSERT INTO factura (fecha_hora, monto_total, id_cliente, id_empleado)
                VALUES (?,?,?,?)
            ");
            $st->execute([$fechaHora, $montoTotal, $idCliente, $idEmpleado]);
            $idFactura = (int)$this->db->lastInsertId();

            // Insertar detalles de la factura
            $insDet = $this->db->prepare("
                INSERT INTO detalle_venta (id_factura, id_plato, cantidad, precio_unitario, subtotal)
                VALUES (?,?,?,?,?)
            ");
            foreach ($items as $it) {
                $line = ((float)$it['precio_unitario']) * ((int)$it['cantidad']); // Calcular subtotal por detalle
                $insDet->execute([$idFactura, (int)$it['id_plato'], (int)$it['cantidad'], (float)$it['precio_unitario'], $line]);
            }

            // Registro de venta (pendiente de cierre)
            $insReg = $this->db->prepare("INSERT INTO registro_venta (id_factura, id_cierre, monto_venta) VALUES (?, NULL, ?)");
            $insReg->execute([$idFactura, $montoTotal]);

            $this->db->commit();
            return $idFactura;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Obtiene factura + cliente + empleado + detalles */
    public function getFactura(int $id): ?array {
        $st = $this->db->prepare("
            SELECT f.*, 
                   c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.nit AS cliente_nit,
                   e.nombre AS empleado_nombre, e.apellido AS empleado_apellido, e.ci AS empleado_ci
            FROM factura f
            JOIN cliente c  ON c.id_cliente  = f.id_cliente
            JOIN empleado e ON e.id_empleado = f.id_empleado
            WHERE f.id_factura = ?
        ");
        $st->execute([$id]);
        $factura = $st->fetch();
        if (!$factura) return null;

        // Obtener detalles de la factura
        $det = $this->db->prepare("
            SELECT d.*, p.nombre AS plato_nombre
            FROM detalle_venta d
            JOIN plato p ON p.id_plato = d.id_plato
            WHERE d.id_factura = ?
            ORDER BY d.id_detalle
        ");
        $det->execute([$id]);
        $factura['detalles'] = $det->fetchAll();
        return $factura;
    }

    /** Cancela una factura */
    public function cancelFactura(int $idFactura, int $idEmpleado, ?string $motivo = null): bool {
        $this->db->beginTransaction();
        try {
            // Verificar estado de la factura y si ya fue cerrada
            $st = $this->db->prepare("
                SELECT f.estado,
                    (SELECT rv.id_cierre FROM registro_venta rv WHERE rv.id_factura = f.id_factura LIMIT 1) AS id_cierre
                FROM factura f
                WHERE f.id_factura = ?
                FOR UPDATE
            ");
            $st->execute([$idFactura]);
            $row = $st->fetch();
            if (!$row) throw new \RuntimeException('Factura no existe');
            if ($row['estado'] === 'anulada') throw new \RuntimeException('La factura ya está anulada');
            if (!empty($row['id_cierre'])) throw new \RuntimeException('No se puede anular: la factura ya fue incluida en un cierre de caja');

            // Borrar el registro de venta pendiente (si existe)
            $del = $this->db->prepare("DELETE FROM registro_venta WHERE id_factura = ?");
            $del->execute([$idFactura]);

            // Marcar la factura como anulada con trazabilidad
            $up = $this->db->prepare("
                UPDATE factura
                SET estado='anulada', anulada_por=?, anulada_motivo=?, anulada_at=?
                WHERE id_factura=?
            ");
            $up->execute([$idEmpleado, $motivo, date('Y-m-d H:i:s'), $idFactura]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

