<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\ConnectionFactory;
use App\Repository\BookRepository;
use App\Validation\BookValidator;

$pdo = ConnectionFactory::create();
$repo = new BookRepository($pdo);

$pdo->exec('DELETE FROM audit_log');
$pdo->exec('DELETE FROM books');

echo "1. Тест валидации пустого поля: ";
$val = BookValidator::validate(['isbn' => '', 'title' => '', 'author' => 'Тест', 'copies' => 5]);
assert(!empty($val['errors']));
echo "OK\n";

echo "2. Тест корректного создания: ";
$id = $repo->create(['isbn' => '978-5-4461-1111-1', 'title' => 'Чистый код', 'author' => 'Роберт Мартин', 'copies' => 3]);
assert($id > 0);
echo "OK (ID=$id)\n";

echo "3. Тест дубликата ISBN: ";
try {
    $repo->create(['isbn' => '978-5-4461-1111-1', 'title' => 'Дубль', 'author' => 'Автор', 'copies' => 1]);
    assert(false);
} catch (PDOException $e) {
    assert($e->getCode() === '23000');
    echo "OK\n";
}

echo "4. Тест чтения существующего и неизвестного ID: ";
assert($repo->find($id)['title'] === 'Чистый код');
assert($repo->find(999999) === null);
echo "OK\n";

echo "5. Тест обновления (включая прежние значения): ";
$updated = $repo->update($id, ['isbn' => '978-5-4461-1111-1', 'title' => 'Чистый код', 'author' => 'Роберт Мартин', 'copies' => 3]);
assert($updated === true);
echo "OK\n";

echo "6. Тест SQL-инъекции в поиске и неверной сортировки: ";
$res = $repo->findAll("' OR '1'='1", "DROP TABLE books;");
assert(count($res) === 0);
echo "OK\n";

echo "7. Тест успешной транзакции (выдача книги): ";
$repo->issueBookWithAudit($id, false);
assert((int)$repo->find($id)['copies'] === 2);
echo "OK\n";

echo "8. Тест Rollback при сбое второго шага: ";
try {
    $repo->issueBookWithAudit($id, true);
} catch (Throwable $e) {
}
assert((int)$repo->find($id)['copies'] === 2, 'Количество копий не должно было уменьшиться!');
echo "OK (Rollback сработал)\n";

echo "9. Тест удаления записи: ";
assert($repo->delete($id) === true);
assert($repo->find($id) === null);
echo "OK\n";

echo "ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!\n";
