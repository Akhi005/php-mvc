<?php

namespace app\models;

use PDO;
use app\core\Database;
use app\mvc\Model;

class Task extends Model
{
    private PDO $db;

    public string $title = '';
    public string $description = '';
    public string $date = '';

    public function __construct(Database $conn)
    {
        $this->db = $conn->getPDO();
    }
    public function rules(): array
    {
        return [
            'title' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 3],
                [self::RULE_MAX, 'max' => 100],
            ],
            'description' => [
                self::RULE_REQUIRED,
                [self::RULE_MIN, 'min' => 5],
                [self::RULE_MAX, 'max' => 1000],
            ],
            'date' => [
                self::RULE_REQUIRED
            ]
        ];
    }
    public function paginate(string $search = '', int $limit = 10, int $offset = 0, string $sortBy = 'date', string $direction = 'DESC'): array
    {
        $search = '%' . $search . '%';

        $allowedSortFields = ['title', 'date'];
        $allowedDirections = ['ASC', 'DESC'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'date';
        }
        $direction = strtoupper($direction);
        if (!in_array($direction, $allowedDirections)) {
            $direction = 'DESC';
        }

        $sql = "
            SELECT id, title, description, date
            FROM todo_list
            WHERE title LIKE :search OR description LIKE :search
            ORDER BY $sortBy $direction
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', $search, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function count(string $search = ''): int
    {
        $search = '%' . $search . '%';

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as cnt
            FROM todo_list
            WHERE title LIKE :search OR description LIKE :search
        ");
        $stmt->bindValue(':search', $search, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetch()['cnt'];
    }
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM todo_list WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
    public function create(array $data): int|false
    {
        $this->loadData($data);

        if (!$this->validate()) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO todo_list (title, description, date) 
            VALUES (:title, :description, :date)
        ");

        $success = $stmt->execute([
            ':title' => $this->title,
            ':description' => $this->description,
            ':date' => $this->date
        ]);

        return $success ? (int)$this->db->lastInsertId() : false;
    }
    public function update(int $id, array $data): bool
    {
        $this->loadData($data);

        if (!$this->validate()) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE todo_list 
            SET title = :title, description = :description, date = :date 
            WHERE id = :id
        ");

        return $stmt->execute([
            ':title' => $this->title,
            ':description' => $this->description,
            ':date' => $this->date,
            ':id' => $id
        ]);
    }
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM todo_list WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    public function markAsComplete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE todo_list SET completed = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    public function getSummary(): array
    {
        $stmt = $this->db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN completed = 0 THEN 1 ELSE 0 END) as not_completed
        FROM todo_list
    ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
