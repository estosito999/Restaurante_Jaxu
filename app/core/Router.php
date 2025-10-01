<?php
namespace Core;

class Router {
    private array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => []
    ];

    public function add(string $method, string $path, callable|array $handler) {
        $method = strtoupper($method);
        // Normaliza: siempre empieza con /
        if ($path === '' || $path[0] !== '/') $path = '/' . ltrim($path, '/');
        // Quita barra final excepto raíz
        if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $requestUri, string $method) {
        // Calcula base desde SCRIPT_NAME (ej: /Restaurant_Jaxu/public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base       = rtrim(dirname($scriptName), '/\\'); // ej: /Restaurant_Jaxu/public
        $path       = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        // Quita el base
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
            if ($path === false) { $path = '/'; }
        }
        // Normaliza
        $path = '/' . ltrim($path, '/');
        if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');

        $method = strtoupper($method);

        // 1) Coincidencia exacta
        if (isset($this->routes[$method][$path])) {
            return $this->call($this->routes[$method][$path], []);
        }

        // 2) Coincidencia con parámetros tipo {id}
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = "@^" . preg_replace('@\{(\w+)\}@', '(?P<$1>[^/]+)', $route) . "$@";
            if (preg_match($pattern, $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) $params[$k] = $v;
                }
                return $this->call($handler, $params);
            }
        }

        // 3) 404
        http_response_code(404);
        echo "404 Not Found";
        return null;
    }

    private function call(callable|array $handler, array $params) {
        if (is_array($handler)) {
            [$class, $action] = $handler;
            // Instancia controlador (debe ser FQN válido)
            if (!class_exists($class)) {
                http_response_code(500);
                echo "Controller no encontrado: " . htmlspecialchars($class);
                return null;
            }
            $controller = new $class();
            if (!method_exists($controller, $action)) {
                http_response_code(500);
                echo "Acción no encontrada: " . htmlspecialchars($action);
                return null;
            }
            return call_user_func_array([$controller, $action], array_values($params));
        }
        return call_user_func_array($handler, array_values($params));
    }
}

if (!function_exists('str_starts_with')) {
    // Polyfill para PHP < 8 (por si tu XAMPP es antiguo)
    function str_starts_with($haystack, $needle) {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }
}
