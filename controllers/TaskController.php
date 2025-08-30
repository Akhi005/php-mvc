<?php

namespace app\controllers;

use app\core\Application;
use app\models\Task;

class TaskController
{
    private function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function index()
    {
        $request = Application::$app->request;
        $search = $request->getQueryParam('search', '');
        $limit = (int)$request->getQueryParam('limit', 10);
        $offset = (int)$request->getQueryParam('offset', 0);
        $sortBy = $request->getQueryParam('sortBy', 'date');
        $direction = $request->getQueryParam('direction', 'DESC');

        $taskModel = new Task(Application::$app->db);
        $tasks = $taskModel->paginate($search, $limit, $offset, $sortBy, $direction);
        $total = $taskModel->count($search);

        echo json_encode([
            'success' => true,
            'data' => $tasks,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function store()
    {
        $request = Application::$app->request;
        $response = Application::$app->response;
        $data = $request->getBody();

        $task = new Task(Application::$app->db);
        $task->loadData($data);

        if (!$task->validate()) {
            return $response->json([
                'success' => false,
                'errors' => $task->errors
            ], 422);
        }

        $id = $task->create($data);

        if (!$id) {
            return $response->json([
                'success' => false,
                'error' => 'Creation failed'
            ], 500);
        }

        return $response->json([
            'success' => true,
            'id' => $id
        ], 201);
    }


    public function update()
    {
        $request = Application::$app->request;
        $response = Application::$app->response;
        $data = $request->getBody();
        $id = $request->getQueryParam('id');

        if (!$id || !is_numeric($id)) {
            return $response->json([
                'success' => false,
                'error' => 'Invalid task ID'
            ], 400); // Bad Request
        }

        $task = new Task(Application::$app->db);
        $success = $task->update((int)$id, $data);

        if (!$success) {
            return $response->json([
                'success' => false,
                'error' => 'Update failed'
            ], 500); // Server error
        }

        return $response->json([
            'success' => true
        ], 200); // OK
    }

    public function delete()
    {
        $request = Application::$app->request;
        $id = $request->getQueryParam('id');

        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
            return;
        }

        $task = new Task(Application::$app->db);
        $deleted = $task->delete((int)$id);

        if ($deleted) {
            http_response_code(204); // No Content
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'error' => 'Deletion failed']);
        }
    }
    public function complete($id)
    {
        $response = Application::$app->response;
        $taskModel = new Task(Application::$app->db);

        if (!$taskModel->markAsComplete((int)$id)) {
            return $response->json([
                'success' => false,
                'error' => 'Failed to mark task as complete'
            ], 500);
        }

        return $response->json([
            'success' => true
        ], 200);
    }
    public function summary()
    {
        $taskModel = new Task(Application::$app->db);

        $summary = $taskModel->getSummary();

        echo json_encode([
            'success' => true,
            'summary' => $summary
        ]);
    }
}
