<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

$pageScripts = $pageScripts ?? [];
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand">
                <div class="footer-logo-row">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="32" height="32" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="1.5" y="1.5" width="33" height="33" rx="6" fill="#0F233D" stroke="#284B73" stroke-width="1.5"/>
                            <circle cx="18" cy="18" r="8" stroke="#38BDF8" stroke-width="1.75"/>
                            <circle cx="18" cy="18" r="3" fill="#0284C7"/>
                            <path d="M18 5V10M18 26V31M5 18H10M26 18H31" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="footer-company-name"><?= e(SITE_NAME) ?></span>
                </div>
                <p class="footer-description">
                    Специализированная организация в области диагностики, ремонта и технического обслуживания электронного и оптического оборудования.
                </p>
                <p class="footer-compliance-note">
                    Работа в регулируемом сегменте осуществляется в соответствии с действующими нормативными требованиями и наличием профильного лицензирования.
                </p>
            </div>

            <div class="footer-col">
                <h2 class="footer-heading">Разделы сайта</h2>
                <ul class="footer-links">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="catalog.php">Каталог услуг</a></li>
                    <li><a href="about.php">О компании</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                    <li><a href="request.php">Оформление заявки</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h2 class="footer-heading">Контакты (демо-данные)</h2>
                <ul class="footer-contact-list">
                    <li>
                        <span class="footer-contact-label">Телефон:</span>
                        <span><?= e(SITE_DEMO_CONTACTS['phone']) ?></span>
                    </li>
                    <li>
                        <span class="footer-contact-label">Эл. почта:</span>
                        <span><?= e(SITE_DEMO_CONTACTS['email']) ?></span>
                    </li>
                    <li>
                        <span class="footer-contact-label">Адрес:</span>
                        <span><?= e(SITE_DEMO_CONTACTS['address']) ?></span>
                    </li>
                    <li>
                        <span class="footer-contact-label">Режим работы:</span>
                        <span><?= e(SITE_DEMO_CONTACTS['schedule']) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copy">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Все права защищены.</p>
            <p class="footer-meta">Корпоративный портал. Модуль каталога услуг и приёма заявок.</p>
        </div>
    </div>
</footer>

<script src="assets/js/main.js" defer></script>
<?php foreach ($pageScripts as $scriptPath): ?>
    <script src="<?= e($scriptPath) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
