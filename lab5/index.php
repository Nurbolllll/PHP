<?php
declare(strict_types=1);

require_once 'functions.php';
$products = require_once 'catalog.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа 5 - Каталог книг</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        table { margin-top: 10px; margin-bottom: 20px; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>

    <h2>Каталог книг (Вариант 5)</h2>

    <?php
    try {
        // Проверяем каждую запись каталога
        array_walk($products, fn(array $p) => validateProduct($p));

        echo "<h3>1. Все книги в каталоге:</h3>";
        echo renderTable($products);

        // Поиск по автору (например, «толстой») и фильтрация по жанру («Роман»)
        $result = searchByAuthor($products, "толстой");
        $result = filterCatalog($result, genre: "Роман", maxPrice: 6000.0, onlyAvailable: true);
        
        // Сортировка по названию
        $result = sortByName($result, true);

        echo "<h3>2. Результат поиска по автору, фильтрации и сортировки:</h3>";
        echo renderTable($result);

        // Расчет общей стоимости запасов
        $totalStockValue = inventoryValue($products);
        echo "<p><strong>Общая стоимость товарного запаса каталога:</strong> " . number_format($totalStockValue, 2, ".", " ") . " тг</p>";

    } catch (InvalidArgumentException $e) {
        echo "<p style='color: red;'><strong>Ошибка данных:</strong> " . e($e->getMessage()) . "</p>";
    }
    ?>

</body>
</html>