<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/functions.php';

$id = $_GET['id'] ?? '';
// Валидация формата ID (строго 32 шестнадцатеричных символа)
if (!is_string($id) || !preg_match('/\A[a-f0-9]{32}\z/', $id)) {
    http_response_code(400);
    exit('Некорректный формат идентификатора.');
}

$item = null;
foreach (loadMetadata() as $candidate) {
    if (($candidate['id'] ?? null) === $id) {
        $item = $candidate;
        break;
    }
}

if (!is_array($item)) {
    http_response_code(404);
    exit('Документ не найден в реестре.');
}

// Формируем путь строго на основе данных из реестра с очисткой basename
$path = UPLOAD_DIR . '/' . basename((string) $item['stored_name']);

if (!is_file($path)) {
    http_response_code(404);
    exit('Физический файл отсутствует на сервере.');
}

// Отправка безопасных HTTP-заголовков для скачивания
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
// Подстановка оригинального безопасного имени при отдаче пользователю
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode((string)$item['original_name']));
header('X-Content-Type-Options: nosniff');

readfile($path);
