<?php
require_once 'config.php';

// Простой автозагрузчик для пространств имён
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Database\Database;
use App\Repositories\ClientRepository;
use App\Repositories\AppointmentRepository;
use App\Exceptions\RepositoryException;

echo "<pre>";
try {
    $pdo = Database::getInstance()->getConnection();
    $clientRepo = new ClientRepository($pdo);
    $appointmentRepo = new AppointmentRepository($pdo);

    echo "1. Поиск клиента по телефону:\n";
    print_r($clientRepo->findByPhone('+79001234567'));

    echo "\n2. Создание записи на приём:\n";
    $newId = $appointmentRepo->createAppointment(1, 2, 5, '2026-05-22 15:00:00', 'pending');
    echo "✅ Запись создана с ID: $newId\n";

    echo "\n3. Записи на 2026-05-22:\n";
    print_r($appointmentRepo->getAppointmentsByDate('2026-05-22'));

    echo "\n4. Изменение статуса:\n";
    $appointmentRepo->updateStatus($newId, 'confirmed');
    print_r($appointmentRepo->findById($newId));

    echo "\n5. Удаление записи:\n";
    $appointmentRepo->delete($newId);
    echo "✅ Запись удалена.\n";

} catch (RepositoryException $e) {
    echo "❌ Ошибка DAL: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "❌ Общая ошибка: " . $e->getMessage() . "\n";
}
echo "</pre>";
