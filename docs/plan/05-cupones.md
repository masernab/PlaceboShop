# Fase 5 — Cupones

**Objetivo:** cupones de descuento (porcentaje o fijo) aplicables en el carrito y validados de nuevo en checkout.

## Checklist

### Backend
- [x] Migración `create_coupons_table` (code unique uppercase, type, value, `min_subtotal_cents`, `max_uses`, `used_count`, `starts_at`/`expires_at`, `is_active`).
- [x] Enum `app/Enums/CouponType.php` (Percent|Fixed).
- [x] `app/Models/Coupon.php` — cast type, scope `active()`, `isRedeemable(int $subtotalCents): bool`, `discountFor(int $subtotalCents): int` (percent redondeado, fixed capado al subtotal).
- [x] `Shop\CartCouponController` — `store` (valida código, guarda `session('coupon_code')`, errores traducidos: no existe/expirado/agotado/min no alcanzado) y `destroy`. Rutas `POST/DELETE /cart/coupon`.
- [x] Integrar en `CartService::totals()` y en `CheckoutController@store`: re-validar el cupón al pagar (puede haber expirado entre aplicar y pagar), snapshot `coupon_code` + FK, incrementar `used_count`, limpiar sesión.
- [x] `CouponFactory` (states `percent/fixed/expired/exhausted`), `CouponSeeder` — WELCOME10 (10%), GLOW20 (20%, min 5000¢), TREAT5 (500¢ fijo), uno expirado.

### Frontend
- [x] `components/shop/coupon-form.tsx` en `cart.tsx` — input + aplicar/quitar, muestra descuento en totales.
- [x] Resumen del checkout muestra el descuento aplicado.

### Tests
- [x] `tests/Feature/Shop/CouponTest.php` — matemática percent/fixed; min-subtotal; expirado/inactivo/agotado rechazados; `used_count` incrementado al pagar; snapshot en la orden; re-validación en checkout (cupón caduca entre aplicar y pagar → error claro).

## Verificación
- Aplicar WELCOME10 en carrito → totales bajan → checkout conserva el descuento → orden lo registra.
