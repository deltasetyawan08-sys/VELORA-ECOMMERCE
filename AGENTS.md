# AGENTS.md

## What this repo actually is
- Bare **Laravel 13** skeleton (PHP 8.3, `laravel/framework ^13.17`). Almost no app code exists yet: only the default `User` model, base `Controller`, `welcome` view, and a single `/` route. Do not assume features exist because a doc describes them.
- `PRD.md` (Indonesian) is the authoritative spec for the target app: **VELORA**, a single-seller fashion e-commerce site with Midtrans Snap payments. Nothing in it is implemented yet.
- No git repository is initialized (`git init` first if commits are needed).

## Ignore the stale bootstrap instructions
- The prior `AGENTS.md`/`CLAUDE.md` content told agents to run `composer require laravel/boost`. **Boost is not installed** (absent from `composer.json`/`composer.lock`). That guidance is obsolete; this file replaces it.

## Commands
- Full setup: `composer setup` (deps, copy `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`).
- Dev (all processes): `composer dev` -> `php artisan dev`, which runs `serve` + `queue:listen` + `npm run dev` concurrently (`php artisan dev:list` to inspect).
- Frontend: `npm run dev` (Vite) / `npm run build`.
- Test: `composer test` (clears config, then `php artisan test`). Single test: `php artisan test --filter=Name` or `php artisan test tests/Feature/ExampleTest.php`.
- Format: `vendor/bin/pint` (Laravel Pint, default rules, no config file).

## Toolchain quirks
- Tests use **PHPUnit 12** (`phpunit.xml`), not Pest, despite `pestphp/pest-plugin` being allow-listed in `composer.json`. Test DB is in-memory SQLite.
- Frontend is **Tailwind CSS v4** via the `@tailwindcss/vite` plugin; theme config lives in `resources/css/app.css` under `@theme` (there is no `tailwind.config.js`). Vite 8, ESM (`"type": "module"`).
- `.npmrc` sets `ignore-scripts=true`; don't rely on npm lifecycle scripts.
- `laravel/pao` reformats PHP test-tool output for agents.

## Database
- Default connection is **SQLite** (`DB_CONNECTION=sqlite`, `database/database.sqlite`), but `PRD.md` targets **MySQL**. Switch `.env` to MySQL when implementing the spec.
- Only default migrations exist (users/cache/jobs). All domain tables (categories, products, variants, carts, orders, payments) still need creating per PRD section 11.

## Conventions from the PRD (easy to get wrong)
- Put business logic in `app/Services`, validation in Form Requests, authorization in middleware + policies; use enums for role/statuses (PRD section 12.1).
- Store money as integer rupiah (BIGINT unsigned), never floats.
- Keep payment status and order status separate. Payment status must come only from verified Midtrans notifications or server-side status calls, never from the browser or admin actions (PRD sections 10, 14).
- Target brand/design tokens are in PRD section 4; current views are still Laravel defaults.
