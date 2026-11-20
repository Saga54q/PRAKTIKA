<?php
declare(strict_types=1);

if (!defined('TECHSERVICE_APP')) {
    http_response_code(403);
    exit('Доступ запрещён');
}

/**
 * Представление страницы оформления заявки: форма, ошибки и уведомление об успешной отправке.
 */
class RequestView
{
    /**
     * Рендерит уведомление об успешном принятии заявки.
     */
    public static function renderSuccessNotice(array $summary): string
    {
        $id = (int) ($summary['id'] ?? 0);
        $clientName = (string) ($summary['client_name'] ?? '');
        $serviceTitle = (string) ($summary['service_title'] ?? '');
        $equipmentType = (string) ($summary['equipment_type'] ?? '');
        $phone = (string) ($summary['phone'] ?? '');
        $email = (string) ($summary['email'] ?? '');

        ob_start();
        ?>
        <section class="request-success-card" role="status" aria-live="polite" data-request-success>
            <div class="success-icon-wrap" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="success-content">
                <span class="success-badge">Заявка зарегистрирована</span>
                <h2 class="success-title">Спасибо, <?= e($clientName) ?>! Ваша заявка № <?= $id ?> принята.</h2>
                <p class="success-lead">
                    Обращение сохранено в системе. Специалист рассмотрит переданные сведения и свяжется с вами по указанным контактным данным для уточнения деталей приёмки.
                </p>

                <dl class="success-summary-list">
                    <div class="summary-row">
                        <dt>Регистрационный номер</dt>
                        <dd>#<?= $id ?></dd>
                    </div>
                    <?php if ($serviceTitle !== ''): ?>
                        <div class="summary-row">
                            <dt>Выбранная услуга</dt>
                            <dd><?= e($serviceTitle) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($equipmentType !== ''): ?>
                        <div class="summary-row">
                            <dt>Тип оборудования</dt>
                            <dd><?= e($equipmentType) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                        <div class="summary-row">
                            <dt>Контактный телефон</dt>
                            <dd><?= e($phone) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <div class="summary-row">
                            <dt>Электронная почта</dt>
                            <dd><?= e($email) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <div class="success-actions">
                    <a href="catalog.php" class="btn btn-primary">Вернуться в каталог</a>
                    <a href="request.php" class="btn btn-outline">Оформить ещё одну заявку</a>
                </div>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Рендерит форму заявки со всеми 6 обязательными полями.
     */
    public static function renderForm(array $viewData): string
    {
        $groupedServices = $viewData['groupedServices'];
        $formData = $viewData['formData'];
        $errors = $viewData['errors'];
        $generalError = $viewData['generalError'];
        $csrfToken = $viewData['csrfToken'];
        $submissionToken = $viewData['submissionToken'];

        ob_start();
        ?>
        <form
            class="request-form"
            action="request.php"
            method="post"
            novalidate
            data-request-form
        >
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="submission_token" value="<?= e($submissionToken) ?>">

            <?php if ($generalError !== null): ?>
                <div class="form-alert form-alert-error" role="alert" data-form-alert>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span><?= e($generalError) ?></span>
                </div>
            <?php else: ?>
                <div class="form-alert form-alert-error is-hidden" role="alert" data-form-alert></div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-field<?= isset($errors['client_name']) ? ' has-error' : '' ?>" data-field="client_name">
                    <label class="field-label" for="client_name">
                        Имя <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="client_name"
                        name="client_name"
                        class="field-input"
                        value="<?= e((string) $formData['client_name']) ?>"
                        placeholder="Например, Алексей Смирнов"
                        maxlength="120"
                        required
                        autocomplete="name"
                        aria-invalid="<?= isset($errors['client_name']) ? 'true' : 'false' ?>"
                        aria-describedby="error-client_name"
                    >
                    <p class="field-error" id="error-client_name" data-field-error="client_name">
                        <?= e($errors['client_name'] ?? '') ?>
                    </p>
                </div>

                <div class="form-field<?= isset($errors['phone']) ? ' has-error' : '' ?>" data-field="phone">
                    <label class="field-label" for="phone">
                        Контактный телефон <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="field-input"
                        value="<?= e((string) $formData['phone']) ?>"
                        placeholder="+7 (900) 000-00-00"
                        maxlength="32"
                        required
                        autocomplete="tel"
                        aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>"
                        aria-describedby="error-phone"
                    >
                    <p class="field-error" id="error-phone" data-field-error="phone">
                        <?= e($errors['phone'] ?? '') ?>
                    </p>
                </div>

                <div class="form-field<?= isset($errors['email']) ? ' has-error' : '' ?>" data-field="email">
                    <label class="field-label" for="email">
                        Электронная почта <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="field-input"
                        value="<?= e((string) $formData['email']) ?>"
                        placeholder="name@organization.ru"
                        maxlength="160"
                        required
                        autocomplete="email"
                        aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
                        aria-describedby="error-email"
                    >
                    <p class="field-error" id="error-email" data-field-error="email">
                        <?= e($errors['email'] ?? '') ?>
                    </p>
                </div>

                <div class="form-field<?= isset($errors['equipment_type']) ? ' has-error' : '' ?>" data-field="equipment_type">
                    <label class="field-label" for="equipment_type">
                        Тип оборудования <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="equipment_type"
                        name="equipment_type"
                        class="field-input"
                        value="<?= e((string) $formData['equipment_type']) ?>"
                        placeholder="Например, промышленный контроллер, оптический модуль"
                        maxlength="180"
                        required
                        aria-invalid="<?= isset($errors['equipment_type']) ? 'true' : 'false' ?>"
                        aria-describedby="error-equipment_type"
                    >
                    <p class="field-error" id="error-equipment_type" data-field-error="equipment_type">
                        <?= e($errors['equipment_type'] ?? '') ?>
                    </p>
                </div>

                <div class="form-field form-field-full<?= isset($errors['service_id']) ? ' has-error' : '' ?>" data-field="service_id">
                    <label class="field-label" for="service_id">
                        Интересующая услуга <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="service_id"
                        name="service_id"
                        class="field-select"
                        required
                        aria-invalid="<?= isset($errors['service_id']) ? 'true' : 'false' ?>"
                        aria-describedby="error-service_id"
                    >
                        <option value="">— Выберите услугу из каталога —</option>
                        <?php foreach ($groupedServices as $group): ?>
                            <optgroup label="<?= e($group['category_name']) ?>">
                                <?php foreach ($group['services'] as $service): ?>
                                    <?php $sId = (int) $service['id']; ?>
                                    <option
                                        value="<?= $sId ?>"
                                        <?= ((int) $formData['service_id'] === $sId) ? 'selected' : '' ?>
                                    >
                                        <?= e((string) $service['code'] . ' — ' . (string) $service['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-error" id="error-service_id" data-field-error="service_id">
                        <?= e($errors['service_id'] ?? '') ?>
                    </p>
                </div>

                <div class="form-field form-field-full<?= isset($errors['problem_description']) ? ' has-error' : '' ?>" data-field="problem_description">
                    <label class="field-label" for="problem_description">
                        Описание проблемы или неисправности <span class="required-mark" aria-hidden="true">*</span>
                    </label>
                    <textarea
                        id="problem_description"
                        name="problem_description"
                        class="field-textarea"
                        rows="5"
                        maxlength="2000"
                        required
                        placeholder="Опишите проявления неисправности, условия возникновения сбоя и требуемый объём работ (от 10 символов)..."
                        aria-invalid="<?= isset($errors['problem_description']) ? 'true' : 'false' ?>"
                        aria-describedby="error-problem_description"
                    ><?= e((string) $formData['problem_description']) ?></textarea>
                    <div class="field-meta-row">
                        <p class="field-error" id="error-problem_description" data-field-error="problem_description">
                            <?= e($errors['problem_description'] ?? '') ?>
                        </p>
                        <span class="field-hint">Обязательно к заполнению</span>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" data-submit-btn>
                    Отправить заявку
                </button>
                <p class="form-privacy-note">
                    Нажимая кнопку отправки, вы передаёте контактные сведения исключительно для первичной обработки обращения по выбранной услуге.
                </p>
            </div>
        </form>
        <?php
        return (string) ob_get_clean();
    }
}
