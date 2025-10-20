<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class FacturaController extends Controller
{
    public function create() {
        $db = Database::getInstance();
        $clientes = $db->query("SELECT id_cliente,nombre,apellido,nit FROM cliente ORDER BY nombre")->fetchAll();
        $items    = $db->query("SELECT id_plato,nombre,precio,stock FROM plato_bebidas ORDER BY nombre")->fetchAll();
        return $this->view('facturas/create', ['clientes'=>$clientes, 'items'=>$items, 'flash'=>Session::flash('msg')]);
    }

    public function store() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/facturas/crear');

        $db   = Database::getInstance();
        $db->beginTransaction();
        try {
            $idCliente  = (int)($_POST['id_cliente'] ?? 0);
            $idEmpleado = (int)(\Core\Session::get('user_id') ?? 0);
            $fecha      = date('Y-m-d H:i:s');

            // 1) Crear cabecera
            $st = $db->prepare("INSERT INTO factura (fecha_hora, estado, monto_total, id_cliente, id_empleado)
                                VALUES (:fh, 'activa', 0, :cli, :emp)");
            $st->execute([':fh'=>$fecha, ':cli'=>$idCliente, ':emp'=>$idEmpleado]);
            $idFactura = (int)$db->lastInsertId();

            // 2) Agregar detalles
            $total = 0;
            $det = $db->prepare("INSERT INTO detalle_venta (id_factura,id_plato,cantidad,precio_unitario,subtotal)
                                 VALUES (:f,:p,:c,:pu,:st)");
            $updStock = $db->prepare("UPDATE plato_bebidas SET stock = stock - :c WHERE id_plato=:p AND stock >= :c");

            $items = $_POST['items'] ?? []; // espera: items[][] con id_plato, cantidad, precio_unitario
            foreach ($items as $it) {
                $idPlato = (int)$it['id_plato'];
                $cant    = (int)$it['cantidad'];
                $precioU = (float)$it['precio_unitario'];
                $sub     = round($cant * $precioU, 2);

                $det->execute([':f'=>$idFactura, ':p'=>$idPlato, ':c'=>$cant, ':pu'=>$precioU, ':st'=>$sub]);
                $updStock->execute([':c'=>$cant, ':p'=>$idPlato]);
                $total += $sub;
            }

            // 3) Actualizar total
            $db->prepare("UPDATE factura SET monto_total=:t WHERE id_factura=:id")
               ->execute([':t'=>$total, ':id'=>$idFactura]);

            // 4) Registrar venta (si hay caja abierta)
            $ap = $db->query("SELECT id_apertura FROM apertura_caja WHERE estado='abierta' ORDER BY id_apertura DESC LIMIT 1")->fetch();
            $idApertura = $ap ? (int)$ap['id_apertura'] : null;

            $rv = $db->prepare("INSERT INTO registro_venta (id_factura,id_apertura,id_empleado,fecha_venta,monto_venta)
                                VALUES (:f,:a,:e,:fv,:m)");
            $rv->execute([
                ':f'=>$idFactura, ':a'=>$idApertura, ':e'=>$idEmpleado,
                ':fv'=>$fecha, ':m'=>$total
            ]);

            $db->commit();
            return $this->redirect('/facturas/'.$idFactura);
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('msg','No se pudo registrar la factura: '.$e->getMessage());
            return $this->redirect('/facturas/crear');
        }
    }

    public function show($id) {
        $db = Database::getInstance();
        $cab = $db->prepare("SELECT f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido,
                                    e.nombre AS empleado_nombre, e.apellido AS empleado_apellido
                             FROM factura f
                             LEFT JOIN cliente  c ON c.id_cliente  = f.id_cliente
                             LEFT JOIN empleado e ON e.id_empleado = f.id_empleado
                             WHERE f.id_factura=:id");
        $cab->execute([':id'=>$id]);
        $factura = $cab->fetch();

        if (!$factura) return $this->redirect('/facturas/crear');

        $det = $db->prepare("SELECT d.*, p.nombre AS plato
                             FROM detalle_venta d
                             LEFT JOIN plato_bebidas p ON p.id_plato = d.id_plato
                             WHERE d.id_factura=:id");
        $det->execute([':id'=>$id]);
        $items = $det->fetchAll();

        return $this->view('facturas/show', ['factura'=>$factura, 'items'=>$items]);
    }
}
