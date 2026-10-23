# Инструменты дня 2
Используются ast-index и Playwright Node API. MCP Kilo настроен, но его сессия не проверялась.
Артефакты в docs/plan/ вместо docs/setup/ и docs/metrics/: ограничение AGENTS.md.

## 2.1.1: до индекса
Вопрос разработчика: все места использования mileage/max_mileage_km, файл и функция.
rg -n 'mileage|max_mileage' backend frontend tests:
- ApplicationValidator::validate — тип, нормализация, диапазон.
- AssessmentService::__construct, assess — порог и review.
- AppFactory::create — передача порога.
- ApplicationRepository::save/find — хранение и чтение mileage_km.
- frontend/app.js collectPayload — перевод поля в Number.
- frontend/index.html — поле формы; backend/config/rules.php — два порога.
- MileageTest::assess/testDecisionAndLimit/testInvalidMileage/testMissingMileage; AssessmentServiceTest::payload; ApplicationValidatorTest::payload.

## Замеры токенов
| Режим | Input | Output | USD | Звал ast-index |
|---|---|---|---|---|
| Без индекса | н/д | н/д | н/д | нет, rg |
| ast-index | н/д | н/д | н/д | результат команд ниже |
| ast-index + Caveman | н/д | н/д | н/д | отдельная Kilo-сессия не запускалась |
| Итог | н/д | н/д | н/д | см. протокол |
Нет доступа к OpenRouter Activity; цифры не выдуманы. Финансовую экономию определить нельзя.

## 2.1.2: сжатый ответ по Caveman
Пробег: rules.php — max 500000, review 400000. Validator::validate — вход. AssessmentService::assess — решение. AppFactory::create — конфиг. Repository::save/find — БД. collectPayload — UI. Тесты: MileageTest, AssessmentServiceTest, ApplicationValidatorTest.
Сравнение: основные места сохранены, номера строк и перечисление отдельных тестовых методов потеряны. Это ручное применение инструкции, не независимый замер модели.

## Индекс: фактический прогон
Установлен ast-index 3.56.0. `rebuild`: 27 файлов. `search mileage`: 32 текстовых совпадения (по умолчанию видны только 20). `symbol AssessmentService`: backend/src/Domain/AssessmentService.php:14. `usages max_mileage_km`: 0; это не отсутствие использования — строковый ключ нашёлся через `search max_mileage_km` (3 совпадения). Индекс нельзя считать доказательством полноты для ключей массива.

## 2.1.3: браузерный результат
Прогон успел завершиться до просьбы пользователя «код не поднимай». После просьбы сервер остановлен; повторные прогоны не выполнялись.
Реальные Chromium/Playwright, настоящий PHP-код, временная SQLite вместо MySQL; это не проверка штатного Docker-окружения.
Команда: node browser.cjs; сценарий рядом в evidence/browser.cjs. Открыть форму, заменить #mileage, нажать «Отправить заявку», дождаться ответа /api/applications и снять полный скриншот.

| Пробег | HTTP | Дословно на экране | Скриншот |
|---|---|---|---|
| 399999 | 201 | APPROVE, 50 %, 450 000 ₽ | evidence/399999.png |
| 400000 | 201 | APPROVE, 50 %, 450 000 ₽ | evidence/400000.png |
| 400001 | 201 | REVIEW, 50 %, 0 ₽ | evidence/400001.png |
| 600000 | 422 | Заявка не принята; mileage: Пробег от 0 до 500000 км | evidence/600000.png |

Полный дословный текст и JSON: evidence/desktop-results.json. На 400001 отдельного объяснения причины review нет: интерфейс выводит только решение и лимит. Второе место, поле ввода, не показывает порог 400000. Для причин нужен отдельный продуктовый запрос.

## 2.1.4: маршрутизация
- «Проверь 400001 и покажи, что видит пользователь» → Playwright, реальный POST и screenshot → подходящий инструмент.
- «Где порог пробега и его значение?» → ast-index search max_mileage_km, без браузера → backend/config/rules.php:23, 500000; порог review рядом, 400000.
Это протокол проверки инструментов, не две независимые сессии Kilo. Автовыбор MiniMax не проверен.
