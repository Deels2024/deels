# Deels new design staging

This staging profile is isolated from production and is intended for the `codex/new-design-migration` branch only.

## Local/test server launch

```bash
cp .env.staging.example .env.staging
docker compose -f docker-compose.staging.yml build
docker compose -f docker-compose.staging.yml run --rm app php artisan key:generate --show
```

Copy the generated key into `APP_KEY` in `.env.staging`, then run:

```bash
docker compose -f docker-compose.staging.yml up -d
```

The application is exposed on `http://SERVER:8080`.

## Database

The compose stack creates a dedicated MySQL database named `deels_staging`. It is not connected to the production database.

Before testing authenticated/data-driven screens, load only a sanitized staging database or run the project's approved migrations/seed procedure. Do not import production secrets into `.env.staging`.

## Health check

```bash
curl -I http://127.0.0.1:8080/
docker compose -f docker-compose.staging.yml ps
```

## Browser smoke checklist

Test desktop and mobile widths for:

- `/`
- `/challenges?content=challenges`
- `/challenges?content=battles`
- `/stories?type=new`
- `/campaign`
- login/register
- profile/dashboard
- wallet
- messages
- challenge/battle create
- challenge/battle detail
- campaign detail
- story popup/swipe

For payments, stop before any real charge unless dedicated staging gateway credentials are configured.

## Production safety

- This profile does not deploy automatically.
- It does not modify `main`.
- It contains no production credentials.
- Use a separate staging hostname and separate staging database.
