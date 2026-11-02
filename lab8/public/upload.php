<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$storedPath = null;
try {
    $file = uploadedFile('document');
    $mime = pdfMime($file['tmp_name']);
    
    // Получение дополнительного поля Варианта 1
    $discipline = trim($_POST['discipline'] ?? 'Общая');
    if ($discipline === '') {
        throw new RuntimeException('Укажите название дисциплины.');
    }

    // Генерация криптографически стойкого случайного имени для скрытия на сервере
    $id = bin2hex(random_bytes(16));
    $storedName = $id . '.pdf';
    $storedPath = UPLOAD_DIR . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
        throw new RuntimeException('Не удалось перенести временный файл в постоянное хранилище.');
    }

    // Запись метаданных (включая хэш sha256 для контроля целостности)
    appendMetadata([
        'id' => $id,
        'original_name' => basename((string) $file['name']),
        'stored_name' => $storedName,
        'mime' => $mime,
        'size' => filesize($storedPath),
        'sha256' => hash_file('sha256', $storedPath),
        'uploaded_at' => (new DateTimeImmutable())->format(DATE_ATOM),
        'discipline' => $discipline
    ]);

    header('Location: /lab8/public/index.php?ok=1');
    exit;
} catch (Throwable $e) {
    // Откат изменений: удаляем файл-сироту, если упал JSON
    if ($storedPath !== null && is_file($storedPath)) {
        unlink($storedPath);
    }
    error_log($e->__toString());
    header('Location: /lab8/public/index.php?error=' . urlencode($e->getMessage()));
    exit;
}
