<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог библиотеки</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 30px 15px; }
        .container { max-width: 960px; margin: 0 auto; background: #ffffff; padding: 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; }
        h1 { margin: 0; font-size: 24px; color: #111827; }
        .btn { display: inline-block; padding: 10px 16px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 500; border: none; cursor: pointer; font-size: 14px; transition: background 0.2s; }
        .btn:hover { background: #1d4ed8; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input, .search-bar select { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .search-bar input { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f9fafb; color: #4b5563; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        tr:hover { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #dbeafe; color: #1e40af; }
        .badge.zero { background: #fee2e2; color: #991b1b; }
        .actions a { color: #2563eb; text-decoration: none; font-weight: 500; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
        .empty { text-align: center; padding: 30px; color: #6b7280; }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 14px; color: #4b5563; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📚 Каталог библиотеки</h1>
        <a href="?action=create" class="btn">+ Добавить книгу</a>
    </div>

    <form method="GET" class="search-bar">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Поиск по названию, автору или ISBN...">
        <select name="sort">
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Сортировка: По названию</option>
            <option value="author" <?= $sort === 'author' ? 'selected' : '' ?>>Сортировка: По автору</option>
            <option value="isbn" <?= $sort === 'isbn' ? 'selected' : '' ?>>Сортировка: По ISBN</option>
            <option value="copies" <?= $sort === 'copies' ? 'selected' : '' ?>>Сортировка: По количеству</option>
        </select>
        <button type="submit" class="btn">Найти</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ISBN</th>
                <th>Название</th>
                <th>Автор</th>
                <th>Экз.</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($books)): ?>
            <tr><td colspan="6" class="empty">Книги пока не найдены. Нажмите «+ Добавить книгу», чтобы создать первую запись.</td></tr>
        <?php else: ?>
            <?php foreach ($books as $b): ?>
                <tr>
                    <td>#<?= (int) $b['id'] ?></td>
                    <td><code><?= htmlspecialchars($b['isbn'], ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><strong><?= htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($b['author'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= (int)$b['copies'] === 0 ? 'zero' : '' ?>"><?= (int) $b['copies'] ?> шт.</span></td>
                    <td class="actions">
                        <a href="?action=show&id=<?= (int) $b['id'] ?>">Открыть</a>
                        <a href="?action=edit&id=<?= (int) $b['id'] ?>">Изменить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <div>
            <?php if ($page > 1): ?>
                <a href="?action=index&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>" class="btn">← Назад</a>
            <?php endif; ?>
        </div>
        <span>Страница <strong><?= $page ?></strong></span>
        <div>
            <?php if (count($books) === $limit): ?>
                <a href="?action=index&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>" class="btn">Вперёд →</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
