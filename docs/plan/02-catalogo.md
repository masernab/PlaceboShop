# Fase 2 — Catálogo (productos, categorías, seed)

**Objetivo:** catálogo navegable con categorías, búsqueda, filtros, orden y páginas de producto; `/` pasa a ser la tienda; ~42 productos ficticios bilingües con imágenes SVG generadas.

## Checklist

### Migraciones y modelos
- [x] Migración `create_categories_table` (slug unique, `name` json, `description` json nullable, `position`).
- [x] Migración `create_products_table` (FK category, slug/sku unique, `name`/`description` json, `price_cents`, `compare_at_price_cents` nullable, `stock`, `is_active`, `is_featured`; índices `(category_id,is_active)`, `price_cents`, `is_featured`).
- [x] Migración `create_product_images_table` (FK product, `path`, `alt`, `position`).
- [x] Trait `app/Models/Concerns/HasLocalizedFields.php` — resuelve campo json al locale actual con fallback EN.
- [x] `app/Models/Category.php` — `products()`, casts json, scope `ordered()`.
- [x] `app/Models/Product.php` — `category()`, `images()` (por position), `primaryImage()` (hasOne ofMany), scopes `active/featured/search/inCategory/priceBetween/sorted` (`newest|price_asc|price_desc|name`).
- [x] `app/Models/ProductImage.php` — accessor `url` (`asset($path)`).

### Factories y seeders
- [x] `CategoryFactory`, `ProductFactory` (bilingüe, precios 900–19900¢), `ProductImageFactory`.
- [x] `database/seeders/Support/PlaceholderSvg.php` — SVG 800×1000, gradiente pastel determinista (`crc32($slug)`), nombre del producto; idempotente; escribe en `public/images/products/`.
- [x] `CategorySeeder` — 6 categorías EN/ES: Fashion/Moda, Beauty/Belleza, Accessories/Accesorios, Jewelry/Joyería, Home & Lifestyle/Hogar y Estilo, Wellness/Bienestar.
- [x] `ProductSeeder` — ~42 productos curados bilingües (7/categoría), algunos con `compare_at_price_cents` (oferta) y `is_featured`; 2 SVGs por producto.
- [x] `AdminUserSeeder` — admin@placeboshop.test / password (is_admin, verificado) + demo@placeboshop.test.
- [x] `DatabaseSeeder` orquesta en orden. Añadir `public/images/products/` a `.gitignore`.

### Controladores, rutas, resources
- [x] `routes/shop.php` requerido desde `routes/web.php`; `/` → `Shop\HomeController` (reemplaza welcome); `GET /products` y `GET /products/{product:slug}` → `Shop\ProductController`.
- [x] `Shop\ProductController@index` — filtros `?q,category,min,max,sort,page`, paginación. (Los agregados `withAvg('reviews','rating')`/`withCount` se añadirán en Fase 6 cuando exista la relación `reviews`.)
- [x] Resources: `ProductCardResource`, `ProductResource`, `CategoryResource` (localizados server-side).
- [x] Regenerar Wayfinder tras añadir rutas.

### Páginas
- [x] `resources/js/pages/shop/home.tsx` — hero + destacados + tarjetas de categoría.
- [x] `resources/js/pages/shop/products/index.tsx` — sidebar de filtros (categorías, rango de precio, sort), grid `ProductCard`, paginación; partial reloads (`only: ['products']`, `preserveState`).
- [x] `resources/js/pages/shop/products/show.tsx` — galería, precio (+tachado si oferta), cantidad + add-to-cart (botón stub hasta Fase 3), relacionados. Secciones wishlist/reseñas llegan en Fase 6.
- [x] Componentes: `product-card.tsx`, `price.tsx` (Intl.NumberFormat con locale).
- [x] Búsqueda del header (Fase 1) conectada a `products.index?q=`.

### Tests
- [x] `tests/Feature/Shop/CatalogTest.php` — home carga; índice filtra por categoría/precio/búsqueda/orden; producto inactivo → 404; show muestra localizado según sesión.

## Verificación
- `php artisan migrate:fresh --seed` genera catálogo + SVGs.
- Navegar: home → categoría → filtros → detalle de producto, en EN y ES.
