<?php
namespace App\Models;

class CierreCaja extends BaseModel {

    /** Ventas pendientes de cierre (solo facturas activas) */
    public function ventasPendientes(): array {
        $sql = "SELECT rv.id_registro, rv.id_factura, rv.monto_venta, f.fecha_hora
                FROM registro_venta rv
                JOIN factura f ON f.id_factura = rv.id_factura
                WHERE rv.id_cierre IS NULL AND f.estado = 'activa'
                ORDER BY f.fecha_hora ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Total de ventas pendientes (solo activas) */
    public function totalPendiente(): float {
        $sql = "SELECT COALESCE(SUM(rv.monto_venta),0) AS t
                FROM registro_venta rv
                JOIN factura f ON f.id_factura = rv.id_factura
                WHERE rv.id_cierre IS NULL AND f.estado='activa'";
        $row = $this->db->query($sql)->fetch();
        return (float)($row['t'] ?? 0);
    }

    /** Cierra caja y asigna id_cierre a TODAS las ventas pendientes (activas) */
    public function cerrarCaja(int $idEmpleado, string $fechaHoraCierre, float $montoEfectivoContado): int {
        $this->db->beginTransaction();
        try {
            // 1) total ventas pendientes
            $total = $this->totalPendiente();
            if ($total <= 0) {
                // nada que cerrar
                $this->db->rollBack();
                throw new \RuntimeException('No hay ventas pendientes para cerrar.');
            }

            $dif = $montoEfectivoContado - $total;

            // 2) crear cierre
            $ins = $this->db->prepare(
               "INSERT INTO cierre_caja(fecha_hora_cierre, monto_total_ventas, monto_efectivo_contado, diferencia, id_empleado)
                VALUES(?,?,?,?,?)"
            );
            $ins->execute([$fechaHoraCierre, $total, $montoEfectivoContado, $dif, $idEmpleado]);
            $idCierre = (int)$this->db->lastInsertId();

            // 3) asignar cierre a todos los registros pendientes (consulta única y segura)
            $up = $this->db->prepare("
                UPDATE registro_venta rv
                JOIN factura f ON f.id_factura = rv.id_factura
                SET rv.id_cierre = ?
                WHERE rv.id_cierre IS NULL AND f.estado='activa'
            ");
            $up->execute([$idCierre]);

            $this->db->commit();
            return $idCierre;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Historial de cierres */
    public function listarCierres(): array {
        $st = $this->db->query("SELECT c.*, e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
                                FROM cierre_caja c
                                JOIN empleado e ON e.id_empleado = c.id_empleado
                                ORDER BY c.fecha_hora_cierre DESC");
        return $st->fetchAll();
    }

    public function getCierre(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM cierre_caja WHERE id_cierre=?");
        $st->execute([$id]);
        $cierre = $st->fetch();
        if (!$cierre) return null;

        $det = $this->db->prepare("SELECT rv.*, f.fecha_hora
                                   FROM registro_venta rv
                                   JOIN factura f ON f.id_factura = rv.id_factura
                                   WHERE rv.id_cierre = ? ORDER BY f.fecha_hora");
        $det->execute([$id]);
        $cierre['registros'] = $det->fetchAll();
        return $cierre;
    }
}
