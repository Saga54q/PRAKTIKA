<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Безопасное экранирование данных перед выводом в HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Формирует относительный URL с опциональными GET-параметрами.
 */
function page_url(string $page, array $params = []): string
{
    if ($params === []) {
        return $page;
    }

    return $page . '?' . http_build_query($params);
}

/**
 * Возвращает или создаёт CSRF-токен текущей сессии.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Проверяет переданный CSRF-токен.
 */
function verify_csrf_token(?string $token): bool
{
    if (!is_string($token) || $token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Выдаёт одноразовый токен отправки формы для защиты от повторного сабмита.
 */
function issue_submission_token(): string
{
    $token = bin2hex(random_bytes(32));
    if (!isset($_SESSION['submission_tokens']) || !is_array($_SESSION['submission_tokens'])) {
        $_SESSION['submission_tokens'] = [];
    }

    // Ограничиваем список активных токенов в сессии (последние 10 вкладок)
    if (count($_SESSION['submission_tokens']) >= 10) {
        $_SESSION['submission_tokens'] = array_slice($_SESSION['submission_tokens'], -9, null, true);
    }

    $_SESSION['submission_tokens'][$token] = time();
    return $token;
}

/**
 * Проверяет и однократно погашает токен отправки формы.
 */
function consume_submission_token(?string $token): bool
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return false;
    }

    if (empty($_SESSION['submission_tokens']) || !isset($_SESSION['submission_tokens'][$token])) {
        return false;
    }

    unset($_SESSION['submission_tokens'][$token]);
    return true;
}

/**
 * Сохраняет flash-сообщение в сессии до следующего чтения.
 */
function set_flash(string $key, mixed $value): void
{
    $_SESSION['flash'][$key] = $value;
}

/**
 * Извлекает и удаляет flash-сообщение из сессии.
 */
function get_flash(string $key): mixed
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}
