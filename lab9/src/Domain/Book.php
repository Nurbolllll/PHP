<?php
declare(strict_types=1);
namespace App\Domain;

final class Book {
    private array $activeLoans = [];
    private const MAX_STUDENT_LOANS = 3;

    public function __construct(public readonly Isbn $isbn, public readonly string $title) {
        if (trim($title) === '') throw new \InvalidArgumentException('Название не может быть пустым.');
    }
    public function issueTo(Student $student, \DateTimeImmutable $now): Loan {
        foreach ($this->activeLoans as $loan) {
            if ($loan->studentId === $student->id) throw new \DomainException('Этот студент уже взял данную книгу.');
        }
        if (count(array_filter($this->activeLoans, fn($l) => $l->studentId === $student->id)) >= self::MAX_STUDENT_LOANS) {
            throw new \DomainException('Превышен лимит выдачи книг.');
        }
        $loan = new Loan($student->id, $this->isbn, $now);
        $this->activeLoans[] = $loan;
        return $loan;
    }
    public function returnFrom(Student $student): void {
        foreach ($this->activeLoans as $key => $loan) {
            if ($loan->studentId === $student->id) {
                unset($this->activeLoans[$key]);
                $this->activeLoans = array_values($this->activeLoans);
                return;
            }
        }
        throw new \DomainException('Данный студент не брал эту книгу.');
    }
}
