# Fase 3 — Carrito

**Objetivo:** carrito en BD para invitados (vía `cart_id` en sesión) y usuarios, con merge automático en login, página de carrito y badge en el header.

## Checklist

### Backend
- [ ] Migraciones `create_carts_table` (`user_id` nullable) y `create_cart_items_table` (`quantity`, unique `cart_id,product_id`).
- [ ] `app/Models/Cart.php` (`items()`, `user()`, `subtotalCents()`), `app/Models/CartItem.php` (`product()`).
- [ ] `app/Services/CartService.php`:
  - `current(bool $create = false): ?Cart` — user → `firstOrCreate`; invitado → `session('cart_id')`.
  - `add(Product, int $qty)` (consolida líneas), `updateQuantity`, `remove`, `clear`.
  - `itemCount(): int` para el badge.
  - `totals(Cart, ?Coupon = null)` — subtotal, descuento, envío falso (499¢, gratis ≥ 5000¢), total.
  - `mergeGuestCartIntoUser(User)` — suma cantidades en conflicto, borra cart invitado, olvida `cart_id`.
- [ ] Listener `app/Listeners/MergeGuestCart.php` en evento `Illuminate\Auth\Events\Login`. Verificar si Fortify dispara `Login` al registrarse; si no, escuchar también `Registered`.
- [ ] `Shop\CartController` — `show`, `storeItem`, `updateItem`, `destroyItem`; verificar que el item pertenece al cart actual (invitados no tienen policy). Rutas en `routes/shop.php`.
- [ ] Prop compartida lazy `'cart' => fn () => ['count' => $cartService->itemCount()]` en `HandleInertiaRequests`.
- [ ] `CartResource`.

### Frontend
- [ ] `resources/js/pages/shop/cart.tsx` — líneas con stepper de cantidad, eliminar, caja de totales, CTA a checkout (login si invitado). Estado vacío.
- [ ] `components/shop/quantity-input.tsx`, `components/shop/cart-badge.tsx` (header del shop-layout).
- [ ] Botón add-to-cart real en `products/show.tsx` y `product-card.tsx` con toast de confirmación.

### Tests
- [ ] `tests/Feature/Shop/CartTest.php` — invitado añade/actualiza/elimina; consolidación de líneas; clamping de cantidad; **merge en login suma cantidades**; no se puede mutar item de otro carrito; badge count correcto.

## Verificación
- Invitado llena carrito → login → carrito fusionado. Badge actualiza en cada operación.
