<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\Factura;

class FacturaController extends Controller {
    private \PDO $db;
    public function __construct() { 
        global $pdo; 
        $this->db = $pdo; 
        Auth::requireLogin(); 
        Auth::requireRole(['admin','cajero','mesero']);
    }

    public function create() {
        $clientes = $this->db->query("SELECT id_cliente, nombre, apellido, nit FROM cliente ORDER BY nombre")->fetchAll();
        $platos   = $this->db->query("SELECT id_plato, nombre, precio, categoria FROM plato ORDER BY categoria, nombre")->fetchAll();
        $this->view('facturas/create', [
            'csrf' => \Core\Session::getCsrf(),
            'clientes' => $clientes,
            'platos' => $platos
        ]);
    }

    public function store() {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $idCliente = (int)($_POST['id_cliente'] ?? 0);
        $ids   = $_POST['id_plato'] ?? [];
        $cants = $_POST['cantidad'] ?? [];
        // Ignoraremos precios enviados y re-leeremos desde DB
        $n = count($ids);

        // Carga precios oficiales
        $map = []; // id_plato => [precio, nombre]
        $rows = $this->db->query("SELECT id_plato, nombre, precio FROM plato")->fetchAll();
        foreach ($rows as $r) { $map[(int)$r['id_plato']] = ['precio'=>(float)$r['precio'], 'nombre'=>$r['nombre']]; }

        $items = [];
        for ($i=0; $i<$n; $i++) {
            $pid = (int)$ids[$i];
            $qty = (int)$cants[$i];
            if ($qty <= 0 || !isset($map[$pid])) continue;
            $items[] = ['id_plato'=>$pid, 'cantidad'=>$qty, 'precio_unitario'=>$map[$pid]['precio']];
        }
        if (empty($items)) {
            Session::flash('msg','Debe agregar al menos un plato.');
            $this->redirect('/facturas/crear');
        }

        $facturaModel = new Factura($this->db);
        $idFactura = $facturaModel->createFactura($idCliente, Auth::userId(), date('Y-m-d H:i:s'), $items, 0.13);

        Session::flash('msg','Factura creada');
        $this->redirect('/facturas/' . $idFactura);
    }

    public function show($id) {
        $factura = (new Factura($this->db))->getFactura((int)$id);
        if (!$factura) { http_response_code(404); exit('Factura no encontrada'); }
        $this->view('facturas/show', ['factura' => $factura]);
    }

    /** Ticket HTML 80mm listo para impresora térmica (imprime desde navegador) */
    public function ticket($id) {
        $factura = (new Factura($this->db))->getFactura((int)$id);
        if (!$factura) { http_response_code(404); exit('Factura no encontrada'); }
        $this->view('facturas/ticket', ['factura' => $factura]);
    }

    /** Exporta PDF (usa FPDF). Requiere app/lib/fpdf.php */
        // app/controllers/FacturaController.php  (método pdf)
    public function pdf($id) {
        $factura = (new \App\Models\Factura($this->db))->getFactura((int)$id);
        if (!$factura) { http_response_code(404); exit('Factura no encontrada'); }

        $fpdfPath = dirname(__DIR__,1) . '/lib/fpdf.php'; // app/lib/fpdf.php
        if (!is_file($fpdfPath)) {
            http_response_code(500);
            echo "Falta la librería FPDF (colócala en app/lib/fpdf.php)";
            return;
        }
        require_once $fpdfPath;

        $items = $factura['detalles'] ?? [];
        $lineCount = max(1, count($items));
        $height = 80 + ($lineCount * 6) + 40; // alto aproximado

        $w = 80; // 80mm
        $pdf = new \FPDF('P','mm', [$w, $height]);
        $pdf->AddPage();
        $pdf->SetMargins(4, 4, 4);

        // ================================
        // (AQUÍ) SELLO ANULADA (si aplica)
        // ================================
        if (($factura['estado'] ?? 'activa') === 'anulada') {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(220, 20, 60); // rojo
            // Imprime una línea centrada; va antes del resto del contenido
            $pdf->Cell(0, 8, '*** ANULADA ***', 0, 1, 'C');
            $pdf->Ln(2);
            // Restablece color para el resto
            $pdf->SetTextColor(0, 0, 0);
        }

        // Header
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,6, 'JAXU — Restaurante',0,1,'C');
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(0,5, 'Factura #'.$factura['id_factura'],0,1,'C');
        $pdf->Cell(0,4, $factura['fecha_hora'],0,1,'C');
        $pdf->Ln(2);

        // Cliente / Empleado
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(0,4,'Cliente: '.trim(($factura['cliente_nombre']??'').' '.($factura['cliente_apellido']??'')),0,1);
        if (!empty($factura['cliente_nit'])) $pdf->Cell(0,4,'NIT/CI: '.$factura['cliente_nit'],0,1);
        $pdf->Cell(0,4,'Atiende: '.trim(($factura['empleado_nombre']??'').' '.($factura['empleado_apellido']??'')),0,1);
        $pdf->Ln(2);

        // Detalle
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(12,5,'Cant',0,0,'L');
        $pdf->Cell(40,5,'Plato',0,0,'L');
        $pdf->Cell(0,5,'Importe',0,1,'R');

        $pdf->SetFont('Arial','',9);
        foreach ($items as $d) {
            $line = number_format((float)$d['subtotal'],2);
            $pdf->Cell(12,5,(int)$d['cantidad'],0,0,'L');
            $pdf->Cell(40,5,substr($d['plato_nombre'],0,28),0,0,'L');
            $pdf->Cell(0,5,$line,0,1,'R');
        }
        $pdf->Ln(1);
        $pdf->Cell(0,0,'','T',1); // línea

        // Totales
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(40,5,'Subtotal',0,0,'L');
        $pdf->Cell(0,5,number_format((float)$factura['subtotal'],2),0,1,'R');
        $pdf->Cell(40,5,'IVA 13%',0,0,'L');
        $pdf->Cell(0,5,number_format((float)$factura['iva'],2),0,1,'R');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,6,'TOTAL',0,0,'L');
        $pdf->Cell(0,6,number_format((float)$factura['total'],2),0,1,'R');

        // Pie
        $pdf->Ln(2);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(0,4,'Gracias por su preferencia',0,1,'C');

        $pdf->Output('I', 'factura_'.$factura['id_factura'].'.pdf'); // Inline
    }


    public function anular($id) {
        \Core\Auth::requireRole(['admin','cajero']);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $idFactura = (int)$id;
        $motivo = trim($_POST['motivo'] ?? '');

        try {
            $model = new \App\Models\Factura($this->db);
            $model->cancelFactura($idFactura, \Core\Auth::userId(), $motivo !== '' ? $motivo : null);
            \Core\Session::flash('msg', 'Factura anulada correctamente.');
        } catch (\Throwable $e) {
            \Core\Session::flash('msg', 'No se pudo anular: ' . $e->getMessage());
        }
        $this->redirect('/facturas/' . $idFactura);
    }

}
