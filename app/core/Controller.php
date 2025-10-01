<?php // Controller.php
namespace Core;

class Controller {
    protected function view(string $template, array $data = []) {
        View::render($template, $data);
    }
    protected function redirect(string $to) {
        header("Location: $to"); exit;
    }
}
