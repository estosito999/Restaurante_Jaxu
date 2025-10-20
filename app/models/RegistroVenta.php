<?php
namespace App\Models;

class RegistroVenta extends BaseModel
{
    protected $table = 'registro_venta';
    protected $primaryKey = 'id_registro';
    protected $fillable = ['id_factura','id_cierre','id_apertura','id_empleado','fecha_venta','monto_venta'];

    public function crear($idFactura, $idApertura, $idEmpleado, $monto, $fecha = null, $idCierre = null) {
        return $this->insert([
            'id_factura'  => $idFactura,
            'id_cierre'   => $idCierre,        // puede ser null si aún no se cerró
            'id_apertura' => $idApertura,
            'id_empleado' => $idEmpleado,
            'fecha_venta' => $fecha ?: date('Y-m-d H:i:s'),
            'monto_venta' => (float)$monto,
        ]);
    }

    public function listarPorDia($fechaYmd) {
        $st = $this->db->prepare("SELECT * FROM registro_venta WHERE DATE(fecha_venta)=:f ORDER BY fecha_venta DESC");
        $st->execute([':f'=>$fechaYmd]);
        return $st->fetchAll();
    }
}
