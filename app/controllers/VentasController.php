<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;

class VentasController extends Controller
{
    public function index() {
        return $this->view('ventas/index');
    }

    public function porDia() {
        $d  = $_GET['fecha'] ?? date('Y-m-d');
        $db = Database::getInstance();
        $st = $db->prepare("SELECT r.*, f.estado
                            FROM registro_venta r
                            LEFT JOIN factura f ON f.id_factura=r.id_factura
                            WHERE DATE(r.fecha_venta)=:d
                            ORDER BY r.fecha_venta DESC");
        $st->execute([':d'=>$d]);
        $rows = $st->fetchAll();
        return $this->view('ventas/dia', ['rows'=>$rows, 'fecha'=>$d]);
    }

    public function porMes() {
        $ym = $_GET['mes'] ?? date('Y-m');
        $db = Database::getInstance();
        $st = $db->prepare("SELECT DATE(r.fecha_venta) dia, SUM(r.monto_venta) total
                            FROM registro_venta r
                            WHERE DATE_FORMAT(r.fecha_venta,'%Y-%m') = :ym
                            GROUP BY dia ORDER BY dia");
        $st->execute([':ym'=>$ym]);
        $rows = $st->fetchAll();
        return $this->view('ventas/mes', ['rows'=>$rows, 'mes'=>$ym]);
    }

    public function porEmpleado() {
        $db = Database::getInstance();
        $rows = $db->query("SELECT e.nombre, e.apellido, SUM(r.monto_venta) total
                            FROM registro_venta r
                            INNER JOIN empleado e ON e.id_empleado = r.id_empleado
                            GROUP BY r.id_empleado
                            ORDER BY total DESC")->fetchAll();
        return $this->view('ventas/empleado', ['rows'=>$rows]);
    }

    public function csv() {
        $db = Database::getInstance();
        $rows = $db->query("SELECT * FROM registro_venta ORDER BY fecha_venta DESC LIMIT 1000")->fetchAll();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=ventas.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($rows ? $rows[0] : ['sin'=>'datos']));
        foreach ($rows as $r) fputcsv($out, $r);
        fclose($out);
        exit;
    }
}
