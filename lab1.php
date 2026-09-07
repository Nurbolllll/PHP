<?php
// Константы
const UNIVERSITY = "Алматинский технологический университет";
const DISCIPLINE = "Программирование на PHP";

// Данные студента
$studentName = "Фамилия Имя"; 
$group = "ИС-23-22";
$course = 3;
$variant = "7 - Стоимость поездки";

// Данные по умолчанию
$distance = isset($_POST['distance']) ? (float)$_POST['distance'] : 350;
$fuelConsumption = isset($_POST['fuelConsumption']) ? (float)$_POST['fuelConsumption'] : 8.5;
$fuelType = isset($_POST['fuelType']) ? $_POST['fuelType'] : '92';
$passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;

// Цены за литр по типу топлива
$fuelPrices = [
    '92' => 205,
    '95' => 255,
    'diesel' => 295
];

$fuelPrice = $fuelPrices[$fuelType] ?? 205;

// Расчеты
$totalFuel = ($distance * $fuelConsumption) / 100;
$result = $totalFuel * $fuelPrice;
$pricePerPerson = $passengers > 0 ? $result / $passengers : $result;

// Статус
if ($result <= 10000) {
    $status = "Поездка выгодная (в пределах бюджета)";
    $statusClass = "success";
} else {
    $status = "Высокие расходы на поездку";
    $statusClass = "warning";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №1 — Вариант 7</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background: #f4f6f8; }
        .card { padding: 25px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .info-block { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background-color: #0056b3; }
        .results-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .warning { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= UNIVERSITY ?></h1>
        <h2><?= DISCIPLINE ?></h2>
        
        <div class="info-block">
            <p><strong>Студент:</strong> <?= $studentName ?></p>
            <p><strong>Группа:</strong> <?= $group ?> | <strong>Курс:</strong> <?= $course ?> | <strong>Вариант:</strong> <?= $variant ?></p>
        </div>

        <div class="info-block">
            <h3>Интерактивный расчет поездки</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="distance">Расстояние: <span id="distVal"><?= $distance ?></span> км</label>
                    <input type="range" min="10" max="2000" step="10" id="distance" name="distance" value="<?= $distance ?>" oninput="document.getElementById('distVal').innerText = this.value">
                </div>

                <div class="form-group">
                    <label for="fuelConsumption">Расход топлива: <?= $fuelConsumption ?> л / 100 км</label>
                    <input type="number" step="0.1" name="fuelConsumption" value="<?= $fuelConsumption ?>" required>
                </div>

                <div class="form-group">
                    <label for="fuelType">Тип топлива:</label>
                    <select name="fuelType" id="fuelType">
                        <option value="92" <?= $fuelType == '92' ? 'selected' : '' ?>>АИ-92 (205 ₸/л)</option>
                        <option value="95" <?= $fuelType == '95' ? 'selected' : '' ?>>АИ-95 (255 ₸/л)</option>
                        <option value="diesel" <?= $fuelType == 'diesel' ? 'selected' : '' ?>>Дизель (295 ₸/л)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="passengers">Количество пассажиров:</label>
                    <input type="number" min="1" max="10" name="passengers" value="<?= $passengers ?>" required>
                </div>

                <button type="submit">Рассчитать стоимость</button>
            </form>
        </div>

        <div class="info-block">
            <h3>Результат расчета</h3>
            <div class="results-grid">
                <div><strong>Необходимо топлива:</strong> <?= round($totalFuel, 2) ?> л</div>
                <div><strong>Итоговая стоимость:</strong> <?= round($result, 2) ?> ₸</div>
                <div><strong>С пассажира:</strong> <?= round($pricePerPerson, 2) ?> ₸</div>
                <div><strong>Статус:</strong> <span class="<?= $statusClass ?>"><?= $status ?></span></div>
            </div>
        </div>

        <p><small>Дата формирования: <?= date("d.m.Y H:i") ?></small></p>
    </div>
</body>
</html>