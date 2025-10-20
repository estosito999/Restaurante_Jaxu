<?php
namespace Core;

class HttpNotFoundException extends \Exception
{
    public function __construct(string $msg = 'Página no encontrada', int $code = 404)
    {
        parent::__construct($msg, $code);
    }
}
