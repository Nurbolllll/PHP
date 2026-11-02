<?php
declare(strict_types=1);
namespace App\Domain;

final class Student extends User {
    #[\Override]
    public function roleLabel(): string { return 'Студент'; }
}
