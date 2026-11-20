<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

require_once TECHSERVICE_ROOT . '/includes/navigation.php';

$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? 'ООО «Техсервис» — диагностика, ремонт и обслуживание электронного и оптического оборудования.';
$activePage = $activePage ?? 'home';
$pageStyles = $pageStyles ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> — <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <?php foreach ($pageStyles as $stylePath): ?>
        <link rel="stylesheet" href="<?= e($stylePath) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
<a class="skip-link" href="#main-content">Перейти к основному содержимому</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="<?= e(SITE_NAME) ?> — на главную">
            <span class="brand-mark" aria-hidden="true">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1.5" y="1.5" width="33" height="33" rx="6" fill="#0B192C" stroke="#1E3A5F" stroke-width="1.5"/>
                    <circle cx="18" cy="18" r="8" stroke="#38BDF8" stroke-width="1.75"/>
                    <circle cx="18" cy="18" r="3" fill="#0284C7"/>
                    <path d="M18 5V10M18 26V31M5 18H10M26 18H31" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="brand-text">
                <span class="brand-title">ООО «Техсервис»</span>
                <span class="brand-subtitle">Электронные и оптические системы</span>
            </span>
        </a>

        <button
            type="button"
            class="nav-toggle"
            aria-controls="primary-navigation"
            aria-expanded="false"
            aria-label="Открыть меню"
            data-nav-toggle
        >
            <span class="nav-toggle-line"></span>
            <span class="nav-toggle-line"></span>
            <span class="nav-toggle-line"></span>
        </button>

        <?= render_navigation($activePage) ?>
    </div>
</header>

<main id="main-content" class="site-main">
