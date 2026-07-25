# Fase 6 — Wishlist y reseñas

**Objetivo:** favoritos por usuaria y reseñas con estrellas restringidas a compradoras.

## Checklist

### Backend
- [x] Migraciones `create_wishlist_items_table` (unique `user_id,product_id`) y `create_reviews_table` (rating 1–5, title nullable, body, unique `product_id,user_id`).
- [x] Modelos `WishlistItem`, `Review` (+ relaciones en User y Product; `Product::wishlistedBy()`).
- [x] `User::hasPurchased(Product): bool` (exists a través de order_items).
- [x] `Shop\WishlistController` — `index`, `store`, `destroy` (toggle por producto). Rutas auth: `GET /wishlist`, `POST/DELETE /wishlist/{product}`.
- [x] `Shop\ReviewController@store` — solo si `hasPurchased` y sin reseña previa; rating 1–5. Ruta `POST /products/{product:slug}/reviews`.
- [x] `ProductController` ahora incluye `withAvg('reviews','rating')`, `withCount('reviews')`, lista de reseñas en show, props `canReview`/`inWishlist`.
- [x] `ReviewResource`. `ReviewFactory` + `ReviewSeeder` (0–6 reseñas por producto de usuarias fake).

### Frontend
- [x] `pages/shop/wishlist.tsx` — grid con quitar y mover al carrito. Estado vacío.
- [x] Toggle corazón en `product-card.tsx` y `products/show.tsx` (invitado → redirect a login).
- [x] `components/shop/star-rating.tsx` (display + input).
- [x] Sección reseñas en `products/show.tsx`: media + distribución simple, lista, formulario solo si `canReview`.
- [x] Icono wishlist en el header (shop-layout).

### Tests
- [x] `tests/Feature/Shop/WishlistTest.php` — toggle añade/quita; unique; requiere auth.
- [x] `tests/Feature/Shop/ReviewTest.php` — sin compra → 403; una reseña por producto; bounds del rating; aparece en show.

## Verificación
- Comprar un producto → reseñarlo; corazón persiste entre sesiones; media de estrellas visible en cards.
