<?php
declare(strict_types=1);

require_once 'functions.php';

// --- ИЗОЛИРОВАННЫЙ ОБРАБОТЧИК API (ДЛЯ ДИНАМИЧЕСКИХ ЗАПРОСОВ) ---
if (isset($_GET['action']) && $_GET['action'] === 'calculate_api') {
    header('Content-Type: application/json');
    try {
        $weight = (float)($_POST['weight'] ?? 0);
        $zone = (int)($_POST['zone'] ?? 0);
        $type = (string)($_POST['type'] ?? 'standard');
        $promo = !empty($_POST['promo']) ? (string)$_POST['promo'] : null;

        // Вызываем наши PHP-функции из лабораторной
        validatePackageData($weight, $zone);
        
        $baseRates = [1 => 1.0, 2 => 1.2, 3 => 1.5, 4 => 2.0];
        $zoneCalc = createZoneCalculator($baseRates);
        
        $base = getBasePrice($weight);
        $zonedBase = $zoneCalc($base, $zone);
        $surcharge = calculateSurcharge($type);
        $total = $zonedBase + $surcharge;
        
        $report = formatDeliveryReport("ONLINE", $total, $promo);

        echo json_encode([
            'success' => true,
            'total' => $total,
            'report' => $report
        ]);
    } catch (\Throwable $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'error_type' => (new \ReflectionClass($e))->getShortName()
        ]);
    }
    exit;
}

// --- БАЗОВЫЙ НАБОР ДАННЫХ ДЛЯ ТАБЛИЦЫ ---
$packages = [
    ['id' => 'A101', 'weight' => 2.5,  'zone' => 1, 'type' => 'standard', 'promo' => 'FIRST10'],
    ['id' => 'B202', 'weight' => 12.0, 'zone' => 3, 'type' => 'fragile',  'promo' => null],
    ['id' => 'C303', 'weight' => 55.0, 'zone' => 2, 'type' => 'oversized', 'promo' => 'BIGDELIVERY'],
    ['id' => 'D404', 'weight' => 0.5,  'zone' => 4, 'type' => 'standard', 'promo' => null],
    ['id' => 'E505', 'weight' => 95.0, 'zone' => 1, 'type' => 'oversized', 'promo' => null],
];

$zoneRates = [1 => 1.0, 2 => 1.2, 3 => 1.5, 4 => 2.0];
$applyZoneModifier = createZoneCalculator($zoneRates);
$heavyPackages = array_filter($packages, fn(array $p): bool => $p['weight'] > 10.0);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивная служба доставки — Лабораторная 4</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --border: #e2e8f0;
            --success: #16a34a;
            --error: #dc2626;
        }

        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            margin: 0; 
            padding: 40px 20px; 
            background-color: var(--background); 
            color: var(--text);
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
        }

        .card { 
            background: var(--surface); 
            padding: 30px; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
            transition: transform 0.2s;
        }

        .card:hover { transform: translateY(-2px); }

        .full-width { grid-column: 1 / -1; }

        h2 { margin-top: 0; color: var(--text); font-size: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 10px; }

        /* Таблицы */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background-color: #f1f5f9; color: #475569; font-weight: 600; }
        tr:hover { background-color: #f8fafc; }

        /* Форма инпутов */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem; }
        input, select { 
            width: 100%; 
            padding: 10px 14px; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        button {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: var(--primary-hover); }

        /* Виджет результата */
        .result-box { 
            margin-top: 20px; 
            padding: 15px; 
            border-radius: 8px; 
            display: none; 
            font-weight: 500;
            animation: fadeIn 0.3s ease-in-out;
        }
        .result-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--success); }
        .result-error { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }

        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ЛЕВАЯ ПАНЕЛЬ: ДИНАМИЧЕСКИЙ ОНЛАЙН КАЛЬКУЛЯТОР (AJAX + ИСКЛЮЧЕНИЯ) -->
    <div class="card">
        <h2>⚡ Интерактивный расчет стоимости</h2>
        <form id="calcForm" onsubmit="calculateOnline(event)">
            <div class="form-group">
                <label for="weight">Масса посылки (кг):</label>
                <input type="number" id="weight" step="0.01" value="5.0" required>
            </div>
            
            <div class="form-group">
                <label for="zone">Тарифная зона доставки:</label>
                <select id="zone">
                    <option value="1">Зона 1 (Локальная)</option>
                    <option value="2">Зона 2 (Региональная)</option>
                    <option value="3">Зона 3 (Междугородняя)</option>
                    <option value="4">Зона 4 (Международная)</option>
                    <option value="5">Зона 5 (Ошибочная тест-зона)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="type">Категория отправления:</label>
                <select id="type">
                    <option value="standard">Стандартная посылка</option>
                    <option value="fragile">Хрупкий груз (+1500 тг надбавка)</option>
                    <option value="oversized">Крупногабаритный (+2500 тг надбавка)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="promo">Промокод (необязательно):</label>
                <input type="text" id="promo" placeholder="Например: NEW2026">
            </div>

            <button type="submit">Рассчитать стоимость мгновенно</button>
        </form>

        <div id="resultWidget" class="result-box"></div>
    </div>

    <!-- ПРАВАЯ ПАНЕЛЬ: ФИЛЬТРАЦИЯ С ПОМОЩЬЮ CALLBACK -->
    <div class="card">
        <h2>📊 Аналитический срез данных</h2>
        <p>Отфильтровано тяжелых посылок (масса <strong>&gt; 10 кг</strong>) с помощью функции <code>array_filter()</code> и стрелочного callback:</p>
        <ul style="padding-left: 20px; line-height: 1.6;">
            <?php foreach ($heavyPackages as $hp): ?>
                <li style="margin-bottom: 8px;">
                    Посылка <strong style="color: var(--primary);"><?= htmlspecialchars($hp['id']) ?></strong> 
                    &mdash; Вес: <strong><?= $p_weight = $hp['weight'] ?> кг</strong> (Зона <?= $hp['zone'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- ТАБЛИЦА ВСЕХ ЗАПИСЕЙ -->
    <div class="card full-width">
        <h2>📋 Реестр плановых отправлений (Статический расчет)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Масса</th>
                    <th>Зона</th>
                    <th>Категория</th>
                    <th>Результат выполнения цепочки чистых функций</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $p): ?>
                    <?php
                    validatePackageData($p['weight'], $p['zone']);
                    $base = getBasePrice($p['weight']);
                    $zonedBase = $applyZoneModifier($base, $p['zone']);
                    $surcharge = calculateSurcharge($p['type']);
                    $total = $zonedBase + $surcharge;
                    $report = formatDeliveryReport($p['id'], $total, $p['promo']);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['id']) ?></strong></td>
                        <td><?= $p['weight'] ?> кг</td>
                        <td>Зона <?= $p['zone'] ?></td>
                        <td><?= htmlspecialchars($p['type']) ?></td>
                        <td><small><?= htmlspecialchars($report) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function calculateOnline(event) {
    event.preventDefault();
    
    const widget = document.getElementById('resultWidget');
    widget.style.display = 'none';

    // Формируем реактивные данные формы
    const formData = new FormData();
    formData.append('weight', document.getElementById('weight').value);
    formData.append('zone', document.getElementById('zone').value);
    formData.append('type', document.getElementById('type').value);
