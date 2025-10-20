<?php
namespace Core;

// Polyfill local para PHP < 8 (en el namespace Core)
if (!function_exists('Core\\str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        $haystack = (string)$haystack; $needle = (string)$needle;
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

class Router
{
    /** @var array<string,array<int,array{path:string,regex:string,vars:array<int,string>,handler:callable|array}>> */
    private array $routes = [
        'GET'=>[], 'POST'=>[], 'PUT'=>[], 'PATCH'=>[], 'DELETE'=>[]
    ];

    /** @var array<string,array<string,callable|array>> */
    private array $static = [
        'GET'=>[], 'POST'=>[], 'PUT'=>[], 'PATCH'=>[], 'DELETE'=>[]
    ];

    // Açúcar sintáctico (PHP 7.4: sin union types en firma)
    public function get(string $path, $handler): void    { $this->add('GET',    $path, $handler); }
    public function post(string $path, $handler): void   { $this->add('POST',   $path, $handler); }
    public function put(string $path, $handler): void    { $this->add('PUT',    $path, $handler); }
    public function patch(string $path, $handler): void  { $this->add('PATCH',  $path, $handler); }
    public function delete(string $path, $handler): void { $this->add('DELETE', $path, $handler); }

    /** @param callable|array $handler */
    public function add(string $method, string $path, $handler): void
    {
        $method = strtoupper($method);
        if ($path === '' || $path[0] !== '/') $path = '/' . ltrim($path, '/');
        if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');

        // Ruta estática (sin llaves)
        if (strpos($path, '{') === false) {
            $this->static[$method][$path] = $handler;
            return;
        }

        // Compilar patrón con soporte {name} y {name:regex}
        $vars = [];
        $regex = preg_replace_callback('/\{(\w+)(?::([^}]+))?\}/', function($m) use (&$vars) {
            $vars[] = $m[1];
            $pat = (isset($m[2]) && $m[2] !== '') ? $m[2] : '[^/]+';
            return '(?P<' . $m[1] . '>' . $pat . ')';
        }, $path);
        $regex = '@^' . $regex . '$@';

        $this->routes[$method][] = [
            'path'    => $path,
            'regex'   => $regex,
            'vars'    => $vars,
            'handler' => $handler,
        ];
    }

    /**
     * Despacha una petición.
     * @return mixed
     */
    public function dispatch(string $requestPath, string $method)
    {
        $method = strtoupper($method);
        $tryHeadAsGet = ($method === 'HEAD');

        // 1) Normalizar contra base (soporta subcarpeta p.ej. /Restaurant_Jaxu/public)
        $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $base   = rtrim(dirname($script), '/\\');
        $path   = parse_url($requestPath, PHP_URL_PATH);
        $path   = $path !== false && $path !== null ? $path : '/';
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        if ($path === '') $path = '/';
        if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');

        // 2) Coincidencia exacta
        if (isset($this->static[$method][$path])) {
            return $this->invoke($this->static[$method][$path], []);
        }
        if ($tryHeadAsGet && isset($this->static['GET'][$path])) {
            return $this->invoke($this->static['GET'][$path], []);
        }

        // 3) Coincidencia con variables
        if (!empty($this->routes[$method])) {
            foreach ($this->routes[$method] as $r) {
                if (preg_match($r['regex'], $path, $m)) {
                    $params = [];
                    foreach ($r['vars'] as $v) { $params[] = $m[$v]; }
                    return $this->invoke($r['handler'], $params);
                }
            }
        }
        if ($tryHeadAsGet && !empty($this->routes['GET'])) {
            foreach ($this->routes['GET'] as $r) {
                if (preg_match($r['regex'], $path, $m)) {
                    $params = [];
                    foreach ($r['vars'] as $v) { $params[] = $m[$v]; }
                    return $this->invoke($r['handler'], $params);
                }
            }
        }

        // 4) ¿Existe la ruta en otro método? => 405
        $allowed = [];
        foreach ($this->static as $m => $map) {
            if (isset($map[$path])) $allowed[] = $m;
        }
        if (!$allowed) {
            foreach ($this->routes as $m => $list) {
                foreach ($list as $r) {
                    if (preg_match($r['regex'], $path)) { $allowed[] = $m; break; }
                }
            }
        }
        if ($allowed) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            throw new \Exception('Method Not Allowed', 405);
        }

        // 5) 404
        throw new \Core\HttpNotFoundException();
    }

    /**
     * @param callable|array $handler
     * @return mixed
     */
    private function invoke($handler, array $params)
    {
        if (is_array($handler)) {
            list($class, $action) = $handler;
            if (!class_exists($class)) {
                throw new \RuntimeException("Controller no encontrado: {$class}", 500);
            }
            $controller = new $class();
            if (!method_exists($controller, $action)) {
                throw new \RuntimeException("Acción no encontrada: {$action}", 500);
            }
            return call_user_func_array([$controller, $action], $params);
        }
        return call_user_func_array($handler, $params);
    }
}
