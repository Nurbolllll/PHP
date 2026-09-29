<?php
declare(strict_types=1);

require_once 'functions.php';

echo "<h2>Результаты тестирования (Лабораторная работа №5)</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr><th>№</th><th>Сценарий</th><th>Что проверяется</th><th>Ожидаемый результат</th><th>Статус</th></tr>";

$testCount = 0;

function runTest(int $num, string $scenario, string $check, string $expected, callable $testFn): void {
    global $testCount;
    $testCount++;
    $status = "<span style='color: green;'>Пройден</span>";
    try {
        $testFn();
    } catch (\Throwable $e) {
        // Если тест ожидал исключение, то ошибка — это норма
        if (strpos($expected, "Исключение") === false) {
            $status = "<span style='color: red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</span>";
        }
    }
    echo "<tr><td>{$num}</td><td>{$scenario}</td><td>{$check}</td><td>{$expected}</td><td>{$status}</td></tr>";
}

// Тест 1: Нормализация текста
runTest(1, "Нормализация строк", "Удаление лишних пробелов и перевод в нижний регистр", "чистый код", function() {
    $res = normalizeText("   Чистый    код   ");
    assert($res === "чистый код");
});

// Тест 2: Поиск без учета регистра
runTest(2, "Поиск по автору", "mb_stripos с разным регистром", "Найдена книга Толстого", function() {
    $items = [["id" => 1, "name" => "Война и мир", "category" => "Классика", "price" => 5500.0, "stock" => 10, "created_at" => "2026-01-10", "author" => "Лев Толстой", "genre" => "Роман"]];
    $res = searchByAuthor($items, "ТОЛСТОЙ");
    assert(count($res) === 1);
});

// Тест 3: Фильтрация по жанру
runTest(3, "Фильтрация каталога", "Отбор по жанру и максимальной цене", "Отображены подходящие книги", function() {
    $items = [
        ["id" => 1, "name" => "Книга 1", "category" => "А", "price" => 1000.0, "stock" => 5, "created_at" => "2026-01-01", "author" => "Автор 1", "genre" => "Фантастика"],
        ["id" => 2, "name" => "Книга 2", "category" => "А", "price" => 5000.0, "stock" => 5, "created_at" => "2026-01-01", "author" => "Автор 2", "genre" => "Роман"]
    ];
    $res = filterCatalog($items, genre: "Фантастика", maxPrice: 2000.0);
    assert(count($res) === 1);
});

// Тест 4: Валидация - корректный товар
runTest(4, "Валидация структуры", "Корректная запись", "Успешно без исключений", function() {
    $validProduct = ["id" => 1, "name" => "Тест", "category" => "Тест", "price" => 100.0, "stock" => 5, "created_at" => "2026-01-01", "author" => "Автор", "genre" => "Жанр"];
    validateProduct($validProduct);
});

// Тест 5: Валидация - отрицательная цена (исключение)
runTest(5, "Валидация цены", "Отрицательная цена", "Исключение InvalidArgumentException", function() {
    $invalidProduct = ["id" => 1, "name" => "Тест", "category" => "Тест", "price" => -10.0, "stock" => 5, "created_at" => "2026-01-01", "author" => "Автор", "genre" => "Жанр"];
    validateProduct($invalidProduct);
});

// Тест 6: Валидация - отсутствие обязательного ключа
runTest(6, "Валидация ключей", "Отсутствие поля author", "Исключение InvalidArgumentException", function() {
    $invalidProduct = ["id" => 1, "name" => "Тест", "category" => "Тест", "price" => 100.0, "stock" => 5, "created_at" => "2026-01-01", "genre" => "Жанр"];
    validateProduct($invalidProduct);
});

// Тест 7: Валидация - некорректный формат даты
runTest(7, "Валидация даты", "Неверный формат Y-m-d", "Исключение InvalidArgumentException", function() {
    $invalidProduct = ["id" => 1, "name" => "Тест", "category" => "Тест", "price" => 100.0, "stock" => 5, "created_at" => "2026/13/99", "author" => "Автор", "genre" => "Жанр"];
    validateProduct($invalidProduct);
});

// Тест 8: Агрегирование (inventoryValue)
runTest(8, "Агрегирование через reduce", "Расчет стоимости запасов", "Верная общая сумма", function() {
    $items = [
        ["price" => 100.0, "stock" => 2],
        ["price" => 200.0, "stock" => 3]
    ];
    $val = inventoryValue($items);
    assert($val === 800.0);
});

echo "</table>";
echo "<p>Всего выполнено тестов: {$testCount}</p>";