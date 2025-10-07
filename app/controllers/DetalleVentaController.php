<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\DetalleVenta;

class DetalleVentaController extends Controller {

    protected $db;

    public function __construct()
    {
        parent::__construct();
        // Replace with your actual DB connection logic
        $this->db = (new \Core\Database())->getPdo(); // Ensure this returns a PDO instance
    }

    // Obtener detalles de una venta
    public function show($id) {
        $detalleVenta = new DetalleVenta($this->db);
        $detalles = $detalleVenta->getByFactura($id);
        
        $this->view('detalle_venta/show', ['detalles' => $detalles]);
    }
}
