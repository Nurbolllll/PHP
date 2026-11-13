<?php
declare(strict_types=1);

namespace University\Library\Model;

use University\Library\Exception\LibraryException;

final readonly class Book
{
    public function __construct(
        public string $isbn,
        public string $title,
        public bool $available = true
    ) {
        if (trim($isbn) === '') {
            throw new LibraryException('ISBN не может быть пустым.');
        }
        if (trim($title) === '') {
            throw new LibraryException('Название книги обязательно.');
        }
    }
}