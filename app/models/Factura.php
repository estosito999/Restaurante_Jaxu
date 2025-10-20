<?php
namespace App\Models;

class Factura extends BaseModel
{
    protected $table = 'factura';
    protected $primaryKey = 'id_factura';
    protected $fillable = ['id_cliente','id_empleado','fecha','total','estado']; // ajusta nombres reales

    public function crearCabecera($idCliente, $idEmpleado, $fecha, $estado = 'emitida') {
        return $this->insert([
            'id_cliente'  => $idCliente,
            'id_empleado' => $idEmpleado,
            'fecha'       => $fecha,
            'total'       => 0,
            'estado'      => $estado,
        ]);
    }

    public function setTotal($id, $total) {
        return $this->updateById($id, ['total'=>$total]);
    }

    public function obtenerConDetalles($id) {
        // Ajusta nombres de columnas/tabla detalle_venta
        $sql = "SELECT f.*, c.nombre AS cliente_nombre, e.nombre AS empleado_nombre
                FROM factura f
                LEFT JOIN cliente c   ON c.id_cliente = f.id_cliente
                LEFT JOIN empleado e  ON e.id_empleado = f.id_empleado
                WHERE f.id_factura = :id
                LIMIT 1";
        $cab = $this->db->prepare($sql);
        $cab->execute([':id'=>$id]);
        $fact = $cab->fetch();
        if (!$fact) return null;

        $it = $this->db->prepare("SELECT * FROM detalle_venta WHERE id_factura = :id ORDER BY id_detalle ASC");
        $it->execute([':id'=>$id]);
        $fact['items'] = $it->fetchAll();
        return $fact;
    }
}
