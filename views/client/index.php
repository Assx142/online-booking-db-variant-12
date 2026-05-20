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
<div class="d-flex justify-content-between mb-3">
    <h2>👤 <?= $this->e($client['last_name']) ?> <?= $this->e($client['first_name']) ?></h2>
    <a href="index.php?entity=client&action=list" class="btn btn-secondary">← Назад</a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <p><b>Телефон:</b> <?= $this->e($client['phone']) ?></p>
        <p><b>Email:</b> <?= $client['email'] ? $this->e($client['email']) : '-' ?></p>
        <p><b>Дата рождения:</b> <?= $client['birth_date'] ? date('d.m.Y', strtotime($client['birth_date'])) : '-' ?></p>
    </div>
</div>
<?php if ($credit): ?>
<div class="card mb-3 <?= $credit['credit_score'] < 600 ? 'border-danger' : 'border-success' ?>">
    <div class="card-header <?= $credit['credit_score'] < 600 ? 'bg-danger text-white' : 'bg-success text-white' ?>">📊 Кредитная история</div>
    <div class="card-body">
        <p><b>Скоринг:</b> <?= $credit['credit_score'] ?> баллов</p>
        <p><b>Просрочки:</b> <?= $credit['has_defaults'] ? 'Да' : 'Нет' ?></p>
        <p><b>Обновлено:</b> <?= date('d.m.Y', strtotime($credit['last_update'])) ?></p>
    </div>
</div>
<?php endif; ?>
<div class="alert alert-info">📅 Всего записей: <b><?= $appointmentsCount ?></b></div>
