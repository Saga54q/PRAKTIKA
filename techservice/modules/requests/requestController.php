<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

require_once TECHSERVICE_ROOT . '/modules/catalog/catalogModel.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestModel.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestValidator.php';
require_once TECHSERVICE_ROOT . '/modules/requests/requestView.php';

/**
 * Контроллер оформления заявки: защита от повторной отправки, валидация, запись в БД и PRG.
 */
class RequestController
{
    private CatalogModel $catalogModel;
    private RequestModel $requestModel;
    private RequestValidator $validator;

    public function __construct(PDO $pdo)
    {
        $this->catalogModel = new CatalogModel($pdo);
        $this->requestModel = new RequestModel($pdo);
        $this->validator = new RequestValidator($this->catalogModel);
    }

    /**
     * Обрабатывает GET и POST запросы страницы request.php.
     *
     * @return array<string, mixed>
     */
    public function handle(string $method, array $queryParams, array $postParams): array
    {
        $groupedServices = $this->catalogModel->getServicesGroupedByCategory();

        $preselectedServiceId = filter_var($queryParams['service_id'] ?? 0, FILTER_VALIDATE_INT);
        $preselectedServiceId = ($preselectedServiceId !== false && $preselectedServiceId > 0) ? $preselectedServiceId : 0;

        $formData = [
            'client_name' => '',
            'phone' => '',
            'email' => '',
            'equipment_type' => '',
            'service_id' => $preselectedServiceId,
            'problem_description' => '',
        ];
        $errors = [];
        $generalError = null;
        $submittedSummary = null;

        if ($method === 'POST') {
            $csrf = isset($postParams['csrf_token']) ? (string) $postParams['csrf_token'] : null;
            $submissionToken = isset($postParams['submission_token']) ? (string) $postParams['submission_token'] : null;

            $validation = $this->validator->validate($postParams);
            $formData = $validation['data'];

            if (!verify_csrf_token($csrf)) {
                $generalError = 'Истёк маркер безопасности сессии. Пожалуйста, обновите страницу и отправьте форму повторно.';
            } elseif (!isset($_SESSION['submission_tokens'][$submissionToken ?? ''])) {
                $generalError = 'Эта заявка уже была отправлена ранее или форма устарела. При необходимости заполните новую заявку.';
            } elseif (!$validation['valid']) {
                $errors = $validation['errors'];
                $generalError = 'Проверьте корректность заполнения полей формы.';
            } else {
                // Погашаем одноразовый токен непосредственно перед записью в БД
                consume_submission_token($submissionToken);

                try {
                    $requestId = $this->requestModel->createRequest($formData, (string) $submissionToken);
                    $summary = $this->requestModel->getRequestSummaryById($requestId);

                    set_flash('request_submitted_summary', $summary ?? [
                        'id' => $requestId,
                        'client_name' => $formData['client_name'],
                        'equipment_type' => $formData['equipment_type'],
                        'service_title' => $validation['service']['title'] ?? '',
                    ]);

                    header('Location: request.php?submitted=1', true, 303);
                    exit;
                } catch (Throwable $e) {
                    error_log('Request save failed: ' . $e->getMessage());
                    $generalError = 'Не удалось сохранить заявку из-за технической ошибки. Пожалуйста, повторите попытку позже.';
                }
            }
        } else {
            if (isset($queryParams['submitted']) && $queryParams['submitted'] === '1') {
                $submittedSummary = get_flash('request_submitted_summary');
            }
        }

        // Проверяем, что предвыбранная услуга существует в активном каталоге
        if ((int) $formData['service_id'] > 0 && $this->catalogModel->getServiceById((int) $formData['service_id']) === null) {
            $formData['service_id'] = 0;
        }

        return [
            'groupedServices' => $groupedServices,
            'formData' => $formData,
            'errors' => $errors,
            'generalError' => $generalError,
            'submittedSummary' => $submittedSummary,
            'csrfToken' => csrf_token(),
            'submissionToken' => issue_submission_token(),
        ];
    }
}
