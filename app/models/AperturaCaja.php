<?php
namespace App\Models;

class AperturaCaja extends BaseModel {
    public function abrir(int $idEmpleado, string $fechaHora, float $saldoInicial): int {
        // Cierra cualquier apertura huérfana (seguridad, opcional)
        $this->db->prepare("UPDATE apertura_caja SET estado='cerrada' WHERE estado='abierta'")->execute();

        $st = $this->db->prepare("INSERT INTO apertura_caja (fecha_hora_apertura, saldo_inicial, id_empleado, estado)
                                  VALUES (?,?,?,'abierta')");
        $st->execute([$fechaHora, $saldoInicial, $idEmpleado]);
        return (int)$this->db->lastInsertId();
    }

    public function actualAbierta(): ?array {
        $st = $this->db->query("SELECT * FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1");
        $row = $st->fetch();
        return $row ?: null;
    }

    public function cerrar(int $idApertura): bool {
        $st = $this->db->prepare("UPDATE apertura_caja SET estado='cerrada' WHERE id_apertura=?");
        return $st->execute([$idApertura]);
    }
}
