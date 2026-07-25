# Fase 4 — Checkout, pedidos, tracking falso y email

**Objetivo:** el corazón placebo — checkout con tarjeta falsa validada con realismo, creación de orden con snapshots, confirmación celebratoria, tracking falso que "avanza" solo, y email de confirmación.

## Checklist

### Backend
- [ ] Migraciones `create_orders_table` + `create_order_items_table` (ver esquema en 00-overview).
- [ ] Enums `app/Enums/OrderStatus.php` (Paid|Processing|Shipped|OutForDelivery|Delivered|Cancelled, string-backed) — labels en el diccionario frontend por valor.
- [ ] `app/Rules/LuhnCard.php` (checksum Luhn tras quitar espacios, 13–19 dígitos) + `app/Support/CardBrand.php` (prefijo → visa/mastercard/amex).
- [ ] `app/Models/Order.php`:
  - `timeline(): array` — offsets desde `placed_at`: Paid +0, Processing +10min, Shipped +6h, OutForDelivery +30h, Delivered +54h; cada entrada `{status, at, reached}`.
  - accessor `status` — `cancelled_at` → Cancelled; si no, última entrada alcanzada.
- [ ] `app/Models/OrderItem.php`.
- [ ] `app/Policies/OrderPolicy.php` — `view` solo dueño.
- [ ] `app/Http/Requests/Shop/StoreOrderRequest.php` — dirección (`ship_name/line1/city/postal_code/country` requeridos, país en lista corta) + tarjeta (`card_name`, `card_number` Luhn, `card_expiry` MM/YY futuro, `card_cvc` 3–4 dígitos).
- [ ] `Shop\CheckoutController`:
  - `show` — redirect a cart si vacío; pasa resumen de totales.
  - `store` — en `DB::transaction`: rechazo simulado si `4000000000000002`; recalcular totales server-side; crear orden (`order_number` `PB-<año>-<6 alfanum>` con retry, `tracking_number` `PBX` + 10 dígitos, brand + last4); snapshots de items (nombre json bilingüe, precio, imagen); limpiar carrito; encolar mail; redirect a `orders.show` con flash de confirmación. **Nunca persistir PAN/CVC.**
- [ ] `Shop\OrderController` — `index` (latest first), `show` (policy; pasa timeline completo).
- [ ] `app/Mail/OrderConfirmationMail.php` — ShouldQueue, markdown `resources/views/mail/orders/confirmation.blade.php`, locale capturado en checkout, items/totales, link al tracking, footer de transparencia. `MAIL_MAILER=log` local.
- [ ] Rutas auth+verified en `routes/shop.php`: `GET/POST /checkout`, `GET /orders`, `GET /orders/{order}`.
- [ ] `dashboard` → redirect a `/orders`; redirect post-login de Fortify → `/` (verificar `config/fortify.php`); intended URL sigue devolviendo a `/checkout` mid-flow.
- [ ] `OrderResource`, `OrderItemResource`.
- [ ] Factories: `OrderFactory` (+items), estados de antigüedad para demo. Seeder: pedidos demo para demo@placeboshop.test (uno de hace 3 días → Delivered, uno de hace 20 min → Processing).

### Frontend
- [ ] `pages/shop/checkout.tsx` — dos columnas: formulario dirección + tarjeta (formateo del número en grupos, detección de brand visual) y resumen del pedido; estado "processing…" ~1.5s en el botón antes/durante el POST.
- [ ] `pages/shop/orders/index.tsx` — lista con `order-status-badge`.
- [ ] `pages/shop/orders/show.tsx` — modo confirmación celebratoria (flash `justPlaced`) + `tracking-timeline.tsx` (5 etapas, done/current/pending, timestamps).
- [ ] Componentes: `order-status-badge.tsx`, `tracking-timeline.tsx`.

### Tests
- [ ] `tests/Feature/Shop/CheckoutTest.php` — guest→login; carrito vacío→redirect; orden con totales correctos y formato de order_number; brand/last4 guardados; **PAN/CVC ausentes de la BD**; tarjeta de rechazo → error sin orden; Luhn/expiry inválidos → validación; mail encolado (`Mail::fake`).
- [ ] `tests/Feature/Shop/OrderTrackingTest.php` — `travelTo` en cada offset; `cancelled_at` override; 403 para orden ajena.

## Verificación
- Checkout con 4242 4242 4242 4242 → confirmación → refrescar tracking pasado el tiempo (o `travelTo` en tinker). Email visible en `storage/logs/laravel.log`.
