<?php
declare(strict_types=1);

if (defined('TECHSERVICE_APP')) {
    return;
}

define('TECHSERVICE_APP', true);
define('TECHSERVICE_ROOT', dirname(__DIR__));

// Безопасная обработка ошибок: технические сведения не выводятся пользователю
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');

if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const SITE_NAME = 'ООО «Техсервис»';
const SITE_TAGLINE = 'Ремонт и обслуживание электронного и оптического оборудования';

// Демонстрационные контактные реквизиты и заполнители (подлежат замене на актуальные данные при публикации)
const SITE_DEMO_CONTACTS = [
    'phone' => '+7 (000) 000-00-00',
    'email' => 'info@techservice.example',
    'address' => '[Адрес организации указывается при публикации]',
    'schedule' => 'Пн–Пт, график приёма уточняется по согласованию',
    'requisites_note' => 'Реквизиты (ИНН, КПП, ОГРН) и сведения о лицензиях предоставляются при официальном обращении или размещаются перед вводом сайта в эксплуатацию.',
];

require_once TECHSERVICE_ROOT . '/includes/helpers.php';
require_once TECHSERVICE_ROOT . '/config/database.php';
