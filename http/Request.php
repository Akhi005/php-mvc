<?php

namespace app\http;

class Request
{

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'];
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    public function getBody()
    {
        $body = [];
        $method = $this->getMethod();
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (in_array($method, ['post', 'put', 'delete'])) {
            if (stripos($contentType, 'application/json') !== false) {
                $raw = file_get_contents("php://input");
                $body = json_decode($raw, true) ?? [];
            } else {
                parse_str(file_get_contents("php://input"), $body);
            }
        } elseif ($method === 'get') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
    public function getQueryParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    public function getParam(string $key, $default = null)
    {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }
}
