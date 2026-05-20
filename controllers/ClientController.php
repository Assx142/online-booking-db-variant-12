<?php
// controllers/ClientController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Repository/ClientRepository.php';
require_once __DIR__ . '/../utils/Pagination.php';

class ClientController extends BaseController {
    private ClientRepository $repo;

    public function __construct() { $this->repo = new ClientRepository(); }

    public function index(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $sortBy = $_GET['sort'] ?? 'client_id';
        $order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $search = trim($_GET['search'] ?? '');

        $items = $search ? $this->repo->search($search, $page, $limit, $sortBy, $order) 
                         : $this->repo->findAll($page, $limit, $sortBy, $order);
        $total = $this->repo->count($search);
        $pagination = new Pagination($page, $limit, $total, $_GET);

        $this->render('client/index', ['items' => $items, 'pagination' => $pagination, 'search' => $search, 'sort' => $sortBy, 'order' => $order]);
    }

    public function create(): void {
        $this->render('client/create', ['errors' => $this->errors, 'old' => $this->oldInput, 'csrf' => CSRF::tokenField()]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirect('index.php?entity=client&action=create');
        }
        $this->oldInput = $_POST;
        $v = new Validator($_POST);
        $v->required('last_name', 'Фамилия')->required('first_name', 'Имя')
          ->phone('phone', 'Телефон')->email('email', 'Email', true)
          ->dateNotFuture('birth_date', 'Дата рождения');

        if ($v->fails()) { $this->render('client/create', ['errors' => $v->getErrors(), 'old' => $v->getData(), 'csrf' => CSRF::tokenField()]); return; }

        $d = $v->getData();
        $this->repo->create([
            'last_name' => $d['last_name'], 'first_name' => $d['first_name'], 'patronymic' => $d['patronymic'] ?: null,
            'phone' => $d['phone'], 'email' => $d['email'] ?: null, 'passport_series' => $d['passport_series'] ?? null,
            'passport_number' => $d['passport_number'] ?? null, 'birth_date' => $d['birth_date'] ?: null
        ]);
        $this->setFlash('success', 'Клиент успешно добавлен');
        $this->redirect('index.php?entity=client&action=list');
    }

    public function edit(?int $id): void {
        if (!$id || !($item = $this->repo->findById($id))) { $this->setFlash('error', 'Клиент не найден'); $this->redirect('index.php?entity=client&action=list'); }
        $this->render('client/edit', ['item' => $item, 'errors' => $this->errors, 'csrf' => CSRF::tokenField()]);
    }

    public function update(?int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || !CSRF::validateToken($_POST['csrf_token'] ?? '')) { $this->redirect("index.php?entity=client&action=edit&id=$id"); }
        $v = new Validator($_POST);
        $v->required('last_name', 'Фамилия')->required('first_name', 'Имя')->phone('phone', 'Телефон');
        if ($v->fails()) { $this->render('client/edit', ['item' => $this->repo->findById($id), 'errors' => $v->getErrors(), 'csrf' => CSRF::tokenField()]); return; }
        $d = $v->getData();
        $this->repo->update($id, ['last_name' => $d['last_name'], 'first_name' => $d['first_name'], 'patronymic' => $d['patronymic'] ?: null, 'phone' => $d['phone'], 'email' => $d['email'] ?: null, 'birth_date' => $d['birth_date'] ?: null]);
        $this->setFlash('success', 'Данные обновлены');
        $this->redirect('index.php?entity=client&action=list');
    }

    public function delete(?int $id): void {
        if (!$id || !($item = $this->repo->findById($id))) { $this->redirect('index.php?entity=client&action=list'); }
        $check = $this->repo->canDelete($id);
        $this->render('client/delete', ['item' => $item, 'canDelete' => $check['can_delete'], 'reason' => $check['reason'], 'csrf' => CSRF::tokenField()]);
    }

    public function destroy(?int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || !CSRF::validateToken($_POST['csrf_token'] ?? '')) { $this->redirect('index.php?entity=client&action=list'); }
        $check = $this->repo->canDelete($id);
        if (!$check['can_delete']) { $this->setFlash('error', $check['reason']); $this->redirect('index.php?entity=client&action=list'); }
        $this->repo->delete($id);
        $this->setFlash('success', 'Клиент удалён');
        $this->redirect('index.php?entity=client&action=list');
    }

    // 🔹 ДОПОЛНИТЕЛЬНО: Детальная карточка
    public function view(?int $id): void {
        if (!$id || !($client = $this->repo->findById($id))) { $this->setFlash('error', 'Не найдено'); $this->redirect('index.php?entity=client&action=list'); }
        $credit = $this->repo->getCreditHistory($id);
        $stmt = $this->repo->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE client_id = :id");
        $stmt->execute(['id' => $id]);
        $appointmentsCount = (int)$stmt->fetchColumn();
        $this->render('client/view', ['client' => $client, 'credit' => $credit, 'appointmentsCount' => $appointmentsCount]);
    }
}
