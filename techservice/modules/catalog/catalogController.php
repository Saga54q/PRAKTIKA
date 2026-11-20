<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

require_once TECHSERVICE_ROOT . '/modules/catalog/catalogModel.php';
require_once TECHSERVICE_ROOT . '/modules/catalog/catalogView.php';

/**
 * Контроллер каталога услуг: обработка фильтров, поиска и JSON-выдачи.
 */
class CatalogController
{
    private CatalogModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new CatalogModel($pdo);
    }

    /**
     * Подготавливает данные для страницы каталога или JSON-ответа.
     *
     * @return array<string, mixed>
     */
    public function handle(array $queryParams): array
    {
        $categories = $this->model->getCategories();
        $allowedSlugs = array_column($categories, 'slug');

        $rawCategory = isset($queryParams['category']) ? trim((string) $queryParams['category']) : 'all';
        $activeCategory = in_array($rawCategory, $allowedSlugs, true) ? $rawCategory : 'all';

        $searchQuery = isset($queryParams['q']) ? trim((string) $queryParams['q']) : '';
        if (mb_strlen($searchQuery, 'UTF-8') > 100) {
            $searchQuery = mb_substr($searchQuery, 0, 100, 'UTF-8');
        }

        $services = $this->model->getServices(
            $activeCategory === 'all' ? null : $activeCategory,
            $searchQuery
        );

        return [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'searchQuery' => $searchQuery,
            'services' => $services,
            'totalServices' => count($services),
        ];
    }

    /**
     * Отдаёт JSON-ответ для динамического поиска и фильтрации без перезагрузки страницы.
     */
    public function respondJson(array $queryParams): void
    {
        $data = $this->handle($queryParams);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'status' => 'ok',
            'activeCategory' => $data['activeCategory'],
            'searchQuery' => $data['searchQuery'],
            'total' => $data['totalServices'],
            'services' => $data['services'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
