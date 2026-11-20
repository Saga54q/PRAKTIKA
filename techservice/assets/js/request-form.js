/**
 * Клиентская валидация формы заявки и защита от двойной отправки.
 */
(function () {
    'use strict';

    const form = document.querySelector('[data-request-form]');
    if (!form) {
        return;
    }

    const alertBox = form.querySelector('[data-form-alert]');
    const submitBtn = form.querySelector('[data-submit-btn]');

    const fields = {
        client_name: form.querySelector('#client_name'),
        phone: form.querySelector('#phone'),
        email: form.querySelector('#email'),
        equipment_type: form.querySelector('#equipment_type'),
        service_id: form.querySelector('#service_id'),
        problem_description: form.querySelector('#problem_description'),
    };

    const validators = {
        client_name: function (value) {
            const v = value.trim().replace(/\s+/g, ' ');
            if (!v) return 'Укажите ваше имя.';
            if (v.length < 2 || v.length > 120) return 'Имя должно содержать от 2 до 120 символов.';
            if (!/^[\p{L}\s\-'.]+$/u.test(v)) {
                return 'Имя может содержать только буквы, пробелы, дефис и точку.';
            }
            return '';
        },
        phone: function (value) {
            const v = value.trim();
            const digits = v.replace(/\D/g, '');
            if (!v) return 'Укажите контактный телефон.';
            if (!/^[0-9+\-\s()]{7,32}$/.test(v) || digits.length < 10 || digits.length > 15) {
                return 'Укажите корректный номер телефона (от 10 до 15 цифр, например +7 (900) 000-00-00).';
            }
            return '';
        },
        email: function (value) {
            const v = value.trim();
            if (!v) return 'Укажите адрес электронной почты.';
            if (v.length > 160 || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) {
                return 'Укажите корректный адрес электронной почты (например, name@company.ru).';
            }
            return '';
        },
        equipment_type: function (value) {
            const v = value.trim().replace(/\s+/g, ' ');
            if (!v) return 'Укажите тип или модель оборудования.';
            if (v.length < 2 || v.length > 180) {
                return 'Название типа оборудования должно содержать от 2 до 180 символов.';
            }
            return '';
        },
        service_id: function (value) {
            const v = parseInt(value, 10);
            if (!v || v <= 0) return 'Выберите интересующую услугу из списка.';
            return '';
        },
        problem_description: function (value) {
            const v = value.trim();
            if (!v) return 'Опишите проблему или характер неисправности.';
            if (v.length < 10) return 'Описание проблемы должно содержать не менее 10 символов.';
            if (v.length > 2000) return 'Описание проблемы не должно превышать 2000 символов.';
            return '';
        },
    };

    function setFieldState(name, errorMessage) {
        const input = fields[name];
        if (!input) return;

        const container = form.querySelector('[data-field="' + name + '"]');
        const errorEl = form.querySelector('[data-field-error="' + name + '"]');
        const hasError = errorMessage !== '';

        if (container) {
            container.classList.toggle('has-error', hasError);
            container.classList.toggle('is-valid', !hasError && input.value.trim() !== '');
        }
        input.setAttribute('aria-invalid', hasError ? 'true' : 'false');
        if (errorEl) {
            errorEl.textContent = errorMessage;
        }
    }

    function validateField(name) {
        const input = fields[name];
        if (!input || !validators[name]) {
            return '';
        }
        const errorMessage = validators[name](input.value);
        setFieldState(name, errorMessage);
        return errorMessage;
    }

    function showGeneralAlert(message) {
        if (!alertBox) return;
        if (!message) {
            alertBox.classList.add('is-hidden');
            alertBox.textContent = '';
            return;
        }
        alertBox.textContent = message;
        alertBox.classList.remove('is-hidden');
    }

    Object.keys(fields).forEach(function (name) {
        const input = fields[name];
        if (!input) return;

        const eventName = input.tagName === 'SELECT' ? 'change' : 'blur';
        input.addEventListener(eventName, function () {
            validateField(name);
        });

        input.addEventListener('input', function () {
            const container = form.querySelector('[data-field="' + name + '"]');
            if (container && container.classList.contains('has-error')) {
                validateField(name);
            }
        });
    });

    let isSubmitting = false;

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        let firstInvalidInput = null;
        let errorCount = 0;

        Object.keys(fields).forEach(function (name) {
            const err = validateField(name);
            if (err !== '') {
                errorCount += 1;
                if (!firstInvalidInput) {
                    firstInvalidInput = fields[name];
                }
            }
        });

        if (errorCount > 0) {
            event.preventDefault();
            showGeneralAlert('Проверьте корректность заполнения обязательных полей формы.');
            if (firstInvalidInput) {
                firstInvalidInput.focus();
            }
            return;
        }

        showGeneralAlert('');
        isSubmitting = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка заявки...';
        }
    });
})();
