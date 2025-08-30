<?php

namespace app\mvc;

use app\core\Application;
use app\http\Request;
use app\http\Response;

class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = Application::$app->request;
        $this->response = Application::$app->response;
    }
}
