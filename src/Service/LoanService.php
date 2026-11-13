<?php
declare(strict_types=1);

namespace University\Library\Service;

use University\Library\Contract\BookRepositoryInterface;
use University\Library\Exception\LibraryException;
use University\Library\Model\Book;

final readonly class LoanService
{
    public function __construct(
        private BookRepositoryInterface $repository
    ) {}

    public function lend(string $isbn): void
    {
        $book = $this->repository->findByIsbn($isbn);
        if ($book === null) {
            throw new LibraryException("Книга с ISBN {$isbn} не найдена.");
        }
        if (!$book->available) {
            throw new LibraryException("Книга уже выдана.");
        }
        $this->repository->save(new Book($book->isbn, $book->title, false));
    }

   /** @return list<Book> */
       public function availableBooks(): array
       {
           return array_values(array_filter(
               $this->repository->all(), // Сначала массив
               static fn (Book $book): bool => $book->available // Затем функция
           ));
       }
}