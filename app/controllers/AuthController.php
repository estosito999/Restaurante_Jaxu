<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

class AuthController extends Controller
{
    public function loginForm() {
        // Renderiza views/auth/login.php
        return $this->view('auth/login', [
            'flash' => Session::flash('msg', 'null')
        ]);
    }

    public function login() {
        if (!Session::checkCsrf($_POST['_token'] ?? '')) {
            Session::flash('msg','Sesión inválida. Recarga la página.');
            return $this->redirect('/login');
        }
        $ci   = preg_replace('/\D+/', '', $_POST['ci'] ?? '');
        $pass = $_POST['password'] ?? '';

        $db = Database::getInstance();
        $st = $db->prepare("SELECT id_empleado, nombre, rol, password_hash FROM empleado WHERE ci=:ci LIMIT 1");
        $st->execute([':ci'=>$ci]);
        $u = $st->fetch();

        // Si tu usuario demo no tiene hash bcrypt, permite modo “dev”:
        $ok = $u && (
            (!empty($u['password_hash']) && password_verify($pass, $u['password_hash']))
            || ($u['password_hash'] === $pass) // ⚠️ quita esto en producción
        );

        if ($ok) {
            Session::set('user_id', (int)$u['id_empleado']);
            Session::set('rol', $u['rol']);
            return $this->redirect('/platos');
        }
        Session::flash('msg','Credenciales inválidas');
        return $this->redirect('/login');
    }

    public function logout() {
        Session::destroy();
        return $this->redirect('/login');
    }
}
