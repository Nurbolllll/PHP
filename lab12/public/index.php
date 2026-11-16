<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\ConnectionFactory;
use App\Repository\BookRepository;
use App\Validation\BookValidator;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Ошибка безопасности: недействительный CSRF-токен.');
    }
}

try {
    $pdo = ConnectionFactory::create();
    $repo = new BookRepository($pdo);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(503);
    exit('Сервис временно недоступен. Попробуйте позже.');
}

$action = $_GET['action'] ?? 'index';
$allowed = ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'issue'];

if (!in_array($action, $allowed, true)) {
    http_response_code(404);
    exit('Страница не найдена');
}

if (in_array($action, ['store', 'update', 'delete', 'issue'], true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Метод не разрешён');
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

switch ($action) {
    case 'index':
        $search = trim((string) ($_GET['search'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'title');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $books = $repo->findAll($search, $sort, $limit, $offset);
        require __DIR__ . '/../templates/books/index.php';
        break;

    case 'show':
        $book = $repo->find($id);
        if (!$book) {
            http_response_code(404);
            exit('Книга не найдена');
        }
        require __DIR__ . '/../templates/books/show.php';
        break;

    case 'create':
        $book = ['isbn' => '', 'title' => '', 'author' => '', 'copies' => 1];
        $errors = [];
        $formAction = '?action=store';
        require __DIR__ . '/../templates/books/form.php';
        break;

    case 'store':
        verifyCsrf();
        $validated = BookValidator::validate($_POST);
        $errors = $validated['errors'];
        $book = $validated['data'];

        if (empty($errors)) {
            try {
                $newId = $repo->create($book);
                header('Location: ?action=show&id=' . $newId, true, 303);
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    http_response_code(409);
                    $errors['isbn'] = 'Книга с таким ISBN уже существует.';
                } else {
                    error_log($e->getMessage());
                    http_response_code(500);
                    exit('Ошибка при сохранении данных.');
                }
            }
        } else {
            http_response_code(422);
        }
        $formAction = '?action=store';
        require __DIR__ . '/../templates/books/form.php';
        break;

    case 'edit':
        $book = $repo->find($id);
        if (!$book) {
            http_response_code(404);
            exit('Книга не найдена');
        }
        $errors = [];
        $formAction = '?action=update&id=' . $id;
        require __DIR__ . '/../templates/books/form.php';
        break;

    case 'update':
        verifyCsrf();
        if (!$repo->find($id)) {
            http_response_code(404);
            exit('Книга не найдена');
        }
        $validated = BookValidator::validate($_POST);
        $errors = $validated['errors'];
        $book = $validated['data'] + ['id' => $id];

        if (empty($errors)) {
            try {
                $repo->update($id, $validated['data']);
                header('Location: ?action=show&id=' . $id, true, 303);
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    http_response_code(409);
                    $errors['isbn'] = 'Конфликт: этот ISBN занят другой книгой.';
                } else {
                    error_log($e->getMessage());
                    http_response_code(500);
                    exit('Ошибка при обновлении.');
                }
            }
        } else {
            http_response_code(422);
        }
        $formAction = '?action=update&id=' . $id;
        require __DIR__ . '/../templates/books/form.php';
        break;

    case 'delete':
        verifyCsrf();
        if (!$repo->delete($id)) {
            http_response_code(404);
            exit('Книга не найдена');
        }
        header('Location: ?action=index', true, 303);
        exit;

    case 'issue':
        verifyCsrf();
        try {
            $repo->issueBookWithAudit($id);
            header('Location: ?action=show&id=' . $id, true, 303);
            exit;
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(400);
            exit('Не удалось выдать книгу (возможно, закончились экземпляры).');
        }
}
