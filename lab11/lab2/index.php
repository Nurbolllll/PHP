<?php
declare(strict_types=1);

// --- ИЗОЛИРОВАННЫЙ ОБРАБОТЧИК API ДЛЯ ИНТЕРАКТИВНОГО ВАРИАНТА 4 ---
if (isset($_GET['action']) && $_GET['action'] === 'calculate_rect') {
    header('Content-Type: application/json');
    $length = (float)($_POST['length'] ?? 0);
    $width = (float)($_POST['width'] ?? 0);

    if ($length <= 0 || $width <= 0) {
        echo json_encode(['success' => false, 'error' => 'Размеры должны быть больше нуля.']);
        exit;
    }

    // Расчеты по Варианту 4
    $area = $length * $width;
    $perimeter = 2 * ($length + $width);

    // Строгое сравнение (например, проверка на квадрат)
    $isSquare = ($length === $width);

    echo json_encode([
        'success' => true,
        'area' => number_format($area, 2, '.', ' '),
        'perimeter' => number_format($perimeter, 2, '.', ' '),
        'isSquare' => $isSquare ? 'Да (квадрат)' : 'Нет (разносторонний)'
    ]);
    exit;
}

// === ЗАДАНЫЕ 2: ДАННЫЕ СТУДЕНТА ===
$studentName = "Алия Садыкова";
$course = 3;
$program = "Информационные системы";
$averageGrade = 85.75;
$hasDebt = false;
$debtText = $hasDebt ? "Есть задолженность" : "Задолженностей нет";

// === ЗАДАНИЕ 4 & 5: РАСЧЕТ ИТОГОВОЙ ОЦЕНКИ И КОНСТАНТА ===
const PASSING_SCORE = 50;
$laboratoryGrade = 85;
$independentGrade = 78;
$examGrade = 92;

$resultScore = $laboratoryGrade * 0.30 + $independentGrade * 0.20 + $examGrade * 0.50;
$statusText = $resultScore >= PASSING_SCORE ? "Дисциплина освоена" : "Дисциплина не освоена";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №2 — Типы данных и выражения</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --text: #1e293b;
            --primary: #3b82f6;
            --border: #e2e8f0;
        }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: var(--bg); color: var(--text); margin: 0; padding: 30px; }
        .grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
        .card { background: var(--surface); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid var(--border); }
        .full-width { grid-column: 1 / -1; }
        h2 { margin-top: 0; padding-bottom: 8px; border-bottom: 2px solid var(--border); font-size: 1.3rem; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px 14px; border: 1px solid var(--border); text-align: left; }
        th { background: #f8fafc; font-weight: 600; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; background: var(--primary); color: white; border: none; padding: 10px; font-weight: 600; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        .res-box { margin-top: 15px; padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; display: none; }
    </style>
</head>
<body>

<div class="grid">

    <!-- КАРТОЧКА 1: ИНФОРМАЦИЯ О СТУДЕНТЕ (ЗАДАНИЯ 2, 4, 5) -->
    <div class="card">
        <h2>👤 Карточка студента и успеваемость</h2>
        <p><strong>ФИО:</strong> <?= htmlspecialchars($studentName) ?></p>
        <p><strong>Курс:</strong> <?= $course ?> (Тип: <?= gettype($course) ?>)</p>
        <p><strong>ОП:</strong> <?= htmlspecialchars($program) ?></p>
        <p><strong>Статус задолженности:</strong> <?= $debtText ?></p>
        <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
        <p><strong>Итог успеваемости (Формула):</strong></p>
        <p>Балл: <strong><?= number_format($resultScore, 2, '.', ' ') ?></strong> из 100 (Константа прохода: <?= PASSING_SCORE ?>)</p>
        <p>Статус дисциплины: <span style="color: green; font-weight: bold;"><?= $statusText ?></span></p>
    </div>

    <!-- КАРТОЧКА 2: ИНДИВИДУАЛЬНЫЙ ВАРИАНТ №4 (ДИНАМИЧЕСКИЙ ГЕОМЕТРИЧЕСКИЙ СЧЕТ) -->
    <div class="card">
        <h2>📐 Вариант 4: Расчет прямоугольника</h2>
        <form id="rectForm" onsubmit="calculateRectangle(event)">
            <div class="form-group">
                <label>Длина (см):</label>
                <input type="number" id="length" step="0.01" value="15.4" required>
            </div>
            <div class="form-group">
                <label>Ширина (см):</label>
                <input type="number" id="width" step="0.01" value="8.2" required>
            </div>
            <button type="submit">Вычислить площадь и периметр</button>
        </form>

        <div id="rectResult" class="res-box"></div>
    </div>

    <!-- КАРТОЧКА 3: ТАБЛИЦА СРАВНЕНИЯ ЗНАЧЕНИЙ (ЗАДАНИЕ 7 — ДЛЯ ОТЧЕТА) -->
    <div class="card full-width">
        <h2>📋 Задание 7: Матрица нестрогого (==) и строгого (===) сравнения типов</h2>
        <table>
            <thead>
                <tr>
                    <th>Выражение</th>
                    <th>Результат PHP</th>
                    <th>Объяснение влияния типа данных</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>"10" == 10</code></td>
                    <td><strong><?php var_export("10" == 10); ?></strong></td>
                    <td>Нестрогое сравнение. Строка "10" приводится к числу 10 перед сопоставлением.</td>
                </tr>
                <tr>
                    <td><code>"10" === 10</code></td>
                    <td><strong><?php var_export("10" === 10); ?></strong></td>
                    <td>Строгое сравнение. Типы различаются (string и int), поэтому результат ложен.</td>
                </tr>
                <tr>
                    <td><code>0 == false</code></td>
                    <td><strong><?php var_export(0 == false); ?></strong></td>
                    <td>Нестрогое сравнение. Число 0 приводится к логическому false.</td>
                </tr>
                <tr>
                    <td><code>0 === false</code></td>
                    <td><strong><?php var_export(0 === false); ?></strong></td>
                    <td>Строгое сравнение. Целое число int(0) не эквивалентно типу bool(false).</td>
                </tr>
                <tr>
                    <td><code>null == false</code></td>
                    <td><strong><?php var_export(null == false); ?></strong></td>
                    <td>Нестрогое сравнение. Пустое значение null приводится к пустому bool значению.</td>
                </tr>
                <tr>
                    <td><code>null === false</code></td>
                    <td><strong><?php var_export(null === false); ?></strong></td>
                    <td>Строгое сравнение. Тип null не совпадает с типом boolean.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
function calculateRectangle(event) {
    event.preventDefault();
    const box = document.getElementById('rectResult');
    
    const data = new FormData();
    data.append('length', document.getElementById('length').value);
    data.append('width', document.getElementById('width').value);

    fetch('index.php?action=calculate_rect', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        box.style.display = 'block';
        if(res.success) {
            box.style.background = '#f0fdf4';
            box.style.borderColor = '#bbf7d0';
            box.innerHTML = `<strong>Площадь (S):</strong> ${res.area} кв. см<br>
                             <strong>Периметр (P):</strong> ${res.perimeter} см<br>
                             <strong>Является квадратом:</strong> ${res.isSquare}`;
        } else {
            box.style.background = '#fef2f2';
            box.style.borderColor = '#fecaca';
            box.innerHTML = `<strong>Ошибка:</strong> ${res.error}`;
        }
    });
}
// Вызываем один раз при загрузке для дефолтных значений
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('rectForm').dispatchEvent(new Event('submit'));
});
</script>

</body>
</html>
