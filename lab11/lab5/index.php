<?php
declare(strict_types=1);

require_once 'functions.php';
$products = require_once 'catalog.php';

// Валидируем каталог при загрузке
try {
    array_walk($products, fn(array $p) => validateProduct($p));
} catch (InvalidArgumentException $e) {
    echo "<p style='color: red;'>Ошибка данных в каталоге: " . e($e->getMessage()) . "</p>";
    exit;
}

// Получаем параметры из формы (из GET-запроса)
$searchQuery = trim($_GET['search'] ?? '');
$selectedGenre = $_GET['genre'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'none';

// Применяем поиск по автору/названию, если он введен
$result = $products;
if ($searchQuery !== '') {
    // Ищем либо по автору, либо по названию
    $result = array_values(array_filter($result, fn(array $p): bool =>
        mb_stripos((string)$p["name"], $searchQuery, 0, "UTF-8") !== false ||
        mb_stripos((string)$p["author"], $searchQuery, 0, "UTF-8") !== false
    ));
}

// Применяем фильтр по жанру
if ($selectedGenre !== 'all' && $selectedGenre !== '') {
    $result = filterCatalog($result, genre: $selectedGenre);
}

// Применяем сортировку
if ($sortBy === 'price_asc') {
    usort($result, fn(array $a, array $b): int => $a["price"] <=> $b["price"]);
} elseif ($sortBy === 'price_desc') {
    usort($result, fn(array $a, array $b): int => $b["price"] <=> $a["price"]);
} elseif ($sortBy === 'name_asc') {
    $result = sortByName($result, true);
}

// Собираем список уникальных жанров для выпадающего списка
$allGenres = array_unique(array_column($products, 'genre'));
sort($allGenres);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивный каталог книг (Вариант 5)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; color: #333; }
        h2 { color: #2c3e50; }
        .filter-form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter-form label { margin-right: 15px; display: inline-block; margin-bottom: 10px; }
        .filter-form input, .filter-form select { padding: 6px; margin-top: 5px; }
        .filter-form button { background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .filter-form button:hover { background: #2980b9; }
        .reset-btn { background: #95a5a6 !important; margin-left: 5px; text-decoration: none; color: white; padding: 8px 15px; border-radius: 4px; display: inline-block; font-size: 13px; }
        table { width: 100%; background: #fff; border-collapse: collapse; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #34495e; color: white; }
        tr:hover { background-color: #f1f2f6; }
        .stats { margin-top: 20px; background: #e8f8f5; padding: 15px; border-radius: 8px; border-left: 5px solid #2ecc71; }
    </style>
</head>
<body>

    <h2>📚 Интерактивный каталог книг (Вариант 5)</h2>

    <!-- Форма взаимодействия -->
    <form method="GET" class="filter-form">
        <label>
            Поиск (название или автор):<br>
            <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Например: Толстой или Код">
        </label>

        <label>
            Жанр:<br>
            <select name="genre">
                <option value="all">Все жанры</option>
                <?php foreach ($allGenres as $genre): ?>
                    <option value="<?= e($genre) ?>" <?= $selectedGenre === $genre ? 'selected' : '' ?>>
                        <?= e($genre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Сортировка:<br>
            <select name="sort">
                <option value="none" <?= $sortBy === 'none' ? 'selected' : '' ?>>По умолчанию (ID)</option>
                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Сначала дешевые</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
                <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>По названию (А-Я)</option>
            </select>
        </label>

        <div style="margin-top: 10px;">
            <button type="submit">Применить</button>
            <a href="index.php" class="reset-btn">Сбросить</a>
        </div>
    </form>

    <!-- Вывод результатов -->
    <?php if (count($result) > 0): ?>
        <?= renderTable($result) ?>
    <?php else: ?>
        <p style="background: #fadbd8; padding: 15px; border-radius: 5px; color: #900;">Книг по вашему запросу не найдено.</p>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="stats">
        <p><strong>Количество найденных позиций:</strong> <?= count($result) ?></p>
        <p><strong>Общая стоимость товарного запаса (по текущей выборке):</strong> <?= number_format(inventoryValue($result), 2, ".", " ") ?> тг</p>
    </div>

</body>
</html>