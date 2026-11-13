<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use University\Library\Model\Book;
use University\Library\Repository\InMemoryBookRepository;
use University\Library\Service\LoanService;

$repository = new InMemoryBookRepository();
$repository->add(new Book('978-5-1', 'Чистый код'));
$repository->add(new Book('978-5-2', 'Совершенный код'));

$service = new LoanService($repository);
$service->lend('978-5-1');

echo "Доступные книги:\n";
foreach ($service->availableBooks() as $book) {
    printf("- [%s] %s\n", $book->isbn, $book->title);
}