<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$pageTitle = 'О компании';
$pageDescription = 'Общая информация о деятельности ООО «Техсервис» в сфере ремонта электронного и оптического оборудования.';
$activePage = 'about';

require TECHSERVICE_ROOT . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Профиль организации</span>
        <h1 class="page-title">О компании ООО «Техсервис»</h1>
        <p class="page-lead">
            Специализированная организация, ориентированная на диагностику, ремонт и техническое обслуживание электронного и оптического оборудования.
        </p>
    </div>
</section>

<section class="section">
    <div class="container content-two-col">
        <div class="content-block">
            <h2 class="content-heading">Основные направления деятельности</h2>
            <p class="content-paragraph">
                ООО «Техсервис» выполняет работы по инструментальной диагностике, компонентному восстановлению электронной аппаратуры, юстировке и обслуживанию оптических узлов, а также плановой профилактике специализированного оборудования.
            </p>
            <p class="content-paragraph">
                Работа выстроена по последовательному инженерному регламенту: от первичной оценки состояния и локализации неисправности до контрольной проверки параметров функционирования.
            </p>

            <h2 class="content-heading">Работа в регулируемом сегменте</h2>
            <p class="content-paragraph">
                Организация обладает профильным лицензированием в области разработки, производства и технического обслуживания шифровальных (криптографических) средств и защищённых телекоммуникационных систем.
            </p>
            <p class="content-paragraph">
                Наличие разрешительной документации подтверждает соответствие компании требованиям регуляторов, регламентам защиты информации и стандартам организационной надёжности. Подробные реквизиты лицензий предоставляются в рамках официального взаимодействия.
            </p>
        </div>

        <aside class="info-card">
            <h2 class="info-card-title">Ключевые сведения</h2>
            <dl class="requisites-list">
                <div class="requisites-row">
                    <dt>Полное наименование</dt>
                    <dd>Общество с ограниченной ответственностью «Техсервис»</dd>
                </div>
                <div class="requisites-row">
                    <dt>Профиль</dt>
                    <dd>Ремонт и обслуживание электронного и оптического оборудования</dd>
                </div>
                <div class="requisites-row">
                    <dt>Лицензируемая деятельность</dt>
                    <dd>Работы в области криптографических средств и защищённых телекоммуникационных систем</dd>
                </div>
                <div class="requisites-row">
                    <dt>Реквизиты</dt>
                    <dd><?= e(SITE_DEMO_CONTACTS['requisites_note']) ?></dd>
                </div>
            </dl>
            <div class="info-card-actions">
                <a href="catalog.php" class="btn btn-primary">Каталог услуг</a>
                <a href="request.php" class="btn btn-outline">Оставить заявку</a>
            </div>
        </aside>
    </div>
</section>

<?php require TECHSERVICE_ROOT . '/includes/footer.php'; ?>
