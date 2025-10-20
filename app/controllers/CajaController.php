<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class CajaController extends Controller
{
    public function abrirForm() {
        return $this->view('caja/abrir', ['flash'=>Session::flash('msg')]);
    }

    public function abrir() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/caja/abrir');
        $db = Database::getInstance();

        // Cerrar cualquier apertura “zombie”
        $db->exec("UPDATE apertura_caja SET estado='cerrada' WHERE estado='abierta'");

        $st = $db->prepare("INSERT INTO apertura_caja (fecha_hora_apertura, saldo_inicial, detalle_gastos, id_empleado, estado)
                            VALUES (:fh,:si,:dg,:emp,'abierta')");
        $ok = $st->execute([
            ':fh'=>date('Y-m-d H:i:s'),
            ':si'=>(float)($_POST['saldo_inicial'] ?? 0),
            ':dg'=>trim($_POST['detalle_gastos'] ?? ''),
            ':emp'=>(int)Session::get('user_id'),
        ]);
        Session::flash('msg', $ok ? 'Caja abierta' : 'No se pudo abrir la caja');
        return $this->redirect('/caja/cierre');
    }

    public function index() {
        $db = Database::getInstance();
        $abierta = $db->query("SELECT * FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1")->fetch();
        $cierres = $db->query("SELECT c.*, e.nombre AS empleado
                               FROM cierre_caja c
                               LEFT JOIN empleado e ON e.id_empleado=c.id_empleado
                               ORDER BY c.id_cierre DESC LIMIT 50")->fetchAll();
        return $this->view('caja/index', ['abierta'=>$abierta, 'cierres'=>$cierres, 'flash'=>Session::flash('msg')]);
    }

    public function cerrar() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/caja/cierre');

        $db = Database::getInstance();
        $ap = $db->query("SELECT * FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1")->fetch();
        if (!$ap) {
            Session::flash('msg','No hay caja abierta');
            return $this->redirect('/caja/cierre');
        }

        // Total de ventas desde esa apertura
        $st = $db->prepare("SELECT SUM(monto_venta) FROM registro_venta WHERE id_apertura=:a");
        $st->execute([':a'=>$ap['id_apertura']]);
        $totalVentas = (float)$st->fetchColumn();

        $contado = (float)($_POST['monto_efectivo_contado'] ?? 0);
        $dif     = $contado - $totalVentas;

        $db->beginTransaction();
        try {
            // Inserta cierre
            $ins = $db->prepare("INSERT INTO cierre_caja (fecha_hora_cierre, monto_total_ventas, monto_efectivo_contado, diferencia, id_empleado)
                                 VALUES (:fh,:tv,:me,:df,:emp)");
            $ins->execute([
                ':fh'=>date('Y-m-d H:i:s'),
                ':tv'=>$totalVentas,
                ':me'=>$contado,
                ':df'=>$dif,
                ':emp'=>(int)Session::get('user_id')
            ]);
            // Marca apertura cerrada
            $db->prepare("UPDATE apertura_caja SET estado='cerrada' WHERE id_apertura=:id")
               ->execute([':id'=>$ap['id_apertura']]);

            $db->commit();
            Session::flash('msg','Caja cerrada');
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('msg','No se pudo cerrar: '.$e->getMessage());
        }
        return $this->redirect('/caja/cierre');
    }
}
