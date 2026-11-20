<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$pageTitle = 'Контакты';
$pageDescription = 'Контактная информация и реквизиты ООО «Техсервис» для первичных обращений.';
$activePage = 'contacts';

require TECHSERVICE_ROOT . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Обратная связь</span>
        <h1 class="page-title">Контакты</h1>
        <p class="page-lead">
            Справочная контактная информация и переход к онлайн-форме первичного обращения.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="notice-box notice-info" role="note">
            <strong>Демонстрационный раздел:</strong> контактные телефоны, адрес и электронная почта на этой странице являются заполнителями и подлежат замене на актуальные реквизиты ООО «Техсервис» перед вводом сайта в эксплуатацию.
        </div>

        <div class="contacts-grid">
            <article class="contact-card">
                <span class="contact-label">Контактный телефон</span>
                <p class="contact-value"><?= e(SITE_DEMO_CONTACTS['phone']) ?></p>
                <p class="contact-note">Демонстрационный номер для последующей подстановки</p>
            </article>

            <article class="contact-card">
                <span class="contact-label">Электронная почта</span>
                <p class="contact-value"><?= e(SITE_DEMO_CONTACTS['email']) ?></p>
                <p class="contact-note">Адрес для общих корпоративных обращений</p>
            </article>

            <article class="contact-card">
                <span class="contact-label">Местонахождение</span>
                <p class="contact-value"><?= e(SITE_DEMO_CONTACTS['address']) ?></p>
                <p class="contact-note">Фактический адрес приёмки указывается организацией</p>
            </article>

            <article class="contact-card">
                <span class="contact-label">Режим приёма</span>
                <p class="contact-value"><?= e(SITE_DEMO_CONTACTS['schedule']) ?></p>
                <p class="contact-note">Предварительная регистрация через форму заявки</p>
            </article>
        </div>

        <div class="contacts-cta-card">
            <div>
                <h2 class="content-heading">Онлайн-регистрация обращения</h2>
                <p class="content-paragraph">
                    Для передачи информации о неисправном оборудовании используйте специализированную форму заявки с выбором услуги из каталога.
                </p>
            </div>
            <a href="request.php" class="btn btn-primary btn-lg">Перейти к форме заявки</a>
        </div>
    </div>
</section>

<?php require TECHSERVICE_ROOT . '/includes/footer.php'; ?>
