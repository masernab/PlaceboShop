# PlaceboShop ✨

A **placebo shopping** e-commerce: the complete thrill of shopping — browsing, cart, checkout, payment, confirmation email and package tracking — where **nothing is ever charged and nothing ever ships**. Transparent entertainment: the retail high without spending a cent.

Bilingual (English/Spanish), with a full admin panel.

## Stack

Laravel 13 · Inertia v3 · React 19 · TypeScript · Tailwind v4 · shadcn/ui · Fortify · Wayfinder · SQLite.

## Setup

```bash
composer run setup          # install, .env, key, migrate, npm install, build
php artisan migrate:fresh --seed
php artisan dev             # server + queue + vite
```

The seeder generates 6 categories, 42 bilingual products with locally generated SVG images, demo coupons and two demo orders in different tracking stages.

## Demo credentials

| Account | Email | Password |
|---|---|---|
| Admin | `admin@placeboshop.test` | `password` |
| Shopper | `demo@placeboshop.test` | `password` |

The admin panel lives at `/admin` (products, categories, coupons and orders).

## Test cards

Payment is 100% simulated — cards are Luhn-validated for realism, but no real payment data is accepted, stored or charged (only the brand and last 4 digits are kept).

| Card | Result |
|---|---|
| `4242 4242 4242 4242` | Payment approved |
| `4000 0000 0000 0002` | Simulated decline |

Any future expiry date and any CVC work.

## Demo coupons

`WELCOME10` (10% off) · `GLOW20` (20% off, min $50) · `TREAT5` ($5 off) · `LASTSEASON` (expired, for testing).

## Tests & checks

```bash
composer test               # pint, phpstan, phpunit
npm run lint && npm run types:check && npm run build
```

Confirmation emails render in the buyer's language and are written to `storage/logs/laravel.log` (log mailer).
