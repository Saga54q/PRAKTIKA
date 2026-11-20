<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogModel.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogView.php';

$pageTitle = 'Главная';
$pageDescription = 'ООО «Техсервис» — диагностика, ремонт и техническое обслуживание электронного и оптического оборудования.';
$activePage = 'home';
$pageStyles = ['assets/css/catalog.css'];
$pageScripts = ['assets/js/catalog.js'];

$categories = [];
$featuredServices = [];
$dbError = false;

try {
    $pdo = getDatabaseConnection();
    $catalogModel = new CatalogModel($pdo);
    $categories = $catalogModel->getCategories();
    $featuredServices = $catalogModel->getFeaturedServices(4);
} catch (Throwable $e) {
    error_log('Home page DB error: ' . $e->getMessage());
    $dbError = true;
}

require TECHSERVICE_ROOT . '/includes/header.php';
?>

<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <span class="eyebrow">Корпоративный технический центр</span>
            <h1 class="hero-title">Диагностика, ремонт и обслуживание электронного и оптического оборудования</h1>
            <p class="hero-lead">
                ООО «Техсервис» выполняет профильные работы по восстановлению и поддержанию работоспособности электронной аппаратуры и оптических систем. Деятельность организации в регулируемом сегменте осуществляется при наличии установленных законодательством лицензий.
            </p>
            <div class="hero-actions">
                <a href="catalog.php" class="btn btn-primary btn-lg">Перейти в каталог услуг</a>
                <a href="request.php" class="btn btn-outline-light btn-lg">Оформить заявку</a>
            </div>
        </div>

        <aside class="hero-panel" aria-label="Ключевые направления">
            <div class="hero-panel-header">
                <span class="hero-panel-code">TS // SERVICE MATRIX</span>
                <span class="hero-panel-status">Приём заявок открыт</span>
            </div>
            <h2 class="hero-panel-title">Профильные компетенции</h2>
            <ul class="hero-capabilities">
                <li>
                    <span class="capability-index">01</span>
                    <div>
                        <strong>Электронная аппаратура</strong>
                        <span>Инструментальная диагностика, компонентный ремонт плат и узлов питания.</span>
                    </div>
                </li>
                <li>
                    <span class="capability-index">02</span>
                    <div>
                        <strong>Оптические системы</strong>
                        <span>Юстировка, настройка оптического тракта и прецизионная очистка элементов.</span>
                    </div>
                </li>
                <li>
                    <span class="capability-index">03</span>
                    <div>
                        <strong>Регламентное обслуживание</strong>
                        <span>Профилактика, контроль эксплуатационных параметров и предремонтная дефектация.</span>
                    </div>
                </li>
                <li>
                    <span class="capability-index">04</span>
                    <div>
                        <strong>Регулируемый сегмент</strong>
                        <span>Наличие лицензирования в сфере шифровальных средств и защищённых систем связи.</span>
                    </div>
                </li>
            </ul>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Направления деятельности</span>
                <h2 class="section-title">Категории технических услуг</h2>
            </div>
            <a href="catalog.php" class="section-link">Весь каталог &rarr;</a>
        </div>

        <?php if ($dbError): ?>
            <div class="notice-box notice-error" role="alert">
                Каталог услуг временно недоступен. Пожалуйста, воспользуйтесь формой заявки или повторите попытку позже.
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a
                        class="category-card"
                        href="<?= e(page_url('catalog.php', ['category' => (string) $category['slug']])) ?>"
                    >
                        <div class="category-card-top">
                            <span class="category-slug-tag"><?= e((string) $category['slug']) ?></span>
                            <span class="category-count"><?= (int) $category['service_count'] ?> усл.</span>
                        </div>
                        <h3 class="category-card-title"><?= e((string) $category['name']) ?></h3>
                        <p class="category-card-desc"><?= e((string) $category['description']) ?></p>
                        <span class="category-card-action">Открыть направление &rarr;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Каталог</span>
                <h2 class="section-title">Востребованные услуги</h2>
            </div>
            <a href="catalog.php" class="section-link">Смотреть все услуги &rarr;</a>
        </div>

        <?php if (!$dbError && $featuredServices !== []): ?>
            <div class="services-grid services-grid-featured">
                <?php foreach ($featuredServices as $service): ?>
                    <?= CatalogView::renderServiceCard($service) ?>
                <?php endforeach; ?>
            </div>
            <?= CatalogView::renderServiceModal() ?>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Принципы работы</span>
                <h2 class="section-title">Инженерный подход и надёжность</h2>
            </div>
            <a href="about.php" class="section-link">О компании &rarr;</a>
        </div>

        <div class="principles-grid">
            <article class="principle-card">
                <div class="principle-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="14" rx="2"/>
                        <path d="M7 12h2l2-4 2 6 2-2h2"/>
                        <path d="M8 20h8"/>
                    </svg>
                </div>
                <h3 class="principle-title">Инструментальная точность</h3>
                <p class="principle-text">
                    Локализация неисправностей выполняется с применением профильного измерительного и диагностического оборудования без предположительных замен узлов.
                </p>
            </article>

            <article class="principle-card">
                <div class="principle-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 2L4 5v6c0 5.25 3.4 10.15 8 11.4 4.6-1.25 8-6.15 8-11.4V5l-8-3z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="principle-title">Регулируемый контур</h3>
                <p class="principle-text">
                    Организация соблюдает установленные требования информационной безопасности и регламенты работы со специализированными телекоммуникационными системами.
                </p>
            </article>

            <article class="principle-card">
                <div class="principle-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="8" y1="13" x2="16" y2="13"/>
                        <line x1="8" y1="17" x2="14" y2="17"/>
                    </svg>
                </div>
                <h3 class="principle-title">Прозрачный регламент</h3>
                <p class="principle-text">
                    Первичная фиксация обращения позволяет согласовать состав обследования и подготовить оборудование к приёмке без лишних этапов.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-band-inner">
        <div>
            <h2 class="cta-band-title">Требуется диагностика или ремонт оборудования?</h2>
            <p class="cta-band-text">
                Заполните форму первичной заявки, выберите направление обслуживания и опишите характер неисправности.
            </p>
        </div>
        <div class="cta-band-actions">
            <a href="request.php" class="btn btn-primary btn-lg">Оставить заявку</a>
            <a href="contacts.php" class="btn btn-outline-light btn-lg">Контактная информация</a>
        </div>
    </div>
</section>

<?php require TECHSERVICE_ROOT . '/includes/footer.php'; ?>
