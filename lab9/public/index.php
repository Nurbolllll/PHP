<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Isbn, Student, Librarian, Book};

// Инициализируем переменные для сообщений
$error = null;
$success = null;
$loan = null;

// Значения по умолчанию для формы, чтобы не вводить заново
$studentName = $_POST['student_name'] ?? 'Грон Пожиратель Хинкалей';
$studentEmail = $_POST['student_email'] ?? 'gron.hinkali@magic.kz';
$isbnValue = $_POST['isbn'] ?? '9785171221111';
$bookTitle = $_POST['book_title'] ?? 'Объектно-ориентированный PHP';

// Создаем фиксированного библиотекаря для демонстрации полиморфизма
$librarian = new Librarian(1, 'Магистр Кринждолф Серый', 'cringedolf@magic.kz');
$student = null;
$book = null;

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Динамическое создание объектов и проверка инвариантов конструкторов
        $student = new Student(101, $studentName, $studentEmail);
        $book = new Book(new Isbn($isbnValue), $bookTitle);

        // 2. Выполнение бизнес-логики
        $now = new \DateTimeImmutable();
        $loan = $book->issueTo($student, $now);
        
        $success = "Операция выполнена успешно! Книга выдана.";
    } catch (\InvalidArgumentException $e) {
        // Ошибки валидации формата данных (конструкторы)
        $error = "Ошибка валидации (InvalidArgumentException): " . $e->getMessage();
    } catch (\DomainException $e) {
        // Ошибки нарушения бизнес-правил
        $error = "Нарушение бизнес-правила (DomainException): " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа 9 — Интерактивная Библиотека</title>
    <style>
        :root {
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #4b5563;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --student-badge: #dbeafe;
            --student-text: #1e40af;
            --lib-badge: #fef3c7;
            --lib-text: #92400e;
            --success-bg: #d1fae5;
            --success-text: #065f46;
            --error-bg: #fee2e2;
            --error-text: #991b1b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container { width: 100%; max-width: 800px; }
        h1 { font-size: 1.8rem; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
        h2 { font-size: 1.2rem; margin: 0 0 12px 0; color: var(--primary); }
        
        /* Стили формы */
        .form-panel { background: var(--card-bg); padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; }
        .form-group input { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; }
        .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn { grid-column: span 2; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: var(--primary-hover); }

        /* Стили вывода */
        .users-grid { display: flex; gap: 20px; margin-bottom: 24px; }
        .card { background: var(--card-bg); padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); flex: 1; border: 1px solid #e5e7eb; }
        .info-row { margin: 8px 0; font-size: 0.95rem; }
        .info-label { color: var(--text-muted); font-weight: 500; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .badge-student { background-color: var(--student-badge); color: var(--student-text); }
        .badge-librarian { background-color: var(--lib-badge); color: var(--lib-text); }

        /* Уведомления */
        .msg { padding: 15px 20px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid; font-size: 0.95rem; }
        .msg-success { background-color: var(--success-bg); color: var(--success-text); border-left-color: #10b981; }
        .msg-error { background-color: var(--error-bg); color: var(--error-text); border-left-color: #ef4444; }
    </style>
</head>
<body>

<div class="container">
    <h1>🧙‍♂️ Интерактивный симулятор ООП-модели</h1>

    <!-- Форма для динамического ввода данных -->
    <form method="POST" class="form-panel">
        <h2>Управление параметрами объектов</h2>
        <div class="form-grid">
            <div class="form-group">
                <label for="student_name">Имя студента фэнтези:</label>
                <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($studentName); ?>" required>
            </div>
            <div class="form-group">
                <label for="student_email">Email студента:</label>
                <input type="email" id="student_email" name="student_email" value="<?php echo htmlspecialchars($studentEmail); ?>" required>
            </div>
            <div class="form-group">
                <label for="book_title">Название магической книги:</label>
                <input type="text" id="book_title" name="book_title" value="<?php echo htmlspecialchars($bookTitle); ?>" required>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN книги (ровно 13 цифр):</label>
                <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($isbnValue); ?>" required>
            </div>
            <button type="submit" class="btn">✨ Создать объекты и вызвать бизнес-метод</button>
        </div>
    </form>

    <!-- Вывод ошибок или успехов бизнес-логики -->
    <?php if ($error): ?>
        <div class="msg msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success && $loan && $student && $book): ?>
        <div class="msg msg-success">
            <strong><?php echo htmlspecialchars($success); ?></strong><br>
            📖 Книга «<?php echo htmlspecialchars($book->title); ?>» (ISBN: <?php echo htmlspecialchars($book->isbn->value); ?>) успешно закреплена за читателем.<br>
            ⏳ Вернуть до: <strong><?php echo $loan->dueDate->format('Y-m-d H:i'); ?></strong> (Срок: 14 дней).
        </div>
    <?php endif; ?>

    <!-- Полиморфное отображение участников системы -->
    <h2>Карточки активных объектов (Полиморфизм)</h2>
    <div class="users-grid">
        <!-- Показываем карточку студента, только если он был успешно создан -->
        <div class="card">
            <h2><?php echo $student ? htmlspecialchars($student->fullName()) : 'Экземпляр не создан'; ?></h2>
            <div class="info-row">
                <span class="info-label">Роль:</span> 
                <span class="badge badge-student"><?php echo $student ? htmlspecialchars($student->roleLabel()) : 'Студент'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span> <?php echo $student ? htmlspecialchars($student->email) : '—'; ?>
            </div>
        </div>

        <!-- Карточка библиотекаря (всегда стабильна) -->
        <div class="card">
            <h2><?php echo htmlspecialchars($librarian->fullName()); ?></h2>
            <div class="info-row">
                <span class="info-label">Роль:</span> 
                <span class="badge badge-librarian"><?php echo htmlspecialchars($librarian->roleLabel()); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span> <?php echo htmlspecialchars($librarian->email); ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
