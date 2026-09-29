<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';

function runTests(): void {
    $testsPassed = 0;
    $totalTests = 0;

    // Тест 1: Нормализация текста
    $totalTests++;
    if (normalizeText("  Книга   ПРО  ") === "книга про") {
        $testsPassed++;
        echo "Тест 1 (Нормализация): ПРОШЕЛ<br>";
    } else {
        echo "Тест 1 (Нормализация): ПРОВАЛЕН<br>";
    }

    // Тест 2: Исключение при отрицательной цене
    $totalTests++;
    try {
        validateProduct([
            "id" => 99,
            "name" => "Тест",
            "category" => "Тест",
            "price" => -100.0,
            "stock" => 5,
            "created_at" => "2026-09-01",
            "author" => "Автор",
            "genre" => "Жанр"
        ]);
        echo "Тест 2 (Отрицательная цена): ПРОВАЛЕН (исключение не вызвано)<br>";
    } catch (InvalidArgumentException $e) {
        $testsPassed++;
        echo "Тест 2 (Отрицательная цена): ПРОШЕЛ<br>";
    }

    // Тест 3: Расчет стоимости запасов
    $totalTests++;
    $sample = [
        ["price" => 1000.0, "stock" => 2],
        ["price" => 500.0, "stock" => 4]
    ];
    if (inventoryValue($sample) === 4000.0) {
        $testsPassed++;
        echo "Тест 3 (inventoryValue): ПРОШЕЛ<br>";
    } else {
        echo "Тест 3 (inventoryValue): ПРОВАЛЕН<br>";
    }

    // Тест 4: Экранирование HTML
    $totalTests++;
    if (e("<script>alert('xss')</script>") === "&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;") {
        $testsPassed++;
        echo "Тест 4 (Экранирование HTML): ПРОШЕЛ<br>";
    } else {
        echo "Тест 4 (Экранирование HTML): ПРОВАЛЕН<br>";
    }

    echo "<h3>Успешно пройдено тестов: $testsPassed из $totalTests</h3>";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тесты (Вариант №5)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
    </style>
</head>
<body>
    <h1>Результаты тестирования</h1>
    <?php runTests(); ?>
</body>
</html>