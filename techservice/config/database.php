<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Возвращает единое настроенное подключение PDO к базе данных.
 * Настройки считываются из локального файла config/database.local.php
 * (не отслеживается в Git) либо из переменных окружения.
 *
 * @throws RuntimeException при невозможности установить соединение
 */
function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Поддержка тестового DSN через переменную окружения для изолированного прогона тестов
    $customDsn = getenv('TECHSERVICE_DB_DSN');
    if (is_string($customDsn) && $customDsn !== '') {
        try {
            if (str_starts_with($customDsn, 'sqlite:') && method_exists(PDO::class, 'connect')) {
                $pdo = PDO::connect($customDsn, null, null, $options);
            } else {
                $pdo = new PDO($customDsn, null, null, $options);
            }

            if (str_starts_with($customDsn, 'sqlite:')) {
                $pdo->exec('PRAGMA foreign_keys = ON;');
                if (class_exists('Pdo\\Sqlite') && $pdo instanceof Pdo\Sqlite) {
                    $pdo->createFunction(
                        'LOWER',
                        static fn(?string $val): string => mb_strtolower((string) $val, 'UTF-8'),
                        1
                    );
                } elseif (method_exists($pdo, 'sqliteCreateFunction')) {
                    $pdo->sqliteCreateFunction(
                        'LOWER',
                        static fn(?string $val): string => mb_strtolower((string) $val, 'UTF-8'),
                        1
                    );
                }
            }
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection error (custom DSN): ' . $e->getMessage());
            throw new RuntimeException('Не удалось подключиться к базе данных.');
        }
    }

    $localConfigPath = __DIR__ . '/database.local.php';
    $config = is_file($localConfigPath) ? require $localConfigPath : [];

    $host = (string) ($config['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1'));
    $port = (string) ($config['port'] ?? (getenv('DB_PORT') ?: '3306'));
    $dbname = (string) ($config['dbname'] ?? (getenv('DB_NAME') ?: 'techservice_db'));
    $charset = (string) ($config['charset'] ?? 'utf8mb4');
    $username = (string) ($config['username'] ?? (getenv('DB_USER') ?: ''));
    $password = (string) ($config['password'] ?? (getenv('DB_PASS') ?: ''));

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port,
        $dbname,
        $charset
    );

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('MySQL connection error: ' . $e->getMessage());
        throw new RuntimeException('Сервис временно недоступен. Пожалуйста, повторите попытку позже.');
    }
}
