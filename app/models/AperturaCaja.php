<?php
namespace App\Models;

class AperturaCaja extends BaseModel
{
    protected $table = 'apertura_caja';
    protected $primaryKey = 'id_apertura';
    protected $fillable = ['id_empleado','fecha_hora_apertura','saldo_inicial','detalle_gastos','estado'];

    public function abrir($idEmpleado, $fechaHora, $saldoInicial, $detalleGastos = '') {
        // Cierra cualquier apertura abierta “huérfana” (opcional)
        $this->db->exec("UPDATE apertura_caja SET estado='cerrada', fecha_hora_cierre = NOW() WHERE estado='abierta'");
        return $this->insert([
            'id_empleado'        => $idEmpleado,
            'fecha_hora_apertura'=> $fechaHora,
            'saldo_inicial'      => (float)$saldoInicial,
            'detalle_gastos'     => $detalleGastos,
            'estado'             => 'abierta',
        ]);
    }

    public function ultimaAbierta() {
        $st = $this->db->query("SELECT * FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1");
        return $st->fetch() ?: null;
    }
}
