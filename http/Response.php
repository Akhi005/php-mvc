<?php

namespace app\http;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }
    public function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit; 
    }
}
