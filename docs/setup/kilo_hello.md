готов
1) Учебный сервис предварительной оценки заявки на заём под ПТС: принимает заявку, считает LTV и возвращает `approve` / `review` / `reject`.
2) Makefile: `make help`, `make up`, `make down`, `make ps`, `make logs`, `make install`, `make test`, `make lint`, `make seed`; docker-compose.yml: backend запускается командой `php -S 0.0.0.0:8080 -t backend/public backend/public/router.php`, MySQL проверяется `mysqladmin ping -h 127.0.0.1 -ulab -plab`.
3) Решение `approve` / `review` / `reject` считается в папке `backend/src/Domain` (`DecisionEngine.php`).
модель: training-2026-09-minimax-m3
