<?php
namespace App\Repositories;
use App\Exceptions\RepositoryException;

abstract class AbstractRepository {
    protected \PDO $pdo;
    protected string $table;
    // Белый список столбцов для сортировки (защита от инъекций через ORDER BY)
    protected array $allowedOrderByColumns = [];

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(array $where = [], ?string $orderBy = null, ?int $limit = null): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $placeholder = ":w_$column";
                $conditions[] = "$column = $placeholder";
                $params[$placeholder] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($orderBy !== null) {
            if (!in_array($orderBy, $this->allowedOrderByColumns, true)) {
                throw new RepositoryException("Недопустимое поле для сортировки: $orderBy");
            }
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
