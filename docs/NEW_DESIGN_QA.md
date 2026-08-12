# Deels v2 — QA перед тестовым развёртыванием

## Статус автоматической проверки

На ветке `codex/new-design-migration` работают два smoke-workflow:
- `New design smoke checks` — Laravel bootstrap, `route:list`, Blade/assets, основные функциональные маршруты и source-design маркеры.
- `New design auth and admin smoke` — e-mail verification/resend states, auth-защита, moderation actions и финальный QA-слой.

Автоматические проверки не заменяют браузерный staging-тест с реальной БД, платежным sandbox/тестовым сценарием и пользовательскими сессиями.

## Публичные страницы — функциональный перенос выполнен
- Главная: source-faithful hero, реальные топ-челленджи, сторис и копилки.
- Челленджи: каталог, detail, join/leave/rejoin, guest login state, приглашения, reporting, выбор победителя, журнал выполнения.
- Баттлы: каталог, detail, принять/отклонить вызов, участие, reporting, battle reports.
- Сторис: вертикальный feed, preview/fullscreen viewer, swipe/keyboard navigation; старые like/comment/donate/paid hooks сохранены.
- Копилки: detail, progress, донат, share/follow/chat, публичные вкладки «О сборе / Донатеры / Обновления / FAQ».
- Публичный профиль: source-style hero, статистика, «Подписаться / Вы подписаны» и «Написать» поверх существующих Laravel hooks.

## Управление копилкой — функциональный перенос выполнен
- Основные данные копилки.
- Награды: create/edit/delete, сумма, количество, дата доставки, digital download.
- Обновления: create/edit/delete, изображение и описание.
- FAQ: create/edit/delete.
- Для пустых списков и validation/server errors есть отдельные понятные состояния.

## Авторизация — функциональный перенос выполнен
- Login обычный и социальный.
- Registration: nickname, e-mail code, phone, password, антибот-поля, honeypot и обязательные согласия.
- E-mail code: отправка, resend timer/retry-after, loading/success/error states, проверка 6 цифр и `aria-invalid`.
- Forgot/reset password.
- Telegram WebApp auth, reCAPTCHA, analytics и service worker не удалены.

## Личный кабинет — функциональный перенос выполнен
- Профиль и аватар.
- Реферальная статистика, донатерский уровень и реферальная ссылка.
- «Мои разделы»: челленджи, сторис, копилки, кошелёк, сообщения, лайки, друзья, подписки, подписчики, автоплатежи, «Спасибо».
- Настройки, пароль и удаление аккаунта остаются на исходных Laravel actions.

## Кошелёк и платежи — UI состояния выполнены, нужен staging-тест денег
- Два баланса.
- Разделы: движения / биллинг / донаты / автоплатежи.
- Deposit popup и существующий Tinkoff redirect.
- Withdraw popup и банковские поля.
- UI показывает backend-правила: минимум 500 ₽, комиссия 20%, один pending-запрос, повторный вывод не чаще раза в 30 дней, проверка доступного баланса сервером.
- Финальную корректность callback/status/payment flows проверить только на staging с тестовой платёжной средой.

## Сообщения — функциональный перенос выполнен, нужен live realtime-test
- Список диалогов и существующий thread.
- Отправка сообщения и текущие read/unread hooks.
- Пустое состояние списка.
- Online/offline banner без вмешательства в WebSocket/Firebase delivery.
- Жалоба/блокировка сохранены.
- Realtime Firebase/WebSocket поведение проверить двумя реальными staging-сессиями.

## Админка и модерация — функциональный перенос выполнен
- Permission-driven «Админ-центр» строится из существующего Laravel admin menu и не расширяет права.
- Челленджи: moderation/declined/active/blocked, фильтр ID/AI, approve/reject/delete/restart.
- Баттлы и сторис используют тот же новый moderation layer; существующие actions сохранены.
- Жалобы: фильтры, approve/decline, таблица пользователей/причин.
- Платежи и другие `.admin-main` таблицы получают единый light/table/filter/empty-state слой.
- Empty-state «Нет данных» и пояснение фильтров добавляются без изменений controllers.

## Финальный QA-слой — подключён
- `:focus-visible` для keyboard navigation.
- Минимальные touch targets и `touch-action`.
- Mobile overflow safety для таблиц, попапов и контента.
- Mobile auth inputs 16px для предотвращения iOS zoom.
- `aria-disabled` visual state.
- `prefers-reduced-motion`.
- Финальный QA CSS подключён последним и в основном, и в auth layout.

## Что обязательно проверить на реальном staging
1. Главная: desktop/mobile и реальные данные.
2. Guest → login → возврат к челленджу/баттлу и корректное действие после авторизации.
3. Join/leave/rejoin и accept/decline баттла реальными тестовыми пользователями.
4. Создание/редактирование челленджа и загрузку video/story.
5. Story viewer, бесконечный feed, swipe и paid content.
6. Копилка: создание, Rewards/Updates/FAQ, донат и payment callbacks.
7. Кошелёк: deposit/withdraw с тестовой платёжной средой; pending/error/success.
8. Messages: две сессии, WebSocket/Firebase, unread/read, reconnect.
9. Follow/unfollow и запуск чата из публичного профиля.
10. Registration: send/resend e-mail code, invalid/expired code, social auth, reset password.
11. Admin: moderation actions на челленджах/баттлах/сторис/жалобах, права разных ролей.
12. Safari/Chrome: desktop 1440/1280, tablet 768, mobile 390/375/320.
13. Console/network, Laravel logs, 404/419/422/500 и долгие/пустые состояния.

## Перед merge в main
1. Развернуть `codex/new-design-migration` на отдельном тестовом домене.
2. Очистить Laravel view/cache/config cache.
3. Пройти staging checklist выше реальными тестовыми пользователями.
4. Проверить PHP/Laravel logs и browser console/network.
5. Исправить найденные runtime-разрывы.
6. Только после зелёного staging smoke-test отдельно согласовывать merge в `main` и production deploy.
