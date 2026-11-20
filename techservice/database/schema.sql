-- =============================================================================
-- База данных корпоративного сайта ООО «Техсервис»
-- Модуль: каталог услуг и оформление заявки на диагностику/ремонт
-- СУБД: MySQL 8.0+ (кодировка utf8mb4)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `techservice_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `techservice_db`;

-- -----------------------------------------------------------------------------
-- Таблица категорий услуг
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(64) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` VARCHAR(500) NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Таблица услуг каталога
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(32) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `short_description` VARCHAR(400) NOT NULL,
    `full_description` TEXT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_services_code` (`code`),
    UNIQUE KEY `uq_services_slug` (`slug`),
    KEY `idx_services_category` (`category_id`),
    KEY `idx_services_active_sort` (`is_active`, `sort_order`),
    CONSTRAINT `fk_services_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Таблица заявок клиентов на диагностику и ремонт оборудования
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` INT UNSIGNED NOT NULL,
    `client_name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(32) NOT NULL,
    `email` VARCHAR(160) NOT NULL,
    `equipment_type` VARCHAR(180) NOT NULL,
    `problem_description` TEXT NOT NULL,
    `submission_token` CHAR(64) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_requests_submission_token` (`submission_token`),
    KEY `idx_requests_service` (`service_id`),
    KEY `idx_requests_created_at` (`created_at`),
    CONSTRAINT `fk_requests_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
