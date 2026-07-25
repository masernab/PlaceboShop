# PlaceboShop — Plan de implementación

E-commerce de **compras placebo** para mujeres: la experiencia completa de comprar (navegar, carrito, checkout, pago, confirmación, tracking) sin que se cobre dinero real ni se envíe nada. Entretenimiento transparente — el "subidón de compra" sin gastar.

## Stack

Laravel 13 + Inertia v3 + React 19 + TypeScript + Tailwind v4 + shadcn/ui + Fortify (auth ya funciona) + Wayfinder + SQLite. Base: Laravel React Starter Kit oficial.

## Estado de avance

| Fase | Documento | Estado |
|---|---|---|
| 1. Fundaciones (i18n, admin flag, shop layout) | [01-fundaciones.md](01-fundaciones.md) | ✅ Completada |
| 2. Catálogo (productos, categorías, seed) | [02-catalogo.md](02-catalogo.md) | ✅ Completada |
| 3. Carrito | [03-carrito.md](03-carrito.md) | ⬜ Pendiente |
| 4. Checkout + pedidos + email | [04-checkout-pedidos.md](04-checkout-pedidos.md) | ⬜ Pendiente |
| 5. Cupones | [05-cupones.md](05-cupones.md) | ⬜ Pendiente |
| 6. Wishlist + reseñas | [06-wishlist-resenas.md](06-wishlist-resenas.md) | ⬜ Pendiente |
| 7. Panel de administración | [07-admin.md](07-admin.md) | ⬜ Pendiente |
| 8. Pulido (i18n completo, estados vacíos, responsive) | [08-pulido.md](08-pulido.md) | ⬜ Pendiente |

Al completar tareas, marcar los checkboxes del documento de fase y actualizar esta tabla (⬜ Pendiente / 🔶 En curso / ✅ Completada).

## Decisiones clave (no reabrir sin motivo)

- **Pagos 100% simulados** — sin pasarela. Tarjeta validada con Luhn para realismo (4242 4242 4242 4242 pasa); `4000000000000002` simula rechazo. Nunca se persiste PAN/CVC, solo brand + last4.
- **Admin custom en Inertia** (no Filament) con flag `users.is_admin` + middleware alias `admin`. Reutiliza el sidebar layout existente.
- **Carrito en BD para todos**: invitados con `cart_id` en sesión; merge al cart del usuario en login vía listener del evento `Login`.
- **Tracking falso computado en lectura** desde `placed_at` + offsets (Paid +0, Processing +10min, Shipped +6h, OutForDelivery +30h, Delivered +54h). Sin cron. Solo `cancelled_at` se persiste.
- **Dinero en centavos** (`*_cents`), formateo cliente con `Intl.NumberFormat(locale)`.
- **Enums**: columnas string + PHP backed enums en `app/Enums/` (SQLite no tiene enums).
- **i18n sin dependencias**: diccionarios `resources/js/lang/{en,es}.json` + hook `useTranslation`; locale en sesión (`PUT /locale`) + middleware `SetLocale`; contenido de catálogo en columnas JSON `{"en":…,"es":…}` resuelto server-side por Resources.
- **Imágenes seed**: SVGs generados localmente en `public/images/products/` (sin red, sin symlink). Uploads del admin a `public/uploads/products` vía disco custom (evita `storage:link` en Windows).
- **Checkout requiere auth** (`auth`+`verified`); invitados llenan carrito y el merge hace el login mid-flow indoloro.
- **`/` es la tienda**; `dashboard` redirige a `/orders`; redirect post-login de Fortify → `/`.

## Esquema de BD

1. `users.is_admin` boolean default false.
2. `categories`: slug unique, `name` json, `description` json nullable, `position`.
3. `products`: category_id FK, slug unique, sku unique, `name`/`description` json, `price_cents`, `compare_at_price_cents` nullable, `stock` (cosmético), `is_active`, `is_featured`. Índices `(category_id,is_active)`, `price_cents`, `is_featured`.
4. `product_images`: product_id FK, `path` relativo web, `alt`, `position`.
5. `carts` (user_id nullable) + `cart_items` (unique `cart_id,product_id`; sin snapshot de precio).
6. `coupons`: code unique uppercase, type (Percent|Fixed), value, `min_subtotal_cents`, `max_uses`, `used_count`, `starts_at`/`expires_at`, `is_active`.
7. `orders`: user_id, `order_number` unique (`PB-2026-XXXXXX`), `placed_at`, `cancelled_at` nullable, totales en cents, coupon_id nullable + `coupon_code` snapshot, `card_brand`, `card_last4`, `tracking_number` falso, dirección inline `ship_*`. Sin columna status (computado). + `order_items` con snapshots (`product_name` json bilingüe, `unit_price_cents`, `image_path`).
8. `reviews`: rating 1–5, title, body, unique(`product_id`,`user_id`).
9. `wishlist_items`: unique(`user_id`,`product_id`).

## Verificación estándar (al cerrar cada fase)

```
php artisan migrate:fresh --seed
composer test                      # pint --test, phpstan, phpunit
npm run lint && npm run types:check && npm run build
php artisan dev                    # navegar http://placeboshop.test
```

## Entorno (Windows + Herd)

Resuelto el 2026-07-25: la versión global de Herd estaba en 8.2 y se cambió con `herd use 8.5`, así que `php` en terminales nuevas ya resuelve 8.5.8. Si una terminal abierta antes del cambio sigue resolviendo 8.2, reabrirla (o quitar `herd\bin\php82` de su `$env:PATH`). Ojo: un PATH obsoleto también rompe `composer test` y `npm run build` (el plugin de Wayfinder invoca `php artisan` vía cmd, que resuelve `php82\php.exe` antes que el shim `php.bat`).

Nota: `php artisan wayfinder:generate` manual necesita `--with-form` (el plugin de Vite ya lo hace con `formVariants: true`).

## Riesgos transversales

- **Wayfinder**: regenerar helpers TS (`php artisan wayfinder:generate`) tras añadir rutas y antes de escribir TSX que las importe.
- **React Compiler** activo: componentes puros, sin mutar props/state.
- **Larastan**: PHPDoc correcto en casts JSON y accessors computados.
- **Convenciones del starter**: `Inertia::flash` + sonner para toasts; formularios como `resources/js/pages/settings/profile.tsx`.
