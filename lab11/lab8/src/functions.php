<?php
declare(strict_types=1);

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 МиБ
const DATA_FILE = __DIR__ . '/../var/data/submissions.json';
const UPLOAD_DIR = __DIR__ . '/../var/uploads';

// Безопасное экранирование строк для вывода в HTML
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Первичный приём и валидация системных параметров файла
function uploadedFile(string $field): array {
    $file = $_FILES[$field] ?? null;
    if (!is_array($file) || is_array($file['error'] ?? null)) {
        throw new RuntimeException('Некорректная структура файла.');
    }
    // 1. Проверяем код ошибки загрузки в первую очередь
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Файл не загружен. Код ошибки: ' . $file['error']);
    }
    // 2. Проверка размера файла
    if (!is_int($file['size']) || $file['size'] < 1 || $file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Недопустимый размер файла (лимит 5 МБ).');
    }
    // 3. Убеждаемся, что файл загружен через HTTP POST, а не локальный чит
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Источник файла не является валидной HTTP-загрузкой.');
    }
    return $file;
}

// Проверка реального содержимого файла через расширение Fileinfo
function pdfMime(string $path): string {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path);
    if ($mime !== 'application/pdf') {
        throw new RuntimeException('Разрешен только формат PDF. Подмена расширения заблокирована.');
    }
    return $mime;
}

// Атомарное обновление базы данных JSON с защитой от одновременной записи
function appendMetadata(array $item): void {
    $lockPath = DATA_FILE . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('Не удалось установить блокировку файла реестра.');
    }
    try {
        $json = is_file(DATA_FILE) ? file_get_contents(DATA_FILE) : '[]';
        if ($json === false) {
            throw new RuntimeException('Ошибка чтения реестра.');
        }
        $items = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($items)) {
            throw new RuntimeException('Неверная структура JSON.');
        }
        
        $items[] = $item;
        
        // Сортировка по дате загрузки (Требование Варианта 1)
        usort($items, fn($a, $b) => strcmp($b['uploaded_at'], $a['uploaded_at']));

        $newJson = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        
        // Запись во временный файл и атомарная замена через rename
        $tmp = DATA_FILE . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($tmp, $newJson, LOCK_EX) === false || !rename($tmp, DATA_FILE)) {
            @unlink($tmp);
            throw new RuntimeException('Не удалось атомарно сохранить изменения в реестр.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

// Чтение метаданных
function loadMetadata(): array {
    if (!is_file(DATA_FILE)) return [];
    $json = file_get_contents(DATA_FILE);
    if ($json === false) return [];
    $items = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    return is_array($items) ? $items : [];
}
