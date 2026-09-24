# Карта кода: как считается решение approve / review / reject

Разбор `backend/src/Domain/` и `backend/config/rules.php`. Код не менялся —
это описание текущего состояния.

---

## Участвующие файлы

| Файл | Роль |
|---|---|
| `backend/config/rules.php` | справочник бизнес-чисел: пороги валидации и решения |
| `backend/src/Domain/AssessmentService.php` | оркестратор: валидация → LTV → решение → ответ |
| `backend/src/Domain/ApplicationValidator.php` | валидация и нормализация полей заявки |
| `backend/src/Domain/VinValidator.php` | проверка VIN (формат) |
| `backend/src/Domain/VehicleAge.php` | возраст авто в полных годах |
| `backend/src/Domain/LtvCalculator.php` | расчёт LTV |
| `backend/src/Domain/DecisionEngine.php` | решение по LTV |
| `backend/src/Domain/ValidationException.php` | ошибка валидации (не решение) |

Связка: `backend/src/AppFactory.php:27–39` подключает `rules.php`, собирает
валидатор, калькулятор и движок решений; `backend/src/Http/ApplicationController.php`
вызывает `AssessmentService::assess()`.

---

## Порядок вызовов

Точка входа — `AssessmentService::assess(array $payload)` (`AssessmentService.php:28`):

1. **`ApplicationValidator::validate($payload)`** (`ApplicationValidator.php:24`)
   - `VinValidator::isValid($vin)` (`VinValidator.php:18`): длина 17
     (`rules['vin']['length']`), только `A-Z` и `0-9`, без `I`, `O`, `Q`
     (`rules['vin']['forbidden_chars']`).
   - `VehicleAge::inYears($year)` (`VehicleAge.php:18`): текущий год (задаётся
     в `AppFactory.php:34` как `(int) date('Y')`) минус год выпуска.
   - Проверки по `rules.php`: `year >= min_year` (1990), `age >= 0`,
     `age <= max_age_years` (20); `mileage` от 0 до `max_mileage_km` (500 000);
     `market_value > 0`; `requested_amount` 50 000–2 000 000 (`rules['amount']`);
     `term_months` 3–48 (`rules['term']`).
   - Есть ошибки → бросается `ValidationException` (`ValidationException.php:9`),
     решения не будет вообще.
   - Иначе возвращается нормализованный массив: `vin`, `year`, `mileage`,
     `market_value`, `requested_amount`, `term_months`.
2. **`LtvCalculator::calculate($input['requested_amount'], $input['market_value'])`**
   (`LtvCalculator.php:15`): `round(сумма / стоимость * 100, 2)` — LTV в процентах.
3. **`DecisionEngine::decide(float $ltv)`** (`DecisionEngine.php:30`), пороги из
   `rules['ltv']`: `approve_max = 60.0`, `review_max = 85.0`:
   - `ltv < 60.0` → `approve`
   - `ltv <= 85.0` → `review`
   - иначе → `reject`

   Примечание из кода: граница approve строгая (`<`, `DecisionEngine.php:32`),
   то есть `ltv == 60.0` даёт `review`, хотя комментарии в `DecisionEngine.php:10`
   и `rules.php:39` описывают границу как `<=`.
4. **Ответ** (`AssessmentService.php:35–41`): `vehicle_age` (снова
   `VehicleAge::inYears`), `ltv`, `decision`, `approved_limit` =
   `requested_amount` при `approve`, иначе 0, плюс исходный `input`.

Отдельно: справочник `ltv_by_age` заполнен (`rules.php:53–58`), но лимит по нему
не считается — это задача LOAN-12, использования в коде нет.

---

## Гипотетическое правило «пробег не больше 400 000 км, иначе review»

**Функция:** `DecisionEngine::decide()` (`DecisionEngine.php:30–41`) — единственное
место, где формируется строка решения. Правило меняет *решение* (approve → review),
а не валидацию, поэтому в `ApplicationValidator` оно не встаёт: валидатор умеет
только бросать `ValidationException`.

**Место:** цепочка `if` в `decide()` (строки 32–40) — дополнительная ветка,
которая при пробеге > 400 000 не даёт `approve` (даёт `review`).

**Что для этого уже есть:**

- нормализованный `mileage`: парсится и проверяется в `ApplicationValidator.php:43–46`,
  возвращается в `$input['mileage']` (`ApplicationValidator.php:78`) и доступен
  в `assess()` рядом с вызовом `decide()` (`AssessmentService.php:33`);
- паттерн «число в `rules.php` → порог в конструктор движка» уже используется
  для `rules['ltv']` (`AppFactory.php:37`).

**Чего не хватает:**

- порога 400 000 в `rules.php` — **нет**. Есть только `max_mileage_km = 500000`
  (`rules.php:23`) — это валидационный потолок, а не порог решения. Понадобится
  новый ключ, например `rules['vehicle']['review_mileage_km']` (по конвенции
  проекта число в код не хардкодим).
- пробега внутри `DecisionEngine` — **нет**: конструктор принимает только пороги
  LTV (`DecisionEngine.php:24–28`), `decide()` — только `float $ltv`
  (`DecisionEngine.php:30`). Нужно передать новый порог в конструктор
  (и в связке `AppFactory.php:37`) и пробег в `decide()` — т. е. поменять
  сигнатуру и вызов в `AssessmentService.php:33`.
- семантика взаимодействия с `reject` в коде не определена — надо решить
  при постановке: правило перекрывает только `approve` или и отказ тоже.

---

## Что в коде сейчас проверяется про пробег

- `ApplicationValidator::validate()` (`ApplicationValidator.php:43–46`):
  `mileage` приводится к int; ошибка, если `< 0` или
  `> rules['vehicle']['max_mileage_km']` (500 000, `rules.php:23`) →
  `ValidationException` «Пробег от 0 до 500000 км». Валидный пробег
  (0–500 000) возвращается в `$input['mileage']` и попадает в ответ `assess()`
  в `input`.
- Влияние пробега на LTV, решение или `approved_limit` — **нет**.
- Порог 400 000 км — **нет**.
- Правило «пробег → review» — **нет**.
