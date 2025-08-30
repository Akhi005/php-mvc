<?php

namespace app\core;
use app\http\Request;
use app\http\Response;

class Application
{
    public static string $ROOT_DIR;
    public static Application $app;

    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;

    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['database']);
    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
