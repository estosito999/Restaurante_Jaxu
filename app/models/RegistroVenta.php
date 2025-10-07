<?php
namespace App\Models;

class RegistroVenta extends BaseModel {

    // Crear un registro de venta
    public function create(array $data): int {
        $st = $this->db->prepare("INSERT INTO registro_venta(id_factura, id_cierre, id_apertura, id_empleado, fecha_venta, monto_venta)
                                  VALUES(?,?,?,?,?,?)");
        $st->execute([
            $data['id_factura'],
            $data['id_cierre'] ?? null,  // Puede ser null si no está cerrado aún
            $data['id_apertura'],
            $data['id_empleado'],
            $data['fecha_venta'],
            $data['monto_venta']
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Obtener el registro de venta por ID
    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM registro_venta WHERE id_registro = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // Listar todos los registros de ventas (con filtrado)
    public function all(string $q = '', string $dateRange = ''): array {
        $sql = "SELECT rv.*, f.fecha_hora, e.nombre AS empleado_nombre
                FROM registro_venta rv
                LEFT JOIN factura f ON f.id_factura = rv.id_factura
                LEFT JOIN empleado e ON e.id_empleado = rv.id_empleado
                WHERE 1=1";
        $args = [];
        
        if ($q !== '') {
            $sql .= " AND (f.id_cliente LIKE ? OR e.nombre LIKE ?)";
            $args[] = "%$q%"; $args[] = "%$q%";
        }

        if ($dateRange !== '') {
            $sql .= " AND rv.fecha_venta BETWEEN ? AND ?";
            $args[] = $dateRange['start'];
            $args[] = $dateRange['end'];
        }

        $st = $this->db->prepare($sql);
        $st->execute($args);
        return $st->fetchAll();
    }
}
