<?php
namespace App\Models;

class AperturaCaja extends BaseModel {

    public function abrir(int $idEmpleado, string $fechaHora, float $saldoInicial, string $detalleGastos): int {
        // Cierra cualquier apertura huérfana (seguridad, opcional)
        $this->db->prepare("UPDATE apertura_caja SET estado='cerrada', fecha_hora_cierre = NOW() WHERE estado='abierta'")->execute();

        // Inserta la nueva apertura de caja con los gastos detallados en texto
        $st = $this->db->prepare("INSERT INTO apertura_caja (fecha_hora_apertura, saldo_inicial, detalle_gastos, id_empleado, estado)
                                  VALUES (?,?,?,?, 'abierta')");
        $st->execute([$fechaHora, $saldoInicial, $detalleGastos, $idEmpleado]);
        
        // Retorna el ID de la nueva apertura
        return (int)$this->db->lastInsertId();
    }

    public function actualAbierta(): ?array {
        $st = $this->db->query("SELECT * FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1");
        $row = $st->fetch();
        return $row ?: null;
    }

    public function cerrar(int $idApertura): bool {
        // Actualiza el estado de la apertura a cerrada
        $st = $this->db->prepare("UPDATE apertura_caja SET estado='cerrada', fecha_hora_cierre = NOW() WHERE id_apertura=?");
        return $st->execute([$idApertura]);
    }
}
