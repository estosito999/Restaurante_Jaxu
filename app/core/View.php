<?php
namespace Core;

class View
{
    /**
     * Renderiza una vista con layout principal.
     * $template: ruta relativa dentro de app/views sin ".php" (p.ej. 'auth/login')
     */
    public static function render(string $template, array $data = [], string $layout = 'layouts/main')
    {
        $viewPath   = BASE_PATH . '/app/views/' . $template . '.php';
        $layoutPath = BASE_PATH . '/app/views/' . $layout . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            exit('Vista no encontrada: ' . htmlspecialchars($template, ENT_QUOTES, 'UTF-8'));
        }
        if (!is_file($layoutPath)) {
            http_response_code(500);
            exit('Layout no encontrado: ' . htmlspecialchars($layout, ENT_QUOTES, 'UTF-8'));
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require $layoutPath;
    }
}
