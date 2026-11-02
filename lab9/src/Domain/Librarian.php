<?php
declare(strict_types=1);
namespace App\Domain;

final class Librarian extends User {
    #[\Override]
    public function roleLabel(): string { return 'Библиотекарь'; }
}
