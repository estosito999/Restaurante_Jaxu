<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Auth;

class AuthController extends Controller {
    private \PDO $db;
    public function __construct() { global $pdo; $this->db = $pdo; }

    public function loginForm() {
        if (Auth::check()) { $this->redirect('/platos'); }
        $this->view('auth/login', ['csrf' => \Core\Session::getCsrf(), 'flash' => Session::flash('msg')]);
    }

    public function login() {
        if (!\Core\Session::checkCsrf($_POST['_csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

        $ci = trim($_POST['ci'] ?? '');
        $password = $_POST['password'] ?? '';

        $st = $this->db->prepare("SELECT id_empleado, password_hash, rol FROM empleado WHERE ci = ?");
        $st->execute([$ci]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id_empleado'];
            $_SESSION['rol']     = $user['rol'];
            Session::flash('msg', '¡Bienvenido!');
            $this->redirect('/platos'); // o dashboard
        }

        Session::flash('msg','CI o contraseña inválidos');
        $this->redirect('/login');
    }

    public function logout() {
        session_destroy();
        session_start();
        Session::flash('msg','Sesión cerrada');
        $this->redirect('/login');
    }
}
