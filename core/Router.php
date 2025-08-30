<?php

namespace app\core;
use app\http\Request;
use app\http\Response;

class Router
{
    protected array $routes = [];

    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post(string $path, $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }

    public function put(string $path, $callback): void
    {
        $this->routes['put'][$path] = $callback;
    }

    public function delete(string $path, $callback): void
    {
        $this->routes['delete'][$path] = $callback;
    }
    public function patch(string $path, $callback): void
    {
        $this->routes['patch'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            return $this->response->json([
                'success' => false,
                'error' => 'Not Found'
            ], 404);
        }
        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];

            $result = call_user_func([$controller, $method]);
        } else {
            $result = call_user_func($callback, $this->request);
        }
        if ($result === null) {
            return;
        }
        if (is_array($result)) {
            return $this->response->json($result);
        }
        echo $result;
    }
}
