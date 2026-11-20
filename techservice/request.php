<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestController.php';

$dbError = false;
$viewData = null;

try {
    $pdo = getDatabaseConnection();
    $controller = new RequestController($pdo);
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $viewData = $controller->handle($method, $_GET, $_POST);
} catch (Throwable $e) {
    error_log('Request page error: ' . $e->getMessage());
    $dbError = true;
}

$pageTitle = 'Оформление заявки';
$pageDescription = 'Отправка заявки на диагностику, ремонт или техническое обслуживание оборудования в ООО «Техсервис».';
$activePage = 'request';
$pageStyles = ['assets/css/request-form.css'];
$pageScripts = ['assets/js/request-form.js'];

require TECHSERVICE_ROOT . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Первичное обращение</span>
        <h1 class="page-title">Оформление заявки</h1>
        <p class="page-lead">
            Заполните сведения о вашем оборудовании и выберите интересующую услугу из каталога для регистрации обращения.
        </p>
    </div>
</section>

<section class="section">
    <div class="container request-layout">
        <div class="request-main">
            <?php if ($dbError): ?>
                <div class="notice-box notice-error" role="alert">
                    Сервис приёма заявок временно недоступен. Пожалуйста, повторите попытку позже.
                </div>
            <?php elseif (!empty($viewData['submittedSummary'])): ?>
                <?= RequestView::renderSuccessNotice($viewData['submittedSummary']) ?>
            <?php else: ?>
                <?= RequestView::renderForm($viewData) ?>
            <?php endif; ?>
        </div>

        <aside class="request-sidebar" aria-label="Порядок обработки обращения">
            <div class="info-card">
                <h2 class="info-card-title">Порядок подачи заявки</h2>
                <ol class="info-steps">
                    <li>
                        <strong>Выбор услуги</strong>
                        <span>Укажите требуемое направление из актуального каталога.</span>
                    </li>
                    <li>
                        <strong>Описание неисправности</strong>
                        <span>Кратко опишите признаки сбоя и тип оборудования.</span>
                    </li>
                    <li>
                        <strong>Регистрация обращения</strong>
                        <span>После проверки данных заявка сохраняется в базе и получает регистрационный номер.</span>
                    </li>
                </ol>
            </div>

            <div class="info-card info-card-muted">
                <h2 class="info-card-title">Конфиденциальность сведений</h2>
                <p class="info-card-text">
                    В первичной форме не следует указывать закрытые технические параметры, ключи, пароли или конфиденциальные сведения о внутренней инфраструктуре организации.
                </p>
                <a href="catalog.php" class="section-link">Открыть каталог услуг &rarr;</a>
            </div>
        </aside>
    </div>
</section>

<?php require TECHSERVICE_ROOT . '/includes/footer.php'; ?>
