<?php
declare(strict_types=1);
namespace App\Domain;

final readonly class Loan {
    public \DateTimeImmutable $dueDate;
    public function __construct(public int $studentId, public Isbn $isbn, public \DateTimeImmutable $borrowedAt) {
        $this->dueDate = $borrowedAt->modify('+14 days');
    }
    public function isOverdue(\DateTimeImmutable $now): bool { return $now > $this->dueDate; }
}
