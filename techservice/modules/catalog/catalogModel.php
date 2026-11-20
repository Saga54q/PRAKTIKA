<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Модель каталога услуг: получение категорий и услуг из БД через PDO.
 */
class CatalogModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Возвращает все категории с количеством активных услуг.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCategories(): array
    {
        $sql = '
            SELECT
                c.id,
                c.slug,
                c.name,
                c.description,
                c.sort_order,
                COUNT(s.id) AS service_count
            FROM categories c
            LEFT JOIN services s
                ON s.category_id = c.id
               AND s.is_active = 1
            GROUP BY c.id, c.slug, c.name, c.description, c.sort_order
            ORDER BY c.sort_order ASC, c.id ASC
        ';

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Возвращает категорию по символьному коду (slug) или null.
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, name, description, sort_order FROM categories WHERE slug = :slug LIMIT 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Возвращает активные услуги с фильтрацией по категории и поисковому запросу.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getServices(?string $categorySlug = null, ?string $searchQuery = null): array
    {
        $conditions = ['s.is_active = 1'];
        $params = [];

        if ($categorySlug !== null && $categorySlug !== '' && $categorySlug !== 'all') {
            $conditions[] = 'c.slug = :category_slug';
            $params[':category_slug'] = $categorySlug;
        }

        $cleanQuery = trim((string) $searchQuery);
        if ($cleanQuery !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cleanQuery);
            $pattern = '%' . mb_strtolower($escaped, 'UTF-8') . '%';

            $conditions[] = '(
                LOWER(s.title) LIKE :q_title ESCAPE \'\\\'
                OR LOWER(s.short_description) LIKE :q_short ESCAPE \'\\\'
                OR LOWER(s.code) LIKE :q_code ESCAPE \'\\\'
            )';
            $params[':q_title'] = $pattern;
            $params[':q_short'] = $pattern;
            $params[':q_code'] = $pattern;
        }

        $sql = '
            SELECT
                s.id,
                s.category_id,
                s.code,
                s.slug,
                s.title,
                s.short_description,
                s.full_description,
                s.image_path,
                s.sort_order,
                c.slug AS category_slug,
                c.name AS category_name
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            WHERE ' . implode(' AND ', $conditions) . '
            ORDER BY s.sort_order ASC, s.id ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Возвращает ограниченную выборку ключевых услуг для главной страницы.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFeaturedServices(int $limit = 4): array
    {
        $sql = '
            SELECT
                s.id,
                s.category_id,
                s.code,
                s.slug,
                s.title,
                s.short_description,
                s.full_description,
                s.image_path,
                c.slug AS category_slug,
                c.name AS category_name
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            WHERE s.is_active = 1
            ORDER BY s.sort_order ASC, s.id ASC
            LIMIT :limit
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Возвращает активную услугу по ID или null.
     */
    public function getServiceById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = '
            SELECT
                s.id,
                s.category_id,
                s.code,
                s.slug,
                s.title,
                s.short_description,
                s.full_description,
                s.image_path,
                c.slug AS category_slug,
                c.name AS category_name
            FROM services s
            INNER JOIN categories c ON c.id = s.category_id
            WHERE s.id = :id AND s.is_active = 1
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Возвращает активные услуги, сгруппированные по категориям, для выпадающего списка заявки.
     *
     * @return array<int, array{category_name: string, services: array<int, array<string, mixed>>}>
     */
    public function getServicesGroupedByCategory(): array
    {
        $services = $this->getServices();
        $grouped = [];

        foreach ($services as $service) {
            $catId = (int) $service['category_id'];
            if (!isset($grouped[$catId])) {
                $grouped[$catId] = [
                    'category_name' => (string) $service['category_name'],
                    'services' => [],
                ];
            }
            $grouped[$catId]['services'][] = $service;
        }

        return array_values($grouped);
    }
}
