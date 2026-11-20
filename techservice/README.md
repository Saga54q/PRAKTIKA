# Корпоративный сайт ООО «Техсервис» (Модуль: каталог услуг и оформление заявки)

Корпоративный веб-портал организации **ООО «Техсервис»**, специализирующейся на диагностике, ремонте и техническом обслуживании электронного и оптического оборудования. Архитектура проекта спроектирована с разделением на логические модули и готова к поэтапному расширению.

## Реализованный функционал (Этап 1)

1. **Информационные страницы компании**:
   - `index.php` — главная страница с представлением организации, направлениями работ, подборкой услуг и принципами работы.
   - `about.php` — общая публичная информация о деятельности организации и работе в регулируемом сегменте.
   - `contacts.php` — контактная информация (демонстрационные заполнители) и переход к оформлению заявки.
2. **Модуль каталога услуг (`modules/catalog/`)**:
   - хранение категорий и услуг в MySQL отдельно от шаблонов интерфейса;
   - фильтрация услуг по категориям (`GET` + динамическое обновление через `fetch`);
   - поиск услуг по названию, коду и описанию;
   - карточки услуг с тематическими SVG-иллюстрациями, модальным окном подробного описания и кнопкой «Оставить заявку» с предвыбором услуги.
3. **Модуль оформления заявки (`modules/requests/`)**:
   - форма с 6 обязательными полями: имя, контактный телефон, электронная почта, тип оборудования, интересующая услуга, описание проблемы или неисправности;
   - клиентская (`assets/js/request-form.js`) и серверная (`requestValidator.php`) валидация;
   - защита от CSRF и повторной отправки формы (одноразовый `submission_token` в сессии + уникальный индекс в БД + паттерн Post/Redirect/Get);
   - сохранение заявок в таблицу `requests` через PDO и подготовленные SQL-запросы;
   - экран подтверждения с регистрационным номером заявки.

## Структура проекта

```text
techservice/
├── index.php
├── catalog.php
├── request.php
├── about.php
├── contacts.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── database.example.php
├── includes/
│   ├── helpers.php
│   ├── header.php
│   ├── navigation.php
│   └── footer.php
├── modules/
│   ├── catalog/
│   │   ├── catalogController.php
│   │   ├── catalogModel.php
│   │   └── catalogView.php
│   └── requests/
│       ├── requestController.php
│       ├── requestModel.php
│       ├── requestValidator.php
│       └── requestView.php
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   ├── header.css
│   │   ├── catalog.css
│   │   ├── request-form.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── main.js
│   │   ├── catalog.js
│   │   └── request-form.js
│   └── images/services/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── tests/
│   └── run_tests.php
└── README.md
```

## Требования к окружению

- PHP 8.0+ (с расширениями `pdo_mysql`, `mbstring`, `session`, `json`)
- MySQL 8.0+ (или MariaDB 10.6+)
- Веб-сервер Apache / Nginx или встроенный сервер PHP для локального запуска

## Инструкция по запуску

### 1. Создание и наполнение базы данных MySQL

Выполните SQL-скрипты из каталога `database/`:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

### 2. Настройка подключения к БД

Создайте файл `config/database.local.php` на основе шаблона `config/database.example.php` (либо задайте переменные окружения `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`):

```bash
cp config/database.example.php config/database.local.php
```

Укажите в `config/database.local.php` актуальные учётные данные MySQL. Файл `config/database.local.php` исключён из Git в `.gitignore`.

### 3. Запуск локального сервера

Из корневой директории `techservice/`:

```bash
php -S 127.0.0.1:8000
```

После запуска откройте в браузере: `http://127.0.0.1:8000/index.php`.

## Запуск автотестов

Автоматический прогон модульных и интеграционных тестов (работает на изолированной SQLite-базе в памяти без модификации рабочей MySQL):

```bash
php tests/run_tests.php
```
