<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\CierreCaja;
use App\Models\AperturaCaja;

class CajaController extends Controller {
    private \PDO $db;
    public function __construct() { global $pdo; $this->db = $pdo; Auth::requireLogin(); }

    public function index() {
        Auth::requireRole(['admin','cajero']);
        $cierre = new CierreCaja($this->db);
        $ap     = new AperturaCaja($this->db);

        $pend    = $cierre->ventasPendientes();
        $totalPend = $cierre->totalPendiente();   // ← nuevo
        $cierres = $cierre->listarCierres();
        $abierta = $ap->actualAbierta();

        $this->view('caja/index', [
            'cierres' => $cierres,
            'pendientes' => $pend,
            'totalPend' => $totalPend,            // ← pásalo a la vista
            'abierta' => $abierta,
            'csrf' => \Core\Session::getCsrf()
        ]);
    }

    public function cerrar() {
        Auth::requireRole(['admin','cajero']);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $efectivo = (float)($_POST['monto_efectivo_contado'] ?? 0);

        $cierre = new CierreCaja($this->db);
        try {
            // valida que haya ventas
            if ($cierre->totalPendiente() <= 0) {
                \Core\Session::flash('msg', 'No hay ventas pendientes para cerrar.');
                return $this->redirect('/caja/cierre');
            }
            $idCierre = $cierre->cerrarCaja(\Core\Auth::userId(), date('Y-m-d H:i:s'), $efectivo);

            // Cierra apertura si la manejas (opcional, tu código ya lo hace)
            $ap = new AperturaCaja($this->db);
            if ($curr = $ap->actualAbierta()) { $ap->cerrar((int)$curr['id_apertura']); }

            \Core\Session::flash('msg', 'Cierre realizado #' . $idCierre);
        } catch (\Throwable $e) {
            \Core\Session::flash('msg', 'No se pudo cerrar: ' . $e->getMessage());
        }
        $this->redirect('/caja/cierre');
    }


    /** GET /caja/abrir (form) */
    public function abrirForm() {
        Auth::requireRole(['admin','cajero']);
        $this->view('caja/abrir', ['csrf' => \Core\Session::getCsrf()]);
    }

    /** POST /caja/abrir */
    public function abrir() {
        Auth::requireRole(['admin','cajero']);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
        $saldo = (float)($_POST['saldo_inicial'] ?? 0);
        $ap = new AperturaCaja($this->db);
        $idAp = $ap->abrir(Auth::userId(), date('Y-m-d H:i:s'), $saldo);
        Session::flash('msg', 'Caja abierta (#'.$idAp.')');
        $this->redirect('/caja/cierre');
    }

    
}
