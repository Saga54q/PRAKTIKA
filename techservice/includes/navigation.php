<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Возвращает список основных пунктов навигации.
 */
function get_navigation_items(): array
{
    return [
        ['id' => 'home', 'label' => 'Главная', 'href' => 'index.php'],
        ['id' => 'catalog', 'label' => 'Каталог услуг', 'href' => 'catalog.php'],
        ['id' => 'about', 'label' => 'О компании', 'href' => 'about.php'],
        ['id' => 'contacts', 'label' => 'Контакты', 'href' => 'contacts.php'],
    ];
}

/**
 * Рендерит основную навигацию сайта.
 */
function render_navigation(string $activePage = 'home'): string
{
    $items = get_navigation_items();
    ob_start();
    ?>
    <nav class="site-nav" id="primary-navigation" aria-label="Основная навигация">
        <ul class="nav-list">
            <?php foreach ($items as $item): ?>
                <?php $isActive = ($activePage === $item['id']); ?>
                <li class="nav-item">
                    <a
                        class="nav-link<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= e($item['href']) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                    >
                        <?= e($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="nav-actions">
            <a
                class="btn btn-primary btn-sm<?= $activePage === 'request' ? ' is-active' : '' ?>"
                href="request.php"
                <?= $activePage === 'request' ? 'aria-current="page"' : '' ?>
            >
                Оставить заявку
            </a>
        </div>
    </nav>
    <?php
    return (string) ob_get_clean();
}
