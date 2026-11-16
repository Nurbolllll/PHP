<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($book['id']) ? 'Редактирование книги' : 'Новая книга' ?></title>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 30px 15px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; padding: 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        h1 { margin-top: 0; font-size: 22px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px; }
        .field { margin-bottom: 16px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; color: #374151; }
        input[type="text"], input[type="number"] { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .err { color: #dc2626; font-size: 13px; margin-top: 5px; background: #fef2f2; padding: 6px 10px; border-radius: 6px; border: 1px solid #fecaca; }
        .actions { display: flex; gap: 12px; align-items: center; margin-top: 24px; }
        .btn { padding: 10px 18px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn:hover { background: #1d4ed8; }
        .btn-cancel { background: #e5e7eb; color: #374151; }
        .btn-cancel:hover { background: #d1d5db; }
    </style>
</head>
<body>
<div class="container">
    <h1><?= isset($book['id']) ? '✏️ Редактирование книги' : '➕ Новая книга' ?></h1>
    <form method="POST" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="field">
            <label>ISBN (например, 978-5-4461-1111-1)</label>
            <input type="text" name="isbn" value="<?= htmlspecialchars((string)($book['isbn'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['isbn'])): ?><div class="err"><?= htmlspecialchars($errors['isbn'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label>Название книги</label>
            <input type="text" name="title" value="<?= htmlspecialchars((string)($book['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['title'])): ?><div class="err"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label>Автор</label>
            <input type="text" name="author" value="<?= htmlspecialchars((string)($book['author'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['author'])): ?><div class="err"><?= htmlspecialchars($errors['author'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <div class="field">
            <label>Количество экземпляров</label>
            <input type="number" name="copies" value="<?= (int)($book['copies'] ?? 1) ?>">
            <?php if (!empty($errors['copies'])): ?><div class="err"><?= htmlspecialchars($errors['copies'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>

        <div class="actions">
            <button type="submit" class="btn">Сохранить</button>
            <a href="?action=index" class="btn btn-cancel">Отмена</a>
        </div>
    </form>
</div>
</body>
</html>
