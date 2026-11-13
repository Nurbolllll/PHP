<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use University\Library\Exception\LibraryException;
use University\Library\Model\Book;
use University\Library\Repository\InMemoryBookRepository;
use University\Library\Service\LoanService;

function expectException(callable $action, string $expected): void
{
    try {
        $action();
        throw new \RuntimeException("Ожидалось исключение {$expected}");
    } catch (\Throwable $error) {
        if (!$error instanceof $expected) {
            throw $error;
        }
    }
}

$repository = new InMemoryBookRepository();
$service = new LoanService($repository);
$repository->add(new Book('123-45', 'PHP 8.4 Guide'));

if (count($service->availableBooks()) !== 1) {
    throw new \RuntimeException("Неверное количество доступных книг.");
}

$service->lend('123-45');
if (count($service->availableBooks()) !== 0) {
    throw new \RuntimeException("Книга должна была стать недоступной после выдачи.");
}

expectException(
    fn() => $service->lend('123-45'),
    LibraryException::class
);

echo "Все проверки успешно пройдены!\n";