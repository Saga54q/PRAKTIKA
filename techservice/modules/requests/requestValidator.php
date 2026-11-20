<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Валидатор данных заявки на диагностику или ремонт оборудования.
 */
class RequestValidator
{
    private CatalogModel $catalogModel;

    public function __construct(CatalogModel $catalogModel)
    {
        $this->catalogModel = $catalogModel;
    }

    /**
     * Проверяет и нормализует входные данные формы заявки.
     *
     * @return array{
     *   valid: bool,
     *   errors: array<string, string>,
     *   data: array<string, mixed>,
     *   service: ?array<string, mixed>
     * }
     */
    public function validate(array $input): array
    {
        $errors = [];

        $clientName = $this->normalizeText((string) ($input['client_name'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $equipmentType = $this->normalizeText((string) ($input['equipment_type'] ?? ''));
        $serviceId = filter_var($input['service_id'] ?? null, FILTER_VALIDATE_INT);
        $problemDescription = trim((string) ($input['problem_description'] ?? ''));

        // 1. Имя
        $nameLen = mb_strlen($clientName, 'UTF-8');
        if ($clientName === '') {
            $errors['client_name'] = 'Укажите ваше имя.';
        } elseif ($nameLen < 2 || $nameLen > 120) {
            $errors['client_name'] = 'Имя должно содержать от 2 до 120 символов.';
        } elseif (!preg_match('/^[\p{L}\s\-\.\']+$/u', $clientName)) {
            $errors['client_name'] = 'Имя может содержать только буквы, пробелы, дефис и точку.';
        }

        // 2. Контактный телефон
        $phoneDigits = preg_replace('/\D/', '', $phone) ?? '';
        $phoneDigitsLen = strlen($phoneDigits);
        if ($phone === '') {
            $errors['phone'] = 'Укажите контактный телефон.';
        } elseif (!preg_match('/^[0-9+\-\s()]{7,32}$/', $phone) || $phoneDigitsLen < 10 || $phoneDigitsLen > 15) {
            $errors['phone'] = 'Укажите корректный номер телефона (от 10 до 15 цифр, например +7 (900) 000-00-00).';
        }

        // 3. Электронная почта
        if ($email === '') {
            $errors['email'] = 'Укажите адрес электронной почты.';
        } elseif (mb_strlen($email, 'UTF-8') > 160 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Укажите корректный адрес электронной почты (например, name@company.ru).';
        }

        // 4. Тип оборудования
        $equipLen = mb_strlen($equipmentType, 'UTF-8');
        if ($equipmentType === '') {
            $errors['equipment_type'] = 'Укажите тип или модель оборудования.';
        } elseif ($equipLen < 2 || $equipLen > 180) {
            $errors['equipment_type'] = 'Название типа оборудования должно содержать от 2 до 180 символов.';
        }

        // 5. Интересующая услуга (проверка существования в БД)
        $service = null;
        if ($serviceId === false || $serviceId <= 0) {
            $errors['service_id'] = 'Выберите интересующую услугу из списка.';
        } else {
            $service = $this->catalogModel->getServiceById($serviceId);
            if ($service === null) {
                $errors['service_id'] = 'Выбранная услуга не найдена в каталоге.';
            }
        }

        // 6. Описание проблемы или неисправности
        $problemLen = mb_strlen($problemDescription, 'UTF-8');
        if ($problemDescription === '') {
            $errors['problem_description'] = 'Опишите проблему или характер неисправности.';
        } elseif ($problemLen < 10) {
            $errors['problem_description'] = 'Описание проблемы должно содержать не менее 10 символов.';
        } elseif ($problemLen > 2000) {
            $errors['problem_description'] = 'Описание проблемы не должно превышать 2000 символов.';
        }

        return [
            'valid' => ($errors === []),
            'errors' => $errors,
            'data' => [
                'client_name' => $clientName,
                'phone' => $phone,
                'email' => $email,
                'equipment_type' => $equipmentType,
                'service_id' => is_int($serviceId) && $serviceId > 0 ? $serviceId : 0,
                'problem_description' => $problemDescription,
            ],
            'service' => $service,
        ];
    }

    private function normalizeText(string $value): string
    {
        $trimmed = trim($value);
        return (string) preg_replace('/\s+/u', ' ', $trimmed);
    }
}
