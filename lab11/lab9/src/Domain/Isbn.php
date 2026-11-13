<?php
declare(strict_types=1);
namespace App\Domain;

final readonly class Isbn {
    public function __construct(public string $value) {
        if (!preg_match('/^\d{13}$/', $value)) {
            throw new \InvalidArgumentException('Формат ISBN должен содержать ровно 13 цифр.');
        }
    }
    public function equals(self $other): bool {
        return $this->value === $other->value;
    }
}
