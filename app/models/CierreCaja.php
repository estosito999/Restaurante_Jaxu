<?php
namespace App\Models;

class CierreCaja extends BaseModel
{
    protected $table = 'cierre_caja';
    protected $primaryKey = 'id_cierre';
    protected $fillable = ['id_apertura','id_empleado','fecha_hora_cierre','saldo_final','observacion'];

    public function cerrar($idApertura, $idEmpleado, $fechaHora, $saldoFinal, $obs = '') {
        // Marca apertura como cerrada
        $st = $this->db->prepare("UPDATE apertura_caja SET estado='cerrada', fecha_hora_cierre=:fh WHERE id_apertura=:id");
        $st->execute([':fh'=>$fechaHora, ':id'=>$idApertura]);

        // Inserta cierre
        return $this->insert([
            'id_apertura'       => $idApertura,
            'id_empleado'       => $idEmpleado,
            'fecha_hora_cierre' => $fechaHora,
            'saldo_final'       => (float)$saldoFinal,
            'observacion'       => $obs,
        ]);
    }
}
