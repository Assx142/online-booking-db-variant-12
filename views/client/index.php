<h2>👥 Клиенты банка</h2>
<a href="index.php?entity=client&action=create" class="btn btn-success mb-3">+ Добавить</a>

<form method="get" class="mb-3 d-flex gap-2">
    <input type="hidden" name="entity" value="client">
    <input type="hidden" name="action" value="list">
    <input type="text" name="search" class="form-control" placeholder="Поиск..." value="<?= $this->e($search ?? '') ?>">
    <button class="btn btn-primary">Найти</button>
    <?php if ($search): ?><a href="index.php?entity=client&action=list" class="btn btn-secondary">Сброс</a><?php endif; ?>
</form>

<?php if (empty($items)): ?><div class="alert alert-info">Нет данных</div><?php else: ?>
<table class="table table-striped table-hover">
    <thead><tr><th>ID</th><th>Фамилия</th><th>Имя</th><th>Телефон</th><th>Паспорт</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($items as $row): ?>
        <tr>
            <td><?= $row['client_id'] ?></td>
            <td><?= $this->e($row['last_name']) ?></td>
            <td><?= $this->e($row['first_name']) ?></td>
            <td><?= $this->e($row['phone']) ?></td>
            <td><?= $this->e($row['passport_series'] ?? '-').'-'. $this->e($row['passport_number'] ?? '-') ?></td>
            <td class="btn-group btn-group-sm">
                <a href="index.php?entity=client&action=view&id=<?= $row['client_id'] ?>" class="btn btn-info" title="Просмотр">👁️</a>
                <a href="index.php?entity=client&action=edit&id=<?= $row['client_id'] ?>" class="btn btn-primary">✏️</a>
                <a href="index.php?entity=client&action=delete&id=<?= $row['client_id'] ?>" class="btn btn-danger">🗑️</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../partials/pagination.php'; endif; ?>
