<?php
// models/Repository/ClientRepository.php
require_once __DIR__ . '/BaseRepository.php';

class ClientRepository extends BaseRepository {
    protected string $table = 'clients';

    protected function getAllowedColumns(): array {
        return ['client_id', 'last_name', 'first_name', 'patronymic', 'phone', 'email', 'birth_date', 'created_at'];
    }

    protected function getSearchableFields(): array {
        return ['last_name', 'first_name', 'phone', 'email'];
    }

    // Проверка связей перед удалением
    public function canDelete(int $id): array {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE client_id = :id AND status != 'отменено'");
        $stmt->execute(['id' => $id]);
        $count = (int)$stmt->fetchColumn();
        return [
            'can_delete' => $count === 0,
            'reason' => $count > 0 ? "У клиента есть $count активных записей. Сначала отмените их." : null
        ];
    }

    // Получение кредитной истории для страницы view
    public function getCreditHistory(int $clientId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM credit_histories WHERE client_id = :id");
        $stmt->execute(['id' => $clientId]);
        return $stmt->fetch() ?: null;
    }
}
