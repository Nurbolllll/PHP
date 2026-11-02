<?php
declare(strict_types=1);
namespace App\Domain;

abstract class User {
    public function __construct(
        public readonly int $id,
        private string $fullName,
        public readonly string $email
    ) {
        if ($id < 1) throw new \InvalidArgumentException('ID должен быть положительным.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Некорректный email.');
        $this->rename($fullName);
    }
    final public function fullName(): string { return $this->fullName; }
    final public function rename(string $value): void {
        $value = trim($value);
        if (mb_strlen($value) < 3) throw new \InvalidArgumentException('Имя слишком короткое (минимум 3 символа).');
        $this->fullName = $value;
    }
    abstract public function roleLabel(): string;
}
