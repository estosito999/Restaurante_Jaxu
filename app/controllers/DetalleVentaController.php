<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;

class DetalleVentaController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
        Auth::requireLogin();
    }

    public function show($id)
    {
        // Muestra items de la factura/venta $id
        return $this->view('detalle_venta/show', ['id'=>(int)$id]);
    }
}
