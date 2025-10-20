<?php
namespace Core;

abstract class Controller
{
    protected function view(string $template, array $data = [])
    {
        View::render($template, $data);
    }

    protected function redirect(string $to)
    {
        header("Location: {$to}");
        exit;
    }

    protected function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
