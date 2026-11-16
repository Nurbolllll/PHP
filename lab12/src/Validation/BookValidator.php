<?php
declare(strict_types=1);

namespace App\Validation;

final class BookValidator
{
    public static function validate(array $input): array
    {
        $isbn = strtoupper(trim((string) ($input['isbn'] ?? '')));
        $title = trim((string) ($input['title'] ?? ''));
        $author = trim((string) ($input['author'] ?? ''));
        $copies = filter_var($input['copies'] ?? null, FILTER_VALIDATE_INT);

        $errors = [];

        if (!preg_match('/^[0-9X-]{10,20}$/', $isbn)) {
            $errors['isbn'] = 'ISBN должен содержать от 10 до 20 символов (цифры, дефис или X).';
        }
        if ($title === '' || mb_strlen($title) > 180) {
            $errors['title'] = 'Название обязательно (до 180 символов).';
        }
        if ($author === '' || mb_strlen($author) > 120) {
            $errors['author'] = 'Автор обязателен (до 120 символов).';
        }
        if ($copies === false || $copies < 0) {
            $errors['copies'] = 'Количество экземпляров должно быть целым числом >= 0.';
        }

        return [
            'data' => [
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'copies' => (int) $copies,
            ],
            'errors' => $errors,
        ];
    }
}
