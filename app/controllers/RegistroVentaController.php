<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use Core\Database;

class RegistroVentaController extends Controller
{
    /** @var \PDO */
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
        Auth::requireLogin();
        Auth::requireRole(['admin','cajero','mesero']); // quienes pueden registrar ventas
    }

    // GET /ventas/registro
    public function index()
    {
        // combos básicos para la vista de registro
        $platos   = $this->db->query("SELECT id, nombre, precio FROM plato WHERE estado=1 ORDER BY nombre")->fetchAll();
        $clientes = $this->db->query("SELECT id, nombre FROM cliente ORDER BY nombre")->fetchAll();

        return $this->view('ventas/registro', [
            'platos'   => $platos,
            'clientes' => $clientes,
            'csrf'     => Session::getCsrf(),
            'flash'    => Session::getFlash('msg'),
        ]);
    }

    // POST /ventas
    public function create()
    {
        if (!Session::verifyCsrf($_POST['_token'] ?? '')) {
            http_response_code(419); exit('CSRF token inválido');
        }

        $idCliente   = isset($_POST['cliente_id']) && $_POST['cliente_id'] !== '' ? (int)$_POST['cliente_id'] : null;
        $idEmpleado  = (int) (Auth::userId() ?? 0);

        // Esperamos arrays plato_id[], cantidad[], precio[]
        $platoIds  = isset($_POST['plato_id']) ? (array)$_POST['plato_id'] : [];
        $cantidades= isset($_POST['cantidad']) ? (array)$_POST['cantidad'] : [];
        $precios   = isset($_POST['precio'])   ? (array)$_POST['precio']   : [];

        $items = [];
        $total = 0.0;

        // Normalizar items
        $n = max(count($platoIds), count($cantidades), count($precios));
        for ($i = 0; $i < $n; $i++) {
            $pid = isset($platoIds[$i]) ? (int)$platoIds[$i] : 0;
            $qty = isset($cantidades[$i]) ? (float)$cantidades[$i] : 0;
            $prc = isset($precios[$i]) ? (float)$precios[$i] : 0.0;

            if ($pid > 0 && $qty > 0 && $prc >= 0) {
                $sub = $qty * $prc;
                $items[] = ['id_plato'=>$pid, 'cantidad'=>$qty, 'precio'=>$prc, 'subtotal'=>$sub];
                $total += $sub;
            }
        }

        if ($idEmpleado <= 0 || empty($items)) {
            Session::flash('msg', 'Datos de venta incompletos.');
            $this->redirect((defined('BASE_URI') ? BASE_URI : '') . '/ventas/registro');
        }

        try {
            $this->db->beginTransaction();

            // Inserta cabecera (asumo tabla registro_venta tiene estas columnas)
            $cab = $this->db->prepare("
                INSERT INTO registro_venta (fecha_venta, monto_venta, id_cliente, id_empleado, id_cierre)
                VALUES (NOW(), ?, ?, ?, NULL)
            ");
            $cab->execute([$total, $idCliente, $idEmpleado]);
            $idVenta = (int)$this->db->lastInsertId();

            // Inserta detalles (asumo tabla detalle_venta)
            $det = $this->db->prepare("
                INSERT INTO detalle_venta (id_venta, id_plato, cantidad, precio_unitario, subtotal)
                VALUES (?,?,?,?,?)
            ");
            foreach ($items as $it) {
                $det->execute([$idVenta, $it['id_plato'], $it['cantidad'], $it['precio'], $it['subtotal']]);
            }

            $this->db->commit();

            Session::flash('msg', 'Venta registrada correctamente.');
            // Redirige al detalle de la venta o a la pantalla de registro
            $this->redirect((defined('BASE_URI') ? BASE_URI : '') . '/detalle_venta/' . $idVenta);

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[venta] ' . $e->getMessage());
            Session::flash('msg', 'Error al registrar la venta.');
            $this->redirect((defined('BASE_URI') ? BASE_URI : '') . '/ventas/registro');
        }
    }
}
