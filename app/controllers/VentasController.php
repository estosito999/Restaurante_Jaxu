<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;

class VentasController extends Controller {
    private \PDO $db;
    public function __construct() { global $pdo; $this->db = $pdo; Auth::requireLogin(); Auth::requireRole(['admin','cajero']); }

    // GET /ventas
    public function index() { $this->view('ventas/index', ['csrf'=>\Core\Session::getCsrf()]); }

    // GET /ventas/dia?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function porDia() {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $st = $this->db->prepare("
            SELECT DATE(fecha_venta) AS fecha, COUNT(*) AS nventas, SUM(monto_venta) AS total
            FROM registro_venta
            WHERE fecha_venta >= ? AND fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)
              AND id_cierre IS NOT NULL OR id_cierre IS NULL
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha
        ");
        $st->execute([$from, $to]);
        $rows = $st->fetchAll();
        $this->view('ventas/por_dia', ['rows'=>$rows, 'from'=>$from, 'to'=>$to]);
    }

    // GET /ventas/mes?year=YYYY
    public function porMes() {
        $year = (int)($_GET['year'] ?? date('Y'));
        $st = $this->db->prepare("
            SELECT DATE_FORMAT(fecha_venta,'%Y-%m') AS ym, COUNT(*) AS nventas, SUM(monto_venta) AS total
            FROM registro_venta
            WHERE YEAR(fecha_venta)=?
            GROUP BY ym
            ORDER BY ym
        ");
        $st->execute([$year]);
        $rows = $st->fetchAll();
        $this->view('ventas/por_mes', ['rows'=>$rows, 'year'=>$year]);
    }

    // GET /ventas/empleado?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function porEmpleado() {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $st = $this->db->prepare("
            SELECT e.id_empleado, e.nombre, e.apellido,
                   COUNT(rv.id_registro) AS nventas, SUM(rv.monto_venta) AS total
            FROM registro_venta rv
            JOIN empleado e ON e.id_empleado = rv.id_empleado
            WHERE rv.fecha_venta >= ? AND rv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY e.id_empleado, e.nombre, e.apellido
            ORDER BY total DESC
        ");
        $st->execute([$from, $to]);
        $rows = $st->fetchAll();
        $this->view('ventas/por_empleado', ['rows'=>$rows, 'from'=>$from, 'to'=>$to]);
    }

    // GET /ventas/csv?from=...&to=...  (exporta CSV básico por día)
    public function csv() {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $st = $this->db->prepare("
            SELECT DATE(fecha_venta) AS fecha, COUNT(*) AS nventas, SUM(monto_venta) AS total
            FROM registro_venta
            WHERE fecha_venta >= ? AND fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY DATE(fecha_venta)
            ORDER BY fecha
        ");
        $st->execute([$from, $to]);
        $rows = $st->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ventas_'.$from.'_a_'.$to.'.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Fecha','N° Ventas','Total']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['fecha'], $r['nventas'], number_format((float)$r['total'],2,'.','')]);
        }
        fclose($out);
        exit;
    }
}
