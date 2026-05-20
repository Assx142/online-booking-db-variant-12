<?php
// public/index.php
session_start();
date_default_timezone_set('Europe/Moscow');

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $map = [
        'Database' => __DIR__ . '/../config/database.php',
        'BaseRepository' => __DIR__ . '/../models/Repository/BaseRepository.php',
        'ClientRepository' => __DIR__ . '/../models/Repository/ClientRepository.php',
        'ServiceRepository' => __DIR__ . '/../models/Repository/ServiceRepository.php',
        'EmployeeRepository' => __DIR__ . '/../models/Repository/EmployeeRepository.php',
        'BaseController' => __DIR__ . '/../controllers/BaseController.php',
        'ClientController' => __DIR__ . '/../controllers/ClientController.php',
        'ServiceController' => __DIR__ . '/../controllers/ServiceController.php',
        'EmployeeController' => __DIR__ . '/../controllers/EmployeeController.php',
        'Validator' => __DIR__ . '/../utils/Validator.php',
        'CSRF' => __DIR__ . '/../utils/CSRF.php',
        'Pagination' => __DIR__ . '/../utils/Pagination.php',
    ];
    if (isset($map[$class])) require_once $map[$class];
});

// Маршрутизация
$entity = $_GET['entity'] ?? 'client';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) && ctype_digit($_GET['id']) && $_GET['id'] > 0 ? (int)$_GET['id'] : null;

$controllers = ['client' => 'ClientController', 'service' => 'ServiceController', 'employee' => 'EmployeeController'];
$controllerClass = $controllers[$entity] ?? null;

if (!$controllerClass || !class_exists($controllerClass)) {
    http_response_code(404);
    die('<div class="container mt-5"><div class="alert alert-danger">404: Сущность не найдена</div></div>');
}

$controller = new $controllerClass();
$methods = ['list' => 'index', 'create' => 'create', 'store' => 'store', 'edit' => 'edit', 'update' => 'update', 'delete' => 'delete', 'destroy' => 'destroy', 'view' => 'view'];
$method = $methods[$action] ?? null;

if ($method && method_exists($controller, $method)) {
    $controller->$method($id);
} else {
    http_response_code(404);
    die('<div class="container mt-5"><div class="alert alert-danger">404: Действие не найдено</div></div>');
}
