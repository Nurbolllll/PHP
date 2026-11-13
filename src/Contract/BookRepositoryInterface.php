<?php
declare(strict_types=1);

namespace University\Library\Contract;

use University\Library\Model\Book;

interface BookRepositoryInterface
{
    public function add(Book $book): void;
    /** @return list<Book> */
    public function all(): array;
    public function findByIsbn(string $isbn): ?Book;
    public function save(Book $book): void;
}