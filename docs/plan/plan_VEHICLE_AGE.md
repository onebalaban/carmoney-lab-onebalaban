# Vehicle age Implementation Plan
Метод исполнения: последовательно в текущей ветке.
Goal: автомобиль старше конфигурационного max_age_years получает reject.
Architecture: валидатор отвечает за допустимый год; assessment — за риск-решение. Один параметр из конфига через AppFactory с совместимым значением по умолчанию.
Tech Stack: PHP / PHPUnit.
Spec: docs/spec/spec_VEHICLE_AGE.md.
## Global Constraints
Не менять существующие пороги rules.php. Без сервера и деплоя.
## Review Focus
Возраст 20, старше 20 при низком/среднем/высоком LTV, min_year, будущее, взаимодействие с пробегом.
## Task 1
Файлы: tests/Unit/VehicleAgeDecisionTest.php, ApplicationValidator.php, AssessmentService.php, AppFactory.php.
Интерфейсы: AssessmentService::assess(array): array; добавить необязательный int maxVehicleAge из конфига. Остальные контракты сохраняются.
- [x] Тест 19/20/21 и 36 лет на 50/75/95 LTV, старое авто + большой пробег; невалидный год.
- [x] make test: убедиться, что новое поведение падает до реализации; отдельный коммит.
- [x] Убрать бизнес-возраст из валидатора, применить reject в assessment по конфигу.
- [x] make test и make lint; не менять тесты после реализации.
- [x] Проверить diff: существующие пороги и тесты неизменны, коммит результата.
