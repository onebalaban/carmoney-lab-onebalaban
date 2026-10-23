# 2.5.1 Superpowers
Библиотека установлена локально в .kilo/skills/: brainstorming, writing-plans, test-driven-development, verification-before-completion, using-superpowers, executing-plans.
Источник: https://github.com/obra/superpowers, commit 2ce8aa15d05bd57213e6f746f953aafb7ded7a8b; лицензия рядом.

Сценарий: «Машины старше 20 лет должны сразу получать отказ, независимо от LTV».
Возраст >20 становится reject; будущий/слишком ранний год остаётся ошибкой ввода. Порог остаётся в конфиге. Проверки: возраст 19/20/21 на approve/review/reject по LTV.
Цикл реализации и результат в домашнем PR [ДЗ 2.7]. Для обнаружения новых скиллов в Kilo нужен /reload. Запуск приложения не требуется.
