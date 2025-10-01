<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\Empleado;

class EmpleadoController extends Controller {
    private \PDO $db;
    public function __construct() {
        global $pdo; $this->db = $pdo;
        Auth::requireLogin();
        Auth::requireRole(['admin']); // Solo admin gestiona empleados
    }

    // GET /empleados
    public function index() {
        $model = new Empleado($this->db);
        $this->view('empleados/index', [
            'empleados' => $model->all(),
            'csrf' => \Core\Session::getCsrf(),
            'flash' => Session::flash('msg')
        ]);
    }

    // GET /empleados/crear
    public function create() {
        $this->view('empleados/create', [
            'csrf' => \Core\Session::getCsrf()
        ]);
    }

    // POST /empleados
    public function store() {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $data = [
            'nombre'   => trim($_POST['nombre']   ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'ci'       => trim($_POST['ci']       ?? ''),
            'puesto'   => trim($_POST['puesto']   ?? ''),
            'rol'      => trim($_POST['rol']      ?? 'mesero'),
            'password' => (string)($_POST['password'] ?? ''),
        ];

        if ($data['nombre']==='' || $data['ci']==='') {
            Session::flash('msg','Nombre y CI son obligatorios.');
            $this->redirect('/empleados/crear');
        }
        if (!in_array($data['rol'], ['admin','cajero','mesero'], true)) {
            Session::flash('msg','Rol inválido.');
            $this->redirect('/empleados/crear');
        }

        try {
            (new Empleado($this->db))->create($data);
            Session::flash('msg','Empleado creado.');
            $this->redirect('/empleados');
        } catch (\PDOException $e) {
            // CI UNIQUE
            if ((int)$e->errorInfo[1] === 1062) {
                Session::flash('msg','El CI ya está registrado.');
                $this->redirect('/empleados/crear');
            }
            throw $e;
        }
    }

    // GET /empleados/editar/{id}
    public function edit($id) {
        $id = (int)$id;
        $model = new Empleado($this->db);
        $emp = $model->find($id);
        if (!$emp) { http_response_code(404); exit('No encontrado'); }
        $this->view('empleados/edit', [
            'emp' => $emp,
            'csrf' => \Core\Session::getCsrf()
        ]);
    }

    // POST /empleados/actualizar/{id}
    public function update($id) {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
        $id = (int)$id;
        $model = new Empleado($this->db);
        $emp = $model->find($id);
        if (!$emp) { http_response_code(404); exit('No encontrado'); }

        $data = [
            'nombre'   => trim($_POST['nombre']   ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'ci'       => trim($_POST['ci']       ?? ''),
            'puesto'   => trim($_POST['puesto']   ?? ''),
            'rol'      => trim($_POST['rol']      ?? $emp['rol']),
        ];

        if ($data['nombre']==='' || $data['ci']==='') {
            Session::flash('msg','Nombre y CI son obligatorios.');
            $this->redirect('/empleados/editar/'.$id);
        }
        if (!in_array($data['rol'], ['admin','cajero','mesero'], true)) {
            Session::flash('msg','Rol inválido.');
            $this->redirect('/empleados/editar/'.$id);
        }

        // Protección: no permitir dejar al sistema sin administradores
        $isAdminOriginal = ($emp['rol'] === 'admin');
        $isAdminNuevo = ($data['rol'] === 'admin');
        if ($isAdminOriginal && !$isAdminNuevo) {
            // Está intentando bajar a un admin
            if ($model->countAdmins() <= 1) {
                Session::flash('msg','No puedes quitar el rol admin: es el último administrador.');
                $this->redirect('/empleados/editar/'.$id);
            }
        }

        try {
            $model->update($id, $data);
            Session::flash('msg','Empleado actualizado.');
            $this->redirect('/empleados');
        } catch (\PDOException $e) {
            if ((int)$e->errorInfo[1] === 1062) {
                Session::flash('msg','El CI ya está registrado.');
                $this->redirect('/empleados/editar/'.$id);
            }
            throw $e;
        }
    }

    // POST /empleados/eliminar/{id}
    public function destroy($id) {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
        $id = (int)$id;
        $model = new Empleado($this->db);

        // No se puede eliminar a sí mismo
        if ($id === \Core\Auth::userId()) {
            Session::flash('msg','No puedes eliminar tu propio usuario.');
            $this->redirect('/empleados');
        }
        // No dejar sin administradores
        if ($model->isAdmin($id) && $model->countAdmins() <= 1) {
            Session::flash('msg','No puedes eliminar al último administrador.');
            $this->redirect('/empleados');
        }

        $model->delete($id);
        Session::flash('msg','Empleado eliminado.');
        $this->redirect('/empleados');
    }

    // GET /empleados/password/{id}
    public function passwordForm($id) {
        $id = (int)$id;
        $model = new Empleado($this->db);
        $emp = $model->find($id);
        if (!$emp) { http_response_code(404); exit('No encontrado'); }
        $this->view('empleados/password', [
            'emp' => $emp,
            'csrf' => \Core\Session::getCsrf()
        ]);
    }

    // POST /empleados/password/{id}
    public function passwordUpdate($id) {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
        $id = (int)$id;
        $pass = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password2'] ?? '');

        if ($pass === '' || $pass !== $pass2 || strlen($pass) < 6) {
            Session::flash('msg','Contraseña inválida (mínimo 6) o no coincide.');
            $this->redirect('/empleados/password/'.$id);
        }
        (new Empleado($this->db))->updatePassword($id, $pass);
        Session::flash('msg','Contraseña actualizada.');
        $this->redirect('/empleados');
    }
}
