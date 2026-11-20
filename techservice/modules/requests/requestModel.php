<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Модель заявок: сохранение заявки и чтение краткого подтверждения через PDO.
 */
class RequestModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Сохраняет новую заявку в базу данных и возвращает её идентификатор.
     */
    public function createRequest(array $data, string $submissionToken): int
    {
        $sql = '
            INSERT INTO requests (
                service_id,
                client_name,
                phone,
                email,
                equipment_type,
                problem_description,
                submission_token
            ) VALUES (
                :service_id,
                :client_name,
                :phone,
                :email,
                :equipment_type,
                :problem_description,
                :submission_token
            )
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':service_id' => (int) $data['service_id'],
            ':client_name' => (string) $data['client_name'],
            ':phone' => (string) $data['phone'],
            ':email' => (string) $data['email'],
            ':equipment_type' => (string) $data['equipment_type'],
            ':problem_description' => (string) $data['problem_description'],
            ':submission_token' => $submissionToken,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Возвращает безопасную сводку по сохранённой заявке для экрана подтверждения.
     */
    public function getRequestSummaryById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = '
            SELECT
                r.id,
                r.client_name,
                r.phone,
                r.email,
                r.equipment_type,
                r.created_at,
                s.code AS service_code,
                s.title AS service_title
            FROM requests r
            INNER JOIN services s ON s.id = r.service_id
            WHERE r.id = :id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
