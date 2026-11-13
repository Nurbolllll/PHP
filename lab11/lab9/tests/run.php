<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Isbn, Student, Librarian, Book, User};

function expectException(callable $action, string $message): void {
    try {
        $action();
        echo "FAIL: $message (Исключение не выброшено)\n";
    } catch (\Throwable $e) {
        echo "OK: $message — Исключение: {$e->getMessage()}\n";
    }
}

function printUserCard(User $user): void {
    printf("ID: %d | %s | %s (%s)\n", $user->id, $user->fullName(), $user->roleLabel(), $user->email);
}

echo "=== Сценарии проверки ===\n";
$student = new Student(101, 'Иван Иванов', 'ivan@stud.kz');
$librarian = new Librarian(1, 'Алия Сергазы', 'aliya@lib.kz');
$book = new Book(new Isbn('9785171221111'), 'ООП на PHP');

echo "\n1. Полиморфизм:\n";
printUserCard($student);
printUserCard($librarian);

echo "\n2. Валидация инвариантов:\n";
expectException(fn() => new Student(102, 'Я', 'test@test.com'), 'Короткое имя');
expectException(fn() => new Isbn('invalid'), 'Некорректный ISBN');

echo "\n3. Работа с бизнес-логикой:\n";
$now = new \DateTimeImmutable();
$loan = $book->issueTo($student, $now);
echo "OK: Книга выдана. Срок: " . $loan->dueDate->format('Y-m-d') . "\n";

expectException(fn() => $book->issueTo($student, $now), 'Повторная выдача (дубликат)');
$book->returnFrom($student);
echo "OK: Книга успешно возвращена.\n";
