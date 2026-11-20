<?php
declare(strict_types=1);

/**
 * Автоматизированный тестовый прогон модуля каталога и оформления заявок.
 * Запуск: php tests/run_tests.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Запуск разрешён только из CLI.');
}

// Изолированная тестовая БД SQLite в памяти (структура соответствует MySQL schema.sql)
putenv('TECHSERVICE_DB_DSN=sqlite::memory:');

if (session_status() === PHP_SESSION_NONE) {
    $_SESSION = [];
}

require_once dirname(__DIR__) . '/config/app.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogModel.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogController.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestModel.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestValidator.php';

$passed = 0;
$failed = 0;

function assertTrue(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
    } else {
        $failed++;
        echo "[FAIL] {$label}\n";
    }
}

function assertEquals(mixed $expected, mixed $actual, string $label): void
{
    assertTrue($expected === $actual, $label . ' (ожидалось: ' . var_export($expected, true) . ', получено: ' . var_export($actual, true) . ')');
}

// -----------------------------------------------------------------------------
// Инициализация схемы и тестовых данных
// -----------------------------------------------------------------------------
$pdo = getDatabaseConnection();
$pdo->exec('
    CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        code TEXT NOT NULL UNIQUE,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        short_description TEXT NOT NULL,
        full_description TEXT NOT NULL,
        image_path TEXT NOT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
    );
    CREATE TABLE requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service_id INTEGER NOT NULL,
        client_name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL,
        equipment_type TEXT NOT NULL,
        problem_description TEXT NOT NULL,
        submission_token TEXT NOT NULL UNIQUE,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT ON UPDATE CASCADE
    );
');

// Подгружаем демонстрационные INSERT из database/seed.sql (адаптируя MySQL-специфичные директивы)
$seedSql = file_get_contents(TECHSERVICE_ROOT . '/database/seed.sql');
assertTrue(is_string($seedSql) && $seedSql !== '', 'Чтение database/seed.sql');
$seedSql = preg_replace('/^USE\s+[^;]+;/mi', '', (string) $seedSql);
$seedSql = preg_replace('/ON\s+DUPLICATE\s+KEY\s+UPDATE[\s\S]*?(?=;)/mi', '', (string) $seedSql);
$pdo->exec((string) $seedSql);

// -----------------------------------------------------------------------------
// Тесты каталога
// -----------------------------------------------------------------------------
$catalogModel = new CatalogModel($pdo);
$catalogController = new CatalogController($pdo);

$categories = $catalogModel->getCategories();
assertEquals(4, count($categories), 'Каталог содержит 4 категории');

$allServices = $catalogModel->getServices();
assertEquals(8, count($allServices), 'Каталог содержит 8 активных услуг');

$opticsServices = $catalogModel->getServices('optics-service');
assertEquals(2, count($opticsServices), 'Фильтрация по категории optics-service возвращает 2 услуги');

$searchUpper = $catalogModel->getServices(null, 'ЮСТИРОВКА');
assertEquals(1, count($searchUpper), 'Регистронезависимый поиск на кириллице находит услугу');
assertEquals('OPT-01', $searchUpper[0]['code'] ?? '', 'Найденная услуга имеет код OPT-01');

$searchWildcard = $catalogModel->getServices(null, '100%');
assertEquals(0, count($searchWildcard), 'Экранирование спецсимвола % в поиске работает корректно');

$grouped = $catalogModel->getServicesGroupedByCategory();
assertEquals(4, count($grouped), 'Группировка услуг по категориям для формы заявки возвращает 4 группы');

$ctrlInvalidCat = $catalogController->handle(['category' => 'non-existent', 'q' => '  плат  ']);
assertEquals('all', $ctrlInvalidCat['activeCategory'], 'Несуществующая категория сбрасывается в all');
assertEquals('плат', $ctrlInvalidCat['searchQuery'], 'Поисковый запрос обрезается от пробелов');
assertTrue($ctrlInvalidCat['totalServices'] >= 1, 'Поиск по слову "плат" находит услуги');

// -----------------------------------------------------------------------------
// Тесты валидации формы заявки
// -----------------------------------------------------------------------------
$validator = new RequestValidator($catalogModel);

$emptyResult = $validator->validate([]);
assertTrue(!$emptyResult['valid'], 'Пустая форма не проходит валидацию');
assertEquals(6, count($emptyResult['errors']), 'Пустая форма возвращает ошибки по всем 6 полям');

$badContactsResult = $validator->validate([
    'client_name' => 'Иван 123',
    'phone' => '12345',
    'email' => 'invalid-email',
    'equipment_type' => 'А',
    'service_id' => '9999',
    'problem_description' => 'Коротко',
]);
assertTrue(!$badContactsResult['valid'], 'Некорректные данные не проходят валидацию');
assertTrue(isset($badContactsResult['errors']['client_name']), 'Ошибка валидации имени с цифрами');
assertTrue(isset($badContactsResult['errors']['phone']), 'Ошибка валидации короткого телефона');
assertTrue(isset($badContactsResult['errors']['email']), 'Ошибка валидации некорректного email');
assertTrue(isset($badContactsResult['errors']['equipment_type']), 'Ошибка валидации слишком короткого типа оборудования');
assertTrue(isset($badContactsResult['errors']['service_id']), 'Ошибка валидации несуществующей услуги');
assertTrue(isset($badContactsResult['errors']['problem_description']), 'Ошибка валидации слишком короткого описания');

$validInput = [
    'client_name' => '  Анна   Петрова  ',
    'phone' => '+7 (917) 555-12-34',
    'email' => 'anna.petrova@example.org',
    'equipment_type' => 'Оптический теодолит',
    'service_id' => (string) $allServices[0]['id'],
    'problem_description' => 'Нарушена фокусировка объектива после транспортировки прибора.',
];
$validResult = $validator->validate($validInput);
assertTrue($validResult['valid'], 'Корректные данные успешно проходят валидацию');
assertEquals('Анна Петрова', $validResult['data']['client_name'], 'Нормализация пробелов в имени');

// -----------------------------------------------------------------------------
// Тесты сохранения заявки и защиты от повторной отправки / XSS / CSRF
// -----------------------------------------------------------------------------
$requestModel = new RequestModel($pdo);
$subToken = issue_submission_token();
assertTrue(isset($_SESSION['submission_tokens'][$subToken]), 'Одноразовый токен отправки сохранён в сессии');
assertTrue(consume_submission_token($subToken), 'Одноразовый токен успешно погашен при первой проверке');
assertTrue(!consume_submission_token($subToken), 'Повторное погашение того же токена заблокировано');

$newRequestId = $requestModel->createRequest($validResult['data'], $subToken);
assertTrue($newRequestId > 0, 'Заявка сохранена в БД и получила ID');

$savedSummary = $requestModel->getRequestSummaryById($newRequestId);
assertTrue($savedSummary !== null, 'Сохранённая заявка читается из БД');
assertEquals('Анна Петрова', $savedSummary['client_name'] ?? '', 'Имя клиента в БД совпадает');
assertEquals('Оптический теодолит', $savedSummary['equipment_type'] ?? '', 'Тип оборудования в БД совпадает');

$duplicateInsertFailed = false;
try {
    $requestModel->createRequest($validResult['data'], $subToken);
} catch (PDOException $e) {
    $duplicateInsertFailed = true;
}
assertTrue($duplicateInsertFailed, 'Уникальный индекс БД блокирует повторную вставку с тем же submission_token');

$csrf = csrf_token();
assertTrue(verify_csrf_token($csrf), 'Проверка корректного CSRF-токена');
assertTrue(!verify_csrf_token('wrong-token'), 'Отклонение некорректного CSRF-токена');

assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'), 'Экранирование XSS при выводе HTML');

echo "\nИтого: пройдено {$passed}, ошибок {$failed}\n";
exit($failed > 0 ? 1 : 0);
