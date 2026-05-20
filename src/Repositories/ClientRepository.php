<?php
namespace App\Repositories;

class ClientRepository extends AbstractRepository {
    protected string $table = 'clients';
    protected array $allowedOrderByColumns = ['id', 'name', 'phone', 'email'];

    public function findByPhone(string $phone): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE phone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}
