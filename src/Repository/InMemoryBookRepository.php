<?php
declare(strict_types=1);

namespace University\Library\Repository;

use University\Library\Contract\BookRepositoryInterface;
use University\Library\Model\Book;

final class InMemoryBookRepository implements BookRepositoryInterface
{
    /** @var array<string, Book> */
    private array $items = [];

    public function add(Book $book): void
    {
        $this->items[$book->isbn] = $book;
    }

    public function save(Book $book): void
    {
        $this->items[$book->isbn] = $book;
    }

    /** @return list<Book> */
    public function all(): array
    {
        return array_values($this->items);
    }

    public function findByIsbn(string $isbn): ?Book
    {
        return $this->items[$isbn] ?? null;
    }
}