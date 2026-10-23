# Файлы
- backend/config/rules.php — новый отдельный review_mileage_km=400000, прежние пороги сохранить.
- ApplicationValidator.php — строго распознать целый пробег, пустой не превращать в 0.
- AssessmentService.php — review только вместо approve, лимит 0.
- AppFactory.php — передать параметр из конфига.
- tests/Unit/MileageTest.php — проверки границ и приоритетов.
# Шаги
1. Spec и план. 2. Коммит теста, красный make test. 3. Реализация без изменения теста. 4. make test и make lint.
# Тесты
Отдельные границы: 399999 / 400000 / 400001, 0 / 500000 / 500001; пустой, null, отсутствующий, неверный тип; reject сильнее review.
# Риски
Не заменить max_mileage_km=500000 новым порогом 400000: первое валидация, второе решение. Не повысить reject до review. API и сохранение используют один assessment.
Reason codes вне scope; вопрос перенесён в spec.
