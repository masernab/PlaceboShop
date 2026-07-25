# Fase 7 — Panel de administración

**Objetivo:** CRUD de productos (con imágenes), categorías y cupones + vista de pedidos, en páginas Inertia propias protegidas por el middleware `admin`.

## Checklist

### Backend
- [x] `routes/admin.php` requerido desde web.php: `prefix('admin')->name('admin.')->middleware(['auth','verified','admin'])`.
- [x] `Admin\DashboardController` — stats (productos, pedidos de hoy, usuarias) + últimos pedidos.
- [x] `Admin\ProductController` — resource (index/create/store/edit/update/destroy) con requests validando campos bilingües (`name.en`, `name.es`, …), precio decimal → cents.
- [x] `Admin\ProductImageController` — `store` (upload) y `destroy`. Disco custom `public_uploads` → `public/uploads/products` (sin symlink; añadir a `.gitignore`). `ProductImage::url` soporta ambos orígenes.
- [x] `Admin\CategoryController` — resource sin show (formularios en dialogs).
- [x] `Admin\CouponController` — resource sin show.
- [x] `Admin\OrderController` — `index`, `show` (read-only, status computado).
- [x] Resources Admin (JSON completo bilingüe para edición).
- [x] Regenerar Wayfinder.

### Frontend
- [x] `resources/js/layouts/admin-layout.tsx` — wrapper del `app-sidebar-layout.tsx` existente con nav: Dashboard, Products, Categories, Orders, Coupons + "Back to shop".
- [x] `pages/admin/dashboard.tsx` — stat cards + tabla de últimos pedidos.
- [x] `pages/admin/products/index.tsx` — tabla (thumb, nombre EN, categoría, precio, activo, acciones) + búsqueda.
- [x] `pages/admin/products/form.tsx` — compartida create/edit: pares de campos EN/ES, select de categoría, precio, flags, sección de imágenes (existentes + upload + eliminar + reordenar por position).
- [x] `pages/admin/categories/index.tsx` — tabla + dialog create/edit.
- [x] `pages/admin/coupons/index.tsx` — tabla + dialog create/edit.
- [x] `pages/admin/orders/index.tsx` y `show.tsx` — read-only con status badge.
- [x] Link "Admin" en el menú de usuario del shop-layout solo si `is_admin`.

### Tests
- [x] `tests/Feature/Admin/AdminAccessTest.php` — loop sobre todas las rutas admin: guest → login, no-admin → 403, admin → 200.
- [x] `tests/Feature/Admin/ProductCrudTest.php` — CRUD con payloads bilingües, validación, upload con `Storage::fake`.
- [x] `tests/Feature/Admin/CategoryCrudTest.php`, `CouponCrudTest.php`.

## Verificación
- Login admin@placeboshop.test → crear producto con imagen → visible en la tienda; no-admin recibe 403 en `/admin`.
