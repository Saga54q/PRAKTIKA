<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogController.php';

$dbError = false;
$catalogData = [
    'categories' => [],
    'activeCategory' => 'all',
    'searchQuery' => '',
    'services' => [],
    'totalServices' => 0,
];

try {
    $pdo = getDatabaseConnection();
    $controller = new CatalogController($pdo);

    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        $controller->respondJson($_GET);
        exit;
    }

    $catalogData = $controller->handle($_GET);
} catch (Throwable $e) {
    error_log('Catalog page error: ' . $e->getMessage());
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error', 'message' => 'Временная ошибка при загрузке каталога.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $dbError = true;
}

$pageTitle = 'Каталог услуг';
$pageDescription = 'Каталог услуг ООО «Техсервис» по диагностике, ремонту и техническому обслуживанию электронного и оптического оборудования.';
$activePage = 'catalog';
$pageStyles = ['assets/css/catalog.css'];
$pageScripts = ['assets/js/catalog.js'];

require TECHSERVICE_ROOT . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Услуги организации</span>
        <h1 class="page-title">Каталог услуг</h1>
        <p class="page-lead">
            Выберите направление работ или воспользуйтесь поиском по названию, чтобы ознакомиться с описанием услуги и оформить заявку на диагностику или ремонт.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($dbError): ?>
            <div class="notice-box notice-error" role="alert">
                Не удалось загрузить каталог услуг. Пожалуйста, обновите страницу позже или перейдите к форме заявки.
            </div>
        <?php else: ?>
            <?= CatalogView::renderToolbar(
                $catalogData['categories'],
                $catalogData['activeCategory'],
                $catalogData['searchQuery'],
                $catalogData['totalServices']
            ) ?>

            <?= CatalogView::renderServiceGrid($catalogData['services']) ?>
            <?= CatalogView::renderServiceModal() ?>
        <?php endif; ?>
    </div>
</section>

<?php require TECHSERVICE_ROOT . '/includes/footer.php'; ?>
