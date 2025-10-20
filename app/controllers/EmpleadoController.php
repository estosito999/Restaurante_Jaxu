<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class EmpleadoController extends Controller
{
    public function index() {
        $db = Database::getInstance();
        $rows = $db->query("SELECT id_empleado,nombre,apellido,ci,puesto,sueldo,rol FROM empleado ORDER BY rol DESC,nombre")->fetchAll();
        return $this->view('empleados/index', ['rows'=>$rows, 'flash'=>Session::flash('msg')]);
    }

    public function create() {
        return $this->view('empleados/create');
    }

    public function store() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/empleados/crear');

        $d = [
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'ci'       => preg_replace('/\D+/', '', $_POST['ci'] ?? ''),
            'puesto'   => trim($_POST['puesto'] ?? 'Gerente'),
            'sueldo'   => (float)($_POST['sueldo'] ?? 0),
            'rol'      => $_POST['rol'] ?? 'cajero',
        ];
        $pass = $_POST['password'] ?? '';
        if ($pass !== ($_POST['password_confirm'] ?? '')) {
            Session::flash('msg','Las contraseñas no coinciden');
            return $this->redirect('/empleados/crear');
        }

        $db = Database::getInstance();
        // CI único
        $q = $db->prepare("SELECT 1 FROM empleado WHERE ci=:ci LIMIT 1");
        $q->execute([':ci'=>$d['ci']]);
        if ($q->fetchColumn()) {
            Session::flash('msg','El CI ya existe');
            return $this->redirect('/empleados/crear');
        }

        $st = $db->prepare("INSERT INTO empleado (nombre,apellido,ci,puesto,sueldo,password_hash,rol)
                            VALUES (:nombre,:apellido,:ci,:puesto,:sueldo,:hash,:rol)");
        $ok = $st->execute([
            ':nombre'=>$d['nombre'],':apellido'=>$d['apellido'],':ci'=>$d['ci'],
            ':puesto'=>$d['puesto'],':sueldo'=>$d['sueldo'],
            ':hash'=> password_hash($pass, PASSWORD_DEFAULT),
            ':rol'=>$d['rol']
        ]);
        Session::flash('msg', $ok ? 'Empleado creado' : 'No se pudo crear');
        return $this->redirect('/empleados');
    }

    public function edit($id) {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM empleado WHERE id_empleado=:id");
        $st->execute([':id'=>$id]);
        $emp = $st->fetch();
        if (!$emp) return $this->redirect('/empleados');
        return $this->view('empleados/edit', ['emp'=>$emp]);
    }

    public function update($id) {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/empleados/editar/'.$id);

        $db = Database::getInstance();
        $st = $db->prepare("UPDATE empleado SET nombre=:nombre, apellido=:apellido, puesto=:puesto, sueldo=:sueldo, rol=:rol WHERE id_empleado=:id");
        $ok = $st->execute([
            ':nombre'=>trim($_POST['nombre'] ?? ''),
            ':apellido'=>trim($_POST['apellido'] ?? ''),
            ':puesto'=>trim($_POST['puesto'] ?? ''),
            ':sueldo'=>(float)($_POST['sueldo'] ?? 0),
            ':rol'=>$_POST['rol'] ?? 'cajero',
            ':id'=>$id
        ]);
        Session::flash('msg', $ok ? 'Empleado actualizado' : 'No se pudo actualizar');
        return $this->redirect('/empleados');
    }

    public function passwordForm($id) {
        return $this->view('empleados/password', ['id'=>$id]);
    }

    public function passwordUpdate($id) {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/empleados/password/'.$id);
        $p1 = $_POST['password'] ?? ''; $p2 = $_POST['password_confirm'] ?? '';
        if ($p1 === '' || $p1 !== $p2) {
            Session::flash('msg','Las contraseñas no coinciden');
            return $this->redirect('/empleados/password/'.$id);
        }
        $db = Database::getInstance();
        $st = $db->prepare("UPDATE empleado SET password_hash=:h WHERE id_empleado=:id");
        $ok = $st->execute([':h'=>password_hash($p1,PASSWORD_DEFAULT), ':id'=>$id]);
        Session::flash('msg', $ok ? 'Contraseña actualizada' : 'No se pudo actualizar');
        return $this->redirect('/empleados');
    }

    public function destroy($id) {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) return $this->redirect('/empleados');
        $db = Database::getInstance();
        $st = $db->prepare("DELETE FROM empleado WHERE id_empleado=:id");
        $ok = $st->execute([':id'=>$id]);
        Session::flash('msg', $ok ? 'Empleado eliminado' : 'No se pudo eliminar');
        return $this->redirect('/empleados');
    }
}
