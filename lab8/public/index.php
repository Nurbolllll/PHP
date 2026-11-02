<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/functions.php';

$items = loadMetadata();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Реестр лабораторных работ</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #f3f4f6; padding: 30px; color: #1f2937; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h1 { color: #2563eb; font-size: 1.6rem; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; }
        form { background: #f9fafb; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 25px; }
        .form-group { margin-bottom: 12px; display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; font-size: 0.9rem; margin-bottom: 5px; color: #4b5563; }
        input[type="text"], input[type="file"] { padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; }
        button { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 0.95rem; }
        th { background: #f3f4f6; color: #4b5563; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .btn-download { color: #2563eb; text-decoration: none; font-weight: bold; }
        .btn-download:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h1>📚 Система приема лабораторных отчетов (Вариант 1)</h1>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">✓ Файл успешно принят, проверен Fileinfo и внесен в JSON-реестр!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">❌ Ошибка: <?php echo h($_GET['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="discipline">Дисциплина (Поле Варианта 1):</label>
            <input type="text" id="discipline" name="discipline" placeholder="Например: Программирование на PHP" required>
        </div>
        <div class="form-group">
            <label for="document">Выберите файл отчета (Только PDF до 5 МБ):</label>
            <input type="file" id="document" name="document" accept="application/pdf" required>
        </div>
        <button type="submit">🚀 Безопасно загрузить отчет</button>
    </form>

    <h2>Реестр загруженных документов (Сортировка по дате)</h2>
    <table>
        <thead>
            <tr>
                <th>Оригинальное имя</th>
                <th>Дисциплина</th>
                <th>Размер (байт)</th>
                <th>SHA-256</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="5" style="text-align: center; color: #9ca3af;">Реестр пока пуст. Загрузите первый PDF.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo h((string) $item['original_name']); ?></td>
                        <td><?php echo h((string) $item['discipline']); ?></td>
                        <td><?php echo h((string) $item['size']); ?></td>
                        <td style="font-family: monospace; font-size: 0.8rem; color: #6b7280;" title="<?php echo h((string) $item['sha256']); ?>">
                            <?php echo substr(h((string) $item['sha256']), 0, 10); ?>...
                        </td>
                        <td><a class="btn-download" href="download.php?id=<?php echo h((string) $item['id']); ?>">📥 Скачать</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
