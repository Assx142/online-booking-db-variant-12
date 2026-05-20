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
<?php
namespace App\Repositories;
use App\Exceptions\RepositoryException;

class AppointmentRepository extends AbstractRepository {
    protected string $table = 'appointments';
    protected array $allowedOrderByColumns = ['id', 'datetime', 'status', 'client_id'];

    public function createAppointment(int $clientId, int $specialistId, int $serviceId, string $datetime, string $status = 'pending'): int {
        $this->pdo->beginTransaction();
        try {
            // Проверка на дублирование времени (бизнес-правило)
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM {$this->table} WHERE specialist_id = :sid AND datetime = :dt");
            $stmt->execute(['sid' => $specialistId, 'dt' => $datetime]);
            if ($stmt->fetch()['cnt'] > 0) {
                throw new RepositoryException("Время уже занято выбранным специалистом.");
            }

            $sql = "INSERT INTO {$this->table} (client_id, specialist_id, service_id, datetime, status) 
                    VALUES (:cid, :sid, :seid, :dt, :st)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'cid' => $clientId, 'sid' => $specialistId, 'seid' => $serviceId, 'dt' => $datetime, 'st' => $status
            ]);

            $newId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return $newId;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw new RepositoryException("Не удалось создать запись: " . $e->getMessage());
        }
    }

    public function updateStatus(int $id, string $newStatus): bool {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['status' => $newStatus, 'id' => $id]);
    }

    public function getAppointmentsByDate(string $date): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE DATE(datetime) = :date ORDER BY datetime");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }
}
