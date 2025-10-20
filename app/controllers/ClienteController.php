<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class ClienteController extends Controller
{
    public function index() {
        $db = Database::getInstance();
        $rows = $db->query("SELECT * FROM cliente ORDER BY nombre,apellido")->fetchAll();
        return $this->view('clientes/index', ['rows'=>$rows, 'flash'=>Session::flash('msg')]);
    }

    public function store() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/clientes');

        $db = Database::getInstance();
        $st = $db->prepare("INSERT INTO cliente (nombre,apellido,nit,telefono,email)
                            VALUES (:nombre,:apellido,:nit,:telefono,:email)");
        $ok = $st->execute([
            ':nombre'=>trim($_POST['nombre'] ?? ''),
            ':apellido'=>trim($_POST['apellido'] ?? ''),
            ':nit'=>trim($_POST['nit'] ?? ''),
            ':telefono'=>trim($_POST['telefono'] ?? ''),
            ':email'=>trim($_POST['email'] ?? '')
        ]);
        Session::flash('msg', $ok ? 'Cliente guardado' : 'No se pudo guardar');
        return $this->redirect('/clientes');
    }
}
