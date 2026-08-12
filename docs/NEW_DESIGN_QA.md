# Deels v2 — QA перед тестовым развёртыванием

## Публичные страницы
- Главная: hero, топ челленджей, сторис, копилки, статистика.
- Челленджи: каталог, карточка, detail, участие, приглашение, жалоба, завершённые состояния.
- Баттлы: detail, принятие/отклонение приглашения, ответы, завершение.
- Сторис: preview, fullscreen viewer, swipe/arrow navigation, лайк, комментарий, донат, платный контент.
- Копилки: каталог, detail, progress, автор, донат, создание.
- Контакты, оферты и статические страницы.

## Авторизация
- Login обычный и социальный.
- Registration: nickname, e-mail code, phone, password, антибот-поля, согласия.
- Forgot/reset password.
- Ошибки 422/validation читаемы на mobile/desktop.

## Личный кабинет
- Профиль и аватар.
- Реферальная статистика и донатерский уровень.
- Мои челленджи / участие / баттлы.
- Настройки, пароль, удаление аккаунта.
- Админские сервисные балансы видны только соответствующим ролям.

## Кошелёк и платежи
- Два баланса.
- Deposit popup и Tinkoff redirect.
- Withdraw popup и все банковские поля.
- Минимум вывода, лимит 30 дней, insufficient balance.
- Истории: движения, пополнения, донаты.
- Success/fail callbacks и статусы.

## Сообщения
- Список диалогов.
- Открытие существующего thread.
- Создание нового thread.
- Пагинация истории.
- Отправка сообщения, unread/read, поиск.
- Firebase/WebSocket/Telegram уведомления.
- Mobile fullscreen chat.

## Совместимость
- Все исходные Laravel routes и controller actions остаются источником данных.
- Новые CSS/JS подключаются после старого bundle и служат presentation layer.
- Не удалять analytics, service worker, Telegram WebApp auth, payment scripts.
- Проверить Safari/Chrome, desktop 1440/1280, tablet 768, mobile 390/375/320.
- Проверить keyboard focus, overflow, long titles, empty states и ошибки.

## Перед merge в main
1. Развернуть `codex/new-design-migration` на отдельном тестовом домене.
2. Очистить Laravel view/cache/config cache.
3. Пройти все пункты выше реальным тестовым пользователем.
4. Проверить PHP/Laravel logs и браузерную console/network.
5. Только после smoke-test решать о merge в `main`.
