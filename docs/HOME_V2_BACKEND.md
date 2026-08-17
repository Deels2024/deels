# Home v2 backend contract

The homepage facade can be replaced without changing authentication, payments,
story previews, contest participation, or campaign pages.

## Stable endpoint

`GET /api/v1/home`

The response contains:

- `meta.section_order` — the order used by the approved long-form layout;
- `links` — existing web routes for catalog and creation actions;
- `bank` — numeric and eight-digit formatted DEELS balance;
- `metrics` — real database totals without presentation-layer offsets;
- `filters` — supported challenge and battle filters;
- `sections` — normalized card payloads for challenges, stories, battles,
  directions, and campaigns.

Every media object contains `type`, `url`, `poster`, and `aspect_ratio`. Story
and campaign cards use `9:16`; challenge cards use `16:9`. Campaign cards use
the latest active campaign story when one exists and fall back to the campaign
cover.

## Server-rendered page

The existing `/` route and `home.blade.php` remain the default. They now use
`HomePageDataService`, the same data source as the v1 endpoint. This preserves
SEO and avoids a second set of homepage queries.

The production-ready facade is isolated in:

- `resources/views/home-v2.blade.php`;
- `public/dist/css/home-v2.css`;
- `public/dist/js/home-v2.js`.

It uses the same server-rendered collections as the current homepage, keeps the
existing story modal and routes, and adds touch-friendly horizontal rails. The
"funded campaigns" and completed-campaign congratulations blocks are intentionally
not rendered in v2. Recently funded and new campaigns remain as vertical `9:16`
cards.

## Administrator preview

After the branch has been deployed, a full administrator can open:

```text
/home-v2-preview
```

The route requires authentication and checks `User::is_admin()` separately, so
campaign and comment moderators cannot open it. It always renders Home v2 even
when `HOME_DESIGN_V2=false`, adds `noindex,nofollow,noarchive`, and does not
change the homepage seen by other users.

After deploying and verifying the files, enable the facade with:

```dotenv
HOME_DESIGN_V2=true
```

If the v2 template is absent, the controller safely falls back to the existing
`home.blade.php` even when the flag is enabled.

After changing the flag, rebuild the Laravel caches:

```shell
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

## Cache and rollback

Homepage blocks are cached for five minutes by default. The duration can be
changed with `HOME_CACHE_TTL_SECONDS`. Rollback only requires setting
`HOME_DESIGN_V2=false` and clearing the Laravel config/view cache.
