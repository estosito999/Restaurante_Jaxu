<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\Plato;

class PlatoController extends Controller {
    private \PDO $db;
    private array $rolesRead  = ['admin','cajero','mesero'];
    private array $rolesWrite = ['admin','cajero'];

    public function __construct() { 
        global $pdo; 
        $this->db = $pdo; 
        Auth::requireLogin();
    }

    public function index() {
        Auth::requireRole($this->rolesRead);
        $plato = new Plato($this->db);

        // filtros opcionales ?q=&cat=
        $q   = trim($_GET['q']   ?? '');
        $cat = trim($_GET['cat'] ?? '');
        $platos = $plato->all($q, $cat);

        $this->view('platos/index', [
            'platos' => $platos,
            'q'      => $q,
            'cat'    => $cat,
            'flash'  => Session::flash('msg'),
        ]);
    }

    public function create() {
        Auth::requireRole($this->rolesWrite);
        $plato = new Plato($this->db);
        $this->view('platos/create', [
            'csrf'       => \Core\Session::getCsrf(),
            'categorias' => $plato->categorias(),
            'cocineros'  => $plato->listarCocineros()
        ]);
    }

    public function store() {
        Auth::requireRole($this->rolesWrite);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $plato = new Plato($this->db);
        $data = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio'      => (float)($_POST['precio'] ?? 0),
            'categoria'   => trim($_POST['categoria'] ?? ''),
            'id_cocinero' => (int)($_POST['id_cocinero'] ?? 0) ?: null,
        ];

        // Validaciones
        if ($data['nombre'] === '' || $data['precio'] < 0) {
            Session::flash('msg','Nombre obligatorio y precio ≥ 0.');
            return $this->redirect('/platos/crear');
        }
        if (!$plato->categoriaValida($data['categoria'])) {
            Session::flash('msg','Categoría inválida.');
            return $this->redirect('/platos/crear');
        }
        if (!is_null($data['id_cocinero']) && !$plato->empleadoExiste((int)$data['id_cocinero'])) {
            Session::flash('msg','Cocinero inexistente.');
            return $this->redirect('/platos/crear');
        }

        try {
            $plato->create($data);
            Session::flash('msg','Plato creado');
            $this->redirect('/platos');
        } catch (\PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                Session::flash('msg','Ya existe un plato con ese nombre en la categoría.');
                return $this->redirect('/platos/crear');
            }
            throw $e;
        }
    }

    public function edit($id) {
        Auth::requireRole($this->rolesWrite);
        $model = new Plato($this->db);
        $plato = $model->find((int)$id);
        if (!$plato) { http_response_code(404); exit('No encontrado'); }
        $this->view('platos/edit', [
            'plato'      => $plato,
            'csrf'       => \Core\Session::getCsrf(),
            'categorias' => $model->categorias(),
            'cocineros'  => $model->listarCocineros()
        ]);
    }

    public function update($id) {
        Auth::requireRole($this->rolesWrite);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $model = new Plato($this->db);
        $data = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio'      => (float)($_POST['precio'] ?? 0),
            'categoria'   => trim($_POST['categoria'] ?? ''),
            'id_cocinero' => (int)($_POST['id_cocinero'] ?? 0) ?: null,
        ];
        if ($data['nombre'] === '' || $data['precio'] < 0) {
            Session::flash('msg','Nombre obligatorio y precio ≥ 0.');
            return $this->redirect('/platos/editar/' . (int)$id);
        }
        if (!$model->categoriaValida($data['categoria'])) {
            Session::flash('msg','Categoría inválida.');
            return $this->redirect('/platos/editar/' . (int)$id);
        }
        if (!is_null($data['id_cocinero']) && !$model->empleadoExiste((int)$data['id_cocinero'])) {
            Session::flash('msg','Cocinero inexistente.');
            return $this->redirect('/platos/editar/' . (int)$id);
        }

        try {
            $model->update((int)$id, $data);
            Session::flash('msg','Plato actualizado');
            $this->redirect('/platos');
        } catch (\PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                Session::flash('msg','Ya existe un plato con ese nombre en la categoría.');
                return $this->redirect('/platos/editar/' . (int)$id);
            }
            throw $e;
        }
    }

    public function destroy($id) {
        Auth::requireRole($this->rolesWrite);
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
        try {
            (new Plato($this->db))->delete((int)$id);
            Session::flash('msg','Plato eliminado');
        } catch (\PDOException $e) {
            // 1451 = row is referenced (FK) -> tiene ventas
            if ((int)($e->errorInfo[1] ?? 0) === 1451) {
                Session::flash('msg','No se puede eliminar: el plato ya está en facturas.');
            } else {
                throw $e;
            }
        }
        $this->redirect('/platos');
    }
}
