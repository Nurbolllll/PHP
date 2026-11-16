<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточка книги</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 30px 15px; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; padding: 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        h1 { margin-top: 0; font-size: 24px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px; }
        .meta { list-style: none; padding: 0; margin: 0 0 24px 0; }
        .meta li { padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; font-size: 15px; }
        .meta strong { color: #4b5563; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .btn { padding: 10px 16px; border-radius: 8px; font-weight: 500; font-size: 14px; text-decoration: none; border: none; cursor: pointer; color: #fff; }
        .btn-primary { background: #2563eb; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; }
        .btn-success:hover { background: #047857; }
        .btn-success:disabled { background: #9ca3af; cursor: not-allowed; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-back { background: #e5e7eb; color: #374151; }
        .btn-back:hover { background: #d1d5db; }
    </style>
</head>
<body>
<div class="container">
    <h1>📖 <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <ul class="meta">
        <li><strong>ID записи:</strong> <span>#<?= (int) $book['id'] ?></span></li>
        <li><strong>ISBN:</strong> <span><code><?= htmlspecialchars($book['isbn'], ENT_QUOTES, 'UTF-8') ?></code></span></li>
        <li><strong>Автор:</strong> <span><?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></span></li>
        <li><strong>Доступно экземпляров:</strong> <span><strong><?= (int) $book['copies'] ?> шт.</strong></span></li>
        <li><strong>Дата добавления:</strong> <span><?= htmlspecialchars($book['created_at'], ENT_QUOTES, 'UTF-8') ?></span></li>
        <li><strong>Последнее обновление:</strong> <span><?= htmlspecialchars($book['updated_at'], ENT_QUOTES, 'UTF-8') ?></span></li>
    </ul>

    <div class="toolbar">
        <form method="POST" action="?action=issue&id=<?= (int) $book['id'] ?>" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-success" <?= $book['copies'] < 1 ? 'disabled' : '' ?>>Выдать книгу (-1 экз. + аудит)</button>
        </form>

        <a href="?action=edit&id=<?= (int) $book['id'] ?>" class="btn btn-primary">Редактировать</a>

        <form method="POST" action="?action=delete&id=<?= (int) $book['id'] ?>" style="margin:0;" onsubmit="return confirm('Вы уверены, что хотите удалить эту книгу?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-danger">Удалить</button>
        </form>

        <a href="?action=index" class="btn btn-back">← К списку</a>
    </div>
</div>
</body>
</html>
