<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Cliente;

class ClienteController extends Controller {
    private \PDO $db;
    public function __construct() { 
        global $pdo; 
        $this->db = $pdo; 
        Auth::requireLogin();                         // requiere sesión
        Auth::requireRole(['admin','cajero','mesero']); // roles que pueden ver/crear clientes
    }

    // GET /clientes
    public function index() {
        $model = new Cliente($this->db);
        $clientes = $model->all();
        $this->view('clientes/index', [
            'clientes' => $clientes,
            'csrf'     => \Core\Session::getCsrf(),
            'flash'    => Session::flash('msg')
        ]);
    }

    // POST /clientes
    public function store() {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) {
            http_response_code(419); exit('CSRF');
        }

        // Validaciones mínimas
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $nit      = trim($_POST['nit']      ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email']    ?? '');

        if ($nombre === '') {
            Session::flash('msg', 'El nombre es obligatorio.');
            $this->redirect('/clientes');
        }

        // (Opcional) Validar email y longitudes aquí...

        $model = new Cliente($this->db);
        $model->create([
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'nit'      => $nit,
            'telefono' => $telefono,
            'email'    => $email
        ]);

        Session::flash('msg', 'Cliente creado correctamente.');
        $this->redirect('/clientes');
    }
}
