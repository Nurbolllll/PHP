<?php
declare(strict_types=1);

/**
 * Безопасное экранирование для HTML-вывода
 */
function h(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Безопасное извлечение строкового поля из $_POST
 */
function postString(string $key): ?string
{
    $value = $_POST[$key] ?? null;
    return is_string($value) ? $value : null;
}

// Разрешенный список для способа уведомления
$allowedNotifications = ['email', 'sms', 'push'];
$notificationLabels = [
    'email' => 'Электронная почта',
    'sms' => 'SMS-уведомление',
    'push' => 'Push-уведомление в приложении'
];

// Инициализация массивов значений и ошибок
$values = [
    'reader' => '',
    'email' => '',
    'isbn' => '',
    'duration' => '',
    'notification' => '',
];
$errors = [];
$success = false;

// Обработка POST-запроса
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $reader = postString('reader');
    $email = postString('email');
    $isbn = postString('isbn');
    $duration = postString('duration');
    $notification = postString('notification');

    // Нормализация (удаление лишних пробелов)
    $values['reader'] = $reader === null ? '' : trim($reader);
    $values['email'] = $email === null ? '' : trim($email);
    $values['isbn'] = $isbn === null ? '' : trim($isbn);
    $values['duration'] = $duration === null ? '' : trim($duration);
    $values['notification'] = $notification ?? '';

    // 1. Валидация читателя (ФИО)
    if ($reader === null || $values['reader'] === '') {
        $errors['reader'] = 'Укажите имя читателя.';
    } elseif (mb_strlen($values['reader']) > 100) {
        $errors['reader'] = 'Имя не должно превышать 100 символов.';
    }

    // 2. Валидация e-mail
    if ($email === null || $values['email'] === '') {
        $errors['email'] = 'Укажите электронную почту.';
    } elseif (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Введите корректный адрес электронной почты.';
    }

    // 3. Валидация ISBN (ISBN-13 должен содержать ровно 13 цифр)
    if ($isbn === null || $values['isbn'] === '') {
        $errors['isbn'] = 'Укажите ISBN книги.';
    } elseif (!preg_match('/^\d{13}$/', $values['isbn'])) {
        $errors['isbn'] = 'ISBN-13 должен содержать ровно 13 цифр.';
    }

    // 4. Валидация срока резервирования (от 1 до 14 дней)
    if ($duration === null || $values['duration'] === '') {
        $errors['duration'] = 'Укажите срок резервирования.';
    } elseif (!filter_var($values['duration'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 14]])) {
        $errors['duration'] = 'Срок резервирования должен быть от 1 до 14 дней.';
    }

    // 5. Валидация способа уведомления (allow-list)
    if (!in_array($values['notification'], $allowedNotifications, true)) {
        $errors['notification'] = 'Выберите корректный способ уведомления из списка.';
    }

    $success = $errors === [];
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Резервирование книги</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --error: #ef4444;
            --success: #10b981;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --border: #cbd5e1;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            max-width: 550px;
            margin: 50px auto;
            padding: 0 20px;
            line-height: 1.6;
        }

        .card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 24px;
            color: var(--text);
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 18px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            display: block;
            width: 100%;
            padding: 10px 14px;
            margin-top: 6px;
            box-sizing: border-box;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .error {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            font-weight: 500;
        }

        .success-box {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        button {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s active;
            margin-top: 10px;
        }

        button:hover {
            background: var(--primary-hover);
        }

        button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
<div class="card">
    <main>
        <h1>Резервирование книги</h1>

        <?php if ($success): ?>
            <div class="success-box">
                <strong>Заявка успешно оформлена!</strong>
                <p>Читатель: <?= h($values['reader']) ?><br>Срок: <?= h($values['duration']) ?> дн.</p>
            </div>
        <?php else: ?>
            <form method="post" action="">
                <label>
                    Читатель (ФИО)
                    <input type="text" name="reader" maxlength="100" value="<?= h($values['reader']) ?>" required>
                    <?php if (isset($errors['reader'])): ?>
                        <div class="error"><?= h($errors['reader']) ?></div>
                    <?php endif; ?>
                </label>

                <label>
                    Электронная почта (E-mail)
                    <input type="email" name="email" value="<?= h($values['email']) ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error"><?= h($errors['email']) ?></div>
                    <?php endif; ?>
                </label>

                <label>
                    ISBN книги (13 цифр)
                    <input type="text" name="isbn" maxlength="13" placeholder="9785171234567" value="<?= h($values['isbn']) ?>" required>
                    <?php if (isset($errors['isbn'])): ?>
                        <div class="error"><?= h($errors['isbn']) ?></div>
                    <?php endif; ?>
                </label>

                <label>
                    Срок резервирования (в днях, от 1 до 14)
                    <input type="number" name="duration" min="1" max="14" value="<?= h($values['duration']) ?>" required>
                    <?php if (isset($errors['duration'])): ?>
                        <div class="error"><?= h($errors['duration']) ?></div>
                    <?php endif; ?>
                </label>

                <label>
                    Способ уведомления
                    <select name="notification" required>
                        <option value="">Выберите способ уведомления</option>
                        <?php foreach ($allowedNotifications as $item): ?>
                            <option value="<?= h($item) ?>" <?= $values['notification'] === $item ? 'selected' : '' ?>>
                                <?= h($notificationLabels[$item]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['notification'])): ?>
                        <div class="error"><?= h($errors['notification']) ?></div>
                    <?php endif; ?>
                </label>

                <button type="submit">Зарезервировать</button>
            </form>
        <?php endif; ?>
    </main>
</div>
</body>
</html>