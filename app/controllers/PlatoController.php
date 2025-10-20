<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class PlatoController extends Controller
{
    public function index() {
        $db = Database::getInstance();
        $rows = $db->query("SELECT p.*, e.nombre AS cocinero
                            FROM plato_bebidas p
                            LEFT JOIN empleado e ON e.id_empleado = p.id_cocinero
                            ORDER BY p.nombre")->fetchAll();
        return $this->view('platos/index', ['rows'=>$rows, 'flash'=>Session::flash('msg')]);
    }

    public function create() {
        $db = Database::getInstance();
        $cocineros = $db->query("SELECT id_empleado, nombre, apellido FROM empleado WHERE rol='cocinero' ORDER BY nombre")->fetchAll();
        return $this->view('platos/create', ['cocineros'=>$cocineros]);
    }

    public function store() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/platos/crear');

        $db = Database::getInstance();
        $st = $db->prepare("INSERT INTO plato_bebidas (nombre,precio,stock,categoria,tipo,id_cocinero)
                            VALUES (:nombre,:precio,:stock,:categoria,:tipo,:idc)");
        $ok = $st->execute([
            ':nombre'=>trim($_POST['nombre'] ?? ''),
            ':precio'=>(float)($_POST['precio'] ?? 0),
            ':stock'=>(int)($_POST['stock'] ?? 0),
            ':categoria'=>trim($_POST['categoria'] ?? 'General'),
            ':tipo'=>$_POST['tipo'] ?? 'almuerzo',
            ':idc'=>(int)($_POST['id_cocinero'] ?? 0),
        ]);
        Session::flash('msg', $ok ? 'Guardado' : 'No se pudo guardar');
        return $this->redirect('/platos');
    }

    public function edit($id) {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM plato_bebidas WHERE id_plato=:id");
        $st->execute([':id'=>$id]);
        $row = $st->fetch();
        $cocineros = $db->query("SELECT id_empleado, nombre, apellido FROM empleado WHERE rol='cocinero'")->fetchAll();
        return $this->view('platos/edit', ['row'=>$row,'cocineros'=>$cocineros]);
    }

    public function update($id) {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/platos/editar/'.$id);
        $db = Database::getInstance();
        $st = $db->prepare("UPDATE plato_bebidas SET nombre=:nombre,precio=:precio,stock=:stock,categoria=:categoria,tipo=:tipo,id_cocinero=:idc WHERE id_plato=:id");
        $ok = $st->execute([
            ':nombre'=>trim($_POST['nombre'] ?? ''),
            ':precio'=>(float)($_POST['precio'] ?? 0),
            ':stock'=>(int)($_POST['stock'] ?? 0),
            ':categoria'=>trim($_POST['categoria'] ?? 'General'),
            ':tipo'=>$_POST['tipo'] ?? 'almuerzo',
            ':idc'=>(int)($_POST['id_cocinero'] ?? 0),
            ':id'=>$id
        ]);
        Session::flash('msg', $ok ? 'Actualizado' : 'No se pudo actualizar');
        return $this->redirect('/platos');
    }

    public function destroy($id) {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/platos');
        $db = Database::getInstance();
        $st = $db->prepare("DELETE FROM plato_bebidas WHERE id_plato=:id");
        $ok = $st->execute([':id'=>$id]);
        Session::flash('msg', $ok ? 'Eliminado' : 'No se pudo eliminar');
        return $this->redirect('/platos');
    }
}
