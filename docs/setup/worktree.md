- `tests/Unit/ApplicationValidatorTest.php` — проверяет приём корректной заявки и нормализацию VIN, отклонение года из будущего и суммы ниже минимума, а также сбор нескольких ошибок валидации за один вызов.
- `tests/Unit/AssessmentServiceTest.php` — проверяет итоговую оценку заявки: `approve` при низком LTV с лимитом в запрошенную сумму, `review` при среднем LTV и `reject` при высоком LTV.
- `tests/Unit/DecisionEngineTest.php` — проверяет выбор решения `approve` / `review` / `reject` по диапазонам LTV, включая граничное значение `85.0`.
- `tests/Unit/LtvCalculatorTest.php` — проверяет расчёт LTV в процентах для разных сумм и выброс исключений при нулевой стоимости автомобиля или неположительной сумме займа.
- `tests/Unit/VinValidatorTest.php` — проверяет формат VIN: корректные верхний и нижний регистр, длину 17 символов, запрещённые буквы, спецсимволы и пустое значение.

Работаю в папке `/Users/roman/GitLab/carmoney-lab-onebalaban/.kilo/worktrees/invented-lyric` на ветке `invented-lyric`.