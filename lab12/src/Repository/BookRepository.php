<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;
use RuntimeException;
use Throwable;

final readonly class BookRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO books (isbn, title, author, copies)
             VALUES (:isbn, :title, :author, :copies)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, isbn, title, author, copies, created_at, updated_at
             FROM books WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

        public function findAll(
        string $search = '',
        string $sort = 'title',
        int $limit = 10,
        int $offset = 0
    ): array {
        $allowedSort = ['title', 'isbn', 'author', 'copies', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'title';
        }

        $sql = "SELECT id, isbn, title, author, copies
                FROM books
                WHERE title LIKE :search1 OR author LIKE :search2 OR isbn LIKE :search3
                ORDER BY $sort ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $like = '%' . $search . '%';
        $stmt->bindValue('search1', $like, PDO::PARAM_STR);
        $stmt->bindValue('search2', $like, PDO::PARAM_STR);
        $stmt->bindValue('search3', $like, PDO::PARAM_STR);
        $stmt->bindValue('limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $stmt->bindValue('offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE books
             SET isbn = :isbn, title = :title, author = :author, copies = :copies
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id] + $data);
        return $this->find($id) !== null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function issueBookWithAudit(int $id, bool $simulateFailure = false): void
    {
        try {
            $this->pdo->beginTransaction();

            $update = $this->pdo->prepare(
                'UPDATE books SET copies = copies - 1 WHERE id = :id AND copies > 0'
            );
            $update->execute(['id' => $id]);

            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Книга не найдена или нет доступных экземпляров.');
            }

            if ($simulateFailure) {
                $this->pdo->exec('INSERT INTO non_existent_table VALUES (1)');
            }

            $log = $this->pdo->prepare(
                'INSERT INTO audit_log (entity_type, entity_id, action)
                 VALUES (:type, :id, :action)'
            );
            $log->execute([
                'type' => 'book',
                'id' => $id,
                'action' => 'issued',
            ]);

            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
