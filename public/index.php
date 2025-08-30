<?php

require_once __DIR__ . '/../vendor/autoload.php';

use app\core\Application;
use app\controllers\TaskController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$config = require __DIR__ . '/../public/config.php';

$app = new Application(dirname(__DIR__), $config);

$app->router->get('/', [TaskController::class, 'index']);

$app->router->get('/tasks', [TaskController::class, 'index']);    
$app->router->post('/tasks', [TaskController::class, 'store']);     
$app->router->put('/tasks', [TaskController::class, 'update']); 
$app->router->delete('/tasks', [TaskController::class, 'delete']); 
$app->router->patch('/tasks/{id}/complete', [TaskController::class, 'complete']);
$app->router->get('/tasks/summary', [TaskController::class, 'summary']);


$app->run();
