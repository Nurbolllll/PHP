<?php
declare(strict_types=1);

require_once 'functions.php';

function runTestCase(int $id, string $description, callable $test): void
{
    try {
        $test();
        echo "<tr><td>{$id}</td><td>{$description}</td><td style='color:green;'>🟢 УСПЕХ (Выполнено)</td></tr>";
    } catch (Throwable $e) {
        echo "<tr><td>{$id}</td><td>{$description}</td><td style='color:red;'>🔴 ИСКЛЮЧЕНИЕ: " . $e::class . " ({$e->getMessage()})</td></tr>";
    }
}

echo "<h2>Результаты тестирования библиотеки (8 обязательных сценариев):</h2>";
echo "<table border='1' style='border-collapse:collapse; width:80%; text-align:left;'>";
echo "<tr style='background:#eee;'><th>№</th><th>Описание сценария</th><th>Статус / Результат</th></tr>";

// 1. Обычные корректные данные
runTestCase(1, "Расчет базовой цены при корректных параметрах", function() {
    $price = getBasePrice(10.0, 200.0);
    if ($price !== 2000.0) throw new Exception("Неверный расчет стоимости");
});

// 2. Нижняя допустимая граница
runTestCase(2, "Нижняя граница массы (0.01 кг)", function() {
    validatePackageData(0.01, 1);
});

// 3. Верхняя допустимая граница
runTestCase(3, "Верхняя граница зоны (Зона 4)", function() {
    validatePackageData(5.0, 4);
});

// 4. Значение ниже границы (Исключение)
runTestCase(4, "Валидация отрицательного веса (Ожидается исключение)", function() {
    try {
        validatePackageData(-5.5, 2);
        throw new Exception("Ошибка: Минусовой вес пропущен!");
    } catch (InvalidArgumentException $e) {
        // Ожидаемое кастомное поведение
    }
});

// 5. Значение выше границы (Исключение)
runTestCase(5, "Валидация зоны вне диапазона (Ожидается исключение)", function() {
    try {
        validatePackageData(10.0, 5);
        throw new Exception("Ошибка: Неверная зона 5 пропущена!");
    } catch (OutOfRangeException $e) {
        // Ожидаемое кастомное поведение
    }
});

// 6. Union-тип: передача числового значения вместо дефолтного
runTestCase(6, "Надбавка с передачей кастомного int/float числового значения", function() {
    $fee = calculateSurcharge('standard', 750);
    if ($fee !== 750.0) throw new Exception("Составной тип отработал некорректно");
});

// 7. Null в разрешенном параметре
runTestCase(7, "Передача null в nullable параметр отчета", function() {
    $res = formatDeliveryReport("A1", 500.0, null);
    if (str_contains($res, "Промокод")) throw new Exception("Null обработан неверно");
});

// 8. Проверка замыкания и захвата контекста через use
runTestCase(8, "Проверка работы фабрики замыканий тарифных зон", function() {
    $zoneCalculator = createZoneCalculator([2 => 1.5]);
    $result = $zoneCalculator(100.0, 2);
    if ($result !== 150.0) throw new Exception("Замыкание не применило коэффициент");
});

echo "</table>";
