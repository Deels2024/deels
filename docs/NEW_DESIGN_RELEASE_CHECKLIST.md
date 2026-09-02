# Deels new design — release checklist

Этот чеклист фиксирует минимальный набор проверок перед заменой старого фасада Deels новым дизайном.

## 1. Автоматические проверки GitHub — должны быть зелёными
- `Deels GitHub Pages` — сборка и публикация preview.
- `Deels responsive browser QA` — Chromium, экраны `home/feed/challenges/battles/profile/wallet/messages/create/campaign/admin` на ширинах 375 / 430 / 768 / 1280 / 1440.
- `Deels final design parity` — старые Laravel capabilities + новый compatibility API + real-data wiring.
- `Deels release candidate audit` — frontend lint/build/tests и Laravel route/runtime contracts.
- `New design smoke checks` — Blade surfaces, restored links, contest/campaign/profile/messages/auth/admin states.

## 2. Визуальный контроль
- Один основной шрифт: Gilroy с системным fallback.
- Нет горизонтального overflow страницы.
- Минимальная touch-зона основных действий — 32px в browser smoke; production-цель для ключевых CTA — 42–44px.
- Вертикальный пользовательский контент — 9:16.
- Hero, карточки, кнопки и заголовки используют единые radius/spacing/typography tokens.
- Проверить контраст текста на фото, особенно Feed / Challenges / Battles / Stories.
- Проверить 375 и 430 отдельно: header, CTA, comments sheet, filters, tables, dialogs.

## 3. Главная
- `$topChallenges`, `$topStories`, `$fundedCampaigns` приходят из реального Laravel.
- Hero использует реальный challenge preview при наличии.
- Story cards используют `getStoryPreview()` и сохраняют paid/viewed/blur logic.
- Ссылки `Создать челлендж`, `Смотреть ленту`, `Смотреть все`, campaign detail ведут на реальные Laravel routes.
- Empty state корректен, если челленджей/сторис/копилок нет.

## 4. Челленджи
- Catalog / detail / create / update.
- Join / leave / rejoin.
- Guest → login → возврат к исходному действию.
- Invite users / reporting / winner selection / journal.
- Video upload, poster, validation, frozen/banned/private/finished states.
- Один ответ пользователя — одна корректная story/video-связка согласно текущей бизнес-логике.

## 5. Баттлы
- List / detail / create / update / response / vote.
- Accept / decline invitation.
- Join / leave / rejoin, где применимо.
- Два участника отображаются 9:16, голосование меняет состояние без двойной отправки.
- Finished / draw / skipped / frozen / private states.
- Проверить prize/refund/winner logic без изменения существующих сервисов.

## 6. Stories / Feed
- Вертикальный viewer, swipe, wheel, keyboard.
- Like / unlike / comments / share / save.
- Paid story: закрытое состояние, оплата/донат и повторный просмотр.
- Comments sheet: open/close, submit, keyboard, mobile safe-area.
- Feed не имеет horizontal overflow и элементы действий не перекрывают caption.

## 7. Копилки и платежи
- Campaign list/detail/create/manage.
- Rewards / Updates / FAQ create/edit/delete.
- Donate flow до тестового Tinkoff redirect.
- Callback/status: success / pending / failed / cancelled.
- Share / follow / chat / likes и public tabs.
- Суммы и progress всегда берутся с backend, не из frontend mocks.

## 8. Кошелёк
- Summary / transactions / billing / donate history / autopayments.
- Deposit popup + redirect.
- Withdraw: minimum, 20% commission, pending request, 30-day rule, insufficient balance.
- App Store / coins flows, если активны в production.
- Никаких реальных выводов при QA — только sandbox/test account.

## 9. Auth / profile / social
- Login / logout / registration.
- E-mail code send/resend/invalid/expired.
- Forgot/reset password.
- VK OAuth, Telegram WebApp, reCAPTCHA, anti-bot fields и consent checkboxes.
- Public profile / edit profile / avatar.
- Follow/unfollow, followers/followings/friends/likes.
- Message action из профиля.

## 10. Messages / notifications
- Два реальных тестовых аккаунта.
- Send / receive / read / unread.
- Reconnect after temporary network loss.
- Firebase/WebSocket hooks.
- Complaint / block.
- Notifications list / read / read-all.

## 11. Admin / permissions
- Owner/admin/moderator roles видят только разрешённые пункты.
- Challenge / battle / story moderation.
- Complaints, users, payments/withdrawals, tags, logs, newsletters/statistics/settings.
- Approve/reject/delete/restart actions.
- Filters / empty state / error state / long tables on mobile/tablet.

## 12. Browser/runtime перед merge
- Safari macOS/iOS.
- Chrome desktop/Android.
- 375 / 390 / 430 / 768 / 1280 / 1440.
- Browser console: без новых JS errors.
- Network: без неожиданных 404/419/422/500.
- Laravel logs: без новых exceptions/deprecations, связанных с новым UI.
- Проверить медленные запросы, пустую БД и ошибки API.

## 13. SEO / release surface
- Уникальные title/description для публичных страниц.
- canonical / OG / Twitter metadata.
- Индексируемые challenge/battle/campaign/profile URLs на production, не hash-only preview.
- robots.txt / sitemap.xml / structured data после подключения production routes.
- Preview GitHub Pages не считать SEO-production версией.

## 14. Перед merge в `main`
1. Все обязательные GitHub checks зелёные.
2. Browser QA = 50/50 screen×viewport combinations.
3. Staging/runtime checklist пройден реальными тестовыми пользователями.
4. Payment и withdraw проверены только в test/sandbox режиме.
5. Сняты скриншоты основных экранов на 375 и 1440.
6. Проверены Laravel/browser logs.
7. Только после этого отдельно согласовать merge в `main`.
8. Production deploy и переключение `deels.ru` — отдельное явное действие после merge approval.

## Текущее правило
Новый дизайн не переписывает бизнес-логику без необходимости. Сначала переиспользуем существующий route/controller/service; compatibility layer нужен только как тонкий контракт для нового frontend.
