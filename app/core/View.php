<?php // View.php
namespace Core;

class View {
    public static function render(string $template, array $data = []) {
        extract($data);
        $viewPath = BASE_PATH . "/app/views/$template.php";
        $layoutPath = BASE_PATH . "/app/views/layouts/main.php";
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require $layoutPath;
        /**necesita una carpeta layauts/main.php y el archivo **/
    }
}
