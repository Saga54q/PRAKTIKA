<?php
declare(strict_types=1);

/**
 * Шаблон локальной конфигурации подключения к MySQL.
 * Скопируйте этот файл как config/database.local.php и укажите реальные параметры.
 * Файл database.local.php исключён из Git-репозитория (.gitignore).
 */
return [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'dbname' => 'techservice_db',
    'charset' => 'utf8mb4',
    'username' => 'techservice_user',
    'password' => '',
];
