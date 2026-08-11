# Deels v2 migration matrix

Goal: move the `Deels2024/deelsweb` visual system into the working Laravel application without replacing existing controllers, models, payment logic, moderation, queues, WebSocket/Firebase, Telegram or contest rules.

## Strategy

1. Keep Laravel routes and actions as the source of truth.
2. Replace presentation incrementally through Blade/CSS/JS.
3. Preserve all existing form names, CSRF, IDs/data attributes and AJAX endpoints unless an adapter is explicitly introduced.
4. Use `/dist/css/deels-v2.css` as the compatibility layer while individual screens are rebuilt.
5. Test desktop + mobile after each screen family.

## Screen families

| Screen / flow | Existing Laravel source | New design reference | Migration state |
|---|---|---|---|
| Global header/navigation | `layouts/neon/main_menu.blade.php` | `deelsweb` site header | compatibility theme active |
| Global typography/buttons/forms | legacy Neon bundle | `deelsweb` styles | compatibility theme active |
| Home | `home.blade.php` | `/` | visual adapter active; structural rebuild next |
| Challenge catalog | web/API challenge controllers + Blade items | `/challenges` | queued |
| Challenge detail | challenge page + stories | `/challenges/[id]` | queued |
| Create/edit challenge | `challenges.create` | `/create/challenge`, `/edit/challenge/[id]` | queued; preserve current POST fields |
| Participation/invite | contest participation + invitation services | challenge detail/respond | queued; keep backend logic |
| Battles | `battles.page`, dashboard battle controller | `/battles` | queued |
| Stories catalog/viewer | story controllers/views/modal | `/stories`, `/feed` | queued; retain current reactions/payments |
| Swipe viewer | existing story media + JS | new swipe viewer | queued; adapt without changing story API |
| Campaign catalog/detail | campaign views/controller | `/campaigns` | queued |
| Create campaign | existing campaign form | `/create/campaign` | missing/new-style screen required |
| Profile/public profile | profile views/UserController | `/profile`, `/users/[id]` | queued |
| Wallet | existing wallet/payment routes | `/wallet` | queued; backend untouched |
| Messages/chat | messenger Blade + WebSocket | `/messages` | queued; preserve live chat hooks |
| Notifications | system thread/Firebase | `/notifications` | queued; adapt current notification source |
| Search | `/search` | `/search` | queued |
| Settings/security | UserController/settings routes | `/settings` | queued; some new UI actions must map to existing routes |
| Login/register/reset | Laravel auth | auth screens | queued; preserve validation/social auth |
| Legal/contact | existing docs/contact | legal screens | queued |
| Admin/moderation | admin views/controllers | no complete new reference | new screens required in Deels v2 visual language |
| Abuse/reports | AbuseController | no dedicated full new screen | new screen/state required |
| Autopayments | PaymentController | no complete new reference | new screen required |
| Thanks/payment comments | PaymentCommentController | no complete new reference | new screen required |
| Telegram account connection | bot/profile settings | no complete new reference | new settings panel required |

## Functionality that must not be replaced by the design project

- wallet balances, transactions, withdrawals and payment callbacks;
- challenge/battle visibility, participation and winner selection;
- moderation jobs and suspicious-account restrictions;
- media processing, thumbnails, watermarks, CDN and video jobs;
- comments, likes, rate limits and abuse handling;
- Telegram commands/integration;
- Firebase/WebSocket messaging and notifications;
- scheduled commands, AppMetrica/Yandex metrics and mailings.

## Preview

`/design-preview.html` is a static visual reference committed to the migration branch. It does not call production APIs and cannot affect application data.
