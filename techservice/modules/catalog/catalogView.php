<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Представление каталога услуг: панель фильтрации, карточки и модальное окно подробностей.
 */
class CatalogView
{
    /**
     * Рендерит панель поиска и категорийных фильтров.
     */
    public static function renderToolbar(array $categories, string $activeCategory, string $searchQuery, int $totalServices): string
    {
        $totalAll = 0;
        foreach ($categories as $cat) {
            $totalAll += (int) $cat['service_count'];
        }

        ob_start();
        ?>
        <section class="catalog-controls" aria-label="Поиск и фильтрация услуг">
            <form class="catalog-search-form" action="catalog.php" method="get" data-catalog-form>
                <input type="hidden" name="category" value="<?= e($activeCategory) ?>" data-category-input>
                <div class="search-field-wrap">
                    <label class="visually-hidden" for="catalog-search-input">Поиск услуг по названию</label>
                    <span class="search-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M20 20L16.5 16.5"/>
                        </svg>
                    </span>
                    <input
                        id="catalog-search-input"
                        type="search"
                        name="q"
                        class="search-input"
                        value="<?= e($searchQuery) ?>"
                        placeholder="Поиск по названию, коду или описанию услуги..."
                        maxlength="100"
                        autocomplete="off"
                        data-catalog-search
                    >
                    <button
                        type="button"
                        class="search-clear-btn<?= $searchQuery === '' ? ' is-hidden' : '' ?>"
                        aria-label="Очистить поиск"
                        data-search-clear
                    >Сбросить</button>
                </div>
                <button type="submit" class="btn btn-secondary">Найти</button>
            </form>

            <div class="category-filter-row" role="tablist" aria-label="Фильтр по категориям услуг" data-category-filters>
                <a
                    href="<?= e(page_url('catalog.php', $searchQuery !== '' ? ['q' => $searchQuery] : [])) ?>"
                    class="filter-pill<?= $activeCategory === 'all' ? ' is-active' : '' ?>"
                    data-category-btn="all"
                    role="tab"
                    aria-selected="<?= $activeCategory === 'all' ? 'true' : 'false' ?>"
                >
                    <span>Все направления</span>
                    <span class="filter-count"><?= $totalAll ?></span>
                </a>
                <?php foreach ($categories as $category): ?>
                    <?php
                    $slug = (string) $category['slug'];
                    $isActive = ($activeCategory === $slug);
                    $params = ['category' => $slug];
                    if ($searchQuery !== '') {
                        $params['q'] = $searchQuery;
                    }
                    ?>
                    <a
                        href="<?= e(page_url('catalog.php', $params)) ?>"
                        class="filter-pill<?= $isActive ? ' is-active' : '' ?>"
                        data-category-btn="<?= e($slug) ?>"
                        role="tab"
                        aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                    >
                        <span><?= e((string) $category['name']) ?></span>
                        <span class="filter-count"><?= (int) $category['service_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="catalog-meta-bar">
                <p class="catalog-result-count" data-catalog-count>
                    Найдено услуг: <strong><?= $totalServices ?></strong>
                </p>
                <?php if ($activeCategory !== 'all' || $searchQuery !== ''): ?>
                    <a class="reset-filters-link" href="catalog.php" data-reset-filters>Сбросить все фильтры</a>
                <?php else: ?>
                    <a class="reset-filters-link is-hidden" href="catalog.php" data-reset-filters>Сбросить все фильтры</a>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Рендерит сетку карточек услуг.
     */
    public static function renderServiceGrid(array $services): string
    {
        ob_start();
        ?>
        <div class="services-grid" data-services-grid>
            <?php if ($services === []): ?>
                <div class="empty-state" data-empty-state>
                    <h2 class="empty-state-title">Услуги по заданным критериям не найдены</h2>
                    <p class="empty-state-text">
                        Измените поисковый запрос или выберите другое направление оборудования, чтобы просмотреть доступные услуги.
                    </p>
                    <a href="catalog.php" class="btn btn-secondary" data-reset-filters>Показать весь каталог</a>
                </div>
            <?php else: ?>
                <?php foreach ($services as $service): ?>
                    <?= self::renderServiceCard($service) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Рендерит одну карточку услуги.
     */
    public static function renderServiceCard(array $service): string
    {
        $id = (int) $service['id'];
        $title = (string) $service['title'];
        $code = (string) $service['code'];
        $categoryName = (string) $service['category_name'];
        $shortDesc = (string) $service['short_description'];
        $fullDesc = (string) $service['full_description'];
        $imagePath = (string) $service['image_path'];

        ob_start();
        ?>
        <article
            class="service-card"
            id="service-<?= $id ?>"
            data-service-card
            data-service-id="<?= $id ?>"
            data-service-code="<?= e($code) ?>"
            data-service-category="<?= e($categoryName) ?>"
            data-service-title="<?= e($title) ?>"
            data-service-full="<?= e($fullDesc) ?>"
            data-service-image="<?= e($imagePath) ?>"
        >
            <div class="service-card-media">
                <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e($title) ?>"
                    loading="lazy"
                    width="480"
                    height="280"
                >
            </div>
            <div class="service-card-body">
                <div class="service-card-meta">
                    <span class="service-category-badge"><?= e($categoryName) ?></span>
                    <span class="service-code"><?= e($code) ?></span>
                </div>
                <h2 class="service-card-title"><?= e($title) ?></h2>
                <p class="service-card-desc"><?= e($shortDesc) ?></p>
            </div>
            <div class="service-card-footer">
                <button
                    type="button"
                    class="btn btn-outline btn-sm"
                    data-open-service-modal
                    data-service-id="<?= $id ?>"
                >
                    Подробнее
                </button>
                <a
                    href="<?= e(page_url('request.php', ['service_id' => $id])) ?>"
                    class="btn btn-primary btn-sm"
                >
                    Оставить заявку
                </a>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Рендерит модальное окно подробного описания услуги.
     */
    public static function renderServiceModal(): string
    {
        ob_start();
        ?>
        <div
            class="modal-backdrop"
            id="service-detail-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-service-title"
            hidden
        >
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-meta">
                        <span class="service-category-badge" data-modal-category></span>
                        <span class="service-code" data-modal-code></span>
                    </div>
                    <button
                        type="button"
                        class="modal-close"
                        aria-label="Закрыть окно"
                        data-close-modal
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6L18 18"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-media">
                    <img src="" alt="" data-modal-image width="480" height="280">
                </div>
                <div class="modal-body">
                    <h2 class="modal-title" id="modal-service-title" data-modal-title></h2>
                    <p class="modal-description" data-modal-description></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-close-modal>Закрыть</button>
                    <a href="request.php" class="btn btn-primary" data-modal-request-link>Оставить заявку</a>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
