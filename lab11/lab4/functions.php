<?php
declare(strict_types=1);

/**
 * 1. Функция валидации параметров посылки
 * Централизованно проверяет диапазоны значений.
 */
function validatePackageData(float $weight, int $zone): void
{
    if ($weight <= 0 || $weight > 100) {
        throw new InvalidArgumentException("Масса посылки должна быть от 0.01 до 100 кг.");
    }
    if ($zone < 1 || $zone > 4) {
        throw new OutOfRangeException("Допустимые тарифные зоны: от 1 до 4.");
    }
}

/**
 * 2. Функция расчета базовой стоимости доставки
 * Использует значение по умолчанию для базового тарифа за кг.
 */
function getBasePrice(float $weight, float $ratePerKg = 300.0): float
{
    if ($ratePerKg <= 0) {
        throw new InvalidArgumentException("Тарифная ставка должна быть больше нуля.");
    }
    return round($weight * $ratePerKg, 2);
}

/**
 * 3. Функция расчета надбавок за хрупкость или габариты
 * Демонстрирует использование Union-типа (принимает число или null).
 */
function calculateSurcharge(string $type, int|float|null $customFee = null): float
{
    if ($customFee !== null && $customFee < 0) {
        throw new InvalidArgumentException("Дополнительная надбавка не может быть отрицательной.");
    }

    if ($customFee !== null) {
        return (float)$customFee;
    }

    return match ($type) {
        'fragile' => 1500.0, // Хрупкий груз
        'oversized' => 2500.0, // Крупногабаритный груз
        default => 0.0
    };
}

/**
 * 4. Функция формирования финального текстового отчета
 * Возвращает строго строку, чистая функция без echo.
 */
function formatDeliveryReport(string $id, float $totalPrice, ?string $promoCode = null): string
{
    if (trim($id) === "") {
        throw new InvalidArgumentException("Идентификатор посылки не может быть пустым.");
    }
    
    $baseText = "Посылка #{$id}: Итоговая стоимость доставки составляет {$totalPrice} тг.";
    return $promoCode === null ? $baseText : "{$baseText} (Применен промокод: {$promoCode})";
}

/**
 * 5. Замыкание для учета коэффициента зоны (создается через фабричную функцию)
 * Явно захватывает массив наценок через use.
 */
function createZoneCalculator(array $zoneModifiers): Closure
{
    return function (float $basePrice, int $zone) use ($zoneModifiers): float {
        if (!array_key_exists($zone, $zoneModifiers)) {
            throw new OutOfRangeException("Коэффициент для зоны {$zone} не определен.");
        }
        return round($basePrice * $zoneModifiers[$zone], 2);
    };
}
